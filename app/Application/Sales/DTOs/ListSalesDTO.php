<?php

namespace App\Application\Sales\DTOs;

readonly class ListSalesDTO
{
    public function __construct(
        public ?string $search = null,
        public ?string $status = null,
        public ?string $paymentMethod = null,
        public ?int $cashierId = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null,
        public int $page = 1,
        public int $perPage = 20,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            status: $data['status'] ?? null,
            paymentMethod: $data['payment_method'] ?? null,
            cashierId: $data['cashier_id'] ?? null,
            dateFrom: $data['date_from'] ?? null,
            dateTo: $data['date_to'] ?? null,
            page: (int) ($data['page'] ?? 1),
            perPage: (int) ($data['per_page'] ?? 20)
        );
    }
}