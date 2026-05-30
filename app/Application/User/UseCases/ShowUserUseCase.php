<?php

namespace App\Application\User\UseCases;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ShowUserUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $repository
    ) {}

    public function execute(User $actor, int $id): User
    {
        $user = $this->repository->findById($id);

        if (! $user) {
            throw new ModelNotFoundException('User not found.');
        }

        if ($actor->hasRole('ADMIN') && ! $user->hasRole('CASHIER')) {
            throw new AuthorizationException('ADMIN can only view CASHIER users.');
        }

        return $user;
    }
}
