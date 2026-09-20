<?php

namespace Database\Seeders;

use App\Models\CourseRecord;
use App\Models\Student;
use App\Models\StudentGrade;
use Illuminate\Database\Seeder;

class StudentGradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            [
                'student_nim' => '20230001',
                'record_code' => 'CR-2024-S1-001',
                'grade' => 3.50,
            ],
            [
                'student_nim' => '20230001',
                'record_code' => 'CR-2024-S1-002',
                'grade' => 3.75,
            ],
            [
                'student_nim' => '20230002',
                'record_code' => 'CR-2024-S1-001',
                'grade' => 3.25,
            ],
            [
                'student_nim' => '20240001',
                'record_code' => 'CR-2025-S1-001',
                'grade' => 3.60,
            ],
            [
                'student_nim' => '20240002',
                'record_code' => 'CR-2025-S1-002',
                'grade' => 3.80,
            ],
        ];

        foreach ($grades as $grade) {
            StudentGrade::create([
                'student_id' => Student::where('nim', $grade['student_nim'])->firstOrFail()->id,
                'course_record_id' => CourseRecord::where('record_code', $grade['record_code'])->firstOrFail()->id,
                'grade' => $grade['grade'],
            ]);
        }
    }
}