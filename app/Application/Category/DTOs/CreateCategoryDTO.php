<?php

namespace App\Application\Category\DTOs;

readonly class CreateCategoryDTO
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public bool $isActive = true,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            description: $data['description'] ?? null,
            isActive: $data['is_active'] ?? true,
        );
    }
}