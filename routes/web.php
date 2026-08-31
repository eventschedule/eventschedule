<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminFederationController;
use App\Http\Controllers\AdminLegalController;
use App\Http\Controllers\AdminNewsletterController;
use App\Http\Controllers\AdminTranslationController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Api\ApiSettingsController;
use App\Http\Controllers\AppController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AppointmentTypeController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\BoostController;
use App\Http\Controllers\BoxOfficeController;
use App\Http\Controllers\CalDAVController;
use App\Http\Controllers\CarpoolController;
use App\Http\Controllers\CheckInController;
use App\Http\Controllers\EventbriteController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\EventTemplateController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\GiftCardController;
use App\Http\Controllers\GoogleCalendarController;
use App\Http\Controllers\GoogleCalendarWebhookController;
use App\Http\Controllers\GraphicController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\InvoiceNinjaController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\MetaAdsWebhookController;
use App\Http\Controllers\MicrosoftCalendarController;
use App\Http\Controllers\MicrosoftCalendarWebhookController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\NewsletterTrackingController;
use App\Http\Controllers\PaymentGatewayController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromoCodeController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\PushController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoleSubscriberController;
use App\Http\Controllers\SeatingPickerController;
use App\Http\Controllers\SeatingPlanController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\SubscriptionWebhookController;
use App\Http\Controllers\SupportChatController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\WaitlistController;
use App\Http\Controllers\WebhookSettingsController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/robots.txt', [AppController::class, 'robots']);

if (config('app.hosted') && ! config('app.is_testing')) {
    if (config('app.env') != 'local') {
        Route::domain('blog.'._base_domain())->group(function () {
            Route::get('/', [BlogController::class, 'index'])->name('blog.index');
            Route::get('/feed', [BlogController::class, 'feed'])->name('blog.feed');
            Route::get('/{slug}', [BlogController::class, 'show'])->name('blog.show');
        });
    }

    Route::domain('{subdomain}.'._base_domain())->where(['subdomain' => '^(?!www|app).*'])->group(function () {
        // Must be registered here, ahead of the domain-less marketing "/" routes below,
        // otherwise those match first on every host and send schedule home pages to login.
        Route::get('/', [RoleController::class, 'viewGuest'])->name('role.view_guest');
        // This schedule's own sitemap. It exists for custom domains, whose URLs the global sitemap
        // is not allowed to carry - see SitemapController::schedule(). withoutMiddleware('web') for
        // the same reason the global sitemap routes use it: a Set-Cookie stops Cloudflare caching.
        Route::get('/sitemap.xml', [SitemapController::class, 'schedule'])
            ->name('sitemap.schedule')
            ->withoutMiddleware('web');
        // This schedule's own web app manifest. It has to be registered per tenant host, ahead of
        // the domain-less platform manifest below, or every schedule's site is installable as an
        // app called "Event Schedule" showing our logo as its splash - see AppController::manifest.
        Route::get('/manifest.webmanifest', [AppController::class, 'manifest'])
            ->name('role.manifest')
            ->withoutMiddleware('web');
        Route::get('/api/past-events', [RoleController::class, 'listPastEvents'])->name('role.list_past_events');
        Route::get('/api/calendar-events', [RoleController::class, 'calendarEvents'])->name('role.calendar_events');
        Route::get('/request', [RoleController::class, 'request'])->name('role.request');
        Route::get('/follow', [RoleController::class, 'follow'])->name('role.follow');
        // Account-less audience capture. The path segment is NOT "subscribe": that URI is
        // already taken by the authenticated plan checkout (SubscriptionController@store, below),
        // whose group is registered first and would shadow this on selfhost.
        //
        // The throttle carries a prefix because an unprefixed one shares its bucket with every
        // other throttled route in this group - a visitor who just used the cart would arrive
        // pre-blocked. The real limit is the per-email one in the controller.
        Route::post('/audience/join', [RoleSubscriberController::class, 'store'])
            ->name('role.audience.join')->middleware('throttle:5,1,audience_join');
        // Promotion click-through. Counted and billed here, then redirected, so the
        // advertiser only pays for clicks that actually left this page.
        //
        // {hash} is constrained to the encodeId charset, matching the event routes below. Two
        // segments means this pattern sits in front of /{slug}/{id}, so an event slugged
        // "promo" would otherwise hand every one of its URLs to this controller; the
        // constraint at least keeps non-hash paths (/promo/photos and friends) on the event
        // routes where they belong.
        Route::get('/promo/{hash}', [PromotionController::class, 'click'])
            ->where(['hash' => '[A-Za-z0-9+=]+'])
            ->name('promo.click')->middleware('throttle:60,1');
        Route::get('/guest-add', [EventController::class, 'showGuestImport'])->name('event.guest_import');
        Route::get('/guest-submit', [EventController::class, 'showGuestSubmit'])->name('event.guest_submit');
        Route::post('/guest-add', [EventController::class, 'guestImport'])->name('event.guest_import.store')->middleware('throttle:10,1');
        Route::post('/guest-add/check-email', [EventController::class, 'checkEmail'])->name('event.check_email')->middleware('throttle:10,1');
        Route::post('/guest-add/send-code', [RegisteredUserController::class, 'sendVerificationCode'])->name('event.guest_send_code')->middleware('throttle:5,1');
        Route::get('/booking-request', [EventController::class, 'showBookingRequest'])->name('event.booking_request');
        Route::post('/booking-request', [EventController::class, 'bookingRequest'])->name('event.booking_request.store')->middleware('throttle:10,1');
        // Appointments (Calendly-style booking). Registered before the /{slug} catch-alls below.
        Route::get('/book', [AppointmentController::class, 'showBook'])->name('appointments.book');
        Route::get('/book/{typeSlug}', [AppointmentController::class, 'showBookType'])->name('appointments.book_type');
        Route::get('/book/{typeSlug}/slots', [AppointmentController::class, 'slots'])->name('appointments.slots')->middleware('throttle:60,1');
        Route::post('/book/{typeSlug}', [AppointmentController::class, 'book'])->name('appointments.book.store')->middleware('throttle:10,1');
        Route::post('/guest-parse', [EventController::class, 'guestParse'])->name('event.guest_parse')->middleware('throttle:10,1');
        Route::post('/guest-upload-image', [EventController::class, 'guestUploadImage'])->name('event.guest_upload_image')->middleware('throttle:20,1');
        Route::get('/guest-search-youtube', [RoleController::class, 'guestSearchYouTube'])->name('role.guest_search_youtube');
        Route::get('/curate-event/{hash}', [EventController::class, 'curate'])->name('event.curate');
        Route::post('/submit-video/{event_hash}', [EventController::class, 'submitVideo'])->name('event.submit_video')->middleware('throttle:10,60');
        Route::post('/submit-comment/{event_hash}', [EventController::class, 'submitComment'])->name('event.submit_comment')->middleware('throttle:20,60');
        Route::post('/submit-photo/{event_hash}', [EventController::class, 'submitPhoto'])->name('event.submit_photo')->middleware('throttle:10,60');
        Route::post('/vote-poll/{event_hash}/{poll_hash}', [EventController::class, 'votePoll'])->name('event.vote_poll')->middleware('throttle:30,60');
        Route::post('/suggest-poll-option/{event_hash}/{poll_hash}', [EventController::class, 'suggestPollOption'])->name('event.suggest_poll_option')->middleware('throttle:20,60');
        Route::post('/event-password', [RoleController::class, 'checkEventPassword'])->name('event.check_password')->middleware('throttle:10,5');
        Route::post('/promo-code/validate', [PromoCodeController::class, 'validate'])->name('promo_code.validate')->middleware('throttle:20,1');
        // Allocated seating, guest side. The hold token lives in the SESSION, never in the
        // payload, so one visitor cannot claim another's held seats by replaying a token.
        Route::get('/seating/state', [SeatingPickerController::class, 'state'])->name('seating.state')->middleware('throttle:120,1');
        Route::post('/seating/hold', [SeatingPickerController::class, 'hold'])->name('seating.hold')->middleware('throttle:60,1');
        Route::post('/checkout', [TicketController::class, 'checkout'])->name('event.checkout')->middleware('throttle:10,1');
        Route::post('/rsvp', [TicketController::class, 'rsvp'])->name('event.rsvp')->middleware('throttle:10,1');
        Route::post('/waitlist/join', [WaitlistController::class, 'join'])->name('waitlist.join')->middleware('throttle:10,1');
        Route::get('/checkout/success/{sale_id}/{date}', [TicketController::class, 'success'])->name('checkout.success');
        Route::get('/checkout/cancel/{sale_id}/{date}', [TicketController::class, 'cancel'])->name('checkout.cancel');
        Route::get('/payment/success/{sale_id}', [TicketController::class, 'paymentUrlSuccess'])->name('payment_url.success');
        Route::get('/payment/cancel/{sale_id}', [TicketController::class, 'paymentUrlCancel'])->name('payment_url.cancel');
        Route::get('/gift-cards', [GiftCardController::class, 'showPurchase'])->name('gift_card.purchase');
        Route::post('/gift-cards', [GiftCardController::class, 'purchase'])->name('gift_card.purchase.store')->middleware('throttle:10,1');
        Route::get('/gift-cards/success/{gift_card_id}', [GiftCardController::class, 'success'])->name('gift_card.success')->middleware('throttle:100,1');
        Route::get('/gift-cards/cancel/{gift_card_id}', [GiftCardController::class, 'cancel'])->name('gift_card.cancel')->middleware('throttle:100,1');
        Route::get('/gift-cards/payment/success/{gift_card_id}', [GiftCardController::class, 'paymentUrlSuccess'])->name('gift_card.payment_url.success')->middleware('throttle:100,1');
        Route::get('/gift-cards/payment/cancel/{gift_card_id}', [GiftCardController::class, 'paymentUrlCancel'])->name('gift_card.payment_url.cancel')->middleware('throttle:100,1');
        Route::post('/gift-card/validate', [GiftCardController::class, 'validateCode'])->name('gift_card.validate')->middleware('throttle:20,1');
        // iCal download for Apple Calendar
        Route::get('/{slug}/{id}/ical', [EventController::class, 'downloadIcal'])->where(['id' => '[A-Za-z0-9+=]+']);
        Route::get('/{slug}/{id}/{date}/ical', [EventController::class, 'downloadIcal'])->where(['date' => '\d{4}-\d{2}-\d{2}', 'id' => '[A-Za-z0-9+=]+']);
        // Feed subscription endpoints
        Route::get('/feed/ical', [FeedController::class, 'icalFeed'])->name('feed.ical');
        Route::get('/feed/rss', [FeedController::class, 'rssFeed'])->name('feed.rss');
        // Carpool
        Route::get('/carpool/{event_hash}', [CarpoolController::class, 'index'])->name('carpool.index');
        Route::get('/carpool/{event_hash}/{date}', [CarpoolController::class, 'index'])->name('carpool.index_date')->where(['date' => '\d{4}-\d{2}-\d{2}']);
        Route::post('/carpool/{event_hash}/agree', [CarpoolController::class, 'agreeDisclaimer'])->name('carpool.agree_disclaimer')->middleware('throttle:5,1');
        Route::post('/carpool/{event_hash}/offer', [CarpoolController::class, 'storeOffer'])->name('carpool.store_offer')->middleware('throttle:10,60');
        Route::post('/carpool/{event_hash}/offer/{offer_hash}/cancel', [CarpoolController::class, 'cancelOffer'])->name('carpool.cancel_offer')->middleware('throttle:10,1');
        Route::post('/carpool/{event_hash}/offer/{offer_hash}/spots', [CarpoolController::class, 'updateSpots'])->name('carpool.update_spots')->middleware('throttle:10,1');
        Route::post('/carpool/{event_hash}/offer/{offer_hash}/request', [CarpoolController::class, 'requestRide'])->name('carpool.request_ride')->middleware('throttle:10,60');
        Route::post('/carpool/{event_hash}/request/{request_hash}/cancel', [CarpoolController::class, 'cancelRequest'])->name('carpool.cancel_request')->middleware('throttle:10,1');
        Route::post('/carpool/{event_hash}/offer/{offer_hash}/approve/{request_hash}', [CarpoolController::class, 'approveRequest'])->name('carpool.approve')->middleware('throttle:10,1');
        Route::post('/carpool/{event_hash}/offer/{offer_hash}/decline/{request_hash}', [CarpoolController::class, 'declineRequest'])->name('carpool.decline')->middleware('throttle:10,1');
        Route::post('/carpool/{event_hash}/offer/{offer_hash}/review', [CarpoolController::class, 'storeReview'])->name('carpool.store_review')->middleware('throttle:5,60');
        Route::post('/carpool/{event_hash}/offer/{offer_hash}/report/{user_hash}', [CarpoolController::class, 'report'])->name('carpool.report')->middleware('throttle:5,60');
        // Static map image (must be before catch-all /{slug}/{id} routes)
        Route::get('/map-image/{id}', [AppController::class, 'mapImage']);

        // Photo gallery
        Route::get('/{slug}/{id}/{date}/photos', [EventController::class, 'photoGallery'])->where(['date' => '\d{4}-\d{2}-\d{2}', 'id' => '[A-Za-z0-9+=]+']);
        Route::get('/{slug}/{id}/photos', [EventController::class, 'photoGallery'])->where(['id' => '[A-Za-z0-9+=]+']);
        Route::get('/{slug}/photos', [EventController::class, 'photoGallery']);

        // Event with ID and date (recurring)
        Route::get('/{slug}/{id}/{date}', [RoleController::class, 'viewGuest'])
            ->name('event.view_guest_full')
            ->where(['date' => '\d{4}-\d{2}-\d{2}', 'id' => '[A-Za-z0-9+=]+']);

        // Event with ID only
        Route::get('/{slug}/{id}', [RoleController::class, 'viewGuest'])
            ->name('event.view_guest_with_id')
            ->where(['id' => '[A-Za-z0-9+=]+']);

        // Existing catch-all remains last
        Route::get('/{slug}', [RoleController::class, 'viewGuest'])->name('event.view_guest');
    });
} else {
    Route::post('/test_database', [AppController::class, 'testDatabase'])->name('app.test_database');
}

// The platform's own web app manifest, for the admin portal and the apex. Deliberately below the
// tenant group above rather than beside /robots.txt at the top of this file: a domain-less route
// registered first matches on tenant subdomains too, and would hand every schedule the Event
// Schedule identity - which is the thing AppController::manifest() exists to stop.
// withoutMiddleware('web') on all three registrations for the same reason the sitemap routes use
// it, plus one this file did not have before: until now /manifest.webmanifest was a static file
// that never reached Laravel, so putting it in the web group would start counting a browser's
// manifest fetch as a marketing visit (TrackMarketingVisit) and attach a session cookie that stops
// the CDN caching it.
Route::get('/manifest.webmanifest', [AppController::class, 'manifest'])
    ->name('app.manifest')
    ->withoutMiddleware('web');

// Self-update is available on any non-nexus install (selfhosted or a self-hosted SaaS).
// On a multi-tenant self-hosted SaaS it is operator-only (the 'admin' middleware,
// EnsureUserIsAdmin, so a tenant cannot trigger a global update); on a plain selfhost any
// authenticated user may trigger it.
//
// Registered on every install and gated at runtime by can_self_update() inside
// AppController::update(), rather than wrapped in a registration-time if. Two reasons:
// a route-cached install freezes whatever the condition evaluated to when the cache was
// written, and phpunit pins IS_NEXUS=true, so a conditional registration made
// route('app.update') throw while rendering the Settings section - which is why issue #106
// was never covered by a test.
//
// Safe to register domain-less: the tenant group Route::domain('{subdomain}...') above is
// registered first, so a schedule's own /update slug still wins on its own host. Same
// ordering rule this file relies on for /manifest.webmanifest.
$updateMiddleware = ['auth', 'verified'];
if (config('app.hosted')) {
    $updateMiddleware[] = 'admin';
}
Route::match(['get', 'post'], '/update', [AppController::class, 'update'])
    ->name('app.update')
    ->middleware($updateMiddleware);

require __DIR__.'/auth.php';

// /sitemap.xml is a sitemap index; the children are streamed.
//
// The bodies are served uncompressed and the CDN negotiates gzip/br from Accept-Encoding. The
// .xml.gz paths are legacy: they used to serve the same XML under a Content-Encoding: gzip
// transport header, which is not what a .gz sitemap is (that has to be a gzip *file*), and any
// proxy that re-negotiates encoding - Cloudflare does - turned the same URL into plain XML for
// callers that did not ask for gzip. They now redirect onto the canonical .xml, which is also the
// only URL robots.txt advertises and therefore the only one authorised to cross-submit the tenant
// and custom-domain URLs the children are made of.
//
// The sitemap paths sit outside 'web' because this is pure crawler traffic that never needs a
// session: the group's StartSession and CSRF middleware attach laravel_session and XSRF-TOKEN
// cookies, and a response carrying Set-Cookie is never cached by Cloudflare, which would make the
// sitemap's Cache-Control header pointless. The group NAME is used rather than a list of classes
// so this keeps covering whatever bootstrap/app.php puts in the group. Global middleware still
// applies.
$sitemapSections = 'pages|blog-[0-9]+|schedules-[0-9]+|events-[0-9]+';

$sitemapRoutes = function () use ($sitemapSections) {
    Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
    // The {section} constraint keeps an unknown name from reaching the controller.
    Route::get('/sitemap-{section}.xml', [SitemapController::class, 'section'])
        ->where('section', $sitemapSections)
        ->name('sitemap.section');

    // A closure rather than Route::permanentRedirect(), which appends any route parameter it did
    // not consume in the target as a query string.
    Route::get('/sitemap.xml.gz', fn () => redirect('/sitemap.xml', 301))->name('sitemap.gz');
    Route::get('/sitemap-{section}.xml.gz', fn (string $section) => redirect('/sitemap-'.$section.'.xml', 301))
        ->where('section', $sitemapSections)
        ->name('sitemap.section_gz');
};

if (config('app.hosted') && ! config('app.is_testing')) {
    // Pin to the canonical host. Without this the domain-less routes also match on app. and www.
    // (the tenant group above excludes those two subdomains), and url() resolves against the
    // request host, so those hosts would serve a sitemap full of URLs that do not exist there.
    // Tenant subdomains, blog. and custom domains are already covered by the /{slug} catch-all in
    // the group above, which is the correct outcome - they should not serve the global sitemap.
    Route::domain(_base_domain())->withoutMiddleware('web')->group($sitemapRoutes);

    // www. is the one host that has to answer rather than fall through: it is a plausible thing to
    // submit to a search console, and without this the /{slug} catch-all sends it to the dashboard,
    // which reads as an unfetchable sitemap. Everything else on www. already 301s to the apex.
    Route::domain('www.'._base_domain())->withoutMiddleware('web')->group(function () use ($sitemapSections) {
        $apex = 'https://'._base_domain();

        foreach (['/sitemap.xml', '/sitemap.xml.gz'] as $path) {
            Route::get($path, fn () => redirect($apex.'/sitemap.xml', 301));
        }

        foreach (['/sitemap-{section}.xml', '/sitemap-{section}.xml.gz'] as $path) {
            Route::get($path, fn (string $section) => redirect($apex.'/sitemap-'.$section.'.xml', 301))
                ->where('section', $sitemapSections);
        }
    });
} else {
    // Selfhost is path-routed on an arbitrary host, so no domain constraint.
    Route::withoutMiddleware('web')->group($sitemapRoutes);
}
Route::get('/unsubscribe', [RoleController::class, 'showUnsubscribe'])->name('role.show_unsubscribe');
Route::post('/unsubscribe', [RoleController::class, 'unsubscribe'])->name('role.unsubscribe')->middleware('throttle:2,2');
Route::get('/user/unsubscribe', [RoleController::class, 'unsubscribeUser'])->name('user.unsubscribe')->middleware('throttle:2,2');
Route::post('/clear-pending-request', [EventController::class, 'clearPendingRequest'])->name('event.clear_pending_request');

// Newsletter tracking routes (public, no auth)
Route::get('/nl/o/{token}', [NewsletterTrackingController::class, 'trackOpen'])->name('newsletter.track_open')->middleware('throttle:60,1');
Route::get('/nl/c/{token}/{encodedUrl}', [NewsletterTrackingController::class, 'trackClick'])->name('newsletter.track_click')->where('encodedUrl', '.*')->middleware('throttle:60,1');
Route::get('/nl/u/{token}', [NewsletterTrackingController::class, 'showUnsubscribe'])->name('newsletter.show_unsubscribe');
Route::post('/nl/u/{token}', [NewsletterTrackingController::class, 'unsubscribe'])->name('newsletter.unsubscribe')->middleware('throttle:2,2');

// Audience subscription confirm / unsubscribe (public, no auth). Top level for the same reason as
// the /nl/* block above. 'sub' is reserved in Role::cleanSubdomain() so no schedule can shadow it.
// Prefixed throttles. An unprefixed one shares its per-IP bucket with every other unprefixed
// throttled route at this level, including /nl/u/* - so unsubscribing from a newsletter and then
// from an audience subscription inside two minutes would return 429, and a 429 on an unsubscribe
// link is exactly what produces a spam complaint.
Route::get('/sub/c/{token}', [RoleSubscriberController::class, 'confirm'])->name('subscriber.confirm')->middleware('throttle:10,1,audience_confirm');
Route::get('/sub/u/{token}', [RoleSubscriberController::class, 'showUnsubscribe'])->name('subscriber.show_unsubscribe');
Route::post('/sub/u/{token}', [RoleSubscriberController::class, 'unsubscribe'])->name('subscriber.unsubscribe')->middleware('throttle:6,2,audience_unsubscribe');

// Schedule ownership handover, recipient side (discussion #119).
//
// Top level rather than nested under /{subdomain}, so all three sit on the host the emailed
// link points at (app_url()) - which on hosted is where the auth session cookie applies. That
// costs the same theoretical selfhost shadowing the /nl/* routes above already accept: a
// schedule whose subdomain were literally "schedule-transfer" would lose these paths.
// Role::generateSubdomain()'s reserved list stops one ever being auto-assigned.
//
// The GET is public because the invitee may have no account yet; the token identifies the
// offer but never authorises it, so accepting is auth-gated and additionally requires being
// signed in as the address the offer was sent to.
Route::get('/schedule-transfer/{token}', [RoleController::class, 'showTransfer'])->name('role.transfer.show')->middleware('throttle:20,1');
Route::post('/schedule-transfer/{token}/accept', [RoleController::class, 'acceptTransfer'])->name('role.transfer.accept')->middleware(['auth', 'verified', 'throttle:10,1']);
Route::post('/schedule-transfer/{token}/decline', [RoleController::class, 'declineTransfer'])->name('role.transfer.decline')->middleware(['auth', 'verified', 'throttle:10,1']);

Route::post('/stripe/webhook', [StripeController::class, 'webhook'])->name('stripe.webhook');
Route::post('/stripe/subscription-webhook', [SubscriptionWebhookController::class, 'handleWebhook'])->name('stripe.subscription_webhook');
Route::post('/invoiceninja/webhook/{secret?}', [InvoiceNinjaController::class, 'webhook'])->name('invoiceninja.webhook')->middleware('throttle:60,1');
Route::post('/invoiceninja/purchase-webhook/{sale}', [InvoiceNinjaController::class, 'purchaseWebhook'])->name('invoiceninja.purchase_webhook')->middleware('throttle:60,1');
Route::post('/invoiceninja/event-purchase-webhook/{event}', [InvoiceNinjaController::class, 'eventPurchaseWebhook'])->name('invoiceninja.event_purchase_webhook')->middleware('throttle:60,1');

// Generic payment gateway endpoints (no auth required).
//
// One set of routes for every gateway, present and future: the {gateway} segment selects the driver,
// so adding a gateway needs no route, no controller and no CSRF exemption of its own. The four
// bespoke webhook endpoints above predate this and keep their URLs, because those are registered in
// external dashboards and in live Invoice Ninja installs - repointing them would silently stop real
// sales from settling.
//
// Domain-less on purpose. A notify_url is generated at checkout and handed to the provider, so it has
// to keep working no matter which host the buyer arrived on. Registration ORDER is what protects
// them: they sit ~1350 lines before the selfhost /{subdomain}/... catch-alls, so they always win.
// 'payments' is also reserved for NEW schedules on hosted (Role::cleanSubdomain gates the list on
// app.hosted), but a pre-existing selfhost schedule literally named "payments" would lose these
// four path segments to the routes below - acceptable, and worth knowing.
Route::post('/payments/{gateway}/webhook/{sale_id?}', [PaymentWebhookController::class, 'handle'])
    ->name('payments.webhook')->middleware('throttle:120,1');
// GET and POST both: most gateways bring the buyer back with a redirect, some post the form back.
Route::match(['get', 'post'], '/payments/{gateway}/return/{sale_id}', [PaymentGatewayController::class, 'handleReturn'])
    ->name('payments.return')->middleware('throttle:100,1');
Route::match(['get', 'post'], '/payments/{gateway}/cancel/{sale_id}', [PaymentGatewayController::class, 'handleCancel'])
    ->name('payments.cancel')->middleware('throttle:100,1');

// Google Calendar webhook routes (no auth required)
Route::get('/google-calendar/webhook', [GoogleCalendarWebhookController::class, 'verify'])->name('google.calendar.webhook.verify')->middleware('throttle:10,1');
Route::post('/google-calendar/webhook', [GoogleCalendarWebhookController::class, 'handle'])->name('google.calendar.webhook.handle')->middleware('throttle:60,1');

// Microsoft (Outlook) Calendar webhook routes (no auth required)
Route::get('/microsoft-calendar/webhook', [MicrosoftCalendarWebhookController::class, 'verify'])->name('microsoft.calendar.webhook.verify')->middleware('throttle:10,1');
Route::post('/microsoft-calendar/webhook', [MicrosoftCalendarWebhookController::class, 'handle'])->name('microsoft.calendar.webhook.handle')->middleware('throttle:60,1');

// Meta Ads webhook routes (no auth required)
Route::get('/webhooks/meta', [MetaAdsWebhookController::class, 'verify'])->name('meta.webhook.verify')->middleware('throttle:10,1');
Route::post('/webhooks/meta', [MetaAdsWebhookController::class, 'handle'])->name('meta.webhook.handle')->middleware('throttle:60,1');

// WhatsApp webhook route (no auth required)
Route::post('/api/whatsapp/webhook', [WhatsAppWebhookController::class, 'handle'])->name('whatsapp.webhook')->middleware('throttle:60,1');

Route::get('/release_tickets', [TicketController::class, 'release'])->name('release_tickets')->middleware('throttle:5,1');
Route::get('/translate_data', [AppController::class, 'translateData'])->name('translate_data')->middleware('throttle:5,1');

Route::get('/ticket/qr_code/{event_id}/{secret}', [TicketController::class, 'qrCode'])->name('ticket.qr_code')->middleware('throttle:100,1');
Route::get('/ticket/view/{event_id}/{secret}', [TicketController::class, 'view'])->name('ticket.view')->middleware('throttle:100,1');
// One checkout that spanned several events. Keyed on the order primary's own secret, so it grants
// no more than the ticket page that secret already opens.
Route::get('/ticket/order/{order_id}/{secret}', [TicketController::class, 'viewOrder'])->name('ticket.order')->middleware('throttle:100,1');
Route::post('/rsvp/cancel/{sale_id}', [TicketController::class, 'cancelRsvp'])->name('rsvp.cancel')->middleware('throttle:10,1');
// Appointment manage/cancel via the sale secret ({event_id} is UrlUtils-encoded, like ticket.view).
Route::get('/appointment/view/{event_id}/{secret}', [AppointmentController::class, 'manage'])->name('appointments.manage')->middleware('throttle:100,1,appt_manage');
Route::post('/appointment/cancel/{event_id}/{secret}', [AppointmentController::class, 'cancelBooking'])->name('appointments.manage_cancel')->middleware('throttle:10,1,appt_cancel');
Route::post('/appointment/pay/{event_id}/{secret}', [AppointmentController::class, 'pay'])->name('appointments.pay')->middleware('throttle:10,1,appt_pay');
Route::get('/appointment/checkout/success/{sale_id}', [AppointmentController::class, 'checkoutSuccess'])->name('appointments.checkout_success')->middleware('throttle:100,1,appt_success');
// Bookings are is_private events, so EventController::downloadIcal() 404s for the guest who made
// them. Same secret-link protection as the manage page, no new auth surface.
//
// Every route in this block carries a DISTINCT throttle prefix. For an unauthenticated request the
// limiter key is $prefix.sha1(domain|ip) with no route name in it, so unprefixed routes shared one
// counter while each still applied its own limit - the tightest one therefore governed all of them.
// With manage (100/min) and ical (60/min) feeding the same bucket as pay and cancel (10/min each), a
// guest who reloaded the manage page and used the add-to-calendar menu a few times could no longer pay
// for their own booking. Note ThrottleRequests short-circuits when app.is_testing, so no test can catch
// a regression here - check it by hand with APP_TESTING=false.
Route::get('/appointment/ical/{event_id}/{secret}', [AppointmentController::class, 'ical'])->name('appointments.ical')->middleware('throttle:60,1,appt_ical');
// Reschedule. The third throttle argument is load-bearing, not cosmetic: for guests the rate-limit
// signature is domain|ip with no route name, so without a prefix every route in this block shares one
// counter and ten month-navigations in the picker would exhaust the POST's allowance of 10.
Route::get('/appointment/reschedule/{event_id}/{secret}', [AppointmentController::class, 'showReschedule'])->name('appointments.reschedule')->middleware('throttle:100,1,resched');
Route::get('/appointment/reschedule/{event_id}/{secret}/slots', [AppointmentController::class, 'rescheduleSlots'])->name('appointments.reschedule_slots')->middleware('throttle:60,1,resched_slots');
Route::post('/appointment/reschedule/{event_id}/{secret}', [AppointmentController::class, 'reschedule'])->name('appointments.reschedule.store')->middleware('throttle:10,1,resched_post');
Route::post('/ticket/book/{event_id}/{secret}', [TicketController::class, 'passBook'])->name('pass.book')->middleware('throttle:30,1');
Route::post('/ticket/cancel-booking/{event_id}/{secret}', [TicketController::class, 'passCancelBooking'])->name('pass.cancel_booking')->middleware('throttle:30,1');
Route::post('/pass/resend-link', [TicketController::class, 'resendPassLink'])->name('pass.resend_link')->middleware('throttle:5,1');

Route::get('/gift-card/view/{gift_card_id}/{secret}', [GiftCardController::class, 'view'])->name('gift_card.view')->middleware('throttle:100,1');

// The buyer's payment-plan page, authenticated by the PLAN's secret (not the sale's, so a
// forwarded ticket link cannot reach it). Flat, outside the tenant groups, so it resolves the same
// on custom domains, subdomains and selfhost path routing.
//
// Distinct throttle prefixes, for the reason spelled out in the appointment block above: an
// unauthenticated limiter key carries no route name, so sharing a prefix would put the buyer's own
// page reloads in the same bucket as the payment POST and let browsing lock them out of paying.
Route::get('/installment/view/{plan_id}/{secret}', [InstallmentController::class, 'view'])->name('installment.view')->middleware('throttle:100,1,instal_view');
Route::post('/installment/pay/{plan_id}/{secret}', [InstallmentController::class, 'pay'])->name('installment.pay')->middleware('throttle:10,1,instal_pay');

Route::get('/feedback/{event_id}/{secret}', [FeedbackController::class, 'show'])->name('feedback.show')->middleware('throttle:60,1');
Route::post('/feedback/{event_id}/{secret}', [FeedbackController::class, 'store'])->name('feedback.store')->middleware('throttle:10,1');

// NOTE: '/tickets' is also a legacy WordPress marketing URL that still ranks, but it is
// deliberately NOT redirected. It collides with the authenticated "my tickets" route below, which
// is registered first and wins everywhere, so reclaiming it needs a host-scoped route ahead of
// this group - and that would 301 a signed-in visitor holding an old bookmark away from their own
// tickets, permanently, since browsers cache a 301 indefinitely. Queued mail is exposed too:
// jobs generate URLs from APP_URL (the bare marketing host), which is why ProcessBackupExport
// forces the root to app_url(), so any future route('tickets') in a mailable would silently
// become a marketing redirect. Not worth it for a page averaging position 23.
Route::middleware(['auth', 'verified', 'app_subdomain'])->group(function () {
    Route::get('/event', [EventController::class, 'createDefault'])->name('event.create_default');
    Route::get('/dashboard', [HomeController::class, 'home'])->name('home');
    Route::get('/dashboard/api/calendar-events', [HomeController::class, 'calendarEvents'])->name('home.calendar_events');
    Route::post('/dashboard/config', [HomeController::class, 'saveDashboardConfig'])->name('home.save_config');
    Route::post('/dashboard/federation-prompt/dismiss', [HomeController::class, 'dismissFederationPrompt'])->name('home.federation_prompt_dismiss');
    Route::post('/dashboard/next-steps/dismiss', [HomeController::class, 'dismissNextStep'])->name('home.next_steps_dismiss');
    Route::post('/dashboard/next-steps/dismiss-all', [HomeController::class, 'dismissAllNextSteps'])->name('home.next_steps_dismiss_all');
    Route::get('/getting-started', [HomeController::class, 'gettingStarted'])->name('getting-started');
    Route::get('/new/{type}', [RoleController::class, 'create'])->name('new');
    Route::post('/validate_address', [RoleController::class, 'validateAddress'])->name('validate_address')->middleware('throttle:25,1440');
    Route::post('/store', [RoleController::class, 'store'])->name('role.store');
    Route::get('/search-roles', [RoleController::class, 'search'])->name('role.search');
    Route::get('/search-subdomains', [RoleController::class, 'searchSubdomains'])->name('role.search-subdomains');
    Route::get('/search-events/{subdomain}', [RoleController::class, 'searchEvents'])->name('role.search_events');
    Route::get('/admin-edit-event/{hash}', [EventController::class, 'editAdmin'])->name('event.edit_admin');
    Route::get('/following', [RoleController::class, 'following'])->name('following');
    Route::post('/following/bulk-unfollow', [RoleController::class, 'bulkUnfollow'])->name('following.bulk-unfollow');
    // Account-wide duplicate venues. Must stay ahead of /{subdomain}/merge-venues below, which
    // has the same segment count and would otherwise match with subdomain = "following".
    Route::get('/following/merge-venues', [RoleController::class, 'mergeMyVenues'])->name('following.merge_venues');
    Route::get('/following/merge-venues/preview', [RoleController::class, 'mergeMyVenuesPreview'])->name('following.merge_venues_preview');
    Route::post('/following/merge-venues', [RoleController::class, 'mergeMyVenuesGroup'])->name('following.merge_venues_group');
    Route::post('/following/merge-venues/dismiss', [RoleController::class, 'dismissMyVenueMergeSuggestion'])->name('following.merge_venues_dismiss');
    Route::get('/tickets', [TicketController::class, 'tickets'])->name('tickets');
    Route::get('/my-carpools', [CarpoolController::class, 'myCarpools'])->name('my_carpools');
    Route::get('/sales', [TicketController::class, 'sales'])->name('sales');
    Route::get('/sales/import', [TicketController::class, 'importAttendees'])->name('sales.import');
    Route::post('/sales/import', [TicketController::class, 'importAttendeesStore'])->middleware('throttle:10,1')->name('sales.import_store');
    Route::get('/sales/export', [TicketController::class, 'exportSales'])->name('sales.export');
    Route::get('/sales/export-feedback', [FeedbackController::class, 'export'])->name('sales.export_feedback')->middleware('throttle:10,1');
    Route::post('/sales/send-feedback-now', [TicketController::class, 'sendFeedbackNow'])->name('sales.send_feedback_now');
    Route::post('/sales/cancel-feedback', [TicketController::class, 'cancelFeedback'])->name('sales.cancel_feedback');
    Route::post('/sales/action/{sale_id}', [TicketController::class, 'handleAction'])->name('sales.action');
    Route::post('/sales/resend-email/{sale_id}', [TicketController::class, 'resendEmail'])->name('sales.resend_email');
    Route::post('/gift-cards/action/{gift_card_id}', [GiftCardController::class, 'handleAction'])->name('gift_card.action');
    Route::post('/gift-cards/resend-email/{gift_card_id}', [GiftCardController::class, 'resendEmail'])->name('gift_card.resend_email');
    Route::post('/sales/resend-feedback/{sale_id}', [TicketController::class, 'resendFeedbackEmail'])->name('sales.resend_feedback')->middleware('throttle:30,1');
    Route::get('/waitlist', [WaitlistController::class, 'index'])->name('waitlist.index');
    Route::post('/waitlist/remove/{id}', [WaitlistController::class, 'remove'])->name('waitlist.remove');
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
    Route::get('/newsletter-segments/{hash}/edit', [NewsletterController::class, 'editSegment'])->name('newsletter.segment.edit');
    Route::post('/newsletter-segments/{hash}/users', [NewsletterController::class, 'storeSegmentUser'])->name('newsletter.segment.user.store');
    Route::put('/newsletter-segments/{hash}/users/{userHash}', [NewsletterController::class, 'updateSegmentUser'])->name('newsletter.segment.user.update');
    Route::delete('/newsletter-segments/{hash}/users/{userHash}', [NewsletterController::class, 'deleteSegmentUser'])->name('newsletter.segment.user.delete');
    Route::get('/newsletter-import', [NewsletterController::class, 'importForm'])->name('newsletter.import');
    Route::post('/newsletter-import', [NewsletterController::class, 'importStore'])->name('newsletter.import.store');
    Route::get('/newsletter-templates', [NewsletterController::class, 'templates'])->name('newsletter.templates');
    Route::get('/newsletter-templates/create', [NewsletterController::class, 'createTemplate'])->name('newsletter.template.create');
    Route::post('/newsletter-templates', [NewsletterController::class, 'storeTemplate'])->name('newsletter.template.store');
    Route::get('/newsletter-templates/{hash}/edit', [NewsletterController::class, 'editTemplate'])->name('newsletter.template.edit');
    Route::put('/newsletter-templates/{hash}', [NewsletterController::class, 'updateTemplate'])->name('newsletter.template.update');
    Route::delete('/newsletter-templates/{hash}', [NewsletterController::class, 'deleteTemplate'])->name('newsletter.template.delete');
    Route::post('/newsletter-templates/{hash}/preview', [NewsletterController::class, 'previewTemplate'])->name('newsletter.template.preview');
    Route::post('/newsletters/{hash}/save-as-template', [NewsletterController::class, 'saveAsTemplate'])->name('newsletter.save_as_template');
    Route::post('/newsletters/upload-image', [NewsletterController::class, 'uploadImage'])->name('newsletter.upload_image');

    // Boost routes
    Route::get('/boost', [BoostController::class, 'index'])->name('boost.index');
    Route::get('/boost/create', [BoostController::class, 'create'])->name('boost.create');
    Route::post('/boost', [BoostController::class, 'store'])->name('boost.store');
    Route::post('/boost/payment-intent', [BoostController::class, 'createPaymentIntent'])->name('boost.payment_intent');
    Route::get('/boost/search-interests', [BoostController::class, 'searchInterests'])->name('boost.search_interests');
    Route::get('/boost/estimate-reach', [BoostController::class, 'estimateReach'])->name('boost.estimate_reach');
    Route::post('/boost/generate-content', [BoostController::class, 'generateContent'])->name('boost.generate_content');
    Route::post('/boost/translate-defaults', [BoostController::class, 'translateDefaults'])->name('boost.translate_defaults');
    // On-network promotions. Separate from the boost.* endpoints on purpose: the Meta
    // payment path hardcodes its markup rate, so sharing it would flag every network
    // purchase as an amount mismatch.
    Route::get('/promotions/create', [PromotionController::class, 'create'])->name('promotions.create');
    Route::post('/promotions/payment-intent', [PromotionController::class, 'createPaymentIntent'])->name('promotions.payment_intent');
    Route::post('/promotions', [PromotionController::class, 'store'])->name('promotions.store');
    Route::get('/boost/{hash}', [BoostController::class, 'show'])->name('boost.show');
    Route::post('/boost/{hash}/toggle-pause', [BoostController::class, 'togglePause'])->name('boost.toggle_pause');
    Route::post('/boost/{hash}/cancel', [BoostController::class, 'cancel'])->name('boost.cancel');

    // Referral routes (hosted only)
    if (config('app.hosted') || config('app.is_testing')) {
        Route::get('/referrals', [ReferralController::class, 'index'])->name('referrals');
        Route::post('/referrals/apply-credit', [ReferralController::class, 'applyCredit'])->name('referrals.apply_credit');
    }

    Route::get('/settings', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/settings', [ProfileController::class, 'update'])->name('profile.update');
    // Throttled: this makes an outbound request to a user-chosen destination.
    Route::patch('/settings/payments', [ProfileController::class, 'updatePayments'])->name('profile.update_payments')->middleware('throttle:10,1');
    Route::delete('/settings', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::delete('/settings/profile-image', [ProfileController::class, 'deleteImage'])->name('profile.delete_image');

    // Push notifications (device subscription state + test send)
    Route::post('/settings/push/subscribe', [PushController::class, 'subscribe'])->name('push.subscribe');
    Route::post('/settings/push/unsubscribe', [PushController::class, 'unsubscribe'])->name('push.unsubscribe');
    Route::post('/settings/push/test', [PushController::class, 'test'])->name('push.test')->middleware('throttle:10,1');

    // Backup & Restore
    Route::post('/settings/backup/export', [BackupController::class, 'export'])->name('backup.export')->middleware('throttle:3,60');
    Route::post('/settings/backup/import', [BackupController::class, 'upload'])->name('backup.upload')->middleware('throttle:3,60');
    Route::post('/settings/backup/import/confirm', [BackupController::class, 'confirm'])->name('backup.confirm')->middleware('throttle:3,60');
    Route::post('/settings/backup/import/cancel', [BackupController::class, 'cancelUpload'])->name('backup.cancel_upload');
    Route::post('/settings/backup/cancel/{backupJob}', [BackupController::class, 'cancel'])->name('backup.cancel');
    Route::get('/settings/backup/download/{backupJob}', [BackupController::class, 'download'])->name('backup.download')->middleware('signed');
    Route::get('/settings/backup/status/{backupJob}', [BackupController::class, 'status'])->name('backup.status')->middleware('throttle:60,1');

    Route::get('/stripe/link', [StripeController::class, 'link'])->name('stripe.link');
    Route::post('/stripe/unlink', [StripeController::class, 'unlink'])->name('stripe.unlink');

    // Generic connect/disconnect for any gateway whose settings are a plain credentials form. Stripe
    // (OAuth) and Invoice Ninja (validate, then register a webhook) keep their own routes above,
    // because their connect flows are not form submissions.
    Route::post('/payments/{gateway}/connect', [PaymentGatewayController::class, 'connect'])->name('payments.connect')->middleware('throttle:10,1');
    Route::post('/payments/{gateway}/disconnect', [PaymentGatewayController::class, 'disconnect'])->name('payments.disconnect')->middleware('throttle:10,1');
    Route::get('/stripe/complete', [StripeController::class, 'complete'])->name('stripe.complete');
    Route::post('/invoiceninja/unlink', [InvoiceNinjaController::class, 'unlink'])->name('invoiceninja.unlink');
    Route::patch('/settings/invoiceninja-mode', [ProfileController::class, 'updateInvoiceninjaMode'])->name('profile.update_invoiceninja_mode');
    Route::post('/payment_url/unlink', [ProfileController::class, 'unlinkPaymentUrl'])->name('profile.unlink_payment_url');

    // Google Calendar routes
    Route::get('/google-calendar/redirect', [GoogleCalendarController::class, 'redirect'])->name('google.calendar.redirect');
    Route::get('/google-calendar/callback', [GoogleCalendarController::class, 'callback'])->name('google.calendar.callback');
    Route::get('/google-calendar/reauthorize', [GoogleCalendarController::class, 'reauthorize'])->name('google.calendar.reauthorize');
    Route::get('/google-calendar/disconnect', [GoogleCalendarController::class, 'disconnect'])->name('google.calendar.disconnect');
    Route::get('/google-calendar/calendars', [GoogleCalendarController::class, 'getCalendars'])->name('google.calendar.calendars');
    Route::post('/google-calendar/sync/{subdomain}', [GoogleCalendarController::class, 'sync'])->name('google.calendar.sync');
    Route::post('/google-calendar/force-sync-to-google/{subdomain}', [GoogleCalendarController::class, 'forceSyncToGoogle'])->name('google.calendar.force_sync_to_google')->middleware('throttle:5,1');
    Route::post('/google-calendar/member-sync/{subdomain}', [GoogleCalendarController::class, 'memberSync'])->name('google.calendar.member_sync');
    Route::post('/google-calendar/sync-event/{subdomain}/{eventId}', [GoogleCalendarController::class, 'syncEvent'])->name('google.calendar.sync_event');
    Route::delete('/google-calendar/unsync-event/{subdomain}/{eventId}', [GoogleCalendarController::class, 'unsyncEvent'])->name('google.calendar.unsync_event');

    // Microsoft (Outlook) Calendar routes
    Route::get('/microsoft-calendar/redirect', [MicrosoftCalendarController::class, 'redirect'])->name('microsoft.calendar.redirect');
    Route::get('/microsoft-calendar/callback', [MicrosoftCalendarController::class, 'callback'])->name('microsoft.calendar.callback');
    Route::get('/microsoft-calendar/reauthorize', [MicrosoftCalendarController::class, 'reauthorize'])->name('microsoft.calendar.reauthorize');
    Route::get('/microsoft-calendar/disconnect', [MicrosoftCalendarController::class, 'disconnect'])->name('microsoft.calendar.disconnect');
    Route::get('/microsoft-calendar/calendars', [MicrosoftCalendarController::class, 'getCalendars'])->name('microsoft.calendar.calendars');
    Route::post('/microsoft-calendar/sync/{subdomain}', [MicrosoftCalendarController::class, 'sync'])->name('microsoft.calendar.sync');
    Route::post('/microsoft-calendar/sync-event/{subdomain}/{eventId}', [MicrosoftCalendarController::class, 'syncEvent'])->name('microsoft.calendar.sync_event');
    Route::delete('/microsoft-calendar/unsync-event/{subdomain}/{eventId}', [MicrosoftCalendarController::class, 'unsyncEvent'])->name('microsoft.calendar.unsync_event');

    // CalDAV routes
    Route::post('/caldav/test-connection', [CalDAVController::class, 'testConnection'])->name('caldav.test_connection')->middleware('throttle:10,1');
    Route::post('/caldav/discover-calendars', [CalDAVController::class, 'discoverCalendars'])->name('caldav.discover_calendars')->middleware('throttle:10,1');
    Route::post('/caldav/settings/{subdomain}', [CalDAVController::class, 'saveSettings'])->name('caldav.save_settings')->middleware('throttle:10,1');
    Route::delete('/caldav/disconnect/{subdomain}', [CalDAVController::class, 'disconnect'])->name('caldav.disconnect')->middleware('throttle:10,1');
    Route::post('/caldav/sync/{subdomain}', [CalDAVController::class, 'sync'])->name('caldav.sync')->middleware('throttle:5,1');
    Route::patch('/caldav/sync-direction/{subdomain}', [CalDAVController::class, 'updateSyncDirection'])->name('caldav.update_sync_direction')->middleware('throttle:30,1');

    Route::get('/scan', [TicketController::class, 'scan'])->name('ticket.scan');
    Route::post('/ticket/view/{event_id}/{secret}', [TicketController::class, 'scanned'])->name('ticket.scanned');

    Route::get('/checkin', [CheckInController::class, 'index'])->name('checkin.index');
    Route::get('/checkin/{event_id}/stats', [CheckInController::class, 'stats'])->name('checkin.stats');
    // Find an attendee at the door. Read-only: admitting still goes through the scan.
    Route::get('/checkin/{event_id}/search', [CheckInController::class, 'search'])->name('checkin.search')->middleware('throttle:60,1');

    Route::get('/{subdomain}/api/admin-calendar-events', [RoleController::class, 'adminCalendarEvents'])->name('role.admin_calendar_events');
    Route::post('/{subdomain}/change-plan/{plan_type}', [RoleController::class, 'changePlan'])->name('role.change_plan');
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
    Route::post('/{subdomain}/test-feedback-email', [RoleController::class, 'testFeedbackEmail'])->name('role.test_feedback_email');
    Route::delete('/{subdomain}/delete', [RoleController::class, 'delete'])->name('role.delete');
    Route::delete('/{subdomain}/delete-image', [RoleController::class, 'deleteImage'])->name('role.delete_image');
    Route::get('/{subdomain}/merge-preview', [RoleController::class, 'mergePreview'])->name('role.merge_preview');
    Route::post('/{subdomain}/merge', [RoleController::class, 'mergeInto'])->name('role.merge');
    Route::get('/{subdomain}/merge-venues', [RoleController::class, 'mergeVenues'])->name('role.merge_venues');
    Route::get('/{subdomain}/merge-venues/preview', [RoleController::class, 'mergeVenuesGroupPreview'])->name('role.merge_venues_preview');
    Route::post('/{subdomain}/merge-venues', [RoleController::class, 'mergeVenuesGroup'])->name('role.merge_venues_group');
    Route::post('/{subdomain}/merge-venues/dismiss', [RoleController::class, 'dismissVenueMergeSuggestion'])->name('role.merge_venues_dismiss');
    Route::post('/{subdomain}/timezone-warning/dismiss', [RoleController::class, 'dismissTimezoneWarning'])->name('role.timezone_warning_dismiss');
    Route::post('/{subdomain}/timezone-warning/fix-events', [RoleController::class, 'fixEventsTimezone'])->name('role.timezone_warning_fix_events');
    Route::get('/{subdomain}/add-event', [EventController::class, 'create'])->name('event.create');
    Route::get('/{subdomain}/verify/{hash}', [RoleController::class, 'verify'])->name('role.verification.verify');
    Route::get('/{subdomain}/resend', [RoleController::class, 'resendVerify'])->name('role.verification.resend')->middleware('throttle:5,1');
    Route::post('/{subdomain}/phone/send-code', [RoleController::class, 'phoneSendCode'])->name('role.phone.send_code')->middleware('throttle:5,1');
    Route::post('/{subdomain}/phone/verify-code', [RoleController::class, 'phoneVerifyCode'])->name('role.phone.verify_code')->middleware('throttle:10,1');
    Route::post('/{subdomain}/resend-invite/{hash}', [RoleController::class, 'resendInvite'])->name('role.resend_invite')->middleware('throttle:5,1');
    Route::post('/{subdomain}/store-event', [EventController::class, 'store'])->name('event.store')->middleware('throttle:60,1');
    Route::get('/{subdomain}/edit-event/{hash}', [EventController::class, 'edit'])->name('event.edit');
    Route::get('/{subdomain}/clone-event/{hash}', [EventController::class, 'clone'])->name('event.clone');
    Route::post('/{subdomain}/save-as-template/{hash}', [EventTemplateController::class, 'store'])->name('event_template.store');
    Route::get('/{subdomain}/templates/{hash}/apply', [EventTemplateController::class, 'apply'])->name('event_template.apply');
    Route::put('/{subdomain}/templates/{hash}', [EventTemplateController::class, 'update'])->name('event_template.update');
    Route::delete('/{subdomain}/templates/{hash}', [EventTemplateController::class, 'destroy'])->name('event_template.destroy');
    Route::delete('/{subdomain}/delete-event/{hash}', [EventController::class, 'delete'])->name('event.delete');
    Route::put('/{subdomain}/update-event/{hash}', [EventController::class, 'update'])->name('event.update');
    Route::post('/{subdomain}/cancel-event/{hash}', [EventController::class, 'cancel'])->name('event.cancel');
    Route::post('/{subdomain}/restore-event/{hash}', [EventController::class, 'restore'])->name('event.restore');
    Route::post('/{subdomain}/notify-preview/{hash}', [EventController::class, 'notifyPreview'])->name('event.notify_preview');
    Route::delete('/{subdomain}/delete-event-image', [EventController::class, 'deleteImage'])->name('event.delete_image');
    Route::get('/{subdomain}/events-graphic', [GraphicController::class, 'generateGraphic'])->name('event.generate_graphic');
    Route::get('/{subdomain}/events-graphic/data', [GraphicController::class, 'generateGraphicData'])->name('event.generate_graphic_data');
    Route::get('/{subdomain}/events-graphic/download', [GraphicController::class, 'downloadGraphic'])->name('event.download_graphic');
    Route::get('/{subdomain}/events-graphic/settings', [GraphicController::class, 'getSettings'])->name('event.graphic_settings');
    Route::post('/{subdomain}/events-graphic/settings', [GraphicController::class, 'saveSettings'])->name('event.save_graphic_settings');
    Route::post('/{subdomain}/events-graphic/ai-text', [GraphicController::class, 'processGraphicAIText'])->name('event.graphic_ai_text');
    Route::get('/{subdomain}/events-graphic/ai-text/{requestId}', [GraphicController::class, 'pollGraphicAIText'])->name('event.graphic_ai_poll');
    Route::post('/{subdomain}/events-graphic/test-email', [GraphicController::class, 'sendTestEmail'])->name('event.graphic_test_email');
    Route::post('/{subdomain}/events-graphic/header-image', [GraphicController::class, 'uploadHeaderImage'])->name('event.graphic_upload_header_image');
    Route::delete('/{subdomain}/events-graphic/header-image', [GraphicController::class, 'deleteHeaderImage'])->name('event.graphic_delete_header_image');
    Route::get('/{subdomain}/clear-videos/{event_hash}/{role_hash}', [EventController::class, 'clearVideos'])->name('event.clear_videos');
    Route::post('/{subdomain}/requests/accept-event/{hash}', [EventController::class, 'accept'])->name('event.accept');
    Route::post('/{subdomain}/requests/decline-event/{hash}', [EventController::class, 'decline'])->name('event.decline');
    Route::post('/{subdomain}/requests/accept-all', [EventController::class, 'acceptAll'])->name('event.accept_all');
    // Appointments: owner-side type management (guest booking routes are in the public blocks).
    Route::post('/{subdomain}/appointments', [AppointmentTypeController::class, 'store'])->name('appointments.store');
    Route::put('/{subdomain}/appointments/{hash}', [AppointmentTypeController::class, 'update'])->name('appointments.update');
    Route::delete('/{subdomain}/appointments/{hash}', [AppointmentTypeController::class, 'destroy'])->name('appointments.destroy');
    Route::post('/{subdomain}/appointments/{hash}/toggle', [AppointmentTypeController::class, 'toggle'])->name('appointments.toggle');
    Route::post('/{subdomain}/appointments/{hash}/duplicate', [AppointmentTypeController::class, 'duplicate'])->name('appointments.duplicate');
    // Allocated seating: template CRUD plus the designer's read/save endpoints. Every one of
    // these is Enterprise-gated in the controller, editor-only, and scoped to the schedule.
    Route::post('/{subdomain}/seating', [SeatingPlanController::class, 'store'])->name('seating.store');
    Route::put('/{subdomain}/seating/{hash}', [SeatingPlanController::class, 'update'])->name('seating.update');
    Route::delete('/{subdomain}/seating/{hash}', [SeatingPlanController::class, 'destroy'])->name('seating.destroy');
    Route::post('/{subdomain}/seating/{hash}/duplicate', [SeatingPlanController::class, 'duplicate'])->name('seating.duplicate');
    Route::get('/{subdomain}/seating/{hash}/design', [SeatingPlanController::class, 'design'])->name('seating.design');
    Route::get('/{subdomain}/seating/{hash}/structure', [SeatingPlanController::class, 'structure'])->name('seating.structure');
    Route::put('/{subdomain}/seating/{hash}/structure', [SeatingPlanController::class, 'saveStructure'])->name('seating.save_structure')->middleware('throttle:60,1,seating_save');
    // "Modify this date only" - the same designer against one occurrence's snapshot. Registered
    // before nothing in particular; the /occurrence/ segment keeps it clear of the plan routes.
    Route::get('/{subdomain}/seating/occurrence/{hash}/design', [SeatingPlanController::class, 'designOccurrence'])->name('seating.occurrence_design');
    Route::get('/{subdomain}/seating/occurrence/{hash}/structure', [SeatingPlanController::class, 'occurrenceStructure'])->name('seating.occurrence_structure');
    Route::put('/{subdomain}/seating/occurrence/{hash}/structure', [SeatingPlanController::class, 'saveOccurrenceStructure'])->name('seating.occurrence_save')->middleware('throttle:60,1,seating_save');
    Route::post('/{subdomain}/seating/occurrence/{hash}/revert', [SeatingPlanController::class, 'revertOccurrence'])->name('seating.occurrence_revert');

    // Box office console. Staff-side, so the payload here carries the internal hold note and the
    // booker - deliberately a different controller from the guest picker's.
    //
    // The five write actions share one limiter. Each takes an array of seats, so even clearing a
    // whole row is a handful of requests, but each can also refund money, create a sale, or mail
    // the waitlist - and on a selfhost install QUEUE_CONNECTION=sync sends that mail inside the
    // request. A stuck retry loop or a compromised staff account should not be able to empty a
    // house or a mail quota faster than a person could click.
    Route::get('/{subdomain}/seating/box-office/{hash}', [BoxOfficeController::class, 'show'])->name('box_office.show');
    Route::get('/{subdomain}/seating/box-office/{hash}/state', [BoxOfficeController::class, 'state'])->name('box_office.state');
    Route::post('/{subdomain}/seating/box-office/{hash}/block', [BoxOfficeController::class, 'block'])->name('box_office.block')->middleware('throttle:60,1,box_office');
    Route::post('/{subdomain}/seating/box-office/{hash}/unblock', [BoxOfficeController::class, 'unblock'])->name('box_office.unblock')->middleware('throttle:60,1,box_office');
    Route::post('/{subdomain}/seating/box-office/{hash}/release-seat', [BoxOfficeController::class, 'releaseSeat'])->name('box_office.release_seat')->middleware('throttle:60,1,box_office');
    Route::post('/{subdomain}/seating/box-office/{hash}/exchange', [BoxOfficeController::class, 'exchange'])->name('box_office.exchange')->middleware('throttle:60,1,box_office');
    Route::post('/{subdomain}/seating/box-office/{hash}/book', [BoxOfficeController::class, 'bookSeats'])->name('box_office.book')->middleware('throttle:60,1,box_office');
    Route::get('/{subdomain}/seating/box-office/{hash}/report', [BoxOfficeController::class, 'report'])->name('box_office.report');
    Route::get('/{subdomain}/seating/box-office/{hash}/report.csv', [BoxOfficeController::class, 'reportCsv'])->name('box_office.report_csv');
    // Owner-side reschedule. Throttled like the other write routes: it drives the same inline calendar
    // fan-out and guest mail, so a compromised editor account should not be unbounded.
    Route::get('/{subdomain}/appointments/bookings/{saleHash}/reschedule', [AppointmentTypeController::class, 'showBookingReschedule'])->name('appointments.booking_reschedule');
    Route::get('/{subdomain}/appointments/bookings/{saleHash}/reschedule/slots', [AppointmentTypeController::class, 'bookingRescheduleSlots'])->name('appointments.booking_reschedule_slots')->middleware('throttle:60,1,owner_resched_slots');
    Route::post('/{subdomain}/appointments/bookings/{saleHash}/reschedule', [AppointmentTypeController::class, 'bookingReschedule'])->name('appointments.booking_reschedule.store')->middleware('throttle:30,1,owner_resched_post');
    Route::post('/{subdomain}/appointments/bookings/{saleHash}/cancel', [AppointmentTypeController::class, 'bookingCancel'])->name('appointments.booking_cancel');
    Route::post('/{subdomain}/publish-event/{hash}', [EventController::class, 'publish'])->name('event.publish');
    Route::post('/{subdomain}/preview-link', [RoleController::class, 'previewLink'])->name('role.preview_link');
    Route::get('/{subdomain}/followers/qr-code', [RoleController::class, 'qrCode'])->name('role.qr_code');
    Route::delete('/{subdomain}/subscribers/{hash}', [RoleSubscriberController::class, 'remove'])
        ->name('role.subscribers.remove')->where('hash', '[A-Za-z0-9+=]+');
    Route::get('/{subdomain}/team/add-member', [RoleController::class, 'createMember'])->name('role.create_member');
    Route::post('/{subdomain}/team/add-member', [RoleController::class, 'storeMember'])->name('role.store_member');
    Route::delete('/{subdomain}/team/remove-member/{hash}', [RoleController::class, 'removeMember'])->name('role.remove_member');
    Route::patch('/{subdomain}/team/update-member-level/{hash}', [RoleController::class, 'updateMemberLevel'])->name('role.update_member_level');
    // Ownership handover, owner side. Owner-only in the controller (roles.user_id, not
    // isEditor) - an admin can manage members but cannot give the schedule away.
    Route::get('/{subdomain}/team/transfer', [RoleController::class, 'createTransfer'])->name('role.transfer.create');
    Route::post('/{subdomain}/team/transfer', [RoleController::class, 'storeTransfer'])->name('role.transfer.store')->middleware('throttle:5,1');
    Route::post('/{subdomain}/team/transfer/cancel', [RoleController::class, 'cancelTransfer'])->name('role.transfer.cancel');
    Route::post('/{subdomain}/team/transfer/resend', [RoleController::class, 'resendTransfer'])->name('role.transfer.resend')->middleware('throttle:5,1');
    Route::delete('/{subdomain}/uncurate-event/{hash}', [EventController::class, 'uncurate'])->name('event.uncurate');
    Route::get('/{subdomain}/import', [EventController::class, 'showImportHub'])->name('event.show_import');
    Route::get('/{subdomain}/import/ai', [EventController::class, 'showImport'])->name('event.show_import_ai');
    Route::post('/{subdomain}/import/ai', [EventController::class, 'import'])->name('event.import')->middleware('throttle:60,1');
    Route::get('/{subdomain}/import/eventbrite', [EventbriteController::class, 'show'])->name('event.show_import_eventbrite');
    Route::post('/{subdomain}/import/eventbrite/connect', [EventbriteController::class, 'connect'])->name('event.eventbrite_connect')->middleware('throttle:10,1');
    Route::post('/{subdomain}/import/eventbrite/import', [EventbriteController::class, 'import'])->name('event.eventbrite_import')->middleware('throttle:60,1');
    Route::post('/{subdomain}/parse', [EventController::class, 'parse'])->name('event.parse')->middleware('throttle:30,1');
    Route::post('/{subdomain}/parse-event-parts', [EventController::class, 'parseEventParts'])->name('event.parse_parts')->middleware('throttle:30,1');
    Route::post('/{subdomain}/generate-flyer', [EventController::class, 'generateFlyer'])->name('event.generate_flyer');
    Route::get('/{subdomain}/generate-flyer/{requestId}', [EventController::class, 'pollFlyer'])->name('event.poll_flyer');
    Route::post('/{subdomain}/generate-style', [RoleController::class, 'generateStyle'])->name('role.generate_style');
    Route::post('/{subdomain}/generate-style-image', [RoleController::class, 'generateStyleImage'])->name('role.generate_style_image');
    Route::get('/{subdomain}/generate-style-image/{requestId}', [RoleController::class, 'pollStyleImage'])->name('role.poll_style_image');
    Route::post('/{subdomain}/generate-schedule-details', [RoleController::class, 'generateScheduleDetails'])->name('role.generate_schedule_details');
    Route::post('/{subdomain}/get-style-prompt', [RoleController::class, 'getStylePrompt'])->name('role.get_style_prompt');
    Route::post('/{subdomain}/get-schedule-details-prompt', [RoleController::class, 'getScheduleDetailsPrompt'])->name('role.get_schedule_details_prompt');
    Route::post('/generate-style', [RoleController::class, 'generateStyleNew'])->name('role.generate_style_new');
    Route::post('/generate-style-image', [RoleController::class, 'generateStyleImageNew'])->name('role.generate_style_image_new');
    Route::get('/generate-style-image/{requestId}', [RoleController::class, 'pollStyleImageNew'])->name('role.poll_style_image_new');
    Route::post('/generate-schedule-details', [RoleController::class, 'generateScheduleDetailsNew'])->name('role.generate_schedule_details_new');
    Route::post('/get-style-prompt', [RoleController::class, 'getStylePromptNew'])->name('role.get_style_prompt_new');
    Route::post('/get-schedule-details-prompt', [RoleController::class, 'getScheduleDetailsPromptNew'])->name('role.get_schedule_details_prompt_new');
    Route::post('/{subdomain}/generate-event-details', [EventController::class, 'generateEventDetails'])->name('event.generate_event_details');
    Route::post('/{subdomain}/get-event-details-prompt', [EventController::class, 'getEventDetailsPrompt'])->name('event.get_event_details_prompt');
    Route::post('/{subdomain}/test-import', [RoleController::class, 'testImport'])->name('role.test_import');
    Route::post('/{subdomain}/update-all-categories', [RoleController::class, 'updateAllCategories'])->name('role.update_all_categories');
    Route::post('/{subdomain}/update-all-slugs', [RoleController::class, 'updateAllSlugs'])->name('role.update_all_slugs');
    Route::get('/{subdomain}/search-youtube', [RoleController::class, 'searchYouTube'])->name('role.search_youtube');
    Route::get('/{subdomain}/match-videos', [RoleController::class, 'getTalentRolesWithoutVideos'])->name('role.talent_roles_without_videos');
    Route::post('/{subdomain}/save-video', [RoleController::class, 'saveVideo'])->name('role.save_video');
    Route::post('/{subdomain}/save-videos', [RoleController::class, 'saveVideos'])->name('role.save_videos');
    Route::post('/{subdomain}/remove-video', [RoleController::class, 'removeVideo'])->name('role.remove_video')->middleware('throttle:60,1');

    Route::post('/{subdomain}/approve-video/{hash}', [EventController::class, 'approveVideo'])->name('event.approve_video');
    Route::delete('/{subdomain}/reject-video/{hash}', [EventController::class, 'rejectVideo'])->name('event.reject_video');
    Route::post('/{subdomain}/approve-comment/{hash}', [EventController::class, 'approveComment'])->name('event.approve_comment');
    Route::delete('/{subdomain}/reject-comment/{hash}', [EventController::class, 'rejectComment'])->name('event.reject_comment');
    Route::post('/{subdomain}/approve-photo/{hash}', [EventController::class, 'approvePhoto'])->name('event.approve_photo');
    Route::delete('/{subdomain}/reject-photo/{hash}', [EventController::class, 'rejectPhoto'])->name('event.reject_photo');
    Route::get('/{subdomain}/download-photos/{event_hash}', [EventController::class, 'downloadPhotos'])->name('event.download_photos')->middleware('throttle:5,1');
    Route::delete('/{subdomain}/carpool/remove-offer/{offer_hash}', [CarpoolController::class, 'adminRemoveOffer'])->name('carpool.admin_remove_offer');
    Route::delete('/{subdomain}/carpool/dismiss-report/{report_hash}', [CarpoolController::class, 'adminDismissReport'])->name('carpool.admin_dismiss_report');

    Route::post('/{subdomain}/polls/{event_hash}', [EventController::class, 'storePoll'])->name('event.store_poll');
    Route::put('/{subdomain}/polls/{event_hash}/{poll_hash}', [EventController::class, 'updatePoll'])->name('event.update_poll');
    Route::delete('/{subdomain}/polls/{event_hash}/{poll_hash}', [EventController::class, 'deletePoll'])->name('event.delete_poll');
    Route::post('/{subdomain}/polls/{event_hash}/{poll_hash}/toggle', [EventController::class, 'togglePoll'])->name('event.toggle_poll');
    Route::post('/{subdomain}/polls/{event_hash}/{poll_hash}/approve-option', [EventController::class, 'approvePollOption'])->name('event.approve_poll_option');
    Route::post('/{subdomain}/polls/{event_hash}/{poll_hash}/reject-option', [EventController::class, 'rejectPollOption'])->name('event.reject_poll_option');

    Route::get('/{subdomain}/scan-agenda', [EventController::class, 'scanAgenda'])->name('event.scan_agenda')->where('subdomain', '(?!docs(?=/|$))[^/]+');
    Route::post('/{subdomain}/save-event-parts', [EventController::class, 'saveEventParts'])->name('event.save_parts');

    Route::get('/{subdomain}/audit-log', [RoleController::class, 'auditLog'])->name('role.audit_log')->where('subdomain', '(?!docs(?=/|$)|admin(?=/|$))[^/]+');
    // `features` is excluded for the same reason as `docs`: this group is domain-less and is
    // registered first, so /features/availability and /features/appointments would otherwise match
    // here as subdomain+tab and 302 to the login page instead of reaching the marketing pages.
    //
    // Only on the nexus, because that is the only place those marketing routes are registered (see
    // the `if (config('app.is_nexus'))` block below). Excluding it everywhere would cost a selfhost
    // install a schedule legitimately living at /features - the reserved-subdomain list in Role.php
    // is gated on config('app.hosted') and is not consulted by the rename path at all, so a
    // schedule really can hold that subdomain.
    $adminTabSubdomain = config('app.is_nexus')
        ? '(?!docs(?=/|$)|features(?=/|$))[^/]+'
        : '(?!docs(?=/|$))[^/]+';
    Route::get('/{subdomain}/{tab}', [RoleController::class, 'viewAdmin'])->name('role.view_admin')->where('tab', 'schedule|templates|availability|appointments|seating|requests|followers|team|plan|videos')->where('subdomain', $adminTabSubdomain);

    Route::post('/{subdomain}/upload-image', [EventController::class, 'uploadImage'])->name('event.upload_image');

    Route::get('/api/documentation', fn () => redirect()->route('marketing.docs.developer.api'))->name('api.documentation');

    Route::patch('/api-settings', [ApiSettingsController::class, 'update'])->name('api-settings.update');

    // Webhook settings
    Route::post('/webhooks', [WebhookSettingsController::class, 'store'])->name('webhooks.store');
    Route::put('/webhooks/{webhookHash}', [WebhookSettingsController::class, 'update'])->name('webhooks.update');
    Route::delete('/webhooks/{webhookHash}', [WebhookSettingsController::class, 'destroy'])->name('webhooks.destroy');
    Route::post('/webhooks/{webhookHash}/toggle', [WebhookSettingsController::class, 'toggle'])->name('webhooks.toggle');
    Route::post('/webhooks/{webhookHash}/regenerate-secret', [WebhookSettingsController::class, 'regenerateSecret'])->name('webhooks.regenerate_secret');
    Route::post('/webhooks/{webhookHash}/test', [WebhookSettingsController::class, 'test'])->name('webhooks.test');
    Route::get('/webhooks/{webhookHash}/deliveries', [WebhookSettingsController::class, 'deliveries'])->name('webhooks.deliveries');

    // Support chat routes (hosted only)
    if (config('app.hosted')) {
        Route::middleware('throttle:30,1')->group(function () {
            Route::get('/support-chat/status', [SupportChatController::class, 'status'])->name('support-chat.status');
            Route::get('/support-chat/messages', [SupportChatController::class, 'getMessages'])->name('support-chat.messages');
            Route::post('/support-chat/messages', [SupportChatController::class, 'sendMessage'])->name('support-chat.send');
            Route::post('/support-chat/mark-read', [SupportChatController::class, 'markRead'])->name('support-chat.mark-read');
        });
    }

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
        // Growth reporting. Registered everywhere but runtime-404s off a hosted install
        // (there are no tiers or subscriptions on a single-tenant selfhost, so the page
        // would be misleading). Same registration-time reasoning as federation below:
        // phpunit leaves IS_HOSTED unset, so a registration-time gate would be untestable.
        Route::get('/admin/growth', [AdminController::class, 'growth'])->name('admin.growth');
        Route::get('/admin/growth/export', [AdminController::class, 'growthExport'])->name('admin.growth.export');
        Route::get('/admin/boost', [AdminController::class, 'boost'])->name('admin.boost');
        Route::post('/admin/boost/grant-credit', [AdminController::class, 'boostGrantCredit'])->name('admin.boost.grant_credit');
        Route::post('/admin/boost/set-limit', [AdminController::class, 'boostSetLimit'])->name('admin.boost.set_limit');
        Route::post('/admin/translation/retry', [AdminController::class, 'retryTranslation'])->name('admin.translation.retry');
        // App Update (selfhost). Registered on every install and 404'd at runtime inside the
        // controller, matching the federation and growth routes above: phpunit pins
        // IS_NEXUS=true, and AdminAlertService::row() drops any alert row whose route is not
        // registered - silently - so a registration-time gate would make the nav badge both
        // untestable and invisible with nothing to debug.
        Route::get('/admin/app-update', [AdminController::class, 'appUpdate'])->name('admin.app_update');
        Route::post('/admin/app-update/run', [AdminController::class, 'appUpdateRun'])->name('admin.app_update.run');
        // Tighter than the group's 30/min: unauthenticated GitHub allows 60 calls an hour for
        // the whole install, and this is the only button that spends that budget on demand.
        Route::post('/admin/app-update/check', [AdminController::class, 'appUpdateCheck'])
            ->name('admin.app_update.check')
            ->middleware('throttle:5,1');
        Route::get('/admin/settings', [AdminController::class, 'settings'])->name('admin.settings');
        Route::post('/admin/settings', [AdminController::class, 'updateSettings'])->name('admin.settings.update');
        // Own endpoint, not admin.settings.update: cards sharing that action have to carry
        // each other's values through as hidden inputs, and a miss silently wipes settings.
        Route::post('/admin/settings/monetization', [AdminController::class, 'updateAdsSettings'])->name('admin.settings.update_ads');
        Route::post('/admin/settings/accommodation', [AdminController::class, 'updateStay22Settings'])->name('admin.settings.update_stay22');
        Route::post('/admin/settings/currency', [AdminController::class, 'updateCurrencySettings'])->name('admin.settings.update_currency');
        Route::post('/admin/settings/plan-pricing', [AdminController::class, 'updatePlanPricingSettings'])->name('admin.settings.update_plan_pricing');

        // Operator-authored privacy policy / terms / cookie policy. One endpoint per
        // document, for the reason given above.
        Route::get('/admin/legal', [AdminLegalController::class, 'index'])->name('admin.legal');
        Route::post('/admin/legal/{type}', [AdminLegalController::class, 'update'])
            ->where('type', 'privacy|terms|cookies')
            ->name('admin.legal.update');

        // Federation moderation. Registered everywhere but runtime-404s off the nexus,
        // because phpunit pins IS_NEXUS=true and a registration-time gate would be
        // untestable (same reasoning as the translation review routes below).
        Route::get('/admin/federation', [AdminFederationController::class, 'index'])->name('admin.federation');
        Route::post('/admin/federation/bulk', [AdminFederationController::class, 'bulk'])->name('admin.federation.bulk');
        Route::post('/admin/federation/{hash}/approve', [AdminFederationController::class, 'approve'])->name('admin.federation.approve');
        Route::post('/admin/federation/{hash}/suspend', [AdminFederationController::class, 'suspend'])->name('admin.federation.suspend');
        Route::post('/admin/federation/{hash}/delete', [AdminFederationController::class, 'destroy'])->name('admin.federation.delete');
        Route::post('/admin/federation/event/{hash}/block', [AdminFederationController::class, 'blockEvent'])->name('admin.federation.block_event');
        if (config('app.hosted')) {
            Route::get('/admin/domains', [AdminController::class, 'domains'])->name('admin.domains');
            Route::post('/admin/domains/{role}/reprovision', [AdminController::class, 'domainReprovision'])->name('admin.domains.reprovision');
            Route::post('/admin/domains/{role}/remove', [AdminController::class, 'domainRemove'])->name('admin.domains.remove');
            Route::redirect('/admin/plans', '/admin/schedules');
            Route::get('/admin/schedules', [AdminController::class, 'schedules'])->name('admin.schedules');
            Route::get('/admin/schedules/{role}/edit', [AdminController::class, 'editSchedule'])->name('admin.schedules.edit');
            Route::put('/admin/schedules/{role}', [AdminController::class, 'updateSchedule'])->name('admin.schedules.update');
            Route::post('/admin/schedules/{role}/verify-email', [AdminController::class, 'verifyScheduleEmail'])->name('admin.schedules.verify_email');
            Route::post('/admin/schedules/{role}/verify-phone', [AdminController::class, 'verifySchedulePhone'])->name('admin.schedules.verify_phone');
        }

        if (config('app.hosted')) {
            Route::get('/admin/referrals', [AdminController::class, 'referrals'])->name('admin.referrals');
        }

        Route::get('/admin/audit-log', [AdminController::class, 'auditLog'])->name('admin.audit_log');
        Route::post('/admin/sale/{sale}/approve', [AdminController::class, 'approveSale'])->name('admin.sale.approve');
        Route::post('/admin/sale/{sale}/refund', [AdminController::class, 'refundSale'])->name('admin.sale.refund');
        Route::post('/admin/promotions/{campaign}/approve', [AdminController::class, 'approvePromotion'])->name('admin.promotions.approve');
        Route::post('/admin/promotions/{campaign}/reject', [AdminController::class, 'rejectPromotion'])->name('admin.promotions.reject');
        Route::post('/admin/boost/{campaign}/approve', [AdminController::class, 'approveBoost'])->name('admin.boost.approve');
        Route::post('/admin/boost/{campaign}/refund', [AdminController::class, 'refundBoost'])->name('admin.boost.refund');

        // Admin queue routes
        Route::get('/admin/queue', [AdminController::class, 'queue'])->name('admin.queue');
        Route::post('/admin/queue/retry/{id}', [AdminController::class, 'queueRetry'])->name('admin.queue.retry');
        Route::post('/admin/queue/delete/{id}', [AdminController::class, 'queueDelete'])->name('admin.queue.delete');
        Route::post('/admin/queue/retry-all', [AdminController::class, 'queueRetryAll'])->name('admin.queue.retry-all');
        Route::post('/admin/queue/clear-failed', [AdminController::class, 'queueClearFailed'])->name('admin.queue.clear-failed');
        Route::post('/admin/queue/flush-pending', [AdminController::class, 'queueFlushPending'])->name('admin.queue.flush-pending');

        // Admin log viewer routes
        Route::get('/admin/logs', [AdminController::class, 'logs'])->name('admin.logs');
        Route::post('/admin/logs/clear', [AdminController::class, 'logsClear'])->name('admin.logs.clear');
        Route::get('/admin/logs/download', [AdminController::class, 'logsDownload'])->name('admin.logs.download');

        // Admin newsletter routes
        Route::get('/admin/newsletters', [AdminNewsletterController::class, 'index'])->name('admin.newsletters.index');
        Route::get('/admin/newsletters/create', [AdminNewsletterController::class, 'create'])->name('admin.newsletters.create');
        Route::post('/admin/newsletters', [AdminNewsletterController::class, 'store'])->name('admin.newsletters.store');
        Route::post('/admin/newsletters/preview-draft', [AdminNewsletterController::class, 'previewDraft'])->name('admin.newsletters.preview_draft');
        Route::get('/admin/newsletters/{hash}/edit', [AdminNewsletterController::class, 'edit'])->name('admin.newsletters.edit');
        Route::put('/admin/newsletters/{hash}', [AdminNewsletterController::class, 'update'])->name('admin.newsletters.update');
        Route::delete('/admin/newsletters/{hash}', [AdminNewsletterController::class, 'delete'])->name('admin.newsletters.delete');
        Route::post('/admin/newsletters/{hash}/send', [AdminNewsletterController::class, 'send'])->name('admin.newsletters.send');
        Route::post('/admin/newsletters/{hash}/schedule', [AdminNewsletterController::class, 'schedule'])->name('admin.newsletters.schedule');
        Route::post('/admin/newsletters/{hash}/cancel', [AdminNewsletterController::class, 'cancel'])->name('admin.newsletters.cancel');
        Route::post('/admin/newsletters/{hash}/clone', [AdminNewsletterController::class, 'cloneNewsletter'])->name('admin.newsletters.clone');
        Route::post('/admin/newsletters/{hash}/preview', [AdminNewsletterController::class, 'preview'])->name('admin.newsletters.preview');
        Route::post('/admin/newsletters/{hash}/test-send', [AdminNewsletterController::class, 'testSend'])->name('admin.newsletters.test_send');
        Route::get('/admin/newsletters/{hash}/stats', [AdminNewsletterController::class, 'stats'])->name('admin.newsletters.stats');
        Route::get('/admin/newsletter-segments', [AdminNewsletterController::class, 'segments'])->name('admin.newsletters.segments');
        Route::post('/admin/newsletter-segments', [AdminNewsletterController::class, 'storeSegment'])->name('admin.newsletters.segment.store');
        Route::delete('/admin/newsletter-segments/{hash}', [AdminNewsletterController::class, 'deleteSegment'])->name('admin.newsletters.segment.delete');
        Route::put('/admin/newsletter-segments/{hash}', [AdminNewsletterController::class, 'updateSegment'])->name('admin.newsletters.segment.update');
        Route::get('/admin/newsletter-segments/{hash}/edit', [AdminNewsletterController::class, 'editSegment'])->name('admin.newsletters.segment.edit');
        Route::post('/admin/newsletter-segments/{hash}/users', [AdminNewsletterController::class, 'storeSegmentUser'])->name('admin.newsletters.segment.user.store');
        Route::delete('/admin/newsletter-segments/{hash}/users/{userHash}', [AdminNewsletterController::class, 'deleteSegmentUser'])->name('admin.newsletters.segment.user.delete');
        Route::get('/admin/users/search', [AdminNewsletterController::class, 'searchUsers'])->name('admin.users.search');
        Route::get('/admin/newsletter-templates', [AdminNewsletterController::class, 'templates'])->name('admin.newsletters.templates');
        Route::get('/admin/newsletter-templates/create', [AdminNewsletterController::class, 'createTemplate'])->name('admin.newsletters.template.create');
        Route::post('/admin/newsletter-templates', [AdminNewsletterController::class, 'storeTemplate'])->name('admin.newsletters.template.store');
        Route::get('/admin/newsletter-templates/{hash}/edit', [AdminNewsletterController::class, 'editTemplate'])->name('admin.newsletters.template.edit');
        Route::put('/admin/newsletter-templates/{hash}', [AdminNewsletterController::class, 'updateTemplate'])->name('admin.newsletters.template.update');
        Route::delete('/admin/newsletter-templates/{hash}', [AdminNewsletterController::class, 'deleteTemplate'])->name('admin.newsletters.template.delete');
        Route::post('/admin/newsletter-templates/{hash}/preview', [AdminNewsletterController::class, 'previewTemplate'])->name('admin.newsletters.template.preview');
        Route::post('/admin/newsletters/{hash}/save-as-template', [AdminNewsletterController::class, 'saveAsTemplate'])->name('admin.newsletters.save_as_template');
        Route::post('/admin/newsletters/upload-image', [AdminNewsletterController::class, 'uploadImage'])->name('admin.newsletters.upload_image');

        // Admin translation manager. Registered on every install: the sharing
        // endpoints runtime-404 on nexus and the suggestion-review endpoints
        // runtime-404 off nexus (registration-time gates would be untestable
        // because phpunit forces IS_NEXUS=true).
        Route::get('/admin/translations', [AdminTranslationController::class, 'index'])->name('admin.translations');
        Route::get('/admin/translations/data', [AdminTranslationController::class, 'data'])->name('admin.translations.data');
        Route::post('/admin/translations', [AdminTranslationController::class, 'save'])->name('admin.translations.save');
        Route::post('/admin/translations/revert', [AdminTranslationController::class, 'revert'])->name('admin.translations.revert');
        Route::get('/admin/translations/unshared', [AdminTranslationController::class, 'unshared'])->name('admin.translations.unshared');
        Route::post('/admin/translations/share', [AdminTranslationController::class, 'share'])->name('admin.translations.share');
        Route::post('/admin/translations/auto-share', [AdminTranslationController::class, 'autoShare'])->name('admin.translations.auto_share');
        Route::get('/admin/translations/suggestions', [AdminTranslationController::class, 'suggestions'])->name('admin.translations.suggestions');
        Route::get('/admin/translations/suggestions/data', [AdminTranslationController::class, 'suggestionsData'])->name('admin.translations.suggestions.data');
        Route::get('/admin/translations/suggestions/export', [AdminTranslationController::class, 'exportApproved'])->name('admin.translations.suggestions.export');
        Route::post('/admin/translations/suggestions/bulk', [AdminTranslationController::class, 'bulkSuggestions'])->name('admin.translations.suggestions.bulk');
        Route::post('/admin/translations/suggestions/{hash}/approve', [AdminTranslationController::class, 'approveSuggestion'])->name('admin.translations.suggestions.approve');
        Route::post('/admin/translations/suggestions/{hash}/reject', [AdminTranslationController::class, 'rejectSuggestion'])->name('admin.translations.suggestions.reject');

        // Admin support chat routes (hosted only)
        if (config('app.hosted')) {
            Route::get('/admin/support', [SupportChatController::class, 'adminIndex'])->name('admin.support');
            Route::get('/admin/support/conversations', [SupportChatController::class, 'adminConversations'])->name('admin.support.conversations');
            Route::get('/admin/support/{id}/messages', [SupportChatController::class, 'adminMessages'])->name('admin.support.messages');
            Route::post('/admin/support/{id}/reply', [SupportChatController::class, 'adminReply'])->name('admin.support.reply');
            Route::post('/admin/support/{id}/mark-read', [SupportChatController::class, 'adminMarkRead'])->name('admin.support.mark-read');
            Route::post('/admin/support/toggle-availability', [SupportChatController::class, 'adminToggleAvailability'])->name('admin.support.toggle-availability');
            Route::post('/admin/support/{id}/close', [SupportChatController::class, 'adminCloseConversation'])->name('admin.support.close');
        }

        // Admin blog routes
        if (config('app.hosted')) {
            Route::get('/admin/blog', [BlogController::class, 'adminIndex'])->name('blog.admin.index');
            Route::get('/admin/blog/create', [BlogController::class, 'create'])->name('blog.create');
            Route::post('/admin/blog', [BlogController::class, 'store'])->name('blog.store');
            Route::get('/admin/blog/{blog_post}/edit', [BlogController::class, 'edit'])->name('blog.edit');
            Route::put('/admin/blog/{blog_post}', [BlogController::class, 'update'])->name('blog.update');
            Route::delete('/admin/blog/{blog_post}', [BlogController::class, 'destroy'])->name('blog.destroy');
            Route::post('/admin/blog/generate-content', [BlogController::class, 'generateContent'])->name('blog.generate-content');
        }
    });
});

Route::get('/tmp/event-image/{filename?}', [AppController::class, 'tempEventImage'])->name('event.tmp_image');
Route::get('/map-image/{id}', [AppController::class, 'mapImage'])->name('map.image');

// Marketing pages - only shown on the nexus (eventschedule.com)
if (config('app.is_nexus')) {
    if (config('app.is_testing')) {
        Route::get('/', [MarketingController::class, 'index'])->name('marketing.index');
        Route::get('/features', [MarketingController::class, 'features'])->name('marketing.features');
        Route::get('/pricing', [MarketingController::class, 'pricing'])->name('marketing.pricing');
        Route::get('/about', [MarketingController::class, 'about'])->name('marketing.about');
        Route::get('/examples', [MarketingController::class, 'demos'])->name('marketing.demos');
        Route::get('/search', [MarketingController::class, 'search'])->name('marketing.search');
        Route::get('/browse', [MarketingController::class, 'browse'])->name('marketing.browse');
        Route::post('/browse/event/{hash}/toggle-discovery', [MarketingController::class, 'toggleEventDiscovery'])->name('marketing.discovery.toggle')->middleware('auth');
        Route::post('/browse/federated/{hash}/block', [MarketingController::class, 'toggleFederatedBlock'])->name('marketing.federation.block')->middleware('auth');
        // Beacon target for outbound clicks on a federated listing. The card link
        // itself stays a direct followable link to the origin, so this cannot be a
        // tracking redirect without destroying the backlink federation exists to give.
        Route::post('/browse/federated/{hash}/click', [MarketingController::class, 'federatedClick'])->name('marketing.federation.click')->middleware('throttle:120,1');
        Route::get('/faq', [MarketingController::class, 'faq'])->name('marketing.faq');
        Route::get('/why-create-account', [MarketingController::class, 'whyCreateAccount'])->name('marketing.why_create_account');
        Route::get('/features/ticketing', [MarketingController::class, 'ticketing'])->name('marketing.ticketing');
        Route::get('/features/gift-cards', [MarketingController::class, 'giftCards'])->name('marketing.gift_cards');
        Route::get('/features/allocated-seating', [MarketingController::class, 'allocatedSeating'])->name('marketing.allocated_seating');
        Route::get('/features/ai', [MarketingController::class, 'ai'])->name('marketing.ai');
        Route::get('/features/calendar-sync', [MarketingController::class, 'calendarSync'])->name('marketing.calendar_sync');
        Route::get('/google-calendar', [MarketingController::class, 'googleCalendar'])->name('marketing.google_calendar');
        Route::get('/outlook-calendar', [MarketingController::class, 'outlookCalendar'])->name('marketing.outlook_calendar');
        Route::get('/caldav', [MarketingController::class, 'caldav'])->name('marketing.caldav');
        Route::get('/stripe', [MarketingController::class, 'stripe'])->name('marketing.stripe');
        Route::get('/invoiceninja', [MarketingController::class, 'invoiceninja'])->name('marketing.invoiceninja');
        Route::get('/features/analytics', [MarketingController::class, 'analytics'])->name('marketing.analytics');
        Route::get('/features/integrations', [MarketingController::class, 'integrations'])->name('marketing.integrations');
        Route::get('/features/custom-fields', [MarketingController::class, 'customFields'])->name('marketing.custom_fields');
        Route::get('/features/custom-labels', [MarketingController::class, 'customLabels'])->name('marketing.custom_labels');
        Route::get('/features/team-scheduling', [MarketingController::class, 'teamScheduling'])->name('marketing.team_scheduling');
        Route::get('/features/sub-schedules', [MarketingController::class, 'subSchedules'])->name('marketing.sub_schedules');
        Route::get('/features/online-events', [MarketingController::class, 'onlineEvents'])->name('marketing.online_events');
        Route::get('/open-source', [MarketingController::class, 'openSource'])->name('marketing.open_source');
        Route::get('/features/newsletters', [MarketingController::class, 'newsletters'])->name('marketing.newsletters');
        Route::get('/features/recurring-events', [MarketingController::class, 'recurringEvents'])->name('marketing.recurring_events');
        Route::get('/features/embed-calendar', [MarketingController::class, 'embedCalendar'])->name('marketing.embed_calendar');
        Route::get('/features/embed-tickets', [MarketingController::class, 'embedTickets'])->name('marketing.embed_tickets');
        Route::get('/features/fan-videos', [MarketingController::class, 'fanVideos'])->name('marketing.fan_videos');
        Route::get('/features/polls', [MarketingController::class, 'polls'])->name('marketing.polls');
        Route::get('/features/boost', [MarketingController::class, 'boost'])->name('marketing.boost');
        Route::get('/features/private-events', [MarketingController::class, 'privateEvents'])->name('marketing.private_events');
        Route::get('/features/event-graphics', [MarketingController::class, 'eventGraphics'])->name('marketing.event_graphics');
        Route::get('/features/white-label', [MarketingController::class, 'whiteLabel'])->name('marketing.white_label');
        Route::get('/features/custom-css', [MarketingController::class, 'customCss'])->name('marketing.custom_css');
        Route::get('/features/custom-domain', [MarketingController::class, 'customDomain'])->name('marketing.custom_domain');
        Route::get('/features/feedback', [MarketingController::class, 'feedback'])->name('marketing.feedback');
        Route::get('/features/availability', [MarketingController::class, 'availability'])->name('marketing.availability');
        Route::get('/features/appointments', [MarketingController::class, 'appointments'])->name('marketing.appointments');
        Route::get('/features/carpool', [MarketingController::class, 'carpool'])->name('marketing.carpool');
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
        Route::get('/embed-tickets', fn () => redirect()->route('marketing.embed_tickets', [], 301));
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
        Route::get('/meetup-alternative', [MarketingController::class, 'compareMeetup'])->name('marketing.compare_meetup');
        Route::get('/dice-alternative', [MarketingController::class, 'compareDice'])->name('marketing.compare_dice');
        Route::get('/brown-paper-tickets-alternative', [MarketingController::class, 'compareBrownPaperTickets'])->name('marketing.compare_brown_paper_tickets');
        Route::get('/splash-alternative', [MarketingController::class, 'compareSplash'])->name('marketing.compare_splash');
        Route::get('/sched-alternative', [MarketingController::class, 'compareSched'])->name('marketing.compare_sched');
        Route::get('/whova-alternative', [MarketingController::class, 'compareWhova'])->name('marketing.compare_whova');
        Route::get('/accelevents-alternative', [MarketingController::class, 'compareAccelevents'])->name('marketing.compare_accelevents');
        Route::get('/tito-alternative', [MarketingController::class, 'compareTito'])->name('marketing.compare_tito');
        Route::get('/addevent-alternative', [MarketingController::class, 'compareAddEvent'])->name('marketing.compare_addevent');
        Route::get('/pretix-alternative', [MarketingController::class, 'comparePretix'])->name('marketing.compare_pretix');
        Route::get('/humanitix-alternative', [MarketingController::class, 'compareHumanitix'])->name('marketing.compare_humanitix');
        Route::get('/eventzilla-alternative', [MarketingController::class, 'compareEventzilla'])->name('marketing.compare_eventzilla');
        Route::get('/replace', [MarketingController::class, 'replace'])->name('marketing.replace');
        Route::get('/google-forms-replacement', [MarketingController::class, 'replaceGoogleForms'])->name('marketing.replace_google_forms');
        Route::get('/mailchimp-replacement', [MarketingController::class, 'replaceMailchimp'])->name('marketing.replace_mailchimp');
        Route::get('/canva-replacement', [MarketingController::class, 'replaceCanva'])->name('marketing.replace_canva');
        Route::get('/linktree-replacement', [MarketingController::class, 'replaceLinktree'])->name('marketing.replace_linktree');
        Route::get('/google-sheets-replacement', [MarketingController::class, 'replaceGoogleSheets'])->name('marketing.replace_google_sheets');
        Route::get('/calendly-replacement', [MarketingController::class, 'replaceCalendly'])->name('marketing.replace_calendly');
        Route::get('/surveymonkey-replacement', [MarketingController::class, 'replaceSurveymonkey'])->name('marketing.replace_surveymonkey');
        Route::get('/doodle-replacement', [MarketingController::class, 'replaceDoodle'])->name('marketing.replace_doodle');
        Route::get('/qr-code-generator-replacement', [MarketingController::class, 'replaceQrCodeGenerators'])->name('marketing.replace_qr_code_generators');
        Route::get('/squarespace-replacement', [MarketingController::class, 'replaceSquarespace'])->name('marketing.replace_squarespace');
        Route::get('/notion-replacement', [MarketingController::class, 'replaceNotion'])->name('marketing.replace_notion');
        Route::get('/trello-replacement', [MarketingController::class, 'replaceTrello'])->name('marketing.replace_trello');
        Route::get('/convertkit-replacement', fn () => redirect()->route('marketing.replace_mailchimp', [], 301));
        Route::get('/excel-replacement', fn () => redirect()->route('marketing.replace_google_sheets', [], 301));
        Route::get('/wix-replacement', fn () => redirect()->route('marketing.replace_squarespace', [], 301));
        Route::get('/asana-replacement', fn () => redirect()->route('marketing.replace_trello', [], 301));
        Route::get('/airtable-replacement', fn () => redirect()->route('marketing.replace_google_sheets', [], 301));
        Route::get('/wordpress-replacement', fn () => redirect()->route('marketing.replace_squarespace', [], 301));
        Route::get('/google-forms-alternative', fn () => redirect()->route('marketing.replace_google_forms', [], 301));
        Route::get('/mailchimp-alternative', fn () => redirect()->route('marketing.replace_mailchimp', [], 301));
        Route::get('/canva-alternative', fn () => redirect()->route('marketing.replace_canva', [], 301));
        Route::get('/linktree-alternative', fn () => redirect()->route('marketing.replace_linktree', [], 301));
        Route::get('/google-sheets-alternative', fn () => redirect()->route('marketing.replace_google_sheets', [], 301));
        Route::get('/calendly-alternative', fn () => redirect()->route('marketing.replace_calendly', [], 301));
        Route::get('/surveymonkey-alternative', fn () => redirect()->route('marketing.replace_surveymonkey', [], 301));
        Route::get('/doodle-alternative', fn () => redirect()->route('marketing.replace_doodle', [], 301));
        Route::get('/qr-code-generator-alternative', fn () => redirect()->route('marketing.replace_qr_code_generators', [], 301));
        Route::get('/squarespace-alternative', fn () => redirect()->route('marketing.replace_squarespace', [], 301));
        Route::get('/notion-alternative', fn () => redirect()->route('marketing.replace_notion', [], 301));
        Route::get('/trello-alternative', fn () => redirect()->route('marketing.replace_trello', [], 301));
        Route::get('/contact', [MarketingController::class, 'contact'])->name('marketing.contact');
        // The legal documents go through LegalController because an operator can replace
        // any of them from /admin/legal; it falls back to the bundled marketing page.
        Route::get('/privacy', [LegalController::class, 'show'])->defaults('type', 'privacy')->name('marketing.privacy');
        Route::get('/accessibility', [MarketingController::class, 'accessibility'])->name('marketing.accessibility');
        Route::get('/terms-of-service', [LegalController::class, 'show'])->defaults('type', 'terms')->name('marketing.terms');
        Route::get('/cookie-policy', [LegalController::class, 'show'])->defaults('type', 'cookies')->name('marketing.cookie_policy');
        Route::get('/self-hosting-terms-of-service', [MarketingController::class, 'selfHostingTerms'])->name('marketing.self_hosting_terms');
        Route::get('/selfhost', [MarketingController::class, 'selfHost'])->name('marketing.selfhost');
        Route::get('/self-host-event-schedule', fn () => redirect()->route('marketing.selfhost', [], 301));
        Route::get('/self-host-event-schedule/', fn () => redirect()->route('marketing.selfhost', [], 301));
        Route::get('/saas', [MarketingController::class, 'saas'])->name('marketing.saas');
        Route::get('/docs', [MarketingController::class, 'docsIndex'])->name('marketing.docs');
        // Fetched lazily by the docs search on first focus. Kept under /docs/ so the
        // {subdomain} negative lookaheads already exclude it.
        Route::get('/docs/search-index.json', [MarketingController::class, 'docsSearchIndex'])->name('marketing.docs.search_index');
        // User Guide (at root level)
        Route::get('/docs/getting-started', [MarketingController::class, 'docsGettingStarted'])->name('marketing.docs.getting_started');
        Route::get('/docs/creating-schedules', [MarketingController::class, 'docsCreatingSchedules'])->name('marketing.docs.creating_schedules');
        Route::redirect('/docs/schedule-basics', '/docs/creating-schedules', 301)->name('marketing.docs.schedule_basics');
        Route::get('/docs/schedule-styling', [MarketingController::class, 'docsScheduleStyling'])->name('marketing.docs.schedule_styling');
        Route::get('/docs/managing-schedules', [MarketingController::class, 'docsManagingSchedules'])->name('marketing.docs.managing_schedules');
        Route::get('/docs/creating-events', [MarketingController::class, 'docsCreatingEvents'])->name('marketing.docs.creating_events');
        Route::get('/docs/sharing', [MarketingController::class, 'docsSharing'])->name('marketing.docs.sharing');
        Route::get('/docs/tickets', [MarketingController::class, 'docsTickets'])->name('marketing.docs.tickets');
        Route::get('/docs/subscriptions', [MarketingController::class, 'docsSubscriptions'])->name('marketing.docs.subscriptions');
        Route::get('/docs/gift-cards', [MarketingController::class, 'docsGiftCards'])->name('marketing.docs.gift_cards');
        Route::get('/docs/allocated-seating', [MarketingController::class, 'docsAllocatedSeating'])->name('marketing.docs.allocated_seating');
        Route::get('/docs/appointments', [MarketingController::class, 'docsAppointments'])->name('marketing.docs.appointments');
        Route::get('/docs/event-graphics', [MarketingController::class, 'docsEventGraphics'])->name('marketing.docs.event_graphics');
        Route::get('/docs/newsletters', [MarketingController::class, 'docsNewsletters'])->name('marketing.docs.newsletters');
        Route::get('/docs/analytics', [MarketingController::class, 'docsAnalytics'])->name('marketing.docs.analytics');
        Route::get('/docs/account-settings', [MarketingController::class, 'docsAccountSettings'])->name('marketing.docs.account_settings');
        Route::redirect('/docs/availability', '/docs/managing-schedules#availability', 301)->name('marketing.docs.availability');
        Route::get('/docs/boost', [MarketingController::class, 'docsBoost'])->name('marketing.docs.boost');
        Route::get('/docs/ai-import', [MarketingController::class, 'docsAiImport'])->name('marketing.docs.ai_import');
        Route::get('/docs/scan-agenda', [MarketingController::class, 'docsScanAgenda'])->name('marketing.docs.scan_agenda');
        Route::get('/docs/referral-program', [MarketingController::class, 'docsReferralProgram'])->name('marketing.docs.referral_program');
        Route::get('/docs/fan-content', fn () => redirect('/docs/creating-events#fan-content', 301))->name('marketing.docs.fan_content');
        Route::get('/docs/polls', fn () => redirect('/docs/creating-events#polls', 301))->name('marketing.docs.polls');
        // Selfhost section
        Route::get('/docs/selfhost', [MarketingController::class, 'docsSelfhostIndex'])->name('marketing.docs.selfhost');
        Route::get('/docs/selfhost/installation', [MarketingController::class, 'docsSelfhostInstallation'])->name('marketing.docs.selfhost.installation');
        Route::get('/docs/selfhost/stripe', [MarketingController::class, 'docsSelfhostStripe'])->name('marketing.docs.selfhost.stripe');
        Route::get('/docs/selfhost/google-calendar', [MarketingController::class, 'docsSelfhostGoogleCalendar'])->name('marketing.docs.selfhost.google_calendar');
        Route::get('/docs/selfhost/microsoft-calendar', [MarketingController::class, 'docsSelfhostMicrosoftCalendar'])->name('marketing.docs.selfhost.microsoft_calendar');
        Route::get('/docs/selfhost/boost', [MarketingController::class, 'docsSelfhostBoost'])->name('marketing.docs.selfhost.boost');
        Route::get('/docs/selfhost/admin', [MarketingController::class, 'docsSelfhostAdmin'])->name('marketing.docs.selfhost.admin');
        Route::get('/docs/selfhost/email', [MarketingController::class, 'docsSelfhostEmail'])->name('marketing.docs.selfhost.email');
        Route::get('/docs/selfhost/ai', [MarketingController::class, 'docsSelfhostAi'])->name('marketing.docs.selfhost.ai');
        Route::get('/docs/selfhost/accessibility', [MarketingController::class, 'docsSelfhostAccessibility'])->name('marketing.docs.selfhost.accessibility');
        // SaaS section
        Route::get('/docs/saas', [MarketingController::class, 'docsSaasSetup'])->name('marketing.docs.saas.setup');
        Route::get('/docs/saas/custom-domains', [MarketingController::class, 'docsSaasCustomDomains'])->name('marketing.docs.saas.custom_domains');
        Route::get('/docs/saas/twilio', [MarketingController::class, 'docsSaasTwilio'])->name('marketing.docs.saas.twilio');
        Route::get('/docs/saas/federation', [MarketingController::class, 'docsSaasFederation'])->name('marketing.docs.saas.federation');
        Route::get('/docs/saas/monetization', [MarketingController::class, 'docsSaasMonetization'])->name('marketing.docs.saas.monetization');
        Route::get('/docs/selfhost/federation', [MarketingController::class, 'docsSelfhostFederation'])->name('marketing.docs.selfhost.federation');
        // Developer section
        Route::get('/docs/developer/api', [MarketingController::class, 'docsDeveloperApi'])->name('marketing.docs.developer.api');
        Route::get('/docs/developer/webhooks', [MarketingController::class, 'docsDeveloperWebhooks'])->name('marketing.docs.developer.webhooks');
        // Redirects from old URLs to new URLs
        Route::get('/docs/installation', fn () => redirect()->route('marketing.docs.selfhost.installation', [], 301));
        Route::get('/docs/saas/setup', fn () => redirect()->route('marketing.docs.saas.setup', [], 301));
        Route::get('/docs/selfhost/saas', fn () => redirect()->route('marketing.docs.saas.setup', [], 301));
        Route::get('/docs/stripe', fn () => redirect()->route('marketing.docs.selfhost.stripe', [], 301));
        Route::get('/docs/google-calendar', fn () => redirect()->route('marketing.docs.selfhost.google_calendar', [], 301));
        Route::get('/docs/api', fn () => redirect()->route('marketing.docs.developer.api', [], 301));
        Route::get('/docs/developer', fn () => redirect()->route('marketing.docs.developer.api', [], 301));
    } else {
        // Nexus mode: show marketing pages at root URLs on the base domain
        // (_base_domain() resolves to eventschedule.com on the real instance, and to the
        // operator's own domain on a white-label nexus install).
        Route::domain(_base_domain())->group(function () {
            Route::get('/', [MarketingController::class, 'index'])->name('marketing.index');
            Route::get('/features', [MarketingController::class, 'features'])->name('marketing.features');
            Route::get('/pricing', [MarketingController::class, 'pricing'])->name('marketing.pricing');
            Route::get('/about', [MarketingController::class, 'about'])->name('marketing.about');
            Route::get('/examples', [MarketingController::class, 'demos'])->name('marketing.demos');
            Route::get('/search', [MarketingController::class, 'search'])->name('marketing.search');
            Route::get('/browse', [MarketingController::class, 'browse'])->name('marketing.browse');
            Route::post('/browse/event/{hash}/toggle-discovery', [MarketingController::class, 'toggleEventDiscovery'])->name('marketing.discovery.toggle')->middleware('auth');
            Route::post('/browse/federated/{hash}/block', [MarketingController::class, 'toggleFederatedBlock'])->name('marketing.federation.block')->middleware('auth');
            // Beacon target for outbound clicks on a federated listing. The card link
            // itself stays a direct followable link to the origin, so this cannot be a
            // tracking redirect without destroying the backlink federation exists to give.
            Route::post('/browse/federated/{hash}/click', [MarketingController::class, 'federatedClick'])->name('marketing.federation.click')->middleware('throttle:120,1');
            Route::get('/faq', [MarketingController::class, 'faq'])->name('marketing.faq');
            Route::get('/why-create-account', [MarketingController::class, 'whyCreateAccount'])->name('marketing.why_create_account');
            Route::get('/features/ticketing', [MarketingController::class, 'ticketing'])->name('marketing.ticketing');
            Route::get('/features/gift-cards', [MarketingController::class, 'giftCards'])->name('marketing.gift_cards');
            Route::get('/features/allocated-seating', [MarketingController::class, 'allocatedSeating'])->name('marketing.allocated_seating');
            Route::get('/features/ai', [MarketingController::class, 'ai'])->name('marketing.ai');
            Route::get('/features/calendar-sync', [MarketingController::class, 'calendarSync'])->name('marketing.calendar_sync');
            Route::get('/google-calendar', [MarketingController::class, 'googleCalendar'])->name('marketing.google_calendar');
            Route::get('/outlook-calendar', [MarketingController::class, 'outlookCalendar'])->name('marketing.outlook_calendar');
            Route::get('/caldav', [MarketingController::class, 'caldav'])->name('marketing.caldav');
            Route::get('/stripe', [MarketingController::class, 'stripe'])->name('marketing.stripe');
            Route::get('/invoiceninja', [MarketingController::class, 'invoiceninja'])->name('marketing.invoiceninja');
            Route::get('/features/analytics', [MarketingController::class, 'analytics'])->name('marketing.analytics');
            Route::get('/features/integrations', [MarketingController::class, 'integrations'])->name('marketing.integrations');
            Route::get('/features/custom-fields', [MarketingController::class, 'customFields'])->name('marketing.custom_fields');
            Route::get('/features/custom-labels', [MarketingController::class, 'customLabels'])->name('marketing.custom_labels');
            Route::get('/features/team-scheduling', [MarketingController::class, 'teamScheduling'])->name('marketing.team_scheduling');
            Route::get('/features/sub-schedules', [MarketingController::class, 'subSchedules'])->name('marketing.sub_schedules');
            Route::get('/features/online-events', [MarketingController::class, 'onlineEvents'])->name('marketing.online_events');
            Route::get('/open-source', [MarketingController::class, 'openSource'])->name('marketing.open_source');
            Route::get('/features/newsletters', [MarketingController::class, 'newsletters'])->name('marketing.newsletters');
            Route::get('/features/recurring-events', [MarketingController::class, 'recurringEvents'])->name('marketing.recurring_events');
            Route::get('/features/embed-calendar', [MarketingController::class, 'embedCalendar'])->name('marketing.embed_calendar');
            Route::get('/features/embed-tickets', [MarketingController::class, 'embedTickets'])->name('marketing.embed_tickets');
            Route::get('/features/fan-videos', [MarketingController::class, 'fanVideos'])->name('marketing.fan_videos');
            Route::get('/features/polls', [MarketingController::class, 'polls'])->name('marketing.polls');
            Route::get('/features/boost', [MarketingController::class, 'boost'])->name('marketing.boost');
            Route::get('/features/private-events', [MarketingController::class, 'privateEvents'])->name('marketing.private_events');
            Route::get('/features/event-graphics', [MarketingController::class, 'eventGraphics'])->name('marketing.event_graphics');
            Route::get('/features/white-label', [MarketingController::class, 'whiteLabel'])->name('marketing.white_label');
            Route::get('/features/custom-css', [MarketingController::class, 'customCss'])->name('marketing.custom_css');
            Route::get('/features/custom-domain', [MarketingController::class, 'customDomain'])->name('marketing.custom_domain');
            Route::get('/features/feedback', [MarketingController::class, 'feedback'])->name('marketing.feedback');
            Route::get('/features/availability', [MarketingController::class, 'availability'])->name('marketing.availability');
            Route::get('/features/appointments', [MarketingController::class, 'appointments'])->name('marketing.appointments');
            Route::get('/features/carpool', [MarketingController::class, 'carpool'])->name('marketing.carpool');
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
            Route::get('/embed-tickets', fn () => redirect()->route('marketing.embed_tickets', [], 301));
            Route::get('/wp/analytics', fn () => redirect()->route('marketing.analytics', [], 301));
            Route::get('/wp/newsletters', fn () => redirect()->route('marketing.newsletters', [], 301));
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
            Route::get('/meetup-alternative', [MarketingController::class, 'compareMeetup'])->name('marketing.compare_meetup');
            Route::get('/dice-alternative', [MarketingController::class, 'compareDice'])->name('marketing.compare_dice');
            Route::get('/brown-paper-tickets-alternative', [MarketingController::class, 'compareBrownPaperTickets'])->name('marketing.compare_brown_paper_tickets');
            Route::get('/splash-alternative', [MarketingController::class, 'compareSplash'])->name('marketing.compare_splash');
            Route::get('/sched-alternative', [MarketingController::class, 'compareSched'])->name('marketing.compare_sched');
            Route::get('/whova-alternative', [MarketingController::class, 'compareWhova'])->name('marketing.compare_whova');
            Route::get('/accelevents-alternative', [MarketingController::class, 'compareAccelevents'])->name('marketing.compare_accelevents');
            Route::get('/tito-alternative', [MarketingController::class, 'compareTito'])->name('marketing.compare_tito');
            Route::get('/addevent-alternative', [MarketingController::class, 'compareAddEvent'])->name('marketing.compare_addevent');
            Route::get('/pretix-alternative', [MarketingController::class, 'comparePretix'])->name('marketing.compare_pretix');
            Route::get('/humanitix-alternative', [MarketingController::class, 'compareHumanitix'])->name('marketing.compare_humanitix');
            Route::get('/eventzilla-alternative', [MarketingController::class, 'compareEventzilla'])->name('marketing.compare_eventzilla');
            Route::get('/replace', [MarketingController::class, 'replace'])->name('marketing.replace');
            Route::get('/google-forms-replacement', [MarketingController::class, 'replaceGoogleForms'])->name('marketing.replace_google_forms');
            Route::get('/mailchimp-replacement', [MarketingController::class, 'replaceMailchimp'])->name('marketing.replace_mailchimp');
            Route::get('/canva-replacement', [MarketingController::class, 'replaceCanva'])->name('marketing.replace_canva');
            Route::get('/linktree-replacement', [MarketingController::class, 'replaceLinktree'])->name('marketing.replace_linktree');
            Route::get('/google-sheets-replacement', [MarketingController::class, 'replaceGoogleSheets'])->name('marketing.replace_google_sheets');
            Route::get('/calendly-replacement', [MarketingController::class, 'replaceCalendly'])->name('marketing.replace_calendly');
            Route::get('/surveymonkey-replacement', [MarketingController::class, 'replaceSurveymonkey'])->name('marketing.replace_surveymonkey');
            Route::get('/doodle-replacement', [MarketingController::class, 'replaceDoodle'])->name('marketing.replace_doodle');
            Route::get('/qr-code-generator-replacement', [MarketingController::class, 'replaceQrCodeGenerators'])->name('marketing.replace_qr_code_generators');
            Route::get('/squarespace-replacement', [MarketingController::class, 'replaceSquarespace'])->name('marketing.replace_squarespace');
            Route::get('/notion-replacement', [MarketingController::class, 'replaceNotion'])->name('marketing.replace_notion');
            Route::get('/trello-replacement', [MarketingController::class, 'replaceTrello'])->name('marketing.replace_trello');
            Route::get('/convertkit-replacement', fn () => redirect()->route('marketing.replace_mailchimp', [], 301));
            Route::get('/excel-replacement', fn () => redirect()->route('marketing.replace_google_sheets', [], 301));
            Route::get('/wix-replacement', fn () => redirect()->route('marketing.replace_squarespace', [], 301));
            Route::get('/asana-replacement', fn () => redirect()->route('marketing.replace_trello', [], 301));
            Route::get('/airtable-replacement', fn () => redirect()->route('marketing.replace_google_sheets', [], 301));
            Route::get('/wordpress-replacement', fn () => redirect()->route('marketing.replace_squarespace', [], 301));
            Route::get('/google-forms-alternative', fn () => redirect()->route('marketing.replace_google_forms', [], 301));
            Route::get('/mailchimp-alternative', fn () => redirect()->route('marketing.replace_mailchimp', [], 301));
            Route::get('/canva-alternative', fn () => redirect()->route('marketing.replace_canva', [], 301));
            Route::get('/linktree-alternative', fn () => redirect()->route('marketing.replace_linktree', [], 301));
            Route::get('/google-sheets-alternative', fn () => redirect()->route('marketing.replace_google_sheets', [], 301));
            Route::get('/calendly-alternative', fn () => redirect()->route('marketing.replace_calendly', [], 301));
            Route::get('/surveymonkey-alternative', fn () => redirect()->route('marketing.replace_surveymonkey', [], 301));
            Route::get('/doodle-alternative', fn () => redirect()->route('marketing.replace_doodle', [], 301));
            Route::get('/qr-code-generator-alternative', fn () => redirect()->route('marketing.replace_qr_code_generators', [], 301));
            Route::get('/squarespace-alternative', fn () => redirect()->route('marketing.replace_squarespace', [], 301));
            Route::get('/notion-alternative', fn () => redirect()->route('marketing.replace_notion', [], 301));
            Route::get('/trello-alternative', fn () => redirect()->route('marketing.replace_trello', [], 301));
            Route::get('/contact', [MarketingController::class, 'contact'])->name('marketing.contact');
            // The legal documents go through LegalController because an operator can replace
            // any of them from /admin/legal; it falls back to the bundled marketing page.
            Route::get('/privacy', [LegalController::class, 'show'])->defaults('type', 'privacy')->name('marketing.privacy');
            Route::get('/accessibility', [MarketingController::class, 'accessibility'])->name('marketing.accessibility');
            Route::get('/terms-of-service', [LegalController::class, 'show'])->defaults('type', 'terms')->name('marketing.terms');
            Route::get('/cookie-policy', [LegalController::class, 'show'])->defaults('type', 'cookies')->name('marketing.cookie_policy');
            Route::get('/self-hosting-terms-of-service', [MarketingController::class, 'selfHostingTerms'])->name('marketing.self_hosting_terms');
            Route::get('/selfhost', [MarketingController::class, 'selfHost'])->name('marketing.selfhost');
            Route::get('/self-host-event-schedule', fn () => redirect()->route('marketing.selfhost', [], 301));
            Route::get('/self-host-event-schedule/', fn () => redirect()->route('marketing.selfhost', [], 301));
            Route::get('/saas', [MarketingController::class, 'saas'])->name('marketing.saas');
            Route::get('/docs', [MarketingController::class, 'docsIndex'])->name('marketing.docs');
            // Fetched lazily by the docs search on first focus. Kept under /docs/ so the
            // {subdomain} negative lookaheads already exclude it.
            Route::get('/docs/search-index.json', [MarketingController::class, 'docsSearchIndex'])->name('marketing.docs.search_index');
            // User Guide (at root level)
            Route::get('/docs/getting-started', [MarketingController::class, 'docsGettingStarted'])->name('marketing.docs.getting_started');
            Route::get('/docs/creating-schedules', [MarketingController::class, 'docsCreatingSchedules'])->name('marketing.docs.creating_schedules');
            Route::redirect('/docs/schedule-basics', '/docs/creating-schedules', 301)->name('marketing.docs.schedule_basics');
            Route::get('/docs/schedule-styling', [MarketingController::class, 'docsScheduleStyling'])->name('marketing.docs.schedule_styling');
            Route::get('/docs/managing-schedules', [MarketingController::class, 'docsManagingSchedules'])->name('marketing.docs.managing_schedules');
            Route::get('/docs/creating-events', [MarketingController::class, 'docsCreatingEvents'])->name('marketing.docs.creating_events');
            Route::get('/docs/sharing', [MarketingController::class, 'docsSharing'])->name('marketing.docs.sharing');
            Route::get('/docs/tickets', [MarketingController::class, 'docsTickets'])->name('marketing.docs.tickets');
            Route::get('/docs/subscriptions', [MarketingController::class, 'docsSubscriptions'])->name('marketing.docs.subscriptions');
            Route::get('/docs/gift-cards', [MarketingController::class, 'docsGiftCards'])->name('marketing.docs.gift_cards');
            Route::get('/docs/allocated-seating', [MarketingController::class, 'docsAllocatedSeating'])->name('marketing.docs.allocated_seating');
            Route::get('/docs/appointments', [MarketingController::class, 'docsAppointments'])->name('marketing.docs.appointments');
            Route::get('/docs/event-graphics', [MarketingController::class, 'docsEventGraphics'])->name('marketing.docs.event_graphics');
            Route::get('/docs/newsletters', [MarketingController::class, 'docsNewsletters'])->name('marketing.docs.newsletters');
            Route::get('/docs/analytics', [MarketingController::class, 'docsAnalytics'])->name('marketing.docs.analytics');
            Route::get('/docs/account-settings', [MarketingController::class, 'docsAccountSettings'])->name('marketing.docs.account_settings');
            Route::redirect('/docs/availability', '/docs/managing-schedules#availability', 301)->name('marketing.docs.availability');
            Route::get('/docs/boost', [MarketingController::class, 'docsBoost'])->name('marketing.docs.boost');
            Route::get('/docs/ai-import', [MarketingController::class, 'docsAiImport'])->name('marketing.docs.ai_import');
            Route::get('/docs/scan-agenda', [MarketingController::class, 'docsScanAgenda'])->name('marketing.docs.scan_agenda');
            Route::get('/docs/referral-program', [MarketingController::class, 'docsReferralProgram'])->name('marketing.docs.referral_program');
            Route::get('/docs/fan-content', fn () => redirect('/docs/creating-events#fan-content', 301))->name('marketing.docs.fan_content');
            Route::get('/docs/polls', fn () => redirect('/docs/creating-events#polls', 301))->name('marketing.docs.polls');
            // Selfhost section
            Route::get('/docs/selfhost', [MarketingController::class, 'docsSelfhostIndex'])->name('marketing.docs.selfhost');
            Route::get('/docs/selfhost/installation', [MarketingController::class, 'docsSelfhostInstallation'])->name('marketing.docs.selfhost.installation');
            Route::get('/docs/selfhost/stripe', [MarketingController::class, 'docsSelfhostStripe'])->name('marketing.docs.selfhost.stripe');
            Route::get('/docs/selfhost/google-calendar', [MarketingController::class, 'docsSelfhostGoogleCalendar'])->name('marketing.docs.selfhost.google_calendar');
            Route::get('/docs/selfhost/microsoft-calendar', [MarketingController::class, 'docsSelfhostMicrosoftCalendar'])->name('marketing.docs.selfhost.microsoft_calendar');
            Route::get('/docs/selfhost/boost', [MarketingController::class, 'docsSelfhostBoost'])->name('marketing.docs.selfhost.boost');
            Route::get('/docs/selfhost/admin', [MarketingController::class, 'docsSelfhostAdmin'])->name('marketing.docs.selfhost.admin');
            Route::get('/docs/selfhost/email', [MarketingController::class, 'docsSelfhostEmail'])->name('marketing.docs.selfhost.email');
            Route::get('/docs/selfhost/ai', [MarketingController::class, 'docsSelfhostAi'])->name('marketing.docs.selfhost.ai');
            Route::get('/docs/selfhost/accessibility', [MarketingController::class, 'docsSelfhostAccessibility'])->name('marketing.docs.selfhost.accessibility');
            // SaaS section
            Route::get('/docs/saas', [MarketingController::class, 'docsSaasSetup'])->name('marketing.docs.saas.setup');
            Route::get('/docs/saas/custom-domains', [MarketingController::class, 'docsSaasCustomDomains'])->name('marketing.docs.saas.custom_domains');
            Route::get('/docs/saas/twilio', [MarketingController::class, 'docsSaasTwilio'])->name('marketing.docs.saas.twilio');
            Route::get('/docs/saas/federation', [MarketingController::class, 'docsSaasFederation'])->name('marketing.docs.saas.federation');
            Route::get('/docs/saas/monetization', [MarketingController::class, 'docsSaasMonetization'])->name('marketing.docs.saas.monetization');
            Route::get('/docs/selfhost/federation', [MarketingController::class, 'docsSelfhostFederation'])->name('marketing.docs.selfhost.federation');
            // Developer section
            Route::get('/docs/developer/api', [MarketingController::class, 'docsDeveloperApi'])->name('marketing.docs.developer.api');
            Route::get('/docs/developer/webhooks', [MarketingController::class, 'docsDeveloperWebhooks'])->name('marketing.docs.developer.webhooks');
            // Redirects from old URLs to new URLs
            Route::get('/docs/installation', fn () => redirect()->route('marketing.docs.selfhost.installation', [], 301));
            Route::get('/docs/saas/setup', fn () => redirect()->route('marketing.docs.saas.setup', [], 301));
            Route::get('/docs/selfhost/saas', fn () => redirect()->route('marketing.docs.saas.setup', [], 301));
            Route::get('/docs/stripe', fn () => redirect()->route('marketing.docs.selfhost.stripe', [], 301));
            Route::get('/docs/google-calendar', fn () => redirect()->route('marketing.docs.selfhost.google_calendar', [], 301));
            Route::get('/docs/api', fn () => redirect()->route('marketing.docs.developer.api', [], 301));
            Route::get('/docs/developer', fn () => redirect()->route('marketing.docs.developer.api', [], 301));
        });

        // Redirect www marketing pages to the non-www base domain
        Route::domain('www.'._base_domain())->group(function () {
            Route::get('/', fn () => redirect('https://'._base_domain().'/', 301));
            Route::get('/features', fn () => redirect('https://'._base_domain().'/features', 301));
            Route::get('/pricing', fn () => redirect('https://'._base_domain().'/pricing', 301));
            Route::get('/about', fn () => redirect('https://'._base_domain().'/about', 301));
            Route::get('/demos', fn () => redirect('https://'._base_domain().'/examples', 301));
            Route::get('/examples', fn () => redirect('https://'._base_domain().'/examples', 301));
            Route::get('/search', fn () => redirect('https://'._base_domain().'/search', 301));
            Route::get('/browse', fn () => redirect('https://'._base_domain().'/browse', 301));
            Route::get('/faq', fn () => redirect('https://'._base_domain().'/faq', 301));
            Route::get('/ticketing', fn () => redirect('https://'._base_domain().'/features/ticketing', 301));
            Route::get('/ai', fn () => redirect('https://'._base_domain().'/features/ai', 301));
            Route::get('/calendar-sync', fn () => redirect('https://'._base_domain().'/features/calendar-sync', 301));
            Route::get('/google-calendar', fn () => redirect('https://'._base_domain().'/google-calendar', 301));
            Route::get('/outlook-calendar', fn () => redirect('https://'._base_domain().'/outlook-calendar', 301));
            Route::get('/caldav', fn () => redirect('https://'._base_domain().'/caldav', 301));
            Route::get('/stripe', fn () => redirect('https://'._base_domain().'/stripe', 301));
            Route::get('/invoiceninja', fn () => redirect('https://'._base_domain().'/invoiceninja', 301));
            Route::get('/analytics', fn () => redirect('https://'._base_domain().'/features/analytics', 301));
            Route::get('/integrations', fn () => redirect('https://'._base_domain().'/features/integrations', 301));
            Route::get('/custom-fields', fn () => redirect('https://'._base_domain().'/features/custom-fields', 301));
            Route::get('/team-scheduling', fn () => redirect('https://'._base_domain().'/features/team-scheduling', 301));
            Route::get('/sub-schedules', fn () => redirect('https://'._base_domain().'/features/sub-schedules', 301));
            Route::get('/online-events', fn () => redirect('https://'._base_domain().'/features/online-events', 301));
            Route::get('/open-source', fn () => redirect('https://'._base_domain().'/open-source', 301));
            Route::get('/newsletters', fn () => redirect('https://'._base_domain().'/features/newsletters', 301));
            Route::get('/recurring-events', fn () => redirect('https://'._base_domain().'/features/recurring-events', 301));
            Route::get('/embed-calendar', fn () => redirect('https://'._base_domain().'/features/embed-calendar', 301));
            Route::get('/embed-tickets', fn () => redirect('https://'._base_domain().'/features/embed-tickets', 301));
            Route::get('/features/ticketing', fn () => redirect('https://'._base_domain().'/features/ticketing', 301));
            Route::get('/features/ai', fn () => redirect('https://'._base_domain().'/features/ai', 301));
            Route::get('/features/calendar-sync', fn () => redirect('https://'._base_domain().'/features/calendar-sync', 301));
            Route::get('/features/analytics', fn () => redirect('https://'._base_domain().'/features/analytics', 301));
            Route::get('/features/integrations', fn () => redirect('https://'._base_domain().'/features/integrations', 301));
            Route::get('/features/custom-fields', fn () => redirect('https://'._base_domain().'/features/custom-fields', 301));
            Route::get('/features/custom-labels', fn () => redirect('https://'._base_domain().'/features/custom-labels', 301));
            Route::get('/features/team-scheduling', fn () => redirect('https://'._base_domain().'/features/team-scheduling', 301));
            Route::get('/features/sub-schedules', fn () => redirect('https://'._base_domain().'/features/sub-schedules', 301));
            Route::get('/features/online-events', fn () => redirect('https://'._base_domain().'/features/online-events', 301));
            Route::get('/features/newsletters', fn () => redirect('https://'._base_domain().'/features/newsletters', 301));
            Route::get('/features/recurring-events', fn () => redirect('https://'._base_domain().'/features/recurring-events', 301));
            Route::get('/features/embed-calendar', fn () => redirect('https://'._base_domain().'/features/embed-calendar', 301));
            Route::get('/features/embed-tickets', fn () => redirect('https://'._base_domain().'/features/embed-tickets', 301));
            Route::get('/features/fan-videos', fn () => redirect('https://'._base_domain().'/features/fan-videos', 301));
            Route::get('/features/polls', fn () => redirect('https://'._base_domain().'/features/polls', 301));
            Route::get('/features/boost', fn () => redirect('https://'._base_domain().'/features/boost', 301));
            Route::get('/features/private-events', fn () => redirect('https://'._base_domain().'/features/private-events', 301));
            Route::get('/features/event-graphics', fn () => redirect('https://'._base_domain().'/features/event-graphics', 301));
            Route::get('/features/white-label', fn () => redirect('https://'._base_domain().'/features/white-label', 301));
            Route::get('/features/custom-css', fn () => redirect('https://'._base_domain().'/features/custom-css', 301));
            Route::get('/features/custom-domain', fn () => redirect('https://'._base_domain().'/features/custom-domain', 301));
            Route::get('/for-talent', fn () => redirect('https://'._base_domain().'/for-talent', 301));
            Route::get('/for-venues', fn () => redirect('https://'._base_domain().'/for-venues', 301));
            Route::get('/for-curators', fn () => redirect('https://'._base_domain().'/for-curators', 301));
            Route::get('/for-musicians', fn () => redirect('https://'._base_domain().'/for-musicians', 301));
            Route::get('/for-djs', fn () => redirect('https://'._base_domain().'/for-djs', 301));
            Route::get('/for-comedians', fn () => redirect('https://'._base_domain().'/for-comedians', 301));
            Route::get('/for-circus-acrobatics', fn () => redirect('https://'._base_domain().'/for-circus-acrobatics', 301));
            Route::get('/for-magicians', fn () => redirect('https://'._base_domain().'/for-magicians', 301));
            Route::get('/for-spoken-word', fn () => redirect('https://'._base_domain().'/for-spoken-word', 301));
            Route::get('/for-bars', fn () => redirect('https://'._base_domain().'/for-bars', 301));
            Route::get('/for-nightclubs', fn () => redirect('https://'._base_domain().'/for-nightclubs', 301));
            Route::get('/for-music-venues', fn () => redirect('https://'._base_domain().'/for-music-venues', 301));
            Route::get('/for-theaters', fn () => redirect('https://'._base_domain().'/for-theaters', 301));
            Route::get('/for-dance-groups', fn () => redirect('https://'._base_domain().'/for-dance-groups', 301));
            Route::get('/for-theater-performers', fn () => redirect('https://'._base_domain().'/for-theater-performers', 301));
            Route::get('/for-food-trucks-and-vendors', fn () => redirect('https://'._base_domain().'/for-food-trucks-and-vendors', 301));
            Route::get('/for-comedy-clubs', fn () => redirect('https://'._base_domain().'/for-comedy-clubs', 301));
            Route::get('/for-restaurants', fn () => redirect('https://'._base_domain().'/for-restaurants', 301));
            Route::get('/for-breweries-and-wineries', fn () => redirect('https://'._base_domain().'/for-breweries-and-wineries', 301));
            Route::get('/for-art-galleries', fn () => redirect('https://'._base_domain().'/for-art-galleries', 301));
            Route::get('/for-community-centers', fn () => redirect('https://'._base_domain().'/for-community-centers', 301));
            Route::get('/for-fitness-and-yoga', fn () => redirect('https://'._base_domain().'/for-fitness-and-yoga', 301));
            Route::get('/for-workshop-instructors', fn () => redirect('https://'._base_domain().'/for-workshop-instructors', 301));
            Route::get('/for-visual-artists', fn () => redirect('https://'._base_domain().'/for-visual-artists', 301));
            Route::get('/for-farmers-markets', fn () => redirect('https://'._base_domain().'/for-farmers-markets', 301));
            Route::get('/for-hotels-and-resorts', fn () => redirect('https://'._base_domain().'/for-hotels-and-resorts', 301));
            Route::get('/for-libraries', fn () => redirect('https://'._base_domain().'/for-libraries', 301));
            Route::get('/for-webinars', fn () => redirect('https://'._base_domain().'/for-webinars', 301));
            Route::get('/for-live-concerts', fn () => redirect('https://'._base_domain().'/for-live-concerts', 301));
            Route::get('/for-online-classes', fn () => redirect('https://'._base_domain().'/for-online-classes', 301));
            Route::get('/for-virtual-conferences', fn () => redirect('https://'._base_domain().'/for-virtual-conferences', 301));
            Route::get('/for-live-qa-sessions', fn () => redirect('https://'._base_domain().'/for-live-qa-sessions', 301));
            Route::get('/for-watch-parties', fn () => redirect('https://'._base_domain().'/for-watch-parties', 301));
            Route::get('/for-ai-agents', fn () => redirect('https://'._base_domain().'/for-ai-agents', 301));
            Route::get('/use-cases', fn () => redirect('https://'._base_domain().'/use-cases', 301));
            Route::get('/compare', fn () => redirect('https://'._base_domain().'/compare', 301));
            Route::get('/eventbrite-alternative', fn () => redirect('https://'._base_domain().'/eventbrite-alternative', 301));
            Route::get('/luma-alternative', fn () => redirect('https://'._base_domain().'/luma-alternative', 301));
            Route::get('/ticket-tailor-alternative', fn () => redirect('https://'._base_domain().'/ticket-tailor-alternative', 301));
            Route::get('/google-calendar-alternative', fn () => redirect('https://'._base_domain().'/google-calendar-alternative', 301));
            Route::get('/meetup-alternative', fn () => redirect('https://'._base_domain().'/meetup-alternative', 301));
            Route::get('/dice-alternative', fn () => redirect('https://'._base_domain().'/dice-alternative', 301));
            Route::get('/brown-paper-tickets-alternative', fn () => redirect('https://'._base_domain().'/brown-paper-tickets-alternative', 301));
            Route::get('/splash-alternative', fn () => redirect('https://'._base_domain().'/splash-alternative', 301));
            Route::get('/replace', fn () => redirect('https://'._base_domain().'/replace', 301));
            Route::get('/google-forms-replacement', fn () => redirect('https://'._base_domain().'/google-forms-replacement', 301));
            Route::get('/mailchimp-replacement', fn () => redirect('https://'._base_domain().'/mailchimp-replacement', 301));
            Route::get('/canva-replacement', fn () => redirect('https://'._base_domain().'/canva-replacement', 301));
            Route::get('/linktree-replacement', fn () => redirect('https://'._base_domain().'/linktree-replacement', 301));
            Route::get('/google-sheets-replacement', fn () => redirect('https://'._base_domain().'/google-sheets-replacement', 301));
            Route::get('/calendly-replacement', fn () => redirect('https://'._base_domain().'/calendly-replacement', 301));
            Route::get('/surveymonkey-replacement', fn () => redirect('https://'._base_domain().'/surveymonkey-replacement', 301));
            Route::get('/doodle-replacement', fn () => redirect('https://'._base_domain().'/doodle-replacement', 301));
            Route::get('/qr-code-generator-replacement', fn () => redirect('https://'._base_domain().'/qr-code-generator-replacement', 301));
            Route::get('/squarespace-replacement', fn () => redirect('https://'._base_domain().'/squarespace-replacement', 301));
            Route::get('/notion-replacement', fn () => redirect('https://'._base_domain().'/notion-replacement', 301));
            Route::get('/trello-replacement', fn () => redirect('https://'._base_domain().'/trello-replacement', 301));
            Route::get('/convertkit-replacement', fn () => redirect('https://'._base_domain().'/mailchimp-replacement', 301));
            Route::get('/excel-replacement', fn () => redirect('https://'._base_domain().'/google-sheets-replacement', 301));
            Route::get('/wix-replacement', fn () => redirect('https://'._base_domain().'/squarespace-replacement', 301));
            Route::get('/asana-replacement', fn () => redirect('https://'._base_domain().'/trello-replacement', 301));
            Route::get('/airtable-replacement', fn () => redirect('https://'._base_domain().'/google-sheets-replacement', 301));
            Route::get('/wordpress-replacement', fn () => redirect('https://'._base_domain().'/squarespace-replacement', 301));
            Route::get('/google-forms-alternative', fn () => redirect('https://'._base_domain().'/google-forms-replacement', 301));
            Route::get('/mailchimp-alternative', fn () => redirect('https://'._base_domain().'/mailchimp-replacement', 301));
            Route::get('/canva-alternative', fn () => redirect('https://'._base_domain().'/canva-replacement', 301));
            Route::get('/linktree-alternative', fn () => redirect('https://'._base_domain().'/linktree-replacement', 301));
            Route::get('/google-sheets-alternative', fn () => redirect('https://'._base_domain().'/google-sheets-replacement', 301));
            Route::get('/calendly-alternative', fn () => redirect('https://'._base_domain().'/calendly-replacement', 301));
            Route::get('/surveymonkey-alternative', fn () => redirect('https://'._base_domain().'/surveymonkey-replacement', 301));
            Route::get('/doodle-alternative', fn () => redirect('https://'._base_domain().'/doodle-replacement', 301));
            Route::get('/qr-code-generator-alternative', fn () => redirect('https://'._base_domain().'/qr-code-generator-replacement', 301));
            Route::get('/squarespace-alternative', fn () => redirect('https://'._base_domain().'/squarespace-replacement', 301));
            Route::get('/notion-alternative', fn () => redirect('https://'._base_domain().'/notion-replacement', 301));
            Route::get('/trello-alternative', fn () => redirect('https://'._base_domain().'/trello-replacement', 301));
            Route::get('/why-create-account', fn () => redirect('https://'._base_domain().'/why-create-account', 301));
            Route::get('/contact', fn () => redirect('https://'._base_domain().'/contact', 301));
            Route::get('/privacy', fn () => redirect('https://'._base_domain().'/privacy', 301));
            Route::get('/accessibility', fn () => redirect('https://'._base_domain().'/accessibility', 301));
            Route::get('/terms-of-service', fn () => redirect('https://'._base_domain().'/terms-of-service', 301));
            Route::get('/cookie-policy', fn () => redirect('https://'._base_domain().'/cookie-policy', 301));
            Route::get('/self-hosting-terms-of-service', fn () => redirect('https://'._base_domain().'/self-hosting-terms-of-service', 301));
            Route::get('/selfhost', fn () => redirect('https://'._base_domain().'/selfhost', 301));
            Route::get('/self-host-event-schedule', fn () => redirect('https://'._base_domain().'/selfhost', 301));
            Route::get('/self-host-event-schedule/', fn () => redirect('https://'._base_domain().'/selfhost', 301));
            Route::get('/saas', fn () => redirect('https://'._base_domain().'/saas', 301));
            Route::get('/docs', fn () => redirect('https://'._base_domain().'/docs', 301));
            // User Guide
            Route::get('/docs/getting-started', fn () => redirect('https://'._base_domain().'/docs/getting-started', 301));
            Route::get('/docs/creating-schedules', fn () => redirect('https://'._base_domain().'/docs/creating-schedules', 301));
            Route::get('/docs/schedule-basics', fn () => redirect('https://'._base_domain().'/docs/creating-schedules', 301));
            Route::get('/docs/schedule-styling', fn () => redirect('https://'._base_domain().'/docs/schedule-styling', 301));
            Route::get('/docs/creating-events', fn () => redirect('https://'._base_domain().'/docs/creating-events', 301));
            Route::get('/docs/sharing', fn () => redirect('https://'._base_domain().'/docs/sharing', 301));
            Route::get('/docs/tickets', fn () => redirect('https://'._base_domain().'/docs/tickets', 301));
            Route::get('/docs/subscriptions', fn () => redirect('https://'._base_domain().'/docs/subscriptions', 301));
            Route::get('/docs/gift-cards', fn () => redirect('https://'._base_domain().'/docs/gift-cards', 301));
            Route::get('/docs/appointments', fn () => redirect('https://'._base_domain().'/docs/appointments', 301));
            Route::get('/docs/event-graphics', fn () => redirect('https://'._base_domain().'/docs/event-graphics', 301));
            Route::get('/docs/newsletters', fn () => redirect('https://'._base_domain().'/docs/newsletters', 301));
            Route::get('/docs/analytics', fn () => redirect('https://'._base_domain().'/docs/analytics', 301));
            Route::get('/docs/account-settings', fn () => redirect('https://'._base_domain().'/docs/account-settings', 301));
            Route::get('/docs/availability', fn () => redirect('https://'._base_domain().'/docs/availability', 301));
            Route::get('/docs/boost', fn () => redirect('https://'._base_domain().'/docs/boost', 301));
            Route::get('/docs/ai-import', fn () => redirect('https://'._base_domain().'/docs/ai-import', 301));
            Route::get('/docs/scan-agenda', fn () => redirect('https://'._base_domain().'/docs/scan-agenda', 301));
            Route::get('/docs/fan-content', fn () => redirect('https://'._base_domain().'/docs/creating-events#fan-content', 301));
            Route::get('/docs/polls', fn () => redirect('https://'._base_domain().'/docs/creating-events#polls', 301));
            // Selfhost section
            Route::get('/docs/selfhost', fn () => redirect('https://'._base_domain().'/docs/selfhost', 301));
            Route::get('/docs/selfhost/installation', fn () => redirect('https://'._base_domain().'/docs/selfhost/installation', 301));
            Route::get('/docs/selfhost/saas', fn () => redirect('https://'._base_domain().'/docs/saas', 301));
            Route::get('/docs/selfhost/stripe', fn () => redirect('https://'._base_domain().'/docs/selfhost/stripe', 301));
            Route::get('/docs/selfhost/google-calendar', fn () => redirect('https://'._base_domain().'/docs/selfhost/google-calendar', 301));
            Route::get('/docs/selfhost/microsoft-calendar', fn () => redirect('https://'._base_domain().'/docs/selfhost/microsoft-calendar', 301));
            Route::get('/docs/selfhost/boost', fn () => redirect('https://'._base_domain().'/docs/selfhost/boost', 301));
            Route::get('/docs/selfhost/admin', fn () => redirect('https://'._base_domain().'/docs/selfhost/admin', 301));
            Route::get('/docs/selfhost/email', fn () => redirect('https://'._base_domain().'/docs/selfhost/email', 301));
            Route::get('/docs/selfhost/ai', fn () => redirect('https://'._base_domain().'/docs/selfhost/ai', 301));
            Route::get('/docs/selfhost/accessibility', fn () => redirect('https://'._base_domain().'/docs/selfhost/accessibility', 301));
            // SaaS section
            Route::get('/docs/saas', fn () => redirect('https://'._base_domain().'/docs/saas', 301));
            Route::get('/docs/saas/custom-domains', fn () => redirect('https://'._base_domain().'/docs/saas/custom-domains', 301));
            Route::get('/docs/saas/twilio', fn () => redirect('https://'._base_domain().'/docs/saas/twilio', 301));
            Route::get('/docs/saas/federation', fn () => redirect('https://'._base_domain().'/docs/saas/federation', 301));
            Route::get('/docs/saas/monetization', fn () => redirect('https://'._base_domain().'/docs/saas/monetization', 301));
            Route::get('/docs/selfhost/federation', fn () => redirect('https://'._base_domain().'/docs/selfhost/federation', 301));
            // Developer section
            Route::get('/docs/developer/api', fn () => redirect('https://'._base_domain().'/docs/developer/api', 301));
            Route::get('/docs/developer/webhooks', fn () => redirect('https://'._base_domain().'/docs/developer/webhooks', 301));
            Route::get('/docs/developer', fn () => redirect('https://'._base_domain().'/docs/developer/api', 301));
            // Old URL redirects
            Route::get('/docs/stripe', fn () => redirect('https://'._base_domain().'/docs/selfhost/stripe', 301));
            Route::get('/docs/google-calendar', fn () => redirect('https://'._base_domain().'/docs/selfhost/google-calendar', 301));
            Route::get('/docs/installation', fn () => redirect('https://'._base_domain().'/docs/selfhost/installation', 301));
            Route::get('/docs/api', fn () => redirect('https://'._base_domain().'/docs/developer/api', 301));
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
    Route::get('/search', fn () => redirect()->route('home'));
    Route::get('/browse', fn () => redirect()->route('home'));
    Route::get('/faq', fn () => redirect()->route('home'));
    Route::get('/referral-program', fn () => redirect('/docs/referral-program', 301));
    Route::get('/features/ticketing', fn () => redirect()->route('home'));
    Route::get('/features/ai', fn () => redirect()->route('home'));
    Route::get('/features/calendar-sync', fn () => redirect()->route('home'));
    Route::get('/google-calendar', fn () => redirect()->route('home'));
    Route::get('/outlook-calendar', fn () => redirect()->route('home'));
    Route::get('/caldav', fn () => redirect()->route('home'));
    Route::get('/stripe', fn () => redirect()->route('home'));
    Route::get('/invoiceninja', fn () => redirect()->route('home'));
    Route::get('/features/integrations', fn () => redirect()->route('home'));
    Route::get('/features/custom-fields', fn () => redirect()->route('home'));
    Route::get('/features/custom-labels', fn () => redirect()->route('home'));
    Route::get('/features/team-scheduling', fn () => redirect()->route('home'));
    Route::get('/features/sub-schedules', fn () => redirect()->route('home'));
    Route::get('/features/online-events', fn () => redirect()->route('home'));
    Route::get('/features/newsletters', fn () => redirect()->route('home'));
    Route::get('/features/recurring-events', fn () => redirect()->route('home'));
    Route::get('/features/embed-calendar', fn () => redirect()->route('home'));
    Route::get('/features/embed-tickets', fn () => redirect()->route('home'));
    Route::get('/features/fan-videos', fn () => redirect()->route('home'));
    Route::get('/features/polls', fn () => redirect()->route('home'))->name('marketing.polls');
    Route::get('/features/boost', fn () => redirect()->route('home'));
    Route::get('/features/private-events', fn () => redirect()->route('home'))->name('marketing.private_events');
    Route::get('/features/event-graphics', fn () => redirect()->route('home'));
    Route::get('/features/white-label', fn () => redirect()->route('home'));
    Route::get('/features/custom-css', fn () => redirect()->route('home'));
    Route::get('/features/custom-domain', fn () => redirect()->route('home'));
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
    Route::get('/embed-tickets', fn () => redirect()->route('home'));
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
    // The legal documents are the exception to the redirect-to-home rule above: an
    // operator can author their own from /admin/legal, and this is where they are
    // served. With none written, LegalController sends the visitor to the marketing
    // site, which is where every consent link on this install already points.
    //
    // These three paths did NOT exist on non-nexus before, so like the reserved
    // segments noted around line 305 they now win over the selfhost /{subdomain}
    // catch-alls below: a pre-existing selfhost schedule literally named 'privacy',
    // 'terms-of-service' or 'cookie-policy' loses its public URL on upgrade.
    // Role::cleanSubdomain() reserves them, but only when app.hosted is true, so it
    // does not defend this case - acceptable, and worth knowing.
    Route::get('/privacy', [LegalController::class, 'show'])->defaults('type', 'privacy')->name('marketing.privacy');
    Route::get('/terms-of-service', [LegalController::class, 'show'])->defaults('type', 'terms')->name('marketing.terms');
    Route::get('/cookie-policy', [LegalController::class, 'show'])->defaults('type', 'cookies')->name('marketing.cookie_policy');
    Route::get('/selfhost', fn () => redirect()->route('home'));
    Route::get('/saas', fn () => redirect()->route('home'));
    Route::get('/docs', fn () => redirect()->route('home'))->name('marketing.docs');
    // User Guide
    Route::get('/docs/getting-started', fn () => redirect()->route('home'))->name('marketing.docs.getting_started');
    Route::get('/docs/creating-schedules', fn () => redirect()->route('home'))->name('marketing.docs.creating_schedules');
    Route::get('/docs/schedule-basics', fn () => redirect()->route('home'))->name('marketing.docs.schedule_basics');
    Route::get('/docs/schedule-styling', fn () => redirect()->route('home'))->name('marketing.docs.schedule_styling');
    Route::get('/docs/managing-schedules', fn () => redirect()->route('home'))->name('marketing.docs.managing_schedules');
    Route::get('/docs/creating-events', fn () => redirect()->route('home'))->name('marketing.docs.creating_events');
    Route::get('/docs/sharing', fn () => redirect()->route('home'))->name('marketing.docs.sharing');
    Route::get('/docs/tickets', fn () => redirect()->route('home'))->name('marketing.docs.tickets');
    Route::get('/docs/subscriptions', fn () => redirect()->route('home'))->name('marketing.docs.subscriptions');
    Route::get('/docs/gift-cards', fn () => redirect()->route('home'))->name('marketing.docs.gift_cards');
    Route::get('/docs/appointments', fn () => redirect()->route('home'))->name('marketing.docs.appointments');
    Route::get('/docs/event-graphics', fn () => redirect()->route('home'))->name('marketing.docs.event_graphics');
    Route::get('/docs/newsletters', fn () => redirect()->route('home'))->name('marketing.docs.newsletters');
    Route::get('/docs/analytics', fn () => redirect()->route('home'))->name('marketing.docs.analytics');
    Route::get('/docs/account-settings', fn () => redirect()->route('home'))->name('marketing.docs.account_settings');
    Route::get('/docs/availability', fn () => redirect()->route('home'))->name('marketing.docs.availability');
    Route::get('/docs/boost', fn () => redirect()->route('home'))->name('marketing.docs.boost');
    Route::get('/docs/scan-agenda', fn () => redirect()->route('home'))->name('marketing.docs.scan_agenda');
    Route::get('/docs/fan-content', fn () => redirect()->route('home'))->name('marketing.docs.fan_content');
    Route::get('/docs/polls', fn () => redirect()->route('home'))->name('marketing.docs.polls');
    // Selfhost section
    Route::get('/docs/selfhost', fn () => redirect()->route('home'))->name('marketing.docs.selfhost');
    Route::get('/docs/selfhost/installation', fn () => redirect()->route('home'))->name('marketing.docs.selfhost.installation');
    Route::get('/docs/selfhost/saas', fn () => redirect()->route('home'));
    Route::get('/docs/selfhost/stripe', fn () => redirect()->route('home'))->name('marketing.docs.selfhost.stripe');
    Route::get('/docs/selfhost/google-calendar', fn () => redirect()->route('home'))->name('marketing.docs.selfhost.google_calendar');
    Route::get('/docs/selfhost/microsoft-calendar', fn () => redirect()->route('home'))->name('marketing.docs.selfhost.microsoft_calendar');
    Route::get('/docs/selfhost/boost', fn () => redirect()->route('home'))->name('marketing.docs.selfhost.boost');
    Route::get('/docs/selfhost/admin', fn () => redirect()->route('home'))->name('marketing.docs.selfhost.admin');
    Route::get('/docs/selfhost/email', fn () => redirect()->route('home'))->name('marketing.docs.selfhost.email');
    Route::get('/docs/selfhost/ai', fn () => redirect()->route('home'))->name('marketing.docs.selfhost.ai');
    Route::get('/docs/selfhost/accessibility', fn () => redirect()->route('home'))->name('marketing.docs.selfhost.accessibility');
    // SaaS section
    Route::get('/docs/saas', fn () => redirect()->route('home'))->name('marketing.docs.saas.setup');
    Route::get('/docs/saas/custom-domains', fn () => redirect()->route('home'))->name('marketing.docs.saas.custom_domains');
    Route::get('/docs/saas/twilio', fn () => redirect()->route('home'))->name('marketing.docs.saas.twilio');
    Route::get('/docs/saas/federation', fn () => redirect()->route('home'))->name('marketing.docs.saas.federation');
    Route::get('/docs/saas/monetization', fn () => redirect()->route('home'))->name('marketing.docs.saas.monetization');
    Route::get('/docs/selfhost/federation', fn () => redirect()->route('home'))->name('marketing.docs.selfhost.federation');
    // Developer section
    Route::get('/docs/developer', fn () => redirect()->route('home'));
    Route::get('/docs/developer/api', fn () => redirect()->route('home'))->name('marketing.docs.developer.api');
    Route::get('/docs/developer/webhooks', fn () => redirect()->route('home'))->name('marketing.docs.developer.webhooks');
    // Old URLs (still redirect to home)
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

// The hosted counterparts of these routes live in the subdomain group near the top of this
// file; role.view_guest in particular must register before the domain-less "/" routes above.
if (! config('app.hosted') || config('app.is_testing')) {
    Route::get('/{subdomain}/sitemap.xml', [SitemapController::class, 'schedule'])
        ->name('sitemap.schedule')
        ->withoutMiddleware('web');
    // Nested under the tenant path, and registered ahead of the /{subdomain}/{slug} catch-all at
    // the bottom of this group, so a guest page advertises the schedule's own manifest rather
    // than the platform one - see AppController::manifest.
    Route::get('/{subdomain}/manifest.webmanifest', [AppController::class, 'manifest'])
        ->name('role.manifest')
        ->withoutMiddleware('web');
    Route::get('/{subdomain}/api/past-events', [RoleController::class, 'listPastEvents'])->name('role.list_past_events');
    Route::get('/{subdomain}/api/calendar-events', [RoleController::class, 'calendarEvents'])->name('role.calendar_events');
    Route::get('/{subdomain}/request', [RoleController::class, 'request'])->name('role.request');
    Route::get('/{subdomain}/follow', [RoleController::class, 'follow'])->name('role.follow');
    // Selfhost twin. See the hosted route for why this is not /{subdomain}/subscribe.
    Route::post('/{subdomain}/audience/join', [RoleSubscriberController::class, 'store'])
        ->name('role.audience.join')->middleware('throttle:5,1,audience_join');
    // Nested under the tenant path so it cannot collide with a schedule whose subdomain
    // happens to be "promo" - selfhost serves every tenant from this same path space.
    // {hash} constrained for the same reason as the hosted twin above.
    Route::get('/{subdomain}/promo/{hash}', [PromotionController::class, 'click'])
        ->where(['hash' => '[A-Za-z0-9+=]+'])
        ->name('promo.click')->middleware('throttle:60,1');
    Route::get('/{subdomain}/guest-add', [EventController::class, 'showGuestImport'])->name('event.guest_import');
    Route::get('/{subdomain}/guest-submit', [EventController::class, 'showGuestSubmit'])->name('event.guest_submit');
    Route::post('/{subdomain}/guest-add', [EventController::class, 'guestImport'])->name('event.guest_import.store')->middleware('throttle:10,1');
    Route::post('/{subdomain}/guest-add/check-email', [EventController::class, 'checkEmail'])->name('event.check_email')->middleware('throttle:10,1');
    Route::post('/{subdomain}/guest-add/send-code', [RegisteredUserController::class, 'sendVerificationCode'])->name('event.guest_send_code')->middleware('throttle:5,1');
    Route::get('/{subdomain}/booking-request', [EventController::class, 'showBookingRequest'])->name('event.booking_request');
    Route::post('/{subdomain}/booking-request', [EventController::class, 'bookingRequest'])->name('event.booking_request.store')->middleware('throttle:10,1');
    // Appointments (Calendly-style booking). Registered before the /{subdomain}/{slug} catch-all below.
    Route::get('/{subdomain}/book', [AppointmentController::class, 'showBook'])->name('appointments.book');
    Route::get('/{subdomain}/book/{typeSlug}', [AppointmentController::class, 'showBookType'])->name('appointments.book_type');
    Route::get('/{subdomain}/book/{typeSlug}/slots', [AppointmentController::class, 'slots'])->name('appointments.slots')->middleware('throttle:60,1');
    Route::post('/{subdomain}/book/{typeSlug}', [AppointmentController::class, 'book'])->name('appointments.book.store')->middleware('throttle:10,1');
    Route::post('/{subdomain}/guest-parse', [EventController::class, 'guestParse'])->name('event.guest_parse')->middleware('throttle:10,1');
    Route::post('/{subdomain}/guest-upload-image', [EventController::class, 'guestUploadImage'])->name('event.guest_upload_image')->middleware('throttle:20,1');
    Route::get('/{subdomain}/guest-search-youtube', [RoleController::class, 'guestSearchYouTube'])->name('role.guest_search_youtube');
    Route::get('/{subdomain}/curate-event/{hash}', [EventController::class, 'curate'])->name('event.curate');
    Route::post('/{subdomain}/submit-video/{event_hash}', [EventController::class, 'submitVideo'])->name('event.submit_video')->middleware('throttle:10,60');
    Route::post('/{subdomain}/submit-comment/{event_hash}', [EventController::class, 'submitComment'])->name('event.submit_comment')->middleware('throttle:20,60');
    Route::post('/{subdomain}/submit-photo/{event_hash}', [EventController::class, 'submitPhoto'])->name('event.submit_photo')->middleware('throttle:10,60');
    Route::post('/{subdomain}/vote-poll/{event_hash}/{poll_hash}', [EventController::class, 'votePoll'])->name('event.vote_poll')->middleware('throttle:30,60');
    Route::post('/{subdomain}/suggest-poll-option/{event_hash}/{poll_hash}', [EventController::class, 'suggestPollOption'])->name('event.suggest_poll_option')->middleware('throttle:20,60');
    Route::post('/{subdomain}/event-password', [RoleController::class, 'checkEventPassword'])->name('event.check_password')->middleware('throttle:10,5');
    Route::post('/{subdomain}/promo-code/validate', [PromoCodeController::class, 'validate'])->name('promo_code.validate')->middleware('throttle:20,1');
    // Allocated seating, guest side. Mirrors the subdomain block above - the guest routes are
    // registered twice, once per routing mode, and only the path-based copy exists on selfhost.
    Route::get('/{subdomain}/seating/state', [SeatingPickerController::class, 'state'])->name('seating.state')->middleware('throttle:120,1');
    Route::post('/{subdomain}/seating/hold', [SeatingPickerController::class, 'hold'])->name('seating.hold')->middleware('throttle:60,1');
    Route::post('/{subdomain}/checkout', [TicketController::class, 'checkout'])->name('event.checkout')->middleware('throttle:10,1');
    Route::post('/{subdomain}/rsvp', [TicketController::class, 'rsvp'])->name('event.rsvp')->middleware('throttle:10,1');
    Route::post('/{subdomain}/waitlist/join', [WaitlistController::class, 'join'])->name('waitlist.join')->middleware('throttle:10,1');
    Route::get('/{subdomain}/checkout/success/{sale_id}', [TicketController::class, 'success'])->name('checkout.success');
    Route::get('/{subdomain}/checkout/cancel/{sale_id}', [TicketController::class, 'cancel'])->name('checkout.cancel');
    Route::get('/{subdomain}/payment/success/{sale_id}', [TicketController::class, 'paymentUrlSuccess'])->name('payment_url.success');
    Route::get('/{subdomain}/payment/cancel/{sale_id}', [TicketController::class, 'paymentUrlCancel'])->name('payment_url.cancel');
    Route::get('/{subdomain}/gift-cards', [GiftCardController::class, 'showPurchase'])->name('gift_card.purchase');
    Route::post('/{subdomain}/gift-cards', [GiftCardController::class, 'purchase'])->name('gift_card.purchase.store')->middleware('throttle:10,1');
    Route::get('/{subdomain}/gift-cards/success/{gift_card_id}', [GiftCardController::class, 'success'])->name('gift_card.success')->middleware('throttle:100,1');
    Route::get('/{subdomain}/gift-cards/cancel/{gift_card_id}', [GiftCardController::class, 'cancel'])->name('gift_card.cancel')->middleware('throttle:100,1');
    Route::get('/{subdomain}/gift-cards/payment/success/{gift_card_id}', [GiftCardController::class, 'paymentUrlSuccess'])->name('gift_card.payment_url.success')->middleware('throttle:100,1');
    Route::get('/{subdomain}/gift-cards/payment/cancel/{gift_card_id}', [GiftCardController::class, 'paymentUrlCancel'])->name('gift_card.payment_url.cancel')->middleware('throttle:100,1');
    Route::post('/{subdomain}/gift-card/validate', [GiftCardController::class, 'validateCode'])->name('gift_card.validate')->middleware('throttle:20,1');
    Route::get('/{subdomain}', [RoleController::class, 'viewGuest'])->name('role.view_guest');

    // iCal download for Apple Calendar
    Route::get('/{subdomain}/{slug}/{id}/ical', [EventController::class, 'downloadIcal'])->where(['id' => '[A-Za-z0-9+=]+']);
    Route::get('/{subdomain}/{slug}/{id}/{date}/ical', [EventController::class, 'downloadIcal'])->where(['date' => '\d{4}-\d{2}-\d{2}', 'id' => '[A-Za-z0-9+=]+']);
    // Feed subscription endpoints
    Route::get('/{subdomain}/feed/ical', [FeedController::class, 'icalFeed'])->name('feed.ical');
    Route::get('/{subdomain}/feed/rss', [FeedController::class, 'rssFeed'])->name('feed.rss');
    // Carpool
    Route::get('/{subdomain}/carpool/{event_hash}', [CarpoolController::class, 'index'])->name('carpool.index');
    Route::get('/{subdomain}/carpool/{event_hash}/{date}', [CarpoolController::class, 'index'])->name('carpool.index_date')->where(['date' => '\d{4}-\d{2}-\d{2}']);
    Route::post('/{subdomain}/carpool/{event_hash}/agree', [CarpoolController::class, 'agreeDisclaimer'])->name('carpool.agree_disclaimer')->middleware('throttle:5,1');
    Route::post('/{subdomain}/carpool/{event_hash}/offer', [CarpoolController::class, 'storeOffer'])->name('carpool.store_offer')->middleware('throttle:10,60');
    Route::post('/{subdomain}/carpool/{event_hash}/offer/{offer_hash}/cancel', [CarpoolController::class, 'cancelOffer'])->name('carpool.cancel_offer')->middleware('throttle:10,1');
    Route::post('/{subdomain}/carpool/{event_hash}/offer/{offer_hash}/spots', [CarpoolController::class, 'updateSpots'])->name('carpool.update_spots')->middleware('throttle:10,1');
    Route::post('/{subdomain}/carpool/{event_hash}/offer/{offer_hash}/request', [CarpoolController::class, 'requestRide'])->name('carpool.request_ride')->middleware('throttle:10,60');
    Route::post('/{subdomain}/carpool/{event_hash}/request/{request_hash}/cancel', [CarpoolController::class, 'cancelRequest'])->name('carpool.cancel_request')->middleware('throttle:10,1');
    Route::post('/{subdomain}/carpool/{event_hash}/offer/{offer_hash}/approve/{request_hash}', [CarpoolController::class, 'approveRequest'])->name('carpool.approve')->middleware('throttle:10,1');
    Route::post('/{subdomain}/carpool/{event_hash}/offer/{offer_hash}/decline/{request_hash}', [CarpoolController::class, 'declineRequest'])->name('carpool.decline')->middleware('throttle:10,1');
    Route::post('/{subdomain}/carpool/{event_hash}/offer/{offer_hash}/review', [CarpoolController::class, 'storeReview'])->name('carpool.store_review')->middleware('throttle:5,60');
    Route::post('/{subdomain}/carpool/{event_hash}/offer/{offer_hash}/report/{user_hash}', [CarpoolController::class, 'report'])->name('carpool.report')->middleware('throttle:5,60');
    // Photo gallery
    Route::get('/{subdomain}/{slug}/{id}/{date}/photos', [EventController::class, 'photoGallery'])->where(['date' => '\d{4}-\d{2}-\d{2}', 'id' => '[A-Za-z0-9+=]+']);
    Route::get('/{subdomain}/{slug}/{id}/photos', [EventController::class, 'photoGallery'])->where(['id' => '[A-Za-z0-9+=]+']);
    Route::get('/{subdomain}/{slug}/photos', [EventController::class, 'photoGallery']);

    // Event with ID and date (recurring)
    Route::get('/{subdomain}/{slug}/{id}/{date}', [RoleController::class, 'viewGuest'])
        ->name('event.view_guest_full')
        ->where(['date' => '\d{4}-\d{2}-\d{2}', 'id' => '[A-Za-z0-9+=]+']);

    // Event with ID only
    Route::get('/{subdomain}/{slug}/{id}', [RoleController::class, 'viewGuest'])
        ->name('event.view_guest_with_id')
        ->where(['id' => '[A-Za-z0-9+=]+']);

    // Existing catch-all remains last
    Route::get('/{subdomain}/{slug}', [RoleController::class, 'viewGuest'])->name('event.view_guest');
}

Route::get('/{slug?}', [HomeController::class, 'landing'])->name('landing');
