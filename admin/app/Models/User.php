<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'role',
        'status',
        'api_token',
        'backend_id',
    ];

    protected $hidden = [
        'password_hash',
        'api_token',
    ];

    protected $casts = [
        'api_token' => 'encrypted',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash ?? '';
    }
}
