<?php

namespace App\Models;

use App\Notifications\VerifyEmail as CustomVerifyEmail;
use App\Traits\RoleBillable;
use App\Utils\CssUtils;
use App\Utils\GeminiUtils;
use App\Utils\MarkdownUtils;
use App\Utils\UrlUtils;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Role extends Model implements MustVerifyEmail
{
    use MustVerifyEmailTrait, Notifiable, RoleBillable;

    protected $fillable = [
        'type',
        'is_unlisted',
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
        'carpool_enabled',
        'first_day_of_week',
        'approved_subdomains',
        'default_curator_ids',
        'sponsor_logos',
        'sponsor_section_title',
        'sponsor_section_title_en',
        'custom_labels',
        'ai_style_instructions',
        'ai_content_instructions',
        'hide_past_events',
        'draft_events_default',
        'default_event_visibility',
        'hide_videos',
        'show_accessibility_widget',
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
        'renewal_reminder_sent_at' => 'datetime',
        'custom_labels' => 'array',
        'hide_past_events' => 'boolean',
        'draft_events_default' => 'boolean',
        'hide_videos' => 'boolean',
        'show_accessibility_widget' => 'boolean',
        'email_settings_failed_at' => 'datetime',
        'email_settings_failure_notified_at' => 'datetime',
        'event_categories' => 'array',
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
                ->whereIn('status', ['active', 'paused', 'pending_payment'])
                ->get();

            foreach ($activeCampaigns as $campaign) {
                try {
                    if ($campaign->meta_campaign_id && \App\Services\MetaAdsService::isBoostConfigured()) {
                        $metaService = app()->make(\App\Services\MetaAdsService::class);
                        $metaService->deleteCampaign($campaign);
                    }

                    $campaign->update(['status' => 'cancelled', 'meta_status' => $campaign->meta_campaign_id ? 'DELETED' : null]);

                    if (config('app.hosted') && ! config('app.is_testing')) {
                        $billingService = new \App\Services\BoostBillingService;
                        if ($campaign->billing_status === 'charged') {
                            $campaign->actual_spend && $campaign->actual_spend > 0
                                ? $billingService->refundUnspent($campaign)
                                : $billingService->refundFull($campaign);
                        } elseif ($campaign->billing_status === 'pending' && $campaign->stripe_payment_intent_id) {
                            $billingService->cancelPaymentIntent($campaign);
                        }
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
                $model->sendEmailVerificationNotification();
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
        static::updated(function ($model) {
            if ($model->wasChanged('translation_language_code')) {
                \App\Jobs\RegenerateRoleTranslations::dispatch($model);
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

    public function events()
    {
        return $this->belongsToMany(Event::class)
            ->withPivot('id', 'name_translated', 'description_translated', 'description_html_translated', 'is_accepted', 'group_id', 'google_event_id', 'caldav_event_uid', 'caldav_event_etag')
            ->using(EventRole::class);
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
            // Draft/internal and unlisted events must not leak booking
            // relationships onto the public wall.
            ->where('events.is_draft', false)
            ->where('events.is_private', false)
            ->where('events.is_cancelled', false)
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
    public function shortVenue($translate = true, $forceEnglish = false)
    {
        if ($forceEnglish) {
            $name = $this->englishName();
            $city = $this->englishCity();
            $address = $this->englishAddress1();
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

    public function hasConfiguredBackground(): bool
    {
        return match ($this->background) {
            'gradient' => filled($this->background_colors),
            'solid' => filled($this->background_color),
            'image' => filled($this->background_image) || filled($this->background_image_url),
            default => false,
        };
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

    public function autoCurateEvent(Event $event): void
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

            $isAccepted = ! $curator->require_approval
                || ($curator->approved_subdomains && in_array($this->subdomain, $curator->approved_subdomains));

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
                    $translated = GeminiUtils::translate($name, 'auto', 'en');
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

        $reserved = [
            'eventschedule',
            'event',
            'events',
            'admin',
            'schedule',
            'availability',
            'requests',
            'profile',
            'followers',
            'team',
            'plan',
            'home',
            'privacy',
            'terms',
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
        ];

        if (config('app.hosted') && in_array($subdomain, $reserved)) {
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
    private static function isLossySlug($source, $slug): bool
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

    public function showBranding()
    {
        if (config('app.hosted')) {
            return $this->actualPlanTier() === 'free';
        } else {
            return ! $this->isWhiteLabeled();
        }
    }

    public function acceptEventRequests()
    {
        if ($this->isClaimed()) {
            return $this->accept_requests;
        }

        return true;
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
    public function isEnglish(): bool
    {
        return strtolower($this->language_code ?? 'en') === 'en';
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
            'buy_tickets' => 'Buy Tickets',
            'category' => 'Category',
            'clear_filters' => 'Clear Filters',
            'done' => 'Done',
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

    public function getEventCustomFieldValidationRules(): array
    {
        $rules = [];
        foreach ($this->getEventCustomFields() as $fieldKey => $field) {
            $type = $field['type'] ?? 'string';
            $required = ! empty($field['required']);
            $key = "custom_field_values.{$fieldKey}";
            $fieldRules = [$required ? 'required' : 'nullable'];

            if (in_array($type, ['string', 'multiline_string'], true)) {
                $fieldRules[] = 'string';
                $fieldRules[] = 'max:5000';
            } elseif ($type === 'switch') {
                $fieldRules[] = 'in:0,1';
            } elseif ($type === 'date') {
                $fieldRules[] = 'date';
            } elseif ($type === 'dropdown') {
                $options = array_values(array_filter(array_map('trim', explode(',', $field['options'] ?? ''))));
                if (! empty($options)) {
                    $fieldRules[] = \Illuminate\Validation\Rule::in($options);
                }
            } elseif ($type === 'multiselect') {
                $fieldRules[] = 'array';
                $options = array_values(array_filter(array_map('trim', explode(',', $field['options'] ?? ''))));
                if (! empty($options)) {
                    $rules["{$key}.*"] = ['string', \Illuminate\Validation\Rule::in($options)];
                }
            }

            $rules[$key] = $fieldRules;
        }

        return $rules;
    }

    public function getEventCustomFieldValidationAttributes(): array
    {
        $attrs = [];
        foreach ($this->getEventCustomFields() as $fieldKey => $field) {
            // Match the field label shown in the AP event form (event/edit.blade.php), which keys
            // off the admin's UI locale - not the guest translate toggle. This runs in the AP
            // (EventCreate/UpdateRequest), where the guest `translate` session flag is never set.
            $name = (app()->getLocale() === 'en' && ! empty($field['name_en']))
                ? $field['name_en']
                : ($field['name'] ?? $fieldKey);
            $attrs["custom_field_values.{$fieldKey}"] = $name;
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
