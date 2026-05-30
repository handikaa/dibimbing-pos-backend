<?php

namespace App\Application\Inventory\DTOs;

readonly class StockAdjustmentDTO
{
    public function __construct(
        public int $productId,
        public string $adjustmentType,
        public int $quantity,
        public ?string $notes = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            productId: (int) $data['product_id'],
            adjustmentType: $data['adjustment_type'],
            quantity: (int) $data['quantity'],
            notes: $data['notes'] ?? null,
        );
    }
}