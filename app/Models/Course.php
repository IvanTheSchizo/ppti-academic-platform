<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_code',
        'course_name',
        'sks',
    ];

    protected $casts = [
        'sks' => 'integer',
    ];

    public function courseRecords()
    {
        return $this->hasMany(CourseRecord::class);
    }
}