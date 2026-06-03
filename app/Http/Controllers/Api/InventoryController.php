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
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class InventoryController extends Controller
{
    public function stocks(Request $request, ListStocksUseCase $useCase): JsonResponse
    {
        $products = $useCase->execute(
            filters: $request->only(['search', 'category_id', 'low_stock']),
            perPage: (int) $request->integer('per_page', 10)
        );

        return ApiResponse::successPaginated(
            pagination: [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
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
            code: 201
        );
    }

    public function movements(Request $request, ListStockMovementsUseCase $useCase): JsonResponse
    {
        $movements = $useCase->execute(
            filters: $request->only(['product_id', 'type', 'date_from', 'date_to']),
            perPage: (int) $request->integer('per_page', 10)
        );

        return ApiResponse::successPaginated(
            data: StockMovementResource::collection($movements),
            pagination: [
                'current_page' => $movements->currentPage(),
                'last_page' => $movements->lastPage(),
                'per_page' => $movements->perPage(),
                'total' => $movements->total(),
            ],
            message: 'Stock movements retrieved successfully'
        );
    }

    public function stockByBarcode(string $barcode, ListStocksUseCase $useCase): JsonResponse
    {
        try {
            // Gunakan UseCase untuk mencari product & stock berdasarkan barcode
            $productStock = $useCase->getStockByBarcode($barcode);

            return ApiResponse::success(
                data: new ProductStockResource($productStock),
                message: 'Product stock retrieved successfully'
            );
        } catch (AuthorizationException $e) {
            return ApiResponse::error(
                message: 'Forbidden',
                code: 403,
                errors: $e->getMessage()
            );
        } catch (ModelNotFoundException $e) {
            return ApiResponse::error(
                message: 'Product not found',
                code: 404
            );
        } catch (\Throwable $e) {
            return ApiResponse::error(
                message: 'Failed to retrieve product stock',
                code: 500,
                errors: config('app.debug') ? ['exception' => $e->getMessage()] : null
            );
        }
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
