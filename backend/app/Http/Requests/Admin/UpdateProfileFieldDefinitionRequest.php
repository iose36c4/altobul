<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileFieldDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        $fieldId = $this->route('field')->id ?? null;

        return [
            'slug' => ['sometimes', 'string', 'max:50', Rule::unique('profile_fields', 'slug')->ignore($fieldId), 'regex:/^[a-z0-9_]+$/'],
            'label' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'type' => ['sometimes', 'string', Rule::in(['TEXT', 'TEXTAREA', 'NUMBER', 'DATE', 'BOOLEAN', 'SELECT', 'MULTISELECT', 'RADIO'])],
            'default_visibility' => ['sometimes', 'string', Rule::in(['PUBLIC', 'MATCH', 'FRIENDS', 'PRIVATE'])],
            'default_requires_verified' => ['boolean'],
            'validation_rules' => ['nullable', 'array'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
            'options' => ['nullable', 'array'],
            'options.*.id' => ['nullable', 'string', 'uuid'],
            'options.*.label' => ['required_with:options', 'string', 'max:200'],
            'options.*.value' => ['required_with:options', 'string', 'max:100'],
            'options.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'options.*.is_active' => ['boolean'],
        ];
    }
}
