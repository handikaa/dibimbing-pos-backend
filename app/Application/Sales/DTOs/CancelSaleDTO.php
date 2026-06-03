<?php

namespace App\Application\Sales\DTOs;

readonly class CancelSaleDTO
{
    public function __construct(
        public int $saleId
    ) {}

    public static function fromId(int $saleId): self
    {
        return new self($saleId);
    }
}