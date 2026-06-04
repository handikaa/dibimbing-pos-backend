<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Application\Pos\DTOs\MidtransWebhookDTO;
use App\Application\Pos\UseCases\MidtransWebhookUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransController extends Controller
{
    public function webhook(
        Request $request,
        MidtransWebhookUseCase $useCase
    ): JsonResponse {

        $useCase->execute(
            MidtransWebhookDTO::fromArray(
                $request->all()
            )
        );

        return response()->json([
            'success' => true,
        ]);
    }
}