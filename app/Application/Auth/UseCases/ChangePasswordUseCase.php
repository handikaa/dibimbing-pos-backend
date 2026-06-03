<?php

namespace App\Application\Auth\UseCases;

use App\Models\User;
use App\Application\Auth\DTOs\ChangePasswordDTO;
use App\Domain\User\Exceptions\IncorrectPasswordException;
use App\Domain\Exceptions\UnauthorizedSessionAccessException;
use Illuminate\Support\Facades\Hash;

class ChangePasswordUseCase
{
    /**
     * Change user password
     *
     * @param User $user
     * @param ChangePasswordDTO $dto
     * @return bool
     *
     * @throws IncorrectPasswordException
     * @throws UnauthorizedSessionAccessException
     */
    public function execute(User $user, ChangePasswordDTO $dto): bool
    {
        // Check permission
        if (!$user->hasPermissionTo('profile.change_password')) {
            throw new UnauthorizedSessionAccessException();
        }

        // Verify current password
        if (!Hash::check($dto->currentPassword, $user->password)) {
            throw new IncorrectPasswordException();
        }

        // Update password
        $user->forceFill([
            'password' => Hash::make($dto->newPassword),
        ])->save();

        return true;
    }
}