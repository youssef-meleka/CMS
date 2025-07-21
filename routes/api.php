<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Authentication routes
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('account', [AuthController::class, 'account']);
    });
});

// Product routes - grouped by middleware
Route::middleware(['auth:sanctum', 'can:view products'])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/categories', [ProductController::class, 'categories']);
    Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'can:create products'])->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'can:edit products'])->group(function () {
    Route::put('/products/{product}', [ProductController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'can:delete products'])->group(function () {
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'can:manage product stock'])->group(function () {
    Route::patch('/products/{product}/stock', [ProductController::class, 'updateStock']);
});

// Order routes - grouped by middleware
Route::middleware(['auth:sanctum', 'can:view statistics'])->group(function () {
    Route::get('/orders/statistics', [OrderController::class, 'statistics']);
});

Route::middleware(['auth:sanctum', 'can:view orders'])->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/statuses', [OrderController::class, 'statuses']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
});

Route::middleware(['auth:sanctum', 'can:create orders'])->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);
});

Route::middleware(['auth:sanctum', 'can:edit orders'])->group(function () {
    Route::put('/orders/{order}', [OrderController::class, 'update']);
});

Route::middleware(['auth:sanctum', 'can:delete orders'])->group(function () {
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'can:update order status'])->group(function () {
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
});

Route::middleware(['auth:sanctum', 'can:assign orders'])->group(function () {
    Route::patch('/orders/{order}/assign', [OrderController::class, 'assign']);
});
