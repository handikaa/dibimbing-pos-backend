<?php

namespace App\Application\Rack\UseCases;

use App\Domain\Rack\Repositories\RackRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Rack;
use App\Infrastructure\Authorization\PermissionGuard;


class ShowRackUseCase
{
    public function __construct(
        private readonly RackRepositoryInterface $repository
    ) {}

    /**
     * Show rack detail with products
     * Permission: rack.view_any
     *
     * @throws AuthorizationException
     */
    public function execute(int $id): ?Rack
    {
        // ✅ Check permission
        PermissionGuard::check(
            auth()->user(),
            'rack.view_any',
            'You do not have permission to view racks'
        );

        return $this->repository->findByIdWithProducts($id);
    }
}
