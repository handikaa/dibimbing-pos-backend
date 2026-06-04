<?php

namespace App\Application\Pos\UseCases;

use App\Application\Pos\DTOs\OpenBillCheckoutMidtransDTO;
use App\Domain\Pos\Repositories\PosRepositoryInterface;
use App\Domain\Sales\Repositories\SalesRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Sale;
use App\Models\User;
use App\Services\MidtransService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OpenBillCheckoutMidtransUseCase
{
    public function __construct(
        private readonly SalesRepositoryInterface $salesRepository,
        private readonly PosRepositoryInterface $posRepository,
        private readonly MidtransService $midtransService,
    ) {}

    public function execute(User $actor, OpenBillCheckoutMidtransDTO $dto): Sale
    {
        return DB::transaction(function () use ($actor, $dto) {
            $sale = $this->salesRepository->findById($dto->saleId);

            if (! $sale) {
                throw new ModelNotFoundException('Sale not found.');
            }

            if ($sale->status !== 'UNPAID') {
                throw ValidationException::withMessages([
                    'sale' => ['Only UNPAID bill can be checked out with Midtrans.'],
                ]);
            }

            if ($sale->cashier_id !== $actor->id) {
                throw ValidationException::withMessages([
                    'sale' => ['You are not allowed to checkout this bill.'],
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

            if ($grandTotal <= 0) {
                throw ValidationException::withMessages([
                    'grand_total' => ['Grand total must be greater than zero.'],
                ]);
            }

            $midtransItems = $this->buildMidtransItems($sale);

            $snapToken = $this->midtransService->generateSnapToken(
                orderId: $sale->sale_number,
                grossAmount: $grandTotal,
                customerDetails: [
                    'first_name' => $sale->customer_name,
                    'phone' => $sale->customer_phone,
                ],
                items: $midtransItems
            );

            $paymentUrl = "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$snapToken}";

            $this->posRepository->createPayment([
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

            $sale->forceFill([
                'status' => 'PENDING_PAYMENT',
                'payment_method' => 'MIDTRANS',
                'paid_amount' => 0,
                'change_amount' => 0,
                'paid_at' => null,
            ])->save();

            return $sale->refresh()->load([
                'items',
                'payment',
                'cashier',
                'cashierSession',
            ]);
        });
    }

    private function buildMidtransItems(Sale $sale): array
    {
        $items = [];

        foreach ($sale->items as $item) {
            $items[] = [
                'id' => (string) $item->product_id,
                'price' => (int) $item->selling_price,
                'quantity' => (int) $item->quantity,
                'name' => Str::limit($item->product_name, 50, ''),
            ];

            if ((int) $item->discount_amount > 0) {
                $items[] = [
                    'id' => "DISC-ITEM-{$item->id}",
                    'price' => -1 * (int) $item->discount_amount,
                    'quantity' => 1,
                    'name' => Str::limit("Discount {$item->product_name}", 50, ''),
                ];
            }
        }

        return $items;
    }
}