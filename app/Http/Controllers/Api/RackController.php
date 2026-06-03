<?php

namespace App\Http\Controllers\Api;

use App\Application\Rack\DTOs\CreateRackDTO;
use App\Application\Rack\DTOs\UpdateRackDTO;
use App\Application\Rack\UseCases\CreateRackUseCase;
use App\Application\Rack\UseCases\DeactivateRackUseCase;
use App\Application\Rack\UseCases\ListRacksUseCase;
use App\Application\Rack\UseCases\ShowRackUseCase;
use App\Application\Rack\UseCases\UpdateRackUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rack\StoreRackRequest;
use App\Http\Requests\Rack\UpdateRackRequest;
use App\Http\Resources\Rack\RackDetailResource;
use App\Http\Resources\Rack\RackResource;
use App\Infrastructure\Persistence\Eloquent\Models\Rack;
use Exception;
use Illuminate\Http\JsonResponse;

class RackController extends Controller
{
    /**
     * List all racks - GET /api/v1/racks
     */
    public function index(ListRacksUseCase $useCase): JsonResponse
    {
        try {
            $page = request()->get('page', 1);
            $perPage = request()->get('per_page', 10);
            $filters = request()->only(['search', 'is_active']);

            $racks = $useCase->execute($page, $perPage, $filters);

            return response()->json([
                'success' => true,
                'message' => 'Success',
                'data' => RackResource::collection($racks),
                'meta' => [
                    'current_page' => $racks->currentPage(),
                    'last_page' => $racks->lastPage(),
                    'per_page' => $racks->perPage(),
                    'total' => $racks->total(),
                ],
            ]);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch racks',
            ], 400);
        }
    }

    /**
     * Show rack detail - GET /api/v1/racks/{id}
     */
    public function show(int $id, ShowRackUseCase $useCase): JsonResponse
    {
        try {
            $rack = $useCase->execute($id);

            if (!$rack) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rack not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Success',
                'data' => new RackDetailResource($rack),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch rack detail',
            ], 400);
        }
    }

    /**
     * Create new rack - POST /api/v1/racks
     */
    public function store(
        StoreRackRequest $request,
        CreateRackUseCase $useCase
    ): JsonResponse {
        try {
            $dto = CreateRackDTO::from($request->validated());
            $rack = $useCase->execute($dto);

            return response()->json([
                'success' => true,
                'message' => 'Rack created successfully',
                'data' => new RackResource($rack),
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create rack',
            ], 400);
        }
    }

    /**
     * Update rack - PUT /api/v1/racks/{id}
     */
    public function update(
        Rack $rack,
        UpdateRackRequest $request,
        UpdateRackUseCase $useCase
    ): JsonResponse {
        try {
            $dto = UpdateRackDTO::from($request->validated());
            $updatedRack = $useCase->execute($rack, $dto);

            return response()->json([
                'success' => true,
                'message' => 'Rack updated successfully',
                'data' => new RackResource($updatedRack),
            ]);
        } catch (Exception $e) {            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update rack',
            ], 400);
        }
    }

    /**
     * Deactivate rack - PATCH /api/v1/racks/{id}/deactivate
     */
    public function deactivate(
        Rack $rack,
        DeactivateRackUseCase $useCase
    ): JsonResponse {
        try {
            $useCase->execute($rack);

            return response()->json([
                'success' => true,
                'message' => 'Rack deactivated successfully',
                'data' => new RackResource($rack->fresh()),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate rack',
            ], 400);
        }
    }
}