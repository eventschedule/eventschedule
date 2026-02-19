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
        'description',
        'description_en',
        'short_description',
        'short_description_en',
        'accept_requests',
        'require_account',
        'use_24_hour_time',
        'timezone',
        'formatted_address',
        'google_place_id',
        'geo_address',
        'geo_lat',
        'geo_lon',
        'show_email',
        'require_approval',
        'import_config',
        'custom_domain',
        'event_layout',
        'google_calendar_id',
        'google_webhook_id',
        'google_webhook_resource_id',
        'google_webhook_expires_at',
        'sync_direction',
        'request_terms',
        'request_terms_en',
        'last_notified_request_count',
        'email_settings',
        'custom_css',
        'event_custom_fields',
        'graphic_settings',
        'caldav_settings',
        'caldav_sync_direction',
        'caldav_sync_token',
        'caldav_last_sync_at',
        'agenda_ai_prompt',
        'agenda_show_times',
        'agenda_show_description',
        'slug_pattern',
        'translation_attempts',
        'last_translated_at',
        'direct_registration',
        'first_day_of_week',
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
        'event_custom_fields' => 'array',
        'last_translated_at' => 'datetime',
        'direct_registration' => 'boolean',
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
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->email) {
                $model->email = strtolower($model->email);
            }

            $model->description_html = MarkdownUtils::convertToHtml($model->description);
            $model->description_html_en = MarkdownUtils::convertToHtml($model->description_en);

            if (isset($model->custom_css)) {
                $model->custom_css = CssUtils::sanitizeCss($model->custom_css);
            }

            if ($model->accent_color == '#ffffff') {
                $model->accent_color = '#000000';
            }

            $address = $model->fullAddress();

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
                } catch (\Exception $e) {
                    \Log::warning('Geocoding failed: '.$e->getMessage());
                }
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('email') && config('app.hosted')) {
                $model->email_verified_at = null;
                $model->sendEmailVerificationNotification();
            }

            if ($model->isDirty(['name', 'short_description', 'description', 'address1', 'address2', 'city', 'state', 'request_terms'])) {
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

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot('level', 'dates_unavailable')
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
            ->withPivot('level', 'dates_unavailable')
            ->where('level', '!=', 'follower')
            ->orderBy('name');
    }

    public function followers()
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps()
            ->withPivot('level')
            ->where('level', 'follower')
            ->orderBy('pivot_created_at', 'desc');
    }

    public function venueEvents()
    {
        return $this->belongsToMany(Event::class, 'event_role', 'role_id', 'event_id')
            ->where('roles.type', 'venue');
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
    public function shortVenue($translate = true)
    {
        $name = $translate ? $this->translatedName() : $this->name;
        $city = $translate ? $this->translatedCity() : $this->city;
        $address = $translate ? $this->translatedAddress1() : $this->address1;

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

    public function isRegistered()
    {
        return $this->email || $this->phone;
    }

    public function isClaimed()
    {
        return ($this->email_verified_at != null || $this->phone_verified_at != null) && $this->user_id != null;
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
        } elseif (config('filesystems.default') == 'local') {
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
        } elseif (config('filesystems.default') == 'local') {
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
        } elseif (config('filesystems.default') == 'local') {
            return url('/storage/'.$value);
        } else {
            return $value;
        }
    }

    public static function cleanSubdomain($name)
    {
        $subdomain = Str::slug($name);

        // Check if significant content was lost during slugification
        // (indicates non-Latin characters that Str::slug can't transliterate)
        $nameChars = mb_strlen(preg_replace('/[^\p{L}\p{N}]/u', '', $name));
        $slugChars = strlen(preg_replace('/[^a-z0-9]/', '', $subdomain));

        if ($nameChars > 0 && $slugChars < $nameChars / 2) {
            try {
                $translated = GeminiUtils::translate($name, 'auto', 'en');

                if ($translated && strlen($translated) > 2) {
                    $subdomain = Str::slug($translated);
                } else {
                    \Log::warning('Subdomain translation returned empty for: '.$name);
                }
            } catch (\Exception $e) {
                \Log::warning('Subdomain translation failed for: '.$name.' - '.$e->getMessage());
            }
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

    public static function generateSubdomain($name = '')
    {
        if (! $name) {
            $name = strtolower(\Str::random(8));
        }

        $subdomain = self::cleanSubdomain($name);

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

    public function getFirstVideoUrl()
    {
        if (! $this->youtube_links) {
            return '';
        }

        $links = json_decode($this->youtube_links);

        if (count($links) >= 1 && isset($links[0]->url)) {
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

        if (count($links) >= 2) {
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
            return $this->custom_domain;
        }

        return route('role.view_guest', ['subdomain' => $this->subdomain]);
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

        if (config('app.is_testing')) {
            return true;
        }

        // Admins get all features
        if (auth()->check() && auth()->user()->isAdmin()) {
            return true;
        }

        // Check if user has an active Stripe subscription
        if ($this->hasActiveSubscription()) {
            return true;
        }

        // Check if user is on a generic trial (first year free)
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
        if (! config('app.hosted') || config('app.is_testing')) {
            return $query;
        }

        if (auth()->check() && auth()->user()->isAdmin()) {
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

    public function isEnterprise()
    {
        // Selfhosted deployments get all features
        if (! config('app.hosted')) {
            return true;
        }

        if (config('app.is_testing')) {
            return true;
        }

        // Admins get all features
        if (auth()->check() && auth()->user()->isAdmin()) {
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
        if (config('app.is_testing')) {
            return true;
        }

        // Check if user has an active Stripe subscription
        if ($this->hasActiveSubscription()) {
            return true;
        }

        // Check if user is on a generic trial (first year free)
        if ($this->onGenericTrial()) {
            return true;
        }

        // Legacy: Check the plan_expires field
        return $this->plan_expires >= now()->format('Y-m-d') && $this->plan_type == 'pro';
    }

    public function showBranding()
    {
        if (config('app.hosted')) {
            return ! $this->isPro();
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
        if (session()->has('translate') || request()->lang == 'en') {
            return false;
        }

        return $this->language_code == 'ar' || $this->language_code == 'he';
    }

    public function translatedName()
    {
        $value = $this->name;

        if ($this->name_en && (session()->has('translate') || request()->lang == 'en')) {
            $value = $this->name_en;
        }

        return $value;
    }

    public function translatedShortDescription()
    {
        $value = $this->short_description;

        if ($this->short_description_en && (session()->has('translate') || request()->lang == 'en')) {
            $value = $this->short_description_en;
        }

        return $value;
    }

    public function translatedDescription()
    {
        $value = $this->description_html;

        if ($this->description_html_en && (session()->has('translate') || request()->lang == 'en')) {
            $value = $this->description_html_en;
        }

        return $value;
    }

    public function translatedAddress1()
    {
        $value = $this->address1;

        if ($this->address1_en && (session()->has('translate') || request()->lang == 'en')) {
            $value = $this->address1_en;
        }

        return $value;
    }

    public function translatedAddress2()
    {
        $value = $this->address2;

        if ($this->address2_en && (session()->has('translate') || request()->lang == 'en')) {
            $value = $this->address2_en;
        }

        return $value;
    }

    public function translatedCity()
    {
        $value = $this->city;

        if ($this->city_en && (session()->has('translate') || request()->lang == 'en')) {
            $value = $this->city_en;
        }

        return $value;
    }

    public function translatedState()
    {
        $value = $this->state;

        if ($this->state_en && (session()->has('translate') || request()->lang == 'en')) {
            $value = $this->state_en;
        }

        return $value;
    }

    public function translatedRequestTerms()
    {
        $value = $this->request_terms;

        if ($this->request_terms_en && (session()->has('translate') || request()->lang == 'en')) {
            $value = $this->request_terms_en;
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
        return $this->newsletters()
            ->whereIn('status', ['sending', 'sent'])
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->whereNotNull('sent_at')
                        ->where('sent_at', '>=', now()->startOfMonth());
                })->orWhere(function ($q) {
                    $q->whereNull('sent_at')
                        ->where('status', 'sending')
                        ->where('created_at', '>=', now()->startOfMonth());
                });
            })
            ->count();
    }

    public function canSendNewsletter(): bool
    {
        $limit = $this->newsletterLimit();

        if (is_null($limit)) {
            return true;
        }

        return $this->newslettersSentThisMonth() < $limit;
    }

    public function groups()
    {
        return $this->hasMany(\App\Models\Group::class);
    }

    /**
     * Get event custom fields definition
     */
    public function getEventCustomFields(): array
    {
        return $this->event_custom_fields ?? [];
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
        return $this->google_calendar_id ?: 'primary';
    }

    /**
     * Check if this role has Google Calendar integration enabled
     */
    public function hasGoogleCalendarIntegration()
    {
        return ! is_null($this->google_calendar_id);
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
}
