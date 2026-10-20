<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\CourseRecord;
use App\Models\Lecturer;
use App\Models\LecturerPerformance;
use App\Models\Period;
use App\Models\Student;
use App\Models\StudentGrade;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InputDataController extends Controller
{
    public const TABS = ['batch', 'period', 'student', 'lecturer', 'course', 'class', 'course-record', 'grades'];

    /** Tabs with CSV import support. Batch and period are entered through their forms. */
    public const IMPORT_TABS = ['student', 'lecturer', 'course', 'class', 'course-record', 'grades'];

    public function index()
    {
        return redirect()->route('input-data.show', 'student');
    }

    public function show(string $tab)
    {
        $data = match ($tab) {
            'batch' => [
                'batchesList' => Batch::withCount('classes')
                    ->orderBy('batch_name')
                    ->get(),
            ],
            'period' => [
                'periodsList' => Period::withCount('courseRecords')
                    ->orderByDesc('start_date')
                    ->get(),
            ],
            'student' => [
                'batches' => $this->batches(),
                'classGroups' => ClassGroup::orderBy('class_code')->get(),
                'periods' => Period::orderBy('start_date')->get(['id', 'period_name']),
            ],
            'class' => ['batches' => $this->batches()],
            'course-record' => [
                'courses' => Course::orderBy('course_code')->get(),
                'periods' => Period::orderBy('start_date')->get(['id', 'period_name']),
                'classGroups' => ClassGroup::orderBy('class_code')->get(),
                'lecturers' => Lecturer::orderBy('name')->get(['id', 'name', 'lecturer_code']),
                'initialStudents' => $this->studentRows(old('students', [])),
            ],
            'grades' => [
                'records' => CourseRecord::with(['course', 'period', 'classGroup'])->orderBy('record_code')->get(),
                'initialStudents' => $this->studentRows(array_keys(old('grades', [])), old('grades', [])),
            ],
            default => [],
        };

        return view('input-data.show', ['tab' => $tab] + $data);
    }

    public function store(Request $request, string $tab, AuditLogger $auditLogger)
    {
        $message = match ($tab) {
            'batch' => $this->storeBatch($request),
            'period' => $this->storePeriod($request),
            'student' => $this->storeStudent($request),
            'lecturer' => $this->storeLecturer($request),
            'course' => $this->storeCourse($request),
            'class' => $this->storeClass($request),
            'course-record' => $this->storeCourseRecord($request),
            'grades' => $this->storeGrades($request),
        };

        return redirect()->route('input-data.show', $tab)->with('success', $message);
    }

    /**
     * JSON: students matching a name/NIM search, or every student in a class.
     */
    public function searchStudents(Request $request)
    {
        $query = Student::with('classGroup.batch')->orderBy('nim');

        // Hide students already enrolled in this course record.
        if ($request->filled('exclude_record')) {
            $query->whereNotIn('id', StudentGrade::where('course_record_id', $request->integer('exclude_record'))->select('student_id'));
        }

        if ($request->filled('class_id')) {
            $query->where('class_id', $request->integer('class_id'))->limit(300);
        } else {
            $q = trim((string) $request->input('q'));

            if ($q === '') {
                return response()->json([]);
            }

            $query->where(fn ($sub) => $sub->where('name', 'like', "%{$q}%")->orWhere('nim', 'like', "%{$q}%"))->limit(8);
        }

        return response()->json($query->get()->map(fn ($student) => $this->studentRow($student)));
    }

    /**
     * JSON: the students already enrolled in a course record, with their letter grade.
     */
    public function recordStudents(CourseRecord $courseRecord)
    {
        $grades = $courseRecord->studentGrades()->with('student.classGroup.batch')->get();

        return response()->json(
            $grades->sortBy(fn ($grade) => $grade->student->nim)
                ->map(fn ($grade) => $this->studentRow($grade->student, $grade->letter_grade))
                ->values()
        );
    }

    private function storeBatch(Request $request): string
    {
        $validated = $request->validate([
            'batch_name' => ['required', 'string', 'max:255', 'unique:batches,batch_name'],
        ], [
            'batch_name.unique' => 'That batch already exists.',
        ]);

        Batch::create($validated);

        return 'Batch added successfully.';
    }

    private function storePeriod(Request $request): string
    {
        $validated = $request->validate([
            'period_name' => ['required', 'string', 'max:255', 'unique:periods,period_name'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ], [
            'period_name.unique' => 'That period name already exists.',
            'end_date.after_or_equal' => 'The end date must be the same as or later than the start date.',
        ]);

        Period::create($validated);

        return 'Period added successfully.';
    }

    private function storeStudent(Request $request, AuditLogger $auditLogger): string
    {
        $validated = $request->validate([
            'nim'                   => 'required|string|max:255|unique:students,nim',
            'name'                  => 'required|string|max:255',
            'class_id'              => 'required|exists:class_groups,id',
            'status'                => 'required|in:Active,Graduated,On Leave',
            'gender'                => 'nullable|in:Male,Female',
            'email'                 => 'nullable|email|max:255',
            'phone_number'          => 'nullable|string|max:30',
            'date_of_birth'         => 'nullable|date|before_or_equal:today',
            'semesters'             => 'nullable|array',
            'semesters.*.period_id' => 'required|distinct|exists:periods,id',
            'semesters.*.gpa'       => 'required|numeric|between:0,4',
        ], [
            'semesters.*.period_id.required' => 'Select a period for every semester GPA row.',
            'semesters.*.period_id.distinct' => 'Each semester can only be listed once.',
            'semesters.*.gpa.required'       => 'Enter a GPA for every semester row.',
            'semesters.*.gpa.between'        => 'Semester GPA must be between 0.00 and 4.00.',
        ]);

        $semesters = collect($validated['semesters'] ?? []);
        unset($validated['semesters']);
        $validated['cumulative_gpa'] = $semesters->isEmpty() ? 0 : round($semesters->avg('gpa'), 2);

        DB::transaction(function () use ($validated, $semesters, $auditLogger) {
            $student = Student::create($validated);
            $auditLogger->record('CREATE', 'Student', $student->id, null, $auditLogger->snapshot($student, ['nim', 'name', 'class_id', 'status', 'gender', 'email', 'phone_number', 'date_of_birth', 'cumulative_gpa']));

            $student->semesterGpas()->createMany(
                $semesters->map(fn ($row) => ['period_id' => $row['period_id'], 'gpa' => $row['gpa']])->all()
            );
        });

        return 'Student added successfully.';
    }

    private function storeLecturer(Request $request): string
    {
        $validated = $request->validate([
            'lecturer_code'     => 'required|string|max:20|unique:lecturers,lecturer_code',
            'name'              => 'required|string|max:255',
            'jja'               => 'nullable|string|max:100',
            'latest_education'  => 'nullable|string|max:50',
            'status'            => 'required|in:Active,Inactive',
            'gender'            => 'nullable|in:Male,Female',
            'email_binus_edu'   => 'nullable|email|max:255|unique:lecturers,email_binus_edu',
            'email_binus_ac_id' => 'nullable|email|max:255|unique:lecturers,email_binus_ac_id',
            'phone_number'      => 'nullable|string|max:30',
        ]);

        Lecturer::create($validated);

        return 'Lecturer added successfully.';
    }

    private function storeCourse(Request $request): string
    {
        $validated = $request->validate([
            'course_code' => 'required|string|max:20|unique:courses,course_code',
            'course_name' => 'required|string|max:255',
            'sks'         => ['required', 'regex:/^\d+(\/\d+)?$/'],
        ], [
            'sks.regex' => 'SCU must be a number or two numbers separated by a slash, e.g. 2/4.',
        ]);

        Course::create($validated);

        return 'Course added successfully.';
    }

    private function storeClass(Request $request): string
    {
        $validated = $request->validate([
            'batch_id'   => 'required|exists:batches,id',
            'class_code' => [
                'required', 'string', 'max:20',
                Rule::unique('class_groups', 'class_code')->where('batch_id', $request->input('batch_id')),
            ],
        ], [
            'class_code.unique' => 'This class already exists in the selected batch.',
        ]);

        ClassGroup::create($validated);

        return 'Class added successfully.';
    }

    private function storeCourseRecord(Request $request): string
    {
        $validated = $request->validate([
            'course_id'   => 'required|exists:courses,id',
            'period_id'   => 'required|exists:periods,id',
            'class_id'    => [
                'required', 'exists:class_groups,id',
                Rule::unique('course_records', 'class_id')
                    ->where('course_id', $request->input('course_id'))
                    ->where('period_id', $request->input('period_id')),
            ],
            'lecturer_id' => 'required|exists:lecturers,id',
            'ikadq'       => 'nullable|numeric|between:0,4',
            'students'    => 'nullable|array',
            'students.*'  => 'exists:students,id|distinct',
        ], [
            'class_id.unique' => 'A course record for this course, period and class already exists.',
        ]);

        DB::transaction(function () use ($validated) {
            $record = CourseRecord::create([
                'course_id'   => $validated['course_id'],
                'period_id'   => $validated['period_id'],
                'class_id'    => $validated['class_id'],
                'lecturer_id' => $validated['lecturer_id'],
                'record_code' => CourseRecord::nextCodeFor(Period::find($validated['period_id'])),
            ]);

            if (filled($validated['ikadq'] ?? null)) {
                LecturerPerformance::create([
                    'lecturer_id'      => $validated['lecturer_id'],
                    'course_record_id' => $record->id,
                    'ikadq'            => $validated['ikadq'],
                ]);
            }

            foreach ($validated['students'] ?? [] as $studentId) {
                StudentGrade::create(['student_id' => $studentId, 'course_record_id' => $record->id]);
            }
        });

        return 'Course record added successfully.';
    }

    private function storeGrades(Request $request): string
    {
        $validated = $request->validate([
            'course_record_id' => 'required|exists:course_records,id',
            'grades'           => 'required|array|min:1',
            'grades.*'         => ['nullable', Rule::in(array_keys(StudentGrade::GRADE_POINTS))],
        ], [
            'grades.required' => 'Add at least one student.',
            'grades.min'      => 'Add at least one student.',
            'grades.*.in'     => 'Pick a valid letter grade for every student.',
        ]);

        $studentIds = array_keys($validated['grades']);

        if (Student::whereIn('id', $studentIds)->count() !== count($studentIds)) {
            abort(422, 'Unknown student in the list.');
        }

        DB::transaction(function () use ($validated, $studentIds, $auditLogger) {
            $recordId = $validated['course_record_id'];

            $removedGrades = StudentGrade::where('course_record_id', $recordId)->whereNotIn('student_id', $studentIds)->get();
            foreach ($removedGrades as $removed) {
                $auditLogger->record('DELETE', 'StudentGrade', $removed->id, $auditLogger->snapshot($removed, ['student_id', 'course_record_id', 'letter_grade', 'numeric_grade']), null);
                $removed->delete();
            }

            foreach ($validated['grades'] as $studentId => $letter) {
                $grade = StudentGrade::firstOrNew(['student_id' => $studentId, 'course_record_id' => $recordId]);
                $wasExisting = $grade->exists;
                $old = $wasExisting ? $auditLogger->snapshot($grade, ['student_id', 'course_record_id', 'letter_grade', 'numeric_grade']) : null;
                $grade->fill(['letter_grade' => $letter, 'numeric_grade' => $letter ? StudentGrade::GRADE_POINTS[$letter] : null]);
                if (! $wasExisting || $grade->isDirty()) {
                    $grade->save();
                    $auditLogger->record($wasExisting ? 'UPDATE' : 'CREATE', 'StudentGrade', $grade->id, $old, $auditLogger->snapshot($grade, ['student_id', 'course_record_id', 'letter_grade', 'numeric_grade']));
                }
            }
        });

        return 'Grades saved successfully.';
    }

    private function batches()
    {
        return Batch::all()
            ->sortBy(fn ($batch) => (int) preg_replace('/\D/', '', $batch->batch_name), SORT_NUMERIC)
            ->values();
    }

    /**
     * Rows for the student picker when a form is re-shown after a validation error.
     */
    private function studentRows(array $ids, array $grades = []): array
    {
        if ($ids === []) {
            return [];
        }

        return Student::with('classGroup.batch')
            ->whereIn('id', $ids)
            ->orderBy('nim')
            ->get()
            ->map(fn ($student) => $this->studentRow($student, $grades[$student->id] ?? null))
            ->all();
    }

    private function studentRow(Student $student, ?string $grade = null): array
    {
        return [
            'id'    => $student->id,
            'nim'   => $student->nim,
            'name'  => $student->name,
            'batch' => $student->classGroup?->batch?->batch_name ?? '-',
            'class' => $student->classGroup?->class_code ?? '-',
            'grade' => $grade,
        ];
    }
}
