<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\CourseRecord;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ClassGroupSeeder extends Seeder
{
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();

        ClassGroup::truncate();

        Schema::enableForeignKeyConstraints();

        $classGroups = [
            [
                'record_code' => 'CR-2024-S1-001',
                'class_code' => '13-A',
            ],
            [
                'record_code' => 'CR-2024-S1-001',
                'class_code' => '13-B',
            ],

            [
                'record_code' => 'CR-2024-S1-002',
                'class_code' => '13-A',
            ],
            [
                'record_code' => 'CR-2024-S1-002',
                'class_code' => '13-B',
            ],

            [
                'record_code' => 'CR-2025-S1-001',
                'class_code' => '14-A',
            ],
            [
                'record_code' => 'CR-2025-S1-001',
                'class_code' => '14-B',
            ],

            [
                'record_code' => 'CR-2025-S1-002',
                'class_code' => '14-A',
            ],
            [
                'record_code' => 'CR-2025-S1-002',
                'class_code' => '14-B',
            ],

            [
                'record_code' => 'CR-2026-S2-001',
                'class_code' => '14-A',
            ],
            [
                'record_code' => 'CR-2026-S2-001',
                'class_code' => '14-B',
            ],
        ];

        foreach ($classGroups as $data) {
            $courseRecord = CourseRecord::where(
                'record_code',
                $data['record_code']
            )->firstOrFail();

            ClassGroup::create([
                'course_record_id' => $courseRecord->id,
                'class_code' => $data['class_code'],
            ]);
        }
    }
}