<?php

namespace Database\Seeders;

use App\Models\ClassInfo;
use App\Models\Student;
use Illuminate\Database\Seeder;

class StudentSeeder extends Seeder
{
    public function run(): void
    {
        $math67 = ClassInfo::where('class_name', 'Math 67')->firstOrFail();
        $ppti69 = ClassInfo::where('class_name', 'PPTI 69')->firstOrFail();
        $lost5050 = ClassInfo::where('class_name', 'Lost my 50/50')->firstOrFail();

        $students = [
            [
                'nim' => '25010001',
                'name' => 'Bry1',
                'class_info_id' => $math67->id,
                'cached_gpa' => 0,
            ],
            [
                'nim' => '25010002',
                'name' => 'Bry2',
                'class_info_id' => $math67->id,
                'cached_gpa' => 0,
            ],
            [
                'nim' => '25020001',
                'name' => 'Bry3',
                'class_info_id' => $ppti69->id,
                'cached_gpa' => 0,
            ],
            [
                'nim' => '25020002',
                'name' => 'Bry4',
                'class_info_id' => $ppti69->id,
                'cached_gpa' => 0,
            ],
            [
                'nim' => '25030001',
                'name' => 'Bry5',
                'class_info_id' => $lost5050->id,
                'cached_gpa' => 0,
            ],
            [
                'nim' => '25030002',
                'name' => 'Bry6',
                'class_info_id' => $lost5050->id,
                'cached_gpa' => 0,
            ],
        ];

        foreach ($students as $student) {
            Student::create($student);
        }
    }
}