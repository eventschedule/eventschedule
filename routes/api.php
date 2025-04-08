<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiScheduleController;
use App\Http\Controllers\Api\ApiSettingsController;
use App\Http\Controllers\Api\ApiEventController;
use App\Http\Middleware\ApiAuthentication;

Route::middleware([ApiAuthentication::class])->prefix('api')->group(function () {
    Route::get('/schedules', [ApiScheduleController::class, 'index']);
    Route::post('/schedules', [ApiScheduleController::class, 'store']);
    Route::put('/schedules/{schedule}', [ApiScheduleController::class, 'update']);
    
    Route::get('/schedules/{schedule}/events', [ApiEventController::class, 'events']);
    Route::post('/schedules/{schedule}/events', [ApiEventController::class, 'storeEvent']);
}); 