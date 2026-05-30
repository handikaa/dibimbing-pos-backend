<?php

namespace App\Http\Controllers\Api;

use App\Application\Auth\DTOs\LoginDTO;
use App\Application\Auth\UseCases\LoginUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Application\Auth\DTOs\ChangePasswordDTO;
use App\Application\Auth\UseCases\ChangePasswordUseCase;
use App\Http\Requests\Auth\ChangePasswordRequest;

class AuthController extends Controller
{
    public function login(
        LoginRequest $request,
        LoginUseCase $loginUseCase
    ): JsonResponse {
        $result = $loginUseCase->execute(
            LoginDTO::fromArray($request->validated())
        );

        return ApiResponse::success(
            data: new AuthResource($result),
            message: 'Login success'
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return ApiResponse::success(
            data: null,
            message: 'Logout success'
        );
    }

    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();

        return ApiResponse::success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'is_active' => $user->is_active,
            'role' => $user->getRoleNames()->first(),
            'permissions' => $user->getAllPermissions()
                ->pluck('name')
                ->values(),
        ]);
    }

    public function changePassword(
        ChangePasswordRequest $request,
        ChangePasswordUseCase $useCase
    ): JsonResponse {
        $useCase->execute(
            user: $request->user(),
            dto: ChangePasswordDTO::fromArray($request->validated())
        );

        return ApiResponse::success(
            data: null,
            message: 'Password changed successfully'
        );
    }
}
