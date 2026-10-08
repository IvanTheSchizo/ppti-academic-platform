<?php

namespace Database\Seeders;

use App\Models\Batch;
use App\Models\Student;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        Student::truncate();

        Schema::enableForeignKeyConstraints();

        $students = [
            // PPTI 18
            [
                'nim' => '2201581001',
                'name' => 'Citra Dewi',
                'batch_name' => 'PPTI 18',
                'status' => 'Graduated',
                'cumulative_gpa' => 3.90,
            ],

            [
                'nim' => '2201581002',
                'name' => 'Joko Wibowo',
                'batch_name' => 'PPTI 18',
                'status' => 'Graduated',
                'cumulative_gpa' => 3.78,
            ],

            // PPTI 19
            [
                'nim' => '2301581003',
                'name' => 'Gita Savitri',
                'batch_name' => 'PPTI 19',
                'status' => 'Graduated',
                'cumulative_gpa' => 3.95,
            ],

            // PPTI 21
            [
                'nim' => '2401581004',
                'name' => 'Dimas Prasetyo',
                'batch_name' => 'PPTI 21',
                'status' => 'Active',
                'cumulative_gpa' => 3.10,
            ],

            [
                'nim' => '2401581005',
                'name' => 'Eka Putri',
                'batch_name' => 'PPTI 21',
                'status' => 'Active',
                'cumulative_gpa' => 3.55,
            ],

            // PPTI 22
            [
                'nim' => '2501581006',
                'name' => 'Hendra Gunawan',
                'batch_name' => 'PPTI 22',
                'status' => 'On Leave',
                'cumulative_gpa' => 3.40,
            ],

            // PPTI 23
            [
                'nim' => '2601581007',
                'name' => 'Ayu Wijaya',
                'batch_name' => 'PPTI 23',
                'status' => 'Active',
                'cumulative_gpa' => 3.72,
            ],

            [
                'nim' => '2601581008',
                'name' => 'Budi Santoso',
                'batch_name' => 'PPTI 23',
                'status' => 'Active',
                'cumulative_gpa' => 3.45,
            ],

            [
                'nim' => '2601581009',
                'name' => 'Fajar Nugraha',
                'batch_name' => 'PPTI 23',
                'status' => 'Active',
                'cumulative_gpa' => 3.82,
            ],

            // PPTI 24
            [
                'nim' => '2701581010',
                'name' => 'Indah Permatasari',
                'batch_name' => 'PPTI 24',
                'status' => 'Active',
                'cumulative_gpa' => 3.65,
            ],

            [
                'nim' => '2701581011',
                'name' => 'Kevin Sanjaya',
                'batch_name' => 'PPTI 24',
                'status' => 'Active',
                'cumulative_gpa' => 3.25,
            ],

            // PPTI 25
            [
                'nim' => '2801581012',
                'name' => 'Lestari Handayani',
                'batch_name' => 'PPTI 25',
                'status' => 'Active',
                'cumulative_gpa' => 3.88,
            ],
        ];

        foreach ($students as $data) {
            $batch = Batch::where('batch_name', $data['batch_name'])->first();

            if ($batch) {
                Student::create([
                    'nim' => $data['nim'],
                    'name' => $data['name'],
                    'batch_id' => $batch->id,
                    'status' => $data['status'],
                    'cumulative_gpa' => $data['cumulative_gpa'],
                ]);
            }
        }
    }
}