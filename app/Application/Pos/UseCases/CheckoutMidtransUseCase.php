<?php

namespace App\Application\Pos\UseCases;

use App\Application\Pos\DTOs\CheckoutMidtransDTO;
use App\Domain\Pos\Repositories\PosRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Sale;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutMidtransUseCase
{
    public function __construct(
        private readonly PosRepositoryInterface $repository,
        private readonly MidtransService $midtransService,
    ) {}

    public function execute(User $actor, CheckoutMidtransDTO $dto): Sale
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

            foreach ($dto->items as $item) {
                $product = $this->repository->findActiveProductById((int) $item['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages([
                        'items' => ["Product ID {$item['product_id']} is not available."],
                    ]);
                }

                $quantity = (int) $item['quantity'];
                $discountAmount = (int) ($item['discount_amount'] ?? 0);

                if ((int) $product->current_stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => ["Stock for {$product->name} is not enough."],
                    ]);
                }

                $lineSubtotal = (int) $product->selling_price * $quantity;

                if ($discountAmount > $lineSubtotal) {
                    throw ValidationException::withMessages([
                        'items' => ["Discount for {$product->name} cannot exceed item subtotal."],
                    ]);
                }

                $lineTotal = $lineSubtotal - $discountAmount;

                $subtotal += $lineSubtotal;
                $discountTotal += $discountAmount;

                $saleItems[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'cost_price' => (int) $product->cost_price,
                    'selling_price' => (int) $product->selling_price,
                    'discount_amount' => $discountAmount,
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

            $dailySequence = $this->repository->getTodaySalesCount() + 1;

            $orderCode = $this->generateOrderCode(
                customerName: $dto->customerName,
                customerPhone: $dto->customerPhone,
                dailySequence: $dailySequence,
                tableCode: $dto->tableCode
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
                'paid_amount' => 0,
                'change_amount' => 0,
                'payment_method' => 'MIDTRANS',
                'status' => 'PENDING_PAYMENT',
                'notes' => $dto->notes,
                'paid_at' => null,
            ]);

            foreach ($saleItems as $saleItem) {
                $product = $saleItem['product'];

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
            }

            $midtransItems = $this->buildMidtransItems($saleItems, $dto->transactionDiscountAmount);

            $snapToken = $this->midtransService->generateSnapToken(
                orderId: $sale->sale_number,
                grossAmount: $grandTotal,
                customerDetails: [
                    'first_name' => $dto->customerName,
                    'phone' => $dto->customerPhone,
                ],
                items: $midtransItems
            );

            $paymentUrl = "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$snapToken}";

            $this->repository->createPayment([
                'sale_id' => $sale->id,
                'payment_method' => 'MIDTRANS',
                'provider' => 'MIDTRANS',
                'provider_reference' => $sale->sale_number,
                'provider_transaction_id' => null,
                'amount' => $grandTotal,
                'paid_amount' => 0,
                'change_amount' => 0,
                'status' => 'PENDING',
                'payment_url' => $paymentUrl,
                'snap_token' => $snapToken,
                'raw_response' => null,
                'paid_at' => null,
                'expired_at' => null,
            ]);

            return $sale->refresh()->load([
                'items',
                'payment',
                'cashier',
                'cashierSession',
            ]);
        });
    }

    private function buildMidtransItems(array $saleItems, int $transactionDiscountAmount): array
    {
        $items = [];

        foreach ($saleItems as $saleItem) {
            $product = $saleItem['product'];

            $items[] = [
                'id' => (string) $product->id,
                'price' => (int) $saleItem['selling_price'],
                'quantity' => (int) $saleItem['quantity'],
                'name' => Str::limit($product->name, 50, ''),
            ];

            if ((int) $saleItem['discount_amount'] > 0) {
                $items[] = [
                    'id' => "DISC-ITEM-{$product->id}",
                    'price' => -1 * (int) $saleItem['discount_amount'],
                    'quantity' => 1,
                    'name' => Str::limit("Discount {$product->name}", 50, ''),
                ];
            }
        }

        if ($transactionDiscountAmount > 0) {
            $items[] = [
                'id' => 'DISC-TRX',
                'price' => -1 * $transactionDiscountAmount,
                'quantity' => 1,
                'name' => 'Transaction Discount',
            ];
        }

        return $items;
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