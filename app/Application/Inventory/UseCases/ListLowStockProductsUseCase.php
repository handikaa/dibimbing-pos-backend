<?php

namespace App\Application\Inventory\UseCases;

use App\Domain\Inventory\Repositories\InventoryRepositoryInterface;
use Illuminate\Support\Collection;

class ListLowStockProductsUseCase
{
    public function __construct(
        private readonly InventoryRepositoryInterface $repository
    ) {
    }

    public function execute(): Collection
    {
        return $this->repository->getLowStockProducts();
    }
}