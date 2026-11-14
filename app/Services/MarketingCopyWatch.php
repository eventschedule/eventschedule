<?php

namespace App\Services;

use App\Models\FederatedInstance;
use App\Models\MarketingCopyHost;
use App\Utils\RealtimeTracker;
use App\Utils\UrlUtils;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Finds sites that may be serving a copy of Event Schedule's marketing pages, so the nexus can ask
 * them to take it down, or ask Google to drop it (GitHub #136). It finds and never acts: what to
 * do about a match is the operator's, on /admin/copies.
 *
 * Two places a copy shows itself to us, both free:
 *  - A copy still links out to eventschedule.com in places (the docs, a plan link, the footer),
 *    so some of its visitors arrive here with it as their referrer. The Realtime beacon hands
 *    every referrer that is no search engine, social network, AI, mail service or host of ours to
 *    sawReferrer(), which keeps the host and a count. Never anything about the visitor.
 *  - Installs that registered for federation, which name their own address.
 *
 * Once a day (app:check-marketing-copies, both cron rails, nexus only) a few hosts are looked at:
 * their / and /pricing, fetched through the SSRF guard, are searched for MARKERS, things only the
 * marketing pages carry. A site that merely links to us, writes about us or carries the licence's
 * attribution matches none of them, so a copy is two kinds or more.
 *
 * A copy that links nowhere near us and has the canonical edited out stays invisible here; only a
 * web search for our own sentences could find it, which was left out on purpose (2026-11-14).
 */
class MarketingCopyWatch
{
    /** A referring host is looked at once it has sent this many visits: one could be anybody's typo. */
    public const MIN_VISITS = 2;

    /** Hosts looked at per run. The run is daily, so this is also the most a day can fetch. */
    public const PER_RUN = 10;

    public const RECHECK_DAYS = 7;

    /** A host that never answered is given up on after this many tries. */
    public const MAX_FAILURES = 5;

    /** A host nobody came from for this long, and that was never a copy, is forgotten. */
    public const FORGET_DAYS = 90;

    /** Pages looked at on each host, in order. */
    public const PATHS = ['/', '/pricing'];

    /**
     * Statuses the operator decided. A dismissed site is never looked at again until somebody
     * asks to watch it; a contacted one is, and leaves the list once the copy is gone.
     */
    public const DECIDED = ['dismissed', 'contacted'];

    /**
     * What only the marketing pages carry, by kind. Each is a pattern over the raw HTML. The
     * phrases are held to the views they come from by MarketingCopyWatchTest, so editing the copy
     * cannot quietly turn one off.
     */
    public const MARKERS = [
        // The one SoftwareApplication node every marketing page's structured data names.
        'structured_data' => '~"@id"\s*:\s*"[^"]*/#software"~',
        // The marketing bundle's built files (resources/css/marketing.css, resources/js/marketing.js).
        'assets' => '~/build/assets/marketing(?:-app)?-[A-Za-z0-9_-]{6,}\.(?:css|js)~',
        // The marketing pages' own type classes.
        'layout' => '~class="[^"]*\bhp-h[12]\b~',
    ];

    /** Sentences from the homepage and the pricing page. Two of them make one marker. */
    public const PHRASES = [
        'See Event Schedule in action',
        'Who uses Event Schedule',
        'More event management features',
        'Compare plans, row by row',
        'Or run it yourself.',
    ];

    /**
     * Stands in for the fetch in tests: the SSRF guard resolves the host in DNS, which a test
     * must not do. Takes a URL and returns the body, or null when the page did not answer.
     *
     * @var (\Closure(string): ?string)|null
     */
    public static ?\Closure $fetchUsing = null;

    public static function enabled(): bool
    {
        return (bool) config('app.is_nexus');
    }

    /**
     * Record that a visit arrived from $host. Called from the Realtime beacon on a page view whose
     * source is an unknown site; never throws, because it runs inside a visitor's beacon.
     */
    public static function sawReferrer(?string $host): void
    {
        if (! self::enabled()) {
            return;
        }

        $host = RealtimeTracker::normalizeHost($host);

        if ($host === null || ! str_contains($host, '.') || RealtimeTracker::hostChannel($host) !== null
            || RealtimeTracker::isPassThroughHost($host)) {
            return;
        }

        try {
            $now = now()->toDateTimeString();
            DB::statement(
                'INSERT INTO marketing_copy_hosts (host, source, visits, first_seen_at, last_seen_at, status, created_at, updated_at)
                 VALUES (?, ?, 1, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE visits = visits + 1, last_seen_at = VALUES(last_seen_at)',
                [$host, 'referrer', $now, $now, 'unchecked', $now, $now]
            );
        } catch (QueryException $e) {
            if ($e->getCode() !== '42S02') {
                report($e);
            }
        } catch (Throwable $e) {
            report($e);
        }
    }

    /** Add the hosts of federated installs. They are looked at however few visits they sent. */
    public static function harvestFederation(): int
    {
        $added = 0;
        $now = now();

        FederatedInstance::query()->select(['id', 'site_url', 'reported_site_url'])->orderBy('id')
            ->each(function (FederatedInstance $instance) use (&$added, $now) {
                foreach ([$instance->site_url, $instance->reported_site_url] as $url) {
                    $host = RealtimeTracker::normalizeHost(parse_url((string) $url, PHP_URL_HOST) ?: null);

                    if ($host === null || RealtimeTracker::isPassThroughHost($host)) {
                        continue;
                    }

                    $row = MarketingCopyHost::firstOrCreate(['host' => $host], [
                        'source' => 'federation',
                        'first_seen_at' => $now,
                        'last_seen_at' => $now,
                    ]);

                    if ($row->wasRecentlyCreated) {
                        $added++;
                    } elseif ($row->source !== 'federation') {
                        // Federated as well as referring: no longer waits for a second visit.
                        $row->forceFill(['source' => 'federation'])->save();
                    }
                }
            });

        return $added;
    }

    /** The hosts the next run looks at: never looked at first, then the longest unlooked. */
    public static function due(int $limit = self::PER_RUN)
    {
        return MarketingCopyHost::query()
            ->where('status', '!=', 'dismissed')
            ->where('check_failures', '<', self::MAX_FAILURES)
            ->where(fn ($q) => $q->where('source', 'federation')->orWhere('visits', '>=', self::MIN_VISITS))
            ->where(fn ($q) => $q->whereNull('checked_at')->orWhere('checked_at', '<=', now()->subDays(self::RECHECK_DAYS)))
            ->orderByRaw('checked_at IS NOT NULL')
            ->orderBy('checked_at')
            ->orderByDesc('visits')
            ->limit($limit)
            ->get();
    }

    /** One daily run: harvest, look at what is due, forget what went quiet. */
    public static function run(int $limit = self::PER_RUN): array
    {
        $added = self::harvestFederation();
        $checked = 0;
        $copies = 0;

        foreach (self::due($limit) as $row) {
            $checked++;
            if (self::check($row) === 'copy') {
                $copies++;
            }
        }

        $forgotten = MarketingCopyHost::query()
            ->whereIn('status', ['unchecked', 'clear', 'unreachable'])
            ->where('source', 'referrer')
            ->where('last_seen_at', '<', now()->subDays(self::FORGET_DAYS))
            ->delete();

        return compact('added', 'checked', 'copies', 'forgotten');
    }

    /** Look at one host's pages and record what was found. Returns the new status. */
    public static function check(MarketingCopyHost $row): string
    {
        $found = [];
        $title = null;
        $checkedUrl = null;
        $answered = false;

        foreach (self::PATHS as $path) {
            $url = 'https://'.$row->host.$path;
            $html = self::$fetchUsing ? (self::$fetchUsing)($url) : UrlUtils::safeFetch($url, 15);

            if ($html === null) {
                continue;
            }

            $answered = true;
            $markers = self::markers($html);

            if (count($markers) > count($found)) {
                $found = $markers;
                $checkedUrl = $url;
                $title = self::title($html);
            } elseif ($title === null) {
                $title = self::title($html);
                $checkedUrl ??= $url;
            }

            if (count($found) >= 2) {
                break;
            }
        }

        // Re-read: the operator may have decided while the fetches ran.
        $row->refresh();
        if ($row->status === 'dismissed') {
            return $row->status;
        }

        if (! $answered) {
            $row->forceFill([
                'status' => in_array($row->status, ['copy', 'contacted'], true) ? $row->status : 'unreachable',
                'checked_at' => now(),
                'check_failures' => min(255, $row->check_failures + 1),
            ])->save();

            return $row->status;
        }

        $isCopy = count($found) >= 2;
        $row->forceFill([
            // Contacted stays contacted while the copy is up; taken down, the site is clear.
            'status' => $isCopy ? ($row->status === 'contacted' ? 'contacted' : 'copy') : 'clear',
            'checked_at' => now(),
            'check_failures' => 0,
            'markers' => $found ?: null,
            'page_title' => $title,
            'checked_url' => $checkedUrl,
            'copy_found_at' => $isCopy ? ($row->copy_found_at ?? now()) : $row->copy_found_at,
        ])->save();

        return $row->status;
    }

    /**
     * The kinds of marker the page carries.
     *
     * @return list<string>
     */
    public static function markers(string $html): array
    {
        $found = [];

        foreach (self::MARKERS as $kind => $pattern) {
            if (preg_match($pattern, $html) === 1) {
                $found[] = $kind;
            }
        }

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $phrases = count(array_filter(self::PHRASES, fn ($phrase) => str_contains($text, $phrase)));
        if ($phrases >= 2) {
            $found[] = 'phrases';
        }

        return $found;
    }

    private static function title(string $html): ?string
    {
        if (preg_match('~<title[^>]*>(.*?)</title>~is', $html, $m) !== 1) {
            return null;
        }

        $title = trim(preg_replace('/\s+/u', ' ', html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? '');

        return $title === '' ? null : mb_substr($title, 0, 200);
    }

    /** New copies waiting for the operator: the admin alert row. */
    public static function count(): int
    {
        if (! self::enabled()) {
            return 0;
        }

        return MarketingCopyHost::where('status', 'copy')->count();
    }
}
