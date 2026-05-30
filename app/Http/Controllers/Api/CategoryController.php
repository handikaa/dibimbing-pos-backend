<?php

namespace App\Http\Controllers\Api;

use App\Application\Category\DTOs\CreateCategoryDTO;
use App\Application\Category\DTOs\UpdateCategoryDTO;
use App\Application\Category\UseCases\CreateCategoryUseCase;
use App\Application\Category\UseCases\DeactivateCategoryUseCase;
use App\Application\Category\UseCases\ListCategoriesUseCase;
use App\Application\Category\UseCases\ShowCategoryUseCase;
use App\Application\Category\UseCases\UpdateCategoryUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Category\StoreCategoryRequest;
use App\Http\Requests\Category\UpdateCategoryRequest;
use App\Http\Resources\Category\CategoryResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request, ListCategoriesUseCase $useCase): JsonResponse
    {
        $categories = $useCase->execute(
            filters: $request->only(['search', 'is_active']),
            perPage: (int) $request->integer('per_page', 10)
        );

        return ApiResponse::pagination(
            paginator: $categories,
            data: CategoryResource::collection($categories),
            message: 'Categories retrieved successfully'
        );
    }

    public function store(StoreCategoryRequest $request, CreateCategoryUseCase $useCase): JsonResponse
    {
        $category = $useCase->execute(
            CreateCategoryDTO::fromArray($request->validated())
        );

        return ApiResponse::success(
            data: new CategoryResource($category),
            message: 'Category created successfully',
            status: 201
        );
    }

    public function show(int $id, ShowCategoryUseCase $useCase): JsonResponse
    {
        $category = $useCase->execute($id);

        return ApiResponse::success(
            data: new CategoryResource($category),
            message: 'Category retrieved successfully'
        );
    }

    public function update(
        UpdateCategoryRequest $request,
        int $id,
        UpdateCategoryUseCase $useCase
    ): JsonResponse {
        $category = $useCase->execute(
            id: $id,
            dto: UpdateCategoryDTO::fromArray($request->validated())
        );

        return ApiResponse::success(
            data: new CategoryResource($category),
            message: 'Category updated successfully'
        );
    }

    public function deactivate(int $id, DeactivateCategoryUseCase $useCase): JsonResponse
    {
        $category = $useCase->execute($id);

        return ApiResponse::success(
            data: new CategoryResource($category),
            message: 'Category deactivated successfully'
        );
    }
}