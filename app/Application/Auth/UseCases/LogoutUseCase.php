<?php

namespace App\Application\Auth\UseCases;

use App\Models\User;
use App\Domain\Exceptions\UnauthorizedSessionAccessException;

class LogoutUseCase
{
    /**
     * Execute logout
     *
     * @param User $user
     * @return bool
     * @throws UnauthorizedSessionAccessException
     */
    public function execute(User $user): bool
    {
        if (!$user->hasPermissionTo('auth.logout')) {
            throw new UnauthorizedSessionAccessException();
        }

        $user->tokens()->delete();

        return true;
    }
}
