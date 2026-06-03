<?php

namespace App\Application\CashierSession\UseCases;

use App\Application\CashierSession\DTOs\OpenCashierSessionDTO;
use App\Domain\CashierSession\Repositories\CashierSessionRepositoryInterface;
use App\Domain\CashierSession\Exceptions\SessionAlreadyActiveException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class OpenCashierSessionUseCase
{
    public function __construct(
        private readonly CashierSessionRepositoryInterface $repository
    ) {}

    /**
     * Open new cashier session
     *
     * @param OpenCashierSessionDTO $dto
     * @return object - Session yang baru dibuat
     *
     * @throws SessionAlreadyActiveException
     */
    public function execute(OpenCashierSessionDTO $dto)
    {
        return DB::transaction(function () use ($dto) {
            

            // Check Business Logic: User sudah punya active session?
            $activeSession = $this->repository->getActiveSessionByUserId($dto->user->id);
            if ($activeSession) {
                throw new SessionAlreadyActiveException();
            }

            // Generate session code
            $sessionCode = $this->generateSessionCode();

            // Create session
            $session = $this->repository->create([
                'user_id' => $dto->user->id,
                'session_code' => $sessionCode,
                'status' => 'OPEN',
                'opening_cash' => $dto->opening_cash,
                'cash_sales_total' => 0,
                'midtrans_sales_total' => 0,
                'refund_total' => 0,
                'transaction_count' => 0,
                'expected_cash' => $dto->opening_cash,
                'opening_note' => $dto->opening_note,
                'opened_at' => now(),
            ]);

            return $session;
        });
    }

    /**
     * Generate unique session code
     * Format: SES-YYYYMMDD-XXXX (e.g., SES-20260531-A1B2)
     */
    private function generateSessionCode(): string
    {
        do {
            $code = 'SES-' . now()->format('Ymd') . '-' . Str::random(4);
        } while ($this->repository->findBySessionCode($code));

        return $code;
    }
}
