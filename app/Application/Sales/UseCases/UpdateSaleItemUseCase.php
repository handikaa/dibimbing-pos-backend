<?php

namespace App\Application\Sales\UseCases;

use App\Application\Sales\DTOs\UpdateSaleItemDTO;
use App\Domain\Sales\Repositories\SalesRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UpdateSaleItemUseCase
{
    public function __construct(
        private readonly SalesRepositoryInterface $salesRepository
    ) {}

    public function execute(User $actor, UpdateSaleItemDTO $dto): Sale
    {
        return DB::transaction(function () use ($actor, $dto) {
            $sale = $this->salesRepository->findById($dto->saleId);

            if (! $sale) {
                throw new ModelNotFoundException('Sale not found.');
            }

            if ($sale->status !== 'UNPAID') {
                throw ValidationException::withMessages([
                    'sale' => ['Only UNPAID bill can be updated.'],
                ]);
            }

            if ($sale->cashier_id !== $actor->id) {
                throw ValidationException::withMessages([
                    'sale' => ['You are not allowed to update this bill.'],
                ]);
            }

            $item = $this->salesRepository->findItemById(
                saleId: $sale->id,
                itemId: $dto->itemId
            );

            if (! $item) {
                throw new ModelNotFoundException('Sale item not found.');
            }

            $grossLineTotal = (float) $item->selling_price * $dto->quantity;

            if ($dto->discountAmount > $grossLineTotal) {
                throw ValidationException::withMessages([
                    'discount_amount' => ['Discount cannot be greater than line total.'],
                ]);
            }

            if ($item->product && $item->product->current_stock < $dto->quantity) {
                throw ValidationException::withMessages([
                    'quantity' => ["Stock for {$item->product->name} is not enough."],
                ]);
            }

            $this->salesRepository->updateSaleItem($item, [
                'quantity' => $dto->quantity,
                'discount_amount' => $dto->discountAmount,
                'line_total' => $grossLineTotal - $dto->discountAmount,
            ]);

            $sale = $this->salesRepository->recalculateSaleTotals($sale);

            return $sale->load(['items', 'payment', 'cashier', 'cashierSession']);
        });
    }
}
