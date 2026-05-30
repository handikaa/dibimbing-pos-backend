<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class EloquentCategoryRepository implements CategoryRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Category::query()
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when(isset($filters['is_active']), function ($query) use ($filters) {
                $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
            })
            ->latest()
            ->paginate($perPage);
    }

    public function getActive(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function findById(int $id): ?Category
    {
        return Category::query()->find($id);
    }

    public function create(array $data): Category
    {
        return Category::query()->create($data);
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->refresh();
    }

    public function deactivate(Category $category): Category
    {
        $category->forceFill([
            'is_active' => false,
        ])->save();

        return $category->refresh();
    }
}