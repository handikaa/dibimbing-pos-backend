<?php

namespace App\Application\Pos\DTOs;

readonly class OpenBillDTO
{
    public function __construct(
        public ?string $customerName = null,
        public ?string $customerPhone = null,
        public ?string $tableCode = null,
        public ?string $notes = null,
        public array $items = [] // array of product + quantity + discount
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            customerName: $data['customer_name'] ?? null,
            customerPhone: $data['customer_phone'] ?? null,
            tableCode: $data['table_code'] ?? null,
            notes: $data['notes'] ?? null,
            items: $data['items'] ?? []
        );
    }
}
