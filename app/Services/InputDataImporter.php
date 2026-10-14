<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\CourseRecord;
use App\Models\Lecturer;
use App\Models\LecturerPerformance;
use App\Models\Period;
use App\Models\Student;
use App\Models\StudentGrade;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

/**
 * Reads a CSV for one Input Data tab, validates each row and creates the records.
 * A header row is required; header names are matched case-insensitively and spaces count as underscores.
 */
class InputDataImporter
{
    public const MAX_ROWS = 5000;

    /** tab => column => required? */
    public const COLUMNS = [
        'student' => ['nim' => true, 'name' => true, 'class_code' => true, 'batch' => true, 'status' => false, 'gender' => false, 'email' => false, 'phone_number' => false, 'date_of_birth' => false],
        'lecturer' => ['lecturer_code' => true, 'name' => true, 'jja' => false, 'latest_education' => false, 'status' => false, 'gender' => false, 'email_binus_edu' => false, 'email_binus_ac_id' => false, 'phone_number' => false],
        'course' => ['course_code' => true, 'course_name' => true, 'sks' => true],
        'class' => ['batch' => true, 'class_code' => true],
        'course-record' => ['course_code' => true, 'period' => true, 'class_code' => true, 'lecturer_code' => true, 'ikadq' => false, 'students' => false],
        'grades' => ['record_code' => true, 'nim' => true, 'grade' => false],
    ];

    /** The column that identifies a row, used to spot duplicates inside one file. */
    private const KEY = [
        'student' => ['nim'],
        'lecturer' => ['lecturer_code'],
        'course' => ['course_code'],
        'class' => ['batch', 'class_code'],
        'course-record' => ['course_code', 'period', 'class_code'],
        'grades' => ['record_code', 'nim'],
    ];

    public function columns(string $tab): array
    {
        return array_keys(self::COLUMNS[$tab]);
    }

    /**
     * @return array{rows: list<array<string, string>>, errors: list<string>}  rows are keyed by column and carry a "_line" number
     */
    public function parse(string $path, string $tab): array
    {
        $handle = fopen($path, 'r');

        if (! $handle) {
            return ['rows' => [], 'errors' => ['The file could not be read.']];
        }

        $firstLine = (string) fgets($handle);
        rewind($handle);
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $header = fgetcsv($handle, null, $delimiter, '"', '');
        $header = array_map(
            fn ($name) => str_replace([' ', '-'], '_', strtolower(trim(preg_replace('/^\xEF\xBB\xBF/', '', (string) $name)))),
            $header ?: []
        );

        $missing = array_diff(array_keys(array_filter(self::COLUMNS[$tab])), $header);

        if ($missing !== []) {
            fclose($handle);

            return ['rows' => [], 'errors' => ['Missing required column(s): ' . implode(', ', $missing) . '.']];
        }

        $rows = [];
        $errors = [];
        $line = 1;

        while (($values = fgetcsv($handle, null, $delimiter, '"', '')) !== false) {
            $line++;

            if ($values === [null] || count(array_filter($values, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            if (count($rows) >= self::MAX_ROWS) {
                $errors[] = 'Only the first ' . self::MAX_ROWS . ' rows are read.';
                break;
            }

            $row = ['_line' => $line];

            foreach (array_keys(self::COLUMNS[$tab]) as $column) {
                $index = array_search($column, $header, true);
                $row[$column] = $index === false ? '' : trim((string) ($values[$index] ?? ''));
            }

            $rows[] = $row;
        }

        fclose($handle);

        return ['rows' => $rows, 'errors' => $errors];
    }

    /**
     * Validate one row. $seen tracks the keys already used in this file.
     *
     * @return array{0: array|null, 1: list<string>}  the data to create (null when invalid) and the error messages
     */
    public function process(string $tab, array $row, array &$seen): array
    {
        $key = $tab . '|' . strtolower(implode('|', array_map(fn ($c) => $row[$c], self::KEY[$tab])));

        if (isset($seen[$key])) {
            return [null, ['Duplicate of row ' . $seen[$key] . ' in this file.']];
        }

        $seen[$key] = $row['_line'];

        [$data, $errors] = match ($tab) {
            'student' => $this->student($row),
            'lecturer' => $this->lecturer($row),
            'course' => $this->course($row),
            'class' => $this->classGroup($row),
            'course-record' => $this->courseRecord($row),
            'grades' => $this->grade($row),
        };

        return [$errors === [] ? $data : null, $errors];
    }

    public function create(string $tab, array $data): void
    {
        match ($tab) {
            'student' => $this->createStudent($data),
            'lecturer' => Lecturer::create($data),
            'course' => Course::create($data),
            'class' => $this->createClassGroup($data),
            'course-record' => $this->createCourseRecord($data),
            'grades' => $this->createGrade($data),
        };
    }

    private function student(array $row): array
    {
        $errors = [];
        $notices = [];
        $classId = $this->studentClassTarget($row['class_code'], $row['batch'], $errors, $notices);
        $dob = $this->date($row['date_of_birth'], $errors);

        $dependencyValidator = Validator::make([
            'batch' => $row['batch'],
            'class_code' => $row['class_code'],
        ], [
            'batch' => 'required|string|max:255',
            'class_code' => 'required|string|max:20',
        ]);
        $errors = array_merge($errors, $dependencyValidator->errors()->all());

        $validator = Validator::make([
            'nim' => $row['nim'], 'name' => $row['name'], 'status' => $row['status'] ?: 'Active',
            'gender' => $this->nullable($row['gender']), 'email' => $this->nullable($row['email']), 'phone_number' => $this->nullable($row['phone_number']),
        ], [
            'nim' => 'required|string|max:255|unique:students,nim',
            'name' => 'required|string|max:255',
            'status' => 'in:Active,Graduated,On Leave',
            'gender' => 'nullable|in:Male,Female',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:30',
        ]);

        $errors = array_merge($errors, $validator->errors()->all());

        if ($errors !== []) {
            return [null, $errors];
        }

        $data = $validator->validated() + ['class_id' => $classId, 'date_of_birth' => $dob];

        // If the class does not exist yet, keep its natural keys in the preview payload.
        // They are resolved to actual records only when the user confirms the import.
        if ($classId === null) {
            $data['_batch_name'] = $row['batch'];
            $data['_class_code'] = $row['class_code'];
        }

        if ($notices !== []) {
            $data['_notices'] = $notices;
        }

        return [$data, []];
    }

    private function lecturer(array $row): array
    {
        $input = array_map(fn ($v) => $this->nullable($v), array_diff_key($row, ['_line' => 1]));
        $input['status'] = $row['status'] ?: 'Active';

        $validator = Validator::make($input, [
            'lecturer_code' => 'required|string|max:20|unique:lecturers,lecturer_code',
            'name' => 'required|string|max:255',
            'jja' => 'nullable|string|max:100',
            'latest_education' => 'nullable|string|max:50',
            'status' => 'in:Active,Inactive',
            'gender' => 'nullable|in:Male,Female',
            'email_binus_edu' => 'nullable|email|max:255|unique:lecturers,email_binus_edu',
            'email_binus_ac_id' => 'nullable|email|max:255|unique:lecturers,email_binus_ac_id',
            'phone_number' => 'nullable|string|max:30',
        ]);

        return [$validator->fails() ? null : $validator->validated(), $validator->errors()->all()];
    }

    private function course(array $row): array
    {
        $validator = Validator::make(['course_code' => $row['course_code'], 'course_name' => $row['course_name'], 'sks' => $row['sks']], [
            'course_code' => 'required|string|max:20|unique:courses,course_code',
            'course_name' => 'required|string|max:255',
            'sks' => ['required', 'regex:/^\d+(\/\d+)?$/'],
        ], ['sks.regex' => 'SCU must look like 2 or 2/4.']);

        return [$validator->fails() ? null : $validator->validated(), $validator->errors()->all()];
    }

    private function classGroup(array $row): array
    {
        $validator = Validator::make([
            'batch' => $row['batch'],
            'class_code' => $row['class_code'],
        ], [
            'batch' => 'required|string|max:255',
            'class_code' => 'required|string|max:20',
        ]);

        if ($validator->fails()) {
            return [null, $validator->errors()->all()];
        }

        $batch = Batch::where('batch_name', $row['batch'])->first();

        if ($batch) {
            $exists = ClassGroup::where('batch_id', $batch->id)
                ->where('class_code', $row['class_code'])
                ->exists();

            if ($exists) {
                return [null, ['This class already exists in that batch.']];
            }

            return [[
                'batch_id' => $batch->id,
                'class_code' => $row['class_code'],
            ], []];
        }

        return [[
            '_batch_name' => $row['batch'],
            'class_code' => $row['class_code'],
            '_notices' => ["Batch \"{$row['batch']}\" will be created automatically."],
        ], []];
    }

    /** Resolve an existing student class, or mark the requested batch/class for automatic creation. */
    private function studentClassTarget(string $classCode, string $batchName, array &$errors, array &$notices): ?int
    {
        if ($classCode === '' || $batchName === '') {
            $errors[] = 'Both batch and class_code are required for student CSV import.';

            return null;
        }

        $batch = Batch::where('batch_name', $batchName)->first();

        if (! $batch) {
            $notices[] = "Batch \"{$batchName}\" will be created automatically.";
            $notices[] = "Class \"{$classCode}\" will be created under batch \"{$batchName}\".";

            return null;
        }

        $class = ClassGroup::where('batch_id', $batch->id)
            ->where('class_code', $classCode)
            ->first();

        if ($class) {
            return $class->id;
        }

        $notices[] = "Class \"{$classCode}\" will be created under batch \"{$batchName}\".";

        return null;
    }

    private function createStudent(array $data): void
    {
        DB::transaction(function () use ($data) {
            if (empty($data['class_id']) && ! empty($data['_batch_name']) && ! empty($data['_class_code'])) {
                $batch = Batch::firstOrCreate(['batch_name' => $data['_batch_name']]);
                $class = ClassGroup::firstOrCreate([
                    'batch_id' => $batch->id,
                    'class_code' => $data['_class_code'],
                ]);
                $data['class_id'] = $class->id;
            }

            unset($data['_batch_name'], $data['_class_code'], $data['_notices']);
            $data['cumulative_gpa'] = 0;

            Student::create($data);
        });
    }

    private function createClassGroup(array $data): void
    {
        DB::transaction(function () use ($data) {
            if (empty($data['batch_id']) && ! empty($data['_batch_name'])) {
                $batch = Batch::firstOrCreate(['batch_name' => $data['_batch_name']]);
                $data['batch_id'] = $batch->id;
            }

            unset($data['_batch_name'], $data['_notices']);
            ClassGroup::firstOrCreate([
                'batch_id' => $data['batch_id'],
                'class_code' => $data['class_code'],
            ]);
        });
    }

    private function courseRecord(array $row): array
    {
        $errors = [];
        $course = Course::where('course_code', $row['course_code'])->first() ?? $errors[] = "Course \"{$row['course_code']}\" does not exist.";
        $period = Period::where('period_name', $row['period'])->first() ?? $errors[] = "Period \"{$row['period']}\" does not exist.";
        $lecturer = Lecturer::where('lecturer_code', $row['lecturer_code'])->first() ?? $errors[] = "Lecturer \"{$row['lecturer_code']}\" does not exist.";
        $classId = $this->classId($row['class_code'], '', $errors);

        $students = [];

        foreach (array_filter(array_map('trim', preg_split('/[;|]/', $row['students']))) as $nim) {
            $student = Student::where('nim', $nim)->first();
            $student ? $students[] = $student->id : $errors[] = "Student \"{$nim}\" does not exist.";
        }

        if ($row['ikadq'] !== '' && (! is_numeric($row['ikadq']) || $row['ikadq'] < 0 || $row['ikadq'] > 4)) {
            $errors[] = 'IKADQ must be a number between 0 and 4.';
        }

        if ($errors === [] && CourseRecord::where(['course_id' => $course->id, 'period_id' => $period->id, 'class_id' => $classId])->exists()) {
            $errors[] = 'A course record for this course, period and class already exists.';
        }

        return [$errors ? null : [
            'course_id' => $course->id, 'period_id' => $period->id, 'class_id' => $classId, 'lecturer_id' => $lecturer->id,
            'ikadq' => $row['ikadq'] === '' ? null : $row['ikadq'], 'students' => array_values(array_unique($students)),
        ], $errors];
    }

    private function grade(array $row): array
    {
        $errors = [];
        $record = CourseRecord::where('record_code', $row['record_code'])->first() ?? $errors[] = "Course record \"{$row['record_code']}\" does not exist.";
        $student = Student::where('nim', $row['nim'])->first() ?? $errors[] = "Student \"{$row['nim']}\" does not exist.";
        $grade = strtoupper($row['grade']);

        if ($grade !== '' && ! array_key_exists($grade, StudentGrade::GRADE_POINTS)) {
            $errors[] = 'Grade must be one of ' . implode(', ', array_keys(StudentGrade::GRADE_POINTS)) . '.';
        }

        return [$errors ? null : ['course_record_id' => $record->id, 'student_id' => $student->id, 'letter_grade' => $grade ?: null], $errors];
    }

    private function createCourseRecord(array $data): void
    {
        DB::transaction(function () use ($data) {
            $record = CourseRecord::create([
                'course_id' => $data['course_id'], 'period_id' => $data['period_id'], 'class_id' => $data['class_id'],
                'lecturer_id' => $data['lecturer_id'], 'record_code' => CourseRecord::nextCodeFor(Period::find($data['period_id'])),
            ]);

            if ($data['ikadq'] !== null) {
                LecturerPerformance::create(['lecturer_id' => $data['lecturer_id'], 'course_record_id' => $record->id, 'ikadq' => $data['ikadq']]);
            }

            foreach ($data['students'] as $studentId) {
                StudentGrade::create(['student_id' => $studentId, 'course_record_id' => $record->id]);
            }
        });
    }

    private function createGrade(array $data): void
    {
        $letter = $data['letter_grade'];

        StudentGrade::updateOrCreate(
            ['student_id' => $data['student_id'], 'course_record_id' => $data['course_record_id']],
            ['letter_grade' => $letter, 'numeric_grade' => $letter ? StudentGrade::GRADE_POINTS[$letter] : null],
        );
    }

    private function classId(string $classCode, string $batchName, array &$errors): ?int
    {
        $query = ClassGroup::where('class_code', $classCode);

        if ($batchName !== '') {
            $query->whereHas('batch', fn ($q) => $q->where('batch_name', $batchName));
        }

        $matches = $query->pluck('id');

        if ($matches->isEmpty()) {
            $errors[] = "Class \"{$classCode}\" does not exist.";

            return null;
        }

        if ($matches->count() > 1) {
            $errors[] = "Class \"{$classCode}\" exists in more than one batch; add a batch column.";

            return null;
        }

        return $matches->first();
    }

    private function date(string $value, array &$errors): ?string
    {
        if ($value === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat('!' . $format, $value);
            } catch (\Throwable) {
                continue;
            }

            if ($date->format($format) === $value && $date->lte(today())) {
                return $date->toDateString();
            }
        }

        $errors[] = 'Date of birth must be YYYY-MM-DD or DD/MM/YYYY and not in the future.';

        return null;
    }

    private function nullable(string $value): ?string
    {
        return $value === '' ? null : $value;
    }
}
