<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    private const ALLOWED_KEYS = [
        // Spec notation (preferred)
        'app.name',
        'app.online_threshold_minutes',
        'discovery.max_distance_km',
        'discovery.max_results',
        'toke.ttl_hours',
        'match.ttl_days',
        'post.ttl_hours',
        'photo.max_per_user',
        'photo.max_size_mb',
        'verification.methods',
        'geo.default_zone_id',
        // Legacy underscore notation (for backward compatibility)
        'app_name',
        'app_tagline',
        'registration_enabled',
        'email_verification_required',
        'location_default_precision_meters',
        'max_photos_per_user',
        'online_threshold_minutes',
        'tos_url',
        'privacy_url',
    ];

    public function rules(): array
    {
        $rules = [];
        foreach (self::ALLOWED_KEYS as $key) {
            $rules[$key] = ['sometimes'];
        }

        return $rules;
    }
}
