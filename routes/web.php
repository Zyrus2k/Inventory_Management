<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LowStockController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:10,1')->name('login.attempt');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/', DashboardController::class)->name('dashboard');

    // ---- Administrator only (registered BEFORE the {id} read-only routes) ----
    Route::middleware('role:admin')->group(function () {
        Route::resource('products', ProductController::class)->except(['index', 'show']);
        Route::resource('suppliers', SupplierController::class)->except(['index', 'show']);
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::patch('users/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    });

    // ---- Administrator + Staff ----
    Route::resource('products', ProductController::class)->only(['index', 'show']);
    Route::resource('suppliers', SupplierController::class)->only(['index', 'show']);

    Route::get('stock-in', [StockController::class, 'create'])->defaults('type', 'in')->name('stock.in');
    Route::post('stock-in', [StockController::class, 'store'])->defaults('type', 'in')->name('stock.in.store');
    Route::get('stock-out', [StockController::class, 'create'])->defaults('type', 'out')->name('stock.out');
    Route::post('stock-out', [StockController::class, 'store'])->defaults('type', 'out')->name('stock.out.store');

    Route::get('transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('low-stock', LowStockController::class)->name('low-stock');
});
