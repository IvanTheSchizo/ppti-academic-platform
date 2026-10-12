<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\ClassGroup;
use App\Models\Period;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StudentController extends Controller
{
    /**
     * Display a paginated listing of students (View or JSON).
     */
    public function index(Request $request)
    {
            // 1. Calculate Stat Bar Metrics
        $stats = [
            'total'     => Student::count(),
            'active'    => Student::where('status', 'Active')->count(),
            'graduated' => Student::where('status', 'Graduated')->count(),
            'on_leave'  => Student::where('status', 'On Leave')->count(),
        ];

        // 2. Base Query with Relationships Eager Loaded
        $query = Student::with(['classGroup.batch']);

        // 3. Search (Name or NIM)
        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('name', 'like', "%{$q}%")
                    ->orWhere('nim', 'like', "%{$q}%");
            });
        }

        // 4. Batch Filter
        if ($request->filled('batch')) {
            $batchName = $request->input('batch');
            $query->whereHas('classGroup.batch', function ($q) use ($batchName) {
                $q->where('batch_name', $batchName);
            });
        }

        // 5. Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // 6. Class Code Filter
        if ($request->filled('class')) {
            $classCode = $request->input('class');
            $query->whereHas('classGroup', function ($q) use ($classCode) {
                $q->where('class_code', $classCode);
            });
        }

        // 7. GPA Range Filters
        if ($request->filled('gpa_min') && is_numeric($request->input('gpa_min'))) {
            $query->where('cumulative_gpa', '>=', (float) $request->input('gpa_min'));
        }
        if ($request->filled('gpa_max') && is_numeric($request->input('gpa_max'))) {
            $query->where('cumulative_gpa', '<=', (float) $request->input('gpa_max'));
        }

// 8. Sorting
        $sortColumn = $request->input('sort', 'name');
        $direction = strtolower($request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if ($sortColumn === 'batch') {
            // Join class_groups and batches so we can sort students by batch number
            $query->join('class_groups', 'students.class_id', '=', 'class_groups.id')
                  ->join('batches', 'class_groups.batch_id', '=', 'batches.id')
                  ->select('students.*')
                  ->orderBy('batches.id', $direction);
        } elseif ($sortColumn === 'class') {
            $query->join('class_groups', 'students.class_id', '=', 'class_groups.id')
                  ->select('students.*')
                  ->orderBy('class_groups.class_code', $direction);
        } elseif ($sortColumn === 'gpa') {
            $query->orderBy('cumulative_gpa', $direction);
        } elseif (in_array($sortColumn, ['nim', 'name', 'status'])) {
            $query->orderBy($sortColumn, $direction);
        } else {
            $query->latest();
        }

        // 9. Paginate and Retain Active Query String
        $perPage = max((int) $request->input('per_page', 10), 1);
        $students = $query->paginate($perPage)->withQueryString();

        // Respond with JSON if requested via API
        if ($request->wantsJson()) {
            return response()->json($students);
        }

        // Dynamic options for the filter dropdowns
        $batches = Batch::all()->sortBy(function ($batch) {
            return (int) preg_replace('/\D/', '', $batch->batch_name);
        }, SORT_NUMERIC)->values();

        $classes = ClassGroup::pluck('class_code')->unique()->sort()->values();
        return view('students.index', compact('students', 'stats', 'batches', 'classes'));
    }

    public function export()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="students_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Batch', 'Student ID', 'Name', 'Class', 'Status', 'GPA']);

            Student::with(['classGroup.batch'])->chunk(200, function ($students) use ($file) {
                foreach ($students as $student) {
                    fputcsv($file, [
                        $student->classGroup?->batch?->batch_name ?? '-',
                        $student->nim,
                        $student->name,
                        $student->classGroup?->class_code ?? '-',
                        $student->status,
                        $student->cumulative_gpa,
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function show(Request $request, Student $student)
    {
        $student->load('classGroup.batch');

        if ($request->wantsJson()) {
            return response()->json($student);
        }

        $activeTab = $request->query('tab') === 'grades' ? 'grades' : 'overview';
        $semesterGpa = collect();
        $grades = null;

        if ($activeTab === 'grades') {
            $query = $this->gradesQuery($student);

            if ($request->filled('q')) {
                $q = $request->input('q');
                $query->where(function ($sub) use ($q) {
                    $sub->whereHas('courseRecord.course', function ($cq) use ($q) {
                        $cq->where('course_name', 'like', "%{$q}%")
                            ->orWhere('course_code', 'like', "%{$q}%");
                    })->orWhereHas('courseRecord.lecturer', function ($lq) use ($q) {
                        $lq->where('name', 'like', "%{$q}%");
                    });
                });
            }

            $perPage = max((int) $request->input('per_page', 10), 1);
            $grades = $query->paginate($perPage)->withQueryString();

            $grades->getCollection()->transform(function ($grade) {
                $record = $grade->courseRecord;

                return (object) [
                    'period'      => $record->period?->period_name ?? '-',
                    'course_id'   => $record->course?->course_code ?? '-',
                    'course_name' => $record->course?->course_name ?? '-',
                    'lecturer'    => $record->lecturer,
                    'sks'         => $record->course?->sks ?? '-',
                    'grade'       => $grade->letter_grade,
                ];
            });
        } else {
            // Average grade per period, oldest first, for the Grade Point chart
            $semesterGpa = $this->semesterAverages($student);
        }

        $classGroups = ClassGroup::with('batch')->get()
            ->sortBy(fn ($classGroup) => ($classGroup->batch?->batch_name ?? '') . '-' . $classGroup->class_code)
            ->values();

        $periods = Period::orderBy('start_date')->get(['id', 'period_name']);

        return view('students.profile', compact('student', 'activeTab', 'grades', 'semesterGpa', 'classGroups', 'periods'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'nim'                    => 'required|string|max:255|unique:students,nim,' . $student->id,
            'name'                   => 'required|string|max:255',
            'class_id'               => 'required|exists:class_groups,id',
            'status'                 => 'required|in:Active,Graduated,On Leave',
            'gender'                 => 'nullable|in:Male,Female',
            'email'                  => 'nullable|email|max:255',
            'phone_number'           => 'nullable|string|max:30',
            'date_of_birth'          => 'nullable|date|before_or_equal:today',
            'semesters'              => 'nullable|array',
            'semesters.*.period_id'  => 'required|distinct|exists:periods,id',
            'semesters.*.gpa'        => 'required|numeric|between:0,4',
        ], [
            'semesters.*.period_id.required' => 'Select a period for every semester GPA row.',
            'semesters.*.period_id.distinct' => 'Each semester can only be listed once.',
            'semesters.*.gpa.required'       => 'Enter a GPA for every semester row.',
            'semesters.*.gpa.between'        => 'Semester GPA must be between 0.00 and 4.00.',
        ]);

        $semesters = collect($validated['semesters'] ?? []);
        unset($validated['semesters']);

        // Cumulative GPA is calculated from the semester GPAs.
        $validated['cumulative_gpa'] = $semesters->isEmpty() ? 0 : round($semesters->avg('gpa'), 2);

        DB::transaction(function () use ($student, $validated, $semesters) {
            $student->update($validated);

            $student->semesterGpas()->delete();
            $student->semesterGpas()->createMany(
                $semesters->map(fn ($row) => ['period_id' => $row['period_id'], 'gpa' => $row['gpa']])->all()
            );
        });

        return redirect()->route('students.profile', $student->id)->with('success', 'Student updated successfully.');
    }
    /**
     * Download one student's profile as CSV: the grade list for the Grades tab,
     * otherwise the student's details and average grade per period.
     */
    public function exportProfile(Request $request, Student $student)
    {
        $grades = $request->query('tab') === 'grades';
        $filename = 'student_' . $student->nim . ($grades ? '_grades' : '') . '.csv';

        $callback = function () use ($student, $grades) {
            $file = fopen('php://output', 'w');

            if ($grades) {
                fputcsv($file, ['Period', 'Course ID', 'Course', 'Lecturer', 'SCU', 'Grade']);

                $this->gradesQuery($student)->chunk(200, function ($rows) use ($file) {
                    foreach ($rows as $row) {
                        $record = $row->courseRecord;

                        fputcsv($file, [
                            $record->period?->period_name,
                            $record->course?->course_code,
                            $record->course?->course_name,
                            $record->lecturer?->name,
                            $record->course?->sks,
                            $row->letter_grade,
                        ]);
                    }
                });
            } else {
                $student->load('classGroup.batch');

                fputcsv($file, ['Student ID', 'Name', 'Batch', 'Class', 'Status', 'Cumulative GPA']);
                fputcsv($file, [
                    $student->nim,
                    $student->name,
                    $student->classGroup?->batch?->batch_name,
                    $student->classGroup?->class_code,
                    $student->status,
                    $student->cumulative_gpa,
                ]);
                fputcsv($file, []);
                fputcsv($file, ['Period', 'Semester GPA']);

                foreach ($this->semesterAverages($student) as $row) {
                    fputcsv($file, [$row['label'], number_format($row['value'], 2)]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function gradesQuery(Student $student)
    {
        return $student->grades()
            ->with(['courseRecord.course', 'courseRecord.lecturer', 'courseRecord.period'])
            ->join('course_records', 'student_grades.course_record_id', '=', 'course_records.id')
            ->join('periods', 'course_records.period_id', '=', 'periods.id')
            ->select('student_grades.*')
            ->orderByDesc('periods.start_date');
    }

    /**
     * The student's stored GPA per period, oldest first.
     */
    private function semesterAverages(Student $student)
    {
        return $student->semesterGpas()
            ->join('periods', 'student_semester_gpas.period_id', '=', 'periods.id')
            ->orderBy('periods.start_date')
            ->get(['periods.period_name as label', 'student_semester_gpas.gpa as value'])
            ->map(fn ($row) => ['label' => $row->label, 'value' => (float) $row->value]);
    }
    public function destroy(Student $student)
    {
        $student->delete();
        return response()->json(null, 204);
    }
}