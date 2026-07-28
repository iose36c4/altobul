<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;

class GrantFieldValueAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'grantee_id' => ['required', 'uuid', 'exists:users,id'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ];
    }
}
