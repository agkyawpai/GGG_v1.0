<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\LogisticsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SettlementController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Support\Facades\Route;

// ── Auth (guest only) ─────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ── App (auth required) ───────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::get('/', fn () => redirect()->route('dashboard'));

    Route::get('/dashboard', [OrderController::class, 'index'])->name('dashboard');

    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/create', [OrderController::class, 'create'])->name('create');
        Route::post('/', [OrderController::class, 'store'])->name('store');
        Route::get('/{order:ulid}', [OrderController::class, 'show'])->name('show');
        Route::patch('/{order:ulid}/assign', [OrderController::class, 'assign'])->name('assign');
        Route::patch('/{order:ulid}/status', [OrderController::class, 'updateStatus'])->name('status');
        Route::patch('/{order:ulid}/reject', [OrderController::class, 'reject'])->name('reject');
    });

    Route::prefix('settlements')->name('settlements.')->group(function () {
        Route::get('/', [SettlementController::class, 'index'])->name('index');
        Route::post('/{order:ulid}', [SettlementController::class, 'store'])->name('store');
    });

    Route::prefix('logistics')->name('logistics.')->group(function () {
        Route::get('/', [LogisticsController::class, 'index'])->name('index');
        Route::patch('/{order:ulid}/assign', [LogisticsController::class, 'assign'])->name('assign');
    });

    Route::prefix('warehouse')->name('warehouse.')->group(function () {
        Route::get('/', [WarehouseController::class, 'index'])->name('index');
        Route::patch('/{warehousing}/receive', [WarehouseController::class, 'receive'])->name('receive');
        Route::patch('/{warehousing}/store', [WarehouseController::class, 'store'])->name('store');
        Route::patch('/{warehousing}/dispatch', [WarehouseController::class, 'dispatch'])->name('dispatch');
    });

});
