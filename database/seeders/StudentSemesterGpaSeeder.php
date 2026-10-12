<?php

namespace Database\Seeders;

use App\Models\StudentGrade;
use App\Models\StudentSemesterGpa;
use Illuminate\Database\Seeder;

class StudentSemesterGpaSeeder extends Seeder
{
    /**
     * Seed each student's semester GPA from their average grade per period.
     */
    public function run(): void
    {
        StudentGrade::query()
            ->join('course_records', 'student_grades.course_record_id', '=', 'course_records.id')
            ->groupBy('student_grades.student_id', 'course_records.period_id')
            ->selectRaw('student_grades.student_id, course_records.period_id, AVG(student_grades.numeric_grade) as gpa')
            ->get()
            ->each(fn ($row) => StudentSemesterGpa::updateOrCreate(
                ['student_id' => $row->student_id, 'period_id' => $row->period_id],
                ['gpa' => round($row->gpa, 2)],
            ));
    }
}
