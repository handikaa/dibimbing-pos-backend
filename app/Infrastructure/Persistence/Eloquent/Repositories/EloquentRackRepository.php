<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Rack\Repositories\RackRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Rack;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentRackRepository implements RackRepositoryInterface
{
    public function __construct(private readonly Rack $model) {}

    /**
     * Get all racks with pagination
     */
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->withCount('products');

        // Filter by search
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%");
        }

        // Filter by active status
        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Find rack by ID
     */
    public function findById(int $id): ?Rack
    {
        return $this->model->find($id);
    }

    /**
     * Find rack by code
     */
    public function findByCode(string $code): ?Rack
    {
        return $this->model->where('code', $code)->first();
    }

    /**
     * Create new rack
     */
    public function create(array $data): Rack
    {
        return $this->model->create($data);
    }

    /**
     * Update rack
     */
    public function update(Rack $rack, array $data): Rack
    {
        $rack->update($data);
        return $rack->fresh();
    }

    /**
     * Deactivate rack (soft delete)
     */
    public function deactivate(Rack $rack): bool
    {
        $rack->update(['is_active' => false]);
        return true;
    }

    /**
     * Get rack with products
     */
    public function findByIdWithProducts(int $id): ?Rack
    {
        return $this->model->with('products')->find($id);
    }
}