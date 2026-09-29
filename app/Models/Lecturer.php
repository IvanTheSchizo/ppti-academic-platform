<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecturer extends Model
{
    use HasFactory;

    protected $fillable = [
        'nip',
        'lecturer_code',
        'name',
        'email_binus_edu',
        'email_binus_ac_id',
        'phone_number',
        'jja',
        'latest_education',
        'status',
    ];

    public function courseRecords()
    {
        return $this->hasMany(CourseRecord::class);
    }

    public function performances()
    {
        return $this->hasMany(LecturerPerformance::class);
    }
}