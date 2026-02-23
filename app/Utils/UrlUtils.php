<?php

namespace App\Utils;

use Sqids\Sqids;

class UrlUtils
{
    private const SUBDOMAIN_PLATFORMS = [
        'bandcamp.com' => 'Bandcamp',
        'tumblr.com' => 'Tumblr',
        'substack.com' => 'Substack',
        'wordpress.com' => 'Wordpress',
        'blogspot.com' => 'Blogspot',
    ];

    private static ?Sqids $sqids = null;

    private static function getSqids(): Sqids
    {
        if (self::$sqids === null) {
            // Derive a deterministic alphabet from APP_KEY
            $key = config('app.key');
            $defaultAlphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $chars = str_split($defaultAlphabet);

            // Use APP_KEY as seed for a deterministic shuffle
            $hash = hash('sha256', $key);
            for ($i = count($chars) - 1; $i > 0; $i--) {
                $j = hexdec(substr($hash, ($i * 2) % 60, 2)) % ($i + 1);
                [$chars[$i], $chars[$j]] = [$chars[$j], $chars[$i]];
            }

            self::$sqids = new Sqids(implode('', $chars), 6);
        }

        return self::$sqids;
    }

    public static function encodeId($value)
    {
        if (! $value) {
            return null;
        }

        return self::getSqids()->encode([$value]);
    }

    public static function decodeId($value)
    {
        if (! $value) {
            return null;
        }

        // Try Sqids decode first (verify round-trip to avoid false positives)
        $decoded = self::getSqids()->decode($value);
        if (! empty($decoded) && self::getSqids()->encode($decoded) === $value) {
            return $decoded[0];
        }

        // Fall back to legacy base64 method for backwards compatibility
        $id = base64_decode($value);
        if (is_numeric($id)) {
            \Log::info('Legacy encoded ID used: consider updating URL', ['value' => $value]);

            return $id - 389278;
        }

        return null;
    }

    public static function decodeIdOrFail($value)
    {
        $decoded = self::decodeId($value);
        if ($decoded === null) {
            abort(404);
        }

        return $decoded;
    }

    /**
     * Generate an HMAC signature for an unsubscribe email token
     */
    public static function signEmail(string $email): string
    {
        return hash_hmac('sha256', $email, config('app.key'));
    }

    /**
     * Verify an HMAC signature for an unsubscribe email token
     */
    public static function verifyEmailSignature(string $email, string $signature): bool
    {
        return hash_equals(self::signEmail($email), $signature);
    }

    public static function clean($url)
    {
        $pattern = '/^(https?:\/\/)?(www\.)?/';

        $url = preg_replace($pattern, '', $url);

        return rtrim($url, '/');
    }

    public static function cleanSlug($slug)
    {
        $slug = preg_replace('/[^a-zA-Z0-9]/', '', trim($slug));
        $slug = strtolower(trim($slug));

        return $slug;
    }

    private static function getSubdomainPlatform($url)
    {
        $url = strtolower($url);
        foreach (self::SUBDOMAIN_PLATFORMS as $domain => $brand) {
            if (str_contains($url, $domain)) {
                return ['domain' => $domain, 'brand' => $brand];
            }
        }

        return null;
    }

    public static function getBrand($url)
    {
        $platform = self::getSubdomainPlatform($url);
        if ($platform) {
            return $platform['brand'];
        }

        $url = self::clean($url);
        $parts = explode('.', strtolower($url));

        return ucfirst($parts[0]);
    }

    public static function getHandle($url)
    {
        $platform = self::getSubdomainPlatform($url);
        if ($platform) {
            $cleaned = self::clean($url);
            $domain = explode('/', $cleaned)[0];
            $subdomain = explode('.', $domain)[0];

            return '@'.$subdomain;
        }

        $url = self::clean($url);
        $parts = explode('/', $url);

        // First part is always the domain, skip it
        // Get the last non-empty path segment
        $handle = '';
        for ($i = count($parts) - 1; $i >= 1; $i--) {
            if (! empty($parts[$i])) {
                $handle = $parts[$i];
                break;
            }
        }

        // Remove @ if already present, we'll add it back
        $handle = ltrim($handle, '@');

        return $handle ? '@'.$handle : '';
    }

    public static function getYouTubeThumbnail($url)
    {
        $videoId = self::extractYouTubeVideoId($url);

        if ($videoId) {
            return 'https://i.ytimg.com/vi/'.$videoId.'/mqdefault.jpg';
        }

        return null;
    }

    public static function extractYouTubeVideoId($url)
    {
        $parsedUrl = parse_url($url);

        $host = isset($parsedUrl['host']) ? strtolower($parsedUrl['host']) : '';
        if (! in_array($host, ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be'])) {
            return null;
        }

        if ($host === 'youtu.be') {
            $videoId = ltrim($parsedUrl['path'], '/');
        } else {
            if (isset($parsedUrl['path'])) {
                $path = ltrim($parsedUrl['path'], '/');
                $pathParts = explode('/', $path);

                if (count($pathParts) >= 2 && in_array($pathParts[0], ['watch', 'v', 'shorts', 'live'])) {
                    $videoId = $pathParts[1];
                } else {
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

        if (isset($videoId) && $videoId && preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoId)) {
            return $videoId;
        }

        return null;
    }

    public static function getYouTubeEmbed($url)
    {
        $videoId = self::extractYouTubeVideoId($url);

        if ($videoId) {
            return 'https://www.youtube-nocookie.com/embed/'.$videoId;
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
        if (! self::isUrlSafe($url)) {
            return null;
        }

        $title = '';
        $thumbnail_url = '';
        $lookup_url = 'https://noembed.com/embed?dataType=json&url='.urlencode($url);

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
    public static function isUrlSafe($url)
    {
        // Parse URL
        $parsedUrl = parse_url($url);

        if (! $parsedUrl || ! isset($parsedUrl['scheme']) || ! isset($parsedUrl['host'])) {
            return false;
        }

        // Only allow HTTP and HTTPS
        if (! in_array($parsedUrl['scheme'], ['http', 'https'])) {
            return false;
        }

        $host = $parsedUrl['host'];

        // Block private IP ranges and localhost (IPv4)
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            // Block private and reserved IPv4 ranges
            if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return false;
            }
        }

        // Block private/reserved IPv6 addresses
        if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Block loopback (::1)
            if ($host === '::1' || strtolower($host) === '0:0:0:0:0:0:0:1') {
                return false;
            }

            // Block link-local (fe80::/10)
            if (self::isIpv6InRange($host, 'fe80::', 10)) {
                return false;
            }

            // Block unique local (fc00::/7 - includes fd00::/8)
            if (self::isIpv6InRange($host, 'fc00::', 7)) {
                return false;
            }

            // Block IPv4-mapped IPv6 addresses (::ffff:0:0/96)
            // These could be used to bypass IPv4 checks
            if (stripos($host, '::ffff:') === 0) {
                return false;
            }

            // Block documentation addresses (2001:db8::/32)
            if (self::isIpv6InRange($host, '2001:db8::', 32)) {
                return false;
            }
        }

        // Block common internal hostnames
        $blockedHosts = [
            'localhost', '127.0.0.1', '::1',
            'metadata.google.internal',
            'instance-data', 'metadata.aws.amazon.com',
            '169.254.169.254', // AWS metadata
            'metadata.google.com',
        ];

        if (in_array(strtolower($host), $blockedHosts)) {
            return false;
        }

        // Resolve hostname to IP and check the resolved IP too (DNS rebinding protection)
        // Only do this for non-IP hosts
        if (! filter_var($host, FILTER_VALIDATE_IP)) {
            $resolvedIp = gethostbyname($host);
            // If resolution fails, gethostbyname returns the original hostname
            if ($resolvedIp !== $host) {
                // Check if resolved IP is private/reserved
                if (filter_var($resolvedIp, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Check if an IPv6 address is within a given prefix range
     */
    private static function isIpv6InRange(string $ip, string $prefix, int $prefixLength): bool
    {
        $ipBinary = inet_pton($ip);
        $prefixBinary = inet_pton($prefix);

        if ($ipBinary === false || $prefixBinary === false) {
            return false;
        }

        // Calculate how many full bytes and remaining bits to compare
        $fullBytes = intdiv($prefixLength, 8);
        $remainingBits = $prefixLength % 8;

        // Compare full bytes
        for ($i = 0; $i < $fullBytes; $i++) {
            if ($ipBinary[$i] !== $prefixBinary[$i]) {
                return false;
            }
        }

        // Compare remaining bits
        if ($remainingBits > 0 && $fullBytes < 16) {
            $mask = (0xFF << (8 - $remainingBits)) & 0xFF;
            if ((ord($ipBinary[$fullBytes]) & $mask) !== (ord($prefixBinary[$fullBytes]) & $mask)) {
                return false;
            }
        }

        return true;
    }

    public static function convertUrlsToLinks($text)
    {
        // Regex pattern to find <a> tags OR standalone URLs
        // Group 1: Matches <a> tags (to be ignored)
        // Group 2: Matches URLs (to be converted)
        $pattern = '/(<a[^>]*>.*?<\/a>)|(?<![=\'"])\b(https?|ftp):\/\/([^\s<]+)/i';

        return preg_replace_callback($pattern, function ($matches) {
            // If the first group is matched, it means we found an <a> tag.
            // Return it as-is without changes.
            if (! empty($matches[1])) {
                return $matches[1];
            }

            // Otherwise, it's a standalone URL. Wrap it in an <a> tag.
            $url = $matches[0];

            // Block javascript: and data: URLs to prevent XSS
            $lowerUrl = strtolower(trim($url));
            if (str_starts_with($lowerUrl, 'javascript:') || str_starts_with($lowerUrl, 'data:')) {
                return htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
            }

            // Escape URL for safe HTML attribute and display
            $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

            return '<a href="'.$escapedUrl.'" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">'.$escapedUrl.'</a>';
        }, $text);
    }

    public static function getUrlMetadata($url)
    {
        $result = [
            'redirect_url' => $url,
            'image_path' => null,
        ];

        // Validate URL for security
        if (! self::isUrlSafe($url)) {
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
            if (! empty($redirectUrls)) {
                $result['redirect_url'] = end($redirectUrls);
            } elseif ($httpCode >= 200 && $httpCode < 400 && self::isUrlSafe($finalUrl)) {
                $result['redirect_url'] = $finalUrl;
            }

            // Process social image
            if ($httpCode >= 200 && $httpCode < 400 && ! empty($html)) {
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
                                    // Use Laravel's storage directory instead of /tmp for security
                                    $tempDir = storage_path('app/temp');
                                    if (! is_dir($tempDir)) {
                                        mkdir($tempDir, 0700, true);
                                    }
                                    $filename = $tempDir.'/event_'.strtolower(\Str::random(32)).'.'.$extension;

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
            \Log::warning('URL metadata fetch failed: '.$e->getMessage());
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
        if (! in_array($contentType, $allowedTypes)) {
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
