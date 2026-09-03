<?php

namespace App\Console\Commands;

use App\Console\Concerns\ReportsChecks;
use App\Http\Middleware\CacheableMarketingResponse;
use App\Models\ScheduledTaskRun;
use App\Services\AdminAlertService;
use App\Services\SchedulerHealth;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
 * Did that step work? Run after each step of docs/NEXUS_RELEASE.md.
 *
 * Two halves, because the two questions live in different places:
 *
 *   --edge   over HTTP, from anywhere. Asserts the contract in docs/CACHING.md: which responses
 *            are public, which stay private, and which must not set a session cookie.
 *   --local  inside the app, so it only means anything on the container. Scheduler rails, the
 *            per-task health rows, the queue, the cache store, the admin alert list.
 *
 * With neither flag it runs whichever it can: the edge checks always, and the local ones only
 * when the database is actually reachable, so the same command works from a laptop and from the
 * console without the operator having to remember which.
 *
 * Before the cutover this command SHOULD fail its edge assertions - the origin has not shipped
 * the middleware and the Cloudflare rule does not exist. That failure is the point: a check
 * that cannot fail is not evidence of anything.
 */
class DeployVerify extends Command
{
    use ReportsChecks;

    protected $signature = 'deploy:verify
        {--url=https://eventschedule.com : Origin to check}
        {--edge : Only the HTTP edge-cache contract}
        {--local : Only the in-app checks (scheduler, queue, cache)}';

    protected $description = 'Verify a deploy step: edge-cache headers over HTTP, and scheduler/queue health in-app.';

    /**
     * Pages that must be edge-cacheable, and pages that must not.
     *
     * The excluded three are in CacheableMarketingResponse::EXCLUDED_ROUTES for their own
     * reasons - /contact has a form, /search and /browse answer a query - and they are here so
     * that "everything is cached" is never mistaken for success.
     */
    private const CACHEABLE_PATHS = ['/pricing', '/', '/faq'];

    private const PRIVATE_PATHS = ['/pricing?lang=fr', '/contact', '/browse'];

    /**
     * Neither is a page, both are fetched BY a cached page, and a session cookie on either takes
     * the visitor off the edge for the rest of their session in exchange for a session nothing
     * reads. This is the check that catches "edge caching stopped working after page one".
     */
    private const NO_COOKIE_PATHS = ['/docs/search-index.json'];

    public function handle(): int
    {
        $onlyLocal = $this->option('local');
        $onlyEdge = $this->option('edge');

        $this->line('');
        $this->line('  <options=bold>Deploy verify</>'
            .($onlyLocal ? '  <fg=gray>'.gethostname().'</>' : '  <fg=gray>'.$this->option('url').'</>'));

        if (! $onlyLocal) {
            $this->verifyEdge();
        }

        if (! $onlyEdge) {
            $this->verifyLocal();
        }

        return $this->summarise();
    }

    private function verifyEdge(): void
    {
        $this->section('edge cache contract');

        $base = rtrim($this->option('url'), '/');

        foreach (self::CACHEABLE_PATHS as $path) {
            $this->assertCacheable($base, $path);
        }

        foreach (self::PRIVATE_PATHS as $path) {
            $this->assertPrivate($base, $path);
        }

        foreach (self::NO_COOKIE_PATHS as $path) {
            $this->assertNoSessionCookie($base, $path);
        }

        $this->assertEveryResponseCarriesCacheControl($base);
    }

    private function assertCacheable(string $base, string $path): void
    {
        $response = $this->fetch($base.$path);

        if ($response === null) {
            return;
        }

        $expected = CacheableMarketingResponse::CACHE_CONTROL;
        $actual = (string) $response->header('Cache-Control');

        // Directive SETS, not the raw string. Symfony's ResponseHeaderBag re-serialises
        // Cache-Control with the directives sorted, so the middleware's
        // "public, max-age=0, s-maxage=600" reaches the wire as
        // "max-age=0, public, s-maxage=600" - and a === against the constant fails on a
        // response that is completely correct. Found by pointing this command at a local build
        // of the very code it is meant to pass.
        if ($this->directives($actual) !== $this->directives($expected)) {
            $this->failed($path.' is not edge-cacheable', 'cache-control: '.($actual ?: 'none'));

            return;
        }

        // Vary on COOKIE specifically, which would give every visitor their own cache entry:
        // the three 30-day utm_* cookies are encrypted, so the same value is different
        // ciphertext per visitor and the cache stops working from their second page onward.
        // Vary: Accept-Encoding is not that - it is the web server's gzip negotiation, present
        // on every response nginx compresses, and rejecting it fails a correct deploy.
        $vary = strtolower((string) $response->header('Vary'));

        if (str_contains($vary, 'cookie') || str_contains($vary, 'authorization')) {
            $this->failed($path.' sends Vary: '.$response->header('Vary'),
                'a per-visitor cache key defeats the whole cache');

            return;
        }

        if ($this->sessionCookieOn($response)) {
            $this->failed($path.' is marked public but still sets a session cookie');

            return;
        }

        $cf = $response->header('CF-Cache-Status');
        $this->passed($path.' is edge-cacheable', trim('no cookies, no cookie-keyed Vary'.($cf ? ', cf-cache-status: '.$cf : '')));
    }

    private function assertPrivate(string $base, string $path): void
    {
        $response = $this->fetch($base.$path);

        if ($response === null) {
            return;
        }

        $actual = (string) $response->header('Cache-Control');

        if (str_contains($actual, 'public') || str_contains($actual, 's-maxage')) {
            $this->failed($path.' is cacheable and must not be', 'cache-control: '.$actual);

            return;
        }

        $this->passed($path.' stays private', 'cache-control: '.($actual ?: 'none'));
    }

    private function assertNoSessionCookie(string $base, string $path): void
    {
        $response = $this->fetch($base.$path);

        if ($response === null) {
            return;
        }

        if ($this->sessionCookieOn($response)) {
            $this->failed($path.' sets a session cookie',
                'one of these takes the visitor off the edge for their whole session');

            return;
        }

        $this->passed($path.' sets no session cookie');
    }

    /**
     * The Cloudflare rule's Edge TTL is "use cache-control if present, USE DEFAULT OTHERWISE",
     * so a 200 on the apex with no Cache-Control at all becomes newly cacheable at whatever the
     * zone default is - including responses nobody considered. Cheap to sweep, so sweep it.
     */
    private function assertEveryResponseCarriesCacheControl(string $base): void
    {
        $paths = ['/robots.txt', '/manifest.webmanifest', '/favicon.ico', '/up', '/sitemap.xml'];
        $missing = [];

        foreach ($paths as $path) {
            $response = $this->fetch($base.$path, quiet: true);

            if ($response !== null && $response->status() === 200 && $response->header('Cache-Control') === '') {
                $missing[] = $path;
            }
        }

        if ($missing === []) {
            $this->passed('Every sampled apex 200 carries a Cache-Control header');
        } else {
            $this->failed('No Cache-Control on: '.implode(', ', $missing),
                'the cache rule would apply its default TTL to these');
        }
    }

    private function verifyLocal(): void
    {
        $this->section('in-app health');

        try {
            DB::select('select 1');
        } catch (\Throwable $e) {
            $this->warned('Skipping in-app checks', 'no database from here - run this on the container');

            return;
        }

        $this->verifyCacheStore();
        $this->verifyScheduler();
        $this->verifyQueue();
        $this->verifyAlerts();
    }

    private function verifyCacheStore(): void
    {
        $store = config('cache.default');
        $probe = 'deploy:verify:'.bin2hex(random_bytes(6));

        try {
            Cache::put($probe, 'ok', 10);
            $roundTripped = Cache::get($probe) === 'ok';
            Cache::forget($probe);
        } catch (\Throwable $e) {
            $this->failed('Cache store "'.$store.'" is not usable', $e->getMessage());

            return;
        }

        if (! $roundTripped) {
            $this->failed('Cache store "'.$store.'" did not return what was written to it');

            return;
        }

        // The round trip passes on the file driver too - it works perfectly well inside one
        // container, which is exactly why the multi-container hazard is silent. So the store
        // NAME has to be asserted separately from the store working.
        if ($store === 'database') {
            $this->passed('Cache store is database and round-trips');
        } elseif (config('app.hosted')) {
            $this->warned('Cache store is "'.$store.'" and round-trips',
                'every lock and the scheduler heartbeat are per-container until this is database');
        } else {
            $this->passed('Cache store is "'.$store.'" and round-trips');
        }
    }

    private function verifyScheduler(): void
    {
        $expected = SchedulerHealth::expectedRail();

        if (SchedulerHealth::isStalled()) {
            $this->failed('Scheduler reports STALLED',
                $expected ? 'expecting rail "'.$expected.'"' : 'no heartbeat within '.SchedulerHealth::staleMinutes().'m');
        } else {
            $this->passed('Scheduler heartbeat is fresh', $expected ? 'rail "'.$expected.'"' : 'aggregate heartbeat');
        }

        foreach (SchedulerHealth::rails() as $rail) {
            $this->note(sprintf('rail %-8s last tick %s%s',
                $rail->name,
                $rail->at ? $rail->at->diffForHumans() : 'never',
                $rail->stale ? '  (STALE)' : ''));
        }

        $problem = SchedulerHealth::tasks()
            ->whereIn('state', ['failed', 'never_finished', 'overdue'])
            ->pluck('name');

        if ($problem->isEmpty()) {
            $this->passed('No scheduled task is failed, overdue or never-finished');
        } else {
            $this->failed($problem->count().' scheduled task(s) in trouble', $problem->implode(', '));
        }

        // last_host is the App Platform instance id, which is the only direct evidence of WHICH
        // container is actually running the schedule.
        $hosts = ScheduledTaskRun::query()
            ->whereNotNull('last_finished_at')
            ->distinct()
            ->pluck('last_via', 'last_host');

        foreach ($hosts as $host => $via) {
            $this->note('tasks completing on host '.($host ?: '?').' via rail "'.($via ?: '?').'"');
        }
    }

    private function verifyQueue(): void
    {
        $failed = DB::table('failed_jobs')->count();

        if ($failed === 0) {
            $this->passed('No failed jobs');
        } else {
            $this->failed($failed.' failed job(s)', 'see /admin/queue');
        }

        // available_at, never created_at: a delayed dispatch has created_at = now and would
        // report a backlog that does not exist. AdminController::queue() takes the same care.
        $oldest = DB::table('jobs')->where('available_at', '<=', now()->timestamp)->min('available_at');

        if ($oldest === null) {
            $this->passed('Nothing waiting in the queue');

            return;
        }

        $waitingMinutes = (int) round((now()->timestamp - $oldest) / 60);

        if ($waitingMinutes >= AdminAlertService::JOBS_STALLED_MINUTES) {
            $this->failed('Oldest due job has waited '.$waitingMinutes.'m',
                'nothing is draining the queue');
        } else {
            $this->passed('Queue is draining', 'oldest due job '.$waitingMinutes.'m old');
        }
    }

    private function verifyAlerts(): void
    {
        $items = AdminAlertService::items();

        if ($items->isEmpty()) {
            $this->passed('No admin alerts');

            return;
        }

        foreach ($items as $item) {
            $label = ($item['title'] ?? $item['type'] ?? 'alert');

            if (($item['color'] ?? '') === 'red') {
                $this->failed('Alert: '.$label, $item['subtitle'] ?? null);
            } else {
                $this->warned('Alert: '.$label, $item['subtitle'] ?? null);
            }
        }
    }

    /**
     * Cache-Control as a comparable set: lower-cased, trimmed, sorted.
     *
     * @return array<int, string>
     */
    private function directives(string $header): array
    {
        $parts = array_filter(array_map('trim', explode(',', strtolower($header))));
        sort($parts);

        return array_values($parts);
    }

    private function sessionCookieOn($response): bool
    {
        $cookies = implode(' ', $response->headers()['Set-Cookie'] ?? []);

        return str_contains($cookies, config('session.cookie', 'laravel_session'))
            || str_contains($cookies, 'XSRF-TOKEN');
    }

    private function fetch(string $url, bool $quiet = false)
    {
        try {
            return Http::timeout(20)->withoutRedirecting()->get($url);
        } catch (\Throwable $e) {
            if (! $quiet) {
                $this->failed('Could not reach '.$url, $e->getMessage());
            }

            return null;
        }
    }
}
