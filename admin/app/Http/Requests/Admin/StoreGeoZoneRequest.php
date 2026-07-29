<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreGeoZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'is_active' => ['boolean'],
            'polygons' => ['required', 'array', 'min:1'],
            'polygons.*.name' => ['required', 'string', 'max:100'],
            'polygons.*.geometry' => ['required', 'array'],
            'polygons.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'polygons.required' => 'Debe agregar al menos un polígono',
            'polygons.min' => 'Debe agregar al menos un polígono',
            'polygons.*.geometry.required' => 'La geometría del polígono es requerida',
        ];
    }
}
