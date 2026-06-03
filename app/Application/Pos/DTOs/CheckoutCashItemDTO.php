<?php

namespace App\Application\Pos\DTOs;

readonly class CheckoutCashItemDTO
{
    public function __construct(
        public int $productId,
        public int $quantity,
        public int $discountAmount = 0,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            productId: (int) $data['product_id'],
            quantity: (int) $data['quantity'],
            discountAmount: (int) ($data['discount_amount'] ?? 0),
        );
    }
}