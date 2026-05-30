<?php

namespace App\Application\User\DTOs;

readonly class CreateUserDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
        public string $role,
        public ?string $phone = null,
        public bool $isActive = true,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            email: $data['email'],
            password: $data['password'],
            role: $data['role'],
            phone: $data['phone'] ?? null,
            isActive: $data['is_active'] ?? true,
        );
    }
}
