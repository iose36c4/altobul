<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscoveryPreference extends Model
{
    protected $table = 'discovery_preferences';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'user_id',
        'field_id',
        'operator',
        'value_text',
        'value_number',
        'value_number_2',
        'value_date',
        'value_date_2',
        'value_option_ids',
        'value_boolean',
        'is_active',
    ];
    
    protected $casts = [
        'value_number' => 'decimal:4',
        'value_number_2' => 'decimal:4',
        'value_date' => 'date',
        'value_date_2' => 'date',
        'value_option_ids' => 'json',
        'value_boolean' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    
    public function field(): BelongsTo
    {
        return $this->belongsTo(ProfileFieldDefinition::class, 'field_id', 'id');
    }
    
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}