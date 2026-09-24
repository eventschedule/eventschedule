<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\Event;
use App\Models\Role;
use App\Services\DemoService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

    /**
     * The platform's own hosts under the base domain. None of them serves a schedule page. See
     * isTenantUrl().
     */
    private const PLATFORM_HOST_LABELS = ['app', 'www', 'blog'];

    /**
     * Every roles column the event loops read, for the narrowed eager load: getGuestUrlData() and
     * canonicalTarget() (subdomain, type, the verification dates, is_deleted and the pivot),
     * servesOnCustomDomain() (custom_domain_*), Role::isIndexableHost() (user_id, email, phone)
     * and is_unlisted. A column that is read but not selected reads as null instead of raising,
     * so an omission fails silently.
     */
    private const EVENT_ROLE_COLUMNS = 'id,subdomain,type,user_id,email,email_verified_at,phone,phone_verified_at,'
        .'is_deleted,is_unlisted,custom_domain,custom_domain_mode,custom_domain_status';

    /** Per-request memo of the section list. */
    private ?array $sections = null;

    private ?string $staticLastmod = null;

    /*
     * Per-request state, cleared by resetRequestState() at every public entry point. Laravel keeps
     * one controller instance per route and serves every request to that route with it (a test
     * makes several), so anything memoised here would otherwise answer the next request with this
     * one's data.
     */

    /** The instant the sitemap windows are measured from. See now(). */
    private ?Carbon $now = null;

    /** See demoOwnerId(). */
    private ?int $demoOwnerId = null;

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
        $this->resetRequestState();
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
        $this->resetRequestState();
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
        $this->resetRequestState();

        // ResolveCustomDomain rewrites the Host header to {subdomain}.{base} so tenant routing
        // matches, and stashes what the request actually arrived on. That original host - not the
        // rewritten one - is what this sitemap is allowed to list.
        $this->scopeHost = $request->attributes->get('custom_domain_host') ?: $request->getHost();
        $this->scopeIncludesSubdomains = false;

        $subdomain = $request->attributes->get('custom_domain_subdomain') ?: $subdomain;

        // scheduleQuery() is Role::isIndexableHost() in SQL, so a schedule whose pages answer
        // noindex - unverified, deleted, demo content - has no sitemap to offer.
        $role = $subdomain
            ? $this->scheduleQuery()->where('roles.subdomain', $subdomain)->first()
            : null;

        $url = $role ? $role->getCanonicalUrl() : null;

        // Unknown or not indexable - or canonical somewhere other than the host being asked, which
        // is what a schedule with an active custom domain looks like when its subdomain is asked
        // instead. A 404 rather than an empty document: a crawler retries a 404, and an empty
        // urlset would read as "this schedule has nothing".
        if (! $url || ! $this->isListable($url) || ! $this->isTenantUrl($url)) {
            abort(404);
        }

        return $this->urlset(fn (callable $write) => $this->writeSchedule($role, $url, $write));
    }

    /**
     * One schedule and its events, in that order.
     *
     * No sub-schedules: a sub-schedule page canonicalizes to the schedule root, so listing it
     * submitted a URL that names another as the page to index (109 of them, in production).
     *
     * Deliberately eventQuery(false): the discovery flags say "do not surface this from OUR
     * listings", and a schedule's own sitemap on its own host is the owner's listing, not ours.
     */
    private function writeSchedule(Role $role, string $url, callable $write): void
    {
        $cap = $this->urlsPerFile();
        $written = 0;

        $write($this->urlNode($url, $role->updated_at));
        $written++;

        // Same shape as eventQuery()'s acceptance check, narrowed to this schedule. A closure,
        // because the collapse needs the same rows as a separate query.
        $eligible = fn () => $this->eventQuery(false)
            ->whereExists(fn ($q) => $q->select(DB::raw(1))
                ->from('event_role')
                ->whereColumn('event_role.event_id', 'events.id')
                ->where('event_role.role_id', $role->id)
                ->where('event_role.is_accepted', true));

        $winners = $this->collapseWinners($eligible());

        $eligible()
            ->select(['id', 'slug', 'starts_at', 'days_of_week', 'creator_role_id', 'updated_at'])
            ->with([
                'roles:'.self::EVENT_ROLE_COLUMNS,
                'creatorRole:id,subdomain,type,user_id,email_verified_at,phone_verified_at',
            ])
            ->chunkByIdDesc(self::HYDRATE_CHUNK, function ($events) use ($role, $write, $cap, $winners, &$written) {
                foreach ($events as $event) {
                    if ($written >= $cap) {
                        return false;
                    }

                    if ($this->isCollapsedAway($event, $winners)) {
                        continue;
                    }

                    // An event listed on several schedules is canonical on only one of them, and
                    // this sitemap lists it only when that one is this schedule. Checking the host
                    // alone was not enough: on selfhost every schedule shares one host, so each
                    // schedule's sitemap listed the canonicals of every event it had accepted.
                    $loc = $this->eventLoc($event, fn (Role $home) => $home->is($role));

                    if (! $loc) {
                        continue;
                    }

                    $write($this->urlNode($loc, $event->updated_at));
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
                $this->pageRanges($this->blogQuery(), 'asc', fn ($post) => $this->blogLastmod($post))
            ));
        }

        // One URL per schedule. Sub-schedules are not listed (see writeSchedule()), so a schedule
        // no longer weighs one URL per sub-schedule.
        $sections = array_merge($sections, $this->namedRanges('schedules', $this->pageRanges(
            $this->discoverableScheduleQuery()->select(['id', 'updated_at']),
            'asc',
            fn ($role) => $role->updated_at
        )));

        // Descending, so sitemap-events-1.xml holds the events crawlers care about most.
        $sections = array_merge($sections, $this->namedRanges('events', $this->pageRanges(
            $this->discoverableEventQuery()->select(['id', 'updated_at']),
            'desc',
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
     *
     * Every row counts as one URL. A page can end up listing fewer - an event whose canonical is on
     * a host this sitemap may not carry is skipped, and so is a collapsed instance (see
     * collapseWinners()) - which keeps it under the cap either way.
     */
    private function pageRanges($query, string $direction, ?callable $stamp = null): array
    {
        $perFile = $this->urlsPerFile();
        $rows = $direction === 'desc'
            ? $query->lazyByIdDesc(self::SCAN_CHUNK)
            : $query->lazyById(self::SCAN_CHUNK);

        $ranges = [];
        $page = null;

        foreach ($rows as $row) {
            if ($page && $perFile < $page['urls'] + 1) {
                $ranges[] = $page;
                $page = null;
            }

            if (! $page) {
                $page = ['min' => $row->id, 'max' => $row->id, 'urls' => 0, 'lastmod' => null];
            }

            $page['min'] = min($page['min'], $row->id);
            $page['max'] = max($page['max'], $row->id);
            $page['urls']++;
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
     *
     * Each page dates itself from the committed config/sitemap_lastmod.php manifest (rebuilt by
     * `php artisan sitemap:lastmod`). Every page used to report the same date - on the hosted
     * deploy, the container's boot time - so every release told Google the whole marketing site
     * had changed, which is how a site teaches Google to ignore lastmod entirely.
     */
    private function pagesSection(): StreamedResponse
    {
        $content = view('sitemap', ['lastmodTag' => $this->lastmodTag(...)])->render();

        return $this->streamed(fn (callable $write) => $write($content));
    }

    /**
     * The <lastmod> element for one static page, or an empty string when the manifest has no date
     * for it. Absent beats wrong: Google discounts the signal site-wide once it can prove it is
     * unreliable, and an undated page simply keeps its previously discovered date.
     *
     * The format check is not paranoia about the generated file so much as about a hand-edit of
     * it: an unparseable <lastmod> makes the whole document invalid, and Google rejects the file
     * rather than the row.
     */
    private function lastmodTag(string $path): string
    {
        // The whole array, not config('sitemap_lastmod.'.$path): the keys are URL paths and
        // Arr::get would read a dot in one of them as a level of nesting.
        $date = (config('sitemap_lastmod') ?: [])[$path] ?? null;

        if (! is_string($date) || ! preg_match('/^\d{4}-\d{2}-\d{2}(T[\d:.]+(Z|[+-]\d{2}:\d{2}))?$/', $date)) {
            return '';
        }

        return '<lastmod>'.$date.'</lastmod>';
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
        // as null instead of raising, so omissions fail silently. Indexability needs no columns:
        // discoverableScheduleQuery() decides it in SQL.
        //
        // No sub-schedules. Their pages canonicalize to the schedule root, so every one listed was
        // a URL naming another as the page to index - 109 of them, in production.
        $this->applyRange($this->discoverableScheduleQuery(), $range)
            ->select([
                'id', 'subdomain', 'user_id', 'email_verified_at', 'phone_verified_at',
                'custom_domain', 'custom_domain_mode', 'custom_domain_status', 'updated_at',
            ])
            ->orderBy('id')
            ->chunkById(self::HYDRATE_CHUNK, function ($roles) use ($write) {
                foreach ($roles as $role) {
                    $url = $role->getCanonicalUrl();

                    if (! $url || ! $this->isListable($url) || ! $this->isTenantUrl($url)) {
                        continue;
                    }

                    $write($this->urlNode($url, $role->updated_at));
                }
            });
    }

    private function writeEvents(array $range, callable $write): void
    {
        $skipped = 0;

        // Over every eligible event, not just this page's range: the instances of one slug are
        // spread across pages, and each page has to agree on which one is listed.
        $winners = $this->collapseWinners($this->discoverableEventQuery());

        // EVENT_ROLE_COLUMNS lists what the loop reads from the roles. is_private / is_draft /
        // is_cancelled / event_password are query predicates only and are deliberately never
        // selected; the canonical carries no date, so creatorRole.timezone is not needed either.
        $this->applyRange($this->discoverableEventQuery(), $range)
            ->select(['id', 'slug', 'starts_at', 'days_of_week', 'creator_role_id', 'updated_at'])
            ->with([
                'roles:'.self::EVENT_ROLE_COLUMNS,
                'creatorRole:id,subdomain,type,user_id,email_verified_at,phone_verified_at',
            ])
            ->chunkByIdDesc(self::HYDRATE_CHUNK, function ($events) use ($write, $winners, &$skipped) {
                foreach ($events as $event) {
                    if ($this->isCollapsedAway($event, $winners)) {
                        continue;
                    }

                    // An unlisted home keeps its events out of OUR listing, as it keeps itself out
                    // of discoverableScheduleQuery(). The per-schedule sitemap has no such rule.
                    $loc = $this->eventLoc($event, fn (Role $home) => ! $home->is_unlisted);

                    if (! $loc) {
                        $skipped++;

                        continue;
                    }

                    $write($this->urlNode($loc, $event->updated_at));
                }
            });

        if ($skipped) {
            Log::info('sitemap: skipped '.$skipped.' events with no routable, indexable or in-scope URL');
        }
    }

    /**
     * The URL an event is listed at, or null when it must not be listed.
     *
     * The URL is the canonical the page prints (Event::canonicalTarget()), so the sitemap never
     * submits a URL that names another as the page to index. It is listed only when the schedule it
     * is canonical on answers "index, follow" (Role::isIndexableHost(), the page's own robots rule)
     * and passes $homeAllowed: eventQuery() only proves that SOME indexable schedule accepted the
     * event, and the canonical can sit on another - a performer whose contact was never verified,
     * say - whose page would refuse indexing.
     *
     * canonicalTarget() rather than getCanonicalUrl(), which logs an error per unroutable event:
     * walking every event in the database would flood the log.
     */
    private function eventLoc(Event $event, callable $homeAllowed): ?string
    {
        [$url, $home] = $event->canonicalTarget();

        if (! $url || ! $home || ! $home->isIndexableHost($this->demoOwnerId()) || ! $homeAllowed($home)) {
            return null;
        }

        return $this->isListable($url) && $this->isTenantUrl($url) ? $url : null;
    }

    /*
    |--------------------------------------------------------------------------
    | Queries
    |--------------------------------------------------------------------------
    */

    /**
     * Every schedule whose pages may be indexed, whether or not it is discoverable.
     *
     * Role::isIndexableHost() in SQL - the rule the page's own robots meta applies - so neither
     * sitemap submits a schedule whose page answers noindex: an unverified one, a deleted one, or
     * demo content (the /examples showcase included, which is caught only by its contact address).
     * In SQL rather than per row: these queries chunk over every schedule, and the demo owner check
     * reads $role->user, which per row would be an N+1.
     *
     * This is the lookup the per-tenant sitemap uses, so it deliberately does NOT apply the
     * discovery rules below: a schedule serving its own host is entitled to a sitemap of that host
     * even when it has opted out of being listed anywhere else.
     */
    private function scheduleQuery()
    {
        return Role::query()
            ->indexableHost()
            ->whereNotNull('roles.subdomain');
    }

    /**
     * The schedules the GLOBAL sitemap advertises.
     *
     * is_unlisted is the owner saying "do not put this in a directory". The page itself stays
     * indexable - unlisted means shared by link, not hidden - but submitting to Google a URL the
     * product keeps out of its own listings is a mixed signal, so discovery is left to the link.
     *
     * And only a schedule with something to show: at least one event it accepted that the events
     * sitemap would consider. 293 of 664 schedules had no listed event at all. Sitemap hygiene
     * only - an empty schedule's page stays indexable, it is just not submitted.
     */
    private function discoverableScheduleQuery()
    {
        return $this->scheduleQuery()
            ->where('roles.is_unlisted', false)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('event_role')
                    ->join('events', 'events.id', '=', 'event_role.event_id')
                    ->whereColumn('event_role.role_id', 'roles.id')
                    ->where('event_role.is_accepted', true)
                    ->where('events.is_hidden_from_discovery', false);

                self::publicEventRows($q);
                Event::constrainSitemapWindow($q, $this->now());
            });
    }

    /**
     * Every event with a public URL that is still in the sitemap window, whether or not it is
     * discoverable. See scheduleQuery() for why the per-tenant sitemap asks for this one with
     * $global false, and Event::constrainSitemapWindow() for the window.
     */
    private function eventQuery(bool $global = true)
    {
        $query = self::publicEventRows(Event::query());

        Event::constrainSitemapWindow($query, $this->now());

        // A correlated EXISTS rather than whereHas: this subquery is re-planned on every chunk, and
        // event_role.event_id is already indexed.
        //
        // The role-side predicate has to live INSIDE this subquery so it binds to the same pivot
        // row as is_accepted, the way publicUpcomingEventsQuery() and
        // FederationService::federatableQuery() do it. Without it, an event whose only accepted
        // pivot is an ownerless placeholder schedule qualifies - and RoleController::viewGuest()
        // turns away anything that fails isClaimed(), so the URL we would emit is a 404.
        //
        // The demo exclusion (inside isIndexableHost) binds to the same pivot row for the same
        // reason: an event only qualifies if some NON-demo schedule accepted it. Excluding demo
        // events outright would drop a real schedule's event that a demo curator picked up.
        return $query->whereExists(fn ($q) => $q->select(DB::raw(1))
            ->from('event_role')
            ->join('roles', 'roles.id', '=', 'event_role.role_id')
            ->whereColumn('event_role.event_id', 'events.id')
            ->where('event_role.is_accepted', true)
            ->where(fn ($r) => Role::constrainIndexableHost($r))
            ->when($global, fn ($r) => $r->where('roles.is_unlisted', false)));
    }

    /**
     * The events the GLOBAL sitemap advertises.
     *
     * is_hidden_from_discovery is set on an event that its schedule has pulled out of /browse. The
     * event page stays public and indexable; it just stops being surfaced by us. Submitting it to
     * Google anyway would be us surfacing it, which is the thing the flag turns off.
     */
    private function discoverableEventQuery()
    {
        return $this->eventQuery()->where('events.is_hidden_from_discovery', false);
    }

    /**
     * The event columns that make a row public at all. Qualified, so the same filter runs inside
     * discoverableScheduleQuery()'s correlated subquery as on the events query itself.
     */
    private static function publicEventRows($query, string $table = 'events')
    {
        return $query->whereNotNull($table.'.starts_at')
            ->whereNotNull($table.'.slug')
            // An empty slug routes nowhere: route() refuses to build /{subdomain}//{id}, and the
            // exception would end the urlset early.
            ->where($table.'.slug', '<>', '')
            ->where($table.'.is_private', false)
            ->where($table.'.is_draft', false)
            ->where($table.'.is_cancelled', false)
            ->whereNull($table.'.event_password');
    }

    /**
     * Which instance of each one-off event the sitemap lists: one URL per (creator, slug).
     *
     * Google Calendar sync imports a recurring series with singleEvents (GoogleCalendarService),
     * so every occurrence arrives as a one-off row of its own sharing a slug: one schedule put
     * 3,090 of the 4,441 event URLs in the sitemap, 404 of them a single slug. Each row keeps its
     * page; the sitemap lists one - the one a bare /{slug} shows (EventRepo::getEvent()), which is
     * the next upcoming instance, else the latest. A recurring series is a single row already, and
     * an event with no creator, or another creator's, is never folded into a group.
     *
     * One GROUP BY over the same rows the listing walks, once per request, and only groups of two
     * or more come back, so the map is as small as the problem. The winner's id is packed behind
     * its starts_at - CONCAT(starts_at, LPAD(id, 20, '0')) orders by time, then by id - so an exact
     * tie resolves the same way on every page of the sitemap, not to whichever row a page saw first.
     *
     * @return array<string, int> collapseKey() => the id of the instance to list
     */
    private function collapseWinners($eligible): array
    {
        $now = $this->now();
        $packed = "CONCAT(events.starts_at, LPAD(events.id, 20, '0'))";

        // EventRepo::getEvent()'s "upcoming" test for a bare slug.
        $upcoming = 'events.starts_at >= ? OR (events.duration >= 24 AND DATE_ADD(events.starts_at, INTERVAL events.duration HOUR) >= ?)';

        $rows = $eligible
            ->whereNull('events.days_of_week')
            ->whereNotNull('events.creator_role_id')
            ->groupBy('events.creator_role_id', 'events.slug')
            ->havingRaw('COUNT(*) > 1')
            ->selectRaw(
                "events.creator_role_id, events.slug, COALESCE(MIN(CASE WHEN {$upcoming} THEN {$packed} END), MAX({$packed})) AS pick",
                [$now->copy()->subDay()->format('Y-m-d H:i:s'), $now->format('Y-m-d H:i:s')]
            )
            ->toBase()
            ->get();

        $winners = [];

        foreach ($rows as $row) {
            $winners[self::collapseKey($row->creator_role_id, $row->slug)] = (int) substr($row->pick, -20);
        }

        return $winners;
    }

    /** Whether $event is an instance collapseWinners() folded into another. */
    private function isCollapsedAway(Event $event, array $winners): bool
    {
        // The same rows the GROUP BY took: days_of_week IS NULL, creator_role_id IS NOT NULL.
        if ($event->days_of_week !== null || ! $event->creator_role_id) {
            return false;
        }

        $winner = $winners[self::collapseKey($event->creator_role_id, $event->slug)] ?? null;

        return $winner !== null && $winner !== (int) $event->id;
    }

    /**
     * Lower-cased because MySQL grouped the slugs case-insensitively. Only ever finer than the
     * collation, so a mismatch can leave an instance listed, never drop the one that was picked.
     */
    private static function collapseKey($creatorRoleId, ?string $slug): string
    {
        return $creatorRoleId.'|'.mb_strtolower((string) $slug);
    }

    private function blogQuery()
    {
        return BlogPost::query()
            ->select(['id', 'slug', 'published_at', 'updated_at', 'is_published'])
            ->published()
            // A post an admin marked noindex renders `noindex, follow`; listing it here would
            // ask Google to crawl a URL the page itself refuses.
            ->where('noindex', false);
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

    /**
     * Whether a <loc> is on a host that serves schedule pages at all.
     *
     * The platform's own app., www. and blog. hosts never do - routes/web.php keeps the tenant
     * group off the first two and registers the blog ahead of it - so a schedule holding one of
     * those names from before they were reserved would be listed at a URL that lands on the
     * dashboard, the apex or the blog. Kept apart from isListable(), which asks whether Google
     * accepts the host in this sitemap at all, and has to accept the blog.
     *
     * Only those hosts under the base domain: a customer's custom domain is free to be www.
     */
    private function isTenantUrl(string $loc): bool
    {
        $host = strtolower((string) parse_url($loc, PHP_URL_HOST));
        $base = strtolower(_base_domain());

        foreach (self::PLATFORM_HOST_LABELS as $label) {
            if ($host === $label.'.'.$base) {
                return false;
            }
        }

        return $host !== '';
    }

    /**
     * The demo user's id, looked up once per request, or 0 when there is none - an id no row has.
     * Handed to Role::isIndexableHost() so that checking every event's home schedule costs this one
     * query rather than a users query per event.
     */
    private function demoOwnerId(): int
    {
        return $this->demoOwnerId ??= (int) DB::table('users')
            ->where('email', DemoService::DEMO_EMAIL)
            ->value('id');
    }

    /** One instant per request, so the queries of one sitemap agree on what "past" means. */
    private function now(): Carbon
    {
        return $this->now ??= Carbon::now('UTC');
    }

    private function resetRequestState(): void
    {
        $this->now = null;
        $this->demoOwnerId = null;
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
     * The index's roll-up <lastmod> for the pages child: the newest date any page in it reports.
     *
     * The per-page dates come from config/sitemap_lastmod.php (see lastmodTag()), so the newest of
     * those is by definition the newest thing in that child. Only when the manifest is missing or
     * empty - a fresh checkout that has never run `php artisan sitemap:lastmod`, or a selfhost
     * install running ahead of one - does this fall back to the old view-mtime reading, and then to
     * the release stamp for filesystems whose mtimes are build artifacts.
     *
     * Memoized per request rather than cached in the cache store: the fallback is ~90 stat calls,
     * and this must keep working when the cache store is the thing that is broken.
     */
    private function staticLastmod(): string
    {
        if ($this->staticLastmod !== null) {
            return $this->staticLastmod;
        }

        $newest = collect(config('sitemap_lastmod') ?: [])->max();

        if (is_string($newest) && $newest !== '') {
            return $this->staticLastmod = $newest;
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
