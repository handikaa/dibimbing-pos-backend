<?php

use App\Http\Responses\ApiResponse;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Spatie\Permission\Exceptions\UnauthorizedException;


return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        /**
         * Jangan pindah urutan ini!
         */

        $exceptions->render(function (UnauthorizedException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden',
                    'errors' => $e->getMessage()
                ], 403);
            }
        });

        // Handle Validation Exception (422)
        $exceptions->render(function (ValidationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $errors = $e->errors();

                $firstMessage = collect($errors)
                    ->flatten()
                    ->first() ?? 'Validation error';

                return ApiResponse::error(
                    message: $firstMessage,
                    errors: $errors,
                    code: 422
                );
            }

            return null;
        });

        // Handle Authentication Exception (401)
        $exceptions->render(function (AuthenticationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: 'Unauthenticated',
                    errors: 'Please provide valid authentication token',
                    code: 401
                );
            }

            return null;
        });

        $exceptions->render(function (AuthorizationException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'Forbidden',
                    'errors' => $e->getMessage() ?: 'User does not have the right permissions.'
                ], 403);
            }
        });

        // Handle Model Not Found Exception (404)
        $exceptions->render(function (ModelNotFoundException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: 'Resource not found',
                    errors: null,
                    code: 404
                );
            }

            return null;
        });

        // Handle Route Not Found Exception (404)
        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: 'Route not found',
                    errors: null,
                    code: 404
                );
            }

            return null;
        });

        // Handle Method Not Allowed Exception (405)
        $exceptions->render(function (MethodNotAllowedHttpException $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return ApiResponse::error(
                    message: 'Method not allowed',
                    errors: null,
                    code: 405
                );
            }

            return null;
        });

        // Handle All Other Exceptions (500) - GENERAL CATCH-ALL
        $exceptions->render(function (Throwable $e, $request) {
            if ($request->is('api/*') || $request->expectsJson()) {


                return ApiResponse::error(
                    message: 'Server error',
                    errors: config('app.debug') ? $e->getMessage() : null,
                    code: 500
                );
            }

            return null;
        });
    })
    ->create();
