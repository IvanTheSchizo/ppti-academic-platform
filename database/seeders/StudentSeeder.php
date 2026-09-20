<?php

namespace Database\Seeders;

use App\Models\Batch;
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
                'batch' => '2023',
                'track' => 'Software Engineering',
                'status' => 'Active',
                'gpa' => 3.45,
            ],
            [
                'nim' => '20230002',
                'name' => 'Bella Maharani',
                'batch' => '2023',
                'track' => 'Network Technology',
                'status' => 'Active',
                'gpa' => 3.62,
            ],
            [
                'nim' => '20240001',
                'name' => 'Farhan Akbar',
                'batch' => '2024',
                'track' => 'Software Engineering',
                'status' => 'Active',
                'gpa' => 3.50,
            ],
            [
                'nim' => '20240002',
                'name' => 'Grace Natalia',
                'batch' => '2024',
                'track' => 'Network Technology',
                'status' => 'Active',
                'gpa' => 3.80,
            ],
            [
                'nim' => '20250001',
                'name' => 'Kevin Santoso',
                'batch' => '2025',
                'track' => 'Software Engineering',
                'status' => 'Active',
                'gpa' => 3.41,
            ],
        ];

        foreach ($students as $student) {
            Student::create([
                'nim' => $student['nim'],
                'name' => $student['name'],
                'batch_id' => Batch::where('batch_name', $student['batch'])->firstOrFail()->id,
                'track' => $student['track'],
                'status' => $student['status'],
                'gpa' => $student['gpa'],
            ]);
        }
    }
}