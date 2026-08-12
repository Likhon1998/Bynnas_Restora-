<?php

use App\Http\Controllers\Admin\CrmController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InventoryItemController;
use App\Http\Controllers\Admin\MenuItemController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\PosController;
use App\Http\Controllers\Admin\PurchaseOrderController;
use App\Http\Controllers\Admin\RecipeController;
use App\Http\Controllers\Admin\ReservationController;
use App\Http\Controllers\Admin\StockTransferController;
use App\Http\Controllers\Admin\SupplierController;
use App\Http\Controllers\Admin\TableController;
use App\Http\Controllers\Admin\WastageController;
use App\Http\Controllers\ProfileController;
use App\Models\InventoryItem;
use App\Models\RestaurantTable;
use App\Models\WastageRecord;
use Illuminate\Support\Facades\Route;

Route::bind('inventory', fn (string $value) => InventoryItem::findOrFail($value));
Route::bind('wastage', fn (string $value) => WastageRecord::findOrFail($value));
Route::bind('table', fn (string $value) => RestaurantTable::findOrFail($value));

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('admin')->name('admin.')->group(function () {
        // Operations
        Route::get('pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('pos', [PosController::class, 'store'])->name('pos.store');
        Route::resource('menu-items', MenuItemController::class)->except(['show']);
        Route::resource('orders', OrderController::class)->only(['index', 'edit', 'update', 'destroy']);
        Route::resource('reservations', ReservationController::class)->except(['show']);
        Route::resource('tables', TableController::class)->except(['show']);
        Route::resource('customers', CustomerController::class)->except(['show']);
        Route::get('crm', [CrmController::class, 'index'])->name('crm.index');
        Route::post('crm/transactions', [CrmController::class, 'storeTransaction'])->name('crm.transactions.store');

        // Inventory
        Route::resource('inventory', InventoryItemController::class)->except(['show']);
        Route::resource('recipes', RecipeController::class)->except(['show']);
        Route::resource('suppliers', SupplierController::class)->except(['show']);
        Route::resource('purchase-orders', PurchaseOrderController::class)->except(['show']);
        Route::resource('stock-transfers', StockTransferController::class)->except(['show']);
        Route::resource('wastage', WastageController::class)->except(['show']);
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::view('/{any?}', 'app')
    ->where(
        'any',
        '^(?!api(?:/|$)|admin(?:/|$)|login(?:/|$)|logout(?:/|$)|register(?:/|$)|dashboard(?:/|$)|profile(?:/|$)|forgot-password(?:/|$)|reset-password(?:/|$)|verify-email(?:/|$)|confirm-password(?:/|$)|email(?:/|$)).*',
    )
    ->name('spa');
