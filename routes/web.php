<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StripeController;
use Illuminate\Support\Facades\Route;

if (config('app.env') != 'local') {
    Route::domain('{subdomain}.eventschedule.com')->where(['subdomain' => '^(?!app$).*'])->group(function () {
        Route::get('/sign_up', [RoleController::class, 'signUp'])->name('event.sign_up');
        Route::get('/follow', [RoleController::class, 'follow'])->name('role.follow');
        Route::get('/{other_subdomain?}/{event_name?}', [RoleController::class, 'viewGuest'])->name('event.view_guest');
    });
}

require __DIR__.'/auth.php';

Route::get('/sitemap.xml', [HomeController::class, 'sitemap'])->name('sitemap');
Route::get('/privacy', [HomeController::class, 'privacy'])->name('privacy');
Route::get('/terms', [HomeController::class, 'terms'])->name('terms');
Route::post('/message', [HomeController::class, 'message'])->name('message')->middleware('throttle:2,2');
Route::get('/unsubscribe', [RoleController::class, 'showUnsubscribe'])->name('role.show_unsubscribe');
Route::post('/unsubscribe', [RoleController::class, 'unsubscribe'])->name('role.unsubscribe')->middleware('throttle:2,2');

Route::middleware(['auth', 'verified'])->group(function () 
{
    Route::get('/events', [HomeController::class, 'home'])->name('home');
    
    Route::get('/new/{type}', [RoleController::class, 'create'])->name('new');
    Route::post('/validate_address', [RoleController::class, 'validateAddress'])->name('validate_address')->middleware('throttle:25,1440');
    Route::post('/store', [RoleController::class, 'store'])->name('role.store');
    Route::get('/search_roles', [RoleController::class, 'search'])->name('role.search');
    Route::get('/edit_event/{hash}', [EventController::class, 'editAdmin'])->name('event.edit_admin');
    Route::get('/following', [RoleController::class, 'following'])->name('following');
    
    Route::get('/account', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/account', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/account', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/stripe/link', [StripeController::class, 'link'])->name('stripe.link');
    Route::get('/stripe/complete/{stripe_account_id}', [StripeController::class, 'complete'])->name('stripe.complete');

    Route::post('/{subdomain}/availability', [RoleController::class, 'availability'])->name('role.availability');
    Route::get('/{subdomain}/edit', [RoleController::class, 'edit'])->name('role.edit');
    Route::get('/{subdomain}/subscribe', [RoleController::class, 'subscribe'])->name('role.subscribe');
    Route::get('/{subdomain}/unfollow', [RoleController::class, 'unfollow'])->name('role.unfollow');
    Route::put('/{subdomain}/update', [RoleController::class, 'update'])->name('role.update');
    Route::get('/{subdomain}/delete', [RoleController::class, 'delete'])->name('role.delete');
    Route::get('/{subdomain}/delete_image', [RoleController::class, 'deleteImage'])->name('role.delete_image');
    Route::get('/{subdomain}/add_event', [EventController::class, 'create'])->name('event.create');
    Route::get('/{subdomain}/verify/{hash}', [RoleController::class, 'verify'])->name('role.verification.verify');
    Route::get('/{subdomain}/resend', [RoleController::class, 'resendVerify'])->name('role.verification.resend');    
    Route::get('/{subdomain}/resend_invite/{hash}', [RoleController::class, 'resendInvite'])->name('role.resend_invite');
    Route::post('/{subdomain}/store_event', [EventController::class, 'store'])->name('event.store');    
    Route::get('/{subdomain}/edit_event/{hash}', [EventController::class, 'edit'])->name('event.edit');
    Route::get('/{subdomain}/delete_event/{hash}', [EventController::class, 'delete'])->name('event.delete');
    Route::put('/{subdomain}/update_event/{hash}', [EventController::class, 'update'])->name('event.update');
    Route::get('/{subdomain}/delete_event_image', [EventController::class, 'deleteImage'])->name('event.delete_image');
    Route::get('/{subdomain}/requests/accept_event/{hash}', [EventController::class, 'accept'])->name('event.accept');
    Route::get('/{subdomain}/requests/decline_event/{hash}', [EventController::class, 'decline'])->name('event.decline');
    Route::post('/{subdomain}/profile/update_links', [RoleController::class, 'updateLinks'])->name('role.update_links');
    Route::post('/{subdomain}/profile/remove_links', [RoleController::class, 'removeLinks'])->name('role.remove_links');
    Route::get('/{subdomain}/followers/qr_code', [RoleController::class, 'qrCode'])->name('role.qr_code');
    Route::get('/{subdomain}/team/add_member', [RoleController::class, 'createMember'])->name('role.create_member');
    Route::post('/{subdomain}/team/add_member', [RoleController::class, 'storeMember'])->name('role.store_member');
    Route::get('/{subdomain}/team/remove_member/{hash}', [RoleController::class, 'removeMember'])->name('role.remove_member');
    Route::get('/{subdomain}/curate_event/{hash}', [EventController::class, 'curate'])->name('event.curate');
    Route::delete('/{subdomain}/uncurate_event/{hash}', [EventController::class, 'uncurate'])->name('event.uncurate');
    Route::get('/{subdomain}/{tab?}', [RoleController::class, 'viewAdmin'])->name('role.view_admin')->where('tab', 'schedule|availability|requests|profile|followers|team');
});

if (config('app.env') == 'local') {
    Route::get('/{subdomain}/view/{other_subdomain?}/{event_name?}', [RoleController::class, 'viewGuest'])->name('event.view_guest');
    Route::get('/{subdomain}/view', [RoleController::class, 'viewGuest'])->name('role.view_guest');
    Route::get('/{subdomain}/sign_up', [RoleController::class, 'signUp'])->name('event.sign_up');
    Route::get('/{subdomain}/follow', [RoleController::class, 'follow'])->name('role.follow');
} else {
    Route::domain('{subdomain}.eventschedule.com')->where(['subdomain' => '^(?!app$).*'])->group(function () {
        Route::get('/', [RoleController::class, 'viewGuest'])->name('role.view_guest');
    });
}

Route::get('/', [HomeController::class, 'landing'])->name('landing');
