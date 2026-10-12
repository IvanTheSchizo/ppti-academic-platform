<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $fillable = [
        'nim',
        'name',
        'class_id',
        'status',
        'cumulative_gpa',
    ];

    protected $casts = [
        'cumulative_gpa' => 'decimal:2',
    ];

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