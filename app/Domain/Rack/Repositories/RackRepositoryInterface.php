<?php

namespace App\Domain\Rack\Repositories;

use App\Infrastructure\Persistence\Eloquent\Models\Rack;
use Illuminate\Pagination\LengthAwarePaginator;

interface RackRepositoryInterface
{
    /**
     * Get all racks with pagination
     */
    public function paginate(int $perPage = 10, array $filters = []): LengthAwarePaginator;

    /**
     * Get rack by ID
     */
    public function findById(int $id): ?Rack;

    /**
     * Get rack by code
     */
    public function findByCode(string $code): ?Rack;

    /**
     * Create new rack
     */
    public function create(array $data): Rack;

    /**
     * Update rack
     */
    public function update(Rack $rack, array $data): Rack;

    /**
     * Deactivate rack (soft delete)
     */
    public function deactivate(Rack $rack): bool;

    /**
     * Get rack with products
     */
    public function findByIdWithProducts(int $id): ?Rack;
}