<?php

namespace App\Domain\Product\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function searchForPos(array $filters = []): Collection;

    public function findById(int $id): ?Product;

    public function create(array $data): Product;

    public function update(Product $product, array $data): Product;

    public function deactivate(Product $product): Product;

    public function findByBarcode(string $barcode): ?Product;
}