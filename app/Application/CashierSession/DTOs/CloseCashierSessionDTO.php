<?php

namespace App\Application\CashierSession\DTOs;

readonly class CloseCashierSessionDTO
{
    public function __construct(
        public float $actualCash,
        public ?string $closingNote = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            actualCash: $data['actual_cash'],
            closingNote: $data['closing_note'] ?? null,
        );
    }
}