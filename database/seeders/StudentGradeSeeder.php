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
                'student_nim' => '2601581007',
                'record_code' => 'CR-2024-S1-001',
                'numeric_grade' => 4.00,
                'letter_grade' => 'A',
            ],
            [
                'student_nim' => '2601581008',
                'record_code' => 'CR-2024-S1-002',
                'numeric_grade' => 3.70,
                'letter_grade' => 'A-',
            ],
            [
                'student_nim' => '2701581010',
                'record_code' => 'CR-2025-S1-001',
                'numeric_grade' => 3.30,
                'letter_grade' => 'B+',
            ],
            [
                'student_nim' => '2701581011',
                'record_code' => 'CR-2025-S1-002',
                'numeric_grade' => 3.00,
                'letter_grade' => 'B',
            ],
        ];

        foreach ($grades as $grade) {
            StudentGrade::create([
                'student_id' => Student::where('nim', $grade['student_nim'])->firstOrFail()->id,
                'course_record_id' => CourseRecord::where('record_code', $grade['record_code'])->firstOrFail()->id,
                'numeric_grade' => $grade['numeric_grade'],
                'letter_grade' => $grade['letter_grade'],
            ]);
        }
    }
}