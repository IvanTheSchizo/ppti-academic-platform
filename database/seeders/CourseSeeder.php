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
                'course_code' => 'COMP101',
                'course_name' => 'Introduction to Programming',
                'sks' => 3,
            ],
            [
                'course_code' => 'DBS201',
                'course_name' => 'Database Systems',
                'sks' => 3,
            ],
            [
                'course_code' => 'WEB202',
                'course_name' => 'Web Development',
                'sks' => 3,
            ],
            [
                'course_code' => 'OOP203',
                'course_name' => 'Object-Oriented Programming',
                'sks' => 3,
            ],
            [
                'course_code' => 'NET204',
                'course_name' => 'Computer Networks',
                'sks' => 3,
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}