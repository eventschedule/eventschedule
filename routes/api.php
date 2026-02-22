<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiBoostProxyController;
use App\Http\Controllers\Api\ApiEventController;
use App\Http\Controllers\Api\ApiGroupController;
use App\Http\Controllers\Api\ApiSaleController;
use App\Http\Controllers\Api\ApiScheduleController;
use App\Http\Middleware\ApiAuthentication;
use Illuminate\Support\Facades\Route;

// Unauthenticated routes
Route::post('/register/send-code', [ApiAuthController::class, 'sendCode']);
Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);

// Boost proxy (selfhosted installs proxy through hosted server)
Route::post('/boost/proxy', [ApiBoostProxyController::class, 'handle']);

// Authenticated routes
Route::middleware([ApiAuthentication::class])->group(function () {
    // Schedules
    Route::get('/schedules', [ApiScheduleController::class, 'index']);
    Route::get('/schedules/{subdomain}', [ApiScheduleController::class, 'show']);
    Route::post('/schedules', [ApiScheduleController::class, 'store']);
    Route::put('/schedules/{subdomain}', [ApiScheduleController::class, 'update']);
    Route::delete('/schedules/{subdomain}', [ApiScheduleController::class, 'destroy']);

    // Sub-schedules (groups)
    Route::get('/schedules/{subdomain}/groups', [ApiGroupController::class, 'index']);
    Route::post('/schedules/{subdomain}/groups', [ApiGroupController::class, 'store']);
    Route::put('/schedules/{subdomain}/groups/{group_id}', [ApiGroupController::class, 'update']);
    Route::delete('/schedules/{subdomain}/groups/{group_id}', [ApiGroupController::class, 'destroy']);

    // Events
    Route::get('/events', [ApiEventController::class, 'index']);
    Route::post('/events/flyer/{event_id}', [ApiEventController::class, 'flyer']);
    Route::post('/events/{subdomain}', [ApiEventController::class, 'store']);
    Route::get('/events/{id}', [ApiEventController::class, 'show']);
    Route::put('/events/{id}', [ApiEventController::class, 'update']);
    Route::delete('/events/{id}', [ApiEventController::class, 'destroy']);

    // Categories
    Route::get('/categories', [ApiEventController::class, 'categories']);

    // Sales
    Route::get('/sales', [ApiSaleController::class, 'index']);
    Route::post('/sales', [ApiSaleController::class, 'store']);
    Route::get('/sales/{id}', [ApiSaleController::class, 'show']);
    Route::put('/sales/{id}', [ApiSaleController::class, 'update']);
    Route::delete('/sales/{id}', [ApiSaleController::class, 'destroy']);
});
