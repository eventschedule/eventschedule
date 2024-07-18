<?php

namespace App\Utils;

class UrlUtils
{
    public static function clean($url)
    {
        $pattern = '/^(https?:\/\/)?(www\.)?/';
        return preg_replace($pattern, '', $url);
    }

    public static function getBrand($url)
    {
        $url = self::clean($url);
        $url = strtolower($url);

        $parts = explode('.', $url);

        return ucfirst($parts[0]);
    }
}
