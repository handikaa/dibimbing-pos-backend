<?php

namespace App\Application\Auth\UseCases;

use App\Application\Auth\DTOs\ChangePasswordDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ChangePasswordUseCase
{
    public function execute(User $user, ChangePasswordDTO $dto): void
    {
        if (! Hash::check($dto->currentPassword, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Current password is incorrect.'],
            ]);
        }

        $user->forceFill([
            'password' => Hash::make($dto->newPassword),
        ])->save();
    }
}