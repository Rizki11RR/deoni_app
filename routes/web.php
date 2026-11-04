<?php

use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PickingController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImportController;
use App\Http\Controllers\ProductPackagingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StocktakeController;
use App\Http\Controllers\UomCategoryController;
use App\Http\Controllers\UomController;
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
    Route::middleware('permission:view-reports')->group(function (): void {
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });
    Route::middleware('permission:manage-payments')->group(function (): void {
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/orders/{order}/payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/orders/{order}/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::post('/payments/{payment}/reverse', [PaymentController::class, 'reverse'])->name('payments.reverse');
    });
    Route::middleware('permission:create-orders')->group(function (): void {
        Route::resource('orders', OrderController::class)->only(['index', 'create', 'store']);
        Route::get('/orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('/orders/{order}/approve', [OrderController::class, 'approve'])->middleware('permission:approve-bons')->name('orders.approve');
        Route::get('/picking', [PickingController::class, 'index'])->name('picking.index');
        Route::patch('/picking/{order}', [PickingController::class, 'update'])->name('picking.update');
        Route::get('/delivery-notes', [DeliveryNoteController::class, 'index'])->name('delivery-notes.index');
        Route::get('/delivery-notes/create', [DeliveryNoteController::class, 'create'])->name('delivery-notes.create');
        Route::post('/delivery-notes', [DeliveryNoteController::class, 'store'])->name('delivery-notes.store');
        Route::post('/delivery-notes/{deliveryNote}/deliver', [DeliveryNoteController::class, 'deliver'])->name('delivery-notes.deliver');
    });
    Route::middleware('permission:manage-customers')->group(function (): void {
        Route::resource('customers', CustomerController::class)->only(['index', 'create', 'store']);
    });
    Route::middleware('permission:manage-products')->group(function (): void {
        Route::resource('products', ProductController::class)->only(['index', 'create', 'store']);
        Route::get('/products/import', [ProductImportController::class, 'create'])->name('products.import.create');
        Route::post('/products/import/preview', [ProductImportController::class, 'preview'])->name('products.import.preview');
        Route::post('/products/import', [ProductImportController::class, 'store'])->name('products.import.store');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::resource('uom-categories', UomCategoryController::class)->only(['index', 'store']);
        Route::resource('uoms', UomController::class)->only(['index', 'store']);
        Route::get('/products/{product}/packagings', [ProductPackagingController::class, 'index'])->name('products.packagings.index');
        Route::post('/products/{product}/packagings', [ProductPackagingController::class, 'store'])->name('products.packagings.store');
    });
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
});
