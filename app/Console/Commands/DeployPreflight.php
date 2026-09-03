<?php

namespace App\Console\Commands;

use App\Console\Concerns\ReportsChecks;
use App\Services\DigitalOceanService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

/**
 * Everything worth knowing BEFORE triggering a hosted deploy, in one read-only pass.
 *
 * Run it from a checkout, not from the container: half of it is questions about the working
 * tree, and the other half is questions about the live app spec, which is reachable from
 * anywhere with DO_API_TOKEN.
 *
 * The thing this exists to stop is a deploy that is fine in every way the operator thought to
 * check. Production config for the hosted install is the DigitalOcean app spec - there is no
 * .env on that container - so "is CACHE_STORE set", "is QUEUE_CONNECTION still database",
 * "which deployment does a rollback go back to" cannot be answered from this repo at all, and
 * were being answered by hand, by eye, from a runbook that named two variables that do not
 * exist.
 *
 * STRICTLY READ ONLY against DigitalOcean. See DigitalOceanService::getAppSnapshot().
 */
class DeployPreflight extends Command
{
    use ReportsChecks;

    protected $signature = 'deploy:preflight
        {--url=https://eventschedule.com : Origin to baseline}
        {--skip-remote : Skip the DigitalOcean and live-site checks, for offline use}';

    protected $description = 'Read-only pre-deploy checks: working tree, live app spec, and the production baseline.';

    /**
     * The four amounts that decide what the marketing site QUOTES. Named here because
     * docs/NEXUS_RELEASE.md named four variables that do not exist in this codebase
     * (STRIPE_PRO_MONTHLY_AMOUNT and friends), so an operator checking the spec by hand looked
     * for the wrong keys, found nothing, and concluded they were unset without having checked.
     */
    private const PRICE_AMOUNT_KEYS = [
        'STRIPE_PRICE_MONTHLY_AMOUNT',
        'STRIPE_PRICE_YEARLY_AMOUNT',
        'STRIPE_ENTERPRISE_PRICE_MONTHLY_AMOUNT',
        'STRIPE_ENTERPRISE_PRICE_YEARLY_AMOUNT',
    ];

    public function handle(): int
    {
        $this->line('');
        $this->line('  <options=bold>Deploy preflight</>  <fg=gray>read-only</>');

        $this->checkWorkingTree();

        if (! $this->option('skip-remote')) {
            $this->checkAppSpec();
            $this->checkProductionBaseline();
        }

        $this->printConsoleOnlyChecks();

        return $this->summarise();
    }

    private function checkWorkingTree(): void
    {
        $this->section('working tree');

        $head = $this->git('rev-parse --short HEAD');
        $branch = $this->git('rev-parse --abbrev-ref HEAD');
        $this->note('HEAD '.$head.' on '.$branch);

        $dirty = $this->git('status --porcelain');
        if ($dirty === '') {
            $this->passed('Working tree is clean');
        } else {
            $lines = explode("\n", $dirty);
            $untracked = array_values(array_filter($lines, fn ($l) => str_starts_with($l, '??')));

            $this->failed(
                'Working tree has '.count($lines).' uncommitted change'.(count($lines) === 1 ? '' : 's'),
                'the deploy ships origin/main, not your disk'
            );

            // Untracked paths get their own line because they are the ones `git commit -am`
            // silently leaves behind. An asset referenced by committed code deploys as a 404,
            // and nothing local ever notices, because locally the file is on disk.
            if ($untracked !== []) {
                foreach ($untracked as $line) {
                    $this->note('  untracked, needs an explicit git add: '.trim(substr($line, 2)));
                }
            }
        }

        $ahead = $this->git('rev-list --count origin/main..HEAD');
        $behind = $this->git('rev-list --count HEAD..origin/main');

        if ($ahead === '0' && $behind === '0') {
            $this->passed('HEAD matches origin/main');
        } elseif ($ahead !== '0') {
            $this->failed('HEAD is '.$ahead.' commit(s) ahead of origin/main', 'push before deploying');
        } else {
            $this->failed('HEAD is '.$behind.' commit(s) behind origin/main');
        }

        $this->checkSitemapManifestFreshness();
    }

    /**
     * config/sitemap_lastmod.php maps each marketing URL to the commit date of the view behind
     * it, and it is generated. Change a marketing view without regenerating it and that page
     * reports a <lastmod> older than its own content, which is worse than no lastmod at all.
     */
    private function checkSitemapManifestFreshness(): void
    {
        $manifestAt = $this->git('log -1 --format=%ct -- config/sitemap_lastmod.php');
        $viewsAt = $this->git('log -1 --format=%ct -- resources/views/marketing/');

        if ($manifestAt === '' || $viewsAt === '') {
            $this->warned('Could not compare the sitemap lastmod manifest against the marketing views');

            return;
        }

        if ((int) $manifestAt >= (int) $viewsAt) {
            $this->passed('Sitemap lastmod manifest is current');
        } else {
            $this->failed(
                'Sitemap lastmod manifest is older than the marketing views',
                'run: php artisan sitemap:lastmod && commit'
            );
        }
    }

    private function checkAppSpec(): void
    {
        $this->section('digitalocean app spec');

        try {
            $app = app(DigitalOceanService::class)->getAppSnapshot();
        } catch (\Throwable $e) {
            $this->failed('Could not read the app spec', $e->getMessage());

            return;
        }

        $spec = $app['spec'] ?? [];
        $env = $this->flattenEnv($spec);

        $this->note('app '.($app['id'] ?? '?').'   region '.($spec['region'] ?? '?'));
        $this->note('ROLLBACK TARGET: deployment '.($app['active_deployment']['id'] ?? 'unknown')
            .' ('.($app['active_deployment']['updated_at'] ?? '?').')');

        // Hard requirements.
        $this->expectEnv($env, 'QUEUE_CONNECTION', 'database');
        $this->expectEnv($env, 'APP_URL', rtrim($this->option('url'), '/'));
        $this->expectEnv($env, 'IS_HOSTED', 'true');
        $this->expectEnv($env, 'IS_NEXUS', 'true', 'the entire marketing edge cache is gated on this');

        // CACHE_STORE: a warn before the runbook's step 5, a pass after it, and a FAIL if
        // someone has saved it blank - which is not a fallback to file, it is
        // "Cache store [] is not defined" on the first cache read anywhere in the app.
        if (! array_key_exists('CACHE_STORE', $env)) {
            $this->warned('CACHE_STORE is unset', 'resolves to the per-container file driver; runbook step 5 sets it');
        } elseif (trim((string) $env['CACHE_STORE']) === '') {
            $this->failed('CACHE_STORE is set but EMPTY', 'this is a site-wide 500, not a fallback');
        } elseif ($env['CACHE_STORE'] === 'database') {
            $this->passed('CACHE_STORE=database');
        } else {
            $this->warned('CACHE_STORE='.$env['CACHE_STORE'], 'expected database on a multi-container install');
        }

        $this->checkInstanceCount($spec);
        $this->checkSchedulerRails($env);
        $this->checkPricing($env);
        $this->checkBackupStorage($env);
        $this->recordDomains($app);
    }

    /**
     * More than one web container while CACHE_STORE is the file driver means every scheduler
     * mutex and every named cross-rail lock serialises against nothing: two containers can
     * translate, charge installments and PUT the app spec concurrently.
     */
    private function checkInstanceCount(array $spec): void
    {
        foreach ($spec['services'] ?? [] as $service) {
            $count = $service['instance_count'] ?? 1;
            $name = $service['name'] ?? '?';

            $sharedCache = ($this->flattenEnv($spec)['CACHE_STORE'] ?? '') === 'database';

            if ($count > 1 && ! $sharedCache) {
                $this->failed('Service '.$name.' runs '.$count.' instances on a non-shared cache store',
                    'every lock in the app is per-container');
            } else {
                $this->passed('Service '.$name.' instance_count='.$count);
            }
        }
    }

    private function checkSchedulerRails(array $env): void
    {
        $workerRails = [];
        $expected = $env['SCHEDULER_EXPECTED_RAIL'] ?? null;

        if ($expected === null || trim((string) $expected) === '') {
            $this->warned('SCHEDULER_EXPECTED_RAIL is unset',
                'fine until a worker exists; after that a dead worker is invisible');

            return;
        }

        // Whatever the app expects has to be what some component actually announces itself as,
        // or the admin panel watches a rail nothing writes and reports a permanent stall.
        foreach (['workers', 'services', 'jobs'] as $kind) {
            foreach ($env['__components'][$kind] ?? [] as $name => $componentEnv) {
                $workerRails[$name] = $componentEnv['SCHEDULER_RAIL'] ?? ($env['SCHEDULER_RAIL'] ?? 'cron');
            }
        }

        if (in_array($expected, $workerRails, true)) {
            $this->passed('SCHEDULER_EXPECTED_RAIL='.$expected, 'announced by '.implode(', ', array_keys($workerRails, $expected)));
        } else {
            $this->failed('SCHEDULER_EXPECTED_RAIL='.$expected.' but no component announces that rail',
                'permanent false "scheduled tasks are not running"');
        }
    }

    private function checkPricing(array $env): void
    {
        $set = array_values(array_filter(self::PRICE_AMOUNT_KEYS, fn ($k) => array_key_exists($k, $env)));

        if ($set === []) {
            $this->passed('No plan-amount overrides on the spec',
                'prices come from the settings table, then config');
        } else {
            $this->note('Plan amount overrides on the spec: '.implode(', ', $set));
        }

        $legacy = array_values(array_filter(array_keys($env), fn ($k) => str_starts_with($k, 'STRIPE_LEGACY_')));

        if ($legacy === []) {
            $this->passed('No STRIPE_LEGACY_* on the spec', 'nothing to lose when the mechanism is removed');
        } else {
            $this->failed('STRIPE_LEGACY_* still set: '.implode(', ', $legacy),
                'this release deletes the code that reads them');
        }
    }

    private function checkBackupStorage(array $env): void
    {
        $driver = $env['BACKUP_DISK_DRIVER'] ?? null;

        if ($driver !== 's3') {
            $this->warned('BACKUP_DISK_DRIVER is '.($driver === null ? 'unset' : $driver),
                'exports land on one container\'s disk and the download 404s from another');

            return;
        }

        if (! array_key_exists('BACKUP_SPACES_BUCKET', $env) || trim((string) $env['BACKUP_SPACES_BUCKET']) === '') {
            $this->failed('BACKUP_DISK_DRIVER=s3 with no BACKUP_SPACES_BUCKET', 'every export will fail');

            return;
        }

        $this->passed('BACKUP_DISK_DRIVER=s3 with a bucket set');

        foreach (['BACKUP_SPACES_KEY', 'BACKUP_SPACES_SECRET'] as $key) {
            if (! array_key_exists($key, $env)) {
                $this->warned($key.' is unset',
                    'falls back to the DO_SPACES_* images credentials, which may not reach the backups bucket');
            }
        }
    }

    /**
     * Custom domains live only on the spec, and DigitalOceanService rebuilds the whole spec to
     * add or remove one. Writing them down before a deploy is the only cheap way back if a
     * concurrent sync ever drops one.
     */
    private function recordDomains(array $app): void
    {
        $domains = array_values(array_filter(array_map(
            fn ($d) => $d['spec']['domain'] ?? ($d['domain'] ?? null),
            $app['domains'] ?? []
        )));

        $path = storage_path('deploy');
        @mkdir($path, 0775, true);

        $payload = [
            'captured_at' => now()->toIso8601String(),
            'app_id' => $app['id'] ?? null,
            'rollback_deployment_id' => $app['active_deployment']['id'] ?? null,
            'domains' => $domains,
        ];

        $file = $path.'/preflight-'.now()->format('Y-m-d-His').'.json';
        file_put_contents($file, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->passed(count($domains).' domains recorded', str_replace(base_path().'/', '', $file));
    }

    private function checkProductionBaseline(): void
    {
        $this->section('production baseline');

        $url = rtrim($this->option('url'), '/');

        try {
            $response = Http::timeout(20)->withoutRedirecting()->get($url.'/pricing');
        } catch (\Throwable $e) {
            $this->failed('Could not reach '.$url.'/pricing', $e->getMessage());

            return;
        }

        $cookies = implode(' ', $response->headers()['Set-Cookie'] ?? []);
        $cacheControl = $response->header('Cache-Control');

        $this->note('/pricing -> '.$response->status().'   cache-control: '.($cacheControl ?: 'none'));

        // The Cloudflare bypass expression hardcodes this cookie name. It is derived from
        // APP_NAME unless SESSION_COOKIE is set, so it can drift without anyone touching the
        // rule - and every signed-in visitor would then be served the anonymous copy.
        if (str_contains($cookies, 'laravel_session')) {
            $this->passed('Session cookie is named laravel_session', 'matches the Cloudflare bypass rule');
        } elseif ($cookies === '') {
            $this->note('No session cookie on /pricing - already serving the edge-cacheable shape');
        } else {
            $this->failed('Session cookie is NOT named laravel_session',
                'the Cloudflare bypass expression will not match it');
        }
    }

    /**
     * Two questions this command cannot answer from outside: they need the production database.
     * Printed as copy-paste rather than silently skipped, because "not checked" and "checked and
     * fine" are the two states a runbook must never confuse.
     */
    private function printConsoleOnlyChecks(): void
    {
        $this->section('run these on the container console');

        $this->line(<<<'SQL'
  -- 1. Is any live subscriber on a Stripe price ID config no longer names? Each of these must
  --    appear in STRIPE_PRICE_MONTHLY / _YEARLY / STRIPE_ENTERPRISE_PRICE_MONTHLY / _YEARLY.
  --    An unlisted ID means that customer loses their tier while still being charged, and this
  --    release removes the STRIPE_LEGACY_* mechanism that used to absorb exactly that.
  SELECT DISTINCT stripe_price FROM subscriptions
  WHERE stripe_status IN ('active','trialing','past_due');

  -- 2. What the marketing site advertises, which beats config. Blank result = config decides.
  SELECT `key`, value FROM settings WHERE `key` LIKE 'plan_price_%';

  -- 3. Migration cost. Three migrations touch `events`; the third is the one that WRITES, and
  --    neither column it filters on is indexed, so it scans.
  SELECT COUNT(*) FROM events;
  SELECT COUNT(*) FROM federated_events;
  SELECT COUNT(*) FROM events WHERE coupon_discount IS NULL AND coupon_discount_type IS NOT NULL;
SQL);
    }

    /**
     * App-level env plus every component's own, with component scopes kept separately under
     * __components so a variable set at the wrong scope is still visible as such.
     *
     * @return array<string, mixed>
     */
    private function flattenEnv(array $spec): array
    {
        $flat = [];

        foreach ($spec['envs'] ?? [] as $env) {
            $flat[$env['key']] = $env['value'] ?? '';
        }

        $flat['__components'] = [];

        foreach (['services', 'workers', 'jobs'] as $kind) {
            foreach ($spec[$kind] ?? [] as $component) {
                $componentEnv = [];
                foreach ($component['envs'] ?? [] as $env) {
                    $componentEnv[$env['key']] = $env['value'] ?? '';
                    // A component-scoped value is what that component actually sees.
                    $flat[$env['key']] ??= $env['value'] ?? '';
                }
                $flat['__components'][$kind][$component['name'] ?? '?'] = $componentEnv;
            }
        }

        return $flat;
    }

    private function expectEnv(array $env, string $key, string $expected, ?string $why = null): void
    {
        $actual = $env[$key] ?? null;

        if ($actual === $expected) {
            $this->passed($key.'='.$expected);
        } elseif ($actual === null) {
            $this->failed($key.' is not set', $why ?? 'expected '.$expected);
        } else {
            $this->failed($key.'='.$actual, $why ?? 'expected '.$expected);
        }
    }

    private function git(string $args): string
    {
        return trim((string) shell_exec('git -C '.escapeshellarg(base_path()).' '.$args.' 2>/dev/null'));
    }
}
