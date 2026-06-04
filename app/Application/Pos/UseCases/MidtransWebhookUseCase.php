<?php

namespace App\Application\Pos\UseCases;

use App\Application\Pos\DTOs\MidtransWebhookDTO;
use App\Domain\Inventory\Repositories\InventoryRepositoryInterface;
use App\Domain\Pos\Repositories\PosRepositoryInterface;
use App\Domain\Sales\Repositories\SalesRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MidtransWebhookUseCase
{
    public function __construct(
        private readonly SalesRepositoryInterface $salesRepository,
        private readonly PosRepositoryInterface $posRepository,
        private readonly InventoryRepositoryInterface $inventoryRepository,
    ) {}

    public function execute(MidtransWebhookDTO $dto): void
    {
        DB::transaction(function () use ($dto) {
            $sale = $this->salesRepository->findBySaleNumber($dto->orderId);

            if (! $sale) {
                throw new ModelNotFoundException('Sale not found.');
            }

            if ($dto->transactionStatus === 'settlement') {
                $this->handlePaid($sale, $dto);
                return;
            }

            if ($dto->transactionStatus === 'capture') {
                if ($dto->fraudStatus === null || $dto->fraudStatus === 'accept') {
                    $this->handlePaid($sale, $dto);
                }

                return;
            }

            if ($dto->transactionStatus === 'expire') {
                $this->handleExpired($sale, $dto);
                return;
            }

            if (in_array($dto->transactionStatus, ['cancel', 'deny'], true)) {
                $this->handleFailed($sale, $dto);
                return;
            }

            if ($dto->transactionStatus === 'pending') {
                $this->handlePending($sale, $dto);
                return;
            }
        });
    }

    private function handlePaid($sale, MidtransWebhookDTO $dto): void
    {
        if ($sale->status === 'PAID') {
            return;
        }

        if (! in_array($sale->status, ['PENDING_PAYMENT', 'UNPAID'], true)) {
            return;
        }

        if ($sale->payment) {
            $sale->payment->forceFill([
                'status' => 'PAID',
                'provider_transaction_id' => $dto->transactionId,
                'paid_amount' => (float) $sale->grand_total,
                'raw_response' => $dto->rawPayload,
                'paid_at' => now(),
            ])->save();
        }

        foreach ($sale->items as $item) {
            $product = $item->product;

            if (! $product) {
                throw ValidationException::withMessages([
                    'items' => ["Product ID {$item->product_id} is not available."],
                ]);
            }

            $stockBefore = (int) $product->current_stock;
            $stockAfter = $stockBefore - (int) $item->quantity;

            if ($stockAfter < 0) {
                throw ValidationException::withMessages([
                    'stock' => ["Stock for {$product->name} is not enough."],
                ]);
            }

            $this->inventoryRepository->updateProductStock($product, $stockAfter);

            $this->inventoryRepository->createMovement([
                'product_id' => $product->id,
                'user_id' => $sale->cashier_id,
                'sale_id' => $sale->id,
                'type' => 'SALE',
                'adjustment_type' => null,
                'quantity' => $item->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => "Midtrans settlement {$sale->sale_number}",
            ]);
        }

        if ($sale->cashierSession) {
            $this->posRepository->updateCashierSessionSummary(
                $sale->cashierSession,
                (int) $sale->grand_total
            );
        }

        $sale->forceFill([
            'status' => 'PAID',
            'payment_method' => 'MIDTRANS',
            'paid_amount' => (float) $sale->grand_total,
            'change_amount' => 0,
            'paid_at' => now(),
        ])->save();
    }

    private function handleExpired($sale, MidtransWebhookDTO $dto): void
    {
        if ($sale->status === 'PAID') {
            return;
        }

        if ($sale->payment) {
            $sale->payment->forceFill([
                'status' => 'EXPIRED',
                'provider_transaction_id' => $dto->transactionId,
                'raw_response' => $dto->rawPayload,
                'expired_at' => now(),
            ])->save();
        }

        $sale->forceFill([
            'status' => 'CANCELLED',
            'cancelled_at' => now(),
        ])->save();
    }

    private function handleFailed($sale, MidtransWebhookDTO $dto): void
    {
        if ($sale->status === 'PAID') {
            return;
        }

        if ($sale->payment) {
            $sale->payment->forceFill([
                'status' => 'FAILED',
                'provider_transaction_id' => $dto->transactionId,
                'raw_response' => $dto->rawPayload,
            ])->save();
        }

        $sale->forceFill([
            'status' => 'CANCELLED',
            'cancelled_at' => now(),
        ])->save();
    }

    private function handlePending($sale, MidtransWebhookDTO $dto): void
    {
        if ($sale->payment) {
            $sale->payment->forceFill([
                'status' => 'PENDING',
                'provider_transaction_id' => $dto->transactionId,
                'raw_response' => $dto->rawPayload,
            ])->save();
        }

        if ($sale->status !== 'PAID') {
            $sale->forceFill([
                'status' => 'PENDING_PAYMENT',
                'payment_method' => 'MIDTRANS',
            ])->save();
        }
    }
}
