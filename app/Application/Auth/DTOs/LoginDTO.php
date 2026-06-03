<?php

namespace App\Application\Auth\DTOs;

readonly class LoginDTO
{
    public function __construct(
        public string $email,
        public string $password,
        public string $deviceName = 'api-token',
    ) {}

    /**
     * Create from Request validated data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            password: $data['password'],
            deviceName: $data['device_name'] ?? 'api-token',
        );
    }
}