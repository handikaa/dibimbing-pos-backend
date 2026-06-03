<?php

namespace App\Application\Sales\DTOs;

use App\Models\User;

readonly class DeleteSaleItemDTO
{
    public function __construct(
        public User $actor,
        public int $saleId,
        public int $itemId,
    ) {}

    public static function fromRequest(User $actor, int $saleId, int $itemId): self
    {
        return new self(actor: $actor, saleId: $saleId, itemId: $itemId);
    }
}
