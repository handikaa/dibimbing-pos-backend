<?php

namespace App\Application\Pos\DTOs;

readonly class OpenBillCheckoutMidtransDTO
{
    public function __construct(
        public int $saleId
    ) {}

    public static function fromSaleId(int $saleId): self
    {
        return new self($saleId);
    }
}