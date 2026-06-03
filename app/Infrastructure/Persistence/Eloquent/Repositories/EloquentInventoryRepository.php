<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Inventory\Repositories\InventoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Product;
use App\Infrastructure\Persistence\Eloquent\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentInventoryRepository implements InventoryRepositoryInterface

{
    // Tambahkan property model
    protected Product $model;

    // Constructor
    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function paginateStocks(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Product::query()
            ->with('category')
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($filters['category_id'] ?? null, function ($query, int|string $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when(isset($filters['low_stock']), function ($query) use ($filters) {
                if (filter_var($filters['low_stock'], FILTER_VALIDATE_BOOLEAN)) {
                    $query->whereColumn('current_stock', '<=', 'minimum_stock');
                }
            })
            ->orderBy('name')
            ->paginate($perPage);
    }

    public function paginateMovements(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return StockMovement::query()
            ->with(['product.category', 'user'])
            ->when($filters['product_id'] ?? null, function ($query, int|string $productId) {
                $query->where('product_id', $productId);
            })
            ->when($filters['type'] ?? null, function ($query, string $type) {
                $query->where('type', $type);
            })
            ->when($filters['date_from'] ?? null, function ($query, string $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($filters['date_to'] ?? null, function ($query, string $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->latest()
            ->paginate($perPage);
    }

    public function getLowStockProducts(): Collection
    {
        return Product::query()
            ->with('category')
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->where('is_active', true)
            ->orderBy('current_stock')
            ->get();
    }

    public function findProductById(int $productId): ?Product
    {
        return Product::query()
            ->lockForUpdate()
            ->find($productId);
    }

    public function updateProductStock(Product $product, int $newStock): Product
    {
        $product->forceFill([
            'current_stock' => $newStock,
        ])->save();

        return $product->refresh()->load('category');
    }

    public function createMovement(array $data): StockMovement
    {
        return StockMovement::query()
            ->create($data)
            ->load(['product.category', 'user']);
    }

    public function findByBarcode(string $barcode): ?Product
    {
        return $this->model->where('barcode', $barcode)->first();
    }
}
