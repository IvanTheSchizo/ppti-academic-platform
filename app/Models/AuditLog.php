<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'admin_id',
        'action_type',
        'target_entity',
        'target_id',
        'old_value',
        'new_value',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}