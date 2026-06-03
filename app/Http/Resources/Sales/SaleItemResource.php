<?php

namespace App\Http\Resources\Sales;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SaleItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product_name' => $this->product_name,
            'product_sku' => $this->product_sku,
            'quantity' => (int) $this->quantity,
            'cost_price' => (float) $this->cost_price,
            'selling_price' => (float) $this->selling_price,
            'discount_amount' => (float) $this->discount_amount,
            'line_total' => (float) $this->line_total,
        ];
    }
}