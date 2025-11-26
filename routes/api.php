<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiScheduleController;
use App\Http\Controllers\Api\ApiSettingsController;
use App\Http\Controllers\Api\ApiEventController;
use App\Http\Middleware\ApiAuthentication;

Route::middleware([ApiAuthentication::class])->group(function () {
    Route::get('/schedules', [ApiScheduleController::class, 'index'])->middleware('ability:resources.view');
    //Route::post('/schedules', [ApiScheduleController::class, 'store']);
    //Route::put('/schedules/{schedule_id}', [ApiScheduleController::class, 'update']);

    Route::get('/events', [ApiEventController::class, 'index'])->middleware('ability:resources.view');
    Route::post('/events/{subdomain}', [ApiEventController::class, 'store'])->middleware('ability:resources.manage');
    Route::post('/events/flyer/{event_id}', [ApiEventController::class, 'flyer'])->middleware('ability:resources.view');
});
