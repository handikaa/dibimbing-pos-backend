<?php

namespace App\Http\Resources\Sales;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payment_method' => $this->payment_method,
            'provider' => $this->provider,
            'provider_reference' => $this->provider_reference,
            'provider_transaction_id' => $this->provider_transaction_id,
            'amount' => (float) $this->amount,
            'paid_amount' => (float) $this->paid_amount,
            'change_amount' => (float) $this->change_amount,
            'status' => $this->status,
            'payment_url' => $this->payment_url,
            'snap_token' => $this->snap_token,
            'paid_at' => $this->paid_at?->toDateTimeString(),
            'expired_at' => $this->expired_at?->toDateTimeString(),
        ];
    }
}