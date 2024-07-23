<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'description',
        'visibility',
        'accept_talent_requests',
        'accept_vendor_requests',
        'use_24_hour_time',
    ];

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
}