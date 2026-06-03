<?php

namespace App\Application\Pos\DTOs;

readonly class SearchPosProductDTO
{
    public function __construct(
        public ?string $search = null,
        public ?string $barcode = null,
        public ?int $categoryId = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            search: $data['search'] ?? null,
            barcode: $data['barcode'] ?? null,
            categoryId: isset($data['category_id']) ? (int)$data['category_id'] : null
        );
    }
}