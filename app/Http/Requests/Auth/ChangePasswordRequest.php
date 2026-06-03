<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Authorization - user harus authenticated
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Validation rules
     */
    public function rules(): array
    {
        return [
            'current_password' => 'required|string|min:6',
            'new_password' => 'required|string|min:8|confirmed|different:current_password',
        ];
    }

    /**
     * Custom messages
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'Password saat ini wajib diisi.',
            'current_password.min' => 'Password minimal 6 karakter.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 8 karakter.',
            'new_password.confirmed' => 'Konfirmasi password tidak sesuai.',
            'new_password.different' => 'Password baru tidak boleh sama dengan password saat ini.',
        ];
    }

    /**
     * Attribute names
     */
    public function attributes(): array
    {
        return [
            'current_password' => 'password saat ini',
            'new_password' => 'password baru',
        ];
    }
}