<?php

namespace App\Http\Requests\CashierSession;

use Illuminate\Foundation\Http\FormRequest;

class OpenCashierSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Bisa diatur role OWNER/ADMIN/CASHIER
        return true;
    }

    public function rules(): array
    {
        return [
            'opening_cash' => 'required|numeric|min:0',
            'opening_note' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'opening_cash.required' => 'Jumlah uang awal wajib diisi.',
            'opening_cash.numeric' => 'Jumlah uang awal harus berupa angka.',
            'opening_cash.min' => 'Jumlah uang awal minimal 0.',
            'opening_note.string' => 'Catatan harus berupa teks.',
            'opening_note.max' => 'Catatan maksimal 255 karakter.',
        ];
    }
}