<?php

namespace App\Utils;

class HostedUtils
{
    public static function isHostedOrAdmin() 
    {
        if (config('app.hosted')) {
            return true;
        }

        return auth()->user() && auth()->user()->isAdmin();
    }
}