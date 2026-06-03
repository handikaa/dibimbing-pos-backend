<?php

namespace App\Application\Sales\UseCases;

use App\Application\Sales\DTOs\CancelSaleDTO;
use App\Domain\Sales\Repositories\SalesRepositoryInterface;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use App\Domain\Inventory\Repositories\InventoryRepositoryInterface;


class CancelSaleUseCase
{
    public function __construct(
        private readonly SalesRepositoryInterface $repository,
        private readonly InventoryRepositoryInterface $inventoryRepository
    ) {}

    public function execute(User $actor, CancelSaleDTO $dto)
    {
        return DB::transaction(function () use ($actor, $dto) {

            $sale = $this->repository->findById($dto->saleId);

            if (! $sale) {
                throw new ModelNotFoundException('Sale not found.');
            }

            // cek permission
            if (
                $actor->hasPermissionTo('sales.view_own')
                && ! $actor->hasPermissionTo('sales.view_any')
                && $sale->cashier_id !== $actor->id
            ) {
                throw new AuthorizationException(
                    'You are not allowed to cancel this sale.'
                );
            }

            if ($sale->status === 'PAID') {
                throw new \Exception('Cannot cancel a paid sale.');
            }

            $inventoryRepo = $this->inventoryRepository;

            foreach ($sale->items as $item) {
                $product = $item->product;
                $stockBefore = (int) $product->current_stock;
                $stockAfter = $stockBefore + (int) $item->quantity;

                $inventoryRepo->updateProductStock($product, $stockAfter);
                $inventoryRepo->createMovement([
                    'product_id' => $product->id,
                    'user_id' => $actor->id,
                    'sale_id' => $sale->id,
                    'type' => 'CANCELLED',
                    'adjustment_type' => null,
                    'quantity' => $item->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'notes' => 'Sale cancelled',
                ]);
            }

            // update sale status
            $sale->status = 'CANCELLED';
            $sale->save();

            return $sale->refresh();
        });
    }
}
