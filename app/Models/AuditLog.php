<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = [
        'admin_id',
        'action_type',
        'target_entity',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}