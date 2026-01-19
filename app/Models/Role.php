<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use App\Utils\MarkdownUtils;
use App\Utils\UrlUtils;
use App\Notifications\VerifyEmail as CustomVerifyEmail;
use App\Utils\GeminiUtils;
use App\Utils\CssUtils;

class Role extends Model implements MustVerifyEmail
{
    use Notifiable, MustVerifyEmailTrait;

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
        'accept_requests',
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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'google_webhook_expires_at' => 'datetime',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'email_settings',
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
                $urlAddress = urlencode($address);
                $url = "https://maps.googleapis.com/maps/api/geocode/json?address={$urlAddress}&key=" . config('services.google.backend');
                $response = file_get_contents($url);
                $responseData = json_decode($response, true);

                if ($responseData['status'] == 'OK') {
                    $latitude = $responseData['results'][0]['geometry']['location']['lat'];
                    $longitude = $responseData['results'][0]['geometry']['location']['lng'];
                            
                    $model->formatted_address = $responseData['results'][0]['formatted_address'];
                    $model->google_place_id = $responseData['results'][0]['place_id'];
                    $model->geo_address = $address;
                    $model->geo_lat = $latitude;
                    $model->geo_lon = $longitude;
                }                
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty('email') && config('app.hosted')) {
                $model->email_verified_at = null;
                $model->sendEmailVerificationNotification();
            }

            if ($model->isDirty('name')) {
                $model->name_en = null;
            }

            if ($model->isDirty('description')) {
                $model->description_en = null;
                $model->description_html_en = null;
            }

            if ($model->isDirty('address1')) {
                $model->address1_en = null;
            }

            if ($model->isDirty('address2')) {
                $model->address2_en = null;
            }

            if ($model->isDirty('city')) {
                $model->city_en = null;
            }

            if ($model->isDirty('state')) {
                $model->state_en = null;
            }

            if ($model->isDirty('request_terms')) {
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
                    ->withPivot('id', 'name_translated', 'description_html_translated', 'is_accepted', 'group_id', 'google_event_id')
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

    public function fullAddress()
    {
        $str = '';

        if ($this->translatedAddress1()) {
            $str .= $this->translatedAddress1() . ', ';
        }

        if ($this->translatedAddress2()) {
            $str .= $this->translatedAddress2() . ', ';
        }

        if ($this->translatedCity()) {
            $str .= $this->translatedCity() . ', ';
        }

        if ($this->translatedState()) {
            $str .= $this->translatedState() . ', ';
        }

        if ($this->postal_code) {
            $str .= $this->postal_code . ', ';
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

        if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
            return 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/' . $value;
        } else if (config('filesystems.default') == 'local') {
            return url('/storage/' . $value);
        } else {
            return $value;
        }
    }

    public function getProfileImageUrlAttribute($value)
    {
        if (! $value) {
            return '';
        }

        if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
            return 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/' . $value;
        } else if (config('filesystems.default') == 'local') {
            return url('/storage/' . $value);
        } else {
            return $value;
        }
    }

    public function getBackgroundImageUrlAttribute($value)
    {
        if (! $value) {
            return '';
        }

        if (config('app.hosted') && config('filesystems.default') == 'do_spaces') {
            return 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/' . $value;
        } else if (config('filesystems.default') == 'local') {
            return url('/storage/' . $value);
        } else {
            return $value;
        }
    }
    
    public static function cleanSubdomain($name)
    {
        $subdomain = Str::slug($name);

        if (strlen($subdomain) <= 2) {            
            $translated = GeminiUtils::translate($name, 'auto', 'en');

            if ($translated && strlen($translated) > 2) {
                $subdomain = Str::slug($translated);
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
    
        return $subdomain;
    }

    public static function generateSubdomain($name = "")
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
            $current = $current ? $current . '-' . $part : $part;
            $variations[] = $current;
        }

        // Check each variation in order - use the first available one
        foreach ($variations as $variation) {
            if (!self::where('subdomain', $variation)->exists()) {
                $subdomain = $variation;
                break;
            }
        }

        // If no variation is available, use the original subdomain with a number suffix
        $originalSubdomain = $subdomain;
        $count = 1;

        while (self::where('subdomain', $subdomain)->exists()) {
            $subdomain = $originalSubdomain . $count;
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

    public function getGuestUrl()
    {
        if (! $this->isClaimed()) {
            return '';
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
        $data->url = $this->getGuestUrl();
        $data->type = $this->type;
        $data->name = $this->name;
        $data->email = $this->email;
        $data->website = $this->website;
        $data->description = $this->description;

        if ($this->isVenue()) {
            $data->address1 = $this->address1;
            $data->city = $this->city;
            $data->state = $this->state;
            $data->postal_code = $this->postal_code;
            $data->country_code = $this->country_code;
        }
                
        return $data;
    }

    public function isPro()
    {
        if (! config('app.hosted')) {
            return true;
        }

        return $this->plan_expires >= now()->format('Y-m-d') && $this->plan_type == 'pro';
    }

    public function isWhiteLabeled()
    {
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

    public function groups()
    {
        return $this->hasMany(\App\Models\Group::class);
    }

    /**
     * Get the import configuration as an array
     */
    public function getImportConfigAttribute($value)
    {
        if (!$value) {
            return [
                'urls' => [],
                'cities' => []
            ];
        }
        
        $config = json_decode($value, true);
        return $config ?: [
            'urls' => [],
            'cities' => []
        ];
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
        if (!$this->isCurator()) {
            return false;
        }
        
        $config = $this->import_config;
        return !empty($config['urls']) || !empty($config['cities']);
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
        return !is_null($this->google_calendar_id);
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
        return match($this->sync_direction) {
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
        if (!$value) {
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
            $value = array_filter($value, function($v) {
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
        return !empty($settings) && !empty($settings['host']) && !empty($settings['username']);
    }

    /**
     * Get decrypted email settings as array
     * Uses raw attribute to avoid infinite recursion
     */
    public function getEmailSettings(): array
    {
        $rawValue = $this->attributes['email_settings'] ?? null;
        if (!$rawValue) {
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
}
