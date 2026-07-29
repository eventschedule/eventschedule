<?php

/**
 * Stay22 accommodation map: a per-schedule, visitor-facing widget that shows hotels and
 * rentals near an event's venue, and earns an affiliate commission on bookings.
 *
 * Two layers, for the same reasons as config/ads.php:
 *
 *  - 'enabled' below is a HARD env gate and is deliberately NOT overridable from the
 *    settings table. SecurityHeaders reads it on EVERY request to decide whether to widen
 *    frame-src, so it must never touch the database. It is also the operator saying "my
 *    customers may opt into this affiliate programme" - without it the per-schedule toggle
 *    is not even rendered.
 *
 *  - 'aid' is only a DEFAULT. Setting::get('stay22_aid') is the source of truth, so an
 *    operator can change the fallback ID at /admin/settings without a redeploy. It is not
 *    a secret: it is interpolated into a public iframe URL.
 *
 * This is NOT part of the monetization feature in config/ads.php. It is independent of
 * ADS_ENABLED, it applies to paid schedules as well as free ones, and each schedule may
 * supply its own affiliate ID and keep the commission itself.
 *
 * Leaving this unconfigured means no third-party frame host is allow-listed and no
 * external request is ever made.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Master switch (env only)
    |--------------------------------------------------------------------------
    */

    'enabled' => (bool) env('STAY22_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Operator fallback affiliate ID (settings table wins)
    |--------------------------------------------------------------------------
    |
    | Used for schedules that enabled the map but did not supply their own ID. The
    | admin portal discloses this to schedule owners, and it is deliberately never
    | used on a customer's own custom domain - see Stay22Service::embedFor().
    |
    */

    'aid' => env('STAY22_AID'),

    /*
    |--------------------------------------------------------------------------
    | Upper bound on the derived stay length, in nights
    |--------------------------------------------------------------------------
    |
    | events.duration is an unbounded nullable float and is populated by importers, so
    | a bad row must not produce a nonsense date range. 30 is well past any real
    | festival, and clamping low is safer than clamping high because Stay22 rejects
    | absurd ranges outright.
    |
    */

    'max_nights' => (int) env('STAY22_MAX_NIGHTS', 30),

];
