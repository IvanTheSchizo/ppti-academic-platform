<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentGrade extends Model
{
    use HasFactory;

    /** Letter grade => grade point. */
    public const GRADE_POINTS = [
        'A' => 4.00, 'A-' => 3.70, 'B+' => 3.30, 'B' => 3.00, 'B-' => 2.70,
        'C+' => 2.30, 'C' => 2.00, 'D' => 1.00, 'E' => 0.00,
    ];

    protected $fillable = [
        'student_id',
        'course_record_id',
        'numeric_grade',
        'letter_grade',
    ];

    protected $casts = [
        'numeric_grade' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function courseRecord()
    {
        return $this->belongsTo(CourseRecord::class);
    }
}