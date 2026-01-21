<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CategoryApiController;
use App\Http\Controllers\Api\RegisterApiController;
use App\Http\Controllers\Api\SyncEventController;
use App\Http\Controllers\Api\IntegrationTokenController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/register', [RegisterApiController::class, 'register']);
Route::get('/categories', [RegisterApiController::class, 'index']);
Route::get('/categories/{id}/children', [RegisterApiController::class, 'children']);

Route::middleware(['auth:sanctum', 'throttle:api'])->group(function () {
    // Generate an integration token for the authenticated user.
    Route::post('/integration/token', [IntegrationTokenController::class, 'store'])->name('api.integration.token');

    Route::post('/events/product_update', [SyncEventController::class, 'productUpdate']);
    Route::post('/events/variant_update', [SyncEventController::class, 'variantUpdate']);
    Route::post('/events/stock_update', [SyncEventController::class, 'stockUpdate']);
    Route::post('/events/price_update', [SyncEventController::class, 'priceUpdate']);

    Route::get('/products/{id}', [SyncEventController::class, 'showProduct']);
    Route::get('/variants/{id}', [SyncEventController::class, 'showVariant']);
});
