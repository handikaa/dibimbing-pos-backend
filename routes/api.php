<?php

use App\Http\Controllers\Api\HealthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\CashierSessionController;
use App\Http\Controllers\Api\RackController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\SalesController;
use App\Http\Controllers\Api\MidtransController;

Route::prefix('v1')->group(function () {
    Route::get('/health', HealthController::class);

    Route::post('/auth/login', [AuthController::class, 'login']);
    // Midtrans webhook endpoint (no authentication, but should verify signature)
    Route::post('midtrans/webhook', [MidtransController::class, 'webhook']);



    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/profile', [AuthController::class, 'profile']);
        Route::put('/auth/change-password', [AuthController::class, 'changePassword']);

        Route::get('/users', [UserController::class, 'index'])->middleware('permission:user.view_any');
        Route::post('/users', [UserController::class, 'store'])->middleware('permission:user.create');
        Route::get('/users/{id}', [UserController::class, 'show'])->middleware('permission:user.view_any');
        Route::put('/users/{id}', [UserController::class, 'update'])->middleware('permission:user.update');
        Route::patch('/users/{id}/deactivate', [UserController::class, 'deactivate'])->middleware('permission:user.deactivate');


        Route::get('/categories', [CategoryController::class, 'index'])
            ->middleware('permission:category.view_any');

        Route::post('/categories', [CategoryController::class, 'store'])
            ->middleware('permission:category.create');

        Route::get('/categories/{id}', [CategoryController::class, 'show'])
            ->middleware('permission:category.view_any');

        Route::put('/categories/{id}', [CategoryController::class, 'update'])
            ->middleware('permission:category.update');

        Route::patch('/categories/{id}/deactivate', [CategoryController::class, 'deactivate'])
            ->middleware('permission:category.deactivate');


        Route::get('/products', [ProductController::class, 'index'])
            ->middleware('permission:product.view_any');

        Route::post('/products', [ProductController::class, 'store'])
            ->middleware('permission:product.create');

        Route::get('/products/{id}', [ProductController::class, 'show'])
            ->middleware('permission:product.view_any');

        Route::put('/products/{id}', [ProductController::class, 'update'])
            ->middleware('permission:product.update');

        Route::patch('/products/{id}/deactivate', [ProductController::class, 'deactivate'])
            ->middleware('permission:product.deactivate');

        Route::get('/pos/products', [ProductController::class, 'posSearch'])
            ->middleware('permission:pos.open');

        Route::get('/products/by-barcode/{barcode}', [ProductController::class, 'getByBarcode'])
            ->middleware('permission:product.view_any');


        Route::get('/inventory/stocks', [InventoryController::class, 'stocks'])
            ->middleware('permission:inventory.view_stock');

        Route::post('/inventory/adjustments', [InventoryController::class, 'adjust'])
            ->middleware('permission:inventory.adjust_stock');

        Route::get('/inventory/movements', [InventoryController::class, 'movements'])
            ->middleware('permission:inventory.view_movement');

        Route::get('/inventory/low-stock', [InventoryController::class, 'lowStock'])
            ->middleware('permission:inventory.view_low_stock');

        Route::get('/inventory/stocks/barcode/{barcode}', [InventoryController::class, 'stockByBarcode'])
            ->middleware('permission:inventory.view_stock');


        Route::get('cashier-sessions/active', [CashierSessionController::class, 'active']);
        Route::post('cashier-sessions/open', [CashierSessionController::class, 'open']);
        Route::post('cashier-sessions/{session}/close', [CashierSessionController::class, 'close']);
        Route::get('/cashier-sessions', [CashierSessionController::class, 'index'])
            ->middleware('permission:session.view_any');

        Route::apiResource('racks', RackController::class);
        Route::patch('racks/{rack}/deactivate', [RackController::class, 'deactivate']);


        Route::get('/pos/products', [PosController::class, 'products'])
            ->middleware('permission:product.view_any');
        Route::post('/pos/checkout/cash', [PosController::class, 'checkoutCash'])
            ->middleware('permission:pos.checkout_cash');
        Route::post('/pos/open-bill', [PosController::class, 'openBill'])
            ->middleware(['auth:sanctum', 'permission:pos.open']);
        Route::post('/pos/checkout/midtrans', [PosController::class, 'checkoutMidtrans'])
            ->middleware('permission:pos.checkout_midtrans');




        Route::get('/sales', [SalesController::class, 'index'])
            ->middleware('permission:sales.view_any|sales.view_own');
        Route::get('/sales/{sale}', [SalesController::class, 'show'])
            ->middleware('permission:sales.view_detail');
        Route::get('/sales/{sale}/receipt', [SalesController::class, 'receipt'])
            ->middleware('permission:sales.view_detail');
        Route::post('/sales/{sale}/cancel', [SalesController::class, 'cancel'])
            ->middleware('permission:sales.cancel_unpaid');
        Route::post('/sales/{sale}/items', [SalesController::class, 'addItem'])
            ->middleware('permission:pos.open');
        Route::put('/sales/{sale}/items/{item}', [SalesController::class, 'updateItem'])
            ->middleware('permission:pos.open');
        Route::delete('/sales/{sale}/items/{item}', [SalesController::class, 'deleteItem'])
            ->middleware('permission:pos.open');
        Route::post('/sales/{sale}/checkout/cash', [SalesController::class, 'openBillCheckoutCash'])
            ->middleware('permission:pos.checkout_cash');
        Route::post('/sales/{sale}/checkout/midtrans', [SalesController::class, 'checkoutMidtrans'])
            ->middleware('permission:pos.checkout_midtrans');
        Route::post(
            '/sales/{sale}/midtrans/check-status',
            [SalesController::class, 'checkMidtransStatus']
        );
    });
});
