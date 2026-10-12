<?php

namespace Database\Seeders;

use App\Models\Period;
use Illuminate\Database\Seeder;

class PeriodSeeder extends Seeder
{
    public function run(): void
    {
        $periods = [
            [
                'period_name' => 'Semester 1 - 2024',
                'start_date' => '2024-02-01',
                'end_date' => '2024-06-30',
            ],
            [
                'period_name' => 'Semester 2 - 2024',
                'start_date' => '2024-08-01',
                'end_date' => '2024-12-20',
            ],
            [
                'period_name' => 'Semester 1 - 2025',
                'start_date' => '2025-02-01',
                'end_date' => '2025-06-30',
            ],
            [
                'period_name' => 'Semester 2 - 2025',
                'start_date' => '2025-08-01',
                'end_date' => '2025-12-20',
            ],
            [
                'period_name' => 'Semester 1 - 2026',
                'start_date' => '2026-02-01',
                'end_date' => '2026-06-30',
            ],
            [
                'period_name' => 'Semester 2 - 2026',
                'start_date' => '2026-08-01',
                'end_date' => null,
            ],
        ];

        foreach ($periods as $period) {
            Period::create($period);
        }
    }
}