<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'name', 'name_en', 'slug', 'role_id',
    ];

    public function role()
    {
        return $this->belongsTo(\App\Models\Role::class);
    }
}