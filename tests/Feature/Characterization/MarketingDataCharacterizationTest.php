<?php

namespace Tests\Feature\Characterization;

use App\Http\Controllers\MarketingController;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Characterizes MarketingController's hardcoded data arrays ahead of the P2
 * extraction (REFACTOR_PLAN.md): golden-fixture tests pin the FULL return
 * value of getComparisonData()/getReplacementData(), and route smokes pin
 * every compare_* and replace_* page rendering with its competitor's name.
 *
 * Golden-file pattern: the fixture is written when absent (inspect + commit
 * it), asserted against byte-for-byte when present. After P2 moves the arrays
 * to app/Services/Marketing/, these tests must still pass unchanged.
 */
class MarketingDataCharacterizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // getReplacementData() embeds GitHubUtils::getStars(), which would
        // otherwise make a LIVE GitHub API call (array cache is empty per
        // process) and leak a varying star count into the golden fixture.
        // Seed the cached-failure marker so getStars() deterministically
        // returns null, and block any accidental outbound HTTP.
        cache()->put('github_stars', false, 300);
        \Illuminate\Support\Facades\Http::fake();
    }

    private const COMPARISON_KEYS = [
        'eventbrite', 'luma', 'ticket-tailor', 'google-calendar', 'meetup',
        'dice', 'brown-paper-tickets', 'splash', 'sched', 'whova',
        'accelevents', 'tito', 'addevent', 'pretix', 'humanitix', 'eventzilla',
    ];

    private const REPLACEMENT_KEYS = [
        'google-forms', 'mailchimp', 'canva', 'linktree', 'google-sheets',
        'calendly', 'surveymonkey', 'doodle', 'qr-code-generators',
        'squarespace', 'notion', 'trello',
    ];

    public function test_comparison_data_matches_golden_fixture(): void
    {
        $this->assertMatchesGoldenFixture('comparison_data.json', 'getComparisonData', self::COMPARISON_KEYS);
    }

    public function test_replacement_data_matches_golden_fixture(): void
    {
        $this->assertMatchesGoldenFixture('replacement_data.json', 'getReplacementData', self::REPLACEMENT_KEYS);
    }

    public function test_every_comparison_route_renders_with_its_competitor_name(): void
    {
        $data = $this->collectData('getComparisonData', self::COMPARISON_KEYS);

        $routes = $this->namedRoutesMatching('marketing.compare_');
        $this->assertCount(count(self::COMPARISON_KEYS), $routes);

        foreach (self::COMPARISON_KEYS as $key) {
            $competitorName = $data[$key]['competitor']['name']
                ?? $data[$key]['competitorName']
                ?? $data[$key]['name']
                ?? null;
            $this->assertNotNull($competitorName, "No competitor name found in data for [$key]");

            $response = $this->get('/'.$key.'-alternative');
            $response->assertOk();
            $response->assertSee($competitorName, false);
        }
    }

    public function test_every_replacement_route_renders_with_its_tool_name(): void
    {
        $data = $this->collectData('getReplacementData', self::REPLACEMENT_KEYS);

        $routes = $this->namedRoutesMatching('marketing.replace_');
        $this->assertCount(count(self::REPLACEMENT_KEYS), $routes);

        foreach ($routes as $route) {
            $response = $this->get('/'.$route->uri());
            $response->assertOk();
        }

        // Each tool's data must carry a display name the page renders.
        foreach (self::REPLACEMENT_KEYS as $key) {
            $this->assertNotEmpty($data[$key], "No replacement data for [$key]");
        }
    }

    private function assertMatchesGoldenFixture(string $fixture, string $method, array $keys): void
    {
        $json = json_encode(
            $this->collectData($method, $keys),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        $path = base_path('tests/fixtures/'.$fixture);

        if (! file_exists($path)) {
            if (! is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            file_put_contents($path, $json."\n");
            $this->markTestIncomplete("Golden fixture generated at tests/fixtures/{$fixture} - inspect and commit it, then re-run.");
        }

        $this->assertSame(
            file_get_contents($path),
            $json."\n",
            "{$method}() output no longer matches tests/fixtures/{$fixture}. ".
            'A P2 data move must be byte-identical; regenerate the fixture only for an intentional content change.'
        );
    }

    private function collectData(string $method, array $keys): array
    {
        $controller = app(MarketingController::class);
        $reflection = new \ReflectionMethod($controller, $method);
        $reflection->setAccessible(true);

        $data = [];
        foreach ($keys as $key) {
            $data[$key] = $reflection->invoke($controller, $key);
        }

        return $data;
    }

    private function namedRoutesMatching(string $prefix): array
    {
        return array_values(array_filter(
            Route::getRoutes()->getRoutes(),
            fn ($route) => str_starts_with((string) $route->getName(), $prefix)
        ));
    }
}
