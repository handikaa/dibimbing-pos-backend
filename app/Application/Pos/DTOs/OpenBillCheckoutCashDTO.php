<?php

namespace App\Application\Pos\DTOs;

readonly class OpenBillCheckoutCashDTO
{
    public function __construct(
        public int $saleId,
        public float $cashReceived
    ) {}

    public static function fromArray(int $saleId, array $data): self
    {
        return new self(
            saleId: $saleId,
            cashReceived: (float) $data['cash_received']
        );
    }
}