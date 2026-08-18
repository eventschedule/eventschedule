<?php

/**
 * Monetization: programmatic ads and the native promotions network.
 *
 * Two layers on purpose:
 *
 *  - 'enabled' below is a HARD env gate and is deliberately NOT overridable from the
 *    settings table. Everything else in this feature is a runtime toggle a super-admin
 *    can flip at /admin/settings, and a single mistaken click would otherwise put ads on
 *    every free-tier public page on the instance. eventschedule.com ships with this off
 *    and leaves it off.
 *
 *  - Every other value here is only a DEFAULT. The settings table is the source of
 *    truth, resolved by AdsService::setting() as: Setting::get($key) ?? config('ads.'.$key).
 *    Publisher and slot IDs are not secrets (they are rendered into the page), so an
 *    operator should not need a redeploy to change them - but selfhosters who prefer
 *    .env can still configure everything here.
 *
 * Like the OneSignal integration, leaving this unconfigured means no third-party script
 * is loaded and no external request is made.
 */
return [

    /*
    |--------------------------------------------------------------------------
    | Master switch (env only)
    |--------------------------------------------------------------------------
    */

    'enabled' => (bool) env('ADS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Google AdSense
    |--------------------------------------------------------------------------
    |
    | Shown on free-tier schedules' public pages only. 'personalized' defaults to
    | false: serving personalized ads to EEA/UK visitors requires a Google-certified
    | consent management platform, which this app does not ship. Non-personalized is
    | the only responsible default; opting in is the operator's own legal decision.
    |
    */

    'adsense_enabled' => (bool) env('ADSENSE_ENABLED', false),
    'adsense_client_id' => env('ADSENSE_PUBLISHER_ID'),      // ca-pub-XXXXXXXXXXXXXXXX
    'adsense_slot_id' => env('ADSENSE_EVENT_SLOT_ID'),       // numeric ad unit id
    'personalized' => (bool) env('ADSENSE_PERSONALIZED', false),

    /*
    |--------------------------------------------------------------------------
    | Native promotions network
    |--------------------------------------------------------------------------
    |
    | Paid schedules buy placement in front of free-tier audiences. Rates are in whole
    | currency units and are snapshotted onto the campaign at purchase, so changing them
    | here never re-prices a campaign someone has already paid for.
    |
    */

    'native_enabled' => (bool) env('PROMOTIONS_ENGINE_ENABLED', false),
    'native_priority' => (bool) env('NATIVE_PROMO_PRIORITY_OVER_PROGRAMMATIC', true),

    'native_cpm' => (float) env('PROMOTIONS_NETWORK_CPM', 2.00),
    'native_cpc' => (float) env('PROMOTIONS_NETWORK_CPC', 0.25),

    'native_min_budget' => (float) env('PROMOTIONS_MIN_BUDGET', 5.00),
    'native_max_budget' => (float) env('PROMOTIONS_MAX_BUDGET', 1000.00),
    // Deliberately NOT falling back to the platform currency. A promotion purchase is a live
    // Stripe charge, and the platform currency is a super-admin dropdown documented as display
    // only - letting it pick the charge currency would silently re-denominate every purchase on
    // an install that had simply never set this variable. Changing what is billed stays here,
    // in .env, where it takes a deploy.
    'native_currency' => env('PROMOTIONS_CURRENCY', 'USD'),

    // Concurrent network campaigns per schedule. Deliberately separate from
    // META_MAX_CONCURRENT_BOOSTS so one channel cannot starve the other.
    'native_max_concurrent' => (int) env('PROMOTIONS_MAX_CONCURRENT', 2),

    // Impressions of one campaign to one visitor per day. Keyed on a hash that includes the
    // User-Agent, so this is a presentation cap, not a spend cap - see below.
    'native_frequency_cap' => (int) env('PROMOTIONS_FREQUENCY_CAP', 3),

    // Billed impressions of one campaign per IP per day. This IS the spend cap: the frequency
    // cap above can be reset by changing User-Agent, and guest pages carry no route throttle.
    // Set well above the frequency cap so a shared address does not cost an advertiser real
    // delivery. 0 disables the cap.
    'native_ip_impression_cap' => (int) env('PROMOTIONS_IP_IMPRESSION_CAP', 100),

    // Approved campaigns a schedule needs before its later campaigns skip manual review.
    'native_auto_approve_after' => (int) env('PROMOTIONS_AUTO_APPROVE_AFTER', 3),

    // A CPC campaign below this click-through rate after native_ctr_floor_min_impressions
    // is paused: otherwise weak creative burns host inventory indefinitely for free.
    'native_ctr_floor' => (float) env('PROMOTIONS_MIN_CTR', 0.0002),
    'native_ctr_floor_min_impressions' => (int) env('PROMOTIONS_MIN_CTR_IMPRESSIONS', 5000),

    // Servable-campaign snapshot TTL, in seconds. Also the worst-case overdelivery window:
    // budget exhaustion is enforced atomically, but the candidate pool is this stale.
    'candidate_cache_ttl' => (int) env('PROMOTIONS_CACHE_TTL', 300),

    // Days of promotion rollup data to retain. These tables are keyed by
    // campaign x host x day, so they grow faster than the other analytics rollups.
    'stats_retention_days' => (int) env('PROMOTIONS_STATS_RETENTION_DAYS', 400),

];
