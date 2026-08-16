<?php

namespace App\Utils;

/**
 * Single-segment marketing URLs from the old WordPress site that still rank but no longer resolve.
 *
 * They were dead-ending: nothing matched them, so the catch-all at the bottom of routes/web.php
 * handed them to HomeController::landing(), which redirected to /dashboard -> /login - a URL
 * robots.txt disallows. All three were still holding positions 5-7 in Google while sending every
 * visitor to a login screen.
 *
 * Consumed only by HomeController::landing(). A legacy path that collides with a real route never
 * reaches the catch-all and so cannot be listed here - see the '/tickets' note below.
 *
 * Targets are marketing paths and were each verified to return 200.
 */
class LegacyRedirects
{
    private const MAP = [
        // "roles" was the old word for schedules; /features is the closest live equivalent.
        'events-roles' => '/features',
        'who-we-help' => '/use-cases',
        'help-center' => '/docs',
        // NOTE: '/tickets' is a legacy URL too, but it is intentionally absent - it collides with
        // the authenticated "my tickets" route, which is registered first and wins. See the note
        // above that route group in routes/web.php.
    ];

    /** The marketing path a legacy slug should 301 to, or null if it is not a legacy URL. */
    public static function targetFor(?string $slug): ?string
    {
        if ($slug === null) {
            return null;
        }

        return self::MAP[trim($slug, '/')] ?? null;
    }
}
