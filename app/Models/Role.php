<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Utils\MarkdownUtils;

class Role extends Model
{
    protected $fillable = [
        'type',
        'design',
        'background',
        'background_rotation',
        'background_colors',
        'background_color',
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
        'visibility',
        'accept_talent_requests',
        'accept_vendor_requests',
        'use_24_hour_time',
        'timezone',
    ];

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->email = strtolower($model->email);
            $model->description_html = MarkdownUtils::convertToHtml($model->description);

            $address = $model->fullAddress();

            if ($address != $model->geo_address) {
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
    }

    public function members()
    {
        return $this->belongsToMany(User::class)                    
                    ->withTimestamps()
                    ->withPivot('level')
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

        if ($this->country_code) {
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

    public function acceptRequests()
    {
        return $this->accept_talent_requests || $this->accept_vendor_requests;
    }

    public function getTypePlural()
    {
        if ($this->type == 'venue') {
            return 'venues';
        } else if ($this->type = 'talent') {
            return 'talent';
        } else {
            return 'vendors';
        }
    }

    public function getProfileImageUrlAttribute($value)
    {
        if (! $value) {
            return '';
        }

        return config('filesystems.default') == 'local' ? url('/storage/' . $value) : $value;
    }

    public function getBackgroundImageUrlAttribute($value)
    {
        if (! $value) {
            return '';
        }

        return config('filesystems.default') == 'local' ? url('/storage/' . $value) : $value;
    }

    public static function generateSubdomain($name)
    {
        $subdomain = preg_replace('/[^a-zA-Z0-9- ]/', '', trim($name));
        $subdomain = str_replace([' '], ['-'], strtolower(trim($subdomain)));
        
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
        ];

        while (self::where('subdomain', $subdomain)->exists() || in_array($subdomain, $reserved)) {
            $subdomain = $originalSubdomain . $count;
            $count++;
        }

        return $subdomain;
    }
}