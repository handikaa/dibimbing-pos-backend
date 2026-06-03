<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Authorization - tidak perlu authenticated untuk login
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:6',
            'device_name' => 'nullable|string|max:255',
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ];
    }

    /**
     * Attribute names
     */
    public function attributes(): array
    {
        return [
            'email' => 'email',
            'password' => 'password',
        ];
    }
}