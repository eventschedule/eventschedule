<?php

namespace App\Utils;

class HostedUtils
{
    public static function showHostedOrAdmin() {
        if (config('app.hosted')) {
            return true;
        }

        return auth()->user() && auth()->user()->isAdmin();
    }
}