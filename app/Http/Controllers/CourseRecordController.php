<?php

namespace App\Http\Controllers;

use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\CourseRecord;
use App\Models\Lecturer;
use App\Models\LecturerPerformance;
use App\Models\Period;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CourseRecordController extends Controller
{
    /**
     * Display a listing of course records with search, filter, and sort.
     */
    public function index(Request $request)
    {
       $query = CourseRecord::query()
            ->select('course_records.*')
            ->withCount('studentGrades')
            ->with([
                'course',
                'lecturer',
                'period',
                'classGroup',
            ]);

        // 1. Search Query (Record Code, Course, Lecturer, or Class)
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('record_code', 'like', "%{$q}%")
                    ->orWhereHas('course', function ($cq) use ($q) {
                        $cq->where('course_code', 'like', "%{$q}%")
                           ->orWhere('course_name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('lecturer', function ($lq) use ($q) {
                        $lq->where('lecturer_code', 'like', "%{$q}%")
                           ->orWhere('name', 'like', "%{$q}%");
                    })
                    ->orWhereHas('classGroup', function ($cgq) use ($q) {
                        $cgq->where('class_code', 'like', "%{$q}%");
                    });
            });
        }

        // 2. Period Filter
        if ($request->filled('period')) {
            $periodVal = $request->input('period');
            $query->whereHas('period', fn ($pq) => $pq->where('period_name', $periodVal));
        }

        // 3. Class Group Filter
        if ($request->filled('class')) {
            $classVal = $request->input('class');
            $query->whereHas('classGroup', function ($cgq) use ($classVal) {
                $cgq->where('class_code', $classVal);
            });
        }

        // 4. Lecturer Filter
        if ($request->filled('lecturer_code')) {
            $lecturerCode = $request->input('lecturer_code');
            $query->whereHas('lecturer', function ($lq) use ($lecturerCode) {
                $lq->where('lecturer_code', $lecturerCode);
            });
        }

        // 5. Course Filter
        if ($request->filled('course')) {
            $courseCode = $request->input('course');
            $query->whereHas('course', fn ($cq) => $cq->where('course_code', $courseCode));
        }
        // 6. Dynamic Sorting
        $sort = $request->input('sort', 'record_code');
        $direction = strtolower($request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sort === 'period') {
            $query->join('periods', 'course_records.period_id', '=', 'periods.id')
                  ->orderBy('periods.period_name', $direction);
        } elseif ($sort === 'course_id') {
            $query->join('courses', 'course_records.course_id', '=', 'courses.id')
                  ->orderBy('courses.course_code', $direction);
        } elseif ($sort === 'class') {
            $query->join('class_groups', 'course_records.class_id', '=', 'class_groups.id')
                  ->orderBy('class_groups.class_code', $direction);
        } elseif ($sort === 'lecturer_code') {
            $query->join('lecturers', 'course_records.lecturer_id', '=', 'lecturers.id')
                  ->orderBy('lecturers.lecturer_code', $direction);
        } elseif ($sort === 'students') {
            $query->orderBy('student_grades_count', $direction);
        } else {
            $query->orderBy('course_records.record_code', $direction);
        }

        // 7. Paginate & Preserve Query String
        $perPage = max((int) $request->input('per_page', 10), 1);
        $courseRecords = $query->paginate($perPage)->withQueryString();

        // Transform for presentation
        $courseRecords->getCollection()->transform(function ($record) {
            return (object) [
                'id'             => $record->id,
                'record_code'    => $record->record_code,
                'period'         => $record->period?->period_name ?? '-',
                'course_id'      => $record->course?->course_code ?? '-',
                'class'          => $record->classGroup?->class_code ?? '-',
                'lecturer_code'  => $record->lecturer?->lecturer_code ?? '-',
                'students_count' => $record->student_grades_count,
            ];
        });

        if ($request->wantsJson()) {
            return response()->json($courseRecords);
        }

        // 8. Dynamic Filter Options directly from DB
        $periodOptions = Period::pluck('period_name')->unique()->sort()->values();

        $classOptions = ClassGroup::pluck('class_code')->unique()->sort()->values();
        $lecturerOptions = Lecturer::pluck('lecturer_code')->unique()->sort()->values();
        $courseOptions = Course::orderBy('course_code')->get(['course_code', 'course_name']);

        return view('course-records.index', compact(
            'courseRecords',
            'periodOptions',
            'classOptions',
            'lecturerOptions',
            'courseOptions'
        ));
    }

    /**
     * Display one course record with its details and enrolled students.
     */
    public function show(Request $request, CourseRecord $courseRecord)
    {
        $courseRecord->load(['course', 'lecturer', 'period', 'classGroup', 'lecturerPerformance']);

        if ($request->wantsJson()) {
            return response()->json($courseRecord);
        }

        $students = $this->studentsQuery($courseRecord, $request->input('q'))
            ->paginate(max((int) $request->input('per_page', 10), 1))
            ->withQueryString();

        return view('course-records.show', [
            'record' => $courseRecord,
            'students' => $students,
            'courses' => Course::orderBy('course_code')->get(['id', 'course_code', 'course_name']),
            'periods' => Period::orderBy('start_date')->get(['id', 'period_name']),
            'classGroups' => ClassGroup::orderBy('class_code')->get(['id', 'class_code']),
            'lecturers' => Lecturer::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update a course record's course, period, class, lecturer and IKADQ.
     */
    public function update(Request $request, CourseRecord $courseRecord)
    {
        $validated = $request->validate([
            'course_id'   => 'required|exists:courses,id',
            'period_id'   => 'required|exists:periods,id',
            'class_id'    => [
                'required', 'exists:class_groups,id',
                Rule::unique('course_records', 'class_id')
                    ->where('course_id', $request->input('course_id'))
                    ->where('period_id', $request->input('period_id'))
                    ->ignore($courseRecord->id),
            ],
            'lecturer_id' => 'required|exists:lecturers,id',
            'ikadq'       => 'nullable|numeric|between:0,4',
        ], [
            'class_id.unique' => 'A course record for this course, period and class already exists.',
        ]);

        DB::transaction(function () use ($courseRecord, $validated) {
            $courseRecord->update(collect($validated)->only(['course_id', 'period_id', 'class_id', 'lecturer_id'])->all());

            if (filled($validated['ikadq'] ?? null)) {
                LecturerPerformance::updateOrCreate(
                    ['course_record_id' => $courseRecord->id],
                    ['lecturer_id' => $validated['lecturer_id'], 'ikadq' => $validated['ikadq']],
                );
            } else {
                LecturerPerformance::where('course_record_id', $courseRecord->id)->delete();
            }
        });

        return redirect()->route('course-records.show', $courseRecord->id)->with('success', 'Course record updated successfully.');
    }

    /**
     * Enrol a student in the course record (no grade yet).
     */
    public function addStudent(Request $request, CourseRecord $courseRecord)
    {
        $validated = $request->validate(['student_id' => 'required|exists:students,id']);

        $grade = $courseRecord->studentGrades()->firstOrCreate(['student_id' => $validated['student_id']]);
        $message = $grade->wasRecentlyCreated ? 'Student added to the course record.' : 'That student is already in this course record.';

        session()->flash('success', $message);

        return response()->json(['message' => $message]);
    }

    /**
     * Remove a student (and their grade) from the course record.
     */
    public function removeStudent(CourseRecord $courseRecord, Student $student)
    {
        $courseRecord->studentGrades()->where('student_id', $student->id)->delete();

        return redirect()->route('course-records.show', $courseRecord->id)->with('success', 'Student removed from the course record.');
    }
    /**
     * Download one course record (details plus enrolled students) as CSV.
     */
    public function exportRecord(CourseRecord $courseRecord)
    {
        $courseRecord->load(['course', 'lecturer', 'period', 'classGroup', 'lecturerPerformance']);

        $callback = function () use ($courseRecord) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Record Code', 'Course Code', 'Course Name', 'Period', 'Class', 'Lecturer', 'IKADQ']);
            fputcsv($handle, [
                $courseRecord->record_code,
                $courseRecord->course?->course_code,
                $courseRecord->course?->course_name,
                $courseRecord->period?->period_name,
                $courseRecord->classGroup?->class_code,
                $courseRecord->lecturer?->name,
                $courseRecord->lecturerPerformance?->ikadq,
            ]);
            fputcsv($handle, []);
            fputcsv($handle, ['NIM', 'Name', 'Batch', 'Class']);

            $this->studentsQuery($courseRecord)->chunk(200, function ($students) use ($handle) {
                foreach ($students as $student) {
                    fputcsv($handle, [
                        $student->nim,
                        $student->name,
                        $student->classGroup?->batch?->batch_name,
                        $student->classGroup?->class_code,
                    ]);
                }
            });

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="course_record_' . $courseRecord->record_code . '.csv"',
        ]);
    }

    private function studentsQuery(CourseRecord $courseRecord, ?string $search = null)
    {
        return Student::query()
            ->with('classGroup.batch')
            ->whereIn('id', $courseRecord->studentGrades()->select('student_id'))
            ->when($search, fn ($query, $q) => $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")->orWhere('nim', 'like', "%{$q}%");
            }))
            ->orderBy('nim');
    }

    /**
     * Export course records to CSV.
     */
    public function export()
    {
        $filename = 'course_records_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Record Code',
                'Period',
                'Course Code',
                'Course Name',
                'Class',
                'Lecturer Code',
                'Lecturer Name',
            ]);

            CourseRecord::with(['course', 'lecturer', 'period', 'classGroup'])
                ->chunk(200, function ($records) use ($handle) {
                    foreach ($records as $record) {
                        fputcsv($handle, [
                            $record->record_code,
                            $record->period?->period_name ?? '-',
                            $record->course?->course_code ?? '-',
                            $record->course?->course_name ?? '-',
                            $record->classGroup?->class_code ?? '-',
                            $record->lecturer?->lecturer_code ?? '-',
                            $record->lecturer?->name ?? '-',
                        ]);
                    }
                });

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}