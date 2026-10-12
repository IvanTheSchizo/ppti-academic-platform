<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
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
            // PPTI 18 & 19
            ['nim' => '2201581001', 'name' => 'Citra Dewi',        'class_code' => '18A', 'status' => 'Graduated', 'cumulative_gpa' => 3.90],
            ['nim' => '2201581002', 'name' => 'Joko Wibowo',       'class_code' => '18B', 'status' => 'Graduated', 'cumulative_gpa' => 3.78],
            ['nim' => '2301581003', 'name' => 'Gita Savitri',      'class_code' => '19A', 'status' => 'Graduated', 'cumulative_gpa' => 3.95],

            // PPTI 21 & 22
            ['nim' => '2401581004', 'name' => 'Dimas Prasetyo',    'class_code' => '21A', 'status' => 'Active',    'cumulative_gpa' => 3.10],
            ['nim' => '2401581005', 'name' => 'Eka Putri',         'class_code' => '21B', 'status' => 'Active',    'cumulative_gpa' => 3.55],
            ['nim' => '2501581006', 'name' => 'Hendra Gunawan',    'class_code' => '22A', 'status' => 'On Leave',  'cumulative_gpa' => 3.40],

            // PPTI 23
            ['nim' => '2601581007', 'name' => 'Ayu Wijaya',        'class_code' => '23A', 'status' => 'Active',    'cumulative_gpa' => 3.72],
            ['nim' => '2601581008', 'name' => 'Budi Santoso',      'class_code' => '23A', 'status' => 'Active',    'cumulative_gpa' => 3.45],
            ['nim' => '2601581009', 'name' => 'Fajar Nugraha',     'class_code' => '23B', 'status' => 'Active',    'cumulative_gpa' => 3.82],

            // PPTI 24 & 25
            ['nim' => '2701581010', 'name' => 'Indah Permatasari', 'class_code' => '24A', 'status' => 'Active',    'cumulative_gpa' => 3.65],
            ['nim' => '2701581011', 'name' => 'Kevin Sanjaya',     'class_code' => '24B', 'status' => 'Active',    'cumulative_gpa' => 3.25],
            ['nim' => '2801581012', 'name' => 'Lestari Handayani', 'class_code' => '25A', 'status' => 'Active',    'cumulative_gpa' => 3.88],
        ];

        foreach ($students as $data) {
            $classGroup = ClassGroup::where('class_code', $data['class_code'])->first();

            if ($classGroup) {
                Student::create([
                    'nim'            => $data['nim'],
                    'name'           => $data['name'],
                    'class_id'       => $classGroup->id,
                    'status'         => $data['status'],
                    'cumulative_gpa' => $data['cumulative_gpa'],
                ]);
            }
        }
    }
}