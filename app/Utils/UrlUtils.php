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

    public static function getUrlInfo($url)
    {
        // Validate and sanitize URL
        if (!self::isUrlSafe($url)) {
            return null;
        }
        
        $title = '';
        $thumbnail_url = '';
        $lookup_url = 'https://noembed.com/embed?dataType=json&url=' . urlencode($url);

        // Use cURL instead of file_get_contents for better security control
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $lookup_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_MAXREDIRS => 3,
            CURLOPT_USERAGENT => 'EventSchedule/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_FOLLOWLOCATION => true,
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response && $httpCode >= 200 && $httpCode < 300) {
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

    /**
     * Validate if URL is safe to make requests to
     */
    private static function isUrlSafe($url)
    {
        // Parse URL
        $parsedUrl = parse_url($url);
        
        if (!$parsedUrl || !isset($parsedUrl['scheme']) || !isset($parsedUrl['host'])) {
            return false;
        }

        // Only allow HTTP and HTTPS
        if (!in_array($parsedUrl['scheme'], ['http', 'https'])) {
            return false;
        }

        $host = $parsedUrl['host'];
        
        // Block private IP ranges and localhost
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return !filter_var($host, FILTER_VALIDATE_IP, 
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }
        
        // Block common internal hostnames
        $blockedHosts = [
            'localhost', '127.0.0.1', '::1',
            'metadata.google.internal',
            'instance-data', 'metadata.aws.amazon.com'
        ];
        
        if (in_array(strtolower($host), $blockedHosts)) {
            return false;
        }

        // Resolve hostname to IP to check for private ranges
        $ip = gethostbyname($host);
        if ($ip !== $host && filter_var($ip, FILTER_VALIDATE_IP)) {
            return !filter_var($ip, FILTER_VALIDATE_IP, 
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
        }

        return true;
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

    public static function getUrlMetadata($url)
    {
        $result = [
            'redirect_url' => $url,
            'image_path' => null
        ];

        // Validate URL for security
        if (!self::isUrlSafe($url)) {
            return $result;
        }

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3, // Reduced from 10
                CURLOPT_TIMEOUT => 15, // Reduced from 30
                CURLOPT_CONNECTTIMEOUT => 10, // Reduced from 30
                CURLOPT_USERAGENT => 'EventSchedule/1.0', // Don't impersonate browsers
                CURLOPT_HTTPHEADER => [
                    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.5',
                ],
                CURLOPT_HEADER => true,
                // Security settings
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_MAXFILESIZE => 10485760, // 10MB limit
            ]);

            $response = curl_exec($ch);
            
            if ($response === false) {
                curl_close($ch);
                return $result;
            }
            
            // Process redirect URL
            $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $headers = substr($response, 0, $headerSize);
            $html = substr($response, $headerSize);
            
            $redirectUrls = [];
            foreach (explode("\n", $headers) as $header) {
                if (stripos($header, 'Location:') === 0) {
                    $redirectUrl = trim(substr($header, 9));
                    // Validate redirect URL too
                    if (self::isUrlSafe($redirectUrl)) {
                        $redirectUrls[] = $redirectUrl;
                    }
                }
            }

            $finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            
            curl_close($ch);

            // Determine redirect URL
            if (!empty($redirectUrls)) {
                $result['redirect_url'] = end($redirectUrls);
            } elseif ($httpCode >= 200 && $httpCode < 400 && self::isUrlSafe($finalUrl)) {
                $result['redirect_url'] = $finalUrl;
            }

            // Process social image
            if ($httpCode >= 200 && $httpCode < 400 && !empty($html)) {
                // Look for Open Graph image meta tag
                if (preg_match('/<meta[^>]*property=["\']og:image["\'][^>]*content=["\']([^"\']*)["\']/', $html, $matches) ||
                    preg_match('/<meta[^>]*content=["\']([^"\']*)["\'][^>]*property=["\']og:image["\']/', $html, $matches)) {
                    
                    if ($imageUrl = $matches[1]) {
                        // Validate image URL
                        if (self::isUrlSafe($imageUrl)) {
                            // Use secure method to download image
                            $imageContents = self::downloadImageSecurely($imageUrl);
                            if ($imageContents !== false) {
                                $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                                // Validate extension
                                $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                                if (in_array(strtolower($extension), $allowedExtensions)) {
                                    $filename = '/tmp/event_' . strtolower(\Str::random(32)) . '.' . $extension;
                                    
                                    if (file_put_contents($filename, $imageContents) !== false) {
                                        $result['image_path'] = $filename;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // Log error but don't expose it
            \Log::warning('URL metadata fetch failed: ' . $e->getMessage());
        }

        return $result;
    }

    /**
     * Securely download image with size and type validation
     */
    private static function downloadImageSecurely($imageUrl)
    {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $imageUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_USERAGENT => 'EventSchedule/1.0',
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
            CURLOPT_MAXFILESIZE => 5242880, // 5MB limit for images
        ]);

        $imageData = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
        curl_close($ch);

        if ($imageData === false || $httpCode !== 200) {
            return false;
        }

        // Validate content type
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($contentType, $allowedTypes)) {
            return false;
        }

        // Validate actual image data
        $imageInfo = getimagesizefromstring($imageData);
        if ($imageInfo === false) {
            return false;
        }

        return $imageData;
    }
}