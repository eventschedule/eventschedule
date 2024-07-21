<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'type',
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
        return $this->address1 . ', ' . $this->state . ', ' . $this->postal_code . ', ' . $this->country_code;
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

}