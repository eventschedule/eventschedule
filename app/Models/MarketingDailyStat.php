<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Daily aggregate counters for the marketing (WP) site, powering the top of the
 * onboarding funnel on /admin/users. One row per UTC day. Written in real time by
 * TrackMarketingVisit middleware and RegisteredUserController::create().
 */
class MarketingDailyStat extends Model
{
    public $timestamps = false;

    protected $table = 'marketing_daily_stats';

    protected $fillable = [
        'date',
        'visitors',
        'page_views',
        'signup_views',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * The countable columns. Whitelisted so the value interpolated into the raw
     * statement below can never be user-influenced.
     */
    public const COLUMNS = ['visitors', 'page_views', 'signup_views'];

    /**
     * Atomically increment one of the daily counters for today (UTC).
     *
     * Mirrors AnalyticsDaily::incrementView(): a single INSERT ... ON DUPLICATE KEY
     * UPDATE is race-safe (two concurrent first-of-day requests cannot both insert
     * and throw a duplicate-key QueryException) and is a single round-trip. Failures
     * are swallowed and reported so a DB hiccup can never break a public page render.
     */
    public static function record(string $column): void
    {
        if (! in_array($column, self::COLUMNS, true)) {
            return;
        }

        try {
            DB::statement(
                "INSERT INTO marketing_daily_stats (date, {$column})
                 VALUES (?, 1)
                 ON DUPLICATE KEY UPDATE {$column} = {$column} + 1",
                [now()->toDateString()]
            );
        } catch (\Throwable $e) {
            report($e);
        }
    }
}
