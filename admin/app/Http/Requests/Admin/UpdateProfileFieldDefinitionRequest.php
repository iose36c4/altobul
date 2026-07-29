<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileFieldDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => ['sometimes', 'string', 'max:100', 'alpha_dash', 'unique:profile_fields,slug,'.$this->route('field')],
            'label' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'type' => ['sometimes', 'string', 'in:text,textarea,number,select,multiselect,radio,checkbox,date,boolean'],
            'validation_rules' => ['nullable', 'json'],
            'is_active' => ['boolean'],
            'is_required' => ['boolean'],
            'is_filterable' => ['boolean'],
            'default_visibility' => ['sometimes', 'string', 'in:PUBLIC,MATCH,FRIENDS,PRIVATE'],
            'default_requires_verified' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'options' => ['nullable', 'array'],
            'options.*.label' => ['required', 'string', 'max:255'],
            'options.*.value' => ['required', 'string', 'max:255'],
            'options.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'options.*.is_active' => ['boolean'],
        ];
    }
}
