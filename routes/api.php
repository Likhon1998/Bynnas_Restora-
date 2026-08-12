<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (React SPA data)
|--------------------------------------------------------------------------
|
| Prefix: /api
| Auth: Laravel Sanctum (SPA cookie-based) — wire up in later phases.
|
| Conventions:
|   /api/pos/*  — POS & KDS
|   /api/web/*  — Customer website
|
*/

use App\Http\Controllers\Api\WebMenuController;
use App\Http\Controllers\Api\WebOrderController;

Route::prefix('web')->group(function () {
    Route::get('menu', [WebMenuController::class, 'index']);
    Route::get('menu/featured', [WebMenuController::class, 'featured']);
    Route::post('orders', [WebOrderController::class, 'store']);
});
