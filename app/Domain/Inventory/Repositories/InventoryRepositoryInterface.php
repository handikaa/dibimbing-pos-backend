<?php

namespace App\Domain\Inventory\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\Product;
use App\Infrastructure\Persistence\Eloquent\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface InventoryRepositoryInterface
{
    public function paginateStocks(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function paginateMovements(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function getLowStockProducts(): Collection;

    public function findProductById(int $productId): ?Product;

    public function updateProductStock(Product $product, int $newStock): Product;

    public function createMovement(array $data): StockMovement;
}