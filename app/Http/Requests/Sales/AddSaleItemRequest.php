<?php

namespace App\Http\Requests\Sales;

use Illuminate\Foundation\Http\FormRequest;

class AddSaleItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pos.open');
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}