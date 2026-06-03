<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSaleItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pos.open');
    }

    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
