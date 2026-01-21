<?php

namespace App\Models;

use Illuminate\Http\Request;

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
        if (! $userAgent) {
            return false;
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
     * Record a page view (returns false if bot detected)
     */
    public static function recordView(Role $role, ?Event $event, Request $request): bool
    {
        $userAgent = $request->userAgent();

        // Skip recording for bots/crawlers
        if (self::isBot($userAgent)) {
            return false;
        }

        $deviceType = self::detectDeviceType($userAgent);

        // Increment schedule-level analytics
        AnalyticsDaily::incrementView($role->id, $deviceType);

        // Track referrer source
        $referrer = $request->header('referer');
        AnalyticsReferrersDaily::incrementView($role->id, $referrer);

        // Increment event-level analytics if event exists
        if ($event) {
            AnalyticsEventsDaily::incrementView($event->id, $deviceType);

            // Track appearance views for associated talents/venues
            // But only if the current schedule is actually a curator of this event
            $roles = $event->relationLoaded('roles') ? $event->roles : $event->roles()->get();

            // Check if the current schedule is linked to this event as a curator
            $scheduleIsCurator = $roles->contains(function ($eventRole) use ($role) {
                return $eventRole->id === $role->id && $eventRole->isCurator();
            });

            if ($scheduleIsCurator) {
                foreach ($roles as $eventRole) {
                    // Only track talents and venues, not the schedule itself
                    if (($eventRole->isTalent() || $eventRole->isVenue()) && $eventRole->id !== $role->id) {
                        AnalyticsAppearancesDaily::incrementView($eventRole->id, $role->id, $deviceType);
                    }
                }
            }
        }

        return true;
    }
}
