<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ApiScheduleController;
use App\Http\Controllers\Api\ApiSettingsController;
use App\Http\Controllers\Api\ApiEventController;
use App\Http\Controllers\Api\ApiRoleController;
use App\Http\Middleware\ApiAuthentication;

Route::middleware([ApiAuthentication::class])->group(function () {
    Route::get('/schedules', [ApiScheduleController::class, 'index'])->middleware('ability:resources.view');
    //Route::post('/schedules', [ApiScheduleController::class, 'store']);
    //Route::put('/schedules/{schedule_id}', [ApiScheduleController::class, 'update']);

    Route::get('/roles', [ApiRoleController::class, 'index'])->middleware('ability:resources.view');
    Route::post('/roles', [ApiRoleController::class, 'store'])->middleware('ability:resources.manage');
    Route::delete('/roles/{role_id}', [ApiRoleController::class, 'destroy'])->middleware('ability:resources.manage');
    Route::delete('/roles/{role_id}/contacts/{contact}', [ApiRoleController::class, 'destroyContact'])->middleware('ability:resources.manage');

    Route::get('/events', [ApiEventController::class, 'index'])->middleware('ability:resources.view');
    Route::get('/events/resources', [ApiEventController::class, 'resources'])->middleware('ability:resources.view');
    Route::post('/events/{subdomain}', [ApiEventController::class, 'store'])->middleware('ability:resources.manage');
    Route::patch('/events/{event_id}', [ApiEventController::class, 'update'])->middleware('ability:resources.manage');
    Route::delete('/events/{event_id}', [ApiEventController::class, 'destroy'])->middleware('ability:resources.manage');
    Route::post('/events/flyer/{event_id}', [ApiEventController::class, 'flyer'])->middleware('ability:resources.view');

    // Profile
    Route::patch('/profile', [\App\Http\Controllers\Api\ApiProfileController::class, 'update'])->middleware('ability:resources.manage');
    Route::delete('/profile', [\App\Http\Controllers\Api\ApiProfileController::class, 'destroy'])->middleware('ability:resources.manage');

    // Tickets
    Route::get('/tickets', [\App\Http\Controllers\Api\ApiTicketController::class, 'index'])->middleware('ability:resources.view');
    Route::patch('/tickets/{sale_id}', [\App\Http\Controllers\Api\ApiTicketController::class, 'update'])->middleware('ability:resources.manage');
    Route::post('/tickets/scan', [\App\Http\Controllers\Api\ApiTicketController::class, 'scanByCode'])->middleware('ability:resources.manage');
    Route::post('/tickets/{sale_id}/scan', [\App\Http\Controllers\Api\ApiTicketController::class, 'scan'])->middleware('ability:resources.manage');
    Route::post('/tickets/{sale_id}/checkout', [\App\Http\Controllers\Api\ApiTicketController::class, 'checkout'])->middleware('ability:resources.manage');
    Route::post('/events/{subdomain}/checkout', [\App\Http\Controllers\Api\ApiTicketController::class, 'createSale'])->middleware('ability:resources.manage');
    Route::post('/tickets/{sale_id}/reassign', [\App\Http\Controllers\Api\ApiTicketController::class, 'reassign'])->middleware('ability:resources.manage');
    Route::post('/tickets/{sale_id}/notes', [\App\Http\Controllers\Api\ApiTicketController::class, 'addNote'])->middleware('ability:resources.manage');

    // Talent/Performers
    Route::get('/talent', [\App\Http\Controllers\Api\ApiTalentController::class, 'index'])->middleware('ability:resources.view');
    Route::get('/talent/{id}', [\App\Http\Controllers\Api\ApiTalentController::class, 'show'])->middleware('ability:resources.view');
    Route::post('/talent', [\App\Http\Controllers\Api\ApiTalentController::class, 'store'])->middleware('ability:resources.manage');
    Route::put('/talent/{id}', [\App\Http\Controllers\Api\ApiTalentController::class, 'update'])->middleware('ability:resources.manage');
    Route::delete('/talent/{id}', [\App\Http\Controllers\Api\ApiTalentController::class, 'destroy'])->middleware('ability:resources.manage');

    // Venues
    Route::get('/venues', [\App\Http\Controllers\Api\ApiVenueController::class, 'index'])->middleware('ability:resources.view');
    Route::get('/venues/{id}', [\App\Http\Controllers\Api\ApiVenueController::class, 'show'])->middleware('ability:resources.view');
    Route::post('/venues', [\App\Http\Controllers\Api\ApiVenueController::class, 'store'])->middleware('ability:resources.manage');
    Route::put('/venues/{id}', [\App\Http\Controllers\Api\ApiVenueController::class, 'update'])->middleware('ability:resources.manage');
    Route::delete('/venues/{id}', [\App\Http\Controllers\Api\ApiVenueController::class, 'destroy'])->middleware('ability:resources.manage');

    // Check-ins
    Route::post('/checkins', [\App\Http\Controllers\Api\ApiCheckInController::class, 'store'])->middleware('ability:resources.manage');
    Route::get('/checkins', [\App\Http\Controllers\Api\ApiCheckInController::class, 'index'])->middleware('ability:resources.view');

    // Media library
    Route::get('/media', [\App\Http\Controllers\Api\ApiMediaLibraryController::class, 'list'])->middleware('ability:resources.view');
    Route::post('/media', [\App\Http\Controllers\Api\ApiMediaLibraryController::class, 'store'])->middleware('ability:resources.manage');
    Route::delete('/media/{asset}', [\App\Http\Controllers\Api\ApiMediaLibraryController::class, 'destroy'])->middleware('ability:resources.manage');
    Route::post('/media/{asset}/variant', [\App\Http\Controllers\Api\ApiMediaLibraryController::class, 'storeVariant'])->middleware('ability:resources.manage');
    Route::get('/media/tags', [\App\Http\Controllers\Api\ApiMediaLibraryController::class, 'tags'])->middleware('ability:resources.view');
    Route::post('/media/tags', [\App\Http\Controllers\Api\ApiMediaLibraryController::class, 'storeTag'])->middleware('ability:resources.manage');
    Route::delete('/media/tags/{tag}', [\App\Http\Controllers\Api\ApiMediaLibraryController::class, 'destroyTag'])->middleware('ability:resources.manage');
    Route::post('/media/{asset}/sync-tags', [\App\Http\Controllers\Api\ApiMediaLibraryController::class, 'syncTags'])->middleware('ability:resources.manage');

    // Role management (additional)
    Route::patch('/roles/{role_id}', [\App\Http\Controllers\Api\ApiRoleController::class, 'update'])->middleware('ability:resources.manage');
    Route::post('/roles/{role_id}/members', [\App\Http\Controllers\Api\ApiRoleController::class, 'storeMember'])->middleware('ability:resources.manage');
    Route::patch('/roles/{role_id}/members/{member_id}', [\App\Http\Controllers\Api\ApiRoleController::class, 'updateMember'])->middleware('ability:resources.manage');
    Route::delete('/roles/{role_id}/members/{member_id}', [\App\Http\Controllers\Api\ApiRoleController::class, 'destroyMember'])->middleware('ability:resources.manage');
});
