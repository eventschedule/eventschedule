<?php

namespace App\Utils;

class UrlUtils
{
    public static function clean($url)
    {
        $url = ltrim($url, 'https://');
        $url = ltrim($url, 'http://');
        $url = ltrim($url, 'www.');

        return $url;
    }
}
