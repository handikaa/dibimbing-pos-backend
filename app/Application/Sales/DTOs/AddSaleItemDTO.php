<?php

namespace App\Application\Sales\DTOs;

readonly class AddSaleItemDTO
{
    public function __construct(
        public int $saleId,
        public array $items,
    ) {}

    public static function fromArray(int $saleId, array $data): self
    {
        return new self(
            saleId: $saleId,
            items: $data['items']
        );
    }
}
