<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Pos\PosController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:login');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/', fn () => redirect()->route(request()->user()->role->homeRoute()));

    // Dashboard: Admin & Owner
    Route::middleware('permission:dashboard.view')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/feed', [DashboardController::class, 'feed'])->name('dashboard.feed');
    });

    Route::middleware('permission:reports.view')->get('/reports/sales/export', [SalesReportController::class, 'export'])->name('reports.sales.export');

    // Point of Sale: Kasir (dan Admin)
    Route::middleware('permission:pos.use')->prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/products', [PosController::class, 'products'])->name('products');
        Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout')->middleware('throttle:checkout');
    });

    // Riwayat transaksi: kasir = miliknya sendiri, admin/owner = semua
    Route::middleware('permission:transactions.view_own')->group(function () {
        Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show'])->name('transactions.show');
    });

    // Admin: pembatalan transaksi
    Route::middleware('permission:transactions.void')->group(function () {
        Route::post('/transactions/{transaction}/void', [TransactionController::class, 'void'])->name('transactions.void');
    });

    // Admin: manajemen produk
    Route::middleware('permission:products.manage')->group(function () {
        Route::resource('products', ProductController::class)->except(['show']);
    });

    // Admin: manajemen pengguna
    Route::middleware('permission:users.manage')->group(function () {
        Route::resource('users', UserController::class)->only(['index', 'store', 'update', 'destroy']);
    });
});
