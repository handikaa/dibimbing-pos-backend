<?php

namespace App\Application\CashierSession\DTOs;

readonly class ListCashierSessionsDTO
{
    public function __construct(
        public ?int $userId = null,
        public ?string $status = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public int $perPage = 10
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            userId: $data['user_id'] ?? null,
            status: $data['status'] ?? null,
            dateFrom: $data['date_from'] ?? null,
            dateTo: $data['date_to'] ?? null,
            perPage: $data['per_page'] ?? 10
        );
    }
}