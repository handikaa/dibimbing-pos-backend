<?php

namespace App\Application\User\UseCases;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DeactivateUserUseCase
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

        if ($user->id === $actor->id) {
            throw new AuthorizationException('You cannot deactivate your own account.');
        }

        if ($user->hasRole('OWNER')) {
            throw new AuthorizationException('OWNER account cannot be deactivated.');
        }

        if ($actor->hasRole('ADMIN') && ! $user->hasRole('CASHIER')) {
            throw new AuthorizationException('ADMIN can only deactivate CASHIER users.');
        }

        return $this->repository->deactivate($user);
    }
}
