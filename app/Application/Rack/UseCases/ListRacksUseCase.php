<?php

namespace App\Application\Rack\UseCases;

use App\Domain\Rack\Repositories\RackRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Infrastructure\Authorization\PermissionGuard;


class ListRacksUseCase
{
    public function __construct(
        private readonly RackRepositoryInterface $repository
    ) {}

    /**
     * List all racks with pagination
     * Permission: rack.view_any
     *
     * @throws AuthorizationException
     */
    public function execute(
        int $page = 1,
        int $perPage = 10,
        array $filters = []
    ): LengthAwarePaginator {
        // ✅ Check permission
        PermissionGuard::check(
            auth()->user(),
            'rack.view_any',
            'You do not have permission to view racks'
        );

        return $this->repository->paginate($perPage, $filters);
    }
}
