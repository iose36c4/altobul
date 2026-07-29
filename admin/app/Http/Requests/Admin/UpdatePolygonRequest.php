<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePolygonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'geometry' => ['sometimes', 'array'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
