<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\DesktopOrderController;
use App\Http\Controllers\Api\RegisterApiController;
use App\Http\Controllers\Api\SyncEventController;
use App\Http\Controllers\Api\IntegrationTokenController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Desktop (OrgaSoft) integration endpoints — authenticated via API-KEY header
Route::middleware('orgasoft.apikey')->prefix('desktop')->group(function () {
    Route::post('/order', [DesktopOrderController::class, 'store']);
    Route::get('/orders', [DesktopOrderController::class, 'index']);
});

Route::post('/register', [RegisterApiController::class, 'register']);
Route::get('/categories', [RegisterApiController::class, 'index']);
Route::get('/categories/{id}/children', [RegisterApiController::class, 'children']);

// Integration token requires logged-in user (Sanctum session/cookie or Bearer).
Route::post('/integration/token', [IntegrationTokenController::class, 'store'])
    ->middleware(['auth:sanctum', 'throttle:api'])
    ->name('api.integration.token');

// Sync events & product/variant read: accept Sanctum Bearer OR API-KEY (OrgaSoft key).
Route::middleware(['sync.api.auth', 'throttle:api'])->group(function () {
    Route::post('/events/product_update', [SyncEventController::class, 'productUpdate']);
    Route::post('/events/variant_update', [SyncEventController::class, 'variantUpdate']);
    Route::post('/events/stock_update', [SyncEventController::class, 'stockUpdate']);
    Route::post('/events/price_update', [SyncEventController::class, 'priceUpdate']);

    Route::get('/products/{id}', [SyncEventController::class, 'showProduct']);
    Route::get('/variants/{id}', [SyncEventController::class, 'showVariant']);
});
