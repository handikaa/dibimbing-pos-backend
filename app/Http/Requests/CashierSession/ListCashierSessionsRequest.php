<?php

namespace App\Http\Requests\CashierSession;

use Illuminate\Foundation\Http\FormRequest;

class ListCashierSessionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // middleware sudah handle permission
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|integer|exists:users,id',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
            'status' => 'nullable|string|in:OPEN,CLOSED',
            'per_page' => 'nullable|integer|min:1',
        ];
    }
}