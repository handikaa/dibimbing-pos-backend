<?php

namespace App\Application\CashierSession\DTOs;

use Illuminate\Contracts\Auth\Authenticatable;

class OpenCashierSessionDTO
{
    public function __construct(
        public readonly Authenticatable $user,
        public readonly float $opening_cash,
        public readonly ?string $opening_note = null,
    ) {}

    /**
     * Create from Request validated data
     */
    public static function fromRequest(Authenticatable $user, array $validated): self
    {
        return new self(
            user: $user,
            opening_cash: (float) $validated['opening_cash'],
            opening_note: $validated['opening_note'] ?? null,
        );
    }
}