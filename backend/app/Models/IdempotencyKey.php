<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdempotencyKey extends Model
{
    protected $table = 'idempotency_keys';
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    
    protected $fillable = [
        'key',
        'user_id',
        'response',
        'status_code',
        'created_at',
    ];
    
    protected $casts = [
        'response' => 'json',
        'created_at' => 'datetime',
    ];
}