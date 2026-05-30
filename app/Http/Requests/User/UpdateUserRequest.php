<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user');

        return [
            'name' => ['sometimes', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:30'],
            'role' => ['sometimes', Rule::in(['OWNER', 'ADMIN', 'CASHIER'])],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
