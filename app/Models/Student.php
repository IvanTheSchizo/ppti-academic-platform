<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    public const STATUSES = ['Active', 'Graduated', 'On Leave', 'Dropout'];

    public const REASON_STATUSES = ['On Leave', 'Dropout'];

    protected $fillable = [
        'nim',
        'name',
        'class_id',
        'status',
        'status_reason',
        'gender',
        'email',
        'phone_number',
        'date_of_birth',
        'cumulative_gpa',
        'photo_path',
    ];

    protected $casts = [
        'cumulative_gpa' => 'decimal:2',
        'date_of_birth' => 'date',
    ];

    public function photoUrl(): ?string
    {
        return $this->photo_path
            ? route('students.photo', ['student' => $this, 'v' => substr(md5($this->photo_path), 0, 8)])
            : null;
    }

    public function semesterGpas()
    {
        return $this->hasMany(StudentSemesterGpa::class);
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class, 'class_id');
    }

    public function batch()
    {
        return $this->hasOneThrough(
            Batch::class,
            ClassGroup::class,
            'id',
            'id',
            'class_id',
            'batch_id'
        );
    }

    public function grades()
    {
        return $this->hasMany(StudentGrade::class);
    }
}
