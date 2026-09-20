<?php

namespace Database\Seeders;

use App\Models\Course;
use Illuminate\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        $courses = [
            [
                'course_name' => 'Introduction to Programming',
                'sks' => 3,
                'track_type' => 'General',
            ],
            [
                'course_name' => 'Database Systems',
                'sks' => 3,
                'track_type' => 'General',
            ],
            [
                'course_name' => 'Web Development',
                'sks' => 3,
                'track_type' => 'Software Engineering',
            ],
            [
                'course_name' => 'Object-Oriented Programming',
                'sks' => 3,
                'track_type' => 'Software Engineering',
            ],
            [
                'course_name' => 'Computer Networks',
                'sks' => 3,
                'track_type' => 'Network Technology',
            ],
            [
                'course_name' => 'Network Security',
                'sks' => 3,
                'track_type' => 'Network Technology',
            ],
            [
                'course_name' => 'Data Structures',
                'sks' => 3,
                'track_type' => 'General',
            ],
            [
                'course_name' => 'Software Project Management',
                'sks' => 2,
                'track_type' => 'Software Engineering',
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}