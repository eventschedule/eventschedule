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
    */

    'wall_cache_seconds' => (int) env('MARKETING_WALL_CACHE_SECONDS', 600),

];
