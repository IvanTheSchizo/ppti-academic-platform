<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,

            // Basic reference data
            BatchSeeder::class,
            LecturerSeeder::class,
            PeriodSeeder::class,
            CourseSeeder::class,

            // Academic structure
            StudentSeeder::class,
            CourseRecordSeeder::class,
            ClassGroupSeeder::class,
            EnrollmentSeeder::class,

            // Grades and lecturer performance
            StudentGradeSeeder::class,
            LecturerPerformanceSeeder::class,
        ]);
    }
}