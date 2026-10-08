<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\Enrollment;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        Enrollment::truncate();

        Schema::enableForeignKeyConstraints();

        $enrollments = [
            [
                'student_nim' => '2201581001',
                'record_code' => 'CR-2024-S1-001',
                'class_code' => '13-A',
            ],

            [
                'student_nim' => '2201581002',
                'record_code' => 'CR-2024-S1-002',
                'class_code' => '13-B',
            ],

            [
                'student_nim' => '2301581003',
                'record_code' => 'CR-2025-S1-001',
                'class_code' => '14-A',
            ],

            [
                'student_nim' => '2401581004',
                'record_code' => 'CR-2025-S1-002',
                'class_code' => '14-B',
            ],

            // Additional examples showing that one student
            // can enroll in multiple courses.
            [
                'student_nim' => '2601581007',
                'record_code' => 'CR-2024-S1-001',
                'class_code' => '13-B',
            ],

            [
                'student_nim' => '2601581007',
                'record_code' => 'CR-2024-S1-002',
                'class_code' => '13-A',
            ],

            [
                'student_nim' => '2601581008',
                'record_code' => 'CR-2025-S1-001',
                'class_code' => '14-B',
            ],
        ];

        foreach ($enrollments as $data) {
            $student = Student::where(
                'nim',
                $data['student_nim']
            )->firstOrFail();

            $classGroup = ClassGroup::where(
                'class_code',
                $data['class_code']
            )
            ->whereHas('courseRecord', function ($query) use ($data) {
                $query->where(
                    'record_code',
                    $data['record_code']
                );
            })
            ->firstOrFail();

            Enrollment::create([
                'student_id' => $student->id,
                'class_group_id' => $classGroup->id,
            ]);
        }
    }
}