<?php

namespace App\Application\User\UseCases;

use App\Application\User\DTOs\RegisterUserDTO;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterUserWithoutEmailUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $repository
    ) {}

    public function execute(RegisterUserDTO $dto): User
    {
        return DB::transaction(function () use ($dto) {
            $user = $this->repository->create([
                'name' => $dto->name,
                'email' => $dto->email,
                'phone' => $dto->phone,
                'password' => Hash::make('sakupos123'),
                'is_active' => false,
                'activation_token' => null,
                'activation_token_expires_at' => null,
            ]);

            $user->assignRole($dto->role);

            return $user->load('roles');
        });
    }
}