<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $students = [
            [
                'nim' => '20230001',
                'name' => 'Aditya Pranoto',
                'class_code' => '13-A',
                'status' => 'Active',
                'cumulative_gpa' => 3.45,
            ],
            [
                'nim' => '20230002',
                'name' => 'Bella Maharani',
                'class_code' => '13-B',
                'status' => 'Active',
                'cumulative_gpa' => 3.62,
            ],
            [
                'nim' => '20240001',
                'name' => 'Farhan Akbar',
                'class_code' => '14-A',
                'status' => 'Active',
                'cumulative_gpa' => 3.50,
            ],
            [
                'nim' => '20240002',
                'name' => 'Grace Natalia',
                'class_code' => '14-B',
                'status' => 'On Leave',
                'cumulative_gpa' => 3.80,
            ],
        ];

        foreach ($students as $student) {
            Student::create([
                'nim' => $student['nim'],
                'name' => $student['name'],
                'class_id' => ClassGroup::where('class_code', $student['class_code'])->firstOrFail()->id,
                'status' => $student['status'],
                'cumulative_gpa' => $student['cumulative_gpa'],
            ]);
        }
    }
}