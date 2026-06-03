<?php

namespace App\Application\Rack\UseCases;

use App\Application\Rack\DTOs\CreateRackDTO;
use App\Domain\Rack\Repositories\RackRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Rack;
use App\Infrastructure\Authorization\PermissionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Auth\Access\AuthorizationException;
use Exception;

class CreateRackUseCase
{
    public function __construct(
        private readonly RackRepositoryInterface $repository
    ) {}

    /**
     * Create new rack
     * Permission: rack.create
     *
     * @throws AuthorizationException
     * @throws Exception
     */
    public function execute(CreateRackDTO $dto): Rack
    {
        /** @var ?Authenticatable $user */
        $user = auth()->user();

        PermissionGuard::check(
            $user,
            'rack.create',
            'You do not have permission to create racks'
        );



        // Then check business logic
        if ($this->repository->findByCode($dto->code)) {
            throw new Exception("Rack code '{$dto->code}' already exists");
        }

        return $this->repository->create($dto->toArray());
    }
}
