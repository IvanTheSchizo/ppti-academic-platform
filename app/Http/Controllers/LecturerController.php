<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\CourseRecord;
use App\Models\Lecturer;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LecturerController extends Controller
{
    /**
     * Display a listing of lecturers.
     */
    public function index(Request $request)
    {
        $query = $this->filteredLecturersQuery($request);
        $exportLecturers = (clone $query)->get();

        $stats = [
            'total' => $exportLecturers->count(),
            'active' => $exportLecturers->where('status', 'Active')->count(),
            'inactive' => $exportLecturers->where('status', 'Inactive')->count(),
        ];

        // Pagination (retaining all active query parameters)
        $perPage = max((int) $request->input('per_page', 10), 1);
        $lecturers = $query->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json($lecturers);
        }

        // Dropdown option sets for filter drawer
        $jjaOptions = Lecturer::whereNotNull('jja')->pluck('jja')->unique()->sort()->values();
        $educationOptions = Lecturer::whereNotNull('latest_education')->pluck('latest_education')->unique()->sort()->values();
        $batches = Batch::all()->sortBy(fn ($batch) => (int) preg_replace('/\D/', '', $batch->batch_name), SORT_NUMERIC)->values();

        return view('lecturers.index', compact('lecturers', 'exportLecturers', 'stats', 'jjaOptions', 'educationOptions', 'batches'));
    }

    private function filteredLecturersQuery(Request $request)
    {
        $query = Lecturer::with('batches');

        // 1. Search Query
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('lecturer_code', 'like', "%{$q}%")
                    ->orWhere('nip', 'like', "%{$q}%")
                    ->orWhere('email_binus_edu', 'like', "%{$q}%")
                    ->orWhere('email_binus_ac_id', 'like', "%{$q}%")
                    ->orWhereHas('batches', fn ($bq) => $bq->where('batch_name', 'like', "%{$q}%"));
            });
        }

        // 2. Filters
        $selectedStatuses = $this->multiFilterValues($request, 'status');
        if ($selectedStatuses !== []) {
            $query->whereIn('status', $selectedStatuses);
        }

        $selectedBatches = $this->multiFilterValues($request, 'batch');
        if ($selectedBatches !== []) {
            $query->whereHas('batches', fn ($bq) => $bq->whereIn('batch_name', $selectedBatches));
        }

        $selectedJjas = $this->multiFilterValues($request, 'jja');
        if ($selectedJjas !== []) {
            $query->whereIn('jja', $selectedJjas);
        }

        $selectedEducations = $this->multiFilterValues($request, 'latest_education');
        if ($selectedEducations !== []) {
            $query->whereIn('latest_education', $selectedEducations);
        }

        // 3. Dynamic Sorting
        $sortable = ['lecturer_code', 'name', 'email_binus_edu', 'status'];
        $sort = $request->input('sort');
        $direction = strtolower($request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (in_array($sort, $sortable)) {
            $query->orderBy($sort, $direction);
        } else {
            $query->orderBy('name', 'asc');
        }

        return $query;
    }

    /**
     * Display the specified lecturer profile with Overview and Courses tabs.
     */
    public function show(Request $request, Lecturer $lecturer)
    {
        $lecturer->load('batches');
        $activeTab = $request->query('tab', 'overview');

        $courses = null;
        $exportCourses = collect();
        if ($activeTab === 'courses') {
            $courseQuery = $this->coursesQuery($lecturer, $request->input('q'));

            $transformCourse = function ($record) {
                return (object) [
                    'id' => $record->id,
                    'period' => $record->period?->period_name ?? '-',
                    'course_id' => $record->course?->course_code ?? '-',
                    'course_name' => $record->course?->course_name ?? '-',
                    'class' => $record->classGroup?->class_code ?? '-',
                    'ikadq' => $record->lecturerPerformance?->ikadq ?? 0,
                    'students_count' => $record->student_grades_count,
                ];
            };

            $exportCourses = (clone $courseQuery)->get()->map($transformCourse);

            $perPage = max((int) $request->input('per_page', 10), 1);
            $courses = $courseQuery->paginate($perPage)->withQueryString();
            $courses->getCollection()->transform($transformCourse);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'lecturer' => $lecturer,
                'courses' => $courses,
            ]);
        }

        $kpi = $lecturer->performances()->avg('ikadq');
        $batches = Batch::all()->sortBy(fn ($batch) => (int) preg_replace('/\D/', '', $batch->batch_name), SORT_NUMERIC)->values();

        return view('lecturers.profile', compact('lecturer', 'activeTab', 'courses', 'exportCourses', 'kpi', 'batches'));
    }

    private function coursesQuery(Lecturer $lecturer, ?string $search = null)
    {
        return CourseRecord::where('lecturer_id', $lecturer->id)
            ->with(['course', 'period', 'classGroup.batch', 'lecturerPerformance'])
            ->withCount('studentGrades')
            ->when(filled($search), fn ($query) => $query->where(function ($sub) use ($search) {
                $sub->where('record_code', 'like', "%{$search}%")
                    ->orWhereHas('course', function ($cq) use ($search) {
                        $cq->where('course_name', 'like', "%{$search}%")
                            ->orWhere('course_code', 'like', "%{$search}%");
                    });
            }));
    }

    /**
     * Update the specified lecturer.
     */
    public function update(Request $request, Lecturer $lecturer, AuditLogger $auditLogger)
    {
        $validated = $request->validate([
            'lecturer_code' => 'required|string|max:20|unique:lecturers,lecturer_code,'.$lecturer->id,
            'name' => 'required|string|max:255',
            'latest_education' => 'nullable|string|max:50',
            'jja' => 'nullable|string|max:100',
            'status' => 'required|string|in:'.implode(',', Lecturer::STATUSES),
            'status_reason' => 'nullable|string|max:1000',
            'email_binus_edu' => 'nullable|email|max:255|unique:lecturers,email_binus_edu,'.$lecturer->id,
            'email_binus_ac_id' => 'nullable|email|max:255|unique:lecturers,email_binus_ac_id,'.$lecturer->id,
            'phone_number' => 'nullable|string|max:30',
            'gender' => 'nullable|in:Male,Female',
            'job_start_date' => 'nullable|date',
            'batch_ids' => 'nullable|array',
            'batch_ids.*' => 'exists:batches,id',
        ]);

        if (! in_array($validated['status'], Lecturer::REASON_STATUSES, true)) {
            $validated['status_reason'] = null;
        }

        // Capture the approved fields before and after the update so the audit log
        // records only actual changes and never includes unrelated model attributes.
        $fields = [
            'lecturer_code', 'name', 'jja', 'latest_education', 'status', 'status_reason',
            'gender', 'email_binus_edu', 'email_binus_ac_id', 'phone_number', 'job_start_date',
        ];

        DB::transaction(function () use ($lecturer, $validated, $auditLogger, $fields) {
            $lecturer->load('batches');
            $oldValues = $auditLogger->snapshot($lecturer, $fields);
            $oldValues['job_start_date'] = $lecturer->job_start_date?->toDateString();
            $oldValues['batches'] = implode(', ', $lecturer->sortedBatchNames()) ?: null;

            $lecturer->update(collect($validated)->except('batch_ids')->all());
            $lecturer->batches()->sync($validated['batch_ids'] ?? []);

            $lecturer->refresh();
            $lecturer->load('batches');
            $newValues = $auditLogger->snapshot($lecturer, $fields);
            $newValues['job_start_date'] = $lecturer->job_start_date?->toDateString();
            $newValues['batches'] = implode(', ', $lecturer->sortedBatchNames()) ?: null;

            if ($oldValues !== $newValues) {
                $auditLogger->record('UPDATE', 'Lecturer', $lecturer->id, $oldValues, $newValues);
            }
        });

        if ($request->wantsJson()) {
            return response()->json($lecturer);
        }

        return redirect()->route('lecturers.profile', $lecturer->id)->with('success', 'Lecturer updated successfully.');
    }

    /**
     * Export lecturers list as CSV.
     */
    public function export(Request $request)
    {
        $filename = 'lecturers_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $ids = array_values(array_filter((array) $request->input('ids', [])));
        $query = $this->filteredLecturersQuery($request)
            ->when($ids !== [], fn ($q) => $q->whereIn('lecturers.id', $ids));

        $callback = function () use ($query) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'NIP',
                'Lecturer Code',
                'Name',
                'Batches',
                'Job Start Date',
                'Email (binus.edu)',
                'Email (binus.ac.id)',
                'Phone Number',
                'JJA',
                'Latest Education',
                'Status',
                'Reason',
            ]);

            $query->chunk(200, function ($lecturers) use ($handle) {
                foreach ($lecturers as $lecturer) {
                    fputcsv($handle, [
                        $lecturer->nip,
                        $lecturer->lecturer_code,
                        $lecturer->name,
                        implode('; ', $lecturer->sortedBatchNames()),
                        $lecturer->job_start_date?->toDateString(),
                        $lecturer->email_binus_edu,
                        $lecturer->email_binus_ac_id,
                        $lecturer->phone_number,
                        $lecturer->jja,
                        $lecturer->latest_education,
                        $lecturer->status,
                        $lecturer->status_reason ?? '-',
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Download one lecturer's profile as CSV: the course list for the Courses tab,
     * otherwise the lecturer's overview details.
     */
    public function exportProfile(Request $request, Lecturer $lecturer)
    {
        $courses = $request->query('tab') === 'courses';
        $filename = 'lecturer_'.$lecturer->lecturer_code.($courses ? '_courses' : '').'.csv';
        $ids = array_values(array_filter((array) $request->input('ids', [])));
        $search = $request->input('q');

        $callback = function () use ($lecturer, $courses, $ids, $search) {
            $file = fopen('php://output', 'w');

            if ($courses) {
                fputcsv($file, ['Period', 'Course ID', 'Course Name', 'Class', 'IKADQ', 'Students']);

                $this->coursesQuery($lecturer, $search)
                    ->when($ids !== [], fn ($q) => $q->whereIn('course_records.id', $ids))
                    ->chunk(200, function ($records) use ($file) {
                        foreach ($records as $record) {
                            fputcsv($file, [
                                $record->period?->period_name ?? '-',
                                $record->course?->course_code ?? '-',
                                $record->course?->course_name ?? '-',
                                $record->classGroup?->class_code ?? '-',
                                number_format((float) ($record->lecturerPerformance?->ikadq ?? 0), 2),
                                $record->student_grades_count,
                            ]);
                        }
                    });
            } else {
                $lecturer->load('batches');
                $kpi = $lecturer->performances()->avg('ikadq');

                fputcsv($file, [
                    'Lecturer Code',
                    'Name',
                    'NIP',
                    'Batches',
                    'Job Start Date',
                    'JJA',
                    'Latest Education',
                    'Status',
                    'Reason',
                    'Gender',
                    'Email (binus.edu)',
                    'Email (binus.ac.id)',
                    'Phone Number',
                    'Key Performance Index',
                ]);
                fputcsv($file, [
                    $lecturer->lecturer_code,
                    $lecturer->name,
                    $lecturer->nip,
                    implode('; ', $lecturer->sortedBatchNames()) ?: '-',
                    $lecturer->job_start_date?->toDateString() ?? '-',
                    $lecturer->jja,
                    $lecturer->latest_education,
                    $lecturer->status,
                    $lecturer->status_reason ?? '-',
                    $lecturer->gender ?? '-',
                    $lecturer->email_binus_edu,
                    $lecturer->email_binus_ac_id,
                    $lecturer->phone_number,
                    $kpi !== null ? number_format((float) $kpi, 2) : '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
