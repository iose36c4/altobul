<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProfileFieldValue extends Model
{
    protected $table = 'profile_field_values';

    protected $primaryKey = 'id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'profile_id',
        'field_id',
        'value_text',
        'value_number',
        'value_boolean',
        'value_date',
        'visibility_override',
        'requires_verified_override',
        'value_json',
    ];

    protected $casts = [
        'value_number' => 'decimal:2',
        'value_boolean' => 'boolean',
        'value_date' => 'date',
        'requires_verified_override' => 'boolean',
        'value_json' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class, 'profile_id', 'user_id');
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(ProfileFieldDefinition::class, 'field_id', 'id');
    }

    public function selectedOptions(): BelongsToMany
    {
        return $this->belongsToMany(
            ProfileFieldOption::class,
            'profile_field_value_options',
            'field_value_id',
            'option_id'
        );
    }

    public function grants(): HasMany
    {
        return $this->hasMany(ProfileFieldValueAccess::class, 'field_value_id', 'id');
    }

    public function getEffectiveVisibilityAttribute(): string
    {
        return $this->visibility_override ?? $this->field->default_visibility;
    }

    public function getEffectiveRequiresVerifiedAttribute(): bool
    {
        return $this->requires_verified_override ?? $this->field->default_requires_verified;
    }
}
