<?php

namespace App\Application\Pos\UseCases;

use App\Application\Pos\DTOs\OpenBillCheckoutCashDTO;
use App\Domain\Sales\Repositories\SalesRepositoryInterface;
use App\Domain\Pos\Repositories\PosRepositoryInterface;
use App\Domain\Inventory\Repositories\InventoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;



class OpenBillCheckoutCashUseCase
{
    public function __construct(
        private readonly SalesRepositoryInterface $salesRepository,
        private readonly PosRepositoryInterface $posRepository,
        private readonly InventoryRepositoryInterface $inventoryRepository,
    ) {}

    public function execute(User $actor, OpenBillCheckoutCashDTO $dto): Sale
    {
        return DB::transaction(function () use ($actor, $dto) {
            $sale = $this->salesRepository->findById($dto->saleId);

            if (! $sale) {
                throw new ModelNotFoundException('Sale not found.');
            }

            if ($sale->status !== 'UNPAID') {
                throw ValidationException::withMessages([
                    'sale' => ['Only UNPAID bill can be checked out.'],
                ]);
            }

            if ($sale->items->isEmpty()) {
                throw ValidationException::withMessages([
                    'items' => ['Bill has no items.'],
                ]);
            }

            foreach ($sale->items as $item) {
                $product = $item->product;

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => ["Product ID {$item->product_id} is not available."],
                    ]);
                }

                if ((int) $product->current_stock < (int) $item->quantity) {
                    throw ValidationException::withMessages([
                        'items' => ["Stock for {$product->name} is not enough."],
                    ]);
                }
            }

            $grandTotal = (float) $sale->grand_total;

            if ($dto->cashReceived < $grandTotal) {
                throw ValidationException::withMessages([
                    'cash_received' => ['Cash received is less than grand total.'],
                ]);
            }

            $changeAmount = $dto->cashReceived - $grandTotal;

            $this->posRepository->createPayment([
                'sale_id' => $sale->id,
                'payment_method' => 'CASH',
                'provider' => null,
                'provider_reference' => null,
                'provider_transaction_id' => null,
                'amount' => $grandTotal,
                'paid_amount' => $dto->cashReceived,
                'change_amount' => $changeAmount,
                'status' => 'PAID',
                'paid_at' => now(),
            ]);

            foreach ($sale->items as $item) {
                $product = $item->product;

                $stockBefore = (int) $product->current_stock;
                $stockAfter = $stockBefore - (int) $item->quantity;

                $this->inventoryRepository->updateProductStock($product, $stockAfter);

                $this->inventoryRepository->createMovement([
                    'product_id' => $product->id,
                    'user_id' => $actor->id,
                    'sale_id' => $sale->id,
                    'type' => 'SALE',
                    'adjustment_type' => null,
                    'quantity' => $item->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'notes' => 'Open bill cash checkout',
                ]);
            }

            $this->posRepository->updateCashierSessionSummary(
                $sale->cashierSession,
                (int) $grandTotal
            );

            $sale->forceFill([
                'status' => 'PAID',
                'payment_method' => 'CASH',
                'paid_amount' => $dto->cashReceived,
                'change_amount' => $changeAmount,
                'paid_at' => now(),
            ])->save();

            return $sale->refresh()->load([
                'items',
                'payment',
                'cashier',
                'cashierSession',
            ]);
        });
    }
}
