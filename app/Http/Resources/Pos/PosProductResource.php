<?php

namespace App\Http\Resources\Pos;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PosProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'category_name' => $this->category?->name,
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'selling_price' => (float) $this->selling_price,
            'current_stock' => (int) $this->current_stock,
            'minimum_stock' => (int) $this->minimum_stock,
            'is_low_stock' => $this->current_stock <= $this->minimum_stock,
            'image_url' => $this->image_url,
        ];
    }
}