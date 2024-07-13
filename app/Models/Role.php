<?php

namespace App\Models;

class Role
{
    public function users()
    {
        return $this->belongsToMany(User::class);
    }    
}