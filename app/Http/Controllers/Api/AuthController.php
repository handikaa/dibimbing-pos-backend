<?php

namespace App\Http\Controllers\Api;

use App\Application\Auth\DTOs\ChangePasswordDTO;
use App\Application\Auth\DTOs\LoginDTO;
use App\Application\Auth\UseCases\ChangePasswordUseCase;
use App\Application\Auth\UseCases\GetProfileUseCase;
use App\Application\Auth\UseCases\LoginUseCase;
use App\Application\Auth\UseCases\LogoutUseCase;
use App\Domain\User\Exceptions\InactiveAccountException;
use App\Domain\User\Exceptions\InvalidCredentialsException;
use App\Domain\User\Exceptions\IncorrectPasswordException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\Auth\AuthResource;
use App\Http\Resources\Auth\ProfileResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\Access\AuthorizationException;
use Throwable;

class AuthController extends Controller
{
/*
     * Login user
     * POST /api/v1/auth/login
     *
     * @param LoginRequest $request
     * @param LoginUseCase $useCase
     * @return JsonResponse
     */
    public function login(
        LoginRequest $request,
        LoginUseCase $useCase
    ): JsonResponse {
        try {
            // DTO validated request
            $dto = LoginDTO::fromArray($request->validated());

            // Execute UseCase
            $result = $useCase->execute($dto);

            // Success response
            return ApiResponse::success(
                data: new AuthResource($result),
                message: 'Login berhasil',
                code: 200
            );

        } catch (InvalidCredentialsException $e) {
            // Invalid email or password
        
            return ApiResponse::error(
                message: $e->getMessage(),
                code: 401
            );

        } catch (InactiveAccountException $e) {
            // Account is inactive

            return ApiResponse::error(
                message: $e->getMessage(),
                code: 403
            );

        } catch (Throwable $e) {
            // Server error    
            return ApiResponse::error(
                message: 'Gagal melakukan login',
                code: 500,
                errors: config('app.debug') ? [
                    'exception' => $e->getMessage(),
                ] : null
            );
        }
    }

    /**
     * Logout user
     * POST /api/v1/auth/logout
     *
     * @param Request $request
     * @param LogoutUseCase $useCase
     * @return JsonResponse
     */
    public function logout(
        Request $request,
        LogoutUseCase $useCase
    ): JsonResponse {
        try {
            // Get authenticated user
            $user = $request->user();

            if (!$user) {
                return ApiResponse::error(
                    message: 'Unauthenticated',
                    code: 401
                );
            }

            // Execute UseCase
            $useCase->execute($user);

            // Success response
            return ApiResponse::success(
                data: null,
                message: 'Logout berhasil'
            );

        } catch (AuthorizationException $e) {
            // No permission to logout           
            return ApiResponse::error(
                message: $e->getMessage(),
                code: 403
            );
        } catch (Throwable $e) {
            return ApiResponse::error(
                message: 'Gagal melakukan logout',
                code: 500,
                errors: config('app.debug') ? [
                    'exception' => $e->getMessage(),
                ] : null
            );
        }
    }

    /**
     * Get user profile
     * GET /api/v1/auth/profile
     *
     * @param Request $request
     * @param GetProfileUseCase $useCase
     * @return JsonResponse
     */
    public function profile(
        Request $request,
        GetProfileUseCase $useCase
    ): JsonResponse {
        try {
            // Get authenticated user
            $user = $request->user();

            if (!$user) {
                return ApiResponse::error(
                    message: 'Unauthenticated',
                    code: 401
                );
            }

            // Execute UseCase
            $profile = $useCase->execute($user);

            // Success response
            return ApiResponse::success(
                data: new ProfileResource($profile),
                message: 'Success'
            );

        } catch (AuthorizationException $e) {
            // No permission to view profile

            return ApiResponse::error(
                message: $e->getMessage(),
                code: 403
            );

        } catch (Throwable $e) {

            return ApiResponse::error(
                message: 'Gagal mengambil profil',
                code: 500,
                errors: config('app.debug') ? [
                    'exception' => $e->getMessage(),
                ] : null
            );
        }
    }

    /**
     * Change password
     * PUT /api/v1/auth/change-password
     *
     * @param ChangePasswordRequest $request
     * @param ChangePasswordUseCase $useCase
     * @return JsonResponse
     */
    public function changePassword(
        ChangePasswordRequest $request,
        ChangePasswordUseCase $useCase
    ): JsonResponse {
        try {
            // Get authenticated user
            $user = $request->user();

            if (!$user) {
                return ApiResponse::error(
                    message: 'Unauthenticated',
                    code: 401
                );
            }

            // Create DTO dari validated request
            $dto = ChangePasswordDTO::fromArray($request->validated());

            // Execute UseCase
            $useCase->execute($user, $dto);

            // Success response
            return ApiResponse::success(
                data: null,
                message: 'Password berhasil diubah'
            );

        } catch (IncorrectPasswordException $e) {
            // Current password is incorrect        
            return ApiResponse::error(
                message: $e->getMessage(),
                code: 422
            );

        } catch (AuthorizationException $e) {
            // No permission to change password
            return ApiResponse::error(
                message: $e->getMessage(),
                code: 403
            );

        } catch (Throwable $e) {
            // Server error
            return ApiResponse::error(
                message: 'Gagal mengubah password',
                code: 500,
                errors: config('app.debug') ? [
                    'exception' => $e->getMessage(),
                ] : null
            );
        }
    }
}