<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * app:pull-search-console and App\Services\SearchConsole: a service account signs its own JWT, asks
 * only Google, asks for final data, and the pull lands in the folder it was given.
 */
class SearchConsoleTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Cache::flush();

        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $pem);
        config([
            'services.search_console.credentials' => base64_encode((string) json_encode([
                'client_email' => 'reader@project.iam.gserviceaccount.com',
                'private_key' => $pem,
            ])),
            'services.search_console.property' => 'sc-domain:eventschedule.com',
        ]);

        $this->dir = sys_get_temp_dir().'/search-console-'.bin2hex(random_bytes(4));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);
        parent::tearDown();
    }

    private function fakeGoogle(): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'ya29.token', 'expires_in' => 3600]),
            'www.googleapis.com/webmasters/v3/sites/*/searchAnalytics/query' => function (Request $request) {
                $rows = match ($request['dimensions']) {
                    [] => [['clicks' => 40, 'impressions' => 2000, 'ctr' => 0.02, 'position' => 18.4]],
                    ['page'] => [
                        ['keys' => ['https://eventschedule.com/pricing'], 'clicks' => 30, 'impressions' => 900, 'ctr' => 0.033, 'position' => 9.1],
                        ['keys' => ['https://eventschedule.com/luma-alternative'], 'clicks' => 0, 'impressions' => 0, 'ctr' => 0, 'position' => 0],
                    ],
                    default => [['keys' => ['event schedule'], 'clicks' => 12, 'impressions' => 300, 'ctr' => 0.04, 'position' => 3.2]],
                };

                return Http::response(['rows' => $rows]);
            },
            'www.googleapis.com/webmasters/v3/sites/*/sitemaps' => Http::response(['sitemap' => [
                ['path' => 'https://eventschedule.com/sitemap.xml', 'errors' => 0, 'warnings' => 1, 'lastDownloaded' => '2026-10-30T10:00:00Z'],
            ]]),
            'searchconsole.googleapis.com/v1/urlInspection/index:inspect' => Http::response(['inspectionResult' => ['indexStatusResult' => [
                'verdict' => 'NEUTRAL',
                'coverageState' => 'Crawled - currently not indexed',
                'lastCrawlTime' => '2026-07-04T08:00:00Z',
                'googleCanonical' => 'https://eventschedule.com/luma-alternative',
            ]]]),
        ]);
    }

    public function test_it_pulls_the_window_against_the_one_before_and_saves_it(): void
    {
        $this->fakeGoogle();

        $this->artisan('app:pull-search-console', ['--dir' => $this->dir, '--inspect' => ['/luma-alternative']])
            ->expectsOutputToContain('Crawled - currently not indexed')
            ->assertExitCode(0);

        $pull = json_decode((string) File::get($this->dir.'/latest.json'), true);
        $this->assertSame('sc-domain:eventschedule.com', $pull['property']);
        $this->assertSame(40, $pull['totals']['clicks']);
        $this->assertSame('https://eventschedule.com/pricing', $pull['pages'][0]['keys'][0]);
        $this->assertSame('Crawled - currently not indexed', $pull['inspections']['https://eventschedule.com/luma-alternative']['coverage']);
        $this->assertCount(2, File::glob($this->dir.'/*.json'));

        // The window ends three days back, where the data is final, and the one before it abuts it.
        [$from, $to] = $pull['window'];
        $this->assertSame(now()->subDays(3)->toDateString(), $to);
        $this->assertSame(now()->subDays(3 + 27)->toDateString(), $from);
        $this->assertSame(now()->subDays(3 + 28)->toDateString(), $pull['previous_window'][1]);

        Http::assertSent(fn (Request $r) => str_contains($r->url(), '/searchAnalytics/query')
            && str_contains($r->url(), rawurlencode('sc-domain:eventschedule.com'))
            && $r['dataState'] === 'final'
            && $r->hasHeader('Authorization', 'Bearer ya29.token'));
    }

    /** The service account signs a JWT for the read-only scope, and the token is asked for once. */
    public function test_the_service_account_signs_in_read_only_and_once(): void
    {
        $this->fakeGoogle();

        $this->artisan('app:pull-search-console', ['--dir' => $this->dir])->assertExitCode(0);

        $tokens = Http::recorded(fn (Request $r) => str_contains($r->url(), 'oauth2.googleapis.com/token'));
        $this->assertCount(1, $tokens);
        [$request] = $tokens[0];
        $this->assertSame('urn:ietf:params:oauth:grant-type:jwt-bearer', $request['grant_type']);
        $claims = json_decode(base64_decode(strtr(explode('.', $request['assertion'])[1], '-_', '+/')), true);
        $this->assertSame('reader@project.iam.gserviceaccount.com', $claims['iss']);
        $this->assertSame('https://www.googleapis.com/auth/webmasters.readonly', $claims['scope']);
    }

    public function test_without_a_key_it_says_how_to_set_one_up_and_asks_nobody(): void
    {
        config(['services.search_console.credentials' => '']);

        $this->artisan('app:pull-search-console', ['--dir' => $this->dir])
            ->expectsOutputToContain('SEARCH_CONSOLE_CREDENTIALS')
            ->assertExitCode(1);

        Http::assertNothingSent();
        $this->assertDirectoryDoesNotExist($this->dir);
    }

    /** A 403 is the account not being a user on the property yet, and the command says so. */
    public function test_a_refusal_says_the_account_needs_adding_to_the_property(): void
    {
        Http::fake([
            'oauth2.googleapis.com/token' => Http::response(['access_token' => 'ya29.token']),
            '*' => Http::response(['error' => ['message' => 'User does not have sufficient permission']], 403),
        ]);

        $this->artisan('app:pull-search-console', ['--dir' => $this->dir])
            ->expectsOutputToContain('not a user on sc-domain:eventschedule.com')
            ->assertExitCode(1);
    }
}
