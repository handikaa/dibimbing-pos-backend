<?php

namespace App\Application\Sales\UseCases;

use App\Application\Sales\DTOs\AddSaleItemDTO;
use App\Domain\Sales\Repositories\SalesRepositoryInterface;
use App\Domain\Pos\Repositories\PosRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AddSaleItemToBillUseCase
{
    public function __construct(
        private readonly SalesRepositoryInterface $salesRepository,
        private readonly PosRepositoryInterface $posRepository,
    ) {}

    public function execute(User $actor, AddSaleItemDTO $dto): Sale
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

            $subtotal = (float) $sale->subtotal;
            $discountTotal = (float) $sale->discount_total;

            foreach ($dto->items as $item) {
                $product = $this->posRepository->findActiveProductById((int) $item['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => ["Product ID {$item['product_id']} is not available."],
                    ]);
                }

                $quantity = (int) $item['quantity'];
                $discountAmount = (float) ($item['discount_amount'] ?? 0);

                if ($product->current_stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => ["Stock for {$product->name} is not enough."],
                    ]);
                }

                $grossLineTotal = (float) $product->selling_price * $quantity;

                if ($discountAmount > $grossLineTotal) {
                    throw ValidationException::withMessages([
                        'discount_amount' => ['Discount cannot be greater than line total.'],
                    ]);
                }

                $lineTotal = $grossLineTotal - $discountAmount;

                $this->salesRepository->createSaleItem([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'quantity' => $quantity,
                    'cost_price' => $product->cost_price,
                    'selling_price' => $product->selling_price,
                    'discount_amount' => $discountAmount,
                    'line_total' => $lineTotal,
                ]);

                $subtotal += $grossLineTotal;
                $discountTotal += $discountAmount;
            }

            $sale->forceFill([
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'grand_total' => $subtotal - $discountTotal,
            ])->save();

            return $sale->refresh()->load(['items', 'payment', 'cashier', 'cashierSession']);
        });
    }
}
