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
        'is_open',
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

    public function events()
    {
        return $this->belongsToMany(Event::class);
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
                    ->orderBy('name');
    }    

    public function venueEvents()
    {
        return $this->hasMany(Event::class, 'venue_id');
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

    public function isSchedule()
    {
        return $this->type == 'schedule';
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
            'newyork',
            'london',
            'paris',
            'tokyo',
            'shanghai',
            'singapore',
            'dubai',
            'hongkong',
            'beijing',
            'sydney',
            'losangeles',
            'sanfrancisco',
            'chicago',
            'moscow',
            'seoul',
            'mumbai',
            'berlin',
            'bangkok',
            'delhi',
            'istanbul',
            'miami',
            'boston',
            'barcelona',
            'madrid',
            'rome',
            'vienna',
            'amsterdam',
            'brussels',
            'cairo',
            'jakarta',
            'mexicocity',
            'kualalumpur',
            'buenosaires',
            'manila',
            'lisbon',
            'athens',
            'rio',
            'prague',
            'stockholm',
            'copenhagen',
            'toronto',
            'montreal',
            'zurich',
            'helsinki',
            'dublin',
            'warsaw',
            'budapest',
            'venice',
            'bucharest',
            'hamburg',
            'milan',
            'vancouver',
            'brisbane',
            'oslo',
            'birmingham',
            'edmonton',
            'munich',
            'kyoto',
            'stpetersburg',
            'nairobi',
            'lagos',
            'casablanca',
            'doha',
            'auckland',
            'kolkata',
            'riyadh',
            'abuja',
            'chennai',
            'jaipur',
            'lahore',
            'karachi',
            'vienna',
            'porto',
            'telaviv',
            'sofia',
            'minsk',
            'capetown',
            'manchester',
            'leeds',
            'durban',
            'brisbane',
            'perth',
            'adelaide',
            'goldcoast',
            'quezoncity',
            'beirut',
            'algiers',
            'medellin',
            'guadalajara',
            'valencia',
            'lyon',
            'marseille',
            'naples',
            'florence',
            'malaga',
            'seville',
            'santodomingo',
            'phnompenh',
            'kingston',
            'kuwaitcity',
            'minneapolis',
            'detroit',
        ];

        if (config('app.hosted') && in_array($subdomain, $reserved)) {
            $subdomain = '';
        }

        if (strlen($subdomain) < 4) {
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

        if (count($links) >= 1) {
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

    public function getDisplayName()
    {
        if ($this->translatedName()) {
            return $this->translatedName();
        } else {
            return $this->translatedAddress1();
        }
    }

    public function getGuestUrl()
    {
        if (! $this->isRegistered()) {
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

    public function isPro()
    {
        if (! config('app.hosted')) {
            return true;
        }

        return $this->plan_expires >= now()->format('Y-m-d') && $this->plan_type == 'pro';
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
        if (! $this->name_en) {
            return $this->name;
        }

        if (session()->has('translate') || request()->lang == 'en') {
            return $this->name_en;
        }

        return $this->name;
    }

    public function translatedDescription()
    {
        if (! $this->description_html_en) {
            return $this->description_html;
        }

        if (session()->has('translate') || request()->lang == 'en') {
            return $this->description_html_en;
        }

        return $this->description_html;
    }   

    public function translatedAddress1()
    {
        if (! $this->address1_en) {
            return $this->address1;
        }
        
        if (session()->has('translate') || request()->lang == 'en') {
            return $this->address1_en;
        }

        return $this->address1;
    }   

    public function translatedAddress2()
    {
        if (! $this->address2_en) {
            return $this->address2;
        }

        if (session()->has('translate') || request()->lang == 'en') {
            return $this->address2_en;
        }

        return $this->address2;
    }

    public function translatedCity()
    {
        if (! $this->city_en) {
            return $this->city;
        }
     
        if (session()->has('translate') || request()->lang == 'en') {   
            return $this->city_en;
        }

        return $this->city;
    }
    
    public function translatedState()
    {
        if (! $this->state_en) {
            return $this->state;
        }
        
        if (session()->has('translate') || request()->lang == 'en') {
            return $this->state_en;
        }

        return $this->state;
    }
    
}
