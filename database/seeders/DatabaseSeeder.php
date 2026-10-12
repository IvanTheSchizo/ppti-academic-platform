<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            BatchSeeder::class,
            ClassGroupSeeder::class,
            LecturerSeeder::class,
            PeriodSeeder::class,
            CourseSeeder::class,
            CourseRecordSeeder::class,
            StudentSeeder::class,
            StudentGradeSeeder::class,
            LecturerPerformanceSeeder::class,
        ]);
    }
}