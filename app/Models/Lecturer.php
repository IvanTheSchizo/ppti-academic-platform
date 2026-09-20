<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecturer extends Model
{
    use HasFactory;

    protected $fillable = [
        'nip',
        'name',
        'status',
    ];

    public function courseRecords()
    {
        return $this->hasMany(CourseRecord::class);
    }
}