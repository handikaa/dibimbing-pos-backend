<?php

namespace App\Application\User\UseCases;

use App\Application\User\DTOs\CreateUserDTO;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Hash;

class CreateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $repository
    ) {}

    public function execute(User $actor, CreateUserDTO $dto): User
    {
        if ($actor->hasRole('ADMIN') && $dto->role !== 'CASHIER') {
            throw new AuthorizationException('ADMIN can only create CASHIER user.');
        }

        $user = $this->repository->create([
            'name' => $dto->name,
            'email' => $dto->email,
            'phone' => $dto->phone,
            'password' => Hash::make($dto->password),
            'is_active' => $dto->isActive,
        ]);

        $user->assignRole($dto->role);

        return $user->load('roles');
    }
}
