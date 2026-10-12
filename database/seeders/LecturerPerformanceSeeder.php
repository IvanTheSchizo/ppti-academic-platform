<?php

namespace Database\Seeders;

use App\Models\CourseRecord;
use App\Models\Lecturer;
use App\Models\LecturerPerformance;
use Illuminate\Database\Seeder;

class LecturerPerformanceSeeder extends Seeder
{
    public function run(): void
    {
        $performances = [
            [
                'lecturer' => 'Dr. Andi Pratama',
                'record_code' => 'CR-2024-S1-001',
                'ikadq' => 3.72,
            ],
            [
                'lecturer' => 'Dr. Budi Santoso',
                'record_code' => 'CR-2024-S1-002',
                'ikadq' => 3.85,
            ],
            [
                'lecturer' => 'Dr. Citra Maharani',
                'record_code' => 'CR-2025-S1-001',
                'ikadq' => 3.61,
            ],
            [
                'lecturer' => 'Dewi Lestari, M.Kom',
                'record_code' => 'CR-2025-S1-002',
                'ikadq' => 3.48,
            ],
            [
                'lecturer' => 'Dr. Budi Santoso',
                'record_code' => 'CR-2026-S2-001',
                'ikadq' => 3.92,
            ],
        ];

        foreach ($performances as $performance) {
            LecturerPerformance::create([
                'lecturer_id' => Lecturer::where('name', $performance['lecturer'])->firstOrFail()->id,
                'course_record_id' => CourseRecord::where('record_code', $performance['record_code'])->firstOrFail()->id,
                'ikadq' => $performance['ikadq'],
            ]);
        }
    }
}