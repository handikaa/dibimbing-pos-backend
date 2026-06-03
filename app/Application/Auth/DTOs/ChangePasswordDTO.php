<?php

namespace App\Application\Auth\DTOs;

readonly class ChangePasswordDTO
{
    public function __construct(
        public string $currentPassword,
        public string $newPassword,
    ) {}

    /**
     * Create from Request validated data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            currentPassword: $data['current_password'],
            newPassword: $data['new_password'],
        );
    }
}