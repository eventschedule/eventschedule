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

    public static function createDomain($name)
    {
        return str_replace([' ', '.'], ['-', ''], strtolower(trim($name)));
    }

    public static function getYouTubeEmbed($url)
    {
        $parsedUrl = parse_url($url);

        if (isset($parsedUrl['host']) && (strpos($parsedUrl['host'], 'youtube.com') !== false || strpos($parsedUrl['host'], 'youtu.be') !== false)) {
            
            if ($parsedUrl['host'] == 'youtu.be') {
                $videoId = ltrim($parsedUrl['path'], '/');
            } else {
                parse_str($parsedUrl['query'], $queryParams);
                $videoId = $queryParams['v'];
            }
    
            if (isset($videoId)) {
                return 'https://www.youtube.com/embed/' . $videoId;
            }
        }
    
        return false;
    }
}
