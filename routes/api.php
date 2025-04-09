<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiScheduleController;
use App\Http\Controllers\Api\ApiSettingsController;
use App\Http\Controllers\Api\ApiEventController;
use App\Http\Middleware\ApiAuthentication;

Route::middleware([ApiAuthentication::class])->group(function () {
    Route::get('/schedules', [ApiScheduleController::class, 'index']);
    //Route::post('/schedules', [ApiScheduleController::class, 'store']);
    //Route::put('/schedules/{schedule_id}', [ApiScheduleController::class, 'update']);
    
    Route::get('/events', [ApiEventController::class, 'index']);
    Route::post('/events/{subdomain}', [ApiEventController::class, 'store']);
    //Route::put('/events/{event_id}', [ApiEventController::class, 'update']);
}); 