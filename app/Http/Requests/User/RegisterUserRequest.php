<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('OWNER') === true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'string', Rule::in(['ADMIN', 'CASHIER'])],
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Role hanya boleh ADMIN atau CASHIER.',
        ];
    }
}