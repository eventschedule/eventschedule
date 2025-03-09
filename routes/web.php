<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\InvoiceNinjaController;
use App\Http\Controllers\AppController;
use Illuminate\Support\Facades\Route;

if (config('app.hosted')) {
    Route::group(['domain' => 'eventschedule.com'], function () {
        Route::get('{path?}', function ($path = null) {
            return redirect('https://www.eventschedule.com/' . ($path ? $path : ''), 301);
        })->where('path', '.*');
    });

    Route::domain('{subdomain}.eventschedule.com')->where(['subdomain' => '^(?!www|app).*'])->group(function () {
        Route::get('/sign_up', [RoleController::class, 'signUp'])->name('event.sign_up');
        Route::get('/follow', [RoleController::class, 'follow'])->name('role.follow');
        Route::post('/checkout', [TicketController::class, 'checkout'])->name('event.checkout');
        Route::get('/checkout/success/{sale_id}/{date}', [TicketController::class, 'success'])->name('checkout.success');
        Route::get('/checkout/cancel/{sale_id}/{date}', [TicketController::class, 'cancel'])->name('checkout.cancel');
        Route::get('/{slug}', [RoleController::class, 'viewGuest'])->name('event.view_guest');
    });
} else {
    Route::match(['get', 'post'], '/update', [AppController::class, 'update'])->name('app.update');
    Route::post('/test_database', [AppController::class, 'testDatabase'])->name('app.test_database');
}

require __DIR__.'/auth.php';

Route::get('/sitemap.xml', [HomeController::class, 'sitemap'])->name('sitemap');
Route::get('/unsubscribe', [RoleController::class, 'showUnsubscribe'])->name('role.show_unsubscribe');
Route::post('/unsubscribe', [RoleController::class, 'unsubscribe'])->name('role.unsubscribe')->middleware('throttle:2,2');

Route::post('/stripe/webhook', [StripeController::class, 'webhook'])->name('stripe.webhook');
Route::post('/invoiceninja/webhook/{secret}', [InvoiceNinjaController::class, 'webhook'])->name('invoiceninja.webhook');

Route::get('/release_tickets', [TicketController::class, 'release'])->name('release_tickets');
Route::get('/translate_data', [AppController::class, 'translateData'])->name('translate_data');

Route::get('/ticket/qr_code/{event_id}/{secret}', [TicketController::class, 'qrCode'])->name('ticket.qr_code');
Route::get('/ticket/view/{event_id}/{secret}', [TicketController::class, 'view'])->name('ticket.view');

Route::middleware(['auth', 'verified'])->group(function () 
{
    Route::get('/events', [HomeController::class, 'home'])->name('home');
    Route::get('/new/{type}', [RoleController::class, 'create'])->name('new');
    Route::post('/validate_address', [RoleController::class, 'validateAddress'])->name('validate_address')->middleware('throttle:25,1440');
    Route::post('/store', [RoleController::class, 'store'])->name('role.store');
    Route::get('/search_roles', [RoleController::class, 'search'])->name('role.search');
    Route::get('/edit_event/{hash}', [EventController::class, 'editAdmin'])->name('event.edit_admin');
    Route::get('/following', [RoleController::class, 'following'])->name('following');
    Route::get('/tickets', [TicketController::class, 'tickets'])->name('tickets');
    Route::get('/sales', [TicketController::class, 'sales'])->name('sales');
    Route::post('/sales/action/{sale_id}', [TicketController::class, 'handleAction'])->name('sales.action');
    
    Route::get('/account', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/account', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/account/payments', [ProfileController::class, 'updatePayments'])->name('profile.update_payments');
    Route::delete('/account', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/stripe/link', [StripeController::class, 'link'])->name('stripe.link');
    Route::get('/stripe/unlink', [StripeController::class, 'unlink'])->name('stripe.unlink');
    Route::get('/stripe/complete', [StripeController::class, 'complete'])->name('stripe.complete');
    Route::get('/invoiceninja/unlink', [InvoiceNinjaController::class, 'unlink'])->name('invoiceninja.unlink');
    
    Route::get('/scan', [TicketController::class, 'scan'])->name('ticket.scan');
    Route::post('/ticket/view/{event_id}/{secret}', [TicketController::class, 'scanned'])->name('ticket.scanned');

    Route::get('/{subdomain}/change_plan/{plan_type}', [RoleController::class, 'changePlan'])->name('role.change_plan');
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
    Route::get('/{subdomain}/import', [EventController::class, 'showImport'])->name('event.show_import');
    Route::post('/{subdomain}/parse', [EventController::class, 'parse'])->name('event.parse');    
    Route::post('/{subdomain}/import', [EventController::class, 'import'])->name('event.import');    
    Route::get('/{subdomain}/{tab}', [RoleController::class, 'viewAdmin'])->name('role.view_admin')->where('tab', 'schedule|availability|requests|profile|followers|team|plan');

    Route::get('/tmp/event-image/{filename?}', function ($filename = null) {
        if (!$filename) {
            abort(404);
        }
    
        $path = '/tmp/event_' . $filename;
        if (file_exists($path)) {
            return response()->file($path);
        }
        abort(404);
    })->name('event.tmp_image');
});

if (config('app.hosted')) {
    Route::domain('{subdomain}.eventschedule.com')->where(['subdomain' => '^(?!www|app).*'])->group(function () {
        Route::get('/', [RoleController::class, 'viewGuest'])->name('role.view_guest');
    });
} else {
    Route::get('/{subdomain}/sign_up', [RoleController::class, 'signUp'])->name('event.sign_up');
    Route::get('/{subdomain}/follow', [RoleController::class, 'follow'])->name('role.follow');
    Route::post('/{subdomain}/checkout', [TicketController::class, 'checkout'])->name('event.checkout');
    Route::get('/{subdomain}/checkout/success/{sale_id}', [TicketController::class, 'success'])->name('checkout.success');
    Route::get('/{subdomain}/checkout/cancel/{sale_id}', [TicketController::class, 'cancel'])->name('checkout.cancel');
    Route::get('/{subdomain}', [RoleController::class, 'viewGuest'])->name('role.view_guest');
    Route::get('/{subdomain}/{slug}', [RoleController::class, 'viewGuest'])->name('event.view_guest');
}

Route::get('/{slug?}', [HomeController::class, 'landing'])->name('landing');


