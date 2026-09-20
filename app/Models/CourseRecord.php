<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'lecturer_id',
        'period_id',
        'batch_id',
        'record_code',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function studentGrades()
    {
        return $this->hasMany(StudentGrade::class);
    }
}