<?php

namespace App\Application\CashierSession\DTOs;

readonly class OpenCashierSessionDTO
{
    public function __construct(
        public int $userId,
        public float $openingCash,
        public ?string $openingNote = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: $data['user_id'],
            openingCash: $data['opening_cash'],
            openingNote: $data['opening_note'] ?? null,
        );
    }
}