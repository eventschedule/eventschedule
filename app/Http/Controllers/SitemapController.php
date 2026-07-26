<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Event;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * The sitemap is a sitemap index (/sitemap.xml) pointing at paginated children.
 *
 * Everything here is streamed, chunked and flushed on purpose. The previous single-file
 * implementation loaded every schedule and every public event into memory as hydrated models and
 * rendered the whole document into one cached string, which grew until it exhausted the PHP memory
 * limit and turned /sitemap.xml into a zero-byte 500 in production. Memory here is flat regardless
 * of row count, and nothing is ever held in full.
 *
 * Nothing is written to disk. The hosted deployment has an ephemeral, per-container filesystem, so
 * pre-generated files would be wiped on every deploy and would only exist on whichever container
 * generated them.
 *
 * There is one representation per sitemap and it is uncompressed; the CDN negotiates the encoding.
 * The legacy /sitemap*.xml.gz paths redirect here (routes/web.php), and /sitemap.xml is the URL
 * every robots.txt advertises - which is what authorises the tenant-subdomain and custom-domain
 * URLs the children are made of to be cross-submitted from this host.
 */
class SitemapController extends Controller
{
    /** Rows hydrated at once while streaming. Bounds peak memory. */
    private const HYDRATE_CHUNK = 250;

    /** Rows read at once while computing page ranges. Only ids and timestamps are read. */
    private const SCAN_CHUNK = 1000;

    /** Bytes buffered before forcing them out to the client. Bounds peak memory (see write()). */
    private const FLUSH_BYTES = 65536;

    private const CACHE_SECONDS = 3600;

    /** How long a stale section list may still be served while it refreshes in the background. */
    private const CACHE_STALE_SECONDS = 7200;

    /**
     * 2000-01-01. Anything older is a build artifact rather than a real edit: reproducible-build
     * images rewrite every file mtime to a fixed epoch. See staticLastmodFallback().
     */
    private const MIN_PLAUSIBLE_MTIME = 946684800;

    /** The longest a DNS label may be. See isListable(). */
    private const MAX_LABEL_LENGTH = 63;

    /** Per-request memo of the section list. */
    private ?array $sections = null;

    private ?string $staticLastmod = null;

    /**
     * The host this sitemap is being served for, and whether its subdomains count as in scope.
     * Set by whichever entry point is running; see isListable().
     */
    private ?string $scopeHost = null;

    private bool $scopeIncludesSubdomains = false;

    /**
     * GET /sitemap.xml - the sitemap index.
     *
     * Query strings are ignored entirely. The old implementation keyed its cache on
     * md5(fullUrl()), so any crawler-appended parameter minted a new entry and a fresh full
     * generation; the legacy ?events= / ?roles= splits are gone with it.
     */
    public function index(): StreamedResponse
    {
        $this->scopeToBaseDomain();

        $sections = $this->sections();

        return $this->streamed(function (callable $write) use ($sections) {
            $write('<?xml version="1.0" encoding="UTF-8"?>'."\n");
            $write('<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");

            foreach ($sections as $section) {
                $write('    <sitemap>'."\n");
                $write('        <loc>'.$this->escape(url('/sitemap-'.$section['name'].'.xml')).'</loc>'."\n");

                if (! empty($section['lastmod'])) {
                    $write('        <lastmod>'.$section['lastmod'].'</lastmod>'."\n");
                }

                $write('    </sitemap>'."\n");
            }

            $write('</sitemapindex>'."\n");
        });
    }

    /**
     * GET /sitemap-{section}.xml - one child sitemap.
     *
     * The section must appear in the same list index() renders, so the index can never advertise
     * a child that 404s and a page number outside the current range is rejected here rather than
     * producing an empty document.
     */
    public function section(string $section)
    {
        $this->scopeToBaseDomain();

        $meta = collect($this->sections())->firstWhere('name', $section);

        if (! $meta) {
            // Either a genuinely unknown section, or the section list is degraded (see sections()).
            // A 404 is right for both: it is not a server error, and a crawler retries.
            abort(404);
        }

        return match (explode('-', $section, 2)[0]) {
            'pages' => $this->pagesSection(),
            'blog' => $this->urlset(fn (callable $write) => $this->writeBlog($meta, $write)),
            'schedules' => $this->urlset(fn (callable $write) => $this->writeSchedules($meta, $write)),
            'events' => $this->urlset(fn (callable $write) => $this->writeEvents($meta, $write)),
            default => abort(404),
        };
    }

    /**
     * GET /sitemap.xml on a tenant host - one schedule's own sitemap.
     *
     * This exists because a customer custom domain cannot be covered by the global sitemap: Google
     * honours the robots.txt cross-submission grant for our own subdomains but rejects a
     * third-party host as "URL not allowed", so those URLs are discarded wherever else they are
     * listed. Served here, on the host that owns them, they are in scope.
     *
     * A single urlset rather than an index: this is one schedule, and the largest custom-domain
     * schedule is three orders of magnitude below the per-file cap.
     */
    public function schedule(Request $request, ?string $subdomain = null)
    {
        // ResolveCustomDomain rewrites the Host header to {subdomain}.{base} so tenant routing
        // matches, and stashes what the request actually arrived on. That original host - not the
        // rewritten one - is what this sitemap is allowed to list.
        $this->scopeHost = $request->attributes->get('custom_domain_host') ?: $request->getHost();
        $this->scopeIncludesSubdomains = false;

        $subdomain = $request->attributes->get('custom_domain_subdomain') ?: $subdomain;

        $role = $subdomain
            ? $this->scheduleQuery()->where('subdomain', $subdomain)->first()
            : null;

        $url = $role ? $role->getCanonicalUrl() : null;

        // Unknown, deleted or unclaimed - or canonical somewhere other than the host being asked,
        // which is what a schedule with an active custom domain looks like when its subdomain is
        // asked instead. A 404 rather than an empty document: a crawler retries a 404, and an
        // empty urlset would read as "this schedule has nothing".
        if (! $url || ! $this->isListable($url)) {
            abort(404);
        }

        return $this->urlset(fn (callable $write) => $this->writeSchedule($role, $url, $write));
    }

    /** One schedule, its sub-schedules and its events, in that order. */
    private function writeSchedule(Role $role, string $url, callable $write): void
    {
        $cap = $this->urlsPerFile();
        $written = 0;

        $write($this->urlNode($url, $role->updated_at));
        $written++;

        foreach ($role->groups()->whereNotNull('slug')->get(['id', 'role_id', 'slug', 'updated_at']) as $group) {
            $write($this->urlNode(rtrim($url, '/').'/'.rawurlencode($group->slug), $group->updated_at));
            $written++;
        }

        $this->eventQuery()
            ->select(['id', 'slug', 'starts_at', 'days_of_week', 'creator_role_id', 'updated_at'])
            // Same shape as eventQuery()'s acceptance check, narrowed to this schedule.
            ->whereExists(fn ($q) => $q->select(DB::raw(1))
                ->from('event_role')
                ->whereColumn('event_role.event_id', 'events.id')
                ->where('event_role.role_id', $role->id)
                ->where('event_role.is_accepted', true))
            ->with([
                'roles:id,subdomain,type,user_id,email_verified_at,phone_verified_at,custom_domain,custom_domain_mode,custom_domain_status',
                'creatorRole:id,subdomain,type,user_id,email_verified_at,phone_verified_at,timezone',
            ])
            ->chunkByIdDesc(self::HYDRATE_CHUNK, function ($events) use ($write, $cap, &$written) {
                foreach ($events as $event) {
                    if ($written >= $cap) {
                        return false;
                    }

                    $url = $event->getCanonicalUrlOrNull();

                    // An event listed on several schedules is canonical on only one of them, so
                    // this drops the ones whose home schedule is a different host.
                    if (! $url || ! $this->isListable($url)) {
                        continue;
                    }

                    $write($this->urlNode($url, $event->updated_at));
                    $written++;
                }
            });

        if ($written >= $cap) {
            // Never silently truncate: a schedule this large needs the paginated treatment the
            // global sitemap gets, and this is the signal to go build it.
            Log::warning('sitemap: schedule '.$role->subdomain.' hit the '.$cap.'-URL cap and was truncated');
        }
    }

    /** The global sitemap covers this install's own host and everything under it. */
    private function scopeToBaseDomain(): void
    {
        $this->scopeHost = _base_domain();
        $this->scopeIncludesSubdomains = true;
    }

    /*
    |--------------------------------------------------------------------------
    | Section metadata
    |--------------------------------------------------------------------------
    */

    /**
     * The child sitemaps, and for the paginated ones the id range each page covers.
     *
     * This is the single source of truth: index() lists exactly these and section() refuses
     * anything absent, so the two can never disagree.
     */
    private function sections(): array
    {
        if ($this->sections !== null) {
            return $this->sections;
        }

        // Always present, needs no database, and keeps the index from ever being empty.
        $static = [['name' => 'pages', 'lastmod' => $this->staticLastmod()]];

        try {
            // flexible(), not remember(): building the ranges walks every id in each source, so a
            // stampede when the entry expires would mean several concurrent full scans while a
            // crawler pulls children in parallel. This serves the stale list immediately and
            // refreshes once, in the background, under a lock.
            $dynamic = Cache::flexible(
                'sitemap:sections:'.config('app.url'),
                [self::CACHE_SECONDS, self::CACHE_STALE_SECONDS],
                fn () => $this->dynamicSections()
            );

            return $this->sections = array_merge($static, $dynamic);
        } catch (\Throwable $e) {
            // This runs BEFORE streaming starts, so an exception here would be an uncaught 500 -
            // the exact failure this controller exists to prevent. The try must cover the cache
            // call itself, not just its callback: on an install whose database is not migrated
            // the cache store lookup throws before the callback ever runs.
            report($e);

            return $this->sections = $static;
        }
    }

    private function dynamicSections(): array
    {
        $sections = [];

        // Blog routes are registered conditionally (routes/web.php), so route() would throw where
        // they are absent.
        if (Route::has('blog.show')) {
            $sections = array_merge($sections, $this->namedRanges(
                'blog',
                $this->pageRanges($this->blogQuery(), 'asc', null, fn ($post) => $this->blogLastmod($post))
            ));
        }

        $sections = array_merge($sections, $this->namedRanges('schedules', $this->pageRanges(
            $this->scheduleQuery()
                ->select(['id', 'updated_at'])
                ->withCount(['groups' => fn ($q) => $q->whereNotNull('slug')]),
            'asc',
            // A schedule emits its own URL plus one per sub-schedule. Weighting by that is what
            // keeps a page under the cap, and it keeps a schedule and its sub-schedules together.
            fn ($role) => 1 + $role->groups_count,
            fn ($role) => $role->updated_at
        )));

        // Descending, so sitemap-events-1.xml holds the events crawlers care about most.
        $sections = array_merge($sections, $this->namedRanges('events', $this->pageRanges(
            $this->eventQuery()->select(['id', 'updated_at']),
            'desc',
            null,
            fn ($event) => $event->updated_at
        )));

        return $sections;
    }

    private function namedRanges(string $name, array $ranges): array
    {
        return collect($ranges)
            ->map(fn ($range, $i) => array_merge($range, ['name' => $name.'-'.($i + 1)]))
            ->all();
    }

    /**
     * Split a source into pages of at most urlsPerFile URLs, returning the id range each page
     * covers plus its newest timestamp.
     *
     * Ranges rather than LIMIT/OFFSET: each child is a separate HTTP request, minutes or hours
     * apart, so with offsets any row inserted or deleted in between shifts every later page and
     * rows get emitted twice or skipped entirely. Events are ordered by descending id, so every
     * insert lands at the front and the drift is continuous. A keyset range is stable against
     * that, and it also stops deep pages re-scanning everything they skip.
     *
     * Only ids and timestamps are read, one SCAN_CHUNK at a time, so this stays memory-flat.
     */
    private function pageRanges($query, string $direction, ?callable $weigh = null, ?callable $stamp = null): array
    {
        $perFile = $this->urlsPerFile();
        $rows = $direction === 'desc'
            ? $query->lazyByIdDesc(self::SCAN_CHUNK)
            : $query->lazyById(self::SCAN_CHUNK);

        $ranges = [];
        $page = null;

        foreach ($rows as $row) {
            $weight = $weigh ? max(1, (int) $weigh($row)) : 1;

            if ($page && $perFile < $page['urls'] + $weight) {
                $ranges[] = $page;
                $page = null;
            }

            if (! $page) {
                $page = ['min' => $row->id, 'max' => $row->id, 'urls' => 0, 'lastmod' => null];
            }

            $page['min'] = min($page['min'], $row->id);
            $page['max'] = max($page['max'], $row->id);
            $page['urls'] += $weight;
            $page['lastmod'] = $this->newest($page['lastmod'], $stamp ? $stamp($row) : null);
        }

        if ($page) {
            $ranges[] = $page;
        }

        if ($ranges) {
            // Leave the two outer edges open so rows created after this list was cached still land
            // in an existing page instead of falling outside every range and vanishing from the
            // sitemap until the next rebuild. That page may run slightly over cap in the meantime,
            // which is self-correcting and harmless against the 50,000-URL limit.
            //
            // Which page holds the highest ids depends on the scan direction: descending sources
            // put them on page 1, ascending ones on the last page. Opening the wrong edge would
            // make page 1 overlap every other page.
            $highest = $direction === 'desc' ? 0 : count($ranges) - 1;
            $lowest = $direction === 'desc' ? count($ranges) - 1 : 0;

            $ranges[$highest]['max'] = null;
            $ranges[$lowest]['min'] = null;
        }

        return collect($ranges)
            ->map(fn ($range) => ['min' => $range['min'], 'max' => $range['max'], 'lastmod' => $range['lastmod']])
            ->all();
    }

    /*
    |--------------------------------------------------------------------------
    | Sections
    |--------------------------------------------------------------------------
    */

    /**
     * The static marketing and docs pages, still hand-authored in resources/views/sitemap.blade.php
     * so that adding a page there remains the only step required. ~150 URLs, safe to render whole.
     * The view gates the marketing block on is_nexus itself and still emits the site root, so this
     * section is always present, and it needs no database - which is what makes it a usable
     * fallback when the section list is degraded.
     */
    private function pagesSection(): StreamedResponse
    {
        $content = view('sitemap', ['lastmod' => $this->staticLastmod()])->render();

        return $this->streamed(fn (callable $write) => $write($content));
    }

    private function writeBlog(array $range, callable $write): void
    {
        if (! Route::has('blog.show')) {
            return;
        }

        $this->applyRange($this->blogQuery(), $range)
            ->orderBy('id')
            ->chunkById(self::HYDRATE_CHUNK, function ($posts) use ($write) {
                foreach ($posts as $post) {
                    $write($this->urlNode(route('blog.show', $post->slug), $this->blogLastmod($post)));
                }
            });
    }

    private function writeSchedules(array $range, callable $write): void
    {
        // Every column read by Role::getCanonicalUrl() -> getGuestUrl() / isClaimed() /
        // servesOnCustomDomain() must be listed here. A column that is read but not selected reads
        // as null instead of raising, so omissions fail silently.
        $this->applyRange($this->scheduleQuery(), $range)
            ->select([
                'id', 'subdomain', 'user_id', 'email_verified_at', 'phone_verified_at',
                'custom_domain', 'custom_domain_mode', 'custom_domain_status', 'updated_at',
            ])
            ->with(['groups' => fn ($q) => $q->select(['id', 'role_id', 'slug', 'updated_at'])->whereNotNull('slug')])
            ->orderBy('id')
            ->chunkById(self::HYDRATE_CHUNK, function ($roles) use ($write) {
                foreach ($roles as $role) {
                    $url = $role->getCanonicalUrl();

                    // Skipping the schedule takes its sub-schedules with it, which is right: they
                    // are emitted as paths under this same host.
                    if (! $url || ! $this->isListable($url)) {
                        continue;
                    }

                    $write($this->urlNode($url, $role->updated_at));

                    foreach ($role->groups as $group) {
                        $write($this->urlNode(
                            rtrim($url, '/').'/'.rawurlencode($group->slug),
                            $group->updated_at
                        ));
                    }
                }
            });
    }

    private function writeEvents(array $range, callable $write): void
    {
        $skipped = 0;

        // Every column read by Event::getGuestUrlData() must be listed here, including
        // creatorRole.timezone (saleEventDateFromStartsAt) and the roles' custom_domain_* columns
        // (servesOnCustomDomain). is_private / is_draft / is_cancelled / event_password are query
        // predicates only and are deliberately never selected.
        $this->applyRange($this->eventQuery(), $range)
            ->select(['id', 'slug', 'starts_at', 'days_of_week', 'creator_role_id', 'updated_at'])
            ->with([
                'roles:id,subdomain,type,user_id,email_verified_at,phone_verified_at,custom_domain,custom_domain_mode,custom_domain_status',
                'creatorRole:id,subdomain,type,user_id,email_verified_at,phone_verified_at,timezone',
            ])
            ->chunkByIdDesc(self::HYDRATE_CHUNK, function ($events) use ($write, &$skipped) {
                foreach ($events as $event) {
                    // ...OrNull rather than getCanonicalUrl(), which logs an error per unroutable
                    // event. Walking every event in the database would otherwise flood the log.
                    $url = $event->getCanonicalUrlOrNull();

                    if (! $url || ! $this->isListable($url)) {
                        $skipped++;

                        continue;
                    }

                    $write($this->urlNode($url, $event->updated_at));
                }
            });

        if ($skipped) {
            Log::info('sitemap: skipped '.$skipped.' events with no routable or in-scope URL');
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Queries
    |--------------------------------------------------------------------------
    */

    private function scheduleQuery()
    {
        return Role::query()
            ->claimed()
            ->where('is_deleted', false)
            ->whereNotNull('subdomain');
    }

    private function eventQuery()
    {
        return Event::query()
            ->whereNotNull('starts_at')
            ->whereNotNull('slug')
            ->where('is_private', false)
            ->where('is_draft', false)
            ->where('is_cancelled', false)
            ->whereNull('event_password')
            // A correlated EXISTS rather than whereHas: this subquery is re-planned on every
            // chunk, and event_role.event_id is already indexed.
            ->whereExists(fn ($q) => $q->select(DB::raw(1))
                ->from('event_role')
                ->whereColumn('event_role.event_id', 'events.id')
                ->where('event_role.is_accepted', true));
    }

    private function blogQuery()
    {
        return BlogPost::query()
            ->select(['id', 'slug', 'published_at', 'updated_at', 'is_published'])
            ->published();
    }

    /** Bound a query to one page's id range. A null bound is open (see pageRanges). */
    private function applyRange($query, array $range)
    {
        return $query
            ->when($range['min'] !== null, fn ($q) => $q->where('id', '>=', $range['min']))
            ->when($range['max'] !== null, fn ($q) => $q->where('id', '<=', $range['max']));
    }

    /*
    |--------------------------------------------------------------------------
    | Streaming
    |--------------------------------------------------------------------------
    */

    /**
     * Stream a <urlset>. The closing tag is written even when the body fails: once streaming has
     * started the headers are already sent, so a failure cannot become a 500 - a short but
     * well-formed sitemap is better than truncated XML.
     */
    private function urlset(callable $body): StreamedResponse
    {
        return $this->streamed(function (callable $write) use ($body) {
            $write('<?xml version="1.0" encoding="UTF-8"?>'."\n");
            $write('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n");

            try {
                $body($write);
            } catch (\Throwable $e) {
                report($e);
            }

            $write('</urlset>'."\n");
        });
    }

    /**
     * Wrap a writer in a streamed response, flushing incrementally so the body is never held in
     * memory in full.
     *
     * Nothing is compressed here. The bodies used to be gzipped in-process for the .xml.gz URLs
     * and served with Content-Encoding: gzip, but that header describes the *transport*, so any
     * proxy is free to decode it and re-negotiate - Cloudflare does, which meant the same .gz URL
     * returned a gzip stream or bare XML depending on the caller's Accept-Encoding, and never the
     * gzip *file* a .gz sitemap is supposed to be. Content negotiation at the edge already gets
     * the same bytes on the wire (776KB of events XML leaves as 72KB), so the .gz paths are now
     * redirects and there is one representation of each sitemap.
     */
    private function streamed(callable $emit): StreamedResponse
    {
        $headers = [
            // Keep this as application/xml. ResolveCustomDomain calls setContent() on text/html
            // and application/json responses, and StreamedResponse::setContent() throws on
            // anything non-null - so a text/html content type here would 500 on custom domains.
            'Content-Type' => 'application/xml',
            'Cache-Control' => 'public, max-age='.self::CACHE_SECONDS,
        ];

        return response()->stream(function () use ($emit) {
            $pending = 0;

            $write = function (string $chunk) use (&$pending) {
                $pending += strlen($chunk);

                // Symfony only flushes after the whole callback returns, so without this an
                // unbounded output_buffering (or zlib.output_compression) would buffer the entire
                // document in memory - reintroducing the exact OOM this controller exists to fix.
                echo $chunk;

                if ($pending >= self::FLUSH_BYTES) {
                    $pending = 0;
                    $this->flushOutput();
                }
            };

            $emit($write);

            $this->flushOutput();
        }, 200, $headers);
    }

    private function flushOutput(): void
    {
        if (ob_get_level() > 0) {
            ob_flush();
        }

        flush();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * Whether a <loc> may appear in the sitemap being served.
     *
     * Google rejects both of these, and both were live in production: a URL outside the sitemap's
     * own host as "URL not allowed" (the robots.txt cross-submission grant covers our own
     * subdomains, but not the customer custom domains getCanonicalUrl() returns for a schedule that
     * servesOnCustomDomain()), and a host with an over-long label as "Invalid URL". The label limit
     * is not cosmetic - such a host predates the 50-character cap in Role::cleanSubdomain() and
     * does not resolve at all, so those URLs are dead links.
     */
    private function isListable(string $loc): bool
    {
        $host = parse_url($loc, PHP_URL_HOST);

        if (! $host) {
            return false;
        }

        $labels = explode('.', $host);

        foreach ($labels as $label) {
            if (strlen($label) > self::MAX_LABEL_LENGTH) {
                return false;
            }
        }

        if ($host === $this->scopeHost) {
            return true;
        }

        return $this->scopeIncludesSubdomains && str_ends_with($host, '.'.$this->scopeHost);
    }

    private function urlNode(string $loc, $lastmod = null): string
    {
        $xml = '    <url>'."\n";
        $xml .= '        <loc>'.$this->escape($loc).'</loc>'."\n";

        // An empty <lastmod> is schema-invalid, and updated_at can be null on legacy rows.
        if ($lastmod = $this->iso($lastmod)) {
            $xml .= '        <lastmod>'.$lastmod.'</lastmod>'."\n";
        }

        return $xml.'    </url>'."\n";
    }

    /**
     * XML-escape a URL for a <loc>.
     *
     * htmlspecialchars covers & < > " ' but leaves XML-illegal control characters in place, and a
     * single one of those makes the whole document non-well-formed - Google rejects the file, not
     * the offending row. Percent-encoding is NOT applied here: route() already encodes its own
     * parameters, so doing it again would double-encode every URL. Hand-concatenated segments are
     * encoded at the point they are joined instead.
     */
    private function escape(string $value): string
    {
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value) ?? $value;

        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function blogLastmod(BlogPost $post)
    {
        return $post->updated_at && $post->published_at && $post->updated_at->gt($post->published_at)
            ? $post->updated_at
            : $post->published_at;
    }

    /** The later of two timestamps, as an ISO 8601 string. */
    private function newest(?string $current, $candidate): ?string
    {
        $candidate = $this->iso($candidate);

        if (! $candidate) {
            return $current;
        }

        return ($current === null || $candidate > $current) ? $candidate : $current;
    }

    private function iso($value): ?string
    {
        if (! $value) {
            return null;
        }

        return $value instanceof \DateTimeInterface
            ? \Illuminate\Support\Carbon::instance($value)->toIso8601String()
            : \Illuminate\Support\Carbon::parse($value)->toIso8601String();
    }

    private function urlsPerFile(): int
    {
        return max(1, (int) config('app.sitemap_urls_per_file', 10000));
    }

    /**
     * Static marketing/docs pages change on deploy, not per crawl. Derive <lastmod> from the newest
     * marketing view mtime so it reflects real content changes instead of "now" (which trains
     * crawlers to ignore it). Memoized per request rather than cached in the cache store: it is
     * ~90 stat calls, and it must keep working when the cache store is the thing that is broken.
     */
    private function staticLastmod(): string
    {
        if ($this->staticLastmod !== null) {
            return $this->staticLastmod;
        }

        $mtime = collect(glob(resource_path('views/marketing/*.blade.php')) ?: [])
            ->push(resource_path('views/sitemap.blade.php'))
            ->map(fn ($file) => @filemtime($file) ?: 0)
            ->max();

        return $this->staticLastmod = $mtime > self::MIN_PLAUSIBLE_MTIME
            ? now()->setTimestamp($mtime)->toIso8601String()
            : $this->staticLastmodFallback();
    }

    /**
     * <lastmod> for deployments whose filesystem has no usable mtimes.
     *
     * Reproducible-build images normalize every file to a fixed timestamp - the buildpack the
     * hosted deployment runs on stamps 1980-01-01T00:00:01Z - so filemtime() there reports that
     * the marketing pages last changed 46 years ago and crawlers stop revisiting them.
     *
     * The release is the honest substitute: record when the running version was first served and
     * reuse it until the next one ships. rememberForever (not remember) and keyed on the version,
     * so the value is stable for every container on that release and moves only on deploy.
     */
    private function staticLastmodFallback(): string
    {
        $now = now()->toIso8601String();

        try {
            return Cache::rememberForever(
                'sitemap:static_lastmod:'.config('self-update.version_installed'),
                fn () => $now
            );
        } catch (\Throwable $e) {
            // staticLastmod() is the one part of the index that has to survive a broken cache
            // store - sections() falls back to the pages section alone, which needs this value.
            report($e);

            return $now;
        }
    }
}
