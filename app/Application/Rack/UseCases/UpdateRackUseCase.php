<?php

namespace App\Application\Rack\UseCases;

use App\Application\Rack\DTOs\UpdateRackDTO;
use App\Domain\Rack\Repositories\RackRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Rack;
use App\Infrastructure\Authorization\PermissionGuard;


class UpdateRackUseCase
{
    public function __construct(
        private readonly RackRepositoryInterface $repository
    ) {}

    /**
     * Update rack
     * Permission: rack.update
     *
     * @throws AuthorizationException
     */
    public function execute(Rack $rack, UpdateRackDTO $dto): Rack
    {
        // ✅ Check permission FIRST
        PermissionGuard::check(
            auth()->user(),
            'rack.update',
            'You do not have permission to update racks'
        );

        return $this->repository->update($rack, $dto->toArray());
    }
}
