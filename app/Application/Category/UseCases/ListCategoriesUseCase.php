<?php

namespace App\Application\Category\UseCases;

use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCategoriesUseCase
{
    public function __construct(
        private readonly CategoryRepositoryInterface $repository
    ) {}

    public function execute(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }
}
