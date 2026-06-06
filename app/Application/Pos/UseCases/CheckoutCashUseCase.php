<?php

namespace App\Application\Pos\UseCases;

use App\Application\Pos\DTOs\CheckoutCashDTO;
use App\Domain\Pos\Repositories\PosRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutCashUseCase
{
    public function __construct(
        private readonly PosRepositoryInterface $repository
    ) {}

    public function execute(User $actor, CheckoutCashDTO $dto)
    {
        return DB::transaction(function () use ($actor, $dto) {
            $session = $this->repository->findActiveSessionByCashierId($actor->id);

            if (! $session) {
                throw ValidationException::withMessages([
                    'cashier_session' => ['Please open cashier session before checkout.'],
                ]);
            }

            $saleItems = [];
            $subtotal = 0;
            $discountTotal = 0;

            foreach ($dto->items as $itemDto) {
                $product = $this->repository->findActiveProductById($itemDto->productId);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => ["Product ID {$itemDto->productId} is not available."],
                    ]);
                }

                if ($product->current_stock < $itemDto->quantity) {
                    throw ValidationException::withMessages([
                        'items' => ["Stock for {$product->name} is not enough."],
                    ]);
                }

                $lineSubtotal = (int) $product->selling_price * $itemDto->quantity;

                if ($itemDto->discountAmount > $lineSubtotal) {
                    throw ValidationException::withMessages([
                        'items' => ["Discount for {$product->name} cannot exceed item subtotal."],
                    ]);
                }

                $lineTotal = $lineSubtotal - $itemDto->discountAmount;

                $subtotal += $lineSubtotal;
                $discountTotal += $itemDto->discountAmount;

                $saleItems[] = [
                    'product' => $product,
                    'quantity' => $itemDto->quantity,
                    'cost_price' => (int) $product->cost_price,
                    'selling_price' => (int) $product->selling_price,
                    'discount_amount' => $itemDto->discountAmount,
                    'line_total' => $lineTotal,
                ];
            }

            if ($dto->transactionDiscountAmount > ($subtotal - $discountTotal)) {
                throw ValidationException::withMessages([
                    'transaction_discount_amount' => ['Transaction discount cannot exceed total.'],
                ]);
            }

            $discountTotal += $dto->transactionDiscountAmount;

            $taxTotal = 0;
            $grandTotal = $subtotal - $discountTotal + $taxTotal;

            if ($dto->cashReceived < $grandTotal) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'cash_received' => ['Cash received is less than the grand total.']
                ]);
            }

            // Hitung kembalian
            $changeAmount = $dto->cashReceived - $grandTotal;

            $dailySequence = $this->repository->getTodaySalesCount() + 1;
            $orderCode = $this->generateOrderCode(
                customerName: $dto->customerName ?? 'Customer',
                customerPhone: $dto->customerPhone ?? '000',
                dailySequence: $dailySequence,
                tableCode: $dto->tableCode ?? 'NA'
            );
            $sale = $this->repository->createSale([
                'sale_number' => $this->repository->generateSaleNumber(),
                'order_code' => $orderCode,
                'daily_sequence' => $dailySequence,
                'cashier_session_id' => $session->id,
                'cashier_id' => $actor->id,
                'customer_name' => $dto->customerName,
                'customer_phone' => $dto->customerPhone,
                'table_code' => $dto->tableCode,
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
                'paid_amount' => $dto->cashReceived,   
                'change_amount' => $changeAmount,      
                'payment_method' => 'CASH',
                'status' => 'PAID',
                'notes' => $dto->notes,
                'paid_at' => now(),
            ]);

            foreach ($saleItems as $saleItem) {
                $product = $saleItem['product'];

                $stockBefore = (int) $product->current_stock;
                $stockAfter = $stockBefore - $saleItem['quantity'];

                $this->repository->createSaleItem([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,

                    'product_name' => $product->name,
                    'product_sku' => $product->sku,

                    'quantity' => $saleItem['quantity'],
                    'cost_price' => $saleItem['cost_price'],
                    'selling_price' => $saleItem['selling_price'],
                    'discount_amount' => $saleItem['discount_amount'],
                    'line_total' => $saleItem['line_total'],
                ]);

                $this->repository->updateProductStock($product, $stockAfter);

                $this->repository->createStockMovement([
                    'product_id' => $product->id,
                    'user_id' => $actor->id,
                    'sale_id' => $sale->id,
                    'type' => 'SALE',
                    'adjustment_type' => null,
                    'quantity' => $saleItem['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'notes' => "Sale checkout {$sale->sale_number}",
                ]);
            }

            $this->repository->createPayment([
                'sale_id' => $sale->id,
                'payment_method' => 'CASH',
                'provider' => 'CASH',
                'provider_reference' => $sale->sale_number,
                'provider_transaction_id' => null,
                'amount' => $grandTotal,
                'paid_amount' => $grandTotal,
                'change_amount' => 0,
                'status' => 'PAID',
                'payment_url' => null,
                'snap_token' => null,
                'raw_response' => null,
                'paid_at' => now(),
                'expired_at' => null,
            ]);

            $this->repository->updateCashierSessionSummary($session, $grandTotal);

            return $sale->refresh()->load([
                'items',
                'payment',
                'cashier',
                'cashierSession',
            ]);
        });
    }

    private function generateOrderCode(
        string $customerName,
        string $customerPhone,
        int $dailySequence,
        string $tableCode
    ): string {
        $initial = Str::upper(Str::substr(trim($customerName), 0, 1));

        $phoneDigits = preg_replace('/\D/', '', $customerPhone);
        $lastThreePhone = Str::substr($phoneDigits, -3);

        $sequence = str_pad((string) $dailySequence, 3, '0', STR_PAD_LEFT);

        return "{$initial}{$lastThreePhone}-{$sequence}-{$tableCode}";
    }
}
