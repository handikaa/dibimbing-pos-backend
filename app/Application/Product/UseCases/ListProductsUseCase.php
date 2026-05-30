<?php

namespace App\Application\Product\UseCases;

use App\Domain\Product\Repositories\ProductRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListProductsUseCase
{
    public function __construct(
        private readonly ProductRepositoryInterface $repository
    ) {
    }

    public function execute(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }
}