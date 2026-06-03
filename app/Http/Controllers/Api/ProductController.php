<?php

namespace App\Http\Controllers\Api;

use App\Application\Product\DTOs\CreateProductDTO;
use App\Application\Product\DTOs\UpdateProductDTO;
use App\Application\Product\UseCases\CreateProductUseCase;
use App\Application\Product\UseCases\DeactivateProductUseCase;
use App\Application\Product\UseCases\ListProductsUseCase;
use App\Application\Product\UseCases\SearchProductsForPosUseCase;
use App\Application\Product\UseCases\ShowProductUseCase;
use App\Application\Product\UseCases\UpdateProductUseCase;
use App\Application\Product\UseCases\GetProductByBarcodeUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\ProductResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request, ListProductsUseCase $useCase): JsonResponse
    {
        $products = $useCase->execute(
            filters: $request->only(['search', 'category_id', 'is_active', 'low_stock']),
            perPage: (int) $request->integer('per_page', 10)
        );

        return ApiResponse::successPaginated(
            data: ProductResource::collection($products),
            pagination: [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
            message: 'Products retrieved successfully',
            code: 200
        );
    }

    public function posSearch(Request $request, SearchProductsForPosUseCase $useCase): JsonResponse
    {
        $products = $useCase->execute(
            filters: $request->only(['search', 'barcode', 'category_id'])
        );

        return ApiResponse::success(
            data: ProductResource::collection($products),
            message: 'POS products retrieved successfully'
        );
    }

    public function store(StoreProductRequest $request, CreateProductUseCase $useCase): JsonResponse
    {
        $product = $useCase->execute(
            CreateProductDTO::fromArray($request->validated())
        );

        return ApiResponse::success(
            data: new ProductResource($product),
            message: 'Product created successfully',
            code: 201
        );
    }

    public function show(int $id, ShowProductUseCase $useCase): JsonResponse
    {
        $product = $useCase->execute($id);

        return ApiResponse::success(
            data: new ProductResource($product),
            message: 'Product retrieved successfully',
            code: 200
        );
    }

    public function update(
        UpdateProductRequest $request,
        int $id,
        UpdateProductUseCase $useCase
    ): JsonResponse {
        $product = $useCase->execute(
            id: $id,
            dto: UpdateProductDTO::fromArray($request->validated())
        );

        return ApiResponse::success(
            data: new ProductResource($product),
            message: 'Product updated successfully'
        );
    }

    public function deactivate(int $id, DeactivateProductUseCase $useCase): JsonResponse
    {
        $product = $useCase->execute($id);

        return ApiResponse::success(
            data: new ProductResource($product),
            message: 'Product deactivated successfully'
        );
    }

    public function getByBarcode(string $barcode, GetProductByBarcodeUseCase $useCase)
    {
        try {
            $product = $useCase->execute($barcode);

            return ApiResponse::success(
                data: new ProductResource($product),
                message: 'Product found'
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
        } catch (Throwable $e) {
            return ApiResponse::error(
                message: 'Failed to retrieve product',
                code: 500,
                errors: config('app.debug') ? ['exception' => $e->getMessage()] : null
            );
        }
    }
}
