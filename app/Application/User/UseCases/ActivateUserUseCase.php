<?php

namespace App\Application\User\UseCases;

use App\Models\User;

class ActivateUserUseCase
{
    public function execute(User $user): User
    {
        if ($user->is_active) {
            return $user->load('roles');
        }

        $user->update([
            'is_active' => true,
            'activation_token' => null,
            'activation_token_expires_at' => null,
        ]);

        return $user->fresh()->load('roles');
    }
}