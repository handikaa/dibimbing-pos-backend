<?php

namespace App\Application\Inventory\UseCases;

use App\Domain\Inventory\Repositories\InventoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Infrastructure\Persistence\Eloquent\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ListStocksUseCase
{
    public function __construct(
        private readonly InventoryRepositoryInterface $repository
    ) {}

    public function execute(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->repository->paginateStocks($filters, $perPage);
    }

    public function getStockByBarcode(string $barcode): Product
    {
        $product = $this->repository->findByBarcode($barcode);

        if (!$product) {
            throw new ModelNotFoundException("Product with barcode {$barcode} not found");
        }

        return $product;
    }
}
