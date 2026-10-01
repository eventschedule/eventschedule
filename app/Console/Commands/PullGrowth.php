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
        if (! $this->isAcceptableUrl($base)) {
            $this->error("Refusing {$base}: the token would travel in cleartext. Use https (plain http only for localhost or *.test).");

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

        $previous = $this->previousFor($data, $this->pulls($dir));
        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

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

        $current = $this->read(array_pop($pulls));
        $this->printSummary($current, $this->previousFor($current, $pulls));

        return self::SUCCESS;
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
        }

        $this->table(['Metric', 'Now', 'Previous pull', 'Change'], GrowthSummary::compare($current, $previous));

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
     * The newest earlier pull with the same schema version, else the newest earlier pull of any
     * version - so a comparison is like with like whenever one exists.
     */
    private function previousFor(array $current, array $earlier): ?array
    {
        $fallback = null;
        foreach (array_reverse($earlier) as $path) {
            $candidate = $this->read($path);
            if ($candidate === null) {
                continue;
            }
            if (($candidate['meta']['schema_version'] ?? null) === $current['meta']['schema_version']) {
                return $candidate;
            }
            $fallback ??= $candidate;
        }

        return $fallback;
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

        return is_array($data) && isset($data['meta']) ? $data : null;
    }

    private function isAcceptableUrl(string $url): bool
    {
        $parts = parse_url($url);
        $scheme = strtolower($parts['scheme'] ?? '');
        $host = strtolower($parts['host'] ?? '');

        if ($host === '') {
            return false;
        }

        return $scheme === 'https'
            || ($scheme === 'http' && (in_array($host, ['localhost', '127.0.0.1'], true) || str_ends_with($host, '.test')));
    }
}
