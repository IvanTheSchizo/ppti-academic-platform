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
        'student' => ['nim' => true, 'name' => true, 'class_code' => true, 'batch' => true, 'status' => false, 'status_reason' => false, 'gender' => false, 'email' => false, 'phone_number' => false, 'date_of_birth' => false, 'cumulative_gpa' => false],
        'lecturer' => ['lecturer_code' => true, 'name' => true, 'batch' => false, 'job_start_date' => false, 'jja' => false, 'latest_education' => false, 'status' => false, 'status_reason' => false, 'gender' => false, 'email_binus_edu' => false, 'email_binus_ac_id' => false, 'phone_number' => false],
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
     * @param  string|resource  $source  Local file path or readable stream
     * @return array{rows: list<array<string, string>>, errors: list<string>} rows are keyed by column and carry a "_line" number
     */
    public function parse(mixed $source, string $tab): array
    {
        if (is_resource($source)) {
            $handle = fopen('php://temp', 'r+');
            if (! $handle) {
                return ['rows' => [], 'errors' => ['The file could not be read.']];
            }

            stream_copy_to_stream($source, $handle);
            fclose($source);
            rewind($handle);
        } else {
            $handle = @fopen((string) $source, 'r');

            if (! $handle) {
                return ['rows' => [], 'errors' => ['The file could not be read.']];
            }
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

            return ['rows' => [], 'errors' => ['Missing required column(s): '.implode(', ', $missing).'.']];
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
                $errors[] = 'Only the first '.self::MAX_ROWS.' rows are read.';
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
     * @return array{0: array|null, 1: list<string>} the data to create (null when invalid) and the error messages
     */
    public function process(string $tab, array $row, array &$seen): array
    {
        $key = $tab.'|'.strtolower(implode('|', array_map(fn ($c) => $row[$c], self::KEY[$tab])));

        if (isset($seen[$key])) {
            return [null, ['Duplicate of row '.$seen[$key].' in this file.']];
        }

        $seen[$key] = $row['_line'];

        [$data, $errors] = match ($tab) {
            'student' => $this->student($row),
            'lecturer' => $this->lecturer($row, $seen),
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
            'lecturer' => $this->createLecturer($data),
            'course' => $this->createCourse($data),
            'class' => $this->createClassGroup($data),
            'course-record' => $this->createCourseRecord($data),
            'grades' => $this->createGrade($data),
        };
    }

    private function student(array $row): array
    {
        $errors = [];
        $notices = [];
        $existing = $row['nim'] !== '' ? Student::where('nim', $row['nim'])->first() : null;
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

        $status = $row['status'] ?: ($existing?->status ?? 'Active');
        $rawReason = $this->nullable($row['status_reason'] ?? '');
        $statusReason = in_array($status, Student::REASON_STATUSES, true)
            ? ($rawReason ?? $existing?->status_reason)
            : null;

        $validator = Validator::make([
            'nim' => $row['nim'],
            'name' => $row['name'],
            'status' => $status,
            'status_reason' => $statusReason,
            'gender' => $this->nullable($row['gender']),
            'email' => $this->nullable($row['email']),
            'phone_number' => $this->nullable($row['phone_number']),
            'cumulative_gpa' => $this->nullable($row['cumulative_gpa'] ?? ''),
        ], [
            'nim' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'status' => 'in:'.implode(',', Student::STATUSES),
            'status_reason' => 'nullable|string|max:1000',
            'gender' => 'nullable|in:Male,Female',
            'email' => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:30',
            'cumulative_gpa' => 'nullable|numeric|between:0,4',
        ]);

        $errors = array_merge($errors, $validator->errors()->all());

        if ($errors !== []) {
            return [null, $errors];
        }

        if ($existing) {
            $notices[] = "Existing student \"{$row['nim']}\" will be updated.";
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

    private function lecturer(array $row, array &$seen): array
    {
        $errors = [];
        $notices = [];
        $existing = $row['lecturer_code'] !== '' ? Lecturer::where('lecturer_code', $row['lecturer_code'])->first() : null;

        $rawJobStartDate = trim((string) ($row['job_start_date'] ?? ''));
        $jobStartDate = $rawJobStartDate !== ''
            ? $this->date($rawJobStartDate, $errors, 'Job start date', true)
            : $existing?->job_start_date?->toDateString();

        $batchNames = array_values(array_unique(array_filter(array_map('trim', preg_split('/[;|]/', (string) ($row['batch'] ?? ''))))));
        foreach ($batchNames as $batchName) {
            if (mb_strlen($batchName) > 255) {
                $errors[] = "Batch \"{$batchName}\" may not be greater than 255 characters.";
            } elseif (! Batch::where('batch_name', $batchName)->exists()) {
                $notices[] = "Batch \"{$batchName}\" will be created automatically.";
            }
        }

        $status = $row['status'] !== '' ? $row['status'] : ($existing?->status ?? 'Active');
        $rawReason = $this->nullable($row['status_reason'] ?? '');
        $statusReason = in_array($status, Lecturer::REASON_STATUSES, true)
            ? ($rawReason ?? $existing?->status_reason)
            : null;

        if ($existing) {
            $input = [
                'lecturer_code' => $row['lecturer_code'],
                'name' => $row['name'] !== '' ? $row['name'] : $existing->name,
                'jja' => $row['jja'] !== '' ? $row['jja'] : $existing->jja,
                'latest_education' => $row['latest_education'] !== '' ? $row['latest_education'] : $existing->latest_education,
                'status' => $status,
                'status_reason' => $statusReason,
                'gender' => $row['gender'] !== '' ? $row['gender'] : $existing->gender,
                'email_binus_edu' => $row['email_binus_edu'] !== '' ? $row['email_binus_edu'] : $existing->email_binus_edu,
                'email_binus_ac_id' => $row['email_binus_ac_id'] !== '' ? $row['email_binus_ac_id'] : $existing->email_binus_ac_id,
                'phone_number' => $row['phone_number'] !== '' ? $row['phone_number'] : $existing->phone_number,
            ];
        } else {
            $input = [
                'lecturer_code' => $this->nullable($row['lecturer_code']),
                'name' => $this->nullable($row['name']),
                'jja' => $this->nullable($row['jja']),
                'latest_education' => $this->nullable($row['latest_education']),
                'status' => $status,
                'status_reason' => $statusReason,
                'gender' => $this->nullable($row['gender']),
                'email_binus_edu' => $this->nullable($row['email_binus_edu']),
                'email_binus_ac_id' => $this->nullable($row['email_binus_ac_id']),
                'phone_number' => $this->nullable($row['phone_number']),
            ];
        }

        $validator = Validator::make($input, [
            'lecturer_code' => 'required|string|max:20',
            'name' => 'required|string|max:255',
            'jja' => 'nullable|string|max:100',
            'latest_education' => 'nullable|string|max:50',
            'status' => 'in:'.implode(',', Lecturer::STATUSES),
            'status_reason' => 'nullable|string|max:1000',
            'gender' => 'nullable|in:Male,Female',
            'email_binus_edu' => ['nullable', 'email', 'max:255', Rule::unique('lecturers', 'email_binus_edu')->ignore($existing?->id)],
            'email_binus_ac_id' => ['nullable', 'email', 'max:255', Rule::unique('lecturers', 'email_binus_ac_id')->ignore($existing?->id)],
            'phone_number' => 'nullable|string|max:30',
        ]);

        $errors = array_merge($errors, $validator->errors()->all());

        $emailFields = [
            'email_binus_edu' => 'Email (binus.edu)',
            'email_binus_ac_id' => 'Email (binus.ac.id)',
        ];

        foreach ($emailFields as $field => $label) {
            if (! empty($input[$field])) {
                $emailKey = "lecturer_{$field}|".strtolower((string) $input[$field]);
                if (isset($seen[$emailKey])) {
                    $errors[] = "{$label} duplicates row {$seen[$emailKey]} in this file.";
                }
            }
        }

        if ($errors !== []) {
            return [null, $errors];
        }

        foreach (array_keys($emailFields) as $field) {
            if (! empty($input[$field])) {
                $emailKey = "lecturer_{$field}|".strtolower((string) $input[$field]);
                $seen[$emailKey] = $row['_line'];
            }
        }

        $data = $validator->validated() + ['job_start_date' => $jobStartDate];

        if ($batchNames !== []) {
            $data['_batch_names'] = $batchNames;
        }

        if ($existing) {
            $notices[] = "Existing lecturer \"{$row['lecturer_code']}\" will be updated.";
        }

        if ($notices !== []) {
            $data['_notices'] = $notices;
        }

        return [$data, []];
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
            $audit = app(AuditLogger::class);

            if (empty($data['class_id']) && ! empty($data['_batch_name']) && ! empty($data['_class_code'])) {
                $batch = Batch::firstOrCreate(['batch_name' => $data['_batch_name']]);
                if ($batch->wasRecentlyCreated) {
                    $audit->record('CREATE', 'Batch', $batch->id, null, $audit->snapshot($batch, ['batch_name']));
                }

                $class = ClassGroup::firstOrCreate([
                    'batch_id' => $batch->id,
                    'class_code' => $data['_class_code'],
                ]);
                if ($class->wasRecentlyCreated) {
                    $audit->record('CREATE', 'ClassGroup', $class->id, null, $audit->snapshot($class, ['batch_id', 'class_code']));
                }

                $data['class_id'] = $class->id;
            }

            unset($data['_batch_name'], $data['_class_code'], $data['_notices']);

            $fields = ['nim', 'name', 'class_id', 'status', 'status_reason', 'gender', 'email', 'phone_number', 'date_of_birth', 'cumulative_gpa'];
            $existing = Student::where('nim', $data['nim'])->first();

            if ($existing) {
                $old = $audit->snapshot($existing, $fields);
                $data['cumulative_gpa'] = $data['cumulative_gpa'] !== null
                    ? round((float) $data['cumulative_gpa'], 2)
                    : $existing->cumulative_gpa;

                $existing->update($data);
                $existing->refresh();
                $new = $audit->snapshot($existing, $fields);

                if ($old !== $new) {
                    $audit->record('UPDATE', 'Student', $existing->id, $old, $new);
                }

                return;
            }

            $data['cumulative_gpa'] = $data['cumulative_gpa'] !== null ? round((float) $data['cumulative_gpa'], 2) : 0;

            $student = Student::create($data);
            $audit->record('CREATE', 'Student', $student->id, null, $audit->snapshot($student, $fields));
        });
    }

    private function createLecturer(array $data): void
    {
        DB::transaction(function () use ($data) {
            $audit = app(AuditLogger::class);
            $fields = [
                'lecturer_code', 'name', 'jja', 'latest_education', 'status', 'status_reason',
                'gender', 'email_binus_edu', 'email_binus_ac_id', 'phone_number', 'job_start_date',
            ];

            $batchNames = $data['_batch_names'] ?? [];
            unset($data['_notices'], $data['_batch_names']);

            $batchIds = [];
            foreach ($batchNames as $batchName) {
                $batch = Batch::firstOrCreate(['batch_name' => $batchName]);
                if ($batch->wasRecentlyCreated) {
                    $audit->record('CREATE', 'Batch', $batch->id, null, $audit->snapshot($batch, ['batch_name']));
                }
                $batchIds[] = $batch->id;
            }

            $existing = Lecturer::where('lecturer_code', $data['lecturer_code'])->first();

            if ($existing) {
                $existing->load('batches');
                $old = $audit->snapshot($existing, $fields);
                $old['job_start_date'] = $existing->job_start_date?->toDateString();
                $old['batches'] = implode(', ', $existing->sortedBatchNames()) ?: null;

                $updateData = [];
                foreach ($fields as $field) {
                    if (array_key_exists($field, $data) && $data[$field] !== null && $data[$field] !== '') {
                        $updateData[$field] = $data[$field];
                    }
                }
                if (! in_array($data['status'] ?? $existing->status, Lecturer::REASON_STATUSES, true)) {
                    $updateData['status_reason'] = null;
                }

                $existing->update($updateData);
                if ($batchIds !== []) {
                    $existing->batches()->sync($batchIds);
                }

                $existing->refresh();
                $existing->load('batches');
                $new = $audit->snapshot($existing, $fields);
                $new['job_start_date'] = $existing->job_start_date?->toDateString();
                $new['batches'] = implode(', ', $existing->sortedBatchNames()) ?: null;

                if ($old !== $new) {
                    $audit->record('UPDATE', 'Lecturer', $existing->id, $old, $new);
                }

                return;
            }

            $lecturer = Lecturer::create($data);
            if ($batchIds !== []) {
                $lecturer->batches()->sync($batchIds);
            }
            $lecturer->load('batches');

            $snapshot = $audit->snapshot($lecturer, $fields);
            $snapshot['job_start_date'] = $lecturer->job_start_date?->toDateString();
            $snapshot['batches'] = implode(', ', $lecturer->sortedBatchNames()) ?: null;

            $audit->record('CREATE', 'Lecturer', $lecturer->id, null, $snapshot);
        });
    }

    private function createCourse(array $data): void
    {
        $course = Course::create($data);
        $audit = app(AuditLogger::class);
        $audit->record('CREATE', 'Course', $course->id, null, $audit->snapshot($course, ['course_code', 'course_name', 'sks']));
    }

    private function createClassGroup(array $data): void
    {
        DB::transaction(function () use ($data) {
            $audit = app(AuditLogger::class);

            if (empty($data['batch_id']) && ! empty($data['_batch_name'])) {
                $batch = Batch::firstOrCreate(['batch_name' => $data['_batch_name']]);
                if ($batch->wasRecentlyCreated) {
                    $audit->record('CREATE', 'Batch', $batch->id, null, $audit->snapshot($batch, ['batch_name']));
                }

                $data['batch_id'] = $batch->id;
            }

            unset($data['_batch_name'], $data['_notices']);
            $classGroup = ClassGroup::firstOrCreate([
                'batch_id' => $data['batch_id'],
                'class_code' => $data['class_code'],
            ]);
            if ($classGroup->wasRecentlyCreated) {
                $audit->record('CREATE', 'ClassGroup', $classGroup->id, null, $audit->snapshot($classGroup, ['batch_id', 'class_code']));
            }
        });
    }

    private function courseRecord(array $row): array
    {
        $errors = [];
        $notices = [];
        $course = Course::where('course_code', $row['course_code'])->first() ?? $errors[] = "Course \"{$row['course_code']}\" does not exist.";
        $lecturer = Lecturer::where('lecturer_code', $row['lecturer_code'])->first() ?? $errors[] = "Lecturer \"{$row['lecturer_code']}\" does not exist.";
        $classId = $this->classId($row['class_code'], '', $errors);

        $period = null;
        if ($row['period'] === '') {
            $errors[] = 'The period field is required.';
        } elseif (mb_strlen($row['period']) > 255) {
            $errors[] = 'Period may not be greater than 255 characters.';
        } else {
            $period = Period::where('period_name', $row['period'])->first();
            if (! $period) {
                $notices[] = "Period \"{$row['period']}\" will be created automatically.";
            }
        }

        $students = [];

        foreach (array_filter(array_map('trim', preg_split('/[;|]/', $row['students']))) as $nim) {
            $student = Student::where('nim', $nim)->first();
            $student ? $students[] = $student->id : $errors[] = "Student \"{$nim}\" does not exist.";
        }

        if ($row['ikadq'] !== '' && (! is_numeric($row['ikadq']) || $row['ikadq'] < 0 || $row['ikadq'] > 4)) {
            $errors[] = 'IKADQ must be a number between 0 and 4.';
        }

        if ($errors === [] && $period !== null && CourseRecord::where(['course_id' => $course->id, 'period_id' => $period->id, 'class_id' => $classId])->exists()) {
            $errors[] = 'A course record for this course, period and class already exists.';
        }

        if ($errors !== []) {
            return [null, $errors];
        }

        $data = [
            'course_id' => $course->id,
            'period_id' => $period?->id,
            'class_id' => $classId,
            'lecturer_id' => $lecturer->id,
            'ikadq' => $row['ikadq'] === '' ? null : $row['ikadq'],
            'students' => array_values(array_unique($students)),
        ];

        if ($period === null) {
            $data['_period_name'] = $row['period'];
        }

        if ($notices !== []) {
            $data['_notices'] = $notices;
        }

        return [$data, []];
    }

    private function grade(array $row): array
    {
        $errors = [];
        $record = CourseRecord::where('record_code', $row['record_code'])->first() ?? $errors[] = "Course record \"{$row['record_code']}\" does not exist.";
        $student = Student::where('nim', $row['nim'])->first() ?? $errors[] = "Student \"{$row['nim']}\" does not exist.";
        $grade = strtoupper($row['grade']);

        if ($grade !== '' && ! array_key_exists($grade, StudentGrade::GRADE_POINTS)) {
            $errors[] = 'Grade must be one of '.implode(', ', array_keys(StudentGrade::GRADE_POINTS)).'.';
        }

        return [$errors ? null : ['course_record_id' => $record->id, 'student_id' => $student->id, 'letter_grade' => $grade ?: null], $errors];
    }

    private function createCourseRecord(array $data): void
    {
        DB::transaction(function () use ($data) {
            $audit = app(AuditLogger::class);

            if (empty($data['period_id']) && ! empty($data['_period_name'])) {
                $period = Period::firstOrCreate(['period_name' => $data['_period_name']]);
                if ($period->wasRecentlyCreated) {
                    $audit->record('CREATE', 'Period', $period->id, null, $audit->snapshot($period, ['period_name', 'start_date', 'end_date']));
                }

                $data['period_id'] = $period->id;
            }

            unset($data['_period_name'], $data['_notices']);

            $record = CourseRecord::create([
                'course_id' => $data['course_id'], 'period_id' => $data['period_id'], 'class_id' => $data['class_id'],
                'lecturer_id' => $data['lecturer_id'], 'record_code' => CourseRecord::nextCodeFor(Period::find($data['period_id'])),
            ]);

            if ($data['ikadq'] !== null) {
                LecturerPerformance::create(['lecturer_id' => $data['lecturer_id'], 'course_record_id' => $record->id, 'ikadq' => $data['ikadq']]);
            }

            $audit->record('CREATE', 'CourseRecord', $record->id, null, [
                'course_record' => $audit->snapshot($record, ['record_code', 'course_id', 'period_id', 'class_id', 'lecturer_id']),
                'ikadq' => $data['ikadq'],
            ]);

            foreach ($data['students'] as $studentId) {
                $grade = StudentGrade::create(['student_id' => $studentId, 'course_record_id' => $record->id]);
                $audit->record('CREATE', 'StudentGrade', $grade->id, null, $audit->snapshot($grade, ['student_id', 'course_record_id', 'letter_grade', 'numeric_grade']));
            }
        });
    }

    private function createGrade(array $data): void
    {
        $letter = $data['letter_grade'];
        $audit = app(AuditLogger::class);
        $grade = StudentGrade::firstOrNew(['student_id' => $data['student_id'], 'course_record_id' => $data['course_record_id']]);
        $wasExisting = $grade->exists;
        $old = $wasExisting ? $audit->snapshot($grade, ['student_id', 'course_record_id', 'letter_grade', 'numeric_grade']) : null;
        $grade->fill(['letter_grade' => $letter, 'numeric_grade' => $letter ? StudentGrade::GRADE_POINTS[$letter] : null]);
        if (! $wasExisting || $grade->isDirty()) {
            $grade->save();
            $audit->record($wasExisting ? 'UPDATE' : 'CREATE', 'StudentGrade', $grade->id, $old, $audit->snapshot($grade, ['student_id', 'course_record_id', 'letter_grade', 'numeric_grade']));
        }
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

    private function date(string $value, array &$errors, string $label = 'Date of birth', bool $allowFuture = false): ?string
    {
        if ($value === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y'] as $format) {
            try {
                $date = Carbon::createFromFormat('!'.$format, $value);
            } catch (\Throwable) {
                continue;
            }

            if ($date->format($format) === $value && ($allowFuture || $date->lte(today()))) {
                return $date->toDateString();
            }
        }

        $errors[] = $allowFuture
            ? "{$label} must be YYYY-MM-DD or DD/MM/YYYY."
            : "{$label} must be YYYY-MM-DD or DD/MM/YYYY and not in the future.";

        return null;
    }

    private function nullable(string $value): ?string
    {
        return $value === '' ? null : $value;
    }
}
