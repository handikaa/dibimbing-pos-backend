<?php

namespace App\Http\Controllers\Api;

use App\Application\User\DTOs\CreateUserDTO;
use App\Application\User\DTOs\UpdateUserDTO;
use App\Application\User\DTOs\RegisterUserDTO;
use App\Application\User\UseCases\CreateUserUseCase;
use App\Application\User\UseCases\DeactivateUserUseCase;
use App\Application\User\UseCases\ListUsersUseCase;
use App\Application\User\UseCases\RegisterUserUseCase;
use App\Application\User\UseCases\ShowUserUseCase;
use App\Application\User\UseCases\UpdateUserUseCase;
use App\Application\User\UseCases\RegisterUserWithoutEmailUseCase;
use App\Application\User\UseCases\ActivateUserUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\RegisterUserRequest;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\User\UserResource;
use App\Http\Responses\ApiResponse;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request, ListUsersUseCase $useCase): JsonResponse
    {
        try {
            $paginator = $useCase->execute(
                filters: $request->only(['search', 'role', 'is_active']),
                page: (int) $request->get('page', 1),
                perPage: (int) $request->get('per_page', 10)
            );

            return ApiResponse::successPaginated(
                data: UserResource::collection($paginator),
                pagination: [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                ],
                message: 'Users retrieved successfully'
            );
        } catch (AuthorizationException $e) {
            return ApiResponse::error(
                message: $e->getMessage(),
                code: 403
            );
        } catch (\Throwable $e) {
            return ApiResponse::error(
                message: 'Failed to retrieve users',
                code: 500
            );
        }
    }

    public function store(StoreUserRequest $request, CreateUserUseCase $useCase): JsonResponse
    {
        $user = $useCase->execute(
            actor: $request->user(),
            dto: CreateUserDTO::fromArray($request->validated())
        );

        return ApiResponse::success(
            data: new UserResource($user),
            message: 'User created successfully',
            code: 201
        );
    }

    public function show(Request $request, int $id, ShowUserUseCase $useCase): JsonResponse
    {
        $user = $useCase->execute($request->user(), $id);

        return ApiResponse::success(
            data: new UserResource($user),
            message: 'User retrieved successfully'
        );
    }

    public function update(
        UpdateUserRequest $request,
        int $id,
        UpdateUserUseCase $useCase
    ): JsonResponse {
        $user = $useCase->execute(
            actor: $request->user(),
            id: $id,
            dto: UpdateUserDTO::fromArray($request->validated())
        );

        return ApiResponse::success(
            data: new UserResource($user),
            message: 'User updated successfully'
        );
    }

    public function deactivate(
        Request $request,
        int $id,
        DeactivateUserUseCase $useCase
    ): JsonResponse {
        $user = $useCase->execute($request->user(), $id);

        return ApiResponse::success(
            data: new UserResource($user),
            message: 'User deactivated successfully'
        );
    }

    public function register(RegisterUserRequest $request, RegisterUserUseCase $useCase)
    {
        $validated = $request->validated();

        $dto = new RegisterUserDTO(
            name: $validated['name'],
            email: $validated['email'],
            phone: $validated['phone'] ?? null,
            role: $validated['role'],
        );

        $useCase->execute($dto);

        return ApiResponse::success(
            message: 'User registered successfully. Activation email sent.'
        );
    }

    public function registerWithoutResend(
        RegisterUserRequest $request,
        RegisterUserWithoutEmailUseCase $useCase
    ): JsonResponse {
        $dto = RegisterUserDTO::fromArray($request->validated());

        $user = $useCase->execute($dto);

        return ApiResponse::success(
            data: new UserResource($user),
            message: 'User registered successfully. Account is inactive by default.'
        );
    }

    public function activate(
        User $user,
        ActivateUserUseCase $useCase
    ): JsonResponse {
        $activatedUser = $useCase->execute($user);

        return ApiResponse::success(
            data: new UserResource($activatedUser),
            message: 'User activated successfully.'
        );
    }
}
