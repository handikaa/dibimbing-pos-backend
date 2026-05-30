<?php

namespace App\Http\Resources\Inventory;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductStockResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->id,
            'category' => $this->whenLoaded('category', function () {
                return [
                    'id' => $this->category->id,
                    'name' => $this->category->name,
                ];
            }),
            'name' => $this->name,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'current_stock' => (int) $this->current_stock,
            'minimum_stock' => (int) $this->minimum_stock,
            'is_low_stock' => $this->current_stock <= $this->minimum_stock,
            'is_active' => (bool) $this->is_active,
        ];
    }
}