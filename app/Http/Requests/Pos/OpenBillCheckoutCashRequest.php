<?php

namespace App\Http\Requests\Pos;

use Illuminate\Foundation\Http\FormRequest;

class OpenBillCheckoutCashRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pos.checkout_cash');
    }

    public function rules(): array
    {
        return [
            'cash_received' => ['required', 'numeric', 'min:0'],
        ];
    }
}