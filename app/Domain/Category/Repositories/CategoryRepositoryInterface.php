<?php

namespace App\Domain\Category\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function getActive(): Collection;

    public function findById(int $id): ?Category;

    public function create(array $data): Category;

    public function update(Category $category, array $data): Category;

    public function deactivate(Category $category): Category;
}