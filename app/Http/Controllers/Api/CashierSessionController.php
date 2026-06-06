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
use Illuminate\Http\Request;
use Throwable;
use App\Domain\CashierSession\Exceptions\SessionAlreadyActiveException;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use App\Http\Requests\CashierSession\ListCashierSessionsRequest;
use App\Application\CashierSession\DTOs\ListCashierSessionsDTO;
use App\Application\CashierSession\UseCases\ListCashierSessionsUseCase;



class CashierSessionController extends Controller
{
    public function active(
        Request $request,
        GetActiveCashierSessionUseCase $useCase
    ): JsonResponse {
        try {
            $user = $request->user();

            if (!$user) {
                return ApiResponse::error(
                    message: 'Unauthenticated',
                    code: 401
                );
            }

            $session = $useCase->execute($user->id);

            if (!$session) {
                return ApiResponse::error(
                    message: 'No active cashier session',
                    code: 404
                );
            }

            return ApiResponse::success(
                data: $session,
                message: 'Active cashier session found'
            );
        } catch (Throwable $e) {
            return ApiResponse::error(
                message: 'Failed to fetch active session',
                errors: config('app.debug') ? [
                    'exception' => $e->getMessage(),
                ] : null,
                code: 400
            );
        }
    }

    public function open(
        OpenCashierSessionRequest $request,
        OpenCashierSessionUseCase $useCase
    ) {
        try {
            // Get authenticated user
            $user = $request->user();

            // Create DTO dari validated request
            $dto = OpenCashierSessionDTO::fromRequest(
                $user,
                $request->validated()
            );

            // Execute UseCase
            $session = $useCase->execute($dto);

            // Success response
            return ApiResponse::success(
                data: $session,
                message: 'Sesi kasir berhasil dibuka',
                code: 201
            );
        } catch (SessionAlreadyActiveException $e) {


            return ApiResponse::error(
                message: $e->getMessage(),
                code: 409 // Conflict
            );
        } catch (Throwable $e) {
            // Generic error logging

            return ApiResponse::error(
                message: 'Gagal membuka sesi kasir',
                code: 500,
                errors: config('app.debug') ? [
                    'exception' => $e->getMessage(),
                ] : null
            );
        }
    }

    public function close(CloseCashierSessionRequest $request, CashierSession $session, CloseCashierSessionUseCase $useCase): JsonResponse
    {
        $dto = CloseCashierSessionDTO::fromArray([
            'actual_cash' => $request->actual_cash,
            'closing_note' => $request->closing_note,
        ]);

        $session = $useCase->execute($session, $dto);

        return ApiResponse::success(
            data: $session,
            message: 'Sesi kasir berhasil ditutup'
        );
    }

    public function index(ListCashierSessionsRequest $request, ListCashierSessionsUseCase $useCase)
    {
        try {
            $dto = ListCashierSessionsDTO::fromRequest($request->validated());

            $sessions = $useCase->execute($dto);

            return ApiResponse::successPaginated(
                data: $sessions,
                pagination: [
                    'current_page' => $sessions->currentPage(),
                    'last_page' => $sessions->lastPage(),
                    'per_page' => $sessions->perPage(),
                    'total' => $sessions->total(),
                ],
                message: 'Cashier sessions retrieved successfully'
            );
        } catch (AuthorizationException $e) {
            return ApiResponse::error(
                message: 'Forbidden',
                code: 403,
                errors: $e->getMessage()
            );
        } catch (Throwable $e) {
            return ApiResponse::error(
                message: 'Failed to retrieve cashier sessions',
                code: 500,
                errors: config('app.debug') ? ['exception' => $e->getMessage()] : null
            );
        }
    }
}
