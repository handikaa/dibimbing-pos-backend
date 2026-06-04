<?php

namespace App\Application\Pos\UseCases;

use App\Application\Pos\UseCases\MidtransWebhookUseCase;
use App\Domain\Sales\Repositories\SalesRepositoryInterface;
use App\Application\Pos\DTOs\MidtransWebhookDTO;
use App\Infrastructure\Persistence\Eloquent\Models\Sale;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class CheckMidtransStatusUseCase
{
    public function __construct(
        private readonly SalesRepositoryInterface $salesRepository,
        private readonly MidtransWebhookUseCase $midtransWebhookUseCase,
    ) {}

    /**
     * @param int|string $saleNumber
     * @return \App\Infrastructure\Persistence\Eloquent\Models\Sale
     */
    public function execute(int $saleId): Sale
    {
        $sale = $this->salesRepository->findById($saleId);

        if (! $sale) {
            throw ValidationException::withMessages([
                'sale' => ["Sale ID {$saleId} not found."],
            ]);
        }

        $response = Http::withBasicAuth(
            config('midtrans.server_key'),
            ''
        )->get("https://api.sandbox.midtrans.com/v2/{$sale->sale_number}/status");

        if (! $response->successful()) {
            throw ValidationException::withMessages([
                'midtrans' => ['Failed to fetch status from Midtrans.'],
            ]);
        }

        $data = $response->json();

        $dto = new MidtransWebhookDTO(
            orderId: $sale->sale_number,
            transactionStatus: $data['transaction_status'] ?? '',
            transactionId: $data['transaction_id'] ?? null,
            paymentType: $data['payment_type'] ?? null,
            fraudStatus: $data['fraud_status'] ?? null,
            grossAmount: $data['gross_amount'] ?? null,
            rawPayload: $data,
        );

        $this->midtransWebhookUseCase->execute($dto);

        return $this->salesRepository->findById($saleId);
    }
}
