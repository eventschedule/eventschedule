<?php

namespace App\Utils;

class UrlUtils
{
    public static function encodeId($value)
    {
        if (! $value) {
            return null;
        }

        return base64_encode($value + 389278);
    }

    public static function decodeId($value)
    {
        if (! $value) {
            return null;
        }

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

    public static function getUrlDetails($url)
    {
        $title = '';
        $thumbnail_url = '';
        $lookup_url = 'https://noembed.com/embed?dataType=json&url=' . urlencode($url);

        if ($response = @file_get_contents($lookup_url)) {
            $json = json_decode($response);

            if (property_exists($json, 'title')) {
                $title = $json->title;
            }

            if (property_exists($json, 'thumbnail_url')) {
                $thumbnail_url = $json->thumbnail_url;
            }
        }
                    
        $obj = new \stdClass;
        $obj->name = $title;
        $obj->url = rtrim($url, '/');
        $obj->thumbnail_url = $thumbnail_url;

        return $obj;
    }    
}