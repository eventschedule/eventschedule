<?php

namespace App\Console\Commands;

use App\Services\SearchConsole;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\File;

/**
 * Pull Google Search Console's numbers for the site onto this machine, for analysis: what used to
 * be a CSV export from the Search Console page (storage/app/growth/search-console/).
 *
 * Run on a DEV machine, like app:pull-growth: it signs in with the service account in this
 * machine's .env (SEARCH_CONSOLE_CREDENTIALS), saves the pull as JSON under
 * storage/app/growth/search-console/ (gitignored, never committed: the repository is public), and
 * prints the last $days days against the $days before them, by page and by query, with the
 * sitemaps' status. --inspect asks Google's URL Inspection about one or more pages: whether they
 * are indexed, when they were last crawled, and which canonical Google chose.
 *
 * Search Analytics lags about three days, so the window ends three days ago.
 *
 * Never scheduled, so it belongs in neither AppController::translateData() nor routes/console.php.
 */
class PullSearchConsole extends Command
{
    protected $signature = 'app:pull-search-console
                            {--days=28 : The window, compared with the same number of days before it}
                            {--inspect=* : A page to look up in Google\'s index (a full URL or a path), repeatable}
                            {--top=15 : How many rows each table shows}
                            {--dir= : Where pulls are kept (default storage/app/growth/search-console)}';

    protected $description = 'Pull Search Console clicks, impressions and index status for analysis';

    public function handle(SearchConsole $console): int
    {
        if (! $console->configured()) {
            $this->error('SEARCH_CONSOLE_CREDENTIALS is not set, or is not a service account key.');
            $this->line('Create a service account in Google Cloud (enable the Search Console API), download its JSON key,');
            $this->line('add the account\'s email as a user on the property in Search Console (Restricted is enough),');
            $this->line('and put the key in this machine\'s .env as SEARCH_CONSOLE_CREDENTIALS (raw JSON or base64).');

            return self::FAILURE;
        }

        $days = max(1, min(240, (int) $this->option('days')));
        $top = max(1, (int) $this->option('top'));
        $to = CarbonImmutable::today()->subDays(3);
        $from = $to->subDays($days - 1);
        $prevTo = $from->subDay();
        $prevFrom = $prevTo->subDays($days - 1);

        $this->line("Search Console {$console->property()}: {$from->toDateString()} to {$to->toDateString()}, against the {$days} days before.");

        try {
            $pull = [
                'property' => $console->property(),
                'pulled_at' => now()->toIso8601String(),
                'window' => [$from->toDateString(), $to->toDateString()],
                'previous_window' => [$prevFrom->toDateString(), $prevTo->toDateString()],
                'totals' => $this->totals($console->query([], $from->toDateString(), $to->toDateString(), 1)),
                'previous_totals' => $this->totals($console->query([], $prevFrom->toDateString(), $prevTo->toDateString(), 1)),
                'pages' => $console->query(['page'], $from->toDateString(), $to->toDateString(), 5000),
                'previous_pages' => $console->query(['page'], $prevFrom->toDateString(), $prevTo->toDateString(), 5000),
                'queries' => $console->query(['query'], $from->toDateString(), $to->toDateString(), 1000),
                'sitemaps' => $console->sitemaps(),
                'inspections' => [],
            ];

            foreach ((array) $this->option('inspect') as $url) {
                $url = $this->absolute((string) $url);
                $pull['inspections'][$url] = $console->inspect($url);
            }
        } catch (RequestException $e) {
            $this->error('Google answered '.$e->response->status().': '.($e->response->json('error.message') ?? 'no message'));
            if ($e->response->status() === 403) {
                $this->line('The service account is probably not a user on '.$console->property().' yet (Search Console: Settings, Users and permissions).');
            }

            return self::FAILURE;
        }

        $dir = rtrim((string) ($this->option('dir') ?: storage_path('app/growth/search-console')), '/');
        File::ensureDirectoryExists($dir);
        $file = $dir.'/sc-'.now()->format('Y-m-d-His').'.json';
        File::put($file, json_encode($pull, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        File::put($dir.'/latest.json', json_encode($pull, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->line("Saved {$file}");

        $this->printSummary($pull, $top);

        return self::SUCCESS;
    }

    /** @return array{clicks: int, impressions: int, ctr: float, position: float} */
    private function totals(array $rows): array
    {
        $row = $rows[0] ?? ['clicks' => 0, 'impressions' => 0, 'ctr' => 0.0, 'position' => 0.0];

        return ['clicks' => $row['clicks'], 'impressions' => $row['impressions'], 'ctr' => $row['ctr'], 'position' => $row['position']];
    }

    private function absolute(string $url): string
    {
        if (preg_match('#^https?://#i', $url)) {
            return $url;
        }

        $property = (string) config('services.search_console.property');
        $host = str_starts_with($property, 'sc-domain:') ? 'https://'.substr($property, 10) : rtrim($property, '/');

        return $host.'/'.ltrim($url, '/');
    }

    private function printSummary(array $pull, int $top): void
    {
        $now = $pull['totals'];
        $was = $pull['previous_totals'];
        $change = fn ($a, $b) => $b ? sprintf('%+.0f%%', ($a - $b) / $b * 100) : '-';

        $this->table(['', 'Now', 'Before', 'Change'], [
            ['Clicks', number_format($now['clicks']), number_format($was['clicks']), $change($now['clicks'], $was['clicks'])],
            ['Impressions', number_format($now['impressions']), number_format($was['impressions']), $change($now['impressions'], $was['impressions'])],
            ['CTR', sprintf('%.2f%%', $now['ctr'] * 100), sprintf('%.2f%%', $was['ctr'] * 100), ''],
            // Lower is better, so the change is said in places, not percent.
            ['Average position', sprintf('%.1f', $now['position']), sprintf('%.1f', $was['position']), $was['position'] ? sprintf('%+.1f', $now['position'] - $was['position']) : '-'],
        ]);

        $before = [];
        foreach ($pull['previous_pages'] as $row) {
            $before[$row['keys'][0]] = $row;
        }

        $pages = $pull['pages'];
        usort($pages, fn ($a, $b) => [$b['clicks'], $b['impressions']] <=> [$a['clicks'], $a['impressions']]);
        $this->line('Top pages by clicks');
        $this->table(['Page', 'Clicks', 'Impressions', 'Position', 'Impressions before'], array_map(fn ($r) => [
            $this->path($r['keys'][0]), $r['clicks'], $r['impressions'], sprintf('%.1f', $r['position']),
            $before[$r['keys'][0]]['impressions'] ?? 0,
        ], array_slice($pages, 0, $top)));

        // Movers by impressions, which move before clicks do and are what an indexing drop shows
        // up in first (the /luma-alternative fall of 2026-06 was impressions to zero).
        $movers = [];
        $seen = [];
        foreach ($pages as $r) {
            $seen[$r['keys'][0]] = true;
            $movers[] = [$r['keys'][0], $r['impressions'], $before[$r['keys'][0]]['impressions'] ?? 0];
        }
        foreach ($before as $page => $r) {
            if (! isset($seen[$page])) {
                $movers[] = [$page, 0, $r['impressions']];
            }
        }
        usort($movers, fn ($a, $b) => ($a[1] - $a[2]) <=> ($b[1] - $b[2]));
        $row = fn ($m) => [$this->path($m[0]), $m[2], $m[1], sprintf('%+d', $m[1] - $m[2])];

        $this->line('Biggest impression losses');
        $this->table(['Page', 'Before', 'Now', 'Change'], array_map($row, array_slice(array_filter($movers, fn ($m) => $m[1] < $m[2]), 0, $top)));
        $this->line('Biggest impression gains');
        $this->table(['Page', 'Before', 'Now', 'Change'], array_map($row, array_slice(array_reverse(array_filter($movers, fn ($m) => $m[1] > $m[2])), 0, $top)));

        $queries = $pull['queries'];
        usort($queries, fn ($a, $b) => [$b['clicks'], $b['impressions']] <=> [$a['clicks'], $a['impressions']]);
        $this->line('Top queries');
        $this->table(['Query', 'Clicks', 'Impressions', 'CTR', 'Position'], array_map(fn ($r) => [
            $r['keys'][0], $r['clicks'], $r['impressions'], sprintf('%.1f%%', $r['ctr'] * 100), sprintf('%.1f', $r['position']),
        ], array_slice($queries, 0, $top)));

        if ($pull['sitemaps']) {
            $this->line('Sitemaps');
            $this->table(['Sitemap', 'Errors', 'Warnings', 'Last read', 'Pending'], array_map(fn ($s) => [
                $s['path'], $s['errors'], $s['warnings'], $s['last_downloaded'] ?? '-', $s['pending'] ? 'yes' : '',
            ], $pull['sitemaps']));
        }

        foreach ($pull['inspections'] as $url => $i) {
            $this->line("Index status of {$url}");
            $this->table(['', ''], [
                ['Verdict', $i['verdict'] ?? '-'],
                ['Coverage', $i['coverage'] ?? '-'],
                ['Last crawl', $i['last_crawl'] ?? '-'],
                ['Fetch', $i['fetch'] ?? '-'],
                ['Google\'s canonical', $i['google_canonical'] ?? '-'],
                ['Declared canonical', $i['user_canonical'] ?? '-'],
            ]);
        }
    }

    /** A page on the site's own host reads as its path; a schedule's subdomain stays whole. */
    private function path(string $url): string
    {
        $property = (string) config('services.search_console.property');
        $host = str_starts_with($property, 'sc-domain:') ? substr($property, 10) : (string) parse_url($property, PHP_URL_HOST);

        foreach (['https://'.$host, 'https://www.'.$host] as $prefix) {
            if ($url === $prefix || str_starts_with($url, $prefix.'/')) {
                return substr($url, strlen($prefix)) ?: '/';
            }
        }

        return $url;
    }
}
