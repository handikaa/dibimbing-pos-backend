<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentProductRepository implements ProductRepositoryInterface
{

    // Tambahkan property model
    protected Product $model;

    // Constructor
    public function __construct(Product $model)
    {
        $this->model = $model;
    }

    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
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
            ->when(isset($filters['is_active']), function ($query) use ($filters) {
                $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
            })
            ->when(isset($filters['low_stock']), function ($query) use ($filters) {
                if (filter_var($filters['low_stock'], FILTER_VALIDATE_BOOLEAN)) {
                    $query->whereColumn('current_stock', '<=', 'minimum_stock');
                }
            })
            ->latest()
            ->paginate($perPage);
    }

    public function searchForPos(array $filters = []): Collection
    {
        return Product::query()
            ->with('category')
            ->where('is_active', true)
            ->where('current_stock', '>', 0)
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($filters['barcode'] ?? null, function ($query, string $barcode) {
                $query->where('barcode', $barcode);
            })
            ->when($filters['category_id'] ?? null, function ($query, int|string $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->orderBy('name')
            ->limit(50)
            ->get();
    }

    public function findById(int $id): ?Product
    {
        return Product::query()
            ->with('category')
            ->find($id);
    }

    public function findByBarcode(string $barcode): ?Product
    {
        return $this->model->where('barcode', $barcode)->first();
    }

    public function create(array $data): Product
    {
        return Product::query()
            ->create($data)
            ->load('category');
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->refresh()->load('category');
    }

    public function deactivate(Product $product): Product
    {
        $product->forceFill([
            'is_active' => false,
        ])->save();

        return $product->refresh()->load('category');
    }
}
