<?php

namespace App\Application\Inventory\UseCases;

use App\Application\Inventory\DTOs\StockAdjustmentDTO;
use App\Domain\Inventory\Repositories\InventoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockAdjustmentUseCase
{
    public function __construct(
        private readonly InventoryRepositoryInterface $repository
    ) {}

    public function execute(User $actor, StockAdjustmentDTO $dto): StockMovement
    {
        return DB::transaction(function () use ($actor, $dto) {
            $product = $this->repository->findProductById($dto->productId);

            if (! $product) {
                throw new ModelNotFoundException('Product not found.');
            }

            $stockBefore = (int) $product->current_stock;

            $stockAfter = match ($dto->adjustmentType) {
                'INCREASE' => $stockBefore + $dto->quantity,
                'DECREASE' => $stockBefore - $dto->quantity,
                'SET' => $dto->quantity,
                default => $stockBefore,
            };

            if ($stockAfter < 0) {
                throw ValidationException::withMessages([
                    'quantity' => ['Stock cannot be negative.'],
                ]);
            }

            $movementType = match ($dto->adjustmentType) {
                'INCREASE' => 'STOCK_IN',
                'DECREASE' => 'STOCK_OUT',
                'SET' => 'STOCK_OPNAME',
            };

            $this->repository->updateProductStock($product, $stockAfter);

            return $this->repository->createMovement([
                'product_id' => $product->id,
                'user_id' => $actor->id,
                'sale_id' => null,
                'type' => $movementType,
                'adjustment_type' => $dto->adjustmentType,
                'quantity' => abs($stockAfter - $stockBefore),
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => $dto->notes,
            ]);
        });
    }
}
