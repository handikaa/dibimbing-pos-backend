<?php

namespace App\Application\User\DTOs;

readonly class UpdateUserDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $phone = null,
        public ?bool $isActive = null,
        public ?string $role = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            phone: $data['phone'] ?? null,
            isActive: $data['is_active'] ?? null,
            role: $data['role'] ?? null,
        );
    }

    public function toUpdateArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'phone' => $this->phone,
            'is_active' => $this->isActive,
        ], fn($value) => ! is_null($value));
    }
}
