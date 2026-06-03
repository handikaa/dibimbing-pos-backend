<?php

namespace App\Application\User\UseCases;

use App\Domain\User\Repositories\UserRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class ListUsersUseCase
{
    public function __construct(
        private readonly UserRepositoryInterface $repository
    ) {}

    public function execute(
        array $filters = [],
        int $page = 1,
        int $perPage = 10
    ): LengthAwarePaginator {
        return $this->repository->paginate(
            page: $page,
            perPage: $perPage,
            filters: $filters
        );
    }
}
