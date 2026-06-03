<?php

namespace App\Application\Sales\UseCases;

use App\Domain\Sales\Repositories\SalesRepositoryInterface;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use App\Application\Sales\DTOs\DeleteSaleItemDTO;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class DeleteSaleItemUseCase
{
    public function __construct(
        private readonly SalesRepositoryInterface $salesRepository
    ) {}

    public function execute(DeleteSaleItemDTO $dto)
{
    return DB::transaction(function () use ($dto) {
        $sale = $this->salesRepository->findById($dto->saleId);

        if (! $sale) {
            throw new ModelNotFoundException('Sale not found.');
        }

        if ($sale->status !== 'UNPAID') {
            throw ValidationException::withMessages([
                'sale' => ['Only UNPAID bill can be updated.'],
            ]);
        }

        if ($sale->cashier_id !== $dto->actor->id) {
            throw ValidationException::withMessages([
                'sale' => ['You are not allowed to modify this bill.'],
            ]);
        }

        $item = $this->salesRepository->findItemById($sale->id, $dto->itemId);

        if (! $item) {
            throw new ModelNotFoundException('Sale item not found.');
        }

        $item->delete();

        $sale = $this->salesRepository->recalculateSaleTotals($sale);

        return $sale->load(['items', 'payment', 'cashier', 'cashierSession']);
    });
}
}
