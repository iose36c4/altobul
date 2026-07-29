<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:CLIENT,ADMIN,MOBILE,INTEGRATION'],
            'expires_in_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
        ];
    }
}
