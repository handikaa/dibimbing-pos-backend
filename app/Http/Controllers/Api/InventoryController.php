<?php

namespace App\Http\Controllers\Api;

use App\Application\Inventory\DTOs\StockAdjustmentDTO;
use App\Application\Inventory\UseCases\ListLowStockProductsUseCase;
use App\Application\Inventory\UseCases\ListStockMovementsUseCase;
use App\Application\Inventory\UseCases\ListStocksUseCase;
use App\Application\Inventory\UseCases\StockAdjustmentUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Inventory\StockAdjustmentRequest;
use App\Http\Resources\Inventory\ProductStockResource;
use App\Http\Resources\Inventory\StockMovementResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function stocks(Request $request, ListStocksUseCase $useCase): JsonResponse
    {
        $products = $useCase->execute(
            filters: $request->only(['search', 'category_id', 'low_stock']),
            perPage: (int) $request->integer('per_page', 10)
        );

        return ApiResponse::pagination(
            paginator: $products,
            data: ProductStockResource::collection($products),
            message: 'Stocks retrieved successfully'
        );
    }

    public function adjust(
        StockAdjustmentRequest $request,
        StockAdjustmentUseCase $useCase
    ): JsonResponse {
        $movement = $useCase->execute(
            actor: $request->user(),
            dto: StockAdjustmentDTO::fromArray($request->validated())
        );

        return ApiResponse::success(
            data: new StockMovementResource($movement),
            message: 'Stock adjusted successfully',
            status: 201
        );
    }

    public function movements(Request $request, ListStockMovementsUseCase $useCase): JsonResponse
    {
        $movements = $useCase->execute(
            filters: $request->only(['product_id', 'type', 'date_from', 'date_to']),
            perPage: (int) $request->integer('per_page', 10)
        );

        return ApiResponse::pagination(
            paginator: $movements,
            data: StockMovementResource::collection($movements),
            message: 'Stock movements retrieved successfully'
        );
    }

    public function lowStock(ListLowStockProductsUseCase $useCase): JsonResponse
    {
        $products = $useCase->execute();

        return ApiResponse::success(
            data: ProductStockResource::collection($products),
            message: 'Low stock products retrieved successfully'
        );
    }
}