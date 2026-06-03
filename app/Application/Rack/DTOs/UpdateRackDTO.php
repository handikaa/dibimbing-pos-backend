<?php

namespace App\Application\Rack\DTOs;

class UpdateRackDTO
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $description = null,
        public readonly ?bool $is_active = null,
    ) {}

    /**
     * Create DTO from array
     */
    public static function from(array $data): self
    {
        return new self(
            name: $data['name'] ?? null,
            description: $data['description'] ?? null,
            is_active: $data['is_active'] ?? null,
        );
    }

    /**
     * Convert to array, only non-null values
     */
    public function toArray(): array
    {
        $data = [];

        if ($this->name !== null) {
            $data['name'] = $this->name;
        }

        if ($this->description !== null) {
            $data['description'] = $this->description;
        }

        if ($this->is_active !== null) {
            $data['is_active'] = $this->is_active;
        }

        return $data;
    }
}