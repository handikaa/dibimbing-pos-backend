<?php

namespace App\Application\CashierSession\UseCases;

use App\Application\CashierSession\DTOs\OpenCashierSessionDTO;
use App\Domain\CashierSession\Repositories\CashierSessionRepositoryInterface;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OpenCashierSessionUseCase
{
    public function __construct(
        private readonly CashierSessionRepositoryInterface $repository
    ) {}

    public function execute(OpenCashierSessionDTO $dto)
    {
        // Cek apakah user sudah punya session OPEN
        $activeSession = $this->repository->getActiveSession($dto->userId);
        if ($activeSession) {
            throw ValidationException::withMessages([
                'session' => ['User sudah memiliki session aktif.'],
            ]);
        }

        // Generate session_code
        $sessionCode = 'SES-' . now()->format('Ymd') . '-' . Str::random(4);

        // Create session
        return $this->repository->createSession([
            'user_id' => $dto->userId,
            'session_code' => $sessionCode,
            'status' => 'OPEN',
            'opening_cash' => $dto->openingCash,
            'opening_note' => $dto->openingNote,
            'opened_at' => now(),
        ]);
    }
}