<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['sometimes', 'email', 'max:255'],
            'role' => ['sometimes', 'string', 'in:user,admin'],
            'status' => ['sometimes', 'string', 'in:active,suspended'],
        ];
    }
}
