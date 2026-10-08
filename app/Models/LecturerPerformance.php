<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LecturerPerformance extends Model
{
    use HasFactory;

    protected $fillable = [
        'lecturer_id',
        'course_record_id',
        'ikadq',
    ];

    protected $casts = [
        'ikadq' => 'decimal:2',
    ];

    public function lecturer()
    {
        return $this->belongsTo(Lecturer::class);
    }

    public function courseRecord()
    {
        return $this->belongsTo(CourseRecord::class);
    }
}