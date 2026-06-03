<?php

namespace App\Application\Sales\UseCases;

use App\Application\Sales\DTOs\ShowSaleDTO;
use App\Domain\Sales\Repositories\SalesRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Auth\Access\AuthorizationException;

class ShowSaleUseCase
{
    public function __construct(
        private readonly SalesRepositoryInterface $repository
    ) {}

    public function execute(
        User $actor,
        ShowSaleDTO $dto
    ): Sale {

        $sale = $this->repository->findById($dto->saleId);

        if (! $sale) {
            throw new ModelNotFoundException('Sale not found.');
        }

        if (
            $actor->hasPermissionTo('sales.view_own')
            && ! $actor->hasPermissionTo('sales.view_any')
            && $sale->cashier_id !== $actor->id
        ) {
            throw new AuthorizationException(
                'You are not allowed to access this sale.'
            );
        }

        return $sale;
    }
}
