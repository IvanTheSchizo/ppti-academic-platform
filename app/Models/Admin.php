<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    protected $fillable = [
        'username',
        'display_name',
        'photo_path',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    public function displayName(): string
    {
        return $this->display_name ?: $this->username;
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path
            ? route('account.photo', ['v' => substr(md5($this->photo_path), 0, 8)])
            : null;
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}
