<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\Course;
use App\Models\CourseRecord;
use App\Models\Lecturer;
use App\Models\Period;
use Illuminate\Database\Seeder;

class CourseRecordSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            [
                'record_code' => 'CR-2024-S1-001',
                'course' => 'Introduction to Programming',
                'lecturer' => 'Dr. Andi Pratama',
                'period' => 'Semester 1 - 2024',
                'class_code' => '13A',
            ],
            [
                'record_code' => 'CR-2024-S1-002',
                'course' => 'Database Systems',
                'lecturer' => 'Dr. Budi Santoso',
                'period' => 'Semester 1 - 2024',
                'class_code' => '13B',
            ],
            [
                'record_code' => 'CR-2025-S1-001',
                'course' => 'Web Development',
                'lecturer' => 'Dr. Citra Maharani',
                'period' => 'Semester 1 - 2025',
                'class_code' => '14A',
            ],
            [
                'record_code' => 'CR-2025-S1-002',
                'course' => 'Object-Oriented Programming',
                'lecturer' => 'Dewi Lestari, M.Kom',
                'period' => 'Semester 1 - 2025',
                'class_code' => '14B',
            ],
            [
                'record_code' => 'CR-2026-S2-001',
                'course' => 'Computer Networks',
                'lecturer' => 'Dr. Budi Santoso',
                'period' => 'Semester 2 - 2026',
                'class_code' => '14B',
            ],
        ];

        foreach ($records as $record) {
            CourseRecord::create([
                'record_code' => $record['record_code'],
                'course_id' => Course::where('course_name', $record['course'])->firstOrFail()->id,
                'lecturer_id' => Lecturer::where('name', $record['lecturer'])->firstOrFail()->id,
                'period_id' => Period::where('period_name', $record['period'])->firstOrFail()->id,
                'class_id' => ClassGroup::where('class_code', $record['class_code'])->firstOrFail()->id,
            ]);
        }
    }
}