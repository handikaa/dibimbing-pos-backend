<?php

namespace App\Application\Inventory\UseCases;

use App\Domain\Inventory\Repositories\InventoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStocksUseCase
{
    public function __construct(
        private readonly InventoryRepositoryInterface $repository
    ) {
    }

    public function execute(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginateStocks($filters, $perPage);
    }
}