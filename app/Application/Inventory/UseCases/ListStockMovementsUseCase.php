<?php

namespace App\Application\Inventory\UseCases;

use App\Domain\Inventory\Repositories\InventoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStockMovementsUseCase
{
    public function __construct(
        private readonly InventoryRepositoryInterface $repository
    ) {
    }

    public function execute(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginateMovements($filters, $perPage);
    }
}