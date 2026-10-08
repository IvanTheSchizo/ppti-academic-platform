<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\StudentGrade;
use Illuminate\Database\Seeder;

class StudentGradeSeeder extends Seeder
{
    public function run(): void
    {
        $grades = [
            [
                'student_nim' => '2201581001',
                'record_code' => 'CR-2024-S1-001',
                'class_code' => '13-A',
                'numeric_grade' => 4.00,
                'letter_grade' => 'A',
            ],

            [
                'student_nim' => '2201581002',
                'record_code' => 'CR-2024-S1-002',
                'class_code' => '13-B',
                'numeric_grade' => 3.70,
                'letter_grade' => 'A-',
            ],

            [
                'student_nim' => '2301581003',
                'record_code' => 'CR-2025-S1-001',
                'class_code' => '14-A',
                'numeric_grade' => 3.30,
                'letter_grade' => 'B+',
            ],

            [
                'student_nim' => '2401581004',
                'record_code' => 'CR-2025-S1-002',
                'class_code' => '14-B',
                'numeric_grade' => 3.00,
                'letter_grade' => 'B',
            ],
        ];

        foreach ($grades as $grade) {
            $enrollment = Enrollment::whereHas('student', function ($query) use ($grade) {
                $query->where('nim', $grade['student_nim']);
            })
            ->whereHas('classGroup', function ($query) use ($grade) {
                $query->where('class_code', $grade['class_code'])
                    ->whereHas('courseRecord', function ($query) use ($grade) {
                        $query->where('record_code', $grade['record_code']);
                    });
            })
            ->firstOrFail();

            StudentGrade::create([
                'enrollment_id' => $enrollment->id,
                'numeric_grade' => $grade['numeric_grade'],
                'letter_grade' => $grade['letter_grade'],
            ]);
        }
    }
}