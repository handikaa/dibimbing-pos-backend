<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Domain\Sales\Repositories\SalesRepositoryInterface;
use App\Application\Pos\UseCases\MidtransWebhookUseCase;
use App\Application\Pos\DTOs\MidtransWebhookDTO;
use Illuminate\Support\Facades\Http;

class PollMidtransStatus extends Command
{
    protected $signature = 'midtrans:poll';
    protected $description = 'Poll Midtrans API for PENDING_PAYMENT sales';

    public function __construct(
        private readonly SalesRepositoryInterface $salesRepository,
        private readonly MidtransWebhookUseCase $midtransUseCase,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $pendingSales = $this->salesRepository->listPendingPaymentSales();

        foreach ($pendingSales as $sale) {

            $response = Http::withBasicAuth(config('midtrans.server_key'), '')
                ->get("https://api.sandbox.midtrans.com/v2/{$sale->sale_number}/status");

            if (! $response->successful()) {
                $this->error("Failed to fetch status for {$sale->sale_number}");
                continue;
            }

            $data = $response->json();

            // Buat DTO seperti webhook
            $dto = new MidtransWebhookDTO(
                orderId: $sale->sale_number,
                transactionStatus: $data['transaction_status'] ?? '',
                transactionId: $data['transaction_id'] ?? null,
                paymentType: $data['payment_type'] ?? null,
                fraudStatus: $data['fraud_status'] ?? null,
                grossAmount: $data['gross_amount'] ?? null,
                rawPayload: $data
            );

            // Jalankan use case webhook
            $this->midtransUseCase->execute($dto);

            $this->info("Checked sale {$sale->sale_number}");
        }

        return 0;
    }
}
