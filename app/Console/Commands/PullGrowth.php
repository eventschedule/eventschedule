<?php

namespace App\Console\Commands;

use App\Http\Controllers\GrowthDataController;
use App\Services\GrowthSummary;
use App\Utils\AdminDateRange;
use Illuminate\Console\Command;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/**
 * Download the growth payload from a hosted install onto this machine, for analysis.
 *
 * Run on a DEV machine, not on the server: it calls GET /api/internal/growth with the bearer token
 * from this machine's .env (GROWTH_DATA_TOKEN, the same value the server has), saves the JSON under
 * storage/app/growth/ (gitignored), and prints the headline numbers against the previous pull.
 * docs/GROWTH_DATA.md says what every field means; the growth-review skill says how to analyse it.
 *
 * Never scheduled, so it belongs in neither AppController::translateData() nor routes/console.php.
 */
class PullGrowth extends Command
{
    protected $signature = 'app:pull-growth
                            {--range=last_30_days : The funnel window: last_7_days, last_30_days, last_90_days or all_time}
                            {--url= : Base URL of the install (default GROWTH_DATA_URL, else https://eventschedule.com)}
                            {--dir= : Where pulls are kept (default storage/app/growth)}
                            {--local : Re-print the summary of the latest pull on disk instead of downloading}';

    protected $description = 'Download the pseudonymous growth payload for analysis and summarise it';

    public function handle(): int
    {
        $dir = rtrim((string) ($this->option('dir') ?: storage_path('app/growth')), '/');
        File::ensureDirectoryExists($dir);

        if ($this->option('local')) {
            return $this->summariseLatest($dir);
        }

        $token = (string) config('app.growth_data_token');
        if (strlen($token) < GrowthDataController::MIN_TOKEN_LENGTH) {
            $this->error('GROWTH_DATA_TOKEN is missing or shorter than '.GrowthDataController::MIN_TOKEN_LENGTH.' characters.');
            $this->line('Generate one with `openssl rand -hex 32`, put it in this machine\'s .env as GROWTH_DATA_TOKEN,');
            $this->line('and set the same value on the server (DigitalOcean: app-level env var, encrypted), then deploy.');

            return self::FAILURE;
        }

        $range = (string) $this->option('range');
        if (! in_array($range, AdminDateRange::RANGES, true)) {
            $this->error('--range must be one of: '.implode(', ', AdminDateRange::RANGES));

            return self::FAILURE;
        }

        $base = rtrim((string) ($this->option('url') ?: config('app.growth_data_url')), '/');
        if ($problem = $this->urlProblem($base)) {
            $this->error("Refusing {$base}: {$problem}");

            return self::FAILURE;
        }

        $this->line("Pulling {$base}/api/internal/growth ({$range})...");

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->withUserAgent('EventSchedule-PullGrowth/1 (+php artisan app:pull-growth)')
                ->timeout(180)
                // A redirect is always a wrong --url (http to https, www to apex), and Guzzle drops
                // the Authorization header when one crosses origins - so following it would turn a
                // fixable URL into a baffling "token mismatch".
                ->withOptions(['allow_redirects' => false])
                ->get($base.'/api/internal/growth', ['range' => $range]);
        } catch (ConnectionException $e) {
            $this->error('Could not connect to '.$base.'.');

            return self::FAILURE;
        }

        if ($problem = $this->describeFailure($response)) {
            $this->error($problem);

            return self::FAILURE;
        }

        $data = $response->json();
        if (! is_array($data) || ! isset($data['meta']['schema_version'])) {
            $this->error('The response was not a growth payload (no meta.schema_version). Nothing was saved.');

            return self::FAILURE;
        }

        // Which window was asked for, so a later pull can be set against one over the same window.
        // meta.range alone cannot say: all_time's length changes every day.
        $data['meta']['pulled_range'] = $range;

        $previous = $this->previousFor($data, $this->pulls($dir));
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR);

        $file = $dir.'/growth-'.now()->format('Y-m-d-His').'.json';
        File::put($file, $json);
        // Atomic, so an interrupted write can never leave a half-written latest.json behind.
        File::put($dir.'/latest.json.tmp', $json);
        File::move($dir.'/latest.json.tmp', $dir.'/latest.json');

        $this->info(sprintf('Saved %s (%s KB) and %s/latest.json', $file, number_format(strlen($json) / 1024, 1), $dir));
        $this->printSummary($data, $previous);

        return self::SUCCESS;
    }

    private function summariseLatest(string $dir): int
    {
        $pulls = $this->pulls($dir);
        if ($pulls === []) {
            $this->error("No pulls in {$dir} yet. Run php artisan app:pull-growth first.");

            return self::FAILURE;
        }

        // The newest file that is actually a payload: a truncated or hand-edited one is skipped
        // rather than crashing the summary.
        while ($pulls !== []) {
            $current = $this->read(array_pop($pulls));
            if ($current !== null) {
                $this->printSummary($current, $this->previousFor($current, $pulls));

                return self::SUCCESS;
            }
        }

        $this->error("No readable pull in {$dir}. Run php artisan app:pull-growth.");

        return self::FAILURE;
    }

    /**
     * Name the failure from the status and headers alone. The body is never printed: a 5xx page
     * could carry anything, and a Cloudflare challenge is a wall of HTML.
     */
    private function describeFailure(Response $response): ?string
    {
        $status = $response->status();
        $type = (string) $response->header('Content-Type');

        return match (true) {
            $response->redirect() => "Redirected ({$status}) to ".($response->header('Location') ?: 'somewhere else')
                .'. Fix --url / GROWTH_DATA_URL to the final address.',
            $response->header('cf-mitigated') !== '' || ($status === 403 && str_contains($type, 'text/html')) => 'Blocked by a Cloudflare challenge. Check Security > Events, or add a WAF skip rule for /api/internal/growth.',
            $status === 401 => 'Token mismatch: GROWTH_DATA_TOKEN here differs from the server\'s (or a proxy stripped the Authorization header).',
            $status === 404 => 'Not found: the endpoint is disabled (no GROWTH_DATA_TOKEN on the server, or one under '
                .GrowthDataController::MIN_TOKEN_LENGTH.' characters), not deployed yet, or this is not a hosted install.',
            $status === 429 => 'Rate limited or a pull is already running. Retry in '.($response->header('Retry-After') ?: '60').' seconds.',
            $response->successful() && ! str_contains($type, 'json') => "Unexpected {$type} response, not JSON.",
            ! $response->successful() => "The server answered {$status}.",
            default => null,
        };
    }

    private function printSummary(array $current, ?array $previous): void
    {
        $meta = $current['meta'];
        $this->newLine();
        $this->line(sprintf('Schema %s, %s, generated %s', $meta['schema_version'], $meta['app_version'] ?? '?', $meta['generated_at'] ?? '?'));

        if ($previous) {
            $this->line('Compared with the pull generated '.($previous['meta']['generated_at'] ?? '?')
                .(($previous['meta']['schema_version'] ?? null) !== $meta['schema_version']
                    ? ' (schema '.($previous['meta']['schema_version'] ?? '?').': definitions differ, read the notes)'
                    : ''));
            if ($this->windowOf($previous) !== $this->windowOf($current)) {
                $this->line('<comment>Its funnel window differs</comment> ('.($this->windowOf($previous) ?? '?').' vs '
                    .($this->windowOf($current) ?? '?').'): the "in range" rows do not compare.');
            }
        }

        $this->table(['Metric', 'Now', 'Previous pull', 'Change'], GrowthSummary::compare($current, $previous));

        if ($hero = GrowthSummary::heroTest($current)) {
            $this->line('Headline test. '.$hero['status']);
            $this->table(['Variant', 'Share now', 'Visitors', 'Clicks', 'Signups', 'Chance best (signups)'], $hero['rows']);
        }

        // Every note is "new" against nothing, and twenty of them would bury the table.
        if ($previous === null) {
            $this->line(sprintf('<comment>%d caveats travel with this data</comment> in meta.notes - read them before drawing conclusions.',
                count($current['meta']['notes'] ?? [])));
        } else {
            foreach (GrowthSummary::newNotes($current, $previous) as $note) {
                $this->line('<comment>New note:</comment> '.$note);
            }
        }

        $this->line('Field definitions and caveats: docs/GROWTH_DATA.md');
    }

    /**
     * The newest earlier pull that compares like with like: the same schema AND the same funnel
     * window, else the same schema, else anything. The funnel rows are range-scoped, so a
     * last_30_days pull set against an all_time one reads as signups collapsing when only the
     * window changed.
     */
    private function previousFor(array $current, array $earlier): ?array
    {
        $sameSchema = null;
        $any = null;
        foreach (array_reverse($earlier) as $path) {
            $candidate = $this->read($path);
            if ($candidate === null) {
                continue;
            }
            if (($candidate['meta']['schema_version'] ?? null) === $current['meta']['schema_version']) {
                if ($this->windowOf($candidate) === $this->windowOf($current)) {
                    return $candidate;
                }
                $sameSchema ??= $candidate;
            }
            $any ??= $candidate;
        }

        return $sameSchema ?? $any;
    }

    /**
     * Which funnel window a pull covers: the --range it was pulled with (meta.pulled_range), or for
     * a pull made before that was recorded, all_time when it starts at the all-time origin and its
     * length in days otherwise.
     */
    private function windowOf(array $pull): ?string
    {
        if (isset($pull['meta']['pulled_range'])) {
            return (string) $pull['meta']['pulled_range'];
        }

        $start = (string) ($pull['meta']['range']['start'] ?? '');
        $end = (string) ($pull['meta']['range']['end'] ?? '');
        if ($start === '' || $end === '') {
            return null;
        }

        return $start === '2020-01-01' ? 'all_time' : 'last_'.(int) round((strtotime($end) - strtotime($start)) / 86400).'_days';
    }

    /** Timestamped pulls, oldest first. Their names sort chronologically. @return list<string> */
    private function pulls(string $dir): array
    {
        $files = glob($dir.'/growth-*.json') ?: [];
        sort($files);

        return $files;
    }

    private function read(string $path): ?array
    {
        $data = json_decode((string) @file_get_contents($path), true);

        // A payload, or nothing: everything downstream indexes meta.schema_version.
        return is_array($data) && isset($data['meta']['schema_version']) ? $data : null;
    }

    /**
     * Why the token must not go to this URL, or null when it may.
     *
     * The token is only ever sent to GROWTH_DATA_URL's own host or to a local install. --url exists
     * for local testing, and it is the one argument that decides where a production secret goes: an
     * agent running this command could be talked into pointing it elsewhere by any text it reads -
     * including values in a pull - and .claude/settings.json auto-approves the command. Pointing at
     * another host takes an edit to .env, which is a person's decision.
     */
    private function urlProblem(string $url): ?string
    {
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = strtolower($parts['host'] ?? '');
        $local = in_array($host, ['localhost', '127.0.0.1'], true) || str_ends_with($host, '.test');
        $configured = strtolower((string) parse_url((string) config('app.growth_data_url'), PHP_URL_HOST));

        return match (true) {
            $host === '' => 'not a URL.',
            ! $local && $host !== $configured => "the token is only sent to GROWTH_DATA_URL's host ({$configured}) or a local install. To pull from another host, set GROWTH_DATA_URL in .env.",
            $scheme !== 'https' && ! ($scheme === 'http' && $local) => 'the token would travel in cleartext. Use https (plain http only for localhost or *.test).',
            default => null,
        };
    }
}
