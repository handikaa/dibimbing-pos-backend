<?php

namespace App\Application\Rack\UseCases;

use App\Domain\Rack\Repositories\RackRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Rack;
use App\Infrastructure\Authorization\PermissionGuard;

class DeactivateRackUseCase
{
    public function __construct(
        private readonly RackRepositoryInterface $repository
    ) {}

    /**
     * Deactivate rack
     * Permission: rack.deactivate
     *
     * @throws AuthorizationException
     */
    public function execute(Rack $rack): bool
    {
        // ✅ Check permission FIRST
        PermissionGuard::check(
            auth()->user(),
            'rack.deactivate',
            'You do not have permission to deactivate racks'
        );

        return $this->repository->deactivate($rack);
    }
}
