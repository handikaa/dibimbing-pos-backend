<?php

namespace App\Http\Requests\CashierSession;

use Illuminate\Foundation\Http\FormRequest;

class OpenCashierSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Permission check akan dilakukan di UseCase
     */
    public function authorize(): bool
    {
        // Basic check: user harus authenticated
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'opening_cash' => 'required|numeric|min:0|max:999999999.99',
            'opening_note' => 'nullable|string|max:255',
        ];
    }

    /**
     * Custom validation messages
     */
    public function messages(): array
    {
        return [
            'opening_cash.required' => 'Jumlah uang awal wajib diisi.',
            'opening_cash.numeric' => 'Jumlah uang awal harus berupa angka.',
            'opening_cash.min' => 'Jumlah uang awal minimal 0.',
            'opening_cash.max' => 'Jumlah uang awal terlalu besar.',
            'opening_note.string' => 'Catatan harus berupa teks.',
            'opening_note.max' => 'Catatan maksimal 255 karakter.',
        ];
    }

    /**
     * Attribute names for error messages
     */
    public function attributes(): array
    {
        return [
            'opening_cash' => 'jumlah uang awal',
            'opening_note' => 'catatan pembukaan',
        ];
    }
}