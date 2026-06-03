<?php

namespace App\Application\Rack\DTOs;

class CreateRackDTO
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $description = null,
        public readonly bool $is_active = true,
    ) {}

    /**
     * Create DTO from array (from FormRequest)
     */
    public static function from(array $data): self
    {
        return new self(
            code: $data['code'],
            name: $data['name'],
            description: $data['description'] ?? null,
            is_active: $data['is_active'] ?? true,
        );
    }

    /**
     * Convert DTO to array for repository
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];
    }
}