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
        'class_id',
        'record_code',
    ];

    /**
     * Next free record code for a period, e.g. CR-2025-S1-003 for "Semester 1 - 2025".
     */
    public static function nextCodeFor(Period $period): string
    {
        $prefix = preg_match('/Semester\s+(\d+)\s*-\s*(\d{4})/i', $period->period_name, $m)
            ? "CR-{$m[2]}-S{$m[1]}"
            : "CR-P{$period->id}";

        $next = static::where('period_id', $period->id)->count() + 1;

        do {
            $code = sprintf('%s-%03d', $prefix, $next++);
        } while (static::where('record_code', $code)->exists());

        return $code;
    }

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

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class, 'class_id');
    }

    public function studentGrades()
    {
        return $this->hasMany(StudentGrade::class);
    }

        public function lecturerPerformance()
    {
        return $this->hasOne(LecturerPerformance::class);
    }
}