<?php

namespace App\Application\Pos\UseCases;

use App\Application\Pos\DTOs\OpenBillDTO;
use App\Domain\Pos\Repositories\PosRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OpenBillUseCase
{
    public function __construct(
        private readonly PosRepositoryInterface $repository
    ) {}

    public function execute(User $actor, OpenBillDTO $dto): Sale
    {
        $session = $this->repository->findActiveSessionByCashierId($actor->id);

        if (! $session) {
            throw new ModelNotFoundException('No active cashier session found.');
        }


        return DB::transaction(function () use ($actor, $dto, $session) {
            $dailySequence = $this->repository->getTodaySalesCount() + 1;


            $orderCode = $this->generateOrderCode(
                customerName: $dto->customerName ?? 'X',
                customerPhone: $dto->customerPhone ?? '000',
                dailySequence: $dailySequence,
                tableCode: $dto->tableCode ?? 'NA'
            );

            $sale = $this->repository->createSale([
                'cashier_session_id' => $session->id,
                'cashier_id' => $actor->id,
                'customer_name' => $dto->customerName,
                'customer_phone' => $dto->customerPhone,
                'table_code' => $dto->tableCode,
                'notes' => $dto->notes,
                'subtotal' => 0,
                'discount_total' => 0,
                'tax_total' => 0,
                'grand_total' => 0,
                'paid_amount' => 0,
                'change_amount' => 0,
                'payment_method' => 'UNPAID',
                'status' => 'UNPAID',
                'sale_number' => $this->repository->generateSaleNumber(),
                'order_code' => $orderCode,
            ]);

            $subtotal = 0;
            $discountTotal = 0;

            foreach ($dto->items as $item) {
                $product = $this->repository->findActiveProductById((int) $item['product_id']);

                // If product not found or inactive, throw error
                if (! $product) {
                    throw new ModelNotFoundException('Product not found.');
                }

                
                $quantity = (int) $item['quantity'];
                $discountAmount = (float) ($item['discount_amount'] ?? 0);
                
                // Check stock availability
                if ($product->current_stock < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => ["Stock for {$product->name} is not enough. Current stock: {$product->current_stock}"],
                    ]);
                }

                $grossLineTotal = (float) $product->selling_price * $quantity;

                if ($discountAmount > $grossLineTotal) {
                    throw ValidationException::withMessages([
                        'discount_amount' => ['Discount cannot be greater than line total.'],
                    ]);
                }

                $lineTotal = $grossLineTotal - $discountAmount;

                $this->repository->createSaleItem([
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

            $grandTotal = $subtotal - $discountTotal;

            $sale->forceFill([
                'subtotal' => $subtotal,
                'discount_total' => $discountTotal,
                'tax_total' => 0,
                'grand_total' => $grandTotal,
            ])->save();

            return $sale->load(['items', 'payment', 'cashier', 'cashierSession']);
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
