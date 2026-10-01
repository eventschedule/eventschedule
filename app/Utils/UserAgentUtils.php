<?php

namespace App\Utils;

/**
 * Coarse browser and operating-system families from a user-agent string, for /admin/realtime.
 *
 * Families only ("Safari", "iOS"), never versions: the realtime table must not hold anything
 * closer to a fingerprint than the visitor's own row already needs. Device type lives in
 * PageView::detectDeviceType(). No dependency: a dozen ordered patterns cover the browsers that
 * actually visit, and everything else is "Other".
 */
class UserAgentUtils
{
    /**
     * Ordered: in-app browsers and Chromium forks announce "Chrome" and "Safari" too, so they have
     * to be matched before the engines they are built on.
     */
    private const BROWSERS = [
        'Facebook' => '/FBAN|FBAV|FB_IAB/',
        'Instagram' => '/Instagram/',
        'Edge' => '/Edg(e|A|iOS)?\//',
        'Opera' => '/OPR\/|Opera/',
        'Samsung Internet' => '/SamsungBrowser/',
        'Firefox' => '/Firefox\/|FxiOS/',
        'Chrome' => '/Chrome\/|CriOS/',
        'Safari' => '/Version\/[\d.]+.*Safari/',
    ];

    private const SYSTEMS = [
        'iOS' => '/iPhone|iPad|iPod/',
        'Android' => '/Android/',
        'ChromeOS' => '/CrOS/',
        'Windows' => '/Windows/',
        'macOS' => '/Macintosh|Mac OS X/',
        'Linux' => '/Linux/',
    ];

    public static function browser(?string $userAgent): string
    {
        return self::match($userAgent, self::BROWSERS);
    }

    public static function os(?string $userAgent): string
    {
        return self::match($userAgent, self::SYSTEMS);
    }

    private static function match(?string $userAgent, array $patterns): string
    {
        if (! $userAgent) {
            return 'Other';
        }

        foreach ($patterns as $name => $pattern) {
            if (preg_match($pattern, $userAgent) === 1) {
                return $name;
            }
        }

        return 'Other';
    }
}
