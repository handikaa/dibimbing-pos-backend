<?php

namespace App\Application\User\UseCases;

use App\Application\User\DTOs\UpdateUserDTO;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UpdateUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $repository
    ) {
    }

    public function execute(User $actor, int $id, UpdateUserDTO $dto): User
    {
        $user = $this->repository->findById($id);

        if (! $user) {
            throw new ModelNotFoundException('User not found.');
        }

        if ($actor->hasRole('ADMIN') && ! $user->hasRole('CASHIER')) {
            throw new AuthorizationException('ADMIN can only update CASHIER users.');
        }

        if ($actor->hasRole('ADMIN') && $dto->role && $dto->role !== 'CASHIER') {
            throw new AuthorizationException('ADMIN can only assign CASHIER role.');
        }

        $user = $this->repository->update($user, $dto->toUpdateArray());

        if ($dto->role) {
            $user->syncRoles([$dto->role]);
        }

        return $user->refresh()->load('roles');
    }
}