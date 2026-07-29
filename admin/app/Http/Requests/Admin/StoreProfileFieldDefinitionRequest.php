<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreProfileFieldDefinitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:100', 'alpha_dash', 'unique:profile_fields,slug'],
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'type' => ['required', 'string', 'in:text,textarea,number,select,multiselect,radio,checkbox,date,boolean'],
            'validation_rules' => ['nullable', 'json'],
            'is_active' => ['boolean'],
            'is_required' => ['boolean'],
            'is_filterable' => ['boolean'],
            'default_visibility' => ['required', 'string', 'in:PUBLIC,MATCH,FRIENDS,PRIVATE'],
            'default_requires_verified' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'options' => ['nullable', 'array'],
            'options.*.label' => ['required', 'string', 'max:255'],
            'options.*.value' => ['required', 'string', 'max:255'],
            'options.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'options.*.is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.alpha_dash' => 'El slug solo puede contener letras, números, guiones y guiones bajos',
            'slug.unique' => 'Este slug ya existe',
            'type.in' => 'Tipo de campo inválido',
            'default_visibility.in' => 'Visibilidad inválida',
        ];
    }
}
