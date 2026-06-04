<?php

namespace App\Application\Pos\DTOs;

readonly class MidtransWebhookDTO
{
    public function __construct(
        public string $orderId,
        public string $transactionStatus,
        public ?string $transactionId,
        public ?string $paymentType,
        public ?string $fraudStatus,
        public ?string $grossAmount,
        public array $rawPayload,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            orderId: $data['order_id'] ?? '',
            transactionStatus: $data['transaction_status'] ?? '',
            transactionId: $data['transaction_id'] ?? null,
            paymentType: $data['payment_type'] ?? null,
            fraudStatus: $data['fraud_status'] ?? null,
            grossAmount: $data['gross_amount'] ?? null,
            rawPayload: $data,
        );
    }
}