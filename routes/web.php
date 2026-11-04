<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PickingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StocktakeController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});
Route::middleware('auth')->group(function (): void {
    Route::view('/dashboard', 'dashboard')->name('dashboard');
    Route::middleware('permission:manage-users')->group(function (): void {
        Route::resource('users', UserController::class)->only(['index', 'create', 'store']);
        Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });
    Route::middleware('permission:manage-inventory')->group(function (): void {
        Route::resource('stock', StockController::class)->only(['index', 'create', 'store']);
        Route::resource('stocktakes', StocktakeController::class)->only(['index', 'create', 'store']);
    });
    Route::middleware('permission:manage-payments')->group(function (): void {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/orders/{order}/payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/orders/{order}/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::post('/payments/{payment}/reverse', [PaymentController::class, 'reverse'])->name('payments.reverse');
    });
    Route::middleware('permission:create-orders')->group(function (): void {
        Route::resource('orders', OrderController::class)->only(['index', 'create', 'store']);
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::get('/picking', [PickingController::class, 'index'])->name('picking.index');
        Route::patch('/picking/{order}', [PickingController::class, 'update'])->name('picking.update');
    });
    Route::middleware('permission:manage-customers')->group(function (): void {
        Route::resource('customers', CustomerController::class)->only(['index', 'create', 'store']);
    });
    Route::middleware('permission:manage-products')->group(function (): void {
        Route::resource('products', ProductController::class)->only(['index', 'create', 'store']);
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    });
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
