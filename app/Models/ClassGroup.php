<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_record_id',
        'class_code',
    ];

    public function courseRecord()
    {
        return $this->belongsTo(CourseRecord::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }
}