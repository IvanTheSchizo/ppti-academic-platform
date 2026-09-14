<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $primaryKey = 'nim';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'nim',
        'name',
        'class_info_id',
        'cached_gpa',
    ];

    protected $casts = [
        'cached_gpa' => 'decimal:2',
    ];

    public function classInfo()
    {
        return $this->belongsTo(ClassInfo::class);
    }

    public function grades()
    {
        return $this->hasMany(StudentGrade::class, 'student_nim', 'nim');
    }
}