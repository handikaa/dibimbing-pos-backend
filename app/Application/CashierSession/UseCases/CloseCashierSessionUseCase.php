<?php

namespace App\Application\CashierSession\UseCases;

use App\Application\CashierSession\DTOs\CloseCashierSessionDTO;
use App\Domain\CashierSession\Repositories\CashierSessionRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\CashierSession;

class CloseCashierSessionUseCase
{
    public function __construct(
        private readonly CashierSessionRepositoryInterface $repository
    ) {}

    public function execute(CashierSession $session, CloseCashierSessionDTO $dto)
    {
        // Hitung expected_cash dan cash_difference
        $cashSalesTotal = $session->sales()->where('payment_method', 'CASH')
                                           ->where('status', 'PAID')
                                           ->sum('paid_amount');

        $refundTotal = $session->sales()->where('status', 'REFUNDED')->sum('grand_total');

        $expectedCash = $session->opening_cash + $cashSalesTotal - $refundTotal;

        $data = [
            'cash_sales_total' => $cashSalesTotal,
            'refund_total' => $refundTotal,
            'expected_cash' => $expectedCash,
            'actual_cash' => $dto->actualCash,
            'cash_difference' => $dto->actualCash - $expectedCash,
            'closing_note' => $dto->closingNote,
            'status' => 'CLOSED',
            'closed_at' => now(),
        ];

        return $this->repository->closeSession($session, $data);
    }
}