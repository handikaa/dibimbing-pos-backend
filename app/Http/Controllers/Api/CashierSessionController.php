<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\CashierSession\OpenCashierSessionRequest;
use App\Http\Requests\CashierSession\CloseCashierSessionRequest;
use App\Application\CashierSession\DTOs\OpenCashierSessionDTO;
use App\Application\CashierSession\DTOs\CloseCashierSessionDTO;
use App\Application\CashierSession\UseCases\OpenCashierSessionUseCase;
use App\Application\CashierSession\UseCases\CloseCashierSessionUseCase;
use App\Application\CashierSession\UseCases\GetActiveCashierSessionUseCase;
use App\Infrastructure\Persistence\Eloquent\Models\CashierSession;

class CashierSessionController extends Controller
{
    public function active(GetActiveCashierSessionUseCase $useCase): JsonResponse
    {
        $userId = auth()->id();
        $session = $useCase->execute($userId);

        return response()->json([
            'success' => true,
            'data' => $session
        ]);
    }

    public function open(OpenCashierSessionRequest $request, OpenCashierSessionUseCase $useCase): JsonResponse
    {
        $dto = OpenCashierSessionDTO::fromArray([
            'user_id' => auth()->id(),
            'opening_cash' => $request->opening_cash,
            'opening_note' => $request->opening_note,
        ]);

        $session = $useCase->execute($dto);

        return response()->json([
            'success' => true,
            'data' => $session
        ]);
    }

    public function close(CloseCashierSessionRequest $request, CashierSession $session, CloseCashierSessionUseCase $useCase): JsonResponse
    {
        $dto = CloseCashierSessionDTO::fromArray([
            'actual_cash' => $request->actual_cash,
            'closing_note' => $request->closing_note,
        ]);

        $session = $useCase->execute($session, $dto);

        return response()->json([
            'success' => true,
            'data' => $session
        ]);
    }
}