<?php

namespace App\Models;

use App\Http\Controllers\MarketingController;
use App\Jobs\GenerateRoleImageVariants;
use App\Notifications\VerifyEmail as CustomVerifyEmail;
use App\Traits\HasImageVariants;
use App\Traits\RoleBillable;
use App\Utils\CssUtils;
use App\Utils\CustomFieldUtils;
use App\Utils\GeminiUtils;
use App\Utils\ImageUtils;
use App\Utils\MarkdownUtils;
use App\Utils\SeoUtils;
use App\Utils\UrlUtils;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class Role extends Model implements MustVerifyEmail
{
    use HasImageVariants, MustVerifyEmailTrait, Notifiable, RoleBillable;

    protected $fillable = [
        'type',
        'is_unlisted',
        'announce_new_events',
        // last_announced_at is deliberately NOT fillable. It is operational state, written only
        // by SendEventAnnouncements and always through forceFill(), and RoleController::update()
        // fills from $request->all() rather than validated() - so with it here any editor could
        // POST a future timestamp and permanently silence their own schedule's announcements
        // (the command compares diffInHours() against it and skips on a negative). Same rule the
        // Event model's $casts block documents for is_cancelled and friends.
        //
        // subdomain, is_deleted and subdomain_before_delete are absent for the same reason. The
        // three of them together are what decides which schedule answers to a name, and
        // ScheduleDeletionService is the only thing that should ever move them as a set.
        'design',
        'header_style',
        'background',
        'background_rotation',
        'background_colors',
        'background_color',
        'background_image',
        'header_image',
        'accent_color',
        'font_color',
        'font_family',
        'name',
        'name_en',
        'phone',
        'email',
        'website',
        'address1',
        'address1_en',
        'address2',
        'address2_en',
        'city',
        'city_en',
        'state',
        'state_en',
        'postal_code',
        'country_code',
        'language_code',
        'translation_language_code',
        'description',
        'description_en',
        'banner_enabled',
        'banner_on_event_pages',
        'banner_message',
        'banner_message_en',
        'short_description',
        'short_description_en',
        'accept_requests',
        'event_request_form',
        'require_account',
        'use_24_hour_time',
        'timezone',
        'formatted_address',
        'google_place_id',
        'geo_address',
        'geo_lat',
        'geo_lon',
        'show_email',
        'show_phone',
        'require_approval',
        'import_config',
        'custom_domain', // Stored as full URL with protocol (e.g. https://example.com)
        'custom_domain_mode',
        'custom_domain_host',
        'custom_domain_status',
        'custom_domain_error',
        'event_layout',
        'sync_direction',
        'request_terms',
        'request_terms_en',
        'last_notified_request_count',
        'last_notified_poll_option_count',
        'custom_css',
        'event_custom_fields',
        'graphic_settings',
        'caldav_settings',
        'caldav_sync_direction',
        'microsoft_sync_direction',
        'calendar_delete_action',
        'calendar_description_template',
        'agenda_ai_prompt',
        'agenda_show_times',
        'agenda_show_description',
        'agenda_save_image',
        'slug_pattern',
        'direct_registration',
        'feedback_enabled',
        'feedback_delay_hours',
        'feedback_public',
        'fan_comments_enabled',
        'fan_photos_enabled',
        'fan_videos_enabled',
        'fan_content_require_account',
        'carpool_enabled',
        'first_day_of_week',
        'approved_subdomains',
        'default_curator_ids',
        'sponsor_logos',
        'sponsor_background_color',
        'sponsor_section_title',
        'sponsor_section_title_en',
        'custom_labels',
        'ai_style_instructions',
        'ai_content_instructions',
        'hide_past_events',
        'federation_enabled',
        'draft_events_default',
        'default_event_visibility',
        'hide_videos',
        'show_accessibility_widget',
        'show_subscribe_panel',
        'show_event_interest',
        'promotions_opt_out',
        'stay22_enabled',
        'stay22_aid',
        'default_category_id',
        'event_categories',
        'gift_cards_enabled',
        'gift_card_amounts',
        'gift_card_currency_code',
        'gift_card_valid_days',
        'gift_card_payment_method',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        // {"w480": "profile_abc_w480.webp", "w960": ..., "src": {"w": 800, "h": 800}} or
        // {"w480": null, "skipped": "too_large"}. Written only by GenerateRoleImageVariants /
        // `images:backfill-variants --roles` through recordImageVariants(), so deliberately NOT in
        // $fillable - and not in BackupService::ROLE_EXPORT_FIELDS, since a restore holds none of
        // the derivative files. The header and background uploads have a column each, for the
        // reason imageVariantSlots() gives.
        'image_variants' => 'array',
        'header_image_variants' => 'array',
        'background_image_variants' => 'array',
        'announce_new_events' => 'boolean',
        'last_announced_at' => 'datetime',
        'google_webhook_expires_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'caldav_last_sync_at' => 'datetime',
        'microsoft_webhook_expires_at' => 'datetime',
        'microsoft_last_sync_at' => 'datetime',
        'microsoft_create_teams_meetings' => 'boolean',
        'event_custom_fields' => 'array',
        'approved_subdomains' => 'array',
        'last_translated_at' => 'datetime',
        'direct_registration' => 'boolean',
        'banner_enabled' => 'boolean',
        'banner_on_event_pages' => 'boolean',
        'feedback_enabled' => 'boolean',
        'feedback_public' => 'boolean',
        'fan_comments_enabled' => 'boolean',
        'fan_photos_enabled' => 'boolean',
        'fan_videos_enabled' => 'boolean',
        'fan_content_require_account' => 'boolean',
        'carpool_enabled' => 'boolean',
        'gift_cards_enabled' => 'boolean',
        'gift_card_amounts' => 'array',
        'boost_credit' => 'decimal:2',
        'boost_max_budget' => 'decimal:2',
        'phone_verified_at' => 'datetime',
        'agenda_show_times' => 'boolean',
        'agenda_show_description' => 'boolean',
        'agenda_save_image' => 'boolean',
        'default_curator_ids' => 'array',
        'trial_reminder_sent_at' => 'datetime',
        'winddown_reminder_sent_at' => 'datetime',
        'renewal_reminder_sent_at' => 'datetime',
        'custom_labels' => 'array',
        'hide_past_events' => 'boolean',
        'federation_enabled' => 'boolean',
        'draft_events_default' => 'boolean',
        'hide_videos' => 'boolean',
        'show_accessibility_widget' => 'boolean',
        'show_subscribe_panel' => 'boolean',
        'show_event_interest' => 'boolean',
        'promotions_opt_out' => 'boolean',
        'stay22_enabled' => 'boolean',
        'email_settings_failed_at' => 'datetime',
        'email_settings_failure_notified_at' => 'datetime',
        'event_categories' => 'array',
        // Deliberately not fillable: RoleController::applyBookingFormConfig() is the only writer,
        // and it whitelists the keys. Read it through bookingFormConfig().
        'booking_form_config' => 'array',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'email_settings',
        'caldav_settings',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'header_image' => 'none',
        'header_style' => 'banner',
        // Match the DB column default so a new (unsaved) Role offers English translation by
        // default, exactly like existing rows. Without this the create form renders a null
        // target and the browser submits the first enabled <option> ('ar').
        'translation_language_code' => 'en',
        'fan_comments_enabled' => true,
        'fan_photos_enabled' => true,
        'fan_videos_enabled' => true,
        // Guests may submit fan content with just a name and email by default; the
        // approval queue is what protects the schedule, not an account requirement.
        'fan_content_require_account' => false,
    ];

    /**
     * Resolve the guest-portal header style. Two styles exist: "banner" (the large
     * card) and "compact" (the slim full-width bar). null/invalid resolves to "banner";
     * the legacy "minimal" value maps to "compact" (the same slim-row design it became).
     */
    public function headerStyle(): string
    {
        $style = $this->header_style === 'minimal' ? 'compact' : $this->header_style;

        return in_array($style, ['banner', 'compact'], true) ? $style : 'banner';
    }

    /**
     * The event layout stored on the schedule, normalised. Two layouts exist: "calendar"
     * (the month grid) and "list". "grid" is a legacy value that survives in the column's
     * enum but was never offered in the UI and no view branches on it, so it resolves to
     * the month calendar; null/invalid does too.
     */
    public function eventLayout(): string
    {
        $layout = $this->event_layout === 'grid' ? 'calendar' : $this->event_layout;

        return in_array($layout, ['calendar', 'list'], true) ? $layout : 'calendar';
    }

    /**
     * The layout to render right now. A valid ?layout= on the URL wins over the schedule's
     * stored setting, which is what lets one site embed the same schedule twice, once as a
     * calendar and once as a list. Read this on guest-facing surfaces; read eventLayout()
     * when you want the stored setting itself.
     */
    public function activeEventLayout(): string
    {
        return requested_event_layout() ?? $this->eventLayout();
    }

    /**
     * Columns whose change makes this schedule's federated listings stale: the name
     * shown on the card, the venue address flattened into each listing, and the
     * subdomain the backlink is built from.
     */
    public const FEDERATION_FIELDS = [
        'name',
        'subdomain',
        'address1',
        'address2',
        'city',
        'state',
        'postal_code',
        'country_code',
        'formatted_address',
        'geo_lat',
        'geo_lon',
        'profile_image_url',
        'language_code',
    ];

    /**
     * Names a schedule may not take, because an app route or a robots.txt rule already owns
     * the path. Extracted from cleanSubdomain() so the admin rename can REJECT one with a
     * message instead of silently substituting Str::random(8) the way cleanSubdomain() must
     * for an owner. One list, two policies.
     */
    public const RESERVED_SUBDOMAINS = [
        'eventschedule',
        'event',
        'events',
        'admin',
        'schedule',
        'availability',
        'requests',
        'profile',
        'followers',
        'following',
        'team',
        'plan',
        'home',
        'privacy',
        'terms',
        'terms-of-service',
        'cookie-policy',
        'register',
        'venues',
        'profile',
        'view',
        'edit',
        'sign_up',
        'login',
        'logout',
        'app',
        'www',
        'dev',
        'contact',
        'info',
        'blog',
        'docs',
        'api',
        'faq',
        'demo',
        'getting-started',
        'thenightowls',
        'marketing',
        'features',
        'pricing',
        'about',
        'ticketing',
        // Disallowed in robots.txt or owned by an app route, so a schedule holding one of these would
        // have its own pages de-indexed (selfhost serves tenants from the same path space).
        'appointment',
        'appointments',
        'checkout',
        'payments',
        'settings',
        'promo',
        'promotions',
        'boost',
        // Owned by the public ownership-handover route, which is registered ahead of
        // the selfhost /{subdomain} catch-all and would shadow this schedule's pages.
        'schedule-transfer',
        // Owned by the public audience confirm/unsubscribe routes (/sub/c, /sub/u), registered
        // ahead of the selfhost /{subdomain} catch-all for the same reason as the above.
        'sub',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->email) {
                $model->email = strtolower($model->email);
            }
            if ($model->phone) {
                $model->phone = \App\Utils\PhoneUtils::normalize($model->phone);
            }
            if ($model->country_code) {
                // Normalize to lowercase ISO 3166-1 alpha-2 (e.g. "ISR" -> "il"). The country
                // picker (intl-tel-input) only accepts alpha-2, so alpha-3 values throw.
                $model->country_code = \App\Utils\CountryUtils::normalizeCountryCode($model->country_code);
            }

            // roles.website is a varchar(255) under a strict connection, so an over-long value is
            // a QueryException (MySQL 1406), not a truncation - pasting a 390-character Facebook
            // link shim into the venue Website field used to 500 the event create form and cost
            // the user the whole event. Unwrapping the shim is what makes the value fit, and it
            // stores the site the paste actually stood for; the clamp is the last resort.
            //
            // Guarded on dirty, like the *_normalized block below: the column can only overflow on
            // a fresh assignment, and re-cleaning an untouched legacy value would rewrite stored
            // data during an unrelated save. saveQuietly() fires no events and so skips this
            // entirely - see BackupService::importRole(), which re-applies it by hand.
            if (! $model->exists || $model->isDirty('website')) {
                $model->website = \App\Utils\TextUtils::clamp(UrlUtils::normalizeWebsiteUrl($model->website), 255);
            }

            // roles.agenda_ai_prompt is the same shape as the events column: a varchar(500) fed by
            // a textarea capped at maxlength="500", which a form submits with CRLF line breaks, so
            // a full-length prompt arrives over the ceiling. Normalizing stores exactly what was
            // typed; the clamp is the last resort. saveQuietly() skips this - see
            // BackupService::importRole().
            if (! $model->exists || $model->isDirty('agenda_ai_prompt')) {
                $model->agenda_ai_prompt = \App\Utils\TextUtils::clamp(
                    \App\Utils\TextUtils::normalizeNewlines($model->agenda_ai_prompt), 500
                );
            }

            // Recompute the *_normalized columns used by the venue dedup lookup
            // whenever the source field changes (or on initial create).
            foreach (['name', 'name_en', 'city', 'address1', 'address1_en'] as $source) {
                if (! $model->exists || $model->isDirty($source)) {
                    $normalized = \App\Utils\GeminiUtils::normalizeForMatch($model->{$source});
                    $model->{$source.'_normalized'} = $normalized === '' ? null : $normalized;
                }
            }

            $model->description_html = MarkdownUtils::convertToHtml($model->description);
            $model->description_html_en = MarkdownUtils::convertToHtml($model->description_en);

            $model->banner_message_html = MarkdownUtils::convertToHtml($model->banner_message);
            $model->banner_message_html_en = MarkdownUtils::convertToHtml($model->banner_message_en);

            if (isset($model->custom_css)) {
                $model->custom_css = CssUtils::sanitizeCss($model->custom_css);
            }

            if ($model->accent_color == '#ffffff') {
                $model->accent_color = '#000000';
            }

            // Store the default panel as null rather than an empty string. Read raw: the accessor
            // already reads '' as null, which would hide the '' this is here to clear.
            if (($model->getAttributes()['sponsor_background_color'] ?? null) === '') {
                $model->sponsor_background_color = null;
            }

            $address = $model->fullAddressRaw();

            if (! $address && $model->geo_address) {
                $model->geo_address = null;
                $model->geo_lat = null;
                $model->geo_lon = null;
                $model->formatted_address = null;
                $model->google_place_id = null;

                // Clear cached map images when address is removed
                $cachePattern = storage_path('app/map_cache/'.$model->id.'_*');
                foreach (glob($cachePattern) as $file) {
                    @unlink($file);
                }
            }

            if (config('services.google.backend') && $address && $address != $model->geo_address) {
                try {
                    $response = \Illuminate\Support\Facades\Http::timeout(10)
                        ->get('https://maps.googleapis.com/maps/api/geocode/json', [
                            'address' => $address,
                            'key' => config('services.google.backend'),
                        ]);

                    if ($response->successful()) {
                        $responseData = $response->json();

                        if (($responseData['status'] ?? '') == 'OK') {
                            $latitude = $responseData['results'][0]['geometry']['location']['lat'];
                            $longitude = $responseData['results'][0]['geometry']['location']['lng'];

                            $model->formatted_address = $responseData['results'][0]['formatted_address'];
                            $model->google_place_id = $responseData['results'][0]['place_id'];
                            $model->geo_address = $address;
                            $model->geo_lat = $latitude;
                            $model->geo_lon = $longitude;
                        }
                    }
                    // Clear cached map images when coordinates change
                    $cachePattern = storage_path('app/map_cache/'.$model->id.'_*');
                    foreach (glob($cachePattern) as $file) {
                        @unlink($file);
                    }
                } catch (\Exception $e) {
                    \Log::warning('Geocoding failed: '.$e->getMessage());
                }
            }
        });

        static::deleting(function ($model) {
            // Cancel active boost campaigns on Meta and issue refunds
            $activeCampaigns = $model->boostCampaigns()
                ->unsettled()
                ->get();

            foreach ($activeCampaigns as $campaign) {
                try {
                    if ($campaign->meta_campaign_id && \App\Services\MetaAdsService::isBoostConfigured()) {
                        $metaService = app()->make(\App\Services\MetaAdsService::class);
                        $metaService->deleteCampaign($campaign);
                    }

                    $campaign->update(['status' => 'cancelled', 'meta_status' => $campaign->meta_campaign_id ? 'DELETED' : null]);

                    $billingService = new \App\Services\BoostBillingService;

                    // Gate the STRIPE call, not the refund. settlePayment()'s credit branch debits
                    // boost_credit regardless of mode, so gating the whole block meant deleting a
                    // schedule on selfhost destroyed the advertiser's wallet balance outright.
                    // refundOnCancellation() reaches Stripe only when there is an intent, which a
                    // selfhost campaign never has. BoostController::cancel() already works this way.
                    if ($campaign->billing_status === 'charged') {
                        $billingService->refundOnCancellation($campaign);
                    } elseif (config('app.hosted') && ! config('app.is_testing')
                        && $campaign->billing_status === 'pending' && $campaign->stripe_payment_intent_id) {
                        $billingService->cancelPaymentIntent($campaign);
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to cancel boost campaign during role deletion', [
                        'campaign_id' => $campaign->id,
                        'role_id' => $model->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('email') && config('app.hosted')) {
                $model->email_verified_at = null;

                // Only when there is somewhere to send it. VerifyEmail::toMail() returns a
                // Mailable that does to($notifiable->getEmailForVerification()), and a Mailable
                // bypasses MailChannel's null-route guard - so a cleared address reaches
                // Symfony as a message with no To and throws "An email must have a To, Cc, or
                // Bcc header" from INSIDE this hook, before the UPDATE runs. The whole save is
                // lost, not just the mail. Reachable from /admin/schedules, where an operator may
                // legitimately blank a junk schedule's address (the owner-facing form requires it).
                if ($model->email) {
                    $model->sendEmailVerificationNotification();
                }
            }

            if ($model->isDirty('phone')) {
                if (config('app.hosted') || ! $model->phone) {
                    $model->phone_verified_at = null;
                }
            }

            if ($model->isDirty(['name', 'short_description', 'description', 'address1', 'address2', 'city', 'state', 'request_terms', 'banner_message'])) {
                $model->translation_attempts = 0;
            }

            if ($model->isDirty('name') && ! $model->isDirty('name_en')) {
                $model->name_en = null;
            }

            if ($model->isDirty('description') && ! $model->isDirty('description_en')) {
                $model->description_en = null;
                $model->description_html_en = null;
            }

            if ($model->isDirty('short_description') && ! $model->isDirty('short_description_en')) {
                $model->short_description_en = null;
            }

            if ($model->isDirty('address1') && ! $model->isDirty('address1_en')) {
                $model->address1_en = null;
            }

            if ($model->isDirty('address2') && ! $model->isDirty('address2_en')) {
                $model->address2_en = null;
            }

            if ($model->isDirty('city') && ! $model->isDirty('city_en')) {
                $model->city_en = null;
            }

            if ($model->isDirty('state') && ! $model->isDirty('state_en')) {
                $model->state_en = null;
            }

            if ($model->isDirty('request_terms') && ! $model->isDirty('request_terms_en')) {
                $model->request_terms_en = null;
            }

            if ($model->isDirty('banner_message') && ! $model->isDirty('banner_message_en')) {
                $model->banner_message_en = null;
                $model->banner_message_html_en = null;
            }
        });

        // When the translation TARGET changes, every stored `_en` value is now in the wrong
        // language. Clear them (and reset attempts) so the cron regenerates in the new target;
        // sub-schedule names are re-translated inline by the job since the cron skips groups.
        static::saving(function ($model) {
            // The per-schedule federation toggle is only rendered once the operator
            // has switched the network on for the whole install. Re-assert that here
            // rather than in the controller: RoleController fills from the request in
            // three separate places, and a hand-crafted POST would otherwise let a
            // customer opt into a network their operator never joined.
            if ($model->isDirty('federation_enabled')
                && (config('app.is_nexus') || ! Setting::get('federation_enabled'))) {
                // Falls back to null, not to a decision. getOriginal() only returns
                // null for a schedule being created, and a schedule created while the
                // install is not on a network has not opted into anything - writing
                // true here would quietly enrol it the day the operator joins one.
                $model->federation_enabled = $model->getOriginal('federation_enabled');
            }
        });

        // The WebP derivatives of every uploaded image (imageVariantSlots()), mirroring Event's
        // flyer hooks. The profile photo is also the card image of every event without a flyer,
        // including on the homepage wall; the background is the schedule page's LCP image.
        static::saving(function ($model) {
            // A new image invalidates every derivative of the old one. Cleared here rather than in
            // the job so pages fall straight back to the (correct) original until the queue
            // rebuilds, and the files deleted because their names derive from the old filename -
            // once this row stops holding it nothing can address them. getRawOriginal(), because
            // getOriginal() runs the accessor and would hand back a URL. deleteStoredVariants()
            // never throws, so this cannot fail the save.
            if (! $model->exists) {
                return;
            }

            foreach ($model->imageVariantSlots() as [$source, $column]) {
                if (! $model->isDirty($source)) {
                    continue;
                }

                $previous = $model->getRawOriginal($source);
                if (is_string($previous) && $previous !== '') {
                    ImageUtils::deleteStoredVariants($previous);
                }

                $model->{$column} = null;
            }
        });

        static::created(function ($model) {
            foreach (array_keys($model->imageVariantSlots()) as $slot) {
                self::queueImageVariants($model, $slot);
            }
        });

        static::updated(function ($model) {
            // created() is separate because an insert never syncs changes, so wasChanged() is
            // blind to it. One job per slot: a save that replaces the header and the background
            // queues two, which write two different columns.
            foreach ($model->imageVariantSlots() as $slot => [$source]) {
                if ($model->wasChanged($source)) {
                    self::queueImageVariants($model, $slot);
                }
            }

            if ($model->wasChanged('profile_image_url')) {
                // Events wearing this photo on the wall are cached with it, and the saving hook
                // above just deleted the derivative files the cached copy points at. Only the
                // photo change busts, as Event::WALL_CACHE_FIELDS' flyer does; recording the new
                // derivatives later does not (see recordImageVariants()). The header and the
                // background are not on the wall.
                MarketingController::forgetWallCache();
            }

            if ($model->wasChanged('translation_language_code')) {
                \App\Jobs\RegenerateRoleTranslations::dispatch($model);
            }

            // A federated listing carries this schedule's name and its flattened venue
            // address, and its backlink is built from the subdomain - none of which
            // touch the event row, so nothing else would re-queue them. Without this,
            // renaming a venue leaves every federated listing for it stale, and changing
            // a subdomain leaves the network holding links that no longer resolve.
            // Explicit Event query rather than $model->events()->update(), which would
            // update through the belongsToMany join and can hit ambiguous columns.
            // A query-builder update fires no model events, so this cannot recurse.
            // Skipped for a soft-deleted schedule: an admin releasing a squatted subdomain
            // renames the row, and subdomain is a FEDERATION_FIELD, so without this every event
            // of a schedule we just took down gets re-queued for a federation push.
            // federatableQuery() filters is_deleted, so those pushes would be dropped anyway -
            // this is churn, not a leak - but re-queueing a deleted schedule is still wrong.
            if ($model->wasChanged(self::FEDERATION_FIELDS) && ! $model->is_deleted) {
                Event::whereIn('id', $model->events()->pluck('events.id'))
                    ->where(function ($q) {
                        $q->whereNotNull('federated_at')->orWhereNotNull('federated_skipped_at');
                    })
                    ->update(['federated_at' => null, 'federated_skipped_at' => null]);
            }
        });

        static::deleted(function ($model) {
            // Nothing can address this row's derivatives once it is gone. The controller deletes
            // the originals itself; the derivatives are ours.
            foreach (array_keys($model->imageVariantSlots()) as $slot) {
                $raw = $model->imageVariantSource($slot);
                if ($raw !== null) {
                    ImageUtils::deleteStoredVariants($raw);
                }
            }
        });

    }

    public function encodeId()
    {
        return UrlUtils::encodeId($this->id);
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail('role', $this->subdomain));
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ownership handover offers, open and resolved. See App\Models\RoleTransfer and
     * App\Services\ScheduleTransferService.
     */
    public function transfers()
    {
        return $this->hasMany(RoleTransfer::class);
    }

    /** The one offer currently awaiting a decision, if any. */
    public function openTransfer(): ?RoleTransfer
    {
        return $this->transfers()->open()->latest('id')->first();
    }

    public function events()
    {
        return $this->belongsToMany(Event::class)
            ->withPivot('id', 'name_translated', 'description_translated', 'description_html_translated', 'is_accepted', 'is_auto_sourced', 'group_id', 'google_event_id', 'caldav_event_uid', 'caldav_event_etag')
            ->using(EventRole::class);
    }

    /**
     * Talent/venue schedules this curator pulls events from. The reverse of
     * default_curator_ids, which lives on the source and only reaches curators the
     * same user owns.
     */
    public function sources()
    {
        return $this->hasMany(RoleSource::class, 'role_id');
    }

    /** Per-request cache for whether this schedule has any pass ticket. */
    protected $hasPassCache = null;

    /**
     * Whether this schedule has any pass ticket at all - bookable OR drop-in. Cached per
     * request. Used to short-circuit pass-reservation counting for the vast majority of
     * events that have no pass. It must include non-bookable passes: reserved-seat
     * counting includes door redemptions (a scanned-in member occupies a seat too), and
     * those are written for any pass, not only booking-enabled ones. Schedule-level - a
     * multi-event-scope pass covering an event may be sold on a different event.
     */
    public function hasPass(): bool
    {
        if ($this->hasPassCache === null) {
            $this->hasPassCache = Ticket::query()
                ->where('is_pass', true)
                ->whereIn('event_id', $this->events()->pluck('events.id'))
                ->exists();
        }

        return $this->hasPassCache;
    }

    public function giftCards()
    {
        return $this->hasMany(GiftCard::class);
    }

    public function appointmentTypes()
    {
        return $this->hasMany(AppointmentType::class);
    }

    /** Per-request memos for bookableAppointmentTypes() and appointmentTypeCount(). */
    protected $bookableAppointmentTypesCache = null;

    protected $appointmentTypeCountCache = null;

    /**
     * Whether this schedule currently offers bookable appointments - drives the GP
     * "Book a Time" button and the guest /book pages. Booking is available on every plan; a type
     * counts when active, not deleted, and either free or priced with both the plan and a working
     * payment method behind it (see bookableAppointmentTypes()).
     */
    public function hasBookableAppointments(): bool
    {
        return $this->bookableAppointmentTypes()->isNotEmpty();
    }

    /**
     * Whether this schedule currently SELLS gift cards (settings on + Pro).
     */
    public function giftCardsEnabled(): bool
    {
        return $this->gift_cards_enabled
            && ! empty($this->gift_card_amounts)
            && $this->isPro();
    }

    /**
     * Whether this schedule may build and sell from allocated seating plans.
     *
     * Enterprise, not Pro: it is aimed at venues and theatres rather than the long tail,
     * and it is the first ticketing capability to sit in that tier. Selfhost gets it for
     * free through isEnterprise(), as with every other gate.
     */
    public function seatingEnabled(): bool
    {
        return $this->isEnterprise();
    }

    /**
     * Whether the configured gift card payment method is usable by the owner.
     */
    public function giftCardPaymentMethodAvailable(): bool
    {
        $user = $this->user;
        if (! $user) {
            return false;
        }

        return match ($this->gift_card_payment_method) {
            'stripe' => $user->canAcceptStripePayments(),
            'invoiceninja' => (bool) $user->invoiceninja_api_key,
            'payment_url' => (bool) ($user->payment_url && $user->payment_secret),
            default => true, // cash
        };
    }

    /**
     * Full selling gate for the public purchase page and entry buttons:
     * settings + Pro, a working delivery channel (hosted needs role SMTP -
     * the recipient email IS the delivery mechanism), and a usable payment method.
     */
    public function canSellGiftCards(): bool
    {
        if (! $this->giftCardsEnabled()) {
            return false;
        }

        if (config('app.hosted') && ! $this->hasEmailSettings()) {
            return false;
        }

        return $this->giftCardPaymentMethodAvailable();
    }

    /** Per-request cache for whether this schedule has any redeemable gift card. */
    protected $hasRedeemableGiftCardsCache = null;

    /**
     * Whether any sold gift card can still be redeemed. Redemption must keep
     * working even when selling is disabled or the Pro plan lapses - sold cards
     * are outstanding liabilities.
     */
    public function hasRedeemableGiftCards(): bool
    {
        if ($this->hasRedeemableGiftCardsCache === null) {
            $this->hasRedeemableGiftCardsCache = $this->giftCards()
                ->where('status', 'active')
                ->where('remaining_amount', '>', 0)
                ->where(function ($query) {
                    $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->exists();
        }

        return $this->hasRedeemableGiftCardsCache;
    }

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot('level', 'dates_unavailable', 'notification_settings', 'google_calendar_id')
            ->orderBy('name');
    }

    public function owner()
    {
        return $this->members()
            ->where('level', '=', 'owner')
            ->first();
    }

    public function members()
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot('level', 'dates_unavailable', 'notification_settings', 'google_calendar_id')
            ->where('level', '!=', 'follower')
            ->orderBy('name');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot('level', 'google_calendar_id')
            ->where('level', 'follower')
            ->orderBy('pivot_created_at', 'desc');
    }

    /**
     * Followers whose pivot this schedule's subscribe panel did NOT create.
     *
     * One person can hold both records: RoleSubscriberController::confirm() creates a stub account
     * and attaches a follower pivot, so a confirmed subscriber is also an account follower. Three
     * read sites have to exclude that overlap or they double-count or misrepresent it - the two
     * audience tables on the Followers tab, and NewsletterSegment's all_followers segment.
     *
     * The rule is "was this pivot created BY the confirmation", not "does this person have a
     * subscriber row", and the difference is load-bearing in both directions:
     *
     *   - Widening all_followers is forbidden in writing by resolveSubscribers(): it "would
     *     silently change the recipient set of every saved segment and every already-scheduled
     *     newsletter". NewsletterService::send() resolves at SEND time, so that is a live hazard.
     *   - NARROWING it has exactly the same mechanism. Somebody who pressed Follow in 2025 and
     *     confirmed a panel subscription in 2026 is an account follower by any definition, and an
     *     owner who names only the all_followers segment must still reach them. Matching on email
     *     alone dropped them.
     *
     * Hence the confirmed_at <= role_user.created_at comparison: linkAccount() attaches the pivot
     * in the same request that stamps confirmed_at, so a pivot that PREDATES the confirmation was
     * created by the person pressing Follow and is theirs. RoleSubscriberController::remove()
     * applies the same test before detaching, for the same reason.
     *
     * Correlated on role_user.role_id rather than a captured $this->id: an eager load builds the
     * relation from an ID-less instance (Builder::getRelation() calls newInstance()), and
     * Relation::noConstraints suppresses addConstraints() but not a whereNotExists added here - so
     * a captured id would bind NULL, match nothing, and silently filter nothing at all.
     *
     * Nobody loses mail either way: NewsletterService::resolveRecipientsUncached()'s default branch
     * merges followers() and confirmed subscribers and dedups by email, which is why that branch
     * deliberately keeps using followers(), not this.
     */
    public function accountOnlyFollowers()
    {
        return $this->followers()->whereNotExists(function ($query) {
            $query->selectRaw('1')
                ->from('role_subscribers')
                ->whereColumn('role_subscribers.role_id', 'role_user.role_id')
                ->whereColumn('role_subscribers.email', 'users.email')
                ->whereNotNull('role_subscribers.confirmed_at')
                ->whereColumn('role_subscribers.confirmed_at', '<=', 'role_user.created_at');
        });
    }

    /**
     * Account-less members of this schedule's audience: people who gave an email address on the
     * guest portal without creating an account. App\Services\AudienceResolver decides which of these
     * rows may actually be mailed; NewsletterService unions them with followers() for a campaign.
     */
    public function subscribers()
    {
        return $this->hasMany(\App\Models\RoleSubscriber::class);
    }

    /**
     * Get non-owner members who have Google Calendar sync enabled
     */
    public function getMembersWithCalendarSync()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('level', 'google_calendar_id')
            ->whereNotNull('role_user.google_calendar_id')
            ->where('level', '!=', 'owner')
            ->get();
    }

    public function getEditorsWantingNotification(string $type): \Illuminate\Support\Collection
    {
        return $this->belongsToMany(User::class)
            ->withPivot('level', 'notification_settings')
            ->whereIn('level', ['owner', 'admin'])
            ->get()
            ->filter(function ($user) use ($type) {
                $settings = json_decode($user->pivot->notification_settings ?? '{}', true);

                // new_request defaults to opt-in when the user has not explicitly set a preference.
                if ($type === 'new_request' && ! array_key_exists($type, $settings)) {
                    return true;
                }

                return ! empty($settings[$type]);
            });
    }

    public function venueEvents()
    {
        return $this->belongsToMany(Event::class, 'event_role', 'role_id', 'event_id')
            ->where('roles.type', 'venue');
    }

    /**
     * IDs of non-deleted roles of the given type that share at least one event with
     * this schedule (e.g. venues this curator has hosted events at). Includes roles
     * connected via pending/unaccepted events, matching the AI venue-matching tiers.
     */
    public function connectedRoleIds(string $type): \Illuminate\Support\Collection
    {
        return \DB::table('event_role as er1')
            ->join('event_role as er2', 'er1.event_id', '=', 'er2.event_id')
            ->join('roles', 'er2.role_id', '=', 'roles.id')
            ->where('er1.role_id', $this->id)
            ->where('roles.type', $type)
            ->where('roles.is_deleted', false)
            ->distinct()
            ->pluck('roles.id');
    }

    /**
     * Schedules shown in the guest-page logo wall banner (header_image = 'logos'):
     * non-deleted venues (talents for a venue schedule) with a profile image that
     * share at least one publicly listed event accepted on this schedule's side of
     * the event_role pivot. Owner-defined order (logo_wall_order) first, the rest
     * alphabetical; capped at 36 after ordering so manual picks survive the cap.
     */
    public function logoWallRoles(): \Illuminate\Database\Eloquent\Collection
    {
        $type = $this->isVenue() ? 'talent' : 'venue';

        $roles = Role::query()
            ->select([
                'roles.id', 'roles.type', 'roles.subdomain',
                'roles.name', 'roles.name_en', 'roles.translation_language_code',
                'roles.profile_image_url',
                'roles.user_id', 'roles.email_verified_at', 'roles.phone_verified_at',
            ])
            ->join('event_role as er2', 'er2.role_id', '=', 'roles.id')
            ->join('event_role as er1', 'er1.event_id', '=', 'er2.event_id')
            ->join('events', 'events.id', '=', 'er1.event_id')
            ->where('er1.role_id', $this->id)
            ->where('er1.is_accepted', true)
            // The other side must not have been advertised without its consent. is_accepted is
            // tri-state (null = never answered, false = declined), so a claimed schedule only
            // qualifies on an explicit true.
            //
            // The exception is a schedule with no user_id: those are placeholders this owner
            // invented while entering an event (EventRepo::saveEvent() attaches them at null and
            // nothing can ever promote them, because a role with no account has no member to
            // accept on its behalf). There is no third party there to consent, and excluding them
            // would empty the wall for the most common way it gets populated. An explicit false
            // still drops them.
            ->where(function ($q) {
                $q->where('er2.is_accepted', true)
                    ->orWhere(function ($q2) {
                        $q2->whereNull('er2.is_accepted')->whereNull('roles.user_id');
                    });
            })
            // Draft/internal and unlisted events must not leak booking
            // relationships onto the public wall, and neither may a password-protected one,
            // which older rows can be while listed.
            ->where('events.is_draft', false)
            ->where('events.is_private', false)
            ->where('events.is_cancelled', false)
            ->where(fn ($q) => Event::constrainNotPasswordProtected($q))
            ->where('roles.id', '!=', $this->id)
            ->where('roles.type', $type)
            ->where('roles.is_deleted', false)
            ->whereNotNull('roles.profile_image_url')
            ->where('roles.profile_image_url', '!=', '')
            ->distinct()
            ->orderBy('roles.name')
            ->get();

        // Guard against a malformed stored value (a JSON scalar would make array_flip
        // throw and 500 this public page); only a JSON array yields a usable order.
        $decoded = json_decode($this->logo_wall_order ?? '[]', true);
        $order = array_flip(is_array($decoded) ? $decoded : []);

        return $roles
            ->sortBy(fn ($r) => $order[$r->id] ?? PHP_INT_MAX)
            ->values()
            ->take(36);
    }

    public function scopeType($query, $type)
    {
        return $query->where('roles.type', $type);
    }

    public function scopeSubdomain($query, $subdomain)
    {
        return $query->where('subdomain', $subdomain);
    }

    public function bestAddress()
    {
        if ($this->formatted_address) {
            return $this->formatted_address;
        } else {
            return $this->fullAddress();
        }
    }

    public function shortAddress()
    {
        $str = '';

        if ($this->translatedAddress1()) {
            $str .= $this->translatedAddress1();
        }

        if ($this->translatedCity()) {
            if ($str) {
                $str .= ', ';
            }

            $str .= $this->translatedCity();
        }

        return $str;
    }

    /**
     * Get a short display string for the venue.
     * Format priority:
     * 1. Name | City (if both have values)
     * 2. Name | Address (else if both have values)
     * 3. Address (else if address has value)
     * 4. City (else)
     */
    public function shortVenue($translate = true, $forceEnglish = false, ?string $want = null)
    {
        if ($forceEnglish) {
            $name = $this->englishName();
            $city = $this->englishCity();
            $address = $this->englishAddress1();
        } elseif ($want !== null) {
            $name = $this->textInLanguage('name', $want);
            $city = $this->textInLanguage('city', $want);
            $address = $this->textInLanguage('address1', $want);
        } else {
            $name = $translate ? $this->translatedName() : $this->name;
            $city = $translate ? $this->translatedCity() : $this->city;
            $address = $translate ? $this->translatedAddress1() : $this->address1;
        }

        if ($name && $city) {
            return $name.' | '.$city;
        }
        if ($name && $address) {
            return $name.' | '.$address;
        }
        if ($address) {
            return $address;
        }

        return $city ?: '';
    }

    public function fullAddress()
    {
        $str = '';

        if ($this->translatedAddress1()) {
            $str .= $this->translatedAddress1().', ';
        }

        if ($this->translatedAddress2()) {
            $str .= $this->translatedAddress2().', ';
        }

        if ($this->translatedCity()) {
            $str .= $this->translatedCity().', ';
        }

        if ($this->translatedState()) {
            $str .= $this->translatedState().', ';
        }

        if ($this->postal_code) {
            $str .= $this->postal_code.', ';
        }

        if ($str && $this->country_code) {
            $str .= $this->country_code;
        }

        return $str;
    }

    public function fullAddressRaw()
    {
        $str = '';

        if ($this->address1) {
            $str .= $this->address1.', ';
        }

        if ($this->address2) {
            $str .= $this->address2.', ';
        }

        if ($this->city) {
            $str .= $this->city.', ';
        }

        if ($this->state) {
            $str .= $this->state.', ';
        }

        if ($this->postal_code) {
            $str .= $this->postal_code.', ';
        }

        if ($str && $this->country_code) {
            $str .= $this->country_code;
        }

        return $str;
    }

    public function isVenue()
    {
        return $this->type == 'venue';
    }

    public function isTalent()
    {
        return $this->type == 'talent';
    }

    public function isCurator()
    {
        return $this->type == 'curator';
    }

    /**
     * The zone a wall-clock entered for this schedule is anchored to.
     *
     * Deliberately the SAME expression as Event::scheduleTimezone(), which is what every DISPLAY
     * path resolves: capture and display must agree, or an event is entered at one time and shown
     * at another. In particular this does NOT consult the venue, which EventRepo::saveEvent()'s own
     * fallback chain does - passing this to saveEvent() as $timezoneOverride is what keeps that
     * chain out of the way. Without it, a schedule with no timezone of its own captures against the
     * venue's zone while the form that collected the time was labelled with the app's, so every
     * re-save shifts the event by the difference (the API had the same bug, issue #123).
     *
     * ?: not ??: roles.timezone is a nullable string, and an empty one is a DateTimeZone error
     * rather than a fallback.
     */
    public function captureTimezone(): string
    {
        return $this->timezone ?: config('app.timezone');
    }

    /**
     * Returns the schedule's effective ordered category list.
     *
     * Each entry: ['id' => int, 'name' => string, 'is_custom' => bool].
     * Removed categories are simply absent from the stored array.
     * For English viewers, `name_en` is preferred when present.
     *
     * Defensively skips any legacy entries that carry `disabled === true`
     * (left over from the early-build phase before the toggle was removed),
     * so they don't silently re-appear after the refinement deploys.
     */
    public function getEventCategories(?string $locale = null): array
    {
        $stored = $this->event_categories;
        $systemDefaults = config('app.event_categories', []);

        // Null column → use system defaults verbatim (translated via existing helper).
        if (is_null($stored)) {
            $list = [];
            foreach ($systemDefaults as $id => $englishName) {
                $key = str_replace(' & ', '_&_', strtolower($englishName));
                $key = str_replace(' ', '_', $key);
                $list[] = [
                    'id' => $id,
                    'name' => $locale ? __("messages.{$key}", [], $locale) : __("messages.{$key}"),
                    'is_custom' => false,
                    'color' => null,
                    'name_en' => null,
                ];
            }
            usort($list, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

            return $list;
        }

        $list = [];
        foreach ($stored as $entry) {
            if (! is_array($entry) || ! isset($entry['id'])) {
                continue;
            }
            // Backward-compat: legacy v1 entries marked disabled are treated as removed.
            if (! empty($entry['disabled'])) {
                continue;
            }

            // Prefer name_en (the translation target's text) when the requested locale is this
            // schedule's target language (matches sponsor_logos / event_custom_fields pattern).
            $name = null;
            if ($locale === ($this->translation_language_code ?: 'en') && ! empty($entry['name_en'])) {
                $name = $entry['name_en'];
            }
            if ($name === null) {
                $name = $entry['name'] ?? null;
            }
            // Final fallback: system default (only valid for ids ≤ 12).
            if (($name === null || $name === '') && isset($systemDefaults[$entry['id']])) {
                $name = $systemDefaults[$entry['id']];
            }
            if ($name === null || $name === '') {
                continue;
            }

            $list[] = [
                'id' => (int) $entry['id'],
                'name' => $name,
                'is_custom' => $entry['id'] >= 100,
                'color' => $entry['color'] ?? null,
                'name_en' => $entry['name_en'] ?? null,
            ];
        }
        usort($list, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        return $list;
    }

    /**
     * Returns the assigned hex color for a category id, or null if unset.
     * System defaults never carry a color; only stored entries can.
     */
    public function getCategoryColor(int $categoryId): ?string
    {
        foreach (($this->event_categories ?? []) as $entry) {
            if (is_array($entry) && (int) ($entry['id'] ?? 0) === $categoryId) {
                return $entry['color'] ?? null;
            }
        }

        return null;
    }

    /**
     * Returns the display name for a category id in this schedule's context.
     * Falls back to the system-default config for ids ≤ 12 even when the entry
     * has been removed from the schedule's list, so historical events always
     * have a name to render. Returns null for unknown custom ids (≥ 100);
     * the caller (typically `Event::resolveCategoryName`) then uses the cached
     * `events.category_name`.
     */
    public function getCategoryName(int $categoryId, ?string $locale = null): ?string
    {
        foreach ($this->getEventCategories($locale) as $entry) {
            if ($entry['id'] === $categoryId) {
                return $entry['name'];
            }
        }

        $systemDefaults = config('app.event_categories', []);
        if (isset($systemDefaults[$categoryId])) {
            $englishName = $systemDefaults[$categoryId];
            $key = str_replace(' & ', '_&_', strtolower($englishName));
            $key = str_replace(' ', '_', $key);

            return $locale ? __("messages.{$key}", [], $locale) : __("messages.{$key}");
        }

        return null;
    }

    /**
     * Allocate a fresh custom category id (≥ 100). Must be called inside a transaction
     * that has acquired `lockForUpdate()` on the role to avoid races.
     * Ids are never reused — also considers historical events.category_id values
     * created under this role, so deleting a custom category never recycles its id.
     */
    public function nextCustomCategoryId(): int
    {
        $max = 99;
        foreach (($this->event_categories ?? []) as $entry) {
            if (is_array($entry) && isset($entry['id'])) {
                $max = max($max, (int) $entry['id']);
            }
        }
        $historicalMax = (int) Event::where('creator_role_id', $this->id)
            ->where('category_id', '>=', 100)
            ->max('category_id');
        $max = max($max, $historicalMax);

        return $max + 1;
    }

    public function usesBookingForm()
    {
        if ($this->isTalent()) {
            return true;
        }

        return $this->event_request_form === 'booking';
    }

    /**
     * The booking form's default fields an owner can make required. `date_time` covers the date and
     * the start time together: a date without a time cannot be saved, so neither is asked alone.
     * `phone` is the odd one out: the others are always on the form, so it is only requirable while
     * ask_phone puts it there at all (see bookingFormRequires()).
     */
    public const BOOKING_FORM_REQUIRABLE_FIELDS = ['event_name', 'date_time', 'description', 'location', 'phone'];

    /**
     * Whether a guest has to create an account to send a booking-form request.
     *
     * Never on a talent schedule. roles.require_account defaults to true and the settings page has
     * no toggle for it on a talent, so every talent carries a true it never chose - and a request to
     * book a performer is read by hand anyway (see RoleController::request()).
     */
    public function bookingFormRequiresAccount(): bool
    {
        return ! $this->isTalent() && (bool) $this->require_account;
    }

    /**
     * The booking form options with every key present and typed, whatever is stored.
     *
     * @return array{required_fields: array<string, bool>, allow_online: bool, ask_phone: bool}
     */
    public function bookingFormConfig(): array
    {
        return self::normalizeBookingFormConfig($this->booking_form_config);
    }

    /**
     * Null (never saved) and anything malformed resolve to the defaults: nothing required and the
     * Online option offered, which is how the form behaved before these options existed.
     *
     * @return array{required_fields: array<string, bool>, allow_online: bool, ask_phone: bool}
     */
    public static function normalizeBookingFormConfig(mixed $config): array
    {
        $config = is_array($config) ? $config : [];
        $stored = is_array($config['required_fields'] ?? null) ? $config['required_fields'] : [];

        $required = [];
        foreach (self::BOOKING_FORM_REQUIRABLE_FIELDS as $field) {
            $required[$field] = filter_var($stored[$field] ?? false, FILTER_VALIDATE_BOOLEAN);
        }

        return [
            'required_fields' => $required,
            'allow_online' => array_key_exists('allow_online', $config)
                ? filter_var($config['allow_online'], FILTER_VALIDATE_BOOLEAN)
                : true,
            // Defaults to FALSE, unlike allow_online: an existing schedule's form must not sprout a
            // field its owner never asked for.
            'ask_phone' => filter_var($config['ask_phone'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }

    public function bookingFormRequires(string $field): bool
    {
        // A venue schedule's booking form has no location to fill in: the venue is the location.
        if ($field === 'location' && $this->isVenue()) {
            return false;
        }

        // A field the form does not ask for cannot be required. Keeps a stored `phone => true` inert
        // while the owner has the phone field switched off, so an out-of-sync page or a stale config
        // can never produce a requirement the visitor has no way to satisfy.
        if ($field === 'phone' && ! $this->bookingFormAsksPhone()) {
            return false;
        }

        return $this->bookingFormConfig()['required_fields'][$field] ?? false;
    }

    /**
     * @return array<string, bool> requirable field => whether this schedule requires it
     */
    public function bookingFormRequiredFields(): array
    {
        $required = [];
        foreach (self::BOOKING_FORM_REQUIRABLE_FIELDS as $field) {
            $required[$field] = $this->bookingFormRequires($field);
        }

        return $required;
    }

    public function bookingFormAllowsOnline(): bool
    {
        return $this->bookingFormConfig()['allow_online'];
    }

    /**
     * Whether the booking form asks the visitor for a phone number at all.
     *
     * Asked of signed-in visitors too, unlike the name and email: the account supplies those, and a
     * phone number is the one contact detail it does not have.
     */
    public function bookingFormAsksPhone(): bool
    {
        return $this->bookingFormConfig()['ask_phone'];
    }

    public function getRequireApprovalAttribute($value)
    {
        if ($this->isTalent()) {
            return true;
        }

        return (bool) $value;
    }

    public function isRegistered()
    {
        return $this->email || $this->phone;
    }

    public function isClaimed()
    {
        return ($this->email_verified_at != null || $this->phone_verified_at != null) && $this->user_id != null;
    }

    /**
     * Whether confirming an audience subscription to this schedule will mint an account.
     *
     * The single definition of RoleSubscriberController::linkAccount()'s three refusals, because
     * the answer is also a SENTENCE shown to the visitor - "Confirming also sets up an account for
     * you" - on the subscribe panel, in the follow modal, on the confirm page and in the
     * confirmation email. Seven places asking the question, one place answering it: a promise that
     * drifts from the code keeping it is worse than no promise.
     *
     * - **Unclaimed is the security clause, not tidiness.** isEditableBy() ends with
     *   "! $this->isClaimed() && $user->isFollowing($this->subdomain)", and the same rule is
     *   repeated in RoleController::following(), VenueUtils and GeminiUtils' venue_is_editable. The
     *   bar for edit rights was "be signed in and press a button"; creating the account here would
     *   make it "type any address into a public form and click the link in the resulting email".
     *   Skipping costs nothing: an unclaimed schedule has no owner to write a newsletter, and
     *   SendEventAnnouncements::dueRoles() already requires a claimed one.
     * - **Registration** is the gate every other account-creating path in the app honours. Without
     *   it a selfhost install with ALLOW_REGISTRATION unset accrues one permanently unclaimable
     *   users row per confirmed subscriber.
     * - **Demo** matches RoleSubscriberController::store(), which abort(404)s a demo schedule
     *   outright. The Follow trigger does not: it gates on is_demo_mode(), which is about the
     *   signed-in demo USER, so a demo schedule's page really does offer a signed-out visitor a
     *   subscribe form that 404s. That is older than this method, but there is no reason for the
     *   modal to promise an account on top of it.
     */
    public function willCreateAccountOnConfirm(): bool
    {
        return public_registration_enabled() && $this->isClaimed() && ! is_demo_role($this);
    }

    /**
     * The demo exclusions on their own. Extracted so the three admin-listing scopes below cannot
     * drift on what "not a demo schedule" means.
     */
    public function scopeNotDemoSchedule($query)
    {
        return $query->where('subdomain', '!=', \App\Services\DemoService::DEMO_ROLE_SUBDOMAIN)
            ->where('subdomain', 'not like', 'demo-%');
    }

    /**
     * Constrain a roles query to demo CONTENT, for negation by the callers.
     *
     * A third demo predicate on purpose. The three ask different questions, and the names are not
     * inverses of each other:
     *
     * - is_demo_role() asks about the demo ACCOUNT. ~30 call sites hang off it - free Pro features
     *   (isPro bypass), ad suppression, blocked paid tickets, suppressed email - so widening it to
     *   cover the showcase schedules would change all of that for them. Keeping them out of
     *   discovery should not cost that.
     * - notDemoSchedule() above is the ADMIN LISTING question, and keys on subdomain shape only.
     * - This one asks "is this fabricated content we show off from /examples", which is the only
     *   question the public discovery surfaces need answered.
     *
     * The email arm is what makes it work. The twelve showcase schedules linked from /examples
     * (villageidiot, sufficientgroundscoffeemusic, karateclub, painting, ...) are ordinary
     * schedules, owned by ordinary accounts, on ordinary subdomains, so the subdomain arms and the
     * owner-email arm all miss them - which is how villageidiot and a fabricated "Wine & Jazz
     * Evening" came to be the top hits for /search?q=jazz. What they DO share is roles.email, the
     * schedule's own contact address, set to DemoService::DEMO_EMAIL.
     *
     * EVERY ARM IS NULL-SAFE, and must stay that way. Callers negate this with whereNot(), i.e.
     * NOT (a OR b OR ...), and in MySQL's three-valued logic one NULL arm with no TRUE arm makes
     * the whole expression NULL - and NOT NULL is NULL, so the row is filtered out. A bare
     * `roles.email = ?` would therefore silently drop every schedule with NO contact email from its
     * own search results, including one with a verified phone, which publicScheduleFilter()
     * explicitly admits. <=> is null-safe equality (MySQL only, which this app already is) and is
     * still index-usable. EXISTS never returns NULL.
     *
     * There is no `demo-%` subdomain arm any more. The Springfield rows DemoService seeds on those
     * subdomains are owned by the demo user and carry DEMO_EMAIL as their contact address, so the
     * email and owner arms already catch every one of them - while real schedules named before
     * cleanSubdomain() reserved the prefix still hold one (a "Demo Night" was handed `demo-night`;
     * it gets `demonight` now), and that arm hid them from search and, through isIndexableHost(),
     * would have de-indexed them.
     *
     * Deliberately NOT gated on config('app.hosted') the way is_demo_role() is. A gate would make
     * tests and production disagree about the same row, and the surfaces that call this are
     * nexus-only anyway (/search redirects to home on non-nexus selfhost).
     */
    public function scopeDemoContent($query)
    {
        return static::constrainDemoContent($query);
    }

    /**
     * scopeDemoContent() for any query that has `roles` in it, not only a Role builder: the sitemap
     * applies it inside an event_role join. Columns are qualified for that reason.
     */
    public static function constrainDemoContent($query)
    {
        // Demo content only exists where DemoService does: hosted (and the test suite, which
        // exercises the hosted paths). A selfhost install has no demo, so a schedule of its own
        // that happens to be called "simpsons" must not be treated as one - the same gate
        // is_demo_role() applies.
        if (! self::demoContentApplies()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function ($q) {
            $q->whereRaw('roles.email <=> ?', [\App\Services\DemoService::DEMO_EMAIL])
                ->orWhereRaw('roles.subdomain <=> ?', [\App\Services\DemoService::DEMO_ROLE_SUBDOMAIN])
                // A correlated subquery rather than a join: these queries scan, and reading
                // $role->user per row would be an N+1 across every schedule in the database.
                ->orWhereExists(fn ($u) => $u->select(DB::raw(1))
                    ->from('users')
                    ->whereColumn('users.id', 'roles.user_id')
                    ->where('users.email', \App\Services\DemoService::DEMO_EMAIL));
        });
    }

    /**
     * In-memory mirror of scopeDemoContent(). Keep the two in sync: RoleIndexabilityPredicateTest
     * holds them to the same answer for every arm.
     *
     * $demoOwnerId is the demo user's id, for a caller that walks many schedules: it turns the
     * owner arm into an id comparison instead of a users query per row. Pass 0 when there is no
     * demo user, which matches no row. Null, the default, reads $this->user instead - one query,
     * the one is_demo_role() has always cost a guest page.
     */
    public function isDemoContent(?int $demoOwnerId = null): bool
    {
        if (! self::demoContentApplies()) {
            return false;
        }

        if (self::equalsAsCollated($this->email, \App\Services\DemoService::DEMO_EMAIL)
            || self::equalsAsCollated($this->subdomain, \App\Services\DemoService::DEMO_ROLE_SUBDOMAIN)) {
            return true;
        }

        if (! $this->user_id) {
            return false;
        }

        return $demoOwnerId !== null
            ? (int) $this->user_id === $demoOwnerId
            : self::equalsAsCollated($this->user?->email, \App\Services\DemoService::DEMO_EMAIL);
    }

    /**
     * Whether demo content can exist on this install at all: hosted, or the test suite. The same
     * gate as is_demo_role().
     */
    private static function demoContentApplies(): bool
    {
        return (bool) (config('app.hosted') || config('app.is_testing'));
    }

    /**
     * String equality the way the utf8mb4_unicode_ci columns compare, so the PHP predicates agree
     * with their SQL twins: case-insensitive, and blind to trailing spaces (a PAD SPACE collation).
     */
    private static function equalsAsCollated(?string $value, string $expected): bool
    {
        return $value !== null && strcasecmp(rtrim($value, ' '), $expected) === 0;
    }

    /**
     * The set of schedules /admin/schedules shows by default: real, owned schedules,
     * never the demo ones.
     *
     * Shared with the search-subdomains autocomplete that feeds that page's filter box.
     * When the two drifted apart, the picker offered auto-created schedules (which have no
     * user_id - see EventRepo::saveEvent) that the table could never return, so picking one
     * and filtering produced an empty list.
     *
     * Kept owner-only rather than widened. AdminSchedulesUnverifiedCountTest exists to pin that
     * the Unverified card and the list it links to never disagree, and that card is owner-only;
     * ownerless auto-created rows are also the long tail, so defaulting to them would bury the
     * paying customers this page exists to manage. The admin opts into them with ?owner=.
     */
    private ?bool $hasRealOwnerMemo = null;

    /**
     * Whether the schedule's nominal owner actually runs it.
     *
     * `user_id IS NOT NULL` is NOT the same question, and reading it as such is a live bug:
     * ConvertsLocationToVenue::152 stamps the CURATOR's user_id onto every venue it invents while
     * attaching that user only as a `follower`, so an auto-created placeholder can carry somebody
     * else's id and still have nobody running it. RoleController::performMerge() has had this test
     * inline since the merge paths were written; this is that test, lifted so the rest of the app
     * can ask the same question the same way.
     *
     * The inverse, isClaimed(), is a THIRD thing again - it also demands a verified contact
     * channel, so it answers false for a real owner who simply never clicked the confirmation
     * link. Anything deciding whether a schedule is a placeholder wants THIS, not that; the
     * AdminSchedulesUnverifiedCountTest population is exactly the set the two disagree on.
     */
    /**
     * Drop the ownership memo when the row is re-read.
     *
     * refresh() replaces $attributes in place and never touches declared properties, so without
     * this the memo outlives the data it was computed from. ScheduleDeletionService::markDeleted()
     * ends with setRawAttributes() on the caller's instance, which is the same shape.
     */
    public function refresh()
    {
        $this->hasRealOwnerMemo = null;

        return parent::refresh();
    }

    public function hasRealOwner(): bool
    {
        // Memoised per instance. Two calls per act on the event page, times every act on the bill,
        // on the highest-traffic page type in the app. An instance property, never a static: a
        // static would survive RefreshDatabase and answer for the wrong row in the next test.
        if ($this->hasRealOwnerMemo !== null) {
            return $this->hasRealOwnerMemo;
        }

        return $this->hasRealOwnerMemo = (bool) $this->user_id && DB::table('role_user')
            ->where('role_id', $this->id)
            ->where('user_id', $this->user_id)
            ->whereIn('level', ['owner', 'admin'])
            ->exists();
    }

    /**
     * Query-level mirror of hasRealOwner(). The exact complement of ownerless(), which is what
     * lets adminListable() and adminListableUnclaimed() stay a partition: a row that satisfies
     * neither, or both, would be missing from the admin schedules page or counted twice by it.
     */
    public function scopeOwned($query)
    {
        return $query->whereNotNull('roles.user_id')
            ->whereExists(function ($sub) {
                $sub->selectRaw('1')
                    ->from('role_user')
                    ->whereColumn('role_user.role_id', 'roles.id')
                    ->whereColumn('role_user.user_id', 'roles.user_id')
                    ->whereIn('role_user.level', ['owner', 'admin']);
            });
    }

    /**
     * The rows a person may take ownership of: a placeholder, and nothing else.
     *
     * ownerless() alone is not enough and the gap is a takeover. It answers "nobody holds an owner
     * or admin pivot", which is ALSO true of a real customer's schedule whose pivot row has
     * drifted - the exact state CheckData::checkRoleOwnership() exists to detect and repair. That
     * row has a paying owner and a verified contact, and handing it to whoever proved control of
     * the address in roles.email would simply take it off them. A placeholder never carries either
     * verified stamp, because nothing verifies a contact nobody has claimed; a real schedule
     * always carries one, because that is half of what isClaimed() means.
     */
    /**
     * In-memory mirror of scopeClaimable(). Keep in sync with it.
     *
     * The two used to disagree, and the gap was reachable: AdminController::verifyScheduleEmail()
     * stamps a verified address on whatever row it is handed, including an ownerless one, one
     * click from the ?owner=unclaimed list. getClaimUrl() only asked about ownership, so it kept
     * handing out a URL whose buttons then bounced off claimTarget() to the marketing home page.
     */
    public function isClaimable(): bool
    {
        return ! $this->is_deleted
            && ! $this->email_verified_at
            && ! $this->phone_verified_at
            // The SCOPE's demo predicate, not is_demo_role(). They are different questions:
            // is_demo_role() asks about the demo ACCOUNT (and answers false outright on selfhost),
            // while notDemoSchedule() excludes the subdomain shapes. cleanSubdomain() no longer
            // hands out a demo- name, but placeholders created before it reserved the prefix
            // still hold one ("Demo 2" was given demo-2), so an ordinary placeholder could pass
            // this and fail the scope - rendering a page whose two buttons then bounced off
            // claimTarget() to the marketing home, which is the exact bug this predicate exists to
            // prevent, in mirror image.
            && $this->subdomain !== \App\Services\DemoService::DEMO_ROLE_SUBDOMAIN
            && ! str_starts_with((string) $this->subdomain, 'demo-')
            // Last, because it is the only clause that can cost a query.
            && ! $this->hasRealOwner();
    }

    public function scopeClaimable($query)
    {
        return $query->ownerless()
            ->whereNull('roles.email_verified_at')
            ->whereNull('roles.phone_verified_at')
            ->notDemoSchedule()
            ->where('roles.is_deleted', false);
    }

    /**
     * Query-level mirror of hasRealOwner(), negated: the placeholder rows.
     * Keep in sync with hasRealOwner().
     *
     * The whereNull arm is redundant against the NOT EXISTS (a null roles.user_id makes the
     * whereColumn comparison null, so the subquery matches nothing and NOT EXISTS is true) and is
     * kept because it states the common case and lets MySQL answer it without the subquery.
     */
    public function scopeOwnerless($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('roles.user_id')
                ->orWhereNotExists(function ($sub) {
                    $sub->selectRaw('1')
                        ->from('role_user')
                        ->whereColumn('role_user.role_id', 'roles.id')
                        ->whereColumn('role_user.user_id', 'roles.user_id')
                        ->whereIn('role_user.level', ['owner', 'admin']);
                });
        });
    }

    public function scopeAdminListable($query)
    {
        return $query->notDemoSchedule()->owned();
    }

    /**
     * The ownerless rows: venues and talent EventRepo::saveEvent() auto-creates while importing an
     * event. They take a subdomain via generateSubdomain() like any other schedule, which makes
     * them the likeliest squatter of a good name - and adminListable() hides every one of them.
     *
     * ownerless(), not whereNull('user_id'): the venues ConvertsLocationToVenue invents carry the
     * curator's id and were invisible to this list, which is where an admin goes to find exactly
     * that kind of row.
     */
    public function scopeAdminListableUnclaimed($query)
    {
        return $query->notDemoSchedule()->ownerless();
    }

    /** Both of the above. */
    public function scopeAdminListableAny($query)
    {
        return $query->notDemoSchedule();
    }

    // Query-level mirror of isClaimed(): has an owner + a verified contact channel.
    // Keep in sync with isClaimed().
    public function scopeClaimed($query)
    {
        return $query->whereNotNull('user_id')
            ->where(function ($q) {
                $q->whereNotNull('email_verified_at')
                    ->orWhereNotNull('phone_verified_at');
            });
    }

    /**
     * Whether the schedule has an owner and a contact channel that was both given and verified:
     * an email with email_verified_at, or a phone with phone_verified_at.
     *
     * Stricter than isClaimed(), which reads only the dates. A verified_at with no address beside
     * it is not a channel anybody can be reached on, and the guest layout has never indexed such a
     * page. This is that rule, lifted out of the layout so the sitemap asks the same question -
     * it used to ask isClaimed()'s, and submitted 31 event URLs whose pages answered noindex.
     * federationEligible() spells the same condition out for its own reasons.
     */
    public function hasVerifiedContact(): bool
    {
        return (bool) $this->user_id
            && ((filled($this->email) && $this->email_verified_at !== null)
                || (filled($this->phone) && $this->phone_verified_at !== null));
    }

    /**
     * hasVerifiedContact() in SQL, for any query with `roles` in it. `<> ''` because an empty
     * contact is no contact, which filled() also says.
     */
    public static function constrainVerifiedContact($query)
    {
        return $query->whereNotNull('roles.user_id')
            ->where(function ($q) {
                $q->where(fn ($e) => $e->whereNotNull('roles.email')
                    ->where('roles.email', '<>', '')
                    ->whereNotNull('roles.email_verified_at'))
                    ->orWhere(fn ($p) => $p->whereNotNull('roles.phone')
                        ->where('roles.phone', '<>', '')
                        ->whereNotNull('roles.phone_verified_at'));
            });
    }

    /**
     * Whether this schedule's guest pages may be indexed: the one rule the page's robots meta
     * (layouts/app-guest.blade.php) and both sitemaps apply, so the sitemap never submits a URL
     * the page then refuses. It exists, is not deleted, has a verified contact, and is not demo
     * content.
     *
     * Not is_demo_role(), which asks about the demo ACCOUNT and has ~70 call sites (free Pro, ad
     * suppression, suppressed email) - see scopeDemoContent() for why the two differ.
     *
     * $demoOwnerId: see isDemoContent().
     */
    public function isIndexableHost(?int $demoOwnerId = null): bool
    {
        return $this->exists
            && ! $this->is_deleted
            && $this->hasVerifiedContact()
            && ! $this->isDemoContent($demoOwnerId);
    }

    /**
     * isIndexableHost() in SQL, for any query with `roles` in it - including the event_role join
     * the sitemap binds it into, so it holds for the same pivot row as is_accepted. Keep the two
     * in sync: RoleIndexabilityPredicateTest holds them to the same answer.
     */
    public static function constrainIndexableHost($query)
    {
        return $query->where('roles.is_deleted', false)
            ->where(fn ($q) => static::constrainVerifiedContact($q))
            ->whereNot(fn ($q) => static::constrainDemoContent($q));
    }

    public function scopeIndexableHost($query)
    {
        return static::constrainIndexableHost($query);
    }

    /**
     * The role-column half of what makes an event federatable: opted in, real, and
     * verified.
     *
     * Extracted from FederationService::federatableQuery()'s whereHas so the settings
     * preview can apply the SAME rule to a standalone Role query. Copying it would
     * have been two definitions of "will be shared" drifting apart, which on this
     * feature means telling an operator their schedule is published when it is not.
     *
     * Stricter than scopeClaimed(): this also requires the contact column matching the
     * verification timestamp to be present, because a verified_at with no address is
     * not a channel anyone can be reached on.
     *
     * The pivot condition (event_role.is_accepted) is NOT here: it is only in scope
     * when the caller reached roles through the event relation, so each call site adds
     * it. The demo exclusion is not here either - federatableQuery() drops any event
     * carrying a demo role, so a demo role can never satisfy the query.
     *
     * $includeUndecided widens the tri-state to include null. Only an explicit yes
     * qualifies a schedule to publish; the adoption prompt is the sole caller that
     * counts "not asked yet" as willing.
     */
    public function scopeFederationEligible($query, bool $includeUndecided = false)
    {
        return $query
            ->where(function ($f) use ($includeUndecided) {
                $f->where('roles.federation_enabled', true);

                if ($includeUndecided) {
                    $f->orWhereNull('roles.federation_enabled');
                }
            })
            ->where('roles.is_deleted', false)
            ->where('roles.is_unlisted', false)
            ->whereNotNull('roles.user_id')
            ->where(function ($r) {
                $r->where(function ($x) {
                    $x->whereNotNull('roles.email')->whereNotNull('roles.email_verified_at');
                })->orWhere(function ($x) {
                    $x->whereNotNull('roles.phone')->whereNotNull('roles.phone_verified_at');
                });
            });
    }

    public function hasConfiguredBackground(): bool
    {
        return match ($this->background) {
            'gradient' => filled($this->background_colors),
            'solid' => filled($this->background_color),
            'image' => filled($this->background_image) || filled($this->background_image_url),
            default => false,
        };
    }

    /*
     * The style values the guest pages print into CSS, sanitized where they are READ.
     *
     * The gradient, the solid colour and the background image go into the layout's <style> block,
     * the font into style="" attributes, and the accent into both and into Vue :style expressions,
     * which the runtime compiler evaluates as JavaScript. No write path validates all of them - the
     * web form checks a few, a backup restore none - so a stored value could close its declaration
     * and restyle the page, including, through $otherRole, an event page of SOMEONE ELSE's schedule
     * that this one appears on. Reading them here gives every reader a safe value: this schedule's
     * pages, other schedules' event pages, a restored row and any view written later.
     *
     * Only reads change. The column keeps what was written, and getAttributes(), getRawOriginal()
     * and every dirty check still see that.
     */

    /** Every hex token, rejoined with ", "; null when there is none, which reads as unconfigured. */
    public function getBackgroundColorsAttribute($value): ?string
    {
        // Every preset in storage/gradients.json passes as stored, the one whose colours have no #
        // included (RoleStyleAttributesTest walks the file).
        $colors = array_filter(
            array_map('trim', explode(',', (string) $value)),
            fn (string $color) => preg_match('/^#?[0-9A-Fa-f]{3,8}\z/', $color) === 1
        );

        return $colors ? implode(', ', $colors) : null;
    }

    public function getBackgroundColorAttribute($value): ?string
    {
        return self::cssHexColor($value);
    }

    /** Null reads as "no accent": the guest views fall back to their default for it. */
    public function getAccentColorAttribute($value): ?string
    {
        return self::cssHexColor($value);
    }

    public function getBackgroundRotationAttribute($value): ?int
    {
        return $value === null ? null : (int) $value;
    }

    /** A built-in background's name: public/images/backgrounds/{name}.webp. */
    public function getBackgroundImageAttribute($value): ?string
    {
        return is_string($value) && preg_match('/^[A-Za-z0-9_-]+\z/', $value) ? $value : null;
    }

    /** Every value in storage/fonts.json passes. Views print it with the underscores as spaces. */
    public function getFontFamilyAttribute($value): ?string
    {
        return is_string($value) && preg_match('/^[A-Za-z0-9_ ]{1,100}\z/', $value) ? $value : null;
    }

    /** What the settings form accepts and sponsorBackground() reads: transparent or #rrggbb. */
    public function getSponsorBackgroundColorAttribute($value): ?string
    {
        return is_string($value) && preg_match('/^(?:transparent|#[0-9A-Fa-f]{6})\z/', $value) ? $value : null;
    }

    private static function cssHexColor($value): ?string
    {
        return is_string($value) && preg_match('/^#(?:[0-9A-Fa-f]{3}|[0-9A-Fa-f]{6}|[0-9A-Fa-f]{8})\z/', $value) ? $value : null;
    }

    public function isEditableBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->isEditor($this->subdomain)) {
            return true;
        }

        // Unclaimed roles can be cleaned up by anyone who follows them.
        // Mirrors the rule already used in GeminiUtils for venue_is_editable.
        return ! $this->isClaimed() && $user->isFollowing($this->subdomain);
    }

    /**
     * Push this schedule's events onto the curators its owner picked in default_curator_ids.
     *
     * $actingUser is the authenticated submitter, so a curator they run accepts immediately
     * instead of queueing a request they would only have to approve themselves. Optional so
     * an unauthenticated caller (webhooks, console) simply gets no membership credit.
     */
    public function autoCurateEvent(Event $event, ?User $actingUser = null): void
    {
        $curatorIds = $this->default_curator_ids;
        if (empty($curatorIds)) {
            return;
        }

        foreach ($curatorIds as $curatorId) {
            $curator = Role::where('id', $curatorId)->where('is_deleted', false)->where('type', 'curator')->first();
            if (! $curator) {
                continue;
            }

            if ($event->roles()->where('roles.id', $curator->id)->exists()) {
                continue;
            }

            $isAccepted = $curator->autoAcceptsEventFrom($actingUser, $this);

            $event->roles()->attach($curator->id, ['is_accepted' => $isAccepted ?: null]);
        }
    }

    public function getHeaderImageUrlAttribute($value)
    {
        if (! $value) {
            return '';
        }

        // Handle demo images in public/images/demo/
        if (str_starts_with($value, 'demo_')) {
            return url('/images/demo/'.$value);
        }

        if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
            return 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$value;
        } elseif (in_array(config('filesystems.default'), ['local', 'public'])) {
            return url('/storage/'.$value);
        } else {
            return $value;
        }
    }

    /**
     * Queue the resized WebP derivatives of one of this schedule's images (a slot of
     * imageVariantSlots()), if it has one worth resizing.
     *
     * afterCommit() for the same reason as Event::queueImageVariants(): schedules are saved inside
     * transactions (calendar sync, merges), and on the `sync` queue the job would otherwise do
     * S3 reads and writes inside the open transaction. The job itself swallows every failure on
     * `sync`, so it cannot turn a successful save into a 500.
     */
    protected static function queueImageVariants(self $model, string $slot): void
    {
        $raw = $model->imageVariantSource($slot);

        // demo_ images ship in the repo; a legacy http value is not ours to resize.
        if ($raw === null || str_starts_with($raw, 'demo_') || str_starts_with($raw, 'http')) {
            return;
        }

        GenerateRoleImageVariants::dispatch($model->id, $raw, $slot)->afterCommit();
    }

    public function imageVariantSourceColumn(): string
    {
        return 'profile_image_url';
    }

    /**
     * The profile photo ('default', the card and wall sizes), and the two wide images an owner can
     * upload: the banner header and the page background, at page widths.
     *
     * Uploads only. header_image and background_image name BUILT-IN art under public/images,
     * which ships as WebP already sized for the page, so neither is a slot; headerImageUrl() and
     * backgroundImageUrl() serve those files directly.
     *
     * A column each, so the two jobs one save can queue (a new header AND a new background) write
     * different columns: recordImageVariants() rewrites a whole column, guarded on one source.
     */
    public function imageVariantSlots(): array
    {
        return [
            'default' => ['profile_image_url', 'image_variants', ImageUtils::VARIANT_WIDTHS],
            'header' => ['header_image_url', 'header_image_variants', ImageUtils::BANNER_VARIANT_WIDTHS],
            'background' => ['background_image_url', 'background_image_variants', ImageUtils::BANNER_VARIANT_WIDTHS],
        ];
    }

    /** The pixel size of every built-in header under public/images/headers. */
    public const BUILT_IN_HEADER_SIZE = [1536, 768];

    /**
     * The URL of the header this schedule shows, or null when it shows none.
     *
     * Mirrors role/partials/headers/banner.blade.php: 'none' and 'logos' (the logo wall) are no
     * image; any other header_image names a built-in header, served as its bundled WebP; a blank
     * header_image with an upload in header_image_url is the owner's own header, which the edit
     * form's "custom" option saves.
     *
     * $width asks for a resized derivative of an upload (ImageUtils::BANNER_VARIANT_WIDTHS), and
     * falls back to the original until one is recorded, so the URL is always renderable. A
     * built-in header ignores it: the bundled file is already a 1536px WebP.
     */
    public function headerImageUrl(?int $width = null): ?string
    {
        $builtIn = $this->header_image;

        if (in_array($builtIn, ['none', 'logos'], true)) {
            return null;
        }

        if (filled($builtIn)) {
            return asset('images/headers/'.$builtIn.'.webp');
        }

        if (! $this->imageVariantSource('header')) {
            return null;
        }

        return ($width ? $this->imageVariantUrl($width, 'header') : null) ?: $this->header_image_url;
    }

    /**
     * The header's size, [width, height], for an <img>'s width and height attributes: fixed for a
     * built-in header, recorded by the pipeline for an upload, null when neither is known.
     *
     * @return array{0: int, 1: int}|null
     */
    public function headerImageDimensions(): ?array
    {
        if (! $this->headerImageUrl()) {
            return null;
        }

        return filled($this->header_image)
            ? self::BUILT_IN_HEADER_SIZE
            : $this->imageSourceDimensions('header');
    }

    /**
     * The URL of this schedule's background image, or null when its background is not an image.
     *
     * The same rules as headerImageUrl(): background_image names a built-in background, served as
     * its bundled WebP (already sized for a phone); a blank one with an upload in
     * background_image_url is the owner's own, as a derivative at $width when one is recorded and
     * the original otherwise.
     */
    public function backgroundImageUrl(?int $width = null): ?string
    {
        if ($this->background !== 'image') {
            return null;
        }

        if (filled($this->background_image)) {
            return asset('images/backgrounds/'.$this->background_image.'.webp');
        }

        if (! $this->imageVariantSource('background')) {
            return null;
        }

        return ($width ? $this->imageVariantUrl($width, 'background') : null) ?: $this->background_image_url;
    }

    /**
     * The profile photo's URL, as a resized derivative when $width is given and one is recorded
     * (see ImageUtils::VARIANT_WIDTHS), else the original - so the result is always renderable.
     * No width means the original, for full-size consumers. '' when there is no photo, like the
     * accessor.
     */
    public function getProfileImageUrl(?int $width = null): string
    {
        if ($width && $this->imageVariantSource() && ($variant = $this->imageVariantFilename($width))) {
            return ImageUtils::variantUrl($variant);
        }

        return $this->profile_image_url;
    }

    /**
     * The picture a link preview of this schedule shows (og:image, twitter:image): the header the
     * owner uploaded, else their profile photo, else the background they uploaded. A wide header
     * is what a large card is shaped for, which a square logo is not.
     *
     * Uploads only, and only the one the owner has selected. header_image names a BUILT-IN header
     * (a public/images/headers file), 'logos' or 'none'; an upload in header_image_url is the
     * schedule's header only while header_image is blank, which is what the edit form's "custom"
     * option saves. The background works the same way through background_image, and only while
     * the background is an image at all. Built-in art is not the owner's picture, and none of ours
     * ever is (docs/BRANDING_MATRIX.md rule 6): with no upload this is null and the card degrades
     * to the owner's own text.
     *
     * width and height only when known: the size the image pipeline recorded for the chosen upload
     * (HasImageVariants::imageSourceDimensions()), else whatever SeoUtils::imageDimensions() can
     * read from a file this app serves itself.
     *
     * @return array{url: string, width?: int, height?: int}|null
     */
    public function shareImage(): ?array
    {
        $header = blank($this->header_image) ? $this->header_image_url : '';
        $background = ($this->background === 'image' && blank($this->background_image))
            ? $this->background_image_url
            : '';

        return match (true) {
            (bool) $header => SeoUtils::imageObject($header, $this->imageSourceDimensions('header')),
            (bool) $this->profile_image_url => SeoUtils::imageObject($this->profile_image_url, $this->imageSourceDimensions()),
            (bool) $background => SeoUtils::imageObject($background, $this->imageSourceDimensions('background')),
            default => null,
        };
    }

    public function getProfileImageUrlAttribute($value)
    {
        if (! $value) {
            return '';
        }

        // Handle demo images in public/images/demo/
        if (str_starts_with($value, 'demo_')) {
            return url('/images/demo/'.$value);
        }

        if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
            return 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$value;
        } elseif (in_array(config('filesystems.default'), ['local', 'public'])) {
            return url('/storage/'.$value);
        } else {
            return $value;
        }
    }

    public function getBackgroundImageUrlAttribute($value)
    {
        if (! $value) {
            return '';
        }

        // Handle demo images in public/images/demo/
        if (str_starts_with($value, 'demo_')) {
            return url('/images/demo/'.$value);
        }

        if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
            return 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$value;
        } elseif (in_array(config('filesystems.default'), ['local', 'public'])) {
            return url('/storage/'.$value);
        } else {
            return $value;
        }
    }

    public static function cleanSubdomain($name, $fallbackEnglish = null)
    {
        $subdomain = Str::slug($name);

        // Significant content lost during slugification => a non-Latin script (Hebrew/CJK/Thai...)
        // that Str::slug can't transliterate. Resolve a readable slug in order: caller-supplied
        // English -> Gemini translation (descriptive names) -> transliteration (proper names) ->
        // random (via the <= 2 guard below).
        if (self::isLossySlug($name, $subdomain)) {
            $resolved = '';

            // 1) Caller-supplied English name, no API round-trip. Mirrors
            //    SlugPatternUtils::defaultSlug() preferring an existing *_en before translating.
            if ($fallbackEnglish) {
                $enSlug = Str::slug($fallbackEnglish);
                if (strlen($enSlug) > 2 && ! self::isLossySlug($fallbackEnglish, $enSlug)) {
                    $resolved = $enSlug;
                }
            }

            // 2) Translate descriptive names ("private pool" -> private-pool). Accept only when the
            //    translation is itself Latin; a proper noun comes back unchanged/non-Latin (still
            //    lossy) and falls through to transliteration.
            if (! $resolved) {
                try {
                    $translated = GeminiUtils::translate($name, 'auto', 'en', [], ['kind' => 'name']);
                    if ($translated) {
                        $translatedSlug = Str::slug($translated);
                        if (strlen($translatedSlug) > 2 && ! self::isLossySlug($translated, $translatedSlug)) {
                            $resolved = $translatedSlug;
                        }
                    }
                } catch (\Exception $e) {
                    \Log::warning('Subdomain translation failed for: '.$name.' - '.$e->getMessage());
                }
            }

            // 3) Romanize the ORIGINAL name (רותם רם -> rwtm-rm, 東京 -> dong-jing).
            if (! $resolved) {
                try {
                    $romanized = Str::slug(self::transliterateToAscii($name));
                    if (strlen($romanized) > 2) {
                        $resolved = $romanized;
                    }
                } catch (\Exception $e) {
                    \Log::warning('Subdomain transliteration failed for: '.$name.' - '.$e->getMessage());
                }
            }

            if ($resolved) {
                $subdomain = $resolved;
            }
            // else: $subdomain stays the (empty) lossy slug -> <= 2 guard below -> random.
        }

        // Unconditional, NOT hosted-only. The list's own entries explain why: several are there
        // because "selfhost serves tenants from the same path space", and 'schedule-transfer' was
        // added for a route registered ahead of the selfhost /{subdomain} catch-all - so gating the
        // check on config('app.hosted') skipped it in the exact deployment mode it was written for.
        // On hosted a reserved name is a DNS subdomain that could not shadow an app-domain route
        // anyway; on selfhost it takes the path outright.
        //
        // RoleController::update() only calls this when new_subdomain actually differs from the
        // stored value, so a schedule already holding one of these keeps it rather than being
        // silently renamed on its next unrelated save.
        //
        // demo- is the Springfield demo's namespace. Its hourly reset deletes and recreates the
        // demo-* schedules it owns, and the admin lists, the plan jobs and federation all read
        // demo-% as "demo", so a real schedule should not be handed one. Rewritten rather than
        // refused, because refusing here means Str::random(8): "Demo Day" becomes demoday, and
        // generateSubdomain() adds a number if that is taken. The same "only a change" rule as
        // above keeps an existing demo-night where it is; RoleUpdateRequest and
        // AdminScheduleDetailsRequest reject a change to one with a message.
        if (str_starts_with($subdomain, 'demo-')) {
            $subdomain = 'demo'.substr($subdomain, 5);
        }

        if (in_array($subdomain, self::RESERVED_SUBDOMAINS, true)) {
            $subdomain = '';
        }

        if (strlen($subdomain) <= 2) {
            return strtolower(\Str::random(8));
        }

        // Truncate to max 50 characters, ensuring we don't cut in the middle of a hyphenated word
        if (strlen($subdomain) > 50) {
            $subdomain = substr($subdomain, 0, 50);
            $subdomain = rtrim($subdomain, '-');
        }

        return $subdomain;
    }

    /**
     * True when Str::slug dropped more than half of the source's letters/digits - the signal that a
     * non-Latin script survived slugification only as separators (or nothing at all).
     */
    /** Shared with Group::cleanSlug(), which needs the same "did slugifying lose the name" test. */
    public static function isLossySlug($source, $slug): bool
    {
        $sourceChars = mb_strlen(preg_replace('/[^\p{L}\p{N}]/u', '', (string) $source));
        $slugChars = strlen(preg_replace('/[^a-z0-9]/', '', (string) $slug));

        return $sourceChars > 0 && $slugChars < $sourceChars / 2;
    }

    /**
     * ICU romanization of any script to lowercase ASCII (Hebrew רותם -> "rwtm", 東京 -> "dong jing").
     * Returns '' when ext-intl is unavailable or ICU fails, so callers degrade to a random subdomain
     * instead of fataling.
     */
    public static function transliterateToAscii(string $name): string
    {
        if (! class_exists(\Transliterator::class)) {
            return '';
        }

        $transliterator = \Transliterator::create('Any-Latin; Latin-ASCII; Lower()');
        if (! $transliterator) {
            return '';
        }

        $result = $transliterator->transliterate($name);

        return $result === false ? '' : $result;
    }

    public static function generateSubdomain($name = '', $fallbackEnglish = null)
    {
        if (! $name) {
            $name = strtolower(\Str::random(8));
        }

        $subdomain = self::cleanSubdomain($name, $fallbackEnglish);

        // Check variations of the subdomain
        $parts = explode('-', $subdomain);
        $variations = [];

        // Build variations from left to right (live, live-music, live-music-shop)
        $current = '';
        foreach ($parts as $i => $part) {
            $current = $current ? $current.'-'.$part : $part;
            $variations[] = $current;
        }

        // Check each variation in order - use the first available one
        foreach ($variations as $variation) {
            // cleanSubdomain() vetted only the WHOLE name, so a prefix could still be reserved:
            // "App Night" was handed "app", "Events at the Park" "events".
            if (in_array($variation, self::RESERVED_SUBDOMAINS, true)) {
                continue;
            }

            if (! self::where('subdomain', $variation)->exists()) {
                $subdomain = $variation;
                break;
            }
        }

        // If no variation is available, use the original subdomain with a number suffix
        $originalSubdomain = $subdomain;
        $count = 1;

        while (self::where('subdomain', $subdomain)->exists()) {
            $subdomain = $originalSubdomain.$count;
            $count++;
        }

        return $subdomain;
    }

    /** How much of the released name is the id suffix allowed to cost. Matches the cap in cleanSubdomain(). */
    private const SUBDOMAIN_MAX = 50;

    /**
     * The name this schedule takes when it gives its own up, so another schedule can have it.
     *
     * roles.subdomain is UNIQUE, so releasing a name is necessarily a RENAME - the row cannot keep
     * the name and let someone else have it. Computes only; the caller saves.
     *
     * The primary key is the disambiguator rather than a counter or a random string: no other row
     * can have this id, so the result is unique against the index by construction, AND it is
     * deterministic, so a double-submitted form recomputes the same value instead of chaining
     * another suffix onto it.
     *
     * Deliberately NOT routed through cleanSubdomain(), for three reasons that each look like a
     * bug fix from the outside:
     *   - it truncates to 50 AFTER we appended the id, which would silently destroy the uniqueness
     *     the id is here to provide;
     *   - it replaces anything <= 2 characters with Str::random(8), turning a recognizable name
     *     into noise;
     *   - for a name Str::slug mangles it calls GeminiUtils::translate(), so releasing a
     *     Hebrew-named schedule would fire a live AI request on an admin's click.
     */
    public function releasedSubdomain(): string
    {
        // The ORIGINAL name where we have it, so re-releasing a row whose Restore could not
        // reclaim its name yields foo-deleted-42 again rather than foo-deleted-42-deleted-42.
        $base = $this->subdomain_before_delete ?: $this->subdomain;

        $n = 1;
        do {
            $suffix = '-deleted-'.$this->id.($n > 1 ? '-'.$n : '');
            // Recomputed each pass: truncating once and then appending the disambiguator would
            // push the result back over the cap. Subdomains longer than 50 chars were
            // grandfathered in before the cap existed, so this genuinely has to truncate.
            $stem = rtrim(substr((string) $base, 0, self::SUBDOMAIN_MAX - strlen($suffix)), '-');
            if ($stem === '') {
                $stem = 'schedule';
            }
            $candidate = $stem.$suffix;
            $n++;
        } while (self::where('subdomain', $candidate)->where('id', '!=', $this->id)->exists());

        return $candidate;
    }

    /**
     * Repoint every curator's approve-list entry for a subdomain that is changing hands.
     *
     * roles.approved_subdomains holds SUBDOMAIN STRINGS, and autoAcceptsEventFrom() reads it to
     * decide whether a submitted event goes live on the curator's public page immediately or waits
     * in Requests. So a name that is freed and then claimed by someone else carries the previous
     * holder's auto-accept trust to a stranger. Releasing passes null (the schedule is gone,
     * nothing is left to trust); renaming passes the new name (same schedule, new address).
     *
     * The column is TEXT cast to array, not a native JSON column, so whereJsonContains() is not
     * reliable on it. The LIKE only narrows the candidate set; the exact match is done in PHP.
     *
     * @return int number of schedules whose list changed
     */
    public static function rewriteApprovedSubdomainReferences(string $from, ?string $to): int
    {
        $escaped = str_replace(['%', '_'], ['\%', '\_'], $from);

        $changed = 0;

        self::whereNotNull('approved_subdomains')
            ->where('approved_subdomains', 'like', '%"'.$escaped.'"%')
            ->get()
            ->each(function (self $role) use ($from, $to, &$changed) {
                $list = $role->approved_subdomains;

                if (! is_array($list) || ! in_array($from, $list, true)) {
                    return;
                }

                $updated = [];
                foreach ($list as $entry) {
                    $entry = $entry === $from ? $to : $entry;
                    if ($entry !== null && ! in_array($entry, $updated, true)) {
                        $updated[] = $entry;
                    }
                }

                // Query-builder update, NOT $role->save(): this runs inside
                // ScheduleDeletionService::markDeleted()'s transaction, holding a row lock, and
                // Role's `saving` hook geocodes through a 10-second Http::get() whenever a row's
                // stored geo_address does not match its composed address - which is any row whose
                // address never geocoded successfully. Network I/O under a lock is the shape that
                // already caused a live 1213 on this table. It also avoids re-rendering
                // description_html, sanitising custom_css and recomputing the *_normalized columns
                // on somebody else's row to change one column. Same reasoning as the federation
                // fan-out in boot(): a query-builder update fires no model events.
                //
                // The array cast does not apply to a query-builder write, so encode by hand. Null
                // rather than [] when the list empties, matching RoleController::update().
                //
                // updated_at is deliberately left alone. SitemapController uses roles.updated_at
                // as the <lastmod> for that schedule's guest page, and dropping a name from an
                // approve list changes nothing it publishes - only whether a FUTURE submission
                // from a name it no longer trusts would auto-accept.
                self::whereKey($role->id)->update([
                    'approved_subdomains' => $updated ? json_encode($updated) : null,
                ]);
                $changed++;
            });

        return $changed;
    }

    public function decodeLinks($field)
    {
        $links = json_decode($this->{$field} ?? '[]');

        if (! is_array($links)) {
            return [];
        }

        return array_values(array_filter($links, function ($link) {
            return $link && isset($link->url) && $link->url !== '';
        }));
    }

    /** @var array<int, string>|null */
    private ?array $shortLinkSlugs = null;

    private ?string $shortLinkSlugsFor = null;

    /**
     * The short-link slug each of this schedule's social links answers to, keyed by its position
     * in decodeLinks('social_links') so every renderer and the resolver agree.
     *
     * Seeded with the other things that own a first path segment here: sub-schedule slugs, and
     * every literal route registered ahead of the /{slug} catch-all. A suggestion equal to one of
     * those could never resolve, so it is never offered as live.
     *
     * Memoized on the instance rather than statically - a static would survive RefreshDatabase
     * and leak one test's links into the next - and keyed on the column, so code that edits
     * social_links and re-renders from the same instance cannot be served the old map.
     *
     * @return array<int, string>
     */
    public function shortLinkSlugs(): array
    {
        $key = (string) $this->social_links;

        if ($this->shortLinkSlugs !== null && $this->shortLinkSlugsFor === $key) {
            return $this->shortLinkSlugs;
        }

        $this->shortLinkSlugsFor = $key;

        $this->loadMissing('groups');

        $taken = array_merge(
            $this->groups->pluck('slug')->filter()->map(fn ($s) => strtolower($s))->all(),
            UrlUtils::reservedPathSlugs(),
        );

        return $this->shortLinkSlugs = UrlUtils::shortLinkSlugs(
            $this->decodeLinks('social_links'),
            $taken,
        );
    }

    /**
     * The href of the social link at $index in decodeLinks('social_links'): its short link on this
     * schedule when it has one, else the link itself. Null - leave the icon out - when the link is
     * not one a browser should open (UrlUtils::safeHref()), since the short-link resolver refuses
     * those and their short address would be a dead end.
     */
    public function socialLinkHref(object $link, int $index): ?string
    {
        $href = UrlUtils::safeHref($link->url ?? null);
        $slug = $this->shortLinkSlugs()[$index] ?? '';

        return $href !== null && $slug !== '' ? $this->getGuestUrl().'/'.$slug : $href;
    }

    public function getFirstVideoUrl()
    {
        if (! $this->youtube_links) {
            return '';
        }

        $links = json_decode($this->youtube_links);

        if (is_array($links) && count($links) >= 1 && isset($links[0]->url)) {
            return $links[0]->url;
        }

        return '';
    }

    public function getSecondVideoUrl()
    {
        if (! $this->youtube_links) {
            return '';
        }

        $links = json_decode($this->youtube_links);

        if (is_array($links) && count($links) >= 2 && isset($links[1]->url)) {
            return $links[1]->url;
        }

        return '';
    }

    public function getVideoColumns()
    {
        if (! $this->youtube_links) {
            return 0;
        }

        $links = json_decode($this->youtube_links);

        if (! is_array($links)) {
            return 0;
        }

        $count = count($links);

        if ($count == 1) {
            return 1;
        } elseif ($count == 2 || $count == 4) {
            return 2;
        } else {
            return 3;
        }
    }

    public function getVideoHeight()
    {
        if (! $this->youtube_links) {
            return 0;
        }

        $count = $this->getVideoColumns();

        if ($count == 1) {
            return 500;
        } else {
            return 300;
        }

    }

    public function getDisplayName($translate = true)
    {
        if ($translate) {
            if ($this->translatedName()) {
                return $this->translatedName();
            } else {
                return $this->translatedAddress1();
            }
        } else {
            if ($this->name) {
                return $this->name;
            } else {
                return $this->address1;
            }
        }
    }

    /**
     * The public address of a schedule nobody has claimed yet.
     *
     * A separate method rather than relaxing getGuestUrl(), whose '' for an unclaimed schedule is
     * load-bearing falsiness: SitemapController::writeSchedules skips on it, Event::getGuestUrlData()
     * picks a subdomain by it, and two guest views print a fallback when it is empty. Widening it
     * would put placeholders into the sitemap and behind other schedules' canonical URLs.
     *
     * Returns '' for anything that has an owner, is deleted, or is a demo row, so a caller can use
     * it the same falsy way.
     */
    public function getClaimUrl(): string
    {
        if (! $this->isClaimable()) {
            return '';
        }

        return route('role.view_guest', ['subdomain' => $this->subdomain]);
    }

    public function getGuestUrl($useCustomDomain = false)
    {
        if (! $this->isClaimed()) {
            return '';
        }

        if ($useCustomDomain && $this->custom_domain) {
            if ($this->custom_domain_mode !== 'direct' || $this->custom_domain_status === 'active') {
                return $this->custom_domain;
            }
        }

        return route('role.view_guest', ['subdomain' => $this->subdomain]);
    }

    /**
     * Whether the schedule is served directly on its custom domain (DigitalOcean direct mode,
     * provisioned and active). This is the only mode where the custom domain renders the page
     * itself, so it is the only mode whose URL should be used as the SEO canonical. Redirect
     * mode 301s the custom domain to the subdomain, so the subdomain stays canonical there.
     */
    public function servesOnCustomDomain(): bool
    {
        return $this->custom_domain
            && $this->custom_domain_mode === 'direct'
            && $this->custom_domain_status === 'active';
    }

    /**
     * The schedule's canonical guest URL: the custom domain when served directly on it, else the
     * subdomain route. Passing the strict result as the flag means getGuestUrl's broad gate is
     * only ever reached in the direct+active case, where both gates agree.
     */
    public function getCanonicalUrl()
    {
        return $this->getGuestUrl($this->servesOnCustomDomain());
    }

    /**
     * The query a guest URL of this schedule carries when shown in $lang: "?lang={code}" for its
     * second language, nothing for its first. The primary language lives on the clean URL - the one
     * the sitemap submits and people link to - so only the alternate carries the parameter.
     *
     * The one definition layouts/app-guest.blade.php prints on the canonical tag and the JSON-LD
     * prints as each node's url (Event::schemaNode(), schemaNode()), so the two cannot drift.
     */
    public function langQuerySuffix(?string $lang): string
    {
        return ($lang && $this->offersTranslation() && $lang !== $this->language_code) ? '?lang='.$lang : '';
    }

    /**
     * Whether the schedule shows its phone number: given, switched on, and verified. The rule the
     * guest header has always applied, shared with the structured data.
     */
    public function showsPhone(): bool
    {
        return (bool) ($this->phone && $this->show_phone && $this->phone_verified_at);
    }

    /*
     * Structured data. A schedule is a node of its own on its page (schemaNode()) and appears in
     * every event's node as its venue, organizer or performer (Event::schemaNode()). All of them
     * share one @id, "{canonical}#schedule", so a crawler reads one entity however it arrives.
     */

    /** How many upcoming events a schedule's node lists. */
    public const SCHEMA_UPCOMING_LIMIT = 10;

    /**
     * The canonical URL structured data may link this schedule at: claimed and not deleted. An
     * unclaimed placeholder's page is not its own (getGuestUrl() is '' for it), and a deleted
     * schedule's page is a 404.
     */
    public function schemaCanonicalUrl(): ?string
    {
        if (! $this->isClaimed() || $this->is_deleted) {
            return null;
        }

        return $this->getCanonicalUrl() ?: null;
    }

    /** "{canonical}#schedule", or null when the schedule has no canonical to hang it on. */
    public function schemaId(): ?string
    {
        $url = $this->schemaCanonicalUrl();

        return $url ? $url.'#schedule' : null;
    }

    /**
     * This schedule's node on its own guest pages.
     *
     * A venue is an EventVenue (a Place: address, geo, a telephone only where the page shows one),
     * talent a Person, a curator an Organization; venues and curators carry their profile picture
     * as their logo, which a Person has no property for. Named and described in $lang, the page's
     * language; the url is the page's canonical, ?lang= included (langQuerySuffix()).
     *
     * No inLanguage, which schema.org defines on creative works and events, not on a place, a
     * person or an organization. sameAs is withheld for an unverified schedule ($isUnverified), so
     * a page nobody has vouched for cannot seed structured-data backlinks.
     *
     * $upcoming is EventRepo::upcomingForGuest(): up to SCHEMA_UPCOMING_LIMIT of those events with a
     * location are listed as compact Event nodes - "event" on a venue or an organization,
     * "performerIn" on a person - at their undated canonical URLs, dated by the occurrence the list
     * computed. Everything they read is already loaded with them, so the list adds no queries.
     *
     * @param  \Illuminate\Support\Collection<int, array{event: Event, date: string}>  $upcoming
     * @return array<string, mixed>
     */
    public function schemaNode(string $lang, bool $isUnverified, \Illuminate\Support\Collection $upcoming): array
    {
        $type = $this->isVenue() ? 'EventVenue' : ($this->isTalent() ? 'Person' : 'Organization');
        $canonical = $this->schemaCanonicalUrl();

        $node = ['@context' => 'https://schema.org', '@type' => $type];

        if ($canonical) {
            $node['@id'] = $canonical.'#schedule';
        }

        $node['name'] = SeoUtils::cleanText($this->nameInLanguage($lang));

        if (($description = $this->schemaDescription($lang)) !== null) {
            $node['description'] = $description;
        }

        if ($canonical) {
            $node['url'] = $canonical.$this->langQuerySuffix($lang);
        }

        if ($type !== 'Person' && ($logo = SeoUtils::schemaImageObject(SeoUtils::imageObject($this->profile_image_url ?: null, $this->imageSourceDimensions())))) {
            $node['logo'] = $logo;
        }

        if ($image = SeoUtils::schemaImageObject($this->shareImage())) {
            $node['image'] = $image;
        }

        if ($this->isVenue()) {
            if ($address = $this->schemaAddress($lang)) {
                $node['address'] = $address;
            }

            if ($geo = $this->schemaGeo()) {
                $node['geo'] = $geo;
            }

            if ($this->showsPhone()) {
                $node['telephone'] = (string) $this->phone;
            }
        }

        if (! $isUnverified && ($sameAs = $this->schemaSameAs())) {
            $node['sameAs'] = $sameAs;
        }

        $events = [];

        foreach ($upcoming as $row) {
            if (! ($row['event'] ?? null) instanceof Event) {
                continue;
            }

            $entry = $row['event']->schemaNode($row['date'] ?? null, $this, $lang, true);

            // Google reads an event without a location as no event at all.
            if (! isset($entry['location'])) {
                continue;
            }

            $events[] = $entry;

            if (count($events) === self::SCHEMA_UPCOMING_LIMIT) {
                break;
            }
        }

        if ($events) {
            $node[$type === 'Person' ? 'performerIn' : 'event'] = $events;
        }

        return $node;
    }

    /**
     * The WebSite node that lets Google show this schedule's name as the site name in results.
     *
     * Only where the schedule IS the site: its canonical is the root of a host (a hosted subdomain,
     * or a custom domain it is served on directly), and the page is that root in its primary
     * language. Null under selfhost path routing, where /{subdomain} is a section of the operator's
     * site, and on the ?lang= alternate, which is not the site's home URL - Google takes one name
     * per site, from its home page. The layout emits it on the schedule home alone.
     *
     * @return array<string, string>|null
     */
    public function websiteSchemaNode(string $lang): ?array
    {
        $canonical = $this->schemaCanonicalUrl();

        if (! $canonical || $this->langQuerySuffix($lang) !== '') {
            return null;
        }

        if (! in_array(parse_url($canonical, PHP_URL_PATH), [null, '', '/'], true)) {
            return null;
        }

        $name = SeoUtils::cleanText($this->nameInLanguage($lang));

        if ($name === '') {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebSite',
            'name' => $name,
            'url' => $canonical,
        ];
    }

    /**
     * This schedule as an event's organizer or performer: a Person for talent, else an
     * Organization (or $type), named in $lang, with its url and @id only when it is claimed. Null
     * without a name.
     *
     * @return array<string, string>|null
     */
    public function schemaAgent(string $lang, ?string $type = null): ?array
    {
        $name = SeoUtils::cleanText($this->nameInLanguage($lang));

        if ($name === '') {
            return null;
        }

        $node = ['@type' => $type ?? ($this->isTalent() ? 'Person' : 'Organization')];

        if ($id = $this->schemaId()) {
            $node['@id'] = $id;
        }

        $node['name'] = $name;

        if ($url = $this->schemaCanonicalUrl()) {
            $node['url'] = $url;
        }

        return $node;
    }

    /**
     * This venue as the Place an event happens at, in $lang: its name, else its short address;
     * the PostalAddress; geo when coordinates exist; url and @id only when it is claimed. Null
     * when it has nothing at all to say about where it is.
     *
     * @return array<string, mixed>|null
     */
    public function schemaPlace(string $lang): ?array
    {
        $name = SeoUtils::cleanText($this->nameInLanguage($lang)) ?: implode(', ', array_filter([
            SeoUtils::cleanText($this->textInLanguage('address1', $lang)),
            SeoUtils::cleanText($this->textInLanguage('city', $lang)),
        ]));
        $address = $this->schemaAddress($lang);
        $geo = $this->schemaGeo();

        if ($name === '' && ! $address && ! $geo) {
            return null;
        }

        $place = ['@type' => 'Place'];

        if ($id = $this->schemaId()) {
            $place['@id'] = $id;
        }

        if ($name !== '') {
            $place['name'] = $name;
        }

        if ($url = $this->schemaCanonicalUrl()) {
            $place['url'] = $url;
        }

        if ($address) {
            $place['address'] = $address;
        }

        if ($geo) {
            $place['geo'] = $geo;
        }

        return $place;
    }

    /**
     * The venue's PostalAddress in $lang: the street (with its second line), city, region and
     * postal code, then the country as an upper-case ISO code. With none of the first four, the
     * geocoded formatted_address stands in as the street. Null when there is neither - never an
     * empty PostalAddress, and never a country on its own, which was 18% of production's event
     * addresses and locates nothing.
     *
     * @return array<string, string>|null
     */
    private function schemaAddress(string $lang): ?array
    {
        $fields = array_filter([
            'streetAddress' => implode(', ', array_filter([
                SeoUtils::cleanText($this->textInLanguage('address1', $lang)),
                SeoUtils::cleanText($this->textInLanguage('address2', $lang)),
            ])),
            'addressLocality' => SeoUtils::cleanText($this->textInLanguage('city', $lang)),
            'addressRegion' => SeoUtils::cleanText($this->textInLanguage('state', $lang)),
            'postalCode' => SeoUtils::cleanText((string) $this->postal_code),
        ], fn (string $value) => $value !== '');

        if (! $fields && ($formatted = SeoUtils::cleanText((string) $this->formatted_address)) !== '') {
            $fields['streetAddress'] = $formatted;
        }

        if (! $fields) {
            return null;
        }

        if ($country = strtoupper(trim((string) $this->country_code))) {
            $fields['addressCountry'] = $country;
        }

        return ['@type' => 'PostalAddress'] + $fields;
    }

    /** GeoCoordinates from geo_lat and geo_lon, when both are real coordinates. */
    private function schemaGeo(): ?array
    {
        if (! is_numeric($this->geo_lat) || ! is_numeric($this->geo_lon)) {
            return null;
        }

        $latitude = (float) $this->geo_lat;
        $longitude = (float) $this->geo_lon;

        if (($latitude == 0.0 && $longitude == 0.0) || abs($latitude) > 90 || abs($longitude) > 180) {
            return null;
        }

        return ['@type' => 'GeoCoordinates', 'latitude' => $latitude, 'longitude' => $longitude];
    }

    /**
     * The schedule's description as plain text in $lang: the long one, else the short one, at most
     * Event::SCHEMA_DESCRIPTION_MAX characters. Null when it has neither.
     */
    private function schemaDescription(string $lang): ?string
    {
        $text = SeoUtils::plainText($this->textInLanguage('description_html', $lang))
            ?: SeoUtils::cleanText($this->textInLanguage('short_description', $lang));

        return $text === '' ? null : SeoUtils::excerpt($text, Event::SCHEMA_DESCRIPTION_MAX);
    }

    /** @return array<int, string> the social links' URLs */
    private function schemaSameAs(): array
    {
        return collect($this->decodeLinks('social_links'))
            ->map(fn ($link) => is_string($link->url ?? null) ? trim($link->url) : '')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    public function toData()
    {
        $url = $this->getGuestUrl();
        $youtubeUrl = $this->getFirstVideoUrl();

        $data = $this->toArray();
        $data['id'] = UrlUtils::encodeId($data['id']);
        $data['user_id'] = UrlUtils::encodeId($data['user_id']);
        $data['url'] = $url;
        $data['youtube_url'] = $youtubeUrl;

        return $data;
    }

    public function toApiData()
    {
        $data = new \stdClass;

        if (! $this->isPro()) {
            return $data;
        }

        $data->id = UrlUtils::encodeId($this->id);
        $data->subdomain = $this->subdomain;
        $data->url = $this->getGuestUrl();
        $data->type = $this->type;
        $data->name = $this->name;
        $data->email = $this->email;
        $data->phone = $this->phone;
        $data->show_phone = $this->show_phone;
        $data->website = $this->website;
        $data->description = $this->description;
        $data->short_description = $this->short_description;
        $data->timezone = $this->timezone;
        $data->language_code = $this->language_code;

        // Profile image URL
        $rawProfileImage = $this->getAttributes()['profile_image_url'] ?? null;
        $data->profile_image_url = $rawProfileImage ? $this->profile_image_url : null;

        $data->address1 = $this->address1;
        $data->city = $this->city;
        $data->state = $this->state;
        $data->postal_code = $this->postal_code;
        $data->country_code = $this->country_code;

        $data->created_at = $this->created_at ? $this->created_at->toIso8601String() : null;
        $data->updated_at = $this->updated_at ? $this->updated_at->toIso8601String() : null;

        if ($this->relationLoaded('groups')) {
            $data->groups = $this->groups->map(function ($group) {
                return [
                    'id' => UrlUtils::encodeId($group->id),
                    'name' => $group->name,
                    'slug' => $group->slug,
                    'color' => $group->color,
                ];
            })->values();
        }

        return $data;
    }

    public function isPro()
    {
        if (! config('app.hosted')) {
            return true;
        }

        // Check if user has an active Stripe subscription
        if ($this->hasActiveSubscription()) {
            return true;
        }

        // Check if user is on a generic trial
        if ($this->onGenericTrial()) {
            return true;
        }

        // Enterprise plans get all Pro features
        if ($this->isEnterprise()) {
            return true;
        }

        // Legacy: Check the plan_expires field
        return $this->plan_expires >= now()->format('Y-m-d') && $this->plan_type == 'pro';
    }

    public function scopeWherePro($query)
    {
        if (! config('app.hosted')) {
            return $query;
        }

        return $query->where(function ($q) {
            $q->whereHas('subscriptions', function ($sq) {
                $sq->whereIn('stripe_status', ['active', 'trialing']);
            })
                ->orWhere(function ($q2) {
                    $q2->whereNotNull('trial_ends_at')
                        ->where('trial_ends_at', '>=', now());
                })
                ->orWhere(function ($q2) {
                    $q2->where('plan_type', 'pro')
                        ->where('plan_expires', '>=', now()->format('Y-m-d'));
                })
                ->orWhere(function ($q2) {
                    $q2->where('plan_type', 'enterprise')
                        ->where('plan_expires', '>=', now()->format('Y-m-d'));
                });
        });
    }

    /**
     * The visibility state new events start with on this schedule.
     * Internal and Unlisted are Enterprise-only, so for non-Enterprise schedules
     * clamp them to the closest allowed state (internal -> draft to stay hidden,
     * unlisted -> public) - matching EventRepo::saveEvent's plan gate.
     */
    public function defaultEventVisibility(): string
    {
        $state = $this->default_event_visibility ?: 'public';

        if (! $this->isEnterprise()) {
            if ($state === 'internal') {
                return 'draft';
            }
            if ($state === 'unlisted') {
                return 'public';
            }
        }

        return $state;
    }

    public function isEnterprise()
    {
        // Selfhosted deployments get all features
        if (! config('app.hosted')) {
            return true;
        }

        // Check for active enterprise Stripe subscription
        if ($this->hasActiveEnterpriseSubscription()) {
            return true;
        }

        // Legacy: Check the plan_type field
        return $this->plan_expires >= now()->format('Y-m-d') && $this->plan_type === 'enterprise';
    }

    public function isWhiteLabeled()
    {
        if (! config('app.hosted')) {
            return true;
        }

        // Check if user has an active Stripe subscription
        if ($this->hasActiveSubscription()) {
            return true;
        }

        // Check if user is on a generic trial
        if ($this->onGenericTrial()) {
            return true;
        }

        // Enterprise plans get white-labeling
        if ($this->isEnterprise()) {
            return true;
        }

        // Legacy: Check the plan_expires field
        return $this->plan_expires >= now()->format('Y-m-d') && $this->plan_type == 'pro';
    }

    /**
     * The colour a browser may tint its own chrome with on this schedule's pages, or null.
     *
     * One value for the two consumers that have to agree: AppController::scheduleManifest() puts
     * it in the manifest as theme_color, and partials/web-app-manifest.blade.php emits the
     * matching <meta name="theme-color">. Null means "say nothing", never a default. The meta tag
     * used to fall back to #4E81FA while the manifest omitted the field in exactly that case, so a
     * schedule that had cleared its accent got OUR brand blue in its address bar: the same leak
     * the manifest split was written to close, surviving in the tag next to it.
     *
     * Not the same question as the page's own accent, which does default - show-guest.blade.php
     * falls back for buttons. A default button colour is UI; a tinted address bar reads as identity.
     *
     * accent_color is NOT NULL with a '#007bff' default, so a cleared accent is stored as an empty
     * string; the accessor reads that, and anything else that is not a hex colour, as null.
     */
    public function manifestThemeColor(): ?string
    {
        return $this->accent_color ?: null;
    }

    public function showBranding()
    {
        // The free-tier fact every free-tier surface reads: the event-page card, both embed
        // snippet lines, the ticket-embed frame and the newsletter footer. Which credit the page
        // itself carries is split between showFooterStrip() and creditChipReason().
        //
        // A single-tenant install has no tiers - actualPlanTier() short-circuits to
        // 'enterprise' - and those surfaces are a hosted platform's growth CTAs, so they have
        // nothing to say there. Selfhost attribution is the credit chip instead; see
        // creditChipReason(). This branch used to read `! $this->isWhiteLabeled()`, which
        // was unconditionally false when unhosted, so the behaviour is unchanged.
        //
        // showFooterStrip() builds on this, and creditChipReason() stands down on that, so the
        // strip wins wherever it renders and the chip fills in elsewhere. Keep this branch
        // answering false: it is what stops a selfhost from being read as a strip-bearing
        // install and losing its attribution.
        if (! config('app.hosted')) {
            return false;
        }

        return $this->actualPlanTier() === 'free';
    }

    /**
     * Whether this schedule's guest pages end with the dark footer strip, "Create your free
     * schedule at ..." linking marketing_url().
     *
     * The strip is the growth CTA of whoever runs the platform, so it is a free-tier thing on an
     * operator's own platform and nowhere else. eventschedule.com credits its free tier with the
     * small corner chip instead (creditChipReason() answers 'free_plan'), which is why this is
     * false on the nexus whatever the plan. It is the strip's one predicate: the layout gates on
     * it and creditChipReason() stands down on it, so a page never carries both credits.
     */
    public function showFooterStrip(): bool
    {
        return ! config('app.is_nexus') && $this->showBranding();
    }

    /**
     * Why this schedule's guest pages carry the small "Event Schedule" credit chip, or null when
     * they do not - either because none is owed, or because the footer strip is already carrying
     * one:
     *
     *  - 'selfhost'     the Attribution Assurance License credit on a single-tenant install.
     *  - 'saas'         the same credit on an operator's own multi-tenant platform.
     *  - 'free_plan'    a free schedule on eventschedule.com. The nexus runs no footer strip, so
     *                   the chip is its free tier's page credit, and a paid plan takes it off.
     *  - 'granted_plan' an Enterprise plan a nexus admin handed out by hand. Customers paying
     *                   through Stripe buy white-label and never carry it, and neither do
     *                   plans earned through the referral programme.
     *
     * Off the nexus it is keyed on the deployment rather than the plan, then stood down wherever
     * the footer strip already renders. The two predicates start from different questions: the
     * strip is a growth CTA that belongs to whoever runs the platform, so it turns on the
     * tenant's tier; this chip is the license credit, owed by whoever redistributes the software,
     * so it turns on the deployment. An operator's paying tenant is as much a part of that
     * redistribution as their free one, and the tenant's subscription is between them and the
     * operator. What the two share is a page, and a page carries one credit: where
     * showFooterStrip() has already put the strip there, this answers null.
     *
     * On an operator's platform that lands the chip on the tiers they charge for and leaves their
     * free tier showing their own strip alone. Two consequences that read like bugs and are not:
     * upgrading a tenant there ADDS the chip rather than removing it, and an operator's free tier
     * carries no Event Schedule attribution at all, because their strip links marketing_url().
     * eventschedule.com is still the one install that sells white-label, so it is the one install
     * where the chip turns on the plan: its free tier carries it, a paid plan takes it off, and an
     * admin-granted one keeps it.
     *
     * Both halves of the granted-plan test are load-bearing. plan_source alone would keep
     * branding someone who was granted a plan and later subscribed, if any Stripe path ever
     * forgot to clear it; hasActiveEnterpriseSubscription() alone would also catch referral
     * rewards, which are earned rather than given (see ReferralController).
     */
    public function creditChipReason(): ?string
    {
        // Tested before is_nexus, and not folded into the branch below: they are independent
        // env vars, so a selfhost operator who also sets IS_NEXUS=true has to land here rather
        // than fall through to the nexus's plan logic, where actualPlanTier() short-circuits to
        // 'enterprise' for every schedule and would answer null - dropping the attribution from
        // every public page on an install that is meant to always carry it.
        if (! config('app.hosted')) {
            return 'selfhost';
        }

        // The strip is already crediting this page, and the two are alternatives rather than a
        // pair - see docs/BRANDING_MATRIX.md rule 2. Deliberately below the selfhost branch:
        // showFooterStrip() is false when unhosted, so today either order behaves the same and
        // no test can tell them apart, but if that ever changes, this order is what keeps the
        // attribution on an install that is meant to always carry it.
        if ($this->showFooterStrip()) {
            return null;
        }

        if (! config('app.is_nexus')) {
            return 'saas';
        }

        // The nexus's free tier. There is no strip here to defer to, so the chip takes the
        // strip's place. Ahead of the granted-plan test, though the order is not load-bearing:
        // that one needs the Enterprise tier, so a lapsed grant - plan_source still 'admin', tier
        // now free - lands here either way.
        if ($this->showBranding()) {
            return 'free_plan';
        }

        $isGrantedPlan = $this->plan_source === 'admin'
            && $this->actualPlanTier() === 'enterprise'
            && ! $this->hasActiveEnterpriseSubscription();

        return $isGrantedPlan ? 'granted_plan' : null;
    }

    /**
     * Whether this schedule's public pages are monetized (ads / paid promotions).
     *
     * Mirrors showBranding(): a free-tier schedule carries them, a paying one does not,
     * so removing ads is a concrete Pro benefit alongside removing the branding footer.
     *
     * Gated on actualPlanTier() rather than config('app.hosted') on purpose. Selfhost
     * already resolves to 'enterprise' there, so a single-tenant install is never
     * monetized - while a self-hosted SaaS operator, who legitimately wants to monetize
     * their own free tier, still is.
     *
     * Deliberately free of request state: the embed / graphic / member / custom-domain
     * guards live in AdsService::isEligible() so this stays safe to call anywhere.
     */
    public function showAds(): bool
    {
        if (! \App\Services\AdsService::isEnabled()) {
            return false;
        }

        // The nexus stays ad-free.
        if (config('app.is_nexus')) {
            return false;
        }

        // The demo schedule is a sales surface.
        if (is_demo_role($this)) {
            return false;
        }

        // A placeholder nobody has claimed is on the free tier by definition, so without this it
        // would be the most ad-eligible page on the platform - and it is a page about a named
        // third party who never signed up. Serving somebody else's paid promotion beside their
        // name is not a trade we are entitled to make on their behalf.
        if (! $this->hasRealOwner()) {
            return false;
        }

        return $this->actualPlanTier() === 'free';
    }

    public function acceptEventRequests()
    {
        if ($this->isClaimed()) {
            return $this->accept_requests;
        }

        return true;
    }

    /**
     * Whether an event attaching to this schedule lands accepted (event_role.is_accepted =
     * true) rather than pending (null). Four ways acceptance is already implied, so no human
     * ever has to click Approve:
     *
     *  1. The submitter runs this schedule - approving your own event is a no-op.
     *  2. Nobody owns this schedule. It is a placeholder somebody invented while entering an
     *     event, so no member could ever accept on its behalf and a pending row here is
     *     permanent - it hides the event on the placeholder's own guest page forever.
     *  3. The owner turned Require approval off while still accepting requests.
     *  4. The submitting schedule is on this schedule's pre-approved list.
     *
     * Rule 2 keys on user_id, NOT isClaimed(), on purpose: a schedule whose owner merely has
     * an unverified email still HAS an owner watching the Requests tab, and the hosted
     * Role::updating hook nulls email_verified_at on every email change - under isClaimed()
     * each of those edits would quietly turn a moderated schedule into an auto-accepting one
     * until the verify link was clicked. Same predicate logoWallRoles() already reads as
     * consent. acceptEventRequests() is rightly laxer: it answers "may I submit at all".
     *
     * $actingUser must be the AUTHENTICATED submitter or null - never EventRepo::saveEvent()'s
     * schedule-owner fallback, or every guest post auto-accepts onto its target schedule.
     */
    public function autoAcceptsEventFrom(?User $actingUser = null, ?Role $fromRole = null): bool
    {
        if ($actingUser && $actingUser->isMember($this->subdomain)) {
            return true;
        }

        if (! $this->user_id) {
            return true;
        }

        if ($this->acceptEventRequests() && ! $this->require_approval) {
            return true;
        }

        return (bool) ($fromRole
            && $this->approved_subdomains
            && in_array($fromRole->subdomain, $this->approved_subdomains));
    }

    public function isRtl()
    {
        if (showing_translation($this)) {
            // When showing the translated content, the direction follows the TARGET language.
            return in_array($this->translation_language_code, ['ar', 'he']);
        }

        return in_array($this->language_code, ['ar', 'he']);
    }

    /**
     * Direction of the schedule's authored content, independent of the viewer's
     * translate state. Mirrors EventTextGenerator's he/ar check, unlike isRtl()
     * which flips to false in translate/English-view mode.
     */
    public function isContentRtl(): bool
    {
        return in_array($this->language_code, ['ar', 'he']);
    }

    /**
     * Whether the schedule's authored content language is English.
     * Used to gate the "generate graphics text in English" option, which
     * is a no-op (and hidden) for English schedules.
     */
    /**
     * Hostnames a tenant may never claim as a custom domain.
     *
     * The old check was the literal string 'eventschedule.com', which left an operator running
     * their own SaaS with no guard at all. ResolveCustomDomain already refuses to SERVE the base
     * domain, so the traffic was never at risk - but nothing stopped a tenant storing it, and
     * deleting that schedule later called removeDomain() on it and dropped the operator's own
     * hostname out of the DigitalOcean app spec. The add is a silent no-op (the apex is already
     * in the spec), which is why nothing surfaced until the delete.
     */
    public static function isReservedCustomDomainHost(?string $host): bool
    {
        $host = strtolower(trim((string) $host));

        if ($host === '') {
            return false;
        }

        if (str_contains($host, 'eventschedule.com')) {
            return true;
        }

        $base = strtolower(_base_domain() ?: '');

        return $base !== '' && ($host === $base || str_ends_with($host, '.'.$base));
    }

    public function isEnglish(): bool
    {
        return strtolower($this->language_code ?? 'en') === 'en';
    }

    /**
     * Whether the "Force English" graphic export can mean anything for this schedule.
     *
     * The `_en` columns hold whatever the schedule's translation TARGET is, not English, so the
     * option only does what it says when that target IS English. The preview and the AI-text
     * endpoint checked this; the download endpoint and the scheduled graphic email did not, and
     * the toggle hides itself once the target changes - so a schedule that turned it on while
     * targeting English and later switched to, say, French kept a stale `true` that nothing could
     * clear, and rendered French text with English date formatting in the download and in every
     * subscriber's email while the preview stayed correct.
     */
    public function canForceEnglish(): bool
    {
        return ! $this->isEnglish() && ($this->translation_language_code ?: 'en') === 'en';
    }

    /**
     * Whether this schedule actually offers a translation, i.e. its target language differs
     * from the language its content is authored in. When they match there is nothing to
     * translate and no visitor language toggle is shown.
     */
    public function offersTranslation(): bool
    {
        return $this->language_code !== ($this->translation_language_code ?: 'en');
    }

    /**
     * The language the visitor is reading this schedule in right now: the target while the
     * translation is being shown, otherwise the authored language.
     *
     * Guest surfaces should resolve event text against THIS, not against a "translated: yes/no"
     * boolean. An aggregated event carries its own language pair (the venue's), which need not
     * match the viewing schedule's - a `he`->`en` curator showing an `en`->`he` venue's event has
     * the English text in `name` and the Hebrew in `name_en`, the exact opposite of its own rows.
     * Asking for a language works in both cases; asking for "the translation" inverts in one.
     */
    public function displayLanguageCode(): string
    {
        return showing_translation($this)
            ? ($this->translation_language_code ?: 'en')
            : ($this->language_code ?: 'en');
    }

    /**
     * This schedule's `$field` in the language `$want`.
     *
     * Needed wherever ANOTHER schedule's name is rendered on a guest page - the venue chip under
     * an event, a talent credit - because `translatedName()` decides from
     * `showing_translation($thatSchedule)`, which is the viewer's flag applied to a schedule whose
     * language pair may be the reverse of the page's. On a `he`->`en` curator that made the
     * `en`->`he` venue "Torah Learning Center" render as "מרכז ללימוד תורה" on the English view.
     * Nothing changes for the viewing schedule itself, whose pair is where `$want` came from.
     */
    public function textInLanguage(string $field, string $want): ?string
    {
        $translated = $this->{$field.'_en'};

        if ($translated && ($this->translation_language_code ?: 'en') === $want) {
            return $translated;
        }

        return $this->{$field};
    }

    public function nameInLanguage(string $want): string
    {
        return (string) $this->textInLanguage('name', $want);
    }

    /**
     * Human-readable name of the schedule's translation TARGET language (e.g. "English",
     * "French"), in the current UI locale. Defaults to English, matching the column default.
     */
    public function translationLanguageName(): string
    {
        $languages = config('app.supported_languages', ['en' => 'english']);
        $code = $this->translation_language_code ?: 'en';

        return isset($languages[$code]) ? __('messages.'.$languages[$code]) : strtoupper($code);
    }

    public function translatedName()
    {
        $value = $this->name;

        if ($this->name_en && (showing_translation($this))) {
            $value = $this->name_en;
        }

        return $value;
    }

    public function getSponsorLogos(): array
    {
        if (! $this->sponsor_logos) {
            return [];
        }

        $sponsors = json_decode($this->sponsor_logos, true);

        if (! is_array($sponsors)) {
            return [];
        }

        $useTranslation = showing_translation($this);

        foreach ($sponsors as &$sponsor) {
            if (! empty($sponsor['logo'])) {
                $filename = $sponsor['logo'];

                if (str_starts_with($filename, 'demo_')) {
                    $sponsor['logo_url'] = url('/images/demo/'.$filename);
                } elseif (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
                    $sponsor['logo_url'] = 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/'.$filename;
                } elseif (in_array(config('filesystems.default'), ['local', 'public'])) {
                    $sponsor['logo_url'] = url('/storage/'.$filename);
                } else {
                    $sponsor['logo_url'] = $filename;
                }
            } else {
                $sponsor['logo_url'] = '';
            }

            if ($useTranslation && ! empty($sponsor['name_en'])) {
                $sponsor['display_name'] = $sponsor['name_en'];
            } else {
                $sponsor['display_name'] = $sponsor['name'] ?? '';
            }
        }

        return $sponsors;
    }

    public static function getCustomizableLabels(): array
    {
        return [
            'about' => 'About',
            'add_comment' => 'Add Comment',
            'add_photo' => 'Add Photo',
            'add_to_calendar' => 'Add to Calendar',
            'add_video' => 'Add Video',
            'agenda' => 'Agenda',
            'back_to_schedule' => 'Back to schedule',
            'book_a_time' => 'Book a Time',
            'buy_tickets' => 'Buy Tickets',
            'category' => 'Category',
            'clear_filters' => 'Clear Filters',
            'done' => 'Done',
            // The KEY stays as it is even though the value no longer reads like it: it indexes
            // stored roles.custom_labels JSON, so renaming it would silently drop every owner's
            // override. The copy changed because SendEventAnnouncements sends a DIGEST floored at
            // usage.audience_announcement_min_hours, not one email per event.
            'email_me_new_events' => 'Keep me posted',
            'events' => 'Events',
            'filters' => 'Filter Events',
            'follow' => 'Follow',
            'free_entry' => 'Free entry',
            'get_tickets' => 'Get Tickets',
            'load_more' => 'Load More',
            'no_scheduled_events' => 'No scheduled events',
            'online' => 'Online',
            'our_sponsors' => 'Our Sponsors',
            'past_events' => 'Past Events',
            'photo_gallery' => 'Photo Gallery',
            'read_more' => 'Read more',
            'register' => 'Register',
            'request_to_book' => 'Request to Book',
            'schedule' => 'Schedule',
            'share' => 'Share',
            'show_all' => 'Show All',
            'show_less' => 'Show less',
            'show_more' => 'Show more',
            'show_past_events' => 'Show Past Events',
            'submit_event' => 'Submit Event',
            'venue' => 'Venue',
            'view_full_schedule' => 'View Full Schedule',
        ];
    }

    public function customLabel(string $key): string
    {
        $labels = $this->custom_labels ?? [];

        if (isset($labels[$key])) {
            if (! empty($labels[$key]['value_en']) && (showing_translation($this))) {
                return $labels[$key]['value_en'];
            }

            if (! empty($labels[$key]['value'])) {
                return $labels[$key]['value'];
            }
        }

        return __('messages.'.$key);
    }

    public function translatedSponsorSectionTitle()
    {
        return $this->customLabel('our_sponsors');
    }

    /**
     * Background for the sponsors panel on guest pages.
     *
     * Returns null for the default translucent card, 'transparent' to let the schedule's
     * own background show through, or a #rrggbb colour. Anything else is treated as null
     * so a bad stored value can never break the panel.
     */
    public function sponsorBackground(): ?string
    {
        $value = $this->sponsor_background_color;

        if ($value === 'transparent') {
            return 'transparent';
        }

        if (is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value)) {
            return $value;
        }

        return null;
    }

    public function translatedShortDescription()
    {
        $value = $this->short_description;

        if ($this->short_description_en && (showing_translation($this))) {
            $value = $this->short_description_en;
        }

        return $value;
    }

    public function translatedDescription()
    {
        $value = $this->description_html;

        if ($this->description_html_en && (showing_translation($this))) {
            $value = $this->description_html_en;
        }

        return $value;
    }

    public function translatedAddress1()
    {
        $value = $this->address1;

        if ($this->address1_en && (showing_translation($this))) {
            $value = $this->address1_en;
        }

        return $value;
    }

    public function translatedAddress2()
    {
        $value = $this->address2;

        if ($this->address2_en && (showing_translation($this))) {
            $value = $this->address2_en;
        }

        return $value;
    }

    public function translatedCity()
    {
        $value = $this->city;

        if ($this->city_en && (showing_translation($this))) {
            $value = $this->city_en;
        }

        return $value;
    }

    public function translatedState()
    {
        $value = $this->state;

        if ($this->state_en && (showing_translation($this))) {
            $value = $this->state_en;
        }

        return $value;
    }

    public function englishName()
    {
        return $this->name_en ?: $this->name;
    }

    public function englishAddress1()
    {
        return $this->address1_en ?: $this->address1;
    }

    public function englishCity()
    {
        return $this->city_en ?: $this->city;
    }

    public function englishState()
    {
        return $this->state_en ?: $this->state;
    }

    public function translatedRequestTerms()
    {
        $value = $this->request_terms;

        if ($this->request_terms_en && (showing_translation($this))) {
            $value = $this->request_terms_en;
        }

        return $value;
    }

    public function translatedBannerMessage()
    {
        $value = $this->banner_message_html;

        if ($this->banner_message_html_en && (showing_translation($this))) {
            $value = $this->banner_message_html_en;
        }

        return $value;
    }

    public function newsletters()
    {
        return $this->hasMany(\App\Models\Newsletter::class);
    }

    public function boostCampaigns()
    {
        return $this->hasMany(\App\Models\BoostCampaign::class);
    }

    public function actualPlanTier(): string
    {
        if (! config('app.hosted')) {
            return 'enterprise';
        }

        if ($this->hasActiveEnterpriseSubscription()) {
            return 'enterprise';
        }

        if ($this->plan_type === 'enterprise' && $this->plan_expires >= now()->format('Y-m-d')) {
            return 'enterprise';
        }

        if ($this->hasActiveSubscription()) {
            return 'pro';
        }

        if ($this->onGenericTrial()) {
            return 'pro';
        }

        if ($this->plan_type === 'pro' && $this->plan_expires >= now()->format('Y-m-d')) {
            return 'pro';
        }

        return 'free';
    }

    /**
     * May this schedule send audience mail (a newsletter, or an automatic event announcement)?
     *
     * The single answer for what used to be duplicated in NewsletterService::send() and
     * NewsletterController::requiresNewsletterVerification(). Both now call this so the three
     * cannot drift.
     *
     * $recipients scales the gate to the size of the send. Requiring SMTP or an SMS-verified phone
     * before ANY bulk mail is very likely why only 4 of 671 schedules sent a newsletter last month
     * and no free schedule ever has; the long tail this is meant to serve has single-digit
     * audiences, where the abuse ceiling is not what the control is protecting.
     *
     * $actor is the person who pressed send. It is NOT always the owner: the newsletter composer
     * gates on the composing user, so an admin-level member with a verified phone can send today
     * and must keep being able to. A scheduled command has no actor and falls back to the owner.
     */
    public function canSendAudienceMail(int $recipients = 0, ?User $actor = null): bool
    {
        // Selfhost runs the operator's own mail server, and the test env has no reputation to
        // protect. Matches the existing gates, which both short-circuit on the same two.
        if (! config('app.hosted') || config('app.is_testing')) {
            return true;
        }

        // Its own SMTP: the consequences land on the schedule's domain, not the platform's.
        if ($this->hasEmailSettings()) {
            return true;
        }

        // Ownerless schedules (roles.user_id is nullable - AI-imported claimable venues, which can
        // still accumulate followers) fail CLOSED.
        $actor = $actor ?: $this->user;
        if (! $actor) {
            return false;
        }

        if ($actor->isAdmin() || $actor->hasVerifiedPhone()) {
            return true;
        }

        $limit = (int) config('usage.audience_mail_unverified_max_recipients', 50);

        return $recipients > 0 && $recipients <= $limit;
    }

    public function newsletterLimit(): ?int
    {
        if (! config('app.hosted') || $this->hasEmailSettings()) {
            return null;
        }

        $tier = $this->actualPlanTier();

        if ($tier === 'enterprise') {
            return 1000;
        }

        if ($tier === 'pro') {
            return 100;
        }

        return 10;
    }

    public function newslettersSentThisMonth(): int
    {
        // Count emails from fully sent newsletters via sent_count
        $sentEmails = (int) $this->newsletters()
            ->where('status', 'sent')
            ->where('sent_at', '>=', now()->startOfMonth())
            ->sum('sent_count');

        // Count emails from currently sending newsletters via recipient records
        $sendingEmails = (int) NewsletterRecipient::whereIn('newsletter_id',
            $this->newsletters()
                ->where('status', 'sending')
                ->where('updated_at', '>=', now()->startOfMonth())
                ->select('id')
        )->whereIn('status', ['pending', 'sent'])->count();

        return $sentEmails + $sendingEmails;
    }

    public function canSendNewsletter(): bool
    {
        $limit = $this->newsletterLimit();

        if (is_null($limit)) {
            return true;
        }

        return $this->newslettersSentThisMonth() < $limit;
    }

    public function isOnTrial(): bool
    {
        if ($this->onGenericTrial()) {
            return true;
        }

        $subscription = $this->subscription('default');

        return $subscription && $subscription->onTrial() && ! $subscription->canceled();
    }

    public function aiImageDailyLimit(): ?int
    {
        if (! config('app.hosted')) {
            return null;
        }

        if ($this->isOnTrial()) {
            return config('usage.ai_image_daily_limit_trial');
        }

        return config('usage.ai_image_daily_limit_paid');
    }

    public function aiImageGenerationsToday(): int
    {
        return (int) \App\Models\UsageDaily::where('role_id', $this->id)
            ->where('date', now()->toDateString())
            ->whereIn('operation', [
                \App\Services\UsageTrackingService::GEMINI_GENERATE_FLYER,
                \App\Services\UsageTrackingService::GEMINI_GENERATE_STYLE_IMAGE,
            ])
            ->sum('count');
    }

    public function canGenerateAiImage(): bool
    {
        $limit = $this->aiImageDailyLimit();

        if (is_null($limit)) {
            return true;
        }

        return $this->aiImageGenerationsToday() < $limit;
    }

    public function aiParseDailyLimit(): ?int
    {
        if (! config('app.hosted')) {
            return null;
        }

        if ($this->isOnTrial()) {
            return config('usage.ai_parse_daily_limit_trial');
        }

        if ($this->isEnterprise()) {
            return config('usage.ai_parse_daily_limit_enterprise');
        }

        return config('usage.ai_parse_daily_limit_pro');
    }

    public function canMakeAiParseRequest(): bool
    {
        $limit = $this->aiParseDailyLimit();

        if (is_null($limit)) {
            return true;
        }

        $count = (int) \App\Models\UsageDaily::where('role_id', $this->id)
            ->where('date', now()->toDateString())
            ->where('operation', \App\Services\UsageTrackingService::GEMINI_PARSE_EVENT)
            ->sum('count');

        return $count < $limit;
    }

    public function eventCreateDailyLimit(): ?int
    {
        if (! config('app.hosted')) {
            return null;
        }

        if ($this->isOnTrial()) {
            return config('usage.event_create_daily_limit_trial');
        }

        if ($this->isEnterprise()) {
            return config('usage.event_create_daily_limit_enterprise');
        }

        return config('usage.event_create_daily_limit_pro');
    }

    public function eventCreateUserDailyLimit(): ?int
    {
        if (! config('app.hosted')) {
            return null;
        }

        if ($this->isOnTrial()) {
            return config('usage.event_create_user_daily_limit_trial');
        }

        if ($this->isEnterprise()) {
            return config('usage.event_create_user_daily_limit_enterprise');
        }

        return config('usage.event_create_user_daily_limit_pro');
    }

    /**
     * Anti-abuse guard: whether another event may be created against this schedule right now.
     * Both limits are hosted-only (the limit helpers return null on selfhost = unlimited). Two
     * independent daily caps must each pass:
     *   - Per-schedule: events created today against THIS schedule.
     *   - Per-user backstop: events created today across every schedule the acting user is an
     *     editor (owner/admin) of - stops one account spreading volume across many schedules.
     *     Skipped for anonymous guests, who are bounded by the per-schedule cap plus the per-IP
     *     route throttle.
     */
    public function canCreateEvent(?User $actingUser = null): bool
    {
        $today = now()->toDateString();
        $operation = \App\Services\UsageTrackingService::EVENT_CREATE;

        $scheduleLimit = $this->eventCreateDailyLimit();

        if (! is_null($scheduleLimit)) {
            $scheduleCount = (int) \App\Models\UsageDaily::where('role_id', $this->id)
                ->where('date', $today)
                ->where('operation', $operation)
                ->sum('count');

            if ($scheduleCount >= $scheduleLimit) {
                return false;
            }
        }

        if ($actingUser) {
            $userLimit = $this->eventCreateUserDailyLimit();

            if (! is_null($userLimit)) {
                $roleIds = $actingUser->editor()->pluck('roles.id');

                if ($roleIds->isNotEmpty()) {
                    $userCount = (int) \App\Models\UsageDaily::whereIn('role_id', $roleIds)
                        ->where('date', $today)
                        ->where('operation', $operation)
                        ->sum('count');

                    if ($userCount >= $userLimit) {
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * Free-plan appointment allowance: how many appointment types this schedule may have.
     * Null means unlimited. The single free type books normally and takes no commission; what it
     * cannot do is carry a price (AppointmentType::canTakePayment()) or raise the advanced
     * scheduling fields (AppointmentTypeController::clampAdvanced()).
     */
    public function appointmentTypeLimit(): ?int
    {
        if (! config('app.hosted') || is_demo_role($this) || $this->isPro()) {
            return null;
        }

        return (int) config('usage.appointment_type_limit_free');
    }

    /**
     * Types held against the allowance.
     *
     * Counts ACTIVE, non-deleted types. Counting paused ones too meant a schedule with a single
     * deactivated type was blocked from creating another while having nothing bookable at all -
     * out of step with bookableAppointmentTypes(), which only ever offers active types.
     */
    public function appointmentTypeCount(): int
    {
        // Memoized: the Appointments tab asks three times per render (the usage meter, its label and
        // the clamped banner), and canCreateAppointmentType() asks again on every save.
        if ($this->appointmentTypeCountCache !== null) {
            return $this->appointmentTypeCountCache;
        }

        return $this->appointmentTypeCountCache = $this->appointmentTypes()
            ->where('is_deleted', false)
            ->where('is_active', true)
            ->count();
    }

    public function canCreateAppointmentType(): bool
    {
        $limit = $this->appointmentTypeLimit();

        if (is_null($limit)) {
            return true;
        }

        return $this->appointmentTypeCount() < $limit;
    }

    /**
     * The appointment types a guest may actually book.
     *
     * Two rules apply, and they are independent. A priced type drops out unless the schedule may
     * charge (AppointmentType::canTakePayment()); what survives is then clamped to the free-plan
     * count allowance (appointmentTypeLimit()). A schedule whose Pro plan lapsed keeps every type it
     * created - they stop being bookable, are never deleted, and light up again on upgrade.
     */
    public function bookableAppointmentTypes()
    {
        // Memoized on the collection rather than on hasBookableAppointments()'s boolean: the GP
        // header asks the boolean four times per render, but AppointmentController asks for the
        // collection twice per booking page, and the admin tab asks again after the boolean has
        // already run. Each of those built a fresh query.
        if ($this->bookableAppointmentTypesCache !== null) {
            return $this->bookableAppointmentTypesCache;
        }

        $types = $this->appointmentTypes()
            ->active()
            ->orderBy('id')
            ->get()
            ->filter(function ($type) {
                $type->setRelation('role', $this);

                return $type->isBookable();
            })
            ->values();

        // Two independent rules, in this order. isBookable() above drops a PRICED type the schedule
        // may not charge for; the cap below then clamps what is left to the free-plan allowance.
        // Clamping picks the oldest BOOKABLE type rather than the oldest active one: if the oldest
        // active type is a paid one the schedule may not charge for, clamping by age alone would
        // leave the schedule with nothing bookable while a banner named the dead one as live.
        $limit = $this->appointmentTypeLimit();

        return $this->bookableAppointmentTypesCache = is_null($limit) ? $types : $types->take($limit)->values();
    }

    public function aiAgendaDailyLimit(): ?int
    {
        if (! config('app.hosted')) {
            return null;
        }

        return config('usage.ai_agenda_daily_limit_enterprise');
    }

    public function canMakeAiAgendaRequest(): bool
    {
        $limit = $this->aiAgendaDailyLimit();

        if (is_null($limit)) {
            return true;
        }

        $count = (int) \App\Models\UsageDaily::where('role_id', $this->id)
            ->where('date', now()->toDateString())
            ->where('operation', \App\Services\UsageTrackingService::GEMINI_PARSE_PARTS)
            ->sum('count');

        return $count < $limit;
    }

    public function aiContentDailyLimit(): ?int
    {
        if (! config('app.hosted')) {
            return null;
        }

        return config('usage.ai_content_daily_limit_enterprise');
    }

    public function canMakeAiContentRequest(): bool
    {
        $limit = $this->aiContentDailyLimit();

        if (is_null($limit)) {
            return true;
        }

        $count = (int) \App\Models\UsageDaily::where('role_id', $this->id)
            ->where('date', now()->toDateString())
            ->whereIn('operation', [
                \App\Services\UsageTrackingService::GEMINI_GENERATE_STYLE,
                \App\Services\UsageTrackingService::GEMINI_GENERATE_SCHEDULE_DETAILS,
                \App\Services\UsageTrackingService::GEMINI_GENERATE_EVENT_DETAILS,
            ])
            ->sum('count');

        return $count < $limit;
    }

    public function photoLimit(): ?int
    {
        if (! config('app.hosted')) {
            return null;
        }

        if ($this->isPro()) {
            return null;
        }

        return 25;
    }

    public function photoCount(): int
    {
        return \App\Models\EventPhoto::whereIn('event_id',
            $this->events()->select('events.id')
        )->count();
    }

    public function canUploadPhoto(): bool
    {
        $limit = $this->photoLimit();

        if (is_null($limit)) {
            return true;
        }

        return $this->photoCount() < $limit;
    }

    public function groups()
    {
        return $this->hasMany(\App\Models\Group::class);
    }

    public function eventTemplates()
    {
        return $this->hasMany(\App\Models\EventTemplate::class)->orderByDesc('created_at');
    }

    /**
     * Get event custom fields definition
     */
    public function getEventCustomFields(): array
    {
        return $this->event_custom_fields ?? [];
    }

    public function isEventCustomFieldPrivate(string $fieldKey): bool
    {
        $field = $this->getEventCustomFields()[$fieldKey] ?? null;
        if (! $field) {
            return true;
        }

        return ! empty($field['private']);
    }

    public function filterPublicCustomFieldValues(?array $values): array
    {
        if (empty($values)) {
            return [];
        }

        return array_filter(
            $values,
            fn ($key) => ! $this->isEventCustomFieldPrivate((string) $key),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * The subset of custom fields shown on the public event request forms.
     *
     * The flag is missing on every field defined before it existed, and the AI import request page
     * has always shown all of them, so an absent value means "shown" - the checkbox is an opt-out.
     */
    public function getRequestFormCustomFields(): array
    {
        return array_filter(
            $this->getEventCustomFields(),
            fn ($field) => $field['show_on_request'] ?? true
        );
    }

    /**
     * The allowed values of a dropdown/multiselect field, trimmed and without blanks.
     */
    public static function customFieldOptions(array $field): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $field['options'] ?? ''))));
    }

    /**
     * The label to show for a custom field. The two contexts pick English by different signals and
     * the caller has to say which it is - do not merge them.
     *
     * Guest surfaces follow the translate toggle, like Group::translatedName(). The AP keys off the
     * admin's UI locale, deliberately NOT the toggle: `session('translate')` is set on guest pages
     * and survives for the rest of the session, so an owner who viewed their own translated guest
     * page would otherwise see English labels back in the admin portal.
     */
    public function customFieldLabel(array $field, ?string $fallback = null, bool $forGuest = false): string
    {
        $nameEn = $field['name_en'] ?? '';
        $wantsEnglish = $forGuest ? showing_translation($this) : app()->getLocale() === 'en';

        if ($nameEn !== '' && $wantsEnglish) {
            return $nameEn;
        }

        return $field['name'] ?? ($fallback ?? '');
    }

    /**
     * Reshape submitted answers into what the validation rules expect. Run this BEFORE validating.
     *
     * Two request forms disagree with the rules on their own:
     * - the AI import page posts a multiselect as one comma-joined string, while the Blade forms
     *   post an array, and the rule below is `array`;
     * - AI parsing can prefill a dropdown with a value that is not on the list. Vue renders such a
     *   select blank while keeping the value, so Rule::in would reject a field the guest sees as
     *   empty. Clearing it here lets the guest fix (or ignore) a field they can actually see.
     */
    public function normalizeCustomFieldValues($values, ?array $fields = null): array
    {
        $values = (array) $values;

        foreach ($fields ?? $this->getEventCustomFields() as $fieldKey => $field) {
            if (! array_key_exists($fieldKey, $values)) {
                continue;
            }

            $type = $field['type'] ?? '';
            if (! in_array($type, ['dropdown', 'multiselect'], true)) {
                continue;
            }

            $options = self::customFieldOptions($field);
            $value = $values[$fieldKey];

            if ($type === 'dropdown') {
                if (is_array($value) || (! in_array((string) $value, $options, true) && (string) $value !== '')) {
                    $values[$fieldKey] = '';
                }

                continue;
            }

            $selected = is_array($value) ? $value : explode(',', (string) $value);
            $selected = array_map(fn ($v) => is_string($v) ? trim($v) : $v, $selected);

            $values[$fieldKey] = array_values(array_filter(
                $selected,
                fn ($v) => is_string($v) && $v !== '' && in_array($v, $options, true)
            ));
        }

        return $values;
    }

    /**
     * Drop empty answers and any dropdown/multiselect value that is not an allowed option, and
     * collapse a multiselect to the comma-joined string the column stores.
     *
     * Every write path shares this: EventRepo::saveEvent(), the booking-request form and the
     * structured guest submission, which each reach the events table by a different route.
     */
    public function sanitizeCustomFieldValues($values, ?array $fields = null): array
    {
        $values = array_filter(
            (array) $values,
            fn ($value) => $value !== null && $value !== ''
        );

        // Passing an explicit subset means "only these fields", so keys outside it are dropped -
        // otherwise a crafted post could set a field the form deliberately does not show. Callers
        // that pass null (the admin save path) keep the historic pass-through behaviour.
        if ($fields !== null) {
            $values = array_intersect_key($values, $fields);
        }

        foreach ($fields ?? $this->getEventCustomFields() as $fieldKey => $field) {
            $type = $field['type'] ?? '';

            if ($type === 'dropdown' && isset($values[$fieldKey])) {
                if (! in_array($values[$fieldKey], self::customFieldOptions($field), true)) {
                    unset($values[$fieldKey]);
                }
            } elseif ($type === 'multiselect' && isset($values[$fieldKey])) {
                $options = self::customFieldOptions($field);
                $selected = is_array($values[$fieldKey])
                    ? array_map('trim', $values[$fieldKey])
                    : array_map('trim', explode(',', $values[$fieldKey]));
                $valid = array_filter($selected, fn ($v) => in_array($v, $options, true));
                $values[$fieldKey] = ! empty($valid) ? implode(', ', $valid) : null;
            }
        }

        return $values;
    }

    /**
     * @param  array|null  $fields  Restrict the rules to a subset (e.g. the request-form fields).
     */
    public function getEventCustomFieldValidationRules(?array $fields = null): array
    {
        $rules = [];
        foreach ($fields ?? $this->getEventCustomFields() as $fieldKey => $field) {
            $type = $field['type'] ?? 'string';
            $required = ! empty($field['required']);
            $key = "custom_field_values.{$fieldKey}";
            $fieldRules = [$required ? 'required' : 'nullable'];

            if (in_array($type, ['string', 'multiline_string'], true)) {
                $fieldRules[] = 'string';
                $fieldRules[] = 'max:5000';

                // Optional schedule-authored format check. An uncompilable pattern yields null and
                // is skipped here; RoleUpdateRequest rejects one before it can be saved.
                $pattern = CustomFieldUtils::compilePattern($field['regex'] ?? null);
                if ($pattern) {
                    $fieldRules[] = 'regex:'.$pattern;
                }
            } elseif ($type === 'switch') {
                $fieldRules[] = 'in:0,1';
            } elseif ($type === 'date') {
                $fieldRules[] = 'date';
            } elseif ($type === 'dropdown') {
                $options = self::customFieldOptions($field);
                if (! empty($options)) {
                    $fieldRules[] = \Illuminate\Validation\Rule::in($options);
                }
            } elseif ($type === 'multiselect') {
                $fieldRules[] = 'array';
                $options = self::customFieldOptions($field);
                if (! empty($options)) {
                    $rules["{$key}.*"] = ['string', \Illuminate\Validation\Rule::in($options)];
                }
            }

            $rules[$key] = $fieldRules;
        }

        return $rules;
    }

    /**
     * Names used in validation messages. Called from both the AP form requests (via
     * ValidatesEventCustomFields) and the public request forms, so the label rule follows the
     * caller - see customFieldLabel().
     *
     * @param  array|null  $fields  Restrict the attributes to the same subset as the rules.
     */
    public function getEventCustomFieldValidationAttributes(?array $fields = null, bool $forGuest = false): array
    {
        $attrs = [];
        foreach ($fields ?? $this->getEventCustomFields() as $fieldKey => $field) {
            $attrs["custom_field_values.{$fieldKey}"] = $this->customFieldLabel($field, $fieldKey, $forGuest);
        }

        return $attrs;
    }

    /**
     * Get the import configuration as an array
     */
    public function getImportConfigAttribute($value)
    {
        $defaults = [
            'urls' => [],
            'cities' => [],
            'fields' => [
                'short_description' => false,
                'description' => false,
                'ticket_price' => false,
                'coupon_code' => false,
                'registration_url' => false,
                'category_id' => false,
                'group_id' => false,
            ],
            'required_fields' => [
                'short_description' => false,
                'description' => false,
                'ticket_price' => false,
                'coupon_code' => false,
                'registration_url' => false,
                'category_id' => false,
                'group_id' => false,
            ],
        ];

        if (! $value) {
            return $defaults;
        }

        $config = json_decode($value, true);

        if (! $config) {
            return $defaults;
        }

        $config['urls'] = $config['urls'] ?? [];
        $config['cities'] = $config['cities'] ?? [];
        $config['fields'] = array_merge($defaults['fields'], $config['fields'] ?? []);
        $config['required_fields'] = array_merge($defaults['required_fields'], $config['required_fields'] ?? []);

        return $config;
    }

    /**
     * Normalize the custom_domain to strip trailing slashes on all save paths.
     * Also extract and store the hostname for fast middleware lookup.
     */
    public function setCustomDomainAttribute($value)
    {
        if ($value) {
            $value = preg_replace('/^http:\/\//', 'https://', rtrim($value, '/'));
            $parsed = parse_url($value);
            if ($parsed && isset($parsed['host'])) {
                $value = ($parsed['scheme'] ?? 'https').'://'.$parsed['host'];
            }
        }
        $this->attributes['custom_domain'] = $value;
        $this->attributes['custom_domain_host'] = $value ? parse_url($value, PHP_URL_HOST) : null;
    }

    /**
     * Set the import configuration from an array
     */
    public function setImportConfigAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['import_config'] = json_encode($value);
        } else {
            $this->attributes['import_config'] = $value;
        }
    }

    /**
     * Check if this curator has import configuration
     */
    public function hasImportConfig()
    {
        if (! $this->isCurator()) {
            return false;
        }

        $config = $this->import_config;

        return ! empty($config['urls']) || ! empty($config['cities']);
    }

    /**
     * Get the graphic settings as an array
     */
    public function getGraphicSettingsAttribute($value)
    {
        $defaults = [
            'enabled' => false,
            'frequency' => 'weekly',
            'ai_prompt' => '',
            'ai_model' => 'gemini-2.5-flash',
            'layout' => 'grid',
            'send_day' => 1,
            'send_hour' => 9,
            'last_sent_at' => null,
            'recipient_emails' => '',
            'date_position' => null,
            'event_count' => null,
            'max_per_schedule' => null,
            'image_size' => 'auto',
        ];

        if (! $value) {
            return $defaults;
        }

        $config = json_decode($value, true);

        return array_merge($defaults, $config ?: []);
    }

    /**
     * Set the graphic settings from an array
     */
    public function setGraphicSettingsAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['graphic_settings'] = json_encode($value);
        } else {
            $this->attributes['graphic_settings'] = $value;
        }
    }

    /**
     * Check if graphic email is enabled and configured
     */
    public function hasGraphicEmailEnabled()
    {
        $settings = $this->graphic_settings;

        return ! empty($settings['enabled']);
    }

    /**
     * Get the Google Calendar ID for this role
     * Returns the role's specific calendar or the primary calendar
     */
    public function getGoogleCalendarId()
    {
        $pivot = RoleUser::where('role_id', $this->id)
            ->where('user_id', $this->user_id)
            ->first();

        return $pivot?->google_calendar_id ?: 'primary';
    }

    /**
     * Check if this role has Google Calendar integration enabled
     */
    public function hasGoogleCalendarIntegration()
    {
        $pivot = RoleUser::where('role_id', $this->id)
            ->where('user_id', $this->user_id)
            ->first();

        return ! is_null($pivot?->google_calendar_id);
    }

    /**
     * Check if this role has bidirectional sync enabled
     */
    public function hasBidirectionalSync()
    {
        return $this->sync_direction === 'both';
    }

    /**
     * Check if this role syncs to Google Calendar
     */
    public function syncsToGoogle()
    {
        return in_array($this->sync_direction, ['to', 'both']);
    }

    /**
     * Check if this role syncs from Google Calendar
     */
    public function syncsFromGoogle()
    {
        return in_array($this->sync_direction, ['from', 'both']);
    }

    /**
     * Get the sync direction as a human-readable string
     */
    public function getSyncDirectionLabel()
    {
        return match ($this->sync_direction) {
            'to' => 'To Google Calendar',
            'from' => 'From Google Calendar',
            'both' => 'Bidirectional Sync',
            default => 'No Sync'
        };
    }

    /**
     * Check if webhook is active and not expired
     */
    public function hasActiveWebhook()
    {
        return $this->google_webhook_id &&
               $this->google_webhook_expires_at &&
               $this->google_webhook_expires_at->isFuture();
    }

    /**
     * Get the Outlook / Microsoft calendar ID for this role.
     * Returns the owner's selected calendar, or null for the default calendar.
     * Unlike Google there is no 'primary' alias in Graph - null routes to /me/events.
     */
    public function getMicrosoftCalendarId(): ?string
    {
        $pivot = RoleUser::where('role_id', $this->id)
            ->where('user_id', $this->user_id)
            ->first();

        return $pivot?->microsoft_calendar_id ?: null;
    }

    /**
     * Check if this role has Outlook / Microsoft calendar integration enabled
     */
    public function hasMicrosoftCalendarIntegration()
    {
        $pivot = RoleUser::where('role_id', $this->id)
            ->where('user_id', $this->user_id)
            ->first();

        return ! is_null($pivot?->microsoft_calendar_id);
    }

    /**
     * Check if this role syncs to Outlook / Microsoft calendar
     */
    public function syncsToMicrosoft()
    {
        return in_array($this->microsoft_sync_direction, ['to', 'both']);
    }

    /**
     * Check if this role syncs from Outlook / Microsoft calendar
     */
    public function syncsFromMicrosoft()
    {
        return in_array($this->microsoft_sync_direction, ['from', 'both']);
    }

    /**
     * How an event deleted in a connected calendar (Google / Microsoft) should be handled here:
     * 'ignore' (keep it), 'cancel' (hide via is_cancelled), or 'delete' (remove). Shared across
     * providers; defaults to 'ignore' when unset.
     */
    public function calendarDeleteAction(): string
    {
        return in_array($this->calendar_delete_action, ['cancel', 'delete'], true)
            ? $this->calendar_delete_action
            : 'ignore';
    }

    /**
     * Check if the Microsoft Graph subscription is active and not expired
     */
    public function hasActiveMicrosoftWebhook()
    {
        return $this->microsoft_webhook_id &&
               $this->microsoft_webhook_expires_at &&
               $this->microsoft_webhook_expires_at->isFuture();
    }

    /**
     * Get the email settings attribute (decrypted and decoded)
     */
    public function getEmailSettingsAttribute($value)
    {
        if (! $value) {
            return null;
        }

        try {
            $decrypted = \Illuminate\Support\Facades\Crypt::decryptString($value);
            $decoded = json_decode($decrypted, true);

            return is_array($decoded) ? $decoded : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Set the email settings attribute (encrypted and encoded)
     */
    public function setEmailSettingsAttribute($value)
    {
        if (is_null($value) || (is_array($value) && empty(array_filter($value)))) {
            $this->attributes['email_settings'] = null;

            return;
        }

        if (is_array($value)) {
            // Remove empty values
            $value = array_filter($value, function ($v) {
                return $v !== null && $v !== '';
            });

            if (empty($value)) {
                $this->attributes['email_settings'] = null;

                return;
            }

            $json = json_encode($value);
            $this->attributes['email_settings'] = \Illuminate\Support\Facades\Crypt::encryptString($json);
        } else {
            // If it's already a string, assume it's already encrypted JSON
            $this->attributes['email_settings'] = $value;
        }
    }

    /**
     * Check if role has email settings configured
     */
    public function hasEmailSettings(): bool
    {
        $settings = $this->getEmailSettings();

        return ! empty($settings) && ! empty($settings['host']) && ! empty($settings['username']);
    }

    /**
     * Get decrypted email settings as array
     * Uses raw attribute to avoid infinite recursion
     */
    public function getEmailSettings(): array
    {
        $rawValue = $this->attributes['email_settings'] ?? null;
        if (! $rawValue) {
            return [];
        }

        try {
            $decrypted = \Illuminate\Support\Facades\Crypt::decryptString($rawValue);
            $decoded = json_decode($decrypted, true);

            return is_array($decoded) ? $decoded : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Set encrypted email settings from array
     */
    public function setEmailSettings(array $settings): void
    {
        $this->email_settings = $settings;
    }

    /**
     * Mark the role's custom SMTP credentials as having failed. Uses a
     * targeted UPDATE so callers that have unsaved attribute changes on the
     * model (e.g. RoleController::testEmail temporarily applying form data
     * via setEmailSettings) don't accidentally persist them.
     */
    public function markEmailSettingsFailed(?string $message): void
    {
        $now = now();
        $truncated = $message ? mb_substr($message, 0, 1000) : null;

        static::query()->whereKey($this->id)->update([
            'email_settings_failed_at' => $now,
            'email_settings_failed_message' => $truncated,
        ]);

        $this->setRawColumns([
            'email_settings_failed_at' => $now,
            'email_settings_failed_message' => $truncated,
        ]);
    }

    /**
     * Record that schedule editors have been notified of the failure. Same
     * targeted-update pattern as markEmailSettingsFailed.
     */
    public function markEmailSettingsFailureNotified(): void
    {
        $now = now();

        static::query()->whereKey($this->id)->update([
            'email_settings_failure_notified_at' => $now,
        ]);

        $this->setRawColumns([
            'email_settings_failure_notified_at' => $now,
        ]);
    }

    /**
     * Clear any previously recorded email-settings failure. Called from the
     * "Send Test Email" success path and after a successful retry through the
     * role's custom mailer. Uses a targeted UPDATE so an in-progress
     * pre-save test (which has unsaved email_settings on the model) does not
     * accidentally commit those settings here.
     */
    public function clearEmailSettingsFailure(): void
    {
        if ($this->email_settings_failed_at === null
            && $this->email_settings_failed_message === null
            && $this->email_settings_failure_notified_at === null) {
            return;
        }

        static::query()->whereKey($this->id)->update([
            'email_settings_failed_at' => null,
            'email_settings_failed_message' => null,
            'email_settings_failure_notified_at' => null,
        ]);

        $this->setRawColumns([
            'email_settings_failed_at' => null,
            'email_settings_failed_message' => null,
            'email_settings_failure_notified_at' => null,
        ]);
    }

    /**
     * True when a failure was recorded within the last 24 hours. While active,
     * the role's custom SMTP is skipped and its outgoing emails are not sent
     * (we do not fall back to the platform mailer); the custom SMTP is retried
     * automatically once this window expires.
     */
    public function isEmailSettingsFailureActive(): bool
    {
        return $this->email_settings_failed_at !== null
            && $this->email_settings_failed_at->gt(now()->subDay());
    }

    /**
     * Reflect a targeted column update onto this in-memory model instance
     * without disturbing other unsaved attribute changes.
     */
    protected function setRawColumns(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->setAttribute($key, $value);
            $this->syncOriginalAttribute($key);
        }
    }

    /**
     * Get the CalDAV settings attribute (decrypted and decoded)
     */
    public function getCalDAVSettingsAttribute($value)
    {
        $settings = $this->decryptCalDAVSettings($value);

        return $settings ?: null;
    }

    /**
     * Set the CalDAV settings attribute (encrypted and encoded)
     */
    public function setCalDAVSettingsAttribute($value)
    {
        if (is_null($value) || (is_array($value) && empty(array_filter($value)))) {
            $this->attributes['caldav_settings'] = null;

            return;
        }

        if (is_array($value)) {
            // Remove empty values
            $value = array_filter($value, function ($v) {
                return $v !== null && $v !== '';
            });

            if (empty($value)) {
                $this->attributes['caldav_settings'] = null;

                return;
            }

            $json = json_encode($value);
            $this->attributes['caldav_settings'] = \Illuminate\Support\Facades\Crypt::encryptString($json);
        } else {
            // If it's already a string, assume it's already encrypted JSON
            $this->attributes['caldav_settings'] = $value;
        }
    }

    /**
     * Check if role has CalDAV settings configured
     */
    public function hasCalDAVSettings(): bool
    {
        $settings = $this->getCalDAVSettings();

        return ! empty($settings) && ! empty($settings['server_url']) && ! empty($settings['username']);
    }

    /**
     * Get decrypted CalDAV settings as array
     * Uses raw attribute to avoid infinite recursion
     */
    public function getCalDAVSettings(): array
    {
        $rawValue = $this->attributes['caldav_settings'] ?? null;

        return $this->decryptCalDAVSettings($rawValue);
    }

    /**
     * Decrypt CalDAV settings from encrypted string
     * Consolidates decryption logic used by both attribute accessor and method
     */
    protected function decryptCalDAVSettings(?string $encryptedValue): array
    {
        if (! $encryptedValue) {
            return [];
        }

        try {
            $decrypted = \Illuminate\Support\Facades\Crypt::decryptString($encryptedValue);
            $decoded = json_decode($decrypted, true);

            return is_array($decoded) ? $decoded : [];
        } catch (\Exception $e) {
            \Log::warning('Failed to decrypt CalDAV settings for role', [
                'role_id' => $this->id ?? null,
                'subdomain' => $this->subdomain ?? null,
                'error' => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * Set encrypted CalDAV settings from array
     */
    public function setCalDAVSettings(array $settings): void
    {
        $this->caldav_settings = $settings;
    }

    /**
     * Check if this role syncs to CalDAV
     */
    public function syncsToCalDAV(): bool
    {
        return in_array($this->caldav_sync_direction, ['to', 'both']);
    }

    /**
     * Check if this role syncs from CalDAV
     */
    public function syncsFromCalDAV(): bool
    {
        return in_array($this->caldav_sync_direction, ['from', 'both']);
    }

    /**
     * Get the CalDAV sync direction as a human-readable string
     */
    public function getCalDAVSyncDirectionLabel()
    {
        return match ($this->caldav_sync_direction) {
            'to' => 'To CalDAV',
            'from' => 'From CalDAV',
            'both' => 'Bidirectional Sync',
            default => 'No Sync'
        };
    }

    /**
     * Get the maximum budget allowed per boost campaign for this role.
     * Selfhosted: returns global max_budget (no graduated restriction).
     * Hosted: returns role-specific override or config default.
     */
    public function getBoostMaxBudget(): float
    {
        if (! config('app.hosted')) {
            return (float) config('services.meta.max_budget', 1000);
        }

        if ($this->boost_max_budget !== null) {
            return (float) $this->boost_max_budget;
        }

        return (float) config('services.meta.boost_default_limit', 10);
    }

    /**
     * Calculate the budget limit tier for a given number of completed campaigns.
     */
    public static function calculateBoostLimitForCompletedCount(int $count): float
    {
        return match (true) {
            $count >= 50 => 1000,
            $count >= 20 => 500,
            $count >= 10 => 250,
            $count >= 5 => 100,
            $count >= 3 => 50,
            $count >= 1 => 25,
            default => (float) config('services.meta.boost_default_limit', 10),
        };
    }

    /**
     * Calculate the max concurrent campaigns for a given number of completed campaigns.
     */
    public static function calculateBoostMaxConcurrentForCompletedCount(int $count): int
    {
        return match (true) {
            $count >= 10 => 3,
            $count >= 3 => 2,
            default => 1,
        };
    }
}
