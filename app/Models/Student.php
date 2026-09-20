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
        'batch_id',
        'track',
        'status',
        'gpa',
    ];

    protected $casts = [
        'gpa' => 'decimal:2',
    ];

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function grades()
    {
        return $this->hasMany(StudentGrade::class);
    }
}