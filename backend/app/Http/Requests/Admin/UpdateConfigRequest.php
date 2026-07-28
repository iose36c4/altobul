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
