<?php

namespace App\Http\Controllers;

use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\CourseRecord;
use App\Models\Lecturer;
use App\Models\LecturerPerformance;
use App\Models\Period;
use App\Models\Student;
use App\Services\AuditLogger;
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
        $query = $this->filteredCourseRecordsQuery($request);

        $transformRecord = function ($record) {
            return (object) [
                'id' => $record->id,
                'record_code' => $record->record_code,
                'period' => $record->period?->period_name ?? '-',
                'course_id' => $record->course?->course_code ?? '-',
                'class' => $record->classGroup?->class_code ?? '-',
                'lecturer_code' => $record->lecturer?->lecturer_code ?? '-',
                'students_count' => $record->student_grades_count,
            ];
        };

        $exportCourseRecords = (clone $query)->get()->map($transformRecord);

        // Paginate & Preserve Query String
        $perPage = max((int) $request->input('per_page', 10), 1);
        $courseRecords = $query->paginate($perPage)->withQueryString();
        $courseRecords->getCollection()->transform($transformRecord);

        if ($request->wantsJson()) {
            return response()->json($courseRecords);
        }

        // Dynamic Filter Options directly from DB
        $periodOptions = Period::pluck('period_name')->unique()->sort()->values();
        $classOptions = ClassGroup::pluck('class_code')->unique()->sort()->values();
        $lecturerOptions = Lecturer::pluck('lecturer_code')->unique()->sort()->values();
        $courseOptions = Course::orderBy('course_code')->get(['course_code', 'course_name']);

        return view('course-records.index', compact(
            'courseRecords',
            'exportCourseRecords',
            'periodOptions',
            'classOptions',
            'lecturerOptions',
            'courseOptions'
        ));
    }

    private function filteredCourseRecordsQuery(Request $request)
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
                $sub->where('course_records.record_code', 'like', "%{$q}%")
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
        $selectedPeriods = $this->multiFilterValues($request, 'period');
        if ($selectedPeriods !== []) {
            $query->whereHas('period', fn ($pq) => $pq->whereIn('period_name', $selectedPeriods));
        }

        // 3. Class Group Filter
        $selectedClasses = $this->multiFilterValues($request, 'class');
        if ($selectedClasses !== []) {
            $query->whereHas('classGroup', function ($cgq) use ($selectedClasses) {
                $cgq->whereIn('class_code', $selectedClasses);
            });
        }

        // 4. Lecturer Filter
        $selectedLecturers = $this->multiFilterValues($request, 'lecturer_code');
        if ($selectedLecturers !== []) {
            $query->whereHas('lecturer', function ($lq) use ($selectedLecturers) {
                $lq->whereIn('lecturer_code', $selectedLecturers);
            });
        }

        // 5. Course Filter
        $selectedCourses = $this->multiFilterValues($request, 'course');
        if ($selectedCourses !== []) {
            $query->whereHas('course', fn ($cq) => $cq->whereIn('course_code', $selectedCourses));
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

        return $query;
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

        // Fetch all students and currently enrolled IDs for the popup modal
        $allStudents = Student::with('classGroup.batch')->orderBy('nim')->get();
        $enrolledStudentIds = $courseRecord->studentGrades()->pluck('student_id')->toArray();

        return view('course-records.show', [
            'record' => $courseRecord,
            'students' => $students,
            'allStudents' => $allStudents,
            'enrolledStudentIds' => $enrolledStudentIds,
            'courses' => Course::orderBy('course_code')->get(['id', 'course_code', 'course_name']),
            'periods' => Period::orderBy('start_date')->get(['id', 'period_name']),
            'classGroups' => ClassGroup::orderBy('class_code')->get(['id', 'class_code']),
            'lecturers' => Lecturer::orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * Update a course record's course, period, class, lecturer and IKADQ.
     */
    public function update(Request $request, CourseRecord $courseRecord, AuditLogger $auditLogger)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'period_id' => 'required|exists:periods,id',
            'class_id' => [
                'required', 'exists:class_groups,id',
                Rule::unique('course_records', 'class_id')
                    ->where('course_id', $request->input('course_id'))
                    ->where('period_id', $request->input('period_id'))
                    ->ignore($courseRecord->id),
            ],
            'lecturer_id' => 'required|exists:lecturers,id',
            'ikadq' => 'nullable|numeric|between:0,4',
        ], [
            'class_id.unique' => 'A course record for this course, period and class already exists.',
        ]);

        DB::transaction(function () use ($courseRecord, $validated, $auditLogger) {
            $fields = ['record_code', 'course_id', 'period_id', 'class_id', 'lecturer_id'];
            $oldRecord = $auditLogger->snapshot($courseRecord, $fields);
            $oldIkadq = $courseRecord->lecturerPerformance?->ikadq;

            $courseRecord->update(collect($validated)->only(['course_id', 'period_id', 'class_id', 'lecturer_id'])->all());

            $newIkadq = filled($validated['ikadq'] ?? null) ? $validated['ikadq'] : null;

            if ($newIkadq !== null) {
                LecturerPerformance::updateOrCreate(
                    ['course_record_id' => $courseRecord->id],
                    ['lecturer_id' => $validated['lecturer_id'], 'ikadq' => $newIkadq],
                );
            } else {
                LecturerPerformance::where('course_record_id', $courseRecord->id)->delete();
            }

            $courseRecord->refresh();
            $newRecord = $auditLogger->snapshot($courseRecord, $fields);

            if ($oldRecord !== $newRecord || (string) $oldIkadq !== (string) $newIkadq) {
                $auditLogger->record(
                    'UPDATE',
                    'CourseRecord',
                    $courseRecord->id,
                    ['course_record' => $oldRecord, 'ikadq' => $oldIkadq],
                    ['course_record' => $newRecord, 'ikadq' => $newIkadq]
                );
            }
        });

        return redirect()->route('course-records.show', $courseRecord->id)->with('success', 'Course record updated successfully.');
    }

    /**
     * Enroll multiple students in the course record via modal form.
     */
    public function addStudent(Request $request, CourseRecord $courseRecord, AuditLogger $auditLogger)
    {
        $validated = $request->validate([
            'students' => 'required|array',
            'students.*' => 'exists:students,id',
        ]);

        $addedCount = 0;
        DB::transaction(function () use ($validated, $courseRecord, $auditLogger, &$addedCount) {
            foreach ($validated['students'] as $studentId) {
                $grade = $courseRecord->studentGrades()->firstOrCreate(['student_id' => $studentId]);
                if ($grade->wasRecentlyCreated) {
                    $addedCount++;
                    $auditLogger->record(
                        'CREATE',
                        'StudentGrade',
                        $grade->id,
                        null,
                        $auditLogger->snapshot($grade, ['student_id', 'course_record_id', 'letter_grade', 'numeric_grade'])
                    );
                }
            }
        });

        return redirect()->route('course-records.show', $courseRecord->id)
            ->with('success', "{$addedCount} student(s) added to the course record.");
    }

    /**
     * Remove a student (and their grade) from the course record.
     */
    public function removeStudent(CourseRecord $courseRecord, Student $student, AuditLogger $auditLogger)
    {
        DB::transaction(function () use ($courseRecord, $student, $auditLogger) {
            $grades = $courseRecord->studentGrades()->where('student_id', $student->id)->get();
            foreach ($grades as $grade) {
                $auditLogger->record(
                    'DELETE',
                    'StudentGrade',
                    $grade->id,
                    $auditLogger->snapshot($grade, ['student_id', 'course_record_id', 'letter_grade', 'numeric_grade']),
                    null
                );
                $grade->delete();
            }
        });

        return redirect()->route('course-records.show', $courseRecord->id)->with('success', 'Student removed from the course record.');
    }

    /**
     * Remove multiple students from the course record via modal form.
     */
    public function removeStudents(Request $request, CourseRecord $courseRecord, AuditLogger $auditLogger)
    {
        $validated = $request->validate([
            'students' => 'required|array',
            'students.*' => 'exists:students,id',
        ]);

        $deletedCount = 0;
        DB::transaction(function () use ($validated, $courseRecord, $auditLogger, &$deletedCount) {
            $grades = $courseRecord->studentGrades()->whereIn('student_id', $validated['students'])->get();
            foreach ($grades as $grade) {
                $auditLogger->record(
                    'DELETE',
                    'StudentGrade',
                    $grade->id,
                    $auditLogger->snapshot($grade, ['student_id', 'course_record_id', 'letter_grade', 'numeric_grade']),
                    null
                );
                $grade->delete();
                $deletedCount++;
            }
        });

        return redirect()->route('course-records.show', $courseRecord->id)
            ->with('success', "{$deletedCount} student(s) removed from the course record.");
    }

    /**
     * Download one course record (details plus enrolled students) as CSV.
     */
    public function exportRecord(Request $request, CourseRecord $courseRecord)
    {
        $courseRecord->load(['course', 'lecturer', 'period', 'classGroup', 'lecturerPerformance']);
        $ids = array_values(array_filter((array) $request->input('ids', [])));
        $search = $request->input('q');

        $callback = function () use ($courseRecord, $ids, $search) {
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

            $this->studentsQuery($courseRecord, $search)
                ->when($ids !== [], fn ($q) => $q->whereIn('students.id', $ids))
                ->chunk(200, function ($students) use ($handle) {
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
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="course_record_'.$courseRecord->record_code.'.csv"',
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
    public function export(Request $request)
    {
        $filename = 'course_records_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $ids = array_values(array_filter((array) $request->input('ids', [])));
        $query = $this->filteredCourseRecordsQuery($request)
            ->when($ids !== [], fn ($q) => $q->whereIn('course_records.id', $ids));

        $callback = function () use ($query) {
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

            $query->chunk(200, function ($records) use ($handle) {
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
