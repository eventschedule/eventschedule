<?php

use App\Http\Controllers\Api\ApiAuthController;
use App\Http\Controllers\Api\ApiEventController;
use App\Http\Controllers\Api\ApiFederationController;
use App\Http\Controllers\Api\ApiFeedbackController;
use App\Http\Controllers\Api\ApiGroupController;
use App\Http\Controllers\Api\ApiSaleController;
use App\Http\Controllers\Api\ApiScheduleController;
use App\Http\Controllers\Api\ApiTranslationSuggestionController;
use App\Http\Controllers\GrowthDataController;
use App\Http\Controllers\GuestFunnelBeaconController;
use App\Http\Controllers\RealtimeBeaconController;
use App\Http\Middleware\ApiAuthentication;
use Illuminate\Support\Facades\Route;

// Unauthenticated routes
Route::post('/register/send-code', [ApiAuthController::class, 'sendCode']);
Route::post('/register', [ApiAuthController::class, 'register']);
Route::post('/login', [ApiAuthController::class, 'login']);

// The /admin/realtime beacon, sent by every page's inline script (partials/realtime-beacon).
// Here rather than in web.php because this group has no session, cookies or CSRF: a beacon must
// never create or slide a session, and API routes register before web routes with no domain, so it
// answers same-origin on the apex, app., tenant subdomains and custom domains alike. Not part of the
// public API: it is not in public/api/openapi.json or on /for-ai-agents.
Route::post('/realtime', [RealtimeBeaconController::class, 'store'])
    ->name('realtime.beacon')
    ->middleware('throttle:realtime');

// The guest pages' three browser-side counts (App\Utils\GuestFunnel), sent by partials/guest-funnel.
// Here for the Realtime beacon's reasons. A plain throttle, not a named limiter: at most three
// useful posts per visitor per day, so 30 a minute is room for a shared address and no more.
Route::post('/guest-count', [GuestFunnelBeaconController::class, 'store'])
    ->name('guest_funnel.beacon')
    ->middleware('throttle:30,1');

// The growth payload, for `php artisan app:pull-growth` on the operator's machine. Bearer-token
// auth, hosted only; see GrowthDataController. Here for the same reasons as the beacon: no session,
// cookie or CSRF, and Cloudflare's cache rule already excludes /api. Not part of the public API:
// not in public/api/openapi.json or on /for-ai-agents. Above the ApiAuthentication group, because
// MarketingCountedClaimsTest counts the public endpoints from that marker down.
Route::get('/internal/growth', [GrowthDataController::class, 'show'])
    ->name('api.growth_data')
    ->middleware('throttle:growth_data');

// Nexus only: receives translation suggestions shared by other installs.
// The api group carries no default throttle, so the route sets its own.
if (config('app.is_nexus')) {
    Route::post('/translations/suggestions', [ApiTranslationSuggestionController::class, 'store'])
        ->middleware('throttle:30,1');

    // Federation intake. Signed with the instance's shared secret rather than an
    // API key, since these publish third-party content and links on this domain.
    // Each route carries its own throttle for the same reason as above.
    Route::post('/federation/register', [ApiFederationController::class, 'register'])
        ->middleware('throttle:10,1');
    Route::post('/federation/events', [ApiFederationController::class, 'store'])
        ->middleware('throttle:60,1');
    Route::post('/federation/reconcile', [ApiFederationController::class, 'reconcile'])
        ->middleware('throttle:30,1');
}

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
    Route::post('/events/{subdomain}', [ApiEventController::class, 'store'])->middleware('throttle:30,1');
    Route::get('/events/{id}', [ApiEventController::class, 'show']);
    Route::put('/events/{id}', [ApiEventController::class, 'update']);
    Route::delete('/events/{id}', [ApiEventController::class, 'destroy']);

    // Categories (global system defaults, and per-schedule effective list)
    Route::get('/categories', [ApiEventController::class, 'categories']);
    Route::get('/categories/{subdomain}', [ApiEventController::class, 'categories']);

    // Feedback and fan content (read only)
    Route::get('/feedback', [ApiFeedbackController::class, 'index']);
    Route::get('/fan-content', [ApiFeedbackController::class, 'fanContent']);

    // Sales
    Route::get('/sales', [ApiSaleController::class, 'index']);
    Route::post('/sales', [ApiSaleController::class, 'store']);
    Route::get('/sales/{id}', [ApiSaleController::class, 'show']);
    Route::put('/sales/{id}', [ApiSaleController::class, 'update']);
    Route::delete('/sales/{id}', [ApiSaleController::class, 'destroy']);
});
