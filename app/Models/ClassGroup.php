<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'class_code',
    ];

    public function batch()
    {
        return $this->belongsTo(Batch::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function courseRecords()
    {
        return $this->hasMany(CourseRecord::class, 'class_id');
    }
}