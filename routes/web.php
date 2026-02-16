<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Api\ApiSettingsController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CalDAVController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\GoogleCalendarWebhookController;
use App\Http\Controllers\GraphicController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceNinjaController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NewsletterTrackingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SubscriptionWebhookController;
use App\Http\Controllers\TicketController;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', [AppController::class, 'robots']);

if (config('app.hosted') && ! config('app.is_testing')) {
    if (config('app.env') != 'local') {
        Route::domain('blog.eventschedule.com')->group(function () {
            Route::get('/', [BlogController::class, 'index'])->name('blog.index');
            Route::get('/feed', [BlogController::class, 'feed'])->name('blog.feed');
            Route::get('/{slug}', [BlogController::class, 'show'])->name('blog.show');
        });
    }

    Route::domain('{subdomain}.eventschedule.com')->where(['subdomain' => '^(?!www|app).*'])->group(function () {
        Route::get('/api/past-events', [RoleController::class, 'listPastEvents'])->name('role.list_past_events');
        Route::get('/api/calendar-events', [RoleController::class, 'calendarEvents'])->name('role.calendar_events');
        Route::get('/request', [RoleController::class, 'request'])->name('role.request');
        Route::get('/follow', [RoleController::class, 'follow'])->name('role.follow');
        Route::get('/guest-add', [EventController::class, 'showGuestImport'])->name('event.guest_import');
        Route::post('/guest-add', [EventController::class, 'guestImport'])->name('event.guest_import.store');
        Route::post('/guest-parse', [EventController::class, 'guestParse'])->name('event.guest_parse');
        Route::post('/guest-upload-image', [EventController::class, 'guestUploadImage'])->name('event.guest_upload_image');
        Route::get('/guest-search-youtube', [RoleController::class, 'guestSearchYouTube'])->name('role.guest_search_youtube');
        Route::get('/curate-event/{hash}', [EventController::class, 'curate'])->name('event.curate');
        Route::post('/submit-video/{event_hash}', [EventController::class, 'submitVideo'])->name('event.submit_video')->middleware('throttle:10,60');
        Route::post('/submit-comment/{event_hash}', [EventController::class, 'submitComment'])->name('event.submit_comment')->middleware('throttle:20,60');
        Route::post('/checkout', [TicketController::class, 'checkout'])->name('event.checkout');
        Route::get('/checkout/success/{sale_id}/{date}', [TicketController::class, 'success'])->name('checkout.success');
        Route::get('/checkout/cancel/{sale_id}/{date}', [TicketController::class, 'cancel'])->name('checkout.cancel');
        Route::get('/payment/success/{sale_id}', [TicketController::class, 'paymentUrlSuccess'])->name('payment_url.success');
        Route::get('/payment/cancel/{sale_id}', [TicketController::class, 'paymentUrlCancel'])->name('payment_url.cancel');
        // Event with ID and date (recurring)
        Route::get('/{slug}/{id}/{date}', [RoleController::class, 'viewGuest'])
            ->name('event.view_guest_full')
            ->where(['date' => '\d{4}-\d{2}-\d{2}', 'id' => '[A-Za-z0-9+/=]+']);

        // Event with ID only
        Route::get('/{slug}/{id}', [RoleController::class, 'viewGuest'])
            ->name('event.view_guest_with_id')
            ->where(['id' => '[A-Za-z0-9+/=]+']);

        // Existing catch-all remains last
        Route::get('/{slug}', [RoleController::class, 'viewGuest'])->name('event.view_guest');
    });
} else {
    Route::match(['get', 'post'], '/update', [AppController::class, 'update'])->name('app.update')->middleware(['auth', 'verified']);
    Route::post('/test_database', [AppController::class, 'testDatabase'])->name('app.test_database');
}

require __DIR__.'/auth.php';

Route::get('/sitemap.xml', [HomeController::class, 'sitemap'])->name('sitemap');
Route::get('/sitemap.xml.gz', [HomeController::class, 'sitemap'])->name('sitemap.gz');
Route::get('/unsubscribe', [RoleController::class, 'showUnsubscribe'])->name('role.show_unsubscribe');
Route::post('/unsubscribe', [RoleController::class, 'unsubscribe'])->name('role.unsubscribe')->middleware('throttle:2,2');
Route::get('/user/unsubscribe', [RoleController::class, 'unsubscribeUser'])->name('user.unsubscribe')->middleware('throttle:2,2');
Route::post('/clear-pending-request', [EventController::class, 'clearPendingRequest'])->name('event.clear_pending_request');

// Newsletter tracking routes (public, no auth)
Route::get('/nl/o/{token}', [NewsletterTrackingController::class, 'trackOpen'])->name('newsletter.track_open')->middleware('throttle:60,1');
Route::get('/nl/c/{token}/{encodedUrl}', [NewsletterTrackingController::class, 'trackClick'])->name('newsletter.track_click')->where('encodedUrl', '.*')->middleware('throttle:60,1');
Route::get('/nl/u/{token}', [NewsletterTrackingController::class, 'showUnsubscribe'])->name('newsletter.show_unsubscribe');
Route::post('/nl/u/{token}', [NewsletterTrackingController::class, 'unsubscribe'])->name('newsletter.unsubscribe')->middleware('throttle:2,2');

Route::post('/stripe/webhook', [StripeController::class, 'webhook'])->name('stripe.webhook')->middleware('throttle:60,1');
Route::post('/stripe/subscription-webhook', [SubscriptionWebhookController::class, 'handleWebhook'])->name('stripe.subscription_webhook')->middleware('throttle:60,1');
Route::post('/invoiceninja/webhook/{secret?}', [InvoiceNinjaController::class, 'webhook'])->name('invoiceninja.webhook')->middleware('throttle:60,1');

// Google Calendar webhook routes (no auth required)
Route::get('/google-calendar/webhook', [GoogleCalendarWebhookController::class, 'verify'])->name('google.calendar.webhook.verify')->middleware('throttle:10,1');
Route::post('/google-calendar/webhook', [GoogleCalendarWebhookController::class, 'handle'])->name('google.calendar.webhook.handle')->middleware('throttle:60,1');

Route::get('/release_tickets', [TicketController::class, 'release'])->name('release_tickets')->middleware('throttle:5,1');
Route::get('/translate_data', [AppController::class, 'translateData'])->name('translate_data')->middleware('throttle:5,1');

Route::get('/ticket/qr_code/{event_id}/{secret}', [TicketController::class, 'qrCode'])->name('ticket.qr_code')->middleware('throttle:100,1');
Route::get('/ticket/view/{event_id}/{secret}', [TicketController::class, 'view'])->name('ticket.view')->middleware('throttle:100,1');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/events', [HomeController::class, 'home'])->name('home');
    Route::get('/events/api/calendar-events', [HomeController::class, 'calendarEvents'])->name('home.calendar_events');
    Route::post('/events/feedback', [HomeController::class, 'submitFeedback'])->name('home.feedback');
    Route::get('/new/{type}', [RoleController::class, 'create'])->name('new');
    Route::post('/validate_address', [RoleController::class, 'validateAddress'])->name('validate_address')->middleware('throttle:25,1440');
    Route::post('/store', [RoleController::class, 'store'])->name('role.store');
    Route::get('/search-roles', [RoleController::class, 'search'])->name('role.search');
    Route::get('/search-events/{subdomain}', [RoleController::class, 'searchEvents'])->name('role.search_events');
    Route::get('/admin-edit-event/{hash}', [EventController::class, 'editAdmin'])->name('event.edit_admin');
    Route::get('/following', [RoleController::class, 'following'])->name('following');
    Route::post('/following/bulk-unfollow', [RoleController::class, 'bulkUnfollow'])->name('following.bulk-unfollow');
    Route::get('/tickets', [TicketController::class, 'tickets'])->name('tickets');
    Route::get('/sales', [TicketController::class, 'sales'])->name('sales');
    Route::post('/sales/action/{sale_id}', [TicketController::class, 'handleAction'])->name('sales.action');
    Route::post('/sales/resend-email/{sale_id}', [TicketController::class, 'resendEmail'])->name('sales.resend_email');
    Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics');

    // Newsletter routes (flat, like analytics - schedule selected via ?role_id= query param)
    Route::get('/newsletters', [NewsletterController::class, 'index'])->name('newsletter.index');
    Route::get('/newsletters/create', [NewsletterController::class, 'create'])->name('newsletter.create');
    Route::post('/newsletters', [NewsletterController::class, 'store'])->name('newsletter.store');
    Route::post('/newsletters/preview-draft', [NewsletterController::class, 'previewDraft'])->name('newsletter.preview_draft');
    Route::get('/newsletters/events', [NewsletterController::class, 'getEvents'])->name('newsletter.events');
    Route::get('/newsletters/{hash}/edit', [NewsletterController::class, 'edit'])->name('newsletter.edit');
    Route::put('/newsletters/{hash}', [NewsletterController::class, 'update'])->name('newsletter.update');
    Route::delete('/newsletters/{hash}', [NewsletterController::class, 'delete'])->name('newsletter.delete');
    Route::post('/newsletters/{hash}/send', [NewsletterController::class, 'send'])->name('newsletter.send');
    Route::post('/newsletters/{hash}/schedule', [NewsletterController::class, 'schedule'])->name('newsletter.schedule');
    Route::post('/newsletters/{hash}/cancel', [NewsletterController::class, 'cancel'])->name('newsletter.cancel');
    Route::post('/newsletters/{hash}/clone', [NewsletterController::class, 'cloneNewsletter'])->name('newsletter.clone');
    Route::post('/newsletters/{hash}/preview', [NewsletterController::class, 'preview'])->name('newsletter.preview');
    Route::post('/newsletters/{hash}/test-send', [NewsletterController::class, 'testSend'])->name('newsletter.test_send');
    Route::get('/newsletters/{hash}/stats', [NewsletterController::class, 'stats'])->name('newsletter.stats');
    Route::post('/newsletters/{hash}/ab-test', [NewsletterController::class, 'createAbTest'])->name('newsletter.ab_test');
    Route::post('/newsletters/{hash}/ab-send', [NewsletterController::class, 'sendAbTest'])->name('newsletter.ab_send');
    Route::get('/newsletter-segments', [NewsletterController::class, 'segments'])->name('newsletter.segments');
    Route::post('/newsletter-segments', [NewsletterController::class, 'storeSegment'])->name('newsletter.segment.store');
    Route::put('/newsletter-segments/{hash}', [NewsletterController::class, 'updateSegment'])->name('newsletter.segment.update');
    Route::delete('/newsletter-segments/{hash}', [NewsletterController::class, 'deleteSegment'])->name('newsletter.segment.delete');
    Route::get('/newsletter-import', [NewsletterController::class, 'importForm'])->name('newsletter.import');
    Route::post('/newsletter-import', [NewsletterController::class, 'importStore'])->name('newsletter.import.store');

    Route::get('/settings', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/settings', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/settings/payments', [ProfileController::class, 'updatePayments'])->name('profile.update_payments');
    Route::delete('/settings', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/stripe/link', [StripeController::class, 'link'])->name('stripe.link');
    Route::post('/stripe/unlink', [StripeController::class, 'unlink'])->name('stripe.unlink');
    Route::get('/stripe/complete', [StripeController::class, 'complete'])->name('stripe.complete');
    Route::post('/invoiceninja/unlink', [InvoiceNinjaController::class, 'unlink'])->name('invoiceninja.unlink');
    Route::post('/payment_url/unlink', [ProfileController::class, 'unlinkPaymentUrl'])->name('profile.unlink_payment_url');

    // Google Calendar routes
    Route::get('/google-calendar/redirect', [GoogleCalendarController::class, 'redirect'])->name('google.calendar.redirect');
    Route::get('/google-calendar/callback', [GoogleCalendarController::class, 'callback'])->name('google.calendar.callback');
    Route::get('/google-calendar/reauthorize', [GoogleCalendarController::class, 'reauthorize'])->name('google.calendar.reauthorize');
    Route::get('/google-calendar/disconnect', [GoogleCalendarController::class, 'disconnect'])->name('google.calendar.disconnect');
    Route::get('/google-calendar/calendars', [GoogleCalendarController::class, 'getCalendars'])->name('google.calendar.calendars');
    Route::post('/google-calendar/sync/{subdomain}', [GoogleCalendarController::class, 'sync'])->name('google.calendar.sync');
    Route::post('/google-calendar/sync-event/{subdomain}/{eventId}', [GoogleCalendarController::class, 'syncEvent'])->name('google.calendar.sync_event');
    Route::delete('/google-calendar/unsync-event/{subdomain}/{eventId}', [GoogleCalendarController::class, 'unsyncEvent'])->name('google.calendar.unsync_event');

    // CalDAV routes
    Route::post('/caldav/test-connection', [CalDAVController::class, 'testConnection'])->name('caldav.test_connection')->middleware('throttle:10,1');
    Route::post('/caldav/discover-calendars', [CalDAVController::class, 'discoverCalendars'])->name('caldav.discover_calendars')->middleware('throttle:10,1');
    Route::post('/caldav/settings/{subdomain}', [CalDAVController::class, 'saveSettings'])->name('caldav.save_settings')->middleware('throttle:10,1');
    Route::delete('/caldav/disconnect/{subdomain}', [CalDAVController::class, 'disconnect'])->name('caldav.disconnect')->middleware('throttle:10,1');
    Route::post('/caldav/sync/{subdomain}', [CalDAVController::class, 'sync'])->name('caldav.sync')->middleware('throttle:5,1');
    Route::patch('/caldav/sync-direction/{subdomain}', [CalDAVController::class, 'updateSyncDirection'])->name('caldav.update_sync_direction')->middleware('throttle:30,1');

    Route::get('/scan', [TicketController::class, 'scan'])->name('ticket.scan');
    Route::post('/ticket/view/{event_id}/{secret}', [TicketController::class, 'scanned'])->name('ticket.scanned');

    Route::get('/{subdomain}/api/admin-calendar-events', [RoleController::class, 'adminCalendarEvents'])->name('role.admin_calendar_events');
    Route::get('/{subdomain}/change-plan/{plan_type}', [RoleController::class, 'changePlan'])->name('role.change_plan');
    Route::post('/{subdomain}/availability', [RoleController::class, 'availability'])->name('role.availability');
    Route::get('/{subdomain}/edit', [RoleController::class, 'edit'])->name('role.edit');
    Route::get('/{subdomain}/subscribe', [SubscriptionController::class, 'show'])->name('role.subscribe');
    Route::post('/{subdomain}/subscribe', [SubscriptionController::class, 'store'])->name('subscription.store');
    Route::get('/{subdomain}/subscription/portal', [SubscriptionController::class, 'portal'])->name('subscription.portal');
    Route::post('/{subdomain}/subscription/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::post('/{subdomain}/subscription/resume', [SubscriptionController::class, 'resume'])->name('subscription.resume');
    Route::post('/{subdomain}/subscription/swap', [SubscriptionController::class, 'swap'])->name('subscription.swap');
    Route::get('/{subdomain}/unfollow', [RoleController::class, 'unfollow'])->name('role.unfollow');
    Route::put('/{subdomain}/update', [RoleController::class, 'update'])->name('role.update');
    Route::post('/{subdomain}/test-email', [RoleController::class, 'testEmail'])->name('role.test_email');
    Route::delete('/{subdomain}/delete', [RoleController::class, 'delete'])->name('role.delete');
    Route::delete('/{subdomain}/delete-image', [RoleController::class, 'deleteImage'])->name('role.delete_image');
    Route::get('/{subdomain}/add-event', [EventController::class, 'create'])->name('event.create');
    Route::get('/{subdomain}/verify/{hash}', [RoleController::class, 'verify'])->name('role.verification.verify');
    Route::get('/{subdomain}/resend', [RoleController::class, 'resendVerify'])->name('role.verification.resend');
    Route::get('/{subdomain}/resend-invite/{hash}', [RoleController::class, 'resendInvite'])->name('role.resend_invite');
    Route::post('/{subdomain}/store-event', [EventController::class, 'store'])->name('event.store');
    Route::get('/{subdomain}/edit-event/{hash}', [EventController::class, 'edit'])->name('event.edit');
    Route::get('/{subdomain}/clone-event/{hash}', [EventController::class, 'clone'])->name('event.clone');
    Route::delete('/{subdomain}/delete-event/{hash}', [EventController::class, 'delete'])->name('event.delete');
    Route::put('/{subdomain}/update-event/{hash}', [EventController::class, 'update'])->name('event.update');
    Route::delete('/{subdomain}/delete-event-image', [EventController::class, 'deleteImage'])->name('event.delete_image');
    Route::get('/{subdomain}/events-graphic', [GraphicController::class, 'generateGraphic'])->name('event.generate_graphic');
    Route::get('/{subdomain}/events-graphic/data', [GraphicController::class, 'generateGraphicData'])->name('event.generate_graphic_data');
    Route::get('/{subdomain}/events-graphic/download', [GraphicController::class, 'downloadGraphic'])->name('event.download_graphic');
    Route::get('/{subdomain}/events-graphic/settings', [GraphicController::class, 'getSettings'])->name('event.graphic_settings');
    Route::post('/{subdomain}/events-graphic/settings', [GraphicController::class, 'saveSettings'])->name('event.save_graphic_settings');
    Route::post('/{subdomain}/events-graphic/test-email', [GraphicController::class, 'sendTestEmail'])->name('event.graphic_test_email');
    Route::post('/{subdomain}/events-graphic/header-image', [GraphicController::class, 'uploadHeaderImage'])->name('event.graphic_upload_header_image');
    Route::delete('/{subdomain}/events-graphic/header-image', [GraphicController::class, 'deleteHeaderImage'])->name('event.graphic_delete_header_image');
    Route::get('/{subdomain}/clear-videos/{event_hash}/{role_hash}', [EventController::class, 'clearVideos'])->name('event.clear_videos');
    Route::post('/{subdomain}/requests/accept-event/{hash}', [EventController::class, 'accept'])->name('event.accept');
    Route::post('/{subdomain}/requests/decline-event/{hash}', [EventController::class, 'decline'])->name('event.decline');
    Route::post('/{subdomain}/requests/accept-all', [EventController::class, 'acceptAll'])->name('event.accept_all');
    Route::post('/{subdomain}/profile/update-links', [RoleController::class, 'updateLinks'])->name('role.update_links');
    Route::post('/{subdomain}/profile/remove-links', [RoleController::class, 'removeLinks'])->name('role.remove_links');
    Route::get('/{subdomain}/followers/qr-code', [RoleController::class, 'qrCode'])->name('role.qr_code');
    Route::get('/{subdomain}/team/add-member', [RoleController::class, 'createMember'])->name('role.create_member');
    Route::post('/{subdomain}/team/add-member', [RoleController::class, 'storeMember'])->name('role.store_member');
    Route::delete('/{subdomain}/team/remove-member/{hash}', [RoleController::class, 'removeMember'])->name('role.remove_member');
    Route::delete('/{subdomain}/uncurate-event/{hash}', [EventController::class, 'uncurate'])->name('event.uncurate');
    Route::get('/{subdomain}/import', [EventController::class, 'showImport'])->name('event.show_import');
    Route::post('/{subdomain}/parse', [EventController::class, 'parse'])->name('event.parse');
    Route::post('/{subdomain}/parse-event-parts', [EventController::class, 'parseEventParts'])->name('event.parse_parts');
    Route::post('/{subdomain}/import', [EventController::class, 'import'])->name('event.import');
    Route::post('/{subdomain}/test-import', [RoleController::class, 'testImport'])->name('role.test_import');
    Route::get('/{subdomain}/search-youtube', [RoleController::class, 'searchYouTube'])->name('role.search_youtube');
    Route::get('/{subdomain}/match-videos', [RoleController::class, 'getTalentRolesWithoutVideos'])->name('role.talent_roles_without_videos');
    Route::post('/{subdomain}/save-video', [RoleController::class, 'saveVideo'])->name('role.save_video');
    Route::post('/{subdomain}/save-videos', [RoleController::class, 'saveVideos'])->name('role.save_videos');

    Route::post('/{subdomain}/approve-video/{hash}', [EventController::class, 'approveVideo'])->name('event.approve_video');
    Route::delete('/{subdomain}/reject-video/{hash}', [EventController::class, 'rejectVideo'])->name('event.reject_video');
    Route::post('/{subdomain}/approve-comment/{hash}', [EventController::class, 'approveComment'])->name('event.approve_comment');
    Route::delete('/{subdomain}/reject-comment/{hash}', [EventController::class, 'rejectComment'])->name('event.reject_comment');

    Route::get('/{subdomain}/scan-agenda', [EventController::class, 'scanAgenda'])->name('event.scan_agenda');
    Route::post('/{subdomain}/save-event-parts', [EventController::class, 'saveEventParts'])->name('event.save_parts');

    Route::get('/{subdomain}/{tab}', [RoleController::class, 'viewAdmin'])->name('role.view_admin')->where('tab', 'schedule|availability|requests|profile|followers|team|plan|videos')->where('subdomain', '(?!docs(?=/|$))[^/]+');

    Route::post('/{subdomain}/upload-image', [EventController::class, 'uploadImage'])->name('event.upload_image');

    Route::get('/api/documentation', fn () => redirect()->route('marketing.docs.developer.api'))->name('api.documentation');

    Route::patch('/api-settings', [ApiSettingsController::class, 'update'])->name('api-settings.update');

    // Admin password confirmation (outside admin middleware - the admin middleware redirects here)
    Route::get('/admin/confirm-password', [AdminController::class, 'showConfirmPassword'])
        ->name('admin.password.confirm.show');
    Route::post('/admin/confirm-password', [AdminController::class, 'confirmPassword'])
        ->name('admin.password.confirm')
        ->middleware('throttle:5,1');

    // Admin routes (only for admin users) - protected by admin middleware for defense-in-depth
    Route::middleware(['admin', 'throttle:30,1'])->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::redirect('/admin', '/admin/dashboard');
        Route::get('/admin/users', [AdminController::class, 'users'])->name('admin.users');
        Route::get('/admin/revenue', [AdminController::class, 'revenue'])->name('admin.revenue');
        Route::get('/admin/analytics', [AdminController::class, 'analytics'])->name('admin.analytics');
        Route::get('/admin/usage', [AdminController::class, 'usage'])->name('admin.usage');
        Route::post('/admin/translation/retry', [AdminController::class, 'retryTranslation'])->name('admin.translation.retry');
        Route::get('/admin/plans', [AdminController::class, 'plans'])->name('admin.plans');
        Route::get('/admin/plans/{role}/edit', [AdminController::class, 'editPlan'])->name('admin.plans.edit');
        Route::put('/admin/plans/{role}', [AdminController::class, 'updatePlan'])->name('admin.plans.update');

        Route::get('/admin/audit-log', [AdminController::class, 'auditLog'])->name('admin.audit_log');

        // Admin queue routes
        Route::get('/admin/queue', [AdminController::class, 'queue'])->name('admin.queue');
        Route::post('/admin/queue/retry/{id}', [AdminController::class, 'queueRetry'])->name('admin.queue.retry');
        Route::post('/admin/queue/delete/{id}', [AdminController::class, 'queueDelete'])->name('admin.queue.delete');
        Route::post('/admin/queue/retry-all', [AdminController::class, 'queueRetryAll'])->name('admin.queue.retry-all');
        Route::post('/admin/queue/clear-failed', [AdminController::class, 'queueClearFailed'])->name('admin.queue.clear-failed');
        Route::post('/admin/queue/flush-pending', [AdminController::class, 'queueFlushPending'])->name('admin.queue.flush-pending');

        // Admin blog routes
        Route::get('/admin/blog', [BlogController::class, 'adminIndex'])->name('blog.admin.index');
        Route::get('/admin/blog/create', [BlogController::class, 'create'])->name('blog.create');
        Route::post('/admin/blog', [BlogController::class, 'store'])->name('blog.store');
        Route::get('/admin/blog/{blog_post}/edit', [BlogController::class, 'edit'])->name('blog.edit');
        Route::put('/admin/blog/{blog_post}', [BlogController::class, 'update'])->name('blog.update');
        Route::delete('/admin/blog/{blog_post}', [BlogController::class, 'destroy'])->name('blog.destroy');
        Route::post('/admin/blog/generate-content', [BlogController::class, 'generateContent'])->name('blog.generate-content');
    });
});

Route::get('/tmp/event-image/{filename?}', [AppController::class, 'tempEventImage'])->name('event.tmp_image');

// Marketing pages - only shown on the nexus (eventschedule.com)
if (config('app.is_nexus')) {
    if (config('app.is_testing')) {
        Route::get('/', [MarketingController::class, 'index'])->name('marketing.index');
        Route::get('/features', [MarketingController::class, 'features'])->name('marketing.features');
        Route::get('/pricing', [MarketingController::class, 'pricing'])->name('marketing.pricing');
        Route::get('/about', [MarketingController::class, 'about'])->name('marketing.about');
        Route::get('/examples', [MarketingController::class, 'demos'])->name('marketing.demos');
        Route::get('/faq', [MarketingController::class, 'faq'])->name('marketing.faq');
        Route::get('/why-create-account', [MarketingController::class, 'whyCreateAccount'])->name('marketing.why_create_account');
        Route::get('/features/ticketing', [MarketingController::class, 'ticketing'])->name('marketing.ticketing');
        Route::get('/features/ai', [MarketingController::class, 'ai'])->name('marketing.ai');
        Route::get('/features/calendar-sync', [MarketingController::class, 'calendarSync'])->name('marketing.calendar_sync');
        Route::get('/google-calendar', [MarketingController::class, 'googleCalendar'])->name('marketing.google_calendar');
        Route::get('/caldav', [MarketingController::class, 'caldav'])->name('marketing.caldav');
        Route::get('/stripe', [MarketingController::class, 'stripe'])->name('marketing.stripe');
        Route::get('/invoiceninja', [MarketingController::class, 'invoiceninja'])->name('marketing.invoiceninja');
        Route::get('/features/analytics', [MarketingController::class, 'analytics'])->name('marketing.analytics');
        Route::get('/features/integrations', [MarketingController::class, 'integrations'])->name('marketing.integrations');
        Route::get('/features/custom-fields', [MarketingController::class, 'customFields'])->name('marketing.custom_fields');
        Route::get('/features/team-scheduling', [MarketingController::class, 'teamScheduling'])->name('marketing.team_scheduling');
        Route::get('/features/sub-schedules', [MarketingController::class, 'subSchedules'])->name('marketing.sub_schedules');
        Route::get('/features/online-events', [MarketingController::class, 'onlineEvents'])->name('marketing.online_events');
        Route::get('/open-source', [MarketingController::class, 'openSource'])->name('marketing.open_source');
        Route::get('/features/newsletters', [MarketingController::class, 'newsletters'])->name('marketing.newsletters');
        Route::get('/features/recurring-events', [MarketingController::class, 'recurringEvents'])->name('marketing.recurring_events');
        Route::get('/features/embed-calendar', [MarketingController::class, 'embedCalendar'])->name('marketing.embed_calendar');
        Route::get('/features/fan-videos', [MarketingController::class, 'fanVideos'])->name('marketing.fan_videos');
        // Redirects from old feature URLs
        Route::get('/wp/analytics', fn () => redirect()->route('marketing.analytics', [], 301));
        Route::get('/wp/newsletters', fn () => redirect()->route('marketing.newsletters', [], 301));
        Route::get('/ticketing', fn () => redirect()->route('marketing.ticketing', [], 301));
        Route::get('/ai', fn () => redirect()->route('marketing.ai', [], 301));
        Route::get('/calendar-sync', fn () => redirect()->route('marketing.calendar_sync', [], 301));
        Route::get('/integrations', fn () => redirect()->route('marketing.integrations', [], 301));
        Route::get('/custom-fields', fn () => redirect()->route('marketing.custom_fields', [], 301));
        Route::get('/team-scheduling', fn () => redirect()->route('marketing.team_scheduling', [], 301));
        Route::get('/sub-schedules', fn () => redirect()->route('marketing.sub_schedules', [], 301));
        Route::get('/online-events', fn () => redirect()->route('marketing.online_events', [], 301));
        Route::get('/recurring-events', fn () => redirect()->route('marketing.recurring_events', [], 301));
        Route::get('/embed-calendar', fn () => redirect()->route('marketing.embed_calendar', [], 301));
        Route::get('/for-talent', [MarketingController::class, 'forTalent'])->name('marketing.for_talent');
        Route::get('/for-venues', [MarketingController::class, 'forVenues'])->name('marketing.for_venues');
        Route::get('/for-curators', [MarketingController::class, 'forCurators'])->name('marketing.for_curators');
        Route::get('/for-musicians', [MarketingController::class, 'forMusicians'])->name('marketing.for_musicians');
        Route::get('/for-djs', [MarketingController::class, 'forDJs'])->name('marketing.for_djs');
        Route::get('/for-comedians', [MarketingController::class, 'forComedians'])->name('marketing.for_comedians');
        Route::get('/for-circus-acrobatics', [MarketingController::class, 'forCircusAcrobatics'])->name('marketing.for_circus_acrobatics');
        Route::get('/for-magicians', [MarketingController::class, 'forMagicians'])->name('marketing.for_magicians');
        Route::get('/for-spoken-word', [MarketingController::class, 'forSpokenWord'])->name('marketing.for_spoken_word');
        Route::get('/for-bars', [MarketingController::class, 'forBars'])->name('marketing.for_bars');
        Route::get('/for-nightclubs', [MarketingController::class, 'forNightclubs'])->name('marketing.for_nightclubs');
        Route::get('/for-music-venues', [MarketingController::class, 'forMusicVenues'])->name('marketing.for_music_venues');
        Route::get('/for-theaters', [MarketingController::class, 'forTheaters'])->name('marketing.for_theaters');
        Route::get('/for-dance-groups', [MarketingController::class, 'forDanceGroups'])->name('marketing.for_dance_groups');
        Route::get('/for-theater-performers', [MarketingController::class, 'forTheaterPerformers'])->name('marketing.for_theater_performers');
        Route::get('/for-food-trucks-and-vendors', [MarketingController::class, 'forFoodTrucksAndVendors'])->name('marketing.for_food_trucks_and_vendors');
        Route::get('/for-comedy-clubs', [MarketingController::class, 'forComedyClubs'])->name('marketing.for_comedy_clubs');
        Route::get('/for-restaurants', [MarketingController::class, 'forRestaurants'])->name('marketing.for_restaurants');
        Route::get('/for-breweries-and-wineries', [MarketingController::class, 'forBreweriesAndWineries'])->name('marketing.for_breweries_and_wineries');
        Route::get('/for-art-galleries', [MarketingController::class, 'forArtGalleries'])->name('marketing.for_art_galleries');
        Route::get('/for-community-centers', [MarketingController::class, 'forCommunityCenters'])->name('marketing.for_community_centers');
        Route::get('/for-fitness-and-yoga', [MarketingController::class, 'forFitnessAndYoga'])->name('marketing.for_fitness_and_yoga');
        Route::get('/for-workshop-instructors', [MarketingController::class, 'forWorkshopInstructors'])->name('marketing.for_workshop_instructors');
        Route::get('/for-visual-artists', [MarketingController::class, 'forVisualArtists'])->name('marketing.for_visual_artists');
        Route::get('/for-farmers-markets', [MarketingController::class, 'forFarmersMarkets'])->name('marketing.for_farmers_markets');
        Route::get('/for-hotels-and-resorts', [MarketingController::class, 'forHotelsAndResorts'])->name('marketing.for_hotels_and_resorts');
        Route::get('/for-libraries', [MarketingController::class, 'forLibraries'])->name('marketing.for_libraries');
        Route::get('/for-webinars', [MarketingController::class, 'forWebinars'])->name('marketing.for_webinars');
        Route::get('/for-live-concerts', [MarketingController::class, 'forLiveConcerts'])->name('marketing.for_live_concerts');
        Route::get('/for-online-classes', [MarketingController::class, 'forOnlineClasses'])->name('marketing.for_online_classes');
        Route::get('/for-virtual-conferences', [MarketingController::class, 'forVirtualConferences'])->name('marketing.for_virtual_conferences');
        Route::get('/for-live-qa-sessions', [MarketingController::class, 'forLiveQaSessions'])->name('marketing.for_live_qa_sessions');
        Route::get('/for-watch-parties', [MarketingController::class, 'forWatchParties'])->name('marketing.for_watch_parties');
        Route::get('/for-ai-agents', [MarketingController::class, 'forAiAgents'])->name('marketing.for_ai_agents');
        Route::get('/use-cases', [MarketingController::class, 'useCases'])->name('marketing.use_cases');
        Route::get('/compare', [MarketingController::class, 'compare'])->name('marketing.compare');
        Route::get('/eventbrite-alternative', [MarketingController::class, 'compareEventbrite'])->name('marketing.compare_eventbrite');
        Route::get('/luma-alternative', [MarketingController::class, 'compareLuma'])->name('marketing.compare_luma');
        Route::get('/ticket-tailor-alternative', [MarketingController::class, 'compareTicketTailor'])->name('marketing.compare_ticket_tailor');
        Route::get('/google-calendar-alternative', [MarketingController::class, 'compareGoogleCalendar'])->name('marketing.compare_google_calendar');
        Route::get('/contact', [MarketingController::class, 'contact'])->name('marketing.contact');
        Route::get('/privacy', [MarketingController::class, 'privacy'])->name('marketing.privacy');
        Route::get('/terms-of-service', [MarketingController::class, 'terms'])->name('marketing.terms');
        Route::get('/self-hosting-terms-of-service', [MarketingController::class, 'selfHostingTerms'])->name('marketing.self_hosting_terms');
        Route::get('/selfhost', [MarketingController::class, 'selfHost'])->name('marketing.selfhost');
        Route::get('/saas', [MarketingController::class, 'saas'])->name('marketing.saas');
        Route::get('/docs', [MarketingController::class, 'docsIndex'])->name('marketing.docs');
        // User Guide (at root level)
        Route::get('/docs/getting-started', [MarketingController::class, 'docsGettingStarted'])->name('marketing.docs.getting_started');
        Route::get('/docs/creating-schedules', [MarketingController::class, 'docsCreatingSchedules'])->name('marketing.docs.creating_schedules');
        Route::get('/docs/schedule-basics', [MarketingController::class, 'docsScheduleBasics'])->name('marketing.docs.schedule_basics');
        Route::get('/docs/schedule-styling', [MarketingController::class, 'docsScheduleStyling'])->name('marketing.docs.schedule_styling');
        Route::get('/docs/creating-events', [MarketingController::class, 'docsCreatingEvents'])->name('marketing.docs.creating_events');
        Route::get('/docs/sharing', [MarketingController::class, 'docsSharing'])->name('marketing.docs.sharing');
        Route::get('/docs/tickets', [MarketingController::class, 'docsTickets'])->name('marketing.docs.tickets');
        Route::get('/docs/event-graphics', [MarketingController::class, 'docsEventGraphics'])->name('marketing.docs.event_graphics');
        Route::get('/docs/newsletters', [MarketingController::class, 'docsNewsletters'])->name('marketing.docs.newsletters');
        Route::get('/docs/analytics', [MarketingController::class, 'docsAnalytics'])->name('marketing.docs.analytics');
        Route::get('/docs/account-settings', [MarketingController::class, 'docsAccountSettings'])->name('marketing.docs.account_settings');
        Route::get('/docs/availability', [MarketingController::class, 'docsAvailability'])->name('marketing.docs.availability');
        // Selfhost section
        Route::get('/docs/selfhost', [MarketingController::class, 'docsSelfhostIndex'])->name('marketing.docs.selfhost');
        Route::get('/docs/selfhost/installation', [MarketingController::class, 'docsSelfhostInstallation'])->name('marketing.docs.selfhost.installation');
        Route::get('/docs/selfhost/saas', [MarketingController::class, 'docsSelfhostSaas'])->name('marketing.docs.selfhost.saas');
        Route::get('/docs/selfhost/stripe', [MarketingController::class, 'docsSelfhostStripe'])->name('marketing.docs.selfhost.stripe');
        Route::get('/docs/selfhost/google-calendar', [MarketingController::class, 'docsSelfhostGoogleCalendar'])->name('marketing.docs.selfhost.google_calendar');
        // Developer section
        Route::get('/docs/developer/api', [MarketingController::class, 'docsDeveloperApi'])->name('marketing.docs.developer.api');
        // Redirects from old URLs to new URLs
        Route::get('/docs/installation', fn () => redirect()->route('marketing.docs.selfhost.installation', [], 301));
        Route::get('/docs/saas', fn () => redirect()->route('marketing.docs.selfhost.saas', [], 301));
        Route::get('/docs/stripe', fn () => redirect()->route('marketing.docs.selfhost.stripe', [], 301));
        Route::get('/docs/google-calendar', fn () => redirect()->route('marketing.docs.selfhost.google_calendar', [], 301));
        Route::get('/docs/api', fn () => redirect()->route('marketing.docs.developer.api', [], 301));
        Route::get('/docs/developer', fn () => redirect()->route('marketing.docs.developer.api', [], 301));
    } else {
        // Nexus mode: show marketing pages at root URLs on eventschedule.com
        Route::domain('eventschedule.com')->group(function () {
            Route::get('/', [MarketingController::class, 'index'])->name('marketing.index');
            Route::get('/features', [MarketingController::class, 'features'])->name('marketing.features');
            Route::get('/pricing', [MarketingController::class, 'pricing'])->name('marketing.pricing');
            Route::get('/about', [MarketingController::class, 'about'])->name('marketing.about');
            Route::get('/examples', [MarketingController::class, 'demos'])->name('marketing.demos');
            Route::get('/faq', [MarketingController::class, 'faq'])->name('marketing.faq');
            Route::get('/why-create-account', [MarketingController::class, 'whyCreateAccount'])->name('marketing.why_create_account');
            Route::get('/features/ticketing', [MarketingController::class, 'ticketing'])->name('marketing.ticketing');
            Route::get('/features/ai', [MarketingController::class, 'ai'])->name('marketing.ai');
            Route::get('/features/calendar-sync', [MarketingController::class, 'calendarSync'])->name('marketing.calendar_sync');
            Route::get('/google-calendar', [MarketingController::class, 'googleCalendar'])->name('marketing.google_calendar');
            Route::get('/caldav', [MarketingController::class, 'caldav'])->name('marketing.caldav');
            Route::get('/stripe', [MarketingController::class, 'stripe'])->name('marketing.stripe');
            Route::get('/invoiceninja', [MarketingController::class, 'invoiceninja'])->name('marketing.invoiceninja');
            Route::get('/features/analytics', [MarketingController::class, 'analytics'])->name('marketing.analytics');
            Route::get('/features/integrations', [MarketingController::class, 'integrations'])->name('marketing.integrations');
            Route::get('/features/custom-fields', [MarketingController::class, 'customFields'])->name('marketing.custom_fields');
            Route::get('/features/team-scheduling', [MarketingController::class, 'teamScheduling'])->name('marketing.team_scheduling');
            Route::get('/features/sub-schedules', [MarketingController::class, 'subSchedules'])->name('marketing.sub_schedules');
            Route::get('/features/online-events', [MarketingController::class, 'onlineEvents'])->name('marketing.online_events');
            Route::get('/open-source', [MarketingController::class, 'openSource'])->name('marketing.open_source');
            Route::get('/features/newsletters', [MarketingController::class, 'newsletters'])->name('marketing.newsletters');
            Route::get('/features/recurring-events', [MarketingController::class, 'recurringEvents'])->name('marketing.recurring_events');
            Route::get('/features/embed-calendar', [MarketingController::class, 'embedCalendar'])->name('marketing.embed_calendar');
            Route::get('/features/fan-videos', [MarketingController::class, 'fanVideos'])->name('marketing.fan_videos');
            // Redirects from old feature URLs
            Route::get('/ticketing', fn () => redirect()->route('marketing.ticketing', [], 301));
            Route::get('/ai', fn () => redirect()->route('marketing.ai', [], 301));
            Route::get('/calendar-sync', fn () => redirect()->route('marketing.calendar_sync', [], 301));
            Route::get('/analytics', fn () => redirect()->route('marketing.analytics', [], 301));
            Route::get('/integrations', fn () => redirect()->route('marketing.integrations', [], 301));
            Route::get('/custom-fields', fn () => redirect()->route('marketing.custom_fields', [], 301));
            Route::get('/team-scheduling', fn () => redirect()->route('marketing.team_scheduling', [], 301));
            Route::get('/sub-schedules', fn () => redirect()->route('marketing.sub_schedules', [], 301));
            Route::get('/online-events', fn () => redirect()->route('marketing.online_events', [], 301));
            Route::get('/newsletters', fn () => redirect()->route('marketing.newsletters', [], 301));
            Route::get('/recurring-events', fn () => redirect()->route('marketing.recurring_events', [], 301));
            Route::get('/embed-calendar', fn () => redirect()->route('marketing.embed_calendar', [], 301));
            Route::get('/for-talent', [MarketingController::class, 'forTalent'])->name('marketing.for_talent');
            Route::get('/for-venues', [MarketingController::class, 'forVenues'])->name('marketing.for_venues');
            Route::get('/for-curators', [MarketingController::class, 'forCurators'])->name('marketing.for_curators');
            Route::get('/for-musicians', [MarketingController::class, 'forMusicians'])->name('marketing.for_musicians');
            Route::get('/for-djs', [MarketingController::class, 'forDJs'])->name('marketing.for_djs');
            Route::get('/for-comedians', [MarketingController::class, 'forComedians'])->name('marketing.for_comedians');
            Route::get('/for-circus-acrobatics', [MarketingController::class, 'forCircusAcrobatics'])->name('marketing.for_circus_acrobatics');
            Route::get('/for-magicians', [MarketingController::class, 'forMagicians'])->name('marketing.for_magicians');
            Route::get('/for-spoken-word', [MarketingController::class, 'forSpokenWord'])->name('marketing.for_spoken_word');
            Route::get('/for-bars', [MarketingController::class, 'forBars'])->name('marketing.for_bars');
            Route::get('/for-nightclubs', [MarketingController::class, 'forNightclubs'])->name('marketing.for_nightclubs');
            Route::get('/for-music-venues', [MarketingController::class, 'forMusicVenues'])->name('marketing.for_music_venues');
            Route::get('/for-theaters', [MarketingController::class, 'forTheaters'])->name('marketing.for_theaters');
            Route::get('/for-dance-groups', [MarketingController::class, 'forDanceGroups'])->name('marketing.for_dance_groups');
            Route::get('/for-theater-performers', [MarketingController::class, 'forTheaterPerformers'])->name('marketing.for_theater_performers');
            Route::get('/for-food-trucks-and-vendors', [MarketingController::class, 'forFoodTrucksAndVendors'])->name('marketing.for_food_trucks_and_vendors');
            Route::get('/for-comedy-clubs', [MarketingController::class, 'forComedyClubs'])->name('marketing.for_comedy_clubs');
            Route::get('/for-restaurants', [MarketingController::class, 'forRestaurants'])->name('marketing.for_restaurants');
            Route::get('/for-breweries-and-wineries', [MarketingController::class, 'forBreweriesAndWineries'])->name('marketing.for_breweries_and_wineries');
            Route::get('/for-art-galleries', [MarketingController::class, 'forArtGalleries'])->name('marketing.for_art_galleries');
            Route::get('/for-community-centers', [MarketingController::class, 'forCommunityCenters'])->name('marketing.for_community_centers');
            Route::get('/for-fitness-and-yoga', [MarketingController::class, 'forFitnessAndYoga'])->name('marketing.for_fitness_and_yoga');
            Route::get('/for-workshop-instructors', [MarketingController::class, 'forWorkshopInstructors'])->name('marketing.for_workshop_instructors');
            Route::get('/for-visual-artists', [MarketingController::class, 'forVisualArtists'])->name('marketing.for_visual_artists');
            Route::get('/for-farmers-markets', [MarketingController::class, 'forFarmersMarkets'])->name('marketing.for_farmers_markets');
            Route::get('/for-hotels-and-resorts', [MarketingController::class, 'forHotelsAndResorts'])->name('marketing.for_hotels_and_resorts');
            Route::get('/for-libraries', [MarketingController::class, 'forLibraries'])->name('marketing.for_libraries');
            Route::get('/for-webinars', [MarketingController::class, 'forWebinars'])->name('marketing.for_webinars');
            Route::get('/for-live-concerts', [MarketingController::class, 'forLiveConcerts'])->name('marketing.for_live_concerts');
            Route::get('/for-online-classes', [MarketingController::class, 'forOnlineClasses'])->name('marketing.for_online_classes');
            Route::get('/for-virtual-conferences', [MarketingController::class, 'forVirtualConferences'])->name('marketing.for_virtual_conferences');
            Route::get('/for-live-qa-sessions', [MarketingController::class, 'forLiveQaSessions'])->name('marketing.for_live_qa_sessions');
            Route::get('/for-watch-parties', [MarketingController::class, 'forWatchParties'])->name('marketing.for_watch_parties');
            Route::get('/for-ai-agents', [MarketingController::class, 'forAiAgents'])->name('marketing.for_ai_agents');
            Route::get('/use-cases', [MarketingController::class, 'useCases'])->name('marketing.use_cases');
            Route::get('/compare', [MarketingController::class, 'compare'])->name('marketing.compare');
            Route::get('/eventbrite-alternative', [MarketingController::class, 'compareEventbrite'])->name('marketing.compare_eventbrite');
            Route::get('/luma-alternative', [MarketingController::class, 'compareLuma'])->name('marketing.compare_luma');
            Route::get('/ticket-tailor-alternative', [MarketingController::class, 'compareTicketTailor'])->name('marketing.compare_ticket_tailor');
            Route::get('/google-calendar-alternative', [MarketingController::class, 'compareGoogleCalendar'])->name('marketing.compare_google_calendar');
            Route::get('/contact', [MarketingController::class, 'contact'])->name('marketing.contact');
            Route::get('/privacy', [MarketingController::class, 'privacy'])->name('marketing.privacy');
            Route::get('/terms-of-service', [MarketingController::class, 'terms'])->name('marketing.terms');
            Route::get('/self-hosting-terms-of-service', [MarketingController::class, 'selfHostingTerms'])->name('marketing.self_hosting_terms');
            Route::get('/selfhost', [MarketingController::class, 'selfHost'])->name('marketing.selfhost');
            Route::get('/saas', [MarketingController::class, 'saas'])->name('marketing.saas');
            Route::get('/docs', [MarketingController::class, 'docsIndex'])->name('marketing.docs');
            // User Guide (at root level)
            Route::get('/docs/getting-started', [MarketingController::class, 'docsGettingStarted'])->name('marketing.docs.getting_started');
            Route::get('/docs/creating-schedules', [MarketingController::class, 'docsCreatingSchedules'])->name('marketing.docs.creating_schedules');
            Route::get('/docs/schedule-basics', [MarketingController::class, 'docsScheduleBasics'])->name('marketing.docs.schedule_basics');
            Route::get('/docs/schedule-styling', [MarketingController::class, 'docsScheduleStyling'])->name('marketing.docs.schedule_styling');
            Route::get('/docs/creating-events', [MarketingController::class, 'docsCreatingEvents'])->name('marketing.docs.creating_events');
            Route::get('/docs/sharing', [MarketingController::class, 'docsSharing'])->name('marketing.docs.sharing');
            Route::get('/docs/tickets', [MarketingController::class, 'docsTickets'])->name('marketing.docs.tickets');
            Route::get('/docs/event-graphics', [MarketingController::class, 'docsEventGraphics'])->name('marketing.docs.event_graphics');
            Route::get('/docs/newsletters', [MarketingController::class, 'docsNewsletters'])->name('marketing.docs.newsletters');
            Route::get('/docs/analytics', [MarketingController::class, 'docsAnalytics'])->name('marketing.docs.analytics');
            Route::get('/docs/account-settings', [MarketingController::class, 'docsAccountSettings'])->name('marketing.docs.account_settings');
            Route::get('/docs/availability', [MarketingController::class, 'docsAvailability'])->name('marketing.docs.availability');
            // Selfhost section
            Route::get('/docs/selfhost', [MarketingController::class, 'docsSelfhostIndex'])->name('marketing.docs.selfhost');
            Route::get('/docs/selfhost/installation', [MarketingController::class, 'docsSelfhostInstallation'])->name('marketing.docs.selfhost.installation');
            Route::get('/docs/selfhost/saas', [MarketingController::class, 'docsSelfhostSaas'])->name('marketing.docs.selfhost.saas');
            Route::get('/docs/selfhost/stripe', [MarketingController::class, 'docsSelfhostStripe'])->name('marketing.docs.selfhost.stripe');
            Route::get('/docs/selfhost/google-calendar', [MarketingController::class, 'docsSelfhostGoogleCalendar'])->name('marketing.docs.selfhost.google_calendar');
            // Developer section
            Route::get('/docs/developer/api', [MarketingController::class, 'docsDeveloperApi'])->name('marketing.docs.developer.api');
            // Redirects from old URLs to new URLs
            Route::get('/docs/installation', fn () => redirect()->route('marketing.docs.selfhost.installation', [], 301));
            Route::get('/docs/saas', fn () => redirect()->route('marketing.docs.selfhost.saas', [], 301));
            Route::get('/docs/stripe', fn () => redirect()->route('marketing.docs.selfhost.stripe', [], 301));
            Route::get('/docs/google-calendar', fn () => redirect()->route('marketing.docs.selfhost.google_calendar', [], 301));
            Route::get('/docs/api', fn () => redirect()->route('marketing.docs.developer.api', [], 301));
            Route::get('/docs/developer', fn () => redirect()->route('marketing.docs.developer.api', [], 301));
        });

        // Redirect www.eventschedule.com marketing pages to non-www
        Route::domain('www.eventschedule.com')->group(function () {
            Route::get('/', fn () => redirect('https://eventschedule.com/', 301));
            Route::get('/features', fn () => redirect('https://eventschedule.com/features', 301));
            Route::get('/pricing', fn () => redirect('https://eventschedule.com/pricing', 301));
            Route::get('/about', fn () => redirect('https://eventschedule.com/about', 301));
            Route::get('/demos', fn () => redirect('https://eventschedule.com/examples', 301));
            Route::get('/examples', fn () => redirect('https://eventschedule.com/examples', 301));
            Route::get('/faq', fn () => redirect('https://eventschedule.com/faq', 301));
            Route::get('/ticketing', fn () => redirect('https://eventschedule.com/features/ticketing', 301));
            Route::get('/ai', fn () => redirect('https://eventschedule.com/features/ai', 301));
            Route::get('/calendar-sync', fn () => redirect('https://eventschedule.com/features/calendar-sync', 301));
            Route::get('/google-calendar', fn () => redirect('https://eventschedule.com/google-calendar', 301));
            Route::get('/caldav', fn () => redirect('https://eventschedule.com/caldav', 301));
            Route::get('/stripe', fn () => redirect('https://eventschedule.com/stripe', 301));
            Route::get('/invoiceninja', fn () => redirect('https://eventschedule.com/invoiceninja', 301));
            Route::get('/analytics', fn () => redirect('https://eventschedule.com/features/analytics', 301));
            Route::get('/integrations', fn () => redirect('https://eventschedule.com/features/integrations', 301));
            Route::get('/custom-fields', fn () => redirect('https://eventschedule.com/features/custom-fields', 301));
            Route::get('/team-scheduling', fn () => redirect('https://eventschedule.com/features/team-scheduling', 301));
            Route::get('/sub-schedules', fn () => redirect('https://eventschedule.com/features/sub-schedules', 301));
            Route::get('/online-events', fn () => redirect('https://eventschedule.com/features/online-events', 301));
            Route::get('/open-source', fn () => redirect('https://eventschedule.com/open-source', 301));
            Route::get('/newsletters', fn () => redirect('https://eventschedule.com/features/newsletters', 301));
            Route::get('/recurring-events', fn () => redirect('https://eventschedule.com/features/recurring-events', 301));
            Route::get('/embed-calendar', fn () => redirect('https://eventschedule.com/features/embed-calendar', 301));
            Route::get('/features/ticketing', fn () => redirect('https://eventschedule.com/features/ticketing', 301));
            Route::get('/features/ai', fn () => redirect('https://eventschedule.com/features/ai', 301));
            Route::get('/features/calendar-sync', fn () => redirect('https://eventschedule.com/features/calendar-sync', 301));
            Route::get('/features/analytics', fn () => redirect('https://eventschedule.com/features/analytics', 301));
            Route::get('/features/integrations', fn () => redirect('https://eventschedule.com/features/integrations', 301));
            Route::get('/features/custom-fields', fn () => redirect('https://eventschedule.com/features/custom-fields', 301));
            Route::get('/features/team-scheduling', fn () => redirect('https://eventschedule.com/features/team-scheduling', 301));
            Route::get('/features/sub-schedules', fn () => redirect('https://eventschedule.com/features/sub-schedules', 301));
            Route::get('/features/online-events', fn () => redirect('https://eventschedule.com/features/online-events', 301));
            Route::get('/features/newsletters', fn () => redirect('https://eventschedule.com/features/newsletters', 301));
            Route::get('/features/recurring-events', fn () => redirect('https://eventschedule.com/features/recurring-events', 301));
            Route::get('/features/embed-calendar', fn () => redirect('https://eventschedule.com/features/embed-calendar', 301));
            Route::get('/features/fan-videos', fn () => redirect('https://eventschedule.com/features/fan-videos', 301));
            Route::get('/for-talent', fn () => redirect('https://eventschedule.com/for-talent', 301));
            Route::get('/for-venues', fn () => redirect('https://eventschedule.com/for-venues', 301));
            Route::get('/for-curators', fn () => redirect('https://eventschedule.com/for-curators', 301));
            Route::get('/for-musicians', fn () => redirect('https://eventschedule.com/for-musicians', 301));
            Route::get('/for-djs', fn () => redirect('https://eventschedule.com/for-djs', 301));
            Route::get('/for-comedians', fn () => redirect('https://eventschedule.com/for-comedians', 301));
            Route::get('/for-circus-acrobatics', fn () => redirect('https://eventschedule.com/for-circus-acrobatics', 301));
            Route::get('/for-magicians', fn () => redirect('https://eventschedule.com/for-magicians', 301));
            Route::get('/for-spoken-word', fn () => redirect('https://eventschedule.com/for-spoken-word', 301));
            Route::get('/for-bars', fn () => redirect('https://eventschedule.com/for-bars', 301));
            Route::get('/for-nightclubs', fn () => redirect('https://eventschedule.com/for-nightclubs', 301));
            Route::get('/for-music-venues', fn () => redirect('https://eventschedule.com/for-music-venues', 301));
            Route::get('/for-theaters', fn () => redirect('https://eventschedule.com/for-theaters', 301));
            Route::get('/for-dance-groups', fn () => redirect('https://eventschedule.com/for-dance-groups', 301));
            Route::get('/for-theater-performers', fn () => redirect('https://eventschedule.com/for-theater-performers', 301));
            Route::get('/for-food-trucks-and-vendors', fn () => redirect('https://eventschedule.com/for-food-trucks-and-vendors', 301));
            Route::get('/for-comedy-clubs', fn () => redirect('https://eventschedule.com/for-comedy-clubs', 301));
            Route::get('/for-restaurants', fn () => redirect('https://eventschedule.com/for-restaurants', 301));
            Route::get('/for-breweries-and-wineries', fn () => redirect('https://eventschedule.com/for-breweries-and-wineries', 301));
            Route::get('/for-art-galleries', fn () => redirect('https://eventschedule.com/for-art-galleries', 301));
            Route::get('/for-community-centers', fn () => redirect('https://eventschedule.com/for-community-centers', 301));
            Route::get('/for-fitness-and-yoga', fn () => redirect('https://eventschedule.com/for-fitness-and-yoga', 301));
            Route::get('/for-workshop-instructors', fn () => redirect('https://eventschedule.com/for-workshop-instructors', 301));
            Route::get('/for-visual-artists', fn () => redirect('https://eventschedule.com/for-visual-artists', 301));
            Route::get('/for-farmers-markets', fn () => redirect('https://eventschedule.com/for-farmers-markets', 301));
            Route::get('/for-hotels-and-resorts', fn () => redirect('https://eventschedule.com/for-hotels-and-resorts', 301));
            Route::get('/for-libraries', fn () => redirect('https://eventschedule.com/for-libraries', 301));
            Route::get('/for-webinars', fn () => redirect('https://eventschedule.com/for-webinars', 301));
            Route::get('/for-live-concerts', fn () => redirect('https://eventschedule.com/for-live-concerts', 301));
            Route::get('/for-online-classes', fn () => redirect('https://eventschedule.com/for-online-classes', 301));
            Route::get('/for-virtual-conferences', fn () => redirect('https://eventschedule.com/for-virtual-conferences', 301));
            Route::get('/for-live-qa-sessions', fn () => redirect('https://eventschedule.com/for-live-qa-sessions', 301));
            Route::get('/for-watch-parties', fn () => redirect('https://eventschedule.com/for-watch-parties', 301));
            Route::get('/for-ai-agents', fn () => redirect('https://eventschedule.com/for-ai-agents', 301));
            Route::get('/use-cases', fn () => redirect('https://eventschedule.com/use-cases', 301));
            Route::get('/compare', fn () => redirect('https://eventschedule.com/compare', 301));
            Route::get('/contact', fn () => redirect('https://eventschedule.com/contact', 301));
            Route::get('/privacy', fn () => redirect('https://eventschedule.com/privacy', 301));
            Route::get('/terms-of-service', fn () => redirect('https://eventschedule.com/terms-of-service', 301));
            Route::get('/self-hosting-terms-of-service', fn () => redirect('https://eventschedule.com/self-hosting-terms-of-service', 301));
            Route::get('/selfhost', fn () => redirect('https://eventschedule.com/selfhost', 301));
            Route::get('/saas', fn () => redirect('https://eventschedule.com/saas', 301));
            Route::get('/docs', fn () => redirect('https://eventschedule.com/docs', 301));
            // User Guide
            Route::get('/docs/getting-started', fn () => redirect('https://eventschedule.com/docs/getting-started', 301));
            Route::get('/docs/creating-schedules', fn () => redirect('https://eventschedule.com/docs/creating-schedules', 301));
            Route::get('/docs/schedule-basics', fn () => redirect('https://eventschedule.com/docs/schedule-basics', 301));
            Route::get('/docs/schedule-styling', fn () => redirect('https://eventschedule.com/docs/schedule-styling', 301));
            Route::get('/docs/creating-events', fn () => redirect('https://eventschedule.com/docs/creating-events', 301));
            Route::get('/docs/sharing', fn () => redirect('https://eventschedule.com/docs/sharing', 301));
            Route::get('/docs/tickets', fn () => redirect('https://eventschedule.com/docs/tickets', 301));
            Route::get('/docs/event-graphics', fn () => redirect('https://eventschedule.com/docs/event-graphics', 301));
            Route::get('/docs/newsletters', fn () => redirect('https://eventschedule.com/docs/newsletters', 301));
            Route::get('/docs/analytics', fn () => redirect('https://eventschedule.com/docs/analytics', 301));
            Route::get('/docs/account-settings', fn () => redirect('https://eventschedule.com/docs/account-settings', 301));
            Route::get('/docs/availability', fn () => redirect('https://eventschedule.com/docs/availability', 301));
            // Selfhost section
            Route::get('/docs/selfhost', fn () => redirect('https://eventschedule.com/docs/selfhost', 301));
            Route::get('/docs/selfhost/installation', fn () => redirect('https://eventschedule.com/docs/selfhost/installation', 301));
            Route::get('/docs/selfhost/saas', fn () => redirect('https://eventschedule.com/docs/selfhost/saas', 301));
            Route::get('/docs/selfhost/stripe', fn () => redirect('https://eventschedule.com/docs/selfhost/stripe', 301));
            Route::get('/docs/selfhost/google-calendar', fn () => redirect('https://eventschedule.com/docs/selfhost/google-calendar', 301));
            // Developer section
            Route::get('/docs/developer/api', fn () => redirect('https://eventschedule.com/docs/developer/api', 301));
            Route::get('/docs/developer', fn () => redirect('https://eventschedule.com/docs/developer/api', 301));
            // Old URL redirects
            Route::get('/docs/saas', fn () => redirect('https://eventschedule.com/docs/selfhost/saas', 301));
            Route::get('/docs/stripe', fn () => redirect('https://eventschedule.com/docs/selfhost/stripe', 301));
            Route::get('/docs/google-calendar', fn () => redirect('https://eventschedule.com/docs/selfhost/google-calendar', 301));
            Route::get('/docs/installation', fn () => redirect('https://eventschedule.com/docs/selfhost/installation', 301));
            Route::get('/docs/api', fn () => redirect('https://eventschedule.com/docs/developer/api', 301));
        });
    }
} else {
    // Non-nexus: redirect marketing URLs to home (/events) or login
    Route::get('/', fn () => redirect()->route('login'));
    Route::get('/features', fn () => redirect()->route('home'));
    Route::get('/pricing', fn () => redirect()->route('home'));
    Route::get('/about', fn () => redirect()->route('home'));
    Route::get('/demos', fn () => redirect()->route('home'));
    Route::get('/examples', fn () => redirect()->route('home'));
    Route::get('/faq', fn () => redirect()->route('home'));
    Route::get('/features/ticketing', fn () => redirect()->route('home'));
    Route::get('/features/ai', fn () => redirect()->route('home'));
    Route::get('/features/calendar-sync', fn () => redirect()->route('home'));
    Route::get('/google-calendar', fn () => redirect()->route('home'));
    Route::get('/caldav', fn () => redirect()->route('home'));
    Route::get('/stripe', fn () => redirect()->route('home'));
    Route::get('/invoiceninja', fn () => redirect()->route('home'));
    Route::get('/features/integrations', fn () => redirect()->route('home'));
    Route::get('/features/custom-fields', fn () => redirect()->route('home'));
    Route::get('/features/team-scheduling', fn () => redirect()->route('home'));
    Route::get('/features/sub-schedules', fn () => redirect()->route('home'));
    Route::get('/features/online-events', fn () => redirect()->route('home'));
    Route::get('/features/newsletters', fn () => redirect()->route('home'));
    Route::get('/features/recurring-events', fn () => redirect()->route('home'));
    Route::get('/features/embed-calendar', fn () => redirect()->route('home'));
    Route::get('/features/fan-videos', fn () => redirect()->route('home'));
    Route::get('/features/analytics', fn () => redirect()->route('home'));
    // Old URLs still redirect to home
    Route::get('/ticketing', fn () => redirect()->route('home'));
    Route::get('/ai', fn () => redirect()->route('home'));
    Route::get('/calendar-sync', fn () => redirect()->route('home'));
    Route::get('/integrations', fn () => redirect()->route('home'));
    Route::get('/custom-fields', fn () => redirect()->route('home'));
    Route::get('/team-scheduling', fn () => redirect()->route('home'));
    Route::get('/sub-schedules', fn () => redirect()->route('home'));
    Route::get('/online-events', fn () => redirect()->route('home'));
    Route::get('/recurring-events', fn () => redirect()->route('home'));
    Route::get('/embed-calendar', fn () => redirect()->route('home'));
    Route::get('/for-talent', fn () => redirect()->route('home'));
    Route::get('/for-venues', fn () => redirect()->route('home'));
    Route::get('/for-curators', fn () => redirect()->route('home'));
    Route::get('/for-musicians', fn () => redirect()->route('home'));
    Route::get('/for-djs', fn () => redirect()->route('home'));
    Route::get('/for-comedians', fn () => redirect()->route('home'));
    Route::get('/for-circus-acrobatics', fn () => redirect()->route('home'));
    Route::get('/for-magicians', fn () => redirect()->route('home'));
    Route::get('/for-spoken-word', fn () => redirect()->route('home'));
    Route::get('/for-bars', fn () => redirect()->route('home'));
    Route::get('/for-nightclubs', fn () => redirect()->route('home'));
    Route::get('/for-music-venues', fn () => redirect()->route('home'));
    Route::get('/for-theaters', fn () => redirect()->route('home'));
    Route::get('/for-dance-groups', fn () => redirect()->route('home'));
    Route::get('/for-theater-performers', fn () => redirect()->route('home'));
    Route::get('/for-food-trucks-and-vendors', fn () => redirect()->route('home'));
    Route::get('/for-comedy-clubs', fn () => redirect()->route('home'));
    Route::get('/for-restaurants', fn () => redirect()->route('home'));
    Route::get('/for-breweries-and-wineries', fn () => redirect()->route('home'));
    Route::get('/for-art-galleries', fn () => redirect()->route('home'));
    Route::get('/for-community-centers', fn () => redirect()->route('home'));
    Route::get('/for-fitness-and-yoga', fn () => redirect()->route('home'));
    Route::get('/for-workshop-instructors', fn () => redirect()->route('home'));
    Route::get('/for-visual-artists', fn () => redirect()->route('home'));
    Route::get('/for-farmers-markets', fn () => redirect()->route('home'));
    Route::get('/for-hotels-and-resorts', fn () => redirect()->route('home'));
    Route::get('/for-libraries', fn () => redirect()->route('home'));
    Route::get('/for-webinars', fn () => redirect()->route('home'));
    Route::get('/for-live-concerts', fn () => redirect()->route('home'));
    Route::get('/for-online-classes', fn () => redirect()->route('home'));
    Route::get('/for-virtual-conferences', fn () => redirect()->route('home'));
    Route::get('/for-live-qa-sessions', fn () => redirect()->route('home'));
    Route::get('/for-watch-parties', fn () => redirect()->route('home'));
    Route::get('/for-ai-agents', fn () => redirect()->route('home'));
    Route::get('/use-cases', fn () => redirect()->route('home'));
    Route::get('/compare', fn () => redirect()->route('home'));
    Route::get('/contact', fn () => redirect()->route('home'));
    Route::get('/selfhost', fn () => redirect()->route('home'));
    Route::get('/saas', fn () => redirect()->route('home'));
    Route::get('/docs', fn () => redirect()->route('home'));
    // User Guide
    Route::get('/docs/getting-started', fn () => redirect()->route('home'));
    Route::get('/docs/creating-schedules', fn () => redirect()->route('home'));
    Route::get('/docs/schedule-basics', fn () => redirect()->route('home'));
    Route::get('/docs/schedule-styling', fn () => redirect()->route('home'));
    Route::get('/docs/creating-events', fn () => redirect()->route('home'));
    Route::get('/docs/sharing', fn () => redirect()->route('home'));
    Route::get('/docs/tickets', fn () => redirect()->route('home'));
    Route::get('/docs/event-graphics', fn () => redirect()->route('home'));
    Route::get('/docs/newsletters', fn () => redirect()->route('home'));
    Route::get('/docs/analytics', fn () => redirect()->route('home'));
    Route::get('/docs/account-settings', fn () => redirect()->route('home'));
    Route::get('/docs/availability', fn () => redirect()->route('home'));
    // Selfhost section
    Route::get('/docs/selfhost', fn () => redirect()->route('home'));
    Route::get('/docs/selfhost/installation', fn () => redirect()->route('home'));
    Route::get('/docs/selfhost/saas', fn () => redirect()->route('home'));
    Route::get('/docs/selfhost/stripe', fn () => redirect()->route('home'));
    Route::get('/docs/selfhost/google-calendar', fn () => redirect()->route('home'));
    // Developer section
    Route::get('/docs/developer', fn () => redirect()->route('home'));
    Route::get('/docs/developer/api', fn () => redirect()->route('home'));
    // Old URLs (still redirect to home)
    Route::get('/docs/saas', fn () => redirect()->route('home'));
    Route::get('/docs/stripe', fn () => redirect()->route('home'));
    Route::get('/docs/google-calendar', fn () => redirect()->route('home'));
    Route::get('/docs/installation', fn () => redirect()->route('home'));
    Route::get('/docs/api', fn () => redirect()->route('home'));
}

// Blog routes: use /blog path for local dev, testing, and selfhosted users
// Hosted mode uses blog.eventschedule.com subdomain (defined above)
if (config('app.is_testing') || config('app.env') == 'local' || ! config('app.hosted')) {
    Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
    Route::get('/blog/feed', [BlogController::class, 'feed'])->name('blog.feed');
    Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
}

// Signed URL route for deleting blog posts via email link (no auth required, signed URL provides security)
Route::get('/blog/delete-signed/{blogPost}', [BlogController::class, 'destroySigned'])
    ->name('blog.destroy.signed')
    ->middleware('signed');

if (config('app.hosted') && ! config('app.is_testing')) {
    Route::domain('{subdomain}.eventschedule.com')->where(['subdomain' => '^(?!www|app).*'])->group(function () {
        Route::get('/', [RoleController::class, 'viewGuest'])->name('role.view_guest');
    });
} else {
    Route::get('/{subdomain}/api/past-events', [RoleController::class, 'listPastEvents'])->name('role.list_past_events');
    Route::get('/{subdomain}/api/calendar-events', [RoleController::class, 'calendarEvents'])->name('role.calendar_events');
    Route::get('/{subdomain}/request', [RoleController::class, 'request'])->name('role.request');
    Route::get('/{subdomain}/follow', [RoleController::class, 'follow'])->name('role.follow');
    Route::get('/{subdomain}/guest-add', [EventController::class, 'showGuestImport'])->name('event.guest_import');
    Route::post('/{subdomain}/guest-add', [EventController::class, 'guestImport'])->name('event.guest_import.store');
    Route::post('/{subdomain}/guest-parse', [EventController::class, 'guestParse'])->name('event.guest_parse');
    Route::post('/{subdomain}/guest-upload-image', [EventController::class, 'guestUploadImage'])->name('event.guest_upload_image');
    Route::get('/{subdomain}/guest-search-youtube', [RoleController::class, 'guestSearchYouTube'])->name('role.guest_search_youtube');
    Route::get('/{subdomain}/curate-event/{hash}', [EventController::class, 'curate'])->name('event.curate');
    Route::post('/{subdomain}/submit-video/{event_hash}', [EventController::class, 'submitVideo'])->name('event.submit_video')->middleware('throttle:10,60');
    Route::post('/{subdomain}/submit-comment/{event_hash}', [EventController::class, 'submitComment'])->name('event.submit_comment')->middleware('throttle:20,60');
    Route::post('/{subdomain}/checkout', [TicketController::class, 'checkout'])->name('event.checkout');
    Route::get('/{subdomain}/checkout/success/{sale_id}', [TicketController::class, 'success'])->name('checkout.success');
    Route::get('/{subdomain}/checkout/cancel/{sale_id}', [TicketController::class, 'cancel'])->name('checkout.cancel');
    Route::get('/{subdomain}/payment/success/{sale_id}', [TicketController::class, 'paymentUrlSuccess'])->name('payment_url.success');
    Route::get('/{subdomain}/payment/cancel/{sale_id}', [TicketController::class, 'paymentUrlCancel'])->name('payment_url.cancel');
    Route::get('/{subdomain}', [RoleController::class, 'viewGuest'])->name('role.view_guest');

    // Event with ID and date (recurring)
    Route::get('/{subdomain}/{slug}/{id}/{date}', [RoleController::class, 'viewGuest'])
        ->name('event.view_guest_full')
        ->where(['date' => '\d{4}-\d{2}-\d{2}', 'id' => '[A-Za-z0-9+/=]+']);

    // Event with ID only
    Route::get('/{subdomain}/{slug}/{id}', [RoleController::class, 'viewGuest'])
        ->name('event.view_guest_with_id')
        ->where(['id' => '[A-Za-z0-9+/=]+']);

    // Existing catch-all remains last
    Route::get('/{subdomain}/{slug}', [RoleController::class, 'viewGuest'])->name('event.view_guest');
}

Route::get('/{slug?}', [HomeController::class, 'landing'])->name('landing');
