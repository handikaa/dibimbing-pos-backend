<?php

namespace App\Http\Resources\Sales;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'sale_number' => $this->sale_number,
            'order_code' => $this->order_code,
            'daily_sequence' => (int) $this->daily_sequence,

            'cashier_session_id' => $this->cashier_session_id,
            'cashier_id' => $this->cashier_id,
            'cashier_name' => $this->cashier?->name,

            'customer_name' => $this->customer_name,
            'customer_phone' => $this->customer_phone,
            'table_code' => $this->table_code,

            'subtotal' => (float) $this->subtotal,
            'discount_total' => (float) $this->discount_total,
            'tax_total' => (float) $this->tax_total,
            'grand_total' => (float) $this->grand_total,
            'paid_amount' => (float) $this->paid_amount,
            'change_amount' => (float) $this->change_amount,

            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'notes' => $this->notes,

            'paid_at' => $this->paid_at?->toDateTimeString(),
            'cancelled_at' => $this->cancelled_at?->toDateTimeString(),
            'created_at' => $this->created_at?->toDateTimeString(),

            'items' => SaleItemResource::collection($this->whenLoaded('items')),
            'payment' => new PaymentResource($this->whenLoaded('payment')),
        ];
    }
}