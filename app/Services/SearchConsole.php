<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Read-only Google Search Console access for `php artisan app:pull-search-console`, which a
 * developer runs locally for analysis, as app:pull-growth is. Nothing in the app calls it.
 *
 * A service account signs in: it is added as a user (Restricted is enough) on the Search Console
 * property, and its JSON key goes in SEARCH_CONSOLE_CREDENTIALS, raw or base64-encoded. The JWT is
 * signed here with openssl, so no Google SDK is needed. Search Analytics data lags about three days.
 *
 * phpunit.xml pins SEARCH_CONSOLE_CREDENTIALS empty, so no test can sign in with a developer's key.
 */
class SearchConsole
{
    private const SCOPE = 'https://www.googleapis.com/auth/webmasters.readonly';

    private const API = 'https://www.googleapis.com/webmasters/v3/sites/';

    public function configured(): bool
    {
        return $this->credentials() !== null;
    }

    /** The property, as Search Console names it: `sc-domain:eventschedule.com` or a URL prefix. */
    public function property(): string
    {
        return (string) config('services.search_console.property');
    }

    /**
     * Search Analytics rows for one date range, grouped by $dimensions (page, query, country,
     * device, date). Only final data, so the last two or three days are not half counted.
     *
     * @param  list<string>  $dimensions
     * @return list<array{keys: list<string>, clicks: int, impressions: int, ctr: float, position: float}>
     */
    public function query(array $dimensions, string $from, string $to, int $limit = 1000): array
    {
        $rows = Http::withToken($this->token())->timeout(60)
            ->post(self::API.rawurlencode($this->property()).'/searchAnalytics/query', [
                'startDate' => $from,
                'endDate' => $to,
                'dimensions' => $dimensions,
                'rowLimit' => $limit,
                'dataState' => 'final',
            ])->throw()->json('rows') ?? [];

        return array_values(array_map(static fn (array $row): array => [
            'keys' => array_map('strval', (array) ($row['keys'] ?? [])),
            'clicks' => (int) ($row['clicks'] ?? 0),
            'impressions' => (int) ($row['impressions'] ?? 0),
            'ctr' => (float) ($row['ctr'] ?? 0),
            'position' => (float) ($row['position'] ?? 0),
        ], is_array($rows) ? $rows : []));
    }

    /**
     * Every submitted sitemap and what Google last made of it.
     *
     * @return list<array{path: string, errors: int, warnings: int, last_downloaded: ?string, pending: bool}>
     */
    public function sitemaps(): array
    {
        $items = Http::withToken($this->token())->timeout(30)
            ->get(self::API.rawurlencode($this->property()).'/sitemaps')->throw()->json('sitemap') ?? [];

        return array_values(array_map(static fn (array $s): array => [
            'path' => (string) ($s['path'] ?? ''),
            'errors' => (int) ($s['errors'] ?? 0),
            'warnings' => (int) ($s['warnings'] ?? 0),
            'last_downloaded' => isset($s['lastDownloaded']) ? (string) $s['lastDownloaded'] : null,
            'pending' => (bool) ($s['isPending'] ?? false),
        ], is_array($items) ? $items : []));
    }

    /**
     * What Google's index says about one URL: the URL Inspection tool, as an API. Google allows
     * about 2,000 of these a day per property.
     *
     * @return array{verdict: ?string, coverage: ?string, indexing: ?string, fetch: ?string, last_crawl: ?string, google_canonical: ?string, user_canonical: ?string}
     */
    public function inspect(string $url): array
    {
        $result = Http::withToken($this->token())->timeout(60)
            ->post('https://searchconsole.googleapis.com/v1/urlInspection/index:inspect', [
                'inspectionUrl' => $url,
                'siteUrl' => $this->property(),
            ])->throw()->json('inspectionResult.indexStatusResult') ?? [];

        return [
            'verdict' => $result['verdict'] ?? null,
            'coverage' => $result['coverageState'] ?? null,
            'indexing' => $result['indexingState'] ?? null,
            'fetch' => $result['pageFetchState'] ?? null,
            'last_crawl' => $result['lastCrawlTime'] ?? null,
            'google_canonical' => $result['googleCanonical'] ?? null,
            'user_canonical' => $result['userCanonical'] ?? null,
        ];
    }

    private function token(): string
    {
        $credentials = $this->credentials()
            ?? throw new RuntimeException('Set SEARCH_CONSOLE_CREDENTIALS to the service account\'s JSON key (raw or base64).');

        // Keyed by the account, so a key swapped in .env is not answered with the old one's token.
        return Cache::remember('search-console:token:'.sha1($credentials['client_email']), 3000, function () use ($credentials): string {
            $now = time();
            $segment = static fn (array $data): string => rtrim(strtr(base64_encode((string) json_encode($data)), '+/', '-_'), '=');
            $unsigned = $segment(['alg' => 'RS256', 'typ' => 'JWT']).'.'.$segment([
                'iss' => $credentials['client_email'],
                'scope' => self::SCOPE,
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ]);

            if (! openssl_sign($unsigned, $signature, $credentials['private_key'], OPENSSL_ALGO_SHA256)) {
                throw new RuntimeException('The Search Console private key could not sign the request.');
            }

            return (string) Http::asForm()->timeout(15)->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $unsigned.'.'.rtrim(strtr(base64_encode($signature), '+/', '-_'), '='),
            ])->throw()->json('access_token');
        });
    }

    /** @return array{client_email: string, private_key: string}|null */
    private function credentials(): ?array
    {
        $raw = trim((string) config('services.search_console.credentials'));
        if ($raw === '') {
            return null;
        }

        $json = json_decode(str_starts_with($raw, '{') ? $raw : (string) base64_decode($raw, true), true);

        return is_array($json) && isset($json['client_email'], $json['private_key'])
            ? ['client_email' => (string) $json['client_email'], 'private_key' => (string) $json['private_key']]
            : null;
    }
}
