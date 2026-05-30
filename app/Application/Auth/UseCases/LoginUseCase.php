<?php

namespace App\Application\Auth\UseCases;

use App\Application\Auth\DTOs\LoginDTO;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginUseCase
{
    public function execute(LoginDTO $dto): array
    {
        $user = User::query()
            ->where('email', $dto->email)
            ->first();

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Akun tidak aktif.'],
            ]);
        }

        $token = $user->createToken($dto->deviceName)->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }
}