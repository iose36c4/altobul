<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProfileFieldDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:50', 'unique:profile_fields,slug', 'regex:/^[a-z0-9_]+$/'],
            'label' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'type' => ['required', 'string', Rule::in(['TEXT', 'TEXTAREA', 'NUMBER', 'DATE', 'BOOLEAN', 'SELECT', 'MULTISELECT', 'RADIO'])],
            'default_visibility' => ['required', 'string', Rule::in(['PUBLIC', 'MATCH', 'FRIENDS', 'PRIVATE'])],
            'default_requires_verified' => ['boolean'],
            'validation_rules' => ['nullable', 'array'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
            'options' => ['nullable', 'array'],
            'options.*.label' => ['required_with:options', 'string', 'max:200'],
            'options.*.value' => ['required_with:options', 'string', 'max:100'],
            'options.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'options.*.is_active' => ['boolean'],
        ];
    }
}
