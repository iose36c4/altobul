<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RequestVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'verification_method' => ['required', 'string', 'in:document,video,manual'],
            'external_reference' => ['nullable', 'string', 'max:255'],
        ];
    }
}