<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * robots.txt is per host.
 *
 * Every host used to get the apex's body. On a tenant host that blocked nothing it serves - there
 * is no /admin or /login there - while its bare prefixes (/events, /checkout) matched the
 * schedule's own event slugs. And on selfhost, where every schedule lives at /{subdomain}, the same
 * bare prefixes blocked schedules such as /eventsnyc or /login-lounge outright. The route also ran
 * the session middleware, so every fetch set a cookie the CDN will not cache past.
 */
class RobotsTxtTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const APP_PATHS = [
        'login', 'sign_up', 'reset-password', 'update-password', 'confirm-password', 'verify-email',
        'two-factor-challenge', 'events', 'settings', 'checkout', 'admin',
    ];

    private function robots(string $url): TestResponse
    {
        $response = $this->get($url)->assertOk();

        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
        $response->assertHeader('Cache-Control', 'max-age=3600, public');
        $this->assertEmpty($response->headers->getCookies(), $url.' set a cookie');
        $this->assertNull($response->headers->get('Set-Cookie'), $url.' set a cookie');

        return $response;
    }

    /** The Disallow values of a body, in order. */
    private function disallows(string $body): array
    {
        preg_match_all('/^Disallow: (.*)$/m', $body, $m);

        return $m[1];
    }

    private function assertAppRules(string $body, string $host): void
    {
        $disallows = $this->disallows($body);

        foreach (self::APP_PATHS as $path) {
            foreach (["/{$path}$", "/{$path}/", "/{$path}?"] as $rule) {
                $this->assertContains($rule, $disallows, $host);
            }

            // Never the bare prefix, which also blocks /eventsnyc and /login-lounge.
            $this->assertNotContains('/'.$path, $disallows, $host);
        }

        foreach (['/auth/', '/admin-edit-event/', '/appointment/', '/gift-card/view/', '/promo/'] as $rule) {
            $this->assertContains($rule, $disallows, $host);
        }
    }

    public function test_the_apex_keeps_the_app_rules_anchored(): void
    {
        config(['app.hosted' => true]);

        $body = $this->robots('https://eventschedule.test/robots.txt')->getContent();

        $this->assertAppRules($body, 'apex');
        $this->assertStringContainsString("\nSitemap: https://eventschedule.test/sitemap.xml\n", $body);
        $this->assertStringContainsString('# AI/LLM-friendly docs: https://eventschedule.test/llms.txt', $body);
    }

    public function test_the_app_host_has_the_rules_and_no_sitemap(): void
    {
        config(['app.hosted' => true]);

        $body = $this->robots('https://app.eventschedule.test/robots.txt')->getContent();

        $this->assertAppRules($body, 'app.');
        $this->assertStringNotContainsString('Sitemap:', $body);
    }

    public function test_www_and_the_blog_keep_the_apex_body(): void
    {
        config(['app.hosted' => true]);

        foreach (['www', 'blog'] as $label) {
            $body = $this->robots("https://{$label}.eventschedule.test/robots.txt")->getContent();

            $this->assertAppRules($body, $label.'.');
            $this->assertStringContainsString('Sitemap: ', $body, $label.'.');
        }
    }

    public function test_a_tenant_subdomain_blocks_only_its_checkout_and_secret_paths(): void
    {
        config(['app.hosted' => true]);

        $body = $this->robots('https://tenant.eventschedule.test/robots.txt')->getContent();

        $this->assertSame([
            '/checkout/',
            '/payment/',
            '/gift-cards/success/',
            '/gift-cards/cancel/',
            '/gift-cards/payment/',
            '/appointment/',
            '/gift-card/view/',
            '/promo/',
        ], $this->disallows($body));

        // The tenant subdomain's line is the cross-submission grant for the global sitemap.
        $this->assertStringContainsString("\nSitemap: https://eventschedule.test/sitemap.xml\n", $body);
        // Never /api/: the calendar renders from it.
        $this->assertStringNotContainsString('/api', $body);
    }

    /** ResolveCustomDomain still runs outside the web group, so the host keeps its own sitemap. */
    public function test_a_custom_domain_gets_the_tenant_body_and_its_own_sitemap(): void
    {
        config(['app.hosted' => true]);

        $this->createRole($this->createOwner(), 'talent', [
            'custom_domain' => 'https://robots-tenant.test',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);

        $body = $this->robots('http://robots-tenant.test/robots.txt')->getContent();

        $this->assertNotContains('/events$', $this->disallows($body));
        $this->assertContains('/gift-card/view/', $this->disallows($body));
        $this->assertStringContainsString("\nSitemap: https://robots-tenant.test/sitemap.xml\n", $body);
        $this->assertStringNotContainsString('eventschedule.test', $body);
    }

    /** Selfhost is path-routed on one host, so every schedule is under the anchored rules. */
    public function test_selfhost_gets_the_anchored_rules_on_any_host(): void
    {
        config(['app.hosted' => false]);

        foreach (['https://eventschedule.test/robots.txt', 'https://tenant.eventschedule.test/robots.txt'] as $url) {
            $body = $this->robots($url)->getContent();

            $this->assertAppRules($body, $url);
            $this->assertStringContainsString('Sitemap: ', $body, $url);
        }
    }
}
