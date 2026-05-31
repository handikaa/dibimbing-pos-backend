<?php

namespace App\Http\Requests\CashierSession;

use Illuminate\Foundation\Http\FormRequest;

class CloseCashierSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Bisa diatur role OWNER/ADMIN/CASHIER
        return true;
    }

    public function rules(): array
    {
        return [
            'actual_cash' => 'required|numeric|min:0',
            'closing_note' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'actual_cash.required' => 'Jumlah uang aktual wajib diisi.',
            'actual_cash.numeric' => 'Jumlah uang aktual harus berupa angka.',
            'actual_cash.min' => 'Jumlah uang aktual minimal 0.',
            'closing_note.string' => 'Catatan harus berupa teks.',
            'closing_note.max' => 'Catatan maksimal 255 karakter.',
        ];
    }
}