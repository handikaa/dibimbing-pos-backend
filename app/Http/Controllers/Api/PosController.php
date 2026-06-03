<?php

namespace App\Http\Controllers\Api;

use App\Application\Pos\UseCases\SearchProductsForPosUseCase;
use App\Application\Pos\DTOs\SearchPosProductDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\SearchPosProductRequest;
use App\Http\Requests\Pos\OpenBillRequest;
use App\Http\Resources\Pos\PosProductResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Application\Pos\DTOs\CheckoutCashDTO;
use App\Application\Pos\DTOs\OpenBillDTO;
use App\Application\Pos\UseCases\CheckoutCashUseCase;
use App\Application\Pos\UseCases\OpenBillUseCase;
use App\Http\Requests\Pos\CheckoutCashRequest;
use App\Http\Resources\Sales\SaleResource;


class PosController extends Controller
{
    public function products(
        SearchPosProductRequest $request,
        SearchProductsForPosUseCase $useCase
    ): JsonResponse {
        $dto = SearchPosProductDTO::fromArray($request->validated());

        $products = $useCase->execute($dto);

        return ApiResponse::success(
            data: PosProductResource::collection($products),
            message: 'POS products retrieved successfully'
        );
    }

    public function checkoutCash(
        CheckoutCashRequest $request,
        CheckoutCashUseCase $useCase
    ): JsonResponse {
        $sale = $useCase->execute(
            actor: $request->user(),
            dto: CheckoutCashDTO::fromArray($request->validated())
        );

        return ApiResponse::success(
            data: new SaleResource($sale),
            message: 'Cash checkout completed successfully',
            code: 201
        );
    }

    public function openBill(OpenBillRequest $request, OpenBillUseCase $useCase): JsonResponse
    {
        $dto = OpenBillDTO::fromRequest($request->validated());
        $sale = $useCase->execute($request->user(), $dto);

        return response()->json([
            'success' => true,
            'message' => 'Open Bill created successfully',
            'data' => new SaleResource($sale)
        ]);
    }
}
