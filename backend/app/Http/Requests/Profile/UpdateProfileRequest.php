<?php

namespace App\Http\Requests\Profile;

use App\Domain\Authorization\VisibilityLevel;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $visibilityValues = implode(',', array_column(VisibilityLevel::cases(), 'value'));

        return [
            'title' => ['nullable', 'string', 'max:120'],
            'title_visibility' => ['sometimes', 'string', "in:$visibilityValues"],
            'title_requires_verified' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
            'description_visibility' => ['sometimes', 'string', "in:$visibilityValues"],
            'description_requires_verified' => ['sometimes', 'boolean'],
            'birth_date' => ['nullable', 'date', 'before_or_equal:today'],
            'birth_date_visibility' => ['sometimes', 'string', "in:$visibilityValues"],
            'birth_date_requires_verified' => ['sometimes', 'boolean'],
            'profile_visibility' => ['sometimes', 'string', "in:$visibilityValues"],
            'profile_requires_verified' => ['sometimes', 'boolean'],
            'discoverable' => ['sometimes', 'boolean'],
            'geo_zone_id' => ['nullable', 'uuid', 'exists:geo_zones,id'],
        ];
    }
}
