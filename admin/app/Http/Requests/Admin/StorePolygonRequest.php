<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StorePolygonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'geometry' => ['required', 'array'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
