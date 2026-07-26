<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminAuditLog extends Model
{
    protected $table = 'admin_audit_logs';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
    
    protected $fillable = [
        'id',
        'admin_id',
        'action',
        'target_type',
        'target_id',
        'metadata',
        'ip_address',
        'user_agent',
        'created_at',
    ];
    
    protected $casts = [
        'metadata' => 'json',
        'created_at' => 'datetime',
    ];

    public function admin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_id', 'id');
    }
}