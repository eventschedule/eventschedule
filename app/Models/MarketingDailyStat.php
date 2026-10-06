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
        'signup_code_invalid',
        'guest_submit_views',
        'guest_submit_code_requests',
        'guest_submit_submissions',
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
        // Visitors who had at least one sign-up code rejected that day. NOT a stage below
        // signup_code_verified: somebody can mistype once and then succeed, so it overlaps
        // both of the others. It separates "came back and got it wrong" from "never came back".
        'signup_code_invalid',
        // The public "Submit your event" page (event.guest_submit), written by EventController and
        // by RegisteredUserController::sendVerificationCode() on the guest route. Its own funnel,
        // beside the sign-up one and never part of it: a visitor who saw the form, one who asked
        // for the emailed code (hosted, new accounts only - someone signing in or signed in skips
        // it, so it is not a stage every submitter passes), and one who got an event through.
        'guest_submit_views',
        'guest_submit_code_requests',
        'guest_submit_submissions',
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
        // 2026_09_25_000001_add_signup_code_invalid_to_marketing_daily_stats
        'signup_code_invalid' => '2026-09-25',
        // 2026_10_06_000001_add_guest_submit_counters_to_marketing_daily_stats
        'guest_submit_views' => '2026-10-06',
        'guest_submit_code_requests' => '2026-10-06',
        'guest_submit_submissions' => '2026-10-06',
    ];

    /**
     * The day each TrackMarketingVisit counter changed what it counts.
     *
     * Before the edge cache (223d1c2a7, released in v1.0.130) the middleware counted every
     * marketing document request that passed the bot filters. After it, an edge-cached page never
     * reaches the origin, so the layout's JS beacon does the counting instead. Visitors went from
     * ~600 a day to ~130 across that line, and the daily visitor-to-signup rate stepped from ~0.3%
     * to ~5% on 2026-09-07, which is the date used here (the tag is Sep 4, the deploy is not
     * recorded anywhere).
     *
     * The CAUSE of that drop is not established. Bots that never run JS falling out would do it;
     * so would a beacon that under-fires, which docs/CACHING.md names as the failure to watch for.
     * signup_views, which is still counted server-side, went from 2.6% of visitors to ~23% across
     * the same line - plausible only if ~85% of the old visitors were bots. Until someone checks
     * the beacon against an independent count, months on either side of this date are not
     * comparable, and GrowthExportService::traffic() says so per month.
     *
     * The signup_* counters are written by RegisteredUserController, not the beacon, and are
     * deliberately absent.
     */
    public const COLUMN_REBASED_AT = [
        'visitors' => '2026-09-07',
        'page_views' => '2026-09-07',
        'docs_page_views' => '2026-09-07',
        'docs_visitors' => '2026-09-07',
        'pricing_views' => '2026-09-07',
        'pricing_visitors' => '2026-09-07',
    ];

    /**
     * How a column was being counted during the given `YYYY-MM` month: 'server' before its
     * rebase, 'mixed' in the month the rebase falls in, 'beacon' after. Null for a column that
     * was never rebased.
     */
    public static function basisInMonth(string $column, string $month): ?string
    {
        $rebasedAt = self::COLUMN_REBASED_AT[$column] ?? null;

        if ($rebasedAt === null) {
            return null;
        }

        $rebaseMonth = substr($rebasedAt, 0, 7);

        if ($month < $rebaseMonth) {
            return 'server';
        }

        // A rebase on the 1st would make the whole month beacon-counted.
        if ($month === $rebaseMonth && substr($rebasedAt, 8, 2) !== '01') {
            return 'mixed';
        }

        return 'beacon';
    }

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
