<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubtractGeoZonesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'minuend_id' => ['required', 'uuid', 'exists:geo_zones,id'],
            'subtrahend_id' => [
                'required',
                'uuid',
                'exists:geo_zones,id',
                Rule::notIn([$this->input('minuend_id')]),
            ],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'minuend_id.required' => 'La zona minuenda (A) es obligatoria.',
            'minuend_id.exists' => 'La zona minuenda seleccionada no existe.',
            'subtrahend_id.required' => 'La zona sustraendo (B) es obligatoria.',
            'subtrahend_id.exists' => 'La zona sustraendo seleccionada no existe.',
            'subtrahend_id.not_in' => 'Las zonas deben ser diferentes.',
            'name.required' => 'El nombre de la zona resultante es obligatorio.',
        ];
    }
}