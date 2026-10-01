<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * app:pull-growth, the dev-machine half of the growth endpoint. Driven against Http::fake() and a
 * throwaway --dir, never the real storage/app/growth, which holds the operator's actual pulls.
 */
class PullGrowthTest extends TestCase
{
    private const TOKEN = '0123456789abcdef0123456789abcdef0123456789abcdef0123456789abcdef';

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir().'/pull-growth-test-'.Str::random(8);
        config(['app.growth_data_token' => self::TOKEN, 'app.growth_data_url' => 'https://eventschedule.com']);
        Http::preventStrayRequests();
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);
        parent::tearDown();
    }

    private function payload(array $meta = []): array
    {
        return [
            'meta' => array_merge([
                'schema_version' => 8,
                'app_version' => 'v1.0.140',
                'generated_at' => '2026-10-01T10:00:00+00:00',
                'partial_month' => ['month' => '2026-10', 'days_elapsed' => 1, 'days_in_month' => 31],
                'notes' => ['a note'],
            ], $meta),
            'monetization' => ['mrr' => 62.5, 'billing_subscriptions' => 9],
            'signups' => ['columns' => [], 'rows' => []],
            'schedules' => ['columns' => [], 'rows' => []],
        ];
    }

    private function pullCommand(array $options = []): array
    {
        $code = Artisan::call('app:pull-growth', array_merge(['--dir' => $this->dir], $options));

        return [$code, Artisan::output()];
    }

    private function savedFiles(): array
    {
        return File::isDirectory($this->dir) ? array_map('basename', File::files($this->dir)) : [];
    }

    public function test_a_pull_saves_the_payload_and_prints_the_summary(): void
    {
        Http::fake(['eventschedule.com/api/internal/growth*' => Http::response($this->payload())]);

        [$code, $output] = $this->pullCommand(['--range' => 'last_90_days']);

        $this->assertSame(0, $code, $output);
        Http::assertSent(fn ($request) => $request->hasHeader('Authorization', 'Bearer '.self::TOKEN)
            && $request->url() === 'https://eventschedule.com/api/internal/growth?range=last_90_days');

        $files = $this->savedFiles();
        $this->assertContains('latest.json', $files);
        $this->assertCount(1, preg_grep('/^growth-\d{4}-\d{2}-\d{2}-\d{6}\.json$/', $files));
        $this->assertNotContains('latest.json.tmp', $files);
        $this->assertSame(8, json_decode(File::get($this->dir.'/latest.json'), true)['meta']['schema_version']);

        $this->assertStringContainsString('MRR', $output);
        $this->assertStringContainsString('62.50', $output);
        $this->assertStringContainsString('docs/GROWTH_DATA.md', $output);
    }

    public function test_the_summary_is_compared_with_the_previous_pull(): void
    {
        File::ensureDirectoryExists($this->dir);
        $older = $this->payload(['generated_at' => '2026-09-28T10:00:00+00:00', 'notes' => []]);
        $older['monetization']['mrr'] = 57.5;
        File::put($this->dir.'/growth-2026-09-28-100000.json', json_encode($older));

        Http::fake(['*' => Http::response($this->payload())]);

        [$code, $output] = $this->pullCommand();

        $this->assertSame(0, $code, $output);
        $this->assertStringContainsString('57.50', $output);
        $this->assertStringContainsString('+5.00', $output);
        $this->assertStringContainsString('New note: a note', $output);
    }

    /** @return array<string, array{0: \Closure, 1: string}> */
    public static function failures(): array
    {
        return [
            'token mismatch' => [fn () => Http::response(['error' => 'Unauthorized'], 401), 'Token mismatch'],
            'disabled' => [fn () => Http::response(['error' => 'Not found'], 404), 'Not found'],
            'busy' => [fn () => Http::response(['error' => 'busy'], 429, ['Retry-After' => '30']), 'Retry in 30 seconds'],
            // Guzzle would drop the Authorization header on a cross-origin redirect, turning a wrong
            // URL into a baffling 401, so redirects are not followed - they are named.
            'redirect' => [fn () => Http::response('', 301, ['Location' => 'https://www.eventschedule.com/api/internal/growth']), 'Redirected (301)'],
            'cloudflare challenge' => [fn () => Http::response('<html>Just a moment...</html>', 403, ['Content-Type' => 'text/html', 'cf-mitigated' => 'challenge']), 'Cloudflare challenge'],
            'server error' => [fn () => Http::response('<html>boom</html>', 502, ['Content-Type' => 'text/html']), 'answered 502'],
            'not json' => [fn () => Http::response('<html>hello</html>', 200, ['Content-Type' => 'text/html']), 'not JSON'],
            'not a payload' => [fn () => Http::response(['hello' => 'world']), 'not a growth payload'],
        ];
    }

    #[DataProvider('failures')]
    public function test_a_failed_pull_names_the_problem_and_writes_nothing(\Closure $response, string $message): void
    {
        Http::fake(['*' => $response()]);

        [$code, $output] = $this->pullCommand();

        $this->assertSame(1, $code);
        $this->assertStringContainsString($message, $output);
        $this->assertSame([], $this->savedFiles(), 'a failed pull must never touch the saved data');
        $this->assertStringNotContainsString('<html>', $output, 'the body is never printed');
    }

    public function test_it_will_not_run_without_a_long_enough_token(): void
    {
        Http::fake();

        foreach (['', 'too-short'] as $token) {
            config(['app.growth_data_token' => $token]);
            [$code, $output] = $this->pullCommand();

            $this->assertSame(1, $code);
            $this->assertStringContainsString('openssl rand -hex 32', $output);
        }

        Http::assertNothingSent();
    }

    /** The token would travel in cleartext. Plain http is only for a local install. */
    public function test_it_refuses_plain_http_to_a_remote_host(): void
    {
        Http::fake(['*' => Http::response($this->payload())]);

        [$code, $output] = $this->pullCommand(['--url' => 'http://eventschedule.com']);
        $this->assertSame(1, $code);
        $this->assertStringContainsString('cleartext', $output);
        Http::assertNothingSent();

        [$code] = $this->pullCommand(['--url' => 'http://eventschedule.test/']);
        $this->assertSame(0, $code);
        Http::assertSent(fn ($request) => str_starts_with($request->url(), 'http://eventschedule.test/api/internal/growth'));
    }

    public function test_an_unknown_range_is_refused_before_any_request(): void
    {
        Http::fake();

        [$code, $output] = $this->pullCommand(['--range' => 'last_year']);

        $this->assertSame(1, $code);
        $this->assertStringContainsString('last_30_days', $output);
        Http::assertNothingSent();
    }

    public function test_local_resummarises_the_latest_pull_without_a_request(): void
    {
        Http::fake();
        File::ensureDirectoryExists($this->dir);
        File::put($this->dir.'/growth-2026-10-01-100000.json', json_encode($this->payload()));

        [$code, $output] = $this->pullCommand(['--local' => true]);

        $this->assertSame(0, $code, $output);
        $this->assertStringContainsString('62.50', $output);
        Http::assertNothingSent();
    }
}
