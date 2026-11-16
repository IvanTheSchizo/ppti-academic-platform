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
        'gender',
        'photo_path',
    ];

    public function photoUrl(): ?string
    {
        return $this->photo_path
            ? route('lecturers.photo', ['lecturer' => $this, 'v' => substr(md5($this->photo_path), 0, 8)])
            : null;
    }

    public function courseRecords()
    {
        return $this->hasMany(CourseRecord::class);
    }

    public function performances()
    {
        return $this->hasMany(LecturerPerformance::class);
    }
}
