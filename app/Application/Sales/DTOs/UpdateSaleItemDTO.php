<?php

namespace App\Application\Sales\DTOs;

readonly class UpdateSaleItemDTO
{
    public function __construct(
        public int $saleId,
        public int $itemId,
        public int $quantity,
        public float $discountAmount = 0,
    ) {}

    public static function fromArray(int $saleId, int $itemId, array $data): self
    {
        return new self(
            saleId: $saleId,
            itemId: $itemId,
            quantity: (int) $data['quantity'],
            discountAmount: (float) ($data['discount_amount'] ?? 0),
        );
    }
}