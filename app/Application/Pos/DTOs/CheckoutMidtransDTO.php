<?php

namespace App\Application\Pos\DTOs;

readonly class CheckoutMidtransDTO
{
    public function __construct(
        public string $customerName,
        public string $customerPhone,
        public string $tableCode,
        public array $items,
        public int $transactionDiscountAmount = 0,
        public ?string $notes = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            customerName: $data['customer_name'],
            customerPhone: $data['customer_phone'],
            tableCode: $data['table_code'],
            items: $data['items'],
            transactionDiscountAmount: (int) ($data['transaction_discount_amount'] ?? 0),
            notes: $data['notes'] ?? null,
        );
    }
}