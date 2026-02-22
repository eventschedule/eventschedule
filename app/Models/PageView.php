<?php

namespace App\Models;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PageView
{
    /**
     * Detect device type from user agent string
     */
    public static function detectDeviceType(?string $userAgent): string
    {
        if (! $userAgent) {
            return 'unknown';
        }

        $userAgent = strtolower($userAgent);

        // Check for tablets first (they often include mobile keywords)
        if (preg_match('/tablet|ipad|playbook|silk/i', $userAgent)) {
            return 'tablet';
        }

        // Check for mobile devices
        if (preg_match('/mobile|iphone|ipod|android.*mobile|windows phone|blackberry|opera mini|opera mobi/i', $userAgent)) {
            return 'mobile';
        }

        // Check for Android without mobile (likely tablet)
        if (preg_match('/android/i', $userAgent) && ! preg_match('/mobile/i', $userAgent)) {
            return 'tablet';
        }

        // Default to desktop for everything else
        return 'desktop';
    }

    /**
     * Check if user agent is a bot/crawler
     */
    public static function isBot(?string $userAgent): bool
    {
        // Real browsers ALWAYS send a user-agent - empty/short/unknown UAs are bots
        if (! $userAgent || strtolower(trim($userAgent)) === 'unknown' || strlen(trim($userAgent)) < 10) {
            return true;
        }

        $botPatterns = [
            // Major search engines
            'googlebot',
            'bingbot',
            'slurp',           // Yahoo
            'duckduckbot',
            'baiduspider',
            'yandexbot',
            'sogou',
            'exabot',
            'facebot',
            'facebookexternalhit',

            // Social media crawlers
            'twitterbot',
            'linkedinbot',
            'pinterest',
            'whatsapp',
            'telegrambot',
            'slackbot',
            'discordbot',

            // SEO and analytics tools
            'semrushbot',
            'ahrefsbot',
            'mj12bot',
            'dotbot',
            'rogerbot',
            'screaming frog',
            'seokicks',

            // Generic bot patterns
            'bot',
            'spider',
            'crawl',
            'scraper',
            'fetch',
            'headless',
            'phantom',
            'selenium',
            'puppeteer',
            'playwright',

            // Monitoring and uptime
            'pingdom',
            'uptimerobot',
            'statuscake',
            'site24x7',
            'newrelic',
            'datadog',

            // Libraries and tools
            'curl',
            'wget',
            'python-requests',
            'python-urllib',
            'java/',
            'libwww',
            'httpunit',
            'nutch',
            'go-http-client',
            'okhttp',
            'axios',
            'node-fetch',

            // Preview generators
            'preview',
            'embed',
            'thumbnail',

            // Other known bots
            'applebot',
            'mediapartners-google',
            'adsbot',
            'apis-google',
            'feedfetcher',
            'google-read-aloud',
            'lighthouse',
            'chrome-lighthouse',
            'pagespeed',
            'gtmetrix',

            // AI Assistants/Crawlers
            'claudebot',
            'claude-web',
            'anthropic',
            'gptbot',
            'chatgpt',
            'chatgpt-user',
            'oai-searchbot',
            'perplexitybot',
            'cohere-ai',
            'diffbot',
            'bytespider',
            'petalbot',
            'yisou',
            'megaindex',
            'blexbot',
            'icc-crawler',
            'amazonbot',

            // Security scanners
            'httpx',
            'nessus',
            'nikto',
            'qualys',
            'acunetix',
            'burp',
            'zap',
            'openvas',

            // Additional HTTP libraries
            'guzzle',
            'http_request',
            'libcurl',
            'php/',
            'ruby/',
            'libwww-perl',
            'scrapy',
            'aiohttp',
            'httplib',
            'requests/',
            'http.rb',

            // Headless/automation
            'chromeheadless',
            'headlesschrome',
            'jsdom',
            'zombiejs',

            // Additional Google/Bing crawlers
            'googlebot-image',
            'googlebot-video',
            'google-inspectiontool',
            'bingpreview',
            'yandeximages',
        ];

        $userAgentLower = strtolower($userAgent);

        foreach ($botPatterns as $pattern) {
            if (strpos($userAgentLower, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if request has suspicious headers (missing Accept-Language, generic Accept)
     */
    public static function isSuspiciousRequest(Request $request): bool
    {
        // Real browsers ALWAYS send Accept-Language
        $acceptLanguage = $request->header('Accept-Language');
        if (empty($acceptLanguage)) {
            return true;
        }

        // Real browsers send specific Accept headers, not just "*/*"
        $accept = $request->header('Accept');
        if (empty($accept) || $accept === '*/*') {
            return true;
        }

        return false;
    }

    /**
     * Generate a privacy-preserving hash for an IP address
     */
    protected static function getIpHash(string $ip): string
    {
        $dailySalt = config('app.key').now()->format('Y-m-d');

        return hash('sha256', $ip.$dailySalt);
    }

    /**
     * Check if IP has exceeded view limit for a role today
     */
    protected static function hasExceededViewLimit(int $roleId, string $ipHash): bool
    {
        $maxViewsPerIpPerRole = 10; // Max views to count per IP per role per day

        $cacheKey = "analytics_view:{$roleId}:{$ipHash}";

        // Use atomic increment to avoid race conditions
        $secondsUntilMidnight = now()->endOfDay()->diffInSeconds(now());
        $viewCount = Cache::increment($cacheKey);

        // Set expiry on first increment (increment creates key with no expiry)
        if ($viewCount === 1) {
            Cache::put($cacheKey, 1, $secondsUntilMidnight);
        }

        return $viewCount > $maxViewsPerIpPerRole;
    }

    /**
     * Record a page view (returns false if bot detected)
     */
    public static function recordView(Role $role, ?Event $event, Request $request): bool
    {
        $userAgent = $request->userAgent();

        // Skip recording for bots/crawlers
        if (self::isBot($userAgent)) {
            return false;
        }

        // Skip recording for requests with suspicious headers
        if (self::isSuspiciousRequest($request)) {
            return false;
        }

        // Skip recording if IP has exceeded view limit for this role today
        $ip = $request->ip();
        if ($ip) {
            $ipHash = self::getIpHash($ip);
            if (self::hasExceededViewLimit($role->id, $ipHash)) {
                return false;
            }
        }

        $deviceType = self::detectDeviceType($userAgent);

        // Increment schedule-level analytics
        AnalyticsDaily::incrementView($role->id, $deviceType);

        // Track referrer source (boost UTM overrides referrer categorization)
        $referrer = $request->header('referer');
        $sourceOverride = ($request->query('utm_source') === 'boost') ? 'boost' : null;
        AnalyticsReferrersDaily::incrementView($role->id, $referrer, $role->custom_domain, $sourceOverride);

        // Increment event-level analytics if event exists
        if ($event) {
            AnalyticsEventsDaily::incrementView($event->id, $deviceType);

            // Track appearance views for associated talents/venues
            $roles = $event->relationLoaded('roles') ? $event->roles : $event->roles()->get();

            foreach ($roles as $eventRole) {
                // Track talents and venues (not curators)
                if ($eventRole->isTalent() || $eventRole->isVenue()) {
                    AnalyticsAppearancesDaily::incrementView($eventRole->id, $role->id, $deviceType);
                }
            }
        }

        return true;
    }
}
