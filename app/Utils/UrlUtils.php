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

        $id = base64_decode($value);

        if (is_numeric($id)) {
            return $id - 389278;
        } else {
            return null;
        }
    }

    public static function clean($url)
    {
        $pattern = '/^(https?:\/\/)?(www\.)?/';
        return preg_replace($pattern, '', $url);
    }

    public static function cleanSlug($slug)
    {
        $slug = preg_replace('/[^a-zA-Z0-9]/', '', trim($slug));
        $slug = strtolower(trim($slug));

        return $slug;
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
                // Check path for video ID first
                if (isset($parsedUrl['path'])) {
                    $path = ltrim($parsedUrl['path'], '/');
                    $pathParts = explode('/', $path);
                    
                    // Handle /watch/VIDEO_ID or /v/VIDEO_ID format
                    if (count($pathParts) >= 2 && ($pathParts[0] === 'watch' || $pathParts[0] === 'v')) {
                        $videoId = $pathParts[1];
                    } else {
                        // Fall back to query parameter
                        if (isset($parsedUrl['query'])) {
                            parse_str($parsedUrl['query'], $queryParams);
                            $videoId = isset($queryParams['v']) ? $queryParams['v'] : null;
                        } else {
                            $videoId = null;
                        }
                    }
                } else {
                    $videoId = null;
                }
            }
    
            if (isset($videoId) && $videoId) {
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

    public static function convertUrlsToLinks($text)
    {
        // Convert URLs to links while preserving the rest of the text
        $pattern = '/\bhttps?:\/\/[^\s<>]+/i';
        $text = preg_replace_callback($pattern, function($matches) {
            $url = rtrim($matches[0], '.,!?:;'); // Remove any trailing punctuation
            $displayUrl = preg_replace('/^https?:\/\/(www\.)?/', '', $url); // Remove http(s):// and www. from display text
            $displayUrl = rtrim($displayUrl, '/'); // Remove trailing slashes from display text
            return '<a href="' . htmlspecialchars($url, ENT_QUOTES) . '" target="_blank">' . htmlspecialchars($displayUrl, ENT_QUOTES) . '</a>';
        }, $text);
        
        return $text;
    }
}