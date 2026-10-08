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

    public function classGroups()
    {
        return $this->hasMany(ClassGroup::class);
    }

    public function studentGrades()
    {
        return $this->hasManyThrough(
            StudentGrade::class,
            ClassGroup::class,
            'course_record_id',
            'enrollment_id'
        );
    }

    public function lecturerPerformance()
    {
        return $this->hasOne(LecturerPerformance::class);
    }
}