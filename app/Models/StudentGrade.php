<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentGrade extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_nim',
        'course_id',
        'grade',
    ];

    protected $casts = [
        'grade' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_nim', 'nim');
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}