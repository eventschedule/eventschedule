<?php

namespace App\Utils;

class UrlUtils
{
    public static function encodeId($value)
    {
        return base64_encode($value + 389278);
    }

    public static function decodeId($value)
    {
        return base64_decode($value) - 389278;
    }

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

    public static function getBackUrl()
    {
        $previous = url()->previous();

        while (strpos($previous, 'edit') || strpos($previous, 'add') || strpos($previous, 'update')) {
            $parts = explode('/', $previous);
            array_pop($parts);
            $previous = implode('/', $parts);
        }

        return $previous;
    }
}