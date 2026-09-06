<?php

namespace App\Models;

use App\Services\GeoIpService;
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
    public static function isSuspiciousRequest(Request $request, bool $expectDocumentAccept = true): bool
    {
        // Real browsers ALWAYS send Accept-Language
        $acceptLanguage = $request->header('Accept-Language');
        if (empty($acceptLanguage)) {
            return true;
        }

        // Real browsers send specific Accept headers, not just "*/*" - when they are asking
        // for a DOCUMENT. A background request cannot satisfy this: navigator.sendBeacon()
        // takes no headers at all and both it and fetch() default to `Accept: */*`, so the
        // marketing visit beacon opts out of this one check and keeps the rest.
        if ($expectDocumentAccept) {
            $accept = $request->header('Accept');
            if (empty($accept) || $accept === '*/*') {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a privacy-preserving hash for an IP address.
     *
     * Public alongside clientIp() and incrementDailyCounter() so a rate cap outside this class
     * (AnalyticsMissingDaily's per-visitor budget) keys off the same salted daily hash instead of
     * inventing a second, weaker identity for the same visitor.
     */
    public static function getIpHash(string $ip): string
    {
        $dailySalt = config('app.key').now()->format('Y-m-d');

        return hash('sha256', $ip.$dailySalt);
    }

    /**
     * Seconds from now until the end of the current day - for a cache TTL that resets at midnight.
     *
     * Operand order matters: now()->endOfDay()->diffInSeconds(now()) is NEGATIVE under Carbon's
     * signed diffs, and Cache::add/put reject a non-positive TTL (storing nothing, or leaving the
     * key with no expiry so it never resets). max(1, ...) also guards the day's final second.
     */
    public static function secondsUntilEndOfDay(): int
    {
        return max(1, (int) now()->diffInSeconds(now()->endOfDay()));
    }

    /**
     * Whether this is the first time today a given IP+UA has been seen for $bucket.
     *
     * Uses the daily-salted getIpHash() so it is privacy-preserving, and - unlike a
     * session cookie - it is robust against cookieless bots that would otherwise get a
     * fresh session (and thus recount) on every request.
     */
    public static function isFirstDailyVisit(string $bucket, ?string $ip, ?string $userAgent): bool
    {
        if (! $ip) {
            return false; // no resolvable IP: do not count (cannot dedup safely)
        }

        $key = 'visit:'.$bucket.':'.self::getIpHash($ip.'|'.($userAgent ?? ''));

        // Cache::add is atomic and returns true only when the key was absent (first visit today).
        return Cache::add($key, 1, self::secondsUntilEndOfDay());
    }

    /**
     * A daily-salted, privacy-preserving identifier for one visitor.
     *
     * Returns null when there is no resolvable IP, in which case callers must not
     * count the request - the same rule isFirstDailyVisit() applies, since without
     * an IP there is no way to dedup safely.
     */
    public static function visitorHash(?string $ip, ?string $userAgent): ?string
    {
        if (! $ip) {
            return null;
        }

        return self::getIpHash($ip.'|'.($userAgent ?? ''));
    }

    /**
     * A daily-salted hash of the IP alone.
     *
     * Use this - not visitorHash() - for anything that RATE LIMITS or bills. visitorHash()
     * mixes in the User-Agent, which the client picks freely, so rotating it mints a fresh
     * bucket per request and any cap keyed on it stops existing. The User-Agent belongs in
     * identity/dedup keys (where a fresh bucket only costs an over-count) and never in a
     * key that guards spend.
     */
    public static function ipHash(?string $ip): ?string
    {
        return $ip ? self::getIpHash($ip) : null;
    }

    /**
     * The client IP, preferring Cloudflare's header over the socket address.
     *
     * Extracted from recordView() so every counter resolves the IP the same way -
     * recordSocialClick() used a bare $request->ip() and undercounted behind Cloudflare.
     */
    public static function clientIp(Request $request): ?string
    {
        return $request->header('CF-Connecting-IP') ?? $request->ip();
    }

    /**
     * Atomically increment a per-visitor counter that resets at midnight, returning the new count.
     *
     * Generalises hasExceededViewLimit() so rate caps elsewhere do not have to re-derive
     * the TTL - getting the operand order wrong there silently disables the cap
     * (see the note on secondsUntilEndOfDay()).
     */
    public static function incrementDailyCounter(string $key): int
    {
        Cache::add($key, 0, self::secondsUntilEndOfDay());

        return (int) Cache::increment($key);
    }

    /**
     * Whether this is one of Google's advertising crawlers.
     *
     * These match isBot() and are correctly excluded from analytics, but an ad slot must
     * still RENDER for them: Mediapartners-Google is how AdSense reads a page to pick
     * contextually relevant ads, so hiding the unit from it degrades ad relevance (and
     * revenue) with no visible symptom.
     */
    public static function isGoogleAdsCrawler(?string $userAgent): bool
    {
        if (! $userAgent) {
            return false;
        }

        $userAgentLower = strtolower($userAgent);

        foreach (['mediapartners-google', 'adsbot-google'] as $pattern) {
            if (str_contains($userAgentLower, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if IP has exceeded the SCHEDULE-level view limit for a role today.
     *
     * One aggregate, one cap. Deliberately no longer gates the event-level counter - see
     * hasExceededEventViewLimit().
     */
    protected static function hasExceededViewLimit(int $roleId, string $ipHash): bool
    {
        $maxViewsPerIpPerRole = 10; // Max views to count per IP per role per day

        return self::incrementDailyCounter("analytics_view:{$roleId}:{$ipHash}") > $maxViewsPerIpPerRole;
    }

    /**
     * Check if IP has exceeded the per-EVENT view limit today.
     *
     * The role cap above used to abort recordView() entirely, which meant the 11th event page a
     * visitor opened on a given schedule recorded nothing anywhere - and on a curator listing forty
     * venues' events, browsing eleven of them in a sitting is ordinary behaviour, not abuse. The
     * effect was silent and uneven: whichever events a visitor happened to open first that day got
     * the views, and because the key expires at midnight the winners changed daily. Events that
     * looked "missing from statistics" one day and present the next are exactly this shape.
     *
     * A separate, tighter budget per (visitor, event) keeps the anti-inflation intent - one person
     * cannot run up a single event's count - without letting one event's traffic silence another's.
     */
    protected static function hasExceededEventViewLimit(int $eventId, string $ipHash): bool
    {
        $maxViewsPerIpPerEvent = 3;

        return self::incrementDailyCounter("analytics_event_view:{$eventId}:{$ipHash}") > $maxViewsPerIpPerEvent;
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

        // Prefer Cloudflare's CF-Connecting-IP header for the real client IP
        $ip = $request->header('CF-Connecting-IP') ?? $request->ip();
        $ipHash = $ip ? self::getIpHash($ip) : null;

        // The role cap now suppresses only the schedule-level aggregates below; the event-level
        // block at the bottom carries its own, so a visitor reading their eleventh event page today
        // still counts for that event. Returning early here is what used to lose it.
        $scheduleCapped = $ipHash !== null && self::hasExceededViewLimit($role->id, $ipHash);

        $deviceType = self::detectDeviceType($userAgent);

        if (! $scheduleCapped) {
            // Increment schedule-level analytics
            AnalyticsDaily::incrementView($role->id, $deviceType);
        }

        // Track visitor location
        if ($ip && ! $scheduleCapped) {
            $countryCode = app(GeoIpService::class)->lookup($ip);
            if ($countryCode) {
                AnalyticsLocationsDaily::incrementView($role->id, $countryCode);
            }
        }

        // Track referrer source (UTM overrides referrer categorization)
        $referrer = $request->header('referer');
        $utmSource = $request->query('utm_source');
        $sourceOverride = match ($utmSource) {
            'boost' => 'boost',
            'newsletter' => 'newsletter',
            default => null,
        };
        if (! $sourceOverride && $request->query('promo')) {
            $sourceOverride = 'promo';
        }
        if (! $scheduleCapped) {
            AnalyticsReferrersDaily::incrementView($role->id, $referrer, $role->custom_domain, $sourceOverride);
        }

        // Track UTM parameters
        $utmParams = [
            'source' => $request->query('utm_source'),
            'medium' => $request->query('utm_medium'),
            'campaign' => $request->query('utm_campaign'),
            'content' => $request->query('utm_content'),
            'term' => $request->query('utm_term'),
        ];
        foreach ($utmParams as $paramType => $paramValue) {
            if (! $scheduleCapped && $paramValue !== null && $paramValue !== '') {
                $paramValue = mb_substr(trim($paramValue), 0, 255);
                AnalyticsUtmDaily::incrementView($role->id, $paramType, $paramValue);
            }
        }

        // Increment event-level analytics if event exists
        if ($event && ($ipHash === null || ! self::hasExceededEventViewLimit($event->id, $ipHash))) {
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
