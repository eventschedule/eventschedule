<?php

namespace App\Models;

use App\Utils\CounterUtils;
use Illuminate\Database\Eloquent\Model;

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
        'docs_page_views',
        'docs_visitors',
        'pricing_views',
        'pricing_visitors',
        'signup_code_requests',
        'signup_code_verified',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * The countable columns. Whitelisted so the value interpolated into the raw
     * statement below can never be user-influenced.
     *
     * docs_page_views / docs_visitors are a SUBSET of page_views / visitors, not a
     * sibling bucket: buyer-intent traffic is (visitors - docs_visitors).
     *
     * pricing_views / pricing_visitors are a SUBSET in the same way, and overlap the docs
     * buckets rather than excluding them - never add the three together.
     */
    public const COLUMNS = [
        'visitors',
        'page_views',
        'signup_views',
        'docs_page_views',
        'docs_visitors',
        'pricing_views',
        'pricing_visitors',
        'signup_code_requests',
        'signup_code_verified',
    ];

    /**
     * The first date each counter was actually being written.
     *
     * Every column after the first three was added by a later migration with `default(0)`,
     * which MySQL also backfills onto every existing row. So a zero in a month before the
     * column existed is a schema default, not a measurement - and nothing in the data
     * distinguishes the two. GrowthExportService::traffic() reads this to emit null instead,
     * because "we were not counting" and "nobody visited" are different answers and only one
     * of them is a growth problem.
     *
     * These are the migration filenames, which is the best declarative answer available: a
     * deploy that lagged its migration by a few days will still report those days as real
     * zeros. That is the same failure the export had everywhere before, now confined to a
     * handful of days rather than two years.
     */
    public const COLUMN_TRACKED_FROM = [
        // 2026_07_07_000000_create_marketing_daily_stats_table
        'visitors' => '2026-07-07',
        'page_views' => '2026-07-07',
        'signup_views' => '2026-07-07',
        // 2026_08_03_000000_add_funnel_columns_to_marketing_daily_stats
        'docs_page_views' => '2026-08-03',
        'docs_visitors' => '2026-08-03',
        'signup_code_requests' => '2026-08-03',
        'signup_code_verified' => '2026-08-03',
        // 2026_08_28_000001_add_upgrade_funnel_tracking
        'pricing_views' => '2026-08-28',
        'pricing_visitors' => '2026-08-28',
    ];

    /**
     * Whether a column was being written for any part of the given `YYYY-MM` month.
     *
     * Month-granular because traffic() reports by month: a column that started mid-month is
     * reported for that whole month, undercounting its first few days rather than hiding it.
     */
    public static function trackedInMonth(string $column, string $month): bool
    {
        $from = self::COLUMN_TRACKED_FROM[$column] ?? null;

        return $from !== null && substr($from, 0, 7) <= $month;
    }

    /**
     * Atomically increment one of the daily counters for today (UTC).
     *
     * Mirrors AnalyticsDaily::incrementView(): a single INSERT ... ON DUPLICATE KEY
     * UPDATE, one round-trip. It is race-safe against DUPLICATE KEYS (two concurrent
     * first-of-day requests cannot both insert and throw a 1062) but not against
     * DEADLOCKS (1213), so it goes through CounterUtils, which retries those and then
     * reports - a DB hiccup can never break a public page render.
     */
    public static function record(string $column): void
    {
        if (! in_array($column, self::COLUMNS, true)) {
            return;
        }

        CounterUtils::statement(
            "INSERT INTO marketing_daily_stats (date, {$column})
             VALUES (?, 1)
             ON DUPLICATE KEY UPDATE {$column} = {$column} + 1",
            [now()->toDateString()]
        );
    }
}
