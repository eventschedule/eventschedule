<?php

namespace App\Models;

use App\Http\Middleware\TrackMarketingVisit;
use App\Utils\CounterUtils;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;

/**
 * Per-page, per-device daily counters for the marketing site (the WP) and the blog: how many
 * visitors opened a page, how many of them reached its end, and how many pressed a sign-up link
 * on it. Written by MarketingPageStatController from the browser beacon in
 * partials/marketing-page-stats, because anonymous marketing HTML is edge-cached (docs/CACHING.md)
 * and the origin never sees most page views.
 *
 * Every column counts VISITORS, one per page per kind per UTC day (PageView::isFirstDailyVisit()
 * on a bucket of its own), so reached_end / visitors and signup_clicks / visitors are rates per
 * visitor. A visitor who reads three pages is counted on each, so a sum over pages is not the
 * site's visitor count (that is marketing_daily_stats.visitors). Nothing about the person is kept.
 */
class MarketingPageDaily extends Model
{
    public $timestamps = false;

    protected $table = 'marketing_page_daily';

    protected $fillable = [
        'date',
        'page',
        'device',
        'visitors',
        'reached_end',
        'signup_clicks',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /** The beacon's kinds, and the column each one moves. Whitelisted: the column is interpolated. */
    public const KINDS = [
        'view' => 'visitors',
        'reached_end' => 'reached_end',
        'signup_click' => 'signup_clicks',
    ];

    public const DEVICE_PHONE = 'phone';

    public const DEVICE_DESKTOP = 'desktop';

    /** A viewport narrower than this (CSS pixels) is a phone: Tailwind's `sm` breakpoint, where the WP's layouts change. */
    public const PHONE_BELOW_WIDTH = 640;

    /**
     * The blog's public pages. `blog.*` also names the admin's blog screens (blog.edit,
     * blog.store, ...), so the blog is an allow-list and never a prefix. Every post is the one
     * route blog.show, which is what makes "the blog" a single row rather than one per post.
     */
    public const BLOG_PAGES = ['blog.index', 'blog.category', 'blog.show'];

    /** The first day the table was written; nothing before it was measured. */
    public const TRACKED_FROM = '2026-11-12';

    /**
     * Is this the name of a page the beacon may report? A marketing page the visit beacon
     * would count (TrackMarketingVisit::isCountableRouteName(), which checks the router), or one
     * of the blog's public pages where this install registers them.
     */
    public static function isCountablePage(mixed $page): bool
    {
        if (! is_string($page) || $page === '' || strlen($page) > 100) {
            return false;
        }

        if (in_array($page, self::BLOG_PAGES, true)) {
            return RouteFacade::has($page);
        }

        return TrackMarketingVisit::isCountableRouteName($page);
    }

    public static function deviceFor(int $viewportWidth): string
    {
        return $viewportWidth < self::PHONE_BELOW_WIDTH ? self::DEVICE_PHONE : self::DEVICE_DESKTOP;
    }

    /**
     * Atomically add one to a page's counter for today (UTC), the same single-statement upsert as
     * MarketingDailyStat::record(), through CounterUtils so a deadlock never fails the beacon.
     */
    public static function record(string $page, string $device, string $kind): void
    {
        $column = self::KINDS[$kind] ?? null;

        if ($column === null || ! in_array($device, [self::DEVICE_PHONE, self::DEVICE_DESKTOP], true)) {
            return;
        }

        CounterUtils::statement(
            "INSERT INTO marketing_page_daily (date, page, device, {$column})
             VALUES (?, ?, ?, 1)
             ON DUPLICATE KEY UPDATE {$column} = {$column} + 1",
            [now()->toDateString(), $page, $device]
        );
    }

    /**
     * The family a page belongs to, for the growth export's monthly rollup. Read from the route's
     * path, so a new for-* or features page lands in its family without a list to keep up.
     *
     * home, pricing, features, for (the for-* pages), compare (alternatives, replacements,
     * compare, switch-from), examples, docs (the user guide), docs_selfhost (the selfhost, SaaS
     * operator and developer docs), selfhost (/selfhost, /open-source, /saas), blog, other.
     */
    public static function groupOf(string $page): string
    {
        if (Str::startsWith($page, 'blog.')) {
            return 'blog';
        }

        if ($page === 'marketing.index') {
            return 'home';
        }

        $uri = trim((string) RouteFacade::getRoutes()->getByName($page)?->uri(), '/');
        $first = Str::before($uri, '/');

        return match (true) {
            $uri === '' => 'other',
            $uri === 'pricing' => 'pricing',
            $first === 'features' => 'features',
            Str::startsWith($first, 'for-') => 'for',
            $first === 'compare' || $first === 'replace'
                || Str::contains($first, ['alternative', 'replacement', 'switch-from']) => 'compare',
            $first === 'examples' => 'examples',
            Str::startsWith($uri, ['docs/selfhost', 'docs/saas', 'docs/developer']) => 'docs_selfhost',
            $first === 'docs' => 'docs',
            in_array($first, ['selfhost', 'open-source', 'saas', 'self-hosting-terms-of-service'], true) => 'selfhost',
            default => 'other',
        };
    }
}
