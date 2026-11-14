<?php

namespace Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\Feature\Concerns\ForcesEnvironment;
use Tests\TestCase;

/**
 * Event Schedule's marketing pages (home, pricing, the comparisons, the docs, the blog) are served
 * by eventschedule.com alone. A selfhosted SaaS once set IS_NEXUS=true and served them on its own
 * domain, selling its service with our pages (GitHub #136), so App\Support\Nexus honours the flag
 * only where APP_URL is eventschedule.com or a development host.
 *
 * Only route tables are read here, by absolute URL: APP_URL moves in these tests, and a relative
 * request would reach the host the app was first booted with (CLAUDE.md, pinAppUrl()).
 */
class MarketingPagesNexusOnlyTest extends TestCase
{
    use ForcesEnvironment;

    public function test_a_selfhosted_saas_that_sets_the_flag_gets_no_marketing_pages(): void
    {
        $this->bootAs('https://tickets.example.co.za');

        $this->assertTrue(config('app.hosted'));
        $this->assertFalse(config('app.is_nexus'));
        $this->assertNull(config('app.sentry_browser_dsn'), 'a SaaS reported its visitors\' errors to our Sentry');

        foreach (['marketing.index', 'marketing.pricing', 'marketing.features', 'blog.index'] as $name) {
            $this->assertFalse(Route::has($name), "{$name} is registered on a selfhosted SaaS");
        }

        foreach (['/', '/pricing', '/features'] as $path) {
            $route = app('router')->getRoutes()->match(Request::create('https://tickets.example.co.za'.$path));
            $this->assertStringStartsNotWith('marketing.', (string) $route->getName(), "{$path} served a marketing page");
        }
    }

    public function test_eventschedule_com_still_serves_them(): void
    {
        $this->bootAs('https://eventschedule.com');

        $this->assertTrue(config('app.is_nexus'));

        $route = app('router')->getRoutes()->match(Request::create('https://eventschedule.com/pricing'));
        $this->assertSame('marketing.pricing', $route->getName());
    }

    private function bootAs(string $appUrl): void
    {
        $this->forceEnv('IS_HOSTED', 'true');
        $this->forceEnv('IS_NEXUS', 'true');
        // Outside the test env the marketing routes sit on Route::domain(_base_domain()), as in
        // production; with APP_TESTING=true they answer on any host and prove nothing about it.
        $this->forceEnv('APP_TESTING', 'false');
        $this->forceEnv('APP_URL', $appUrl);
        $this->forceEnv('SENTRY_BROWSER_DSN', '');
        $this->forceEnv('REPORT_ERRORS', 'false');
        $this->refreshApplication();
    }
}
