<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ScheduleController;
use App\Http\Controllers\Api\ApiSettingsController;
use App\Http\Middleware\ApiAuthentication;

Route::middleware([ApiAuthentication::class])->prefix('api')->group(function () {
    Route::get('/schedules', [ScheduleController::class, 'index']);
    Route::get('/schedules/{schedule}/events', [ScheduleController::class, 'events']);
    Route::post('/schedules/{schedule}/events', [ScheduleController::class, 'storeEvent']);
}); 