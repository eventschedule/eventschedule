<?php

namespace App\Models;

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
        'accent_color',
        'font_color',
        'font_family',
        'name',
        'phone',
        'email',
        'website',
        'address1',
        'address2',
        'city',
        'state',
        'postal_code',
        'country_code',
        'language_code',
        'description',
        'accept_talent_requests',
        'accept_vendor_requests',
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

            /*
            $address = $model->fullAddress();

            if ($address && $address != $model->geo_address) {
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
            */
        });

        static::updating(function ($model) {
            if ($model->isDirty('email')) {
                $model->email_verified_at = null;
                $model->sendEmailVerificationNotification();
            }
        });

    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new CustomVerifyEmail('role', $this->subdomain));
    }

    public function events()
    {
        return $this->belongsToMany(Event::class);
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

    public function fullAddress()
    {
        $str = '';

        if ($this->address1) {
            $str .= $this->address1 . ', ';
        }

        if ($this->address2) {
            $str .= $this->address2 . ', ';
        }

        if ($this->city) {
            $str .= $this->city . ', ';
        }

        if ($this->state) {
            $str .= $this->state . ', ';
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

    public function isVendor()
    {
        return $this->type == 'vendor';
    }

    public function isTalent()
    {
        return $this->type == 'talent';
    }

    public function isCurator()
    {
        return $this->type == 'curator';
    }

    public function isClaimed()
    {
        return $this->email_verified_at != null && $this->user_id != null;
    }

    public function canHaveAddress()
    {
        return $this->isVenue() || $this->isVendor();
    }

    public function acceptRequests()
    {
        return $this->accept_talent_requests || $this->accept_vendor_requests;
    }

    public function getTypePlural()
    {
        if ($this->isVenue()) {
            return 'venues';
        } else if ($this->isTalent()) {
            return 'talent';
        } else if ($this->isVendor()) {
            return 'vendors';
        } else if ($this->isCurator()) {
            return 'curators';
        }
    }

    public function getProfileImageUrlAttribute($value)
    {
        if (! $value) {
            return '';
        }

        if (config('filesystems.default') == 'do_spaces') {
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

        if (config('filesystems.default') == 'do_spaces') {
            return 'https://eventschedule.nyc3.cdn.digitaloceanspaces.com/' . $value;
        } else if (config('filesystems.default') == 'local') {
            return url('/storage/' . $value);
        } else {
            return $value;
        }
    }
    
    public static function cleanSubdomain($name)
    {
        $subdomain = preg_replace('/[^a-zA-Z0-9]/', '', trim($name));
        $subdomain = strtolower(trim($subdomain));

        if (strlen($subdomain) < 4) {
            return strtolower(\Str::random(8));
        }
    
        return $subdomain;
    }

    public static function generateSubdomain($name)
    {
        $subdomain = self::cleanSubdomain($name);

        $originalSubdomain = $subdomain;
        $count = 1;

        $reserved = [
            'home',
            'privacy',
            'terms',
            'register',
            'venues',
            'talent',
            'vendors',
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
        ];

        while (self::where('subdomain', $subdomain)->exists() || in_array($subdomain, $reserved)) {
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
        if ($this->name) {
            return $this->name;
        } else {
            return $this->address1;
        }
    }

    public function getGuestUrl()
    {
        return route('role.view_guest', ['subdomain' => $this->subdomain]);
    }

    public function isPro()
    {
        return $this->plan_expires >= now()->format('Y-m-d');
    }

    public function toData()
    {
        $url = $this->getGuestUrl();
        $youtubeUrl = $this->getFirstVideoUrl();
        
        $data = $this->toArray();   
        $data['id'] = UrlUtils::encodeId($data['id']);
        $data['user_id'] = UrlUtils::encodeId($data['user_id']);
        $role['url'] = $url;
        $data['youtube_url'] = $youtubeUrl;

        return $data;
    }
}