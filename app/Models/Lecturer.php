<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lecturer extends Model
{
    use HasFactory;

    public const STATUSES = ['Active', 'Inactive'];

    public const REASON_STATUSES = ['Inactive'];

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
        'status_reason',
        'gender',
        'job_start_date',
        'photo_path',
    ];

    protected $casts = [
        'job_start_date' => 'date',
    ];

    public function photoUrl(): ?string
    {
        return $this->photo_path
            ? route('lecturers.photo', ['lecturer' => $this, 'v' => substr(md5($this->photo_path), 0, 8)])
            : null;
    }

    public function batches()
    {
        return $this->belongsToMany(Batch::class);
    }

    /**
     * Return the lecturer's assigned batch names sorted naturally.
     *
     * @return list<string>
     */
    public function sortedBatchNames(): array
    {
        return $this->batches
            ->sortBy(fn (Batch $batch) => (int) preg_replace('/\D/', '', $batch->batch_name), SORT_NUMERIC)
            ->pluck('batch_name')
            ->values()
            ->all();
    }

    public function batchLabels(): string
    {
        $names = $this->sortedBatchNames();

        return $names !== [] ? implode(', ', $names) : '-';
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
