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
        'address1',
        'address2',
        'city',
        'state',
        'postal_code',
        'country_code',
        'description',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }    

    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}