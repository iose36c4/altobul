<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MergeGeoZonesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'zone_a_id' => ['required', 'uuid', 'exists:geo_zones,id'],
            'zone_b_id' => [
                'required',
                'uuid',
                'exists:geo_zones,id',
                Rule::notIn([$this->input('zone_a_id')]),
            ],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'zone_a_id.required' => 'La zona A es obligatoria.',
            'zone_a_id.exists' => 'La zona A seleccionada no existe.',
            'zone_b_id.required' => 'La zona B es obligatoria.',
            'zone_b_id.exists' => 'La zona B seleccionada no existe.',
            'zone_b_id.not_in' => 'Las zonas deben ser diferentes.',
            'name.required' => 'El nombre de la zona resultante es obligatorio.',
        ];
    }
}