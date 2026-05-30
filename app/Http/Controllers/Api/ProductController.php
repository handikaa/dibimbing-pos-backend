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
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\Product\ProductResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request, ListProductsUseCase $useCase): JsonResponse
    {
        $products = $useCase->execute(
            filters: $request->only(['search', 'category_id', 'is_active', 'low_stock']),
            perPage: (int) $request->integer('per_page', 10)
        );

        return ApiResponse::pagination(
            paginator: $products,
            data: ProductResource::collection($products),
            message: 'Products retrieved successfully'
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
            status: 201
        );
    }

    public function show(int $id, ShowProductUseCase $useCase): JsonResponse
    {
        $product = $useCase->execute($id);

        return ApiResponse::success(
            data: new ProductResource($product),
            message: 'Product retrieved successfully'
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
}
