<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Homepage poster wall cache
    |--------------------------------------------------------------------------
    |
    | How long MarketingController::discoverWallEvents() caches the events behind the homepage
    | poster wall, the mobile strip and the discover rail. That query is five correlated
    | subqueries plus a regex pass on the most-hit page on the site, and it is invalidated
    | eagerly (Event's saved/deleted hooks call MarketingController::forgetWallCache()), so the
    | TTL is only a backstop for the writes that go around Eloquent.
    |
    | Set to 0 to disable the cache entirely, which is what the test suite does: the `array`
    | store survives RefreshDatabase within a process, so a wall cached by one test would
    | otherwise render against the next test's wiped database.
    |
    | is_numeric() rather than the `?:` form used elsewhere in config/, because 0 is a MEANINGFUL
    | value here and `?:` would map it straight back to 600, breaking the pin phpunit.xml relies
    | on. A second argument to env() is wrong for the opposite reason: it never fires for a
    | present-but-empty var, and (int) '' is 0 - so a blank entry on a deployed app spec would
    | silently disable the cache and put five correlated subqueries on every origin hit of `/`.
    | is_numeric() is the only form that separates "unset or blank" from "deliberately zero":
    | null and '' fail it, '0' passes.
    |
    */

    'wall_cache_seconds' => is_numeric(env('MARKETING_WALL_CACHE_SECONDS'))
        ? (int) env('MARKETING_WALL_CACHE_SECONDS')
        : 600,

];
