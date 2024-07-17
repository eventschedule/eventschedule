<?php

namespace App\Utils;

class UrlUtils
{
    public static function clean($url)
    {
        $pattern = '/^(https?:\/\/)?(www\.)?/';
        return preg_replace($pattern, '', $url);
    }
}
