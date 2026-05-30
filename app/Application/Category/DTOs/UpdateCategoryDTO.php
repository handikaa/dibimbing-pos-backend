<?php

namespace App\Application\Category\DTOs;

readonly class UpdateCategoryDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $description = null,
        public ?bool $isActive = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            isActive: $data['is_active'] ?? null,
        );
    }

    public function toUpdateArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->isActive,
        ], fn($value) => ! is_null($value));
    }
}
