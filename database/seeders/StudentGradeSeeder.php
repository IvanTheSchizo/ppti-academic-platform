<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\Student;
use App\Models\StudentGrade;
use Illuminate\Database\Seeder;

class StudentGradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = Course::pluck('id', 'course_name');

        $grades = [
            '25010001' => [
                'Database Systems' => 3.75,
                'Programming' => 4.00,
                'Web Development' => 3.50,
                'Data Structures' => 3.75,
                'Computer Networks' => 3.25,
                'Software Engineering' => 3.50,
            ],

            '25010002' => [
                'Database Systems' => 3.25,
                'Programming' => 3.50,
                'Web Development' => 3.25,
                'Data Structures' => 3.00,
                'Computer Networks' => 3.50,
                'Software Engineering' => 3.25,
            ],

            '25020001' => [
                'Database Systems' => 4.00,
                'Programming' => 3.75,
                'Web Development' => 4.00,
                'Data Structures' => 3.75,
                'Computer Networks' => 3.50,
                'Software Engineering' => 4.00,
            ],

            '25020002' => [
                'Database Systems' => 3.00,
                'Programming' => 3.25,
                'Web Development' => 3.50,
                'Data Structures' => 3.25,
                'Computer Networks' => 3.00,
                'Software Engineering' => 3.50,
            ],

            '25030001' => [
                'Database Systems' => 3.50,
                'Programming' => 4.00,
                'Web Development' => 3.75,
                'Data Structures' => 3.50,
                'Computer Networks' => 3.75,
                'Software Engineering' => 3.75,
            ],

            '25030002' => [
                'Database Systems' => 2.75,
                'Programming' => 3.25,
                'Web Development' => 3.00,
                'Data Structures' => 2.75,
                'Computer Networks' => 3.25,
                'Software Engineering' => 3.00,
            ],
        ];

        foreach ($grades as $nim => $studentGrades) {
            foreach ($studentGrades as $courseName => $grade) {
                StudentGrade::create([
                    'student_nim' => $nim,
                    'course_id' => $courses[$courseName],
                    'grade' => $grade,
                ]);
            }

            // Recalculate cached GPA after inserting grades
            $student = Student::findOrFail($nim);

            $student->cached_gpa = round($student->grades()->avg('grade') ?? 0, 2);
            $student->save();
        }
    }
}