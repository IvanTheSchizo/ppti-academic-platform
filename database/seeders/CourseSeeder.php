<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            'Database Systems',
            'Programming',
            'Web Development',
            'Data Structures',
            'Computer Networks',
            'Software Engineering',
        ];

        foreach ($courses as $courseName) {
            Course::create([
                'course_name' => $courseName,
            ]);
        }
    }
}