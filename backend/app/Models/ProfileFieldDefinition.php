<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfileFieldDefinition extends Model
{
    protected $table = 'profile_fields';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'slug',
        'label',
        'description',
        'type',
        'validation_rules',
        'is_active',
        'is_required',
        'is_filterable',
        'default_visibility',
        'default_requires_verified',
        'sort_order',
    ];

    protected $casts = [
        'validation_rules' => 'json',
        'is_active' => 'boolean',
        'is_required' => 'boolean',
        'is_filterable' => 'boolean',
        'default_visibility' => 'string',
        'default_requires_verified' => 'boolean',
        'sort_order' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function options(): HasMany
    {
        return $this->hasMany(ProfileFieldOption::class, 'field_id', 'id');
    }

    public function values(): HasMany
    {
        return $this->hasMany(ProfileFieldValue::class, 'field_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }
}
