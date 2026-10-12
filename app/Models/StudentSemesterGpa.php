<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StudentSemesterGpa extends Model
{
    protected $fillable = [
        'student_id',
        'period_id',
        'gpa',
    ];

    protected $casts = [
        'gpa' => 'decimal:2',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function period()
    {
        return $this->belongsTo(Period::class);
    }
}
