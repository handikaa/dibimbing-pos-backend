<?php

namespace App\Services;

use Midtrans\Snap;
use Midtrans\Config;
use Illuminate\Support\Str;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = config('midtrans.server_key'); // <- pastikan ini tidak null
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Generate Snap Token for a sale
     *
     * @param string $orderId
     * @param float $grossAmount
     * @param array $customerDetails ['first_name' => '', 'phone' => '']
     * @param array $items optional, format Midtrans items
     * @return string snap_token
     */
    public function generateSnapToken(
        string $orderId,
        float $grossAmount,
        array $customerDetails = [],
        array $items = []
    ): string {
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => $customerDetails,
            'item_details' => $items,
        ];

        return Snap::getSnapToken($params);
    }

    /**
     * Generate payment URL (optional)
     *
     * @param string $orderId
     * @param float $grossAmount
     * @param array $customerDetails
     * @param array $items
     * @return string
     */
    public function generatePaymentUrl(
        string $orderId,
        float $grossAmount,
        array $customerDetails = [],
        array $items = []
    ): string {
        $snapToken = $this->generateSnapToken($orderId, $grossAmount, $customerDetails, $items);
        return "https://app.sandbox.midtrans.com/snap/v2/vtweb/{$snapToken}";
    }
}
