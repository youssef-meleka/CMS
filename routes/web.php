<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\Dashboard\ProductController;
use App\Http\Controllers\Dashboard\OrderController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Dashboard Authentication Routes
Route::prefix('dashboard')->name('dashboard.')->group(function () {
    Route::get('login', [DashboardController::class, 'showLogin'])->name('login');
    Route::post('login', [DashboardController::class, 'login'])->name('login.post');
    Route::post('logout', [DashboardController::class, 'logout'])->name('logout');

    // Protected Dashboard Routes
    Route::middleware(['auth', App\Http\Middleware\DashboardAccess::class])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('index');

        // User Management Routes
        Route::resource('users', UserController::class);

        // Product Management Routes
        Route::resource('products', ProductController::class);
        Route::patch('products/{product}/stock', [ProductController::class, 'updateStock'])->name('products.stock');

        // Order Management Routes
        Route::resource('orders', OrderController::class);
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::patch('orders/{order}/assign', [OrderController::class, 'assign'])->name('orders.assign');
        Route::get('orders-statistics', [OrderController::class, 'statistics'])->name('orders.statistics');
    });
});
