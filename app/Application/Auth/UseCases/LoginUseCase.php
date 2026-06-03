<?php

namespace App\Application\Auth\UseCases;

use App\Application\Auth\DTOs\LoginDTO;
use App\Domain\User\Exceptions\InvalidCredentialsException;
use App\Domain\User\Exceptions\InactiveAccountException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class LoginUseCase
{
    /**
     * Execute login
     *
     * @param LoginDTO $dto
     * @return array {token: string, user: User}
     *
     * @throws InvalidCredentialsException
     * @throws InactiveAccountException
     */
    public function execute(LoginDTO $dto): array
    {
        // Find user by email
        $user = User::query()
            ->where('email', $dto->email)
            ->first();

        // Check if user exists and password is correct
        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw new InvalidCredentialsException();
        }

        //  Check if user is active
        if (!$user->is_active) {
            throw new InactiveAccountException(
                'Akun Anda tidak aktif. Hubungi administrator.'
            );
        }

        // Create token
        $token = $user->createToken($dto->deviceName)->plainTextToken;        

        return [
            'token' => $token,
            'user' => $user,
        ];
    }
}