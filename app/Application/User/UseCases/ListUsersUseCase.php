<?php

namespace App\Application\User\UseCases;

use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListUsersUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $repository
    ) {}

    public function execute(User $actor, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        if ($actor->hasRole('ADMIN')) {
            $filters['role'] = 'CASHIER';
        }

        return $this->repository->paginate($filters, $perPage);
    }
}
