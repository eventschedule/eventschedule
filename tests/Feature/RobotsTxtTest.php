<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Utils\UrlUtils;
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
 *
 * The secret-bearing paths (a ticket, an order, a subscription's links, a newsletter's tracking
 * links) are blocked on every host, each by its full prefix: a bare /ticket/ or /sub/ would also
 * block an event slugged "ticket" or "sub", whose page on its schedule's host is /ticket/{id}.
 */
class RobotsTxtTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const APP_PATHS = [
        'login', 'sign_up', 'reset-password', 'update-password', 'confirm-password', 'verify-email',
        'two-factor-challenge', 'events', 'settings', 'checkout', 'admin',
    ];

    /** Blocked on every host, in the order the body lists them. */
    private const SECRET_PATHS = [
        '/appointment/', '/gift-card/view/', '/installment/view/',
        '/ticket/view/', '/ticket/qr_code/', '/ticket/wallet/', '/ticket/order/',
        '/sub/c/', '/sub/m/', '/sub/u/', '/int/u/',
        '/nl/o/', '/nl/c/', '/nl/u/',
        '/promo/',
    ];

    /** A selfhost schedule's promo clicks and return pages, one segment under its name. */
    private const SELFHOST_SCHEDULE_PATHS = [
        '/*/promo/',
        '/*/checkout/success/', '/*/checkout/cancel/', '/*/payment/success/', '/*/payment/cancel/',
        '/*/gift-cards/success/', '/*/gift-cards/cancel/', '/*/gift-cards/payment/',
    ];

    /**
     * Prefixes no body may carry: each is the start of a real event page somewhere. A tenant host
     * serves an event slugged "ticket" at /ticket/{id}, and selfhost one slugged "checkout" at
     * /{subdomain}/checkout/{id}.
     */
    private const NEVER_BARE = [
        '/ticket/', '/sub/', '/nl/', '/int/', '/installment/', '/gift-card/',
        '/*/', '/*/checkout/', '/*/payment/', '/*/gift-cards/',
    ];

    /** Words that are both the start of a secret or return path and a slug an event can have. */
    private const EVENT_SLUGS = ['checkout', 'payment', 'gift-cards', 'ticket', 'sub', 'nl', 'int', 'installment', 'gift-card'];

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

    /**
     * Whether a crawler honouring these Disallow values may not fetch $path. Each value is a
     * prefix in which * matches any run of characters and a final $ anchors the end (RFC 9309);
     * with no Allow lines, any match blocks.
     */
    private function blocks(array $disallows, string $path): bool
    {
        foreach ($disallows as $rule) {
            $anchored = str_ends_with($rule, '$');
            $pattern = str_replace('\*', '.*', preg_quote($anchored ? substr($rule, 0, -1) : $rule, '~'));

            if (preg_match('~^'.$pattern.($anchored ? '$' : '').'~', $path)) {
                return true;
            }
        }

        return false;
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

        foreach (['/auth/', '/admin-edit-event/', ...self::SECRET_PATHS] as $rule) {
            $this->assertContains($rule, $disallows, $host);
        }

        $this->assertNoBarePrefix($disallows, $host);
    }

    private function assertNoBarePrefix(array $disallows, string $host): void
    {
        foreach (self::NEVER_BARE as $prefix) {
            $this->assertNotContains($prefix, $disallows, $host);
        }
    }

    /**
     * The secret-bearing pages, as the route table writes them. Every one of these routes answers
     * on every host, a schedule's included.
     *
     * @return array<string, string> route name => path
     */
    private function secretPaths(): array
    {
        $paths = [];

        foreach ([
            'ticket.view' => ['event_id' => 'aBc1', 'secret' => 's3cret'],
            'ticket.qr_code' => ['event_id' => 'aBc1', 'secret' => 's3cret'],
            'ticket.wallet.google' => ['event_id' => 'aBc1', 'secret' => 's3cret'],
            'ticket.order' => ['order_id' => 'aBc1', 'secret' => 's3cret'],
            'installment.view' => ['plan_id' => 'aBc1', 'secret' => 's3cret'],
            'gift_card.view' => ['gift_card_id' => 'aBc1', 'secret' => 's3cret'],
            'appointments.manage' => ['event_id' => 'aBc1', 'secret' => 's3cret'],
            'subscriber.show_confirm' => ['token' => 't0ken'],
            'subscriber.show_manage' => ['token' => 't0ken'],
            'subscriber.show_unsubscribe' => ['token' => 't0ken'],
            'event.interest.show_unsubscribe' => ['token' => 't0ken'],
            'newsletter.track_open' => ['token' => 't0ken'],
            'newsletter.track_click' => ['token' => 't0ken', 'encodedUrl' => 'aHR0cHM6Ly9leGFtcGxlLmNvbQ'],
            'newsletter.show_unsubscribe' => ['token' => 't0ken'],
        ] as $name => $params) {
            $paths[$name] = route($name, $params, false);
        }

        return $paths;
    }

    /**
     * A schedule's promo click and return pages, under /{subdomain}/ as selfhost serves them.
     * Tests register the path-based routes, so a tenant host's copy is the same path with the
     * schedule's segment taken off.
     *
     * @return array<string, string> route name => path
     */
    private function scheduleReturnPaths(Role $role): array
    {
        $paths = [];

        foreach ([
            'promo.click' => ['hash' => 'aBc1'],
            'checkout.success' => ['sale_id' => 'aBc1'],
            'checkout.cancel' => ['sale_id' => 'aBc1'],
            'payment_url.success' => ['sale_id' => 'aBc1'],
            'payment_url.cancel' => ['sale_id' => 'aBc1'],
            'gift_card.success' => ['gift_card_id' => 'aBc1'],
            'gift_card.cancel' => ['gift_card_id' => 'aBc1'],
            'gift_card.payment_url.success' => ['gift_card_id' => 'aBc1'],
            'gift_card.payment_url.cancel' => ['gift_card_id' => 'aBc1'],
        ] as $name => $params) {
            $paths[$name] = route($name, ['subdomain' => $role->subdomain] + $params, false);
        }

        return $paths;
    }

    /**
     * One event slugged with each word in EVENT_SLUGS, as its page's path on selfhost: the slug an
     * event made before such words gained "-event" (Event::storableSlug()) keeps, and the one
     * every event slugged "ticket" or "sub" still gets.
     *
     * @return array<string, string> slug => /{subdomain}/{slug}/{id}
     */
    private function eventPaths(Role $role): array
    {
        $paths = [];

        foreach (self::EVENT_SLUGS as $slug) {
            $event = $this->createEvent($role, ['name' => 'Slugged '.$slug, 'slug' => $slug, 'creator_role_id' => $role->id]);
            $path = parse_url($event->getGuestUrl($role->subdomain), PHP_URL_PATH);

            $this->assertSame('/'.$role->subdomain.'/'.$slug.'/'.UrlUtils::encodeId($event->id), $path, 'fixture: the event page is named by its slug');

            $paths[$slug] = $path;
        }

        return $paths;
    }

    /** $path with its leading /{subdomain} taken off, as the schedule's own host serves it. */
    private function onScheduleHost(Role $role, string $path): string
    {
        $this->assertStringStartsWith('/'.$role->subdomain.'/', $path);

        return substr($path, strlen('/'.$role->subdomain));
    }

    public function test_the_apex_keeps_the_app_rules_anchored(): void
    {
        config(['app.hosted' => true]);

        $body = $this->robots('https://eventschedule.test/robots.txt')->getContent();

        $this->assertAppRules($body, 'apex');
        $this->assertStringContainsString("\nSitemap: https://eventschedule.test/sitemap.xml\n", $body);
        $this->assertStringContainsString('# AI/LLM-friendly docs: https://eventschedule.test/llms.txt', $body);

        // A hosted install serves a schedule's return pages on the schedule's own host.
        foreach (self::SELFHOST_SCHEDULE_PATHS as $rule) {
            $this->assertNotContains($rule, $this->disallows($body));
        }
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
        $disallows = $this->disallows($body);

        $this->assertSame([
            '/checkout/success/',
            '/checkout/cancel/',
            '/payment/success/',
            '/payment/cancel/',
            '/gift-cards/success/',
            '/gift-cards/cancel/',
            '/gift-cards/payment/',
            ...self::SECRET_PATHS,
        ], $disallows);

        // The return prefixes, never the words alone: an event slugged "checkout" before such
        // words gained "-event" is /checkout/{id} on this host.
        foreach (['/checkout/', '/payment/', '/gift-cards/'] as $prefix) {
            $this->assertNotContains($prefix, $disallows);
        }
        $this->assertNoBarePrefix($disallows, 'tenant');

        // The tenant subdomain's line is the cross-submission grant for the global sitemap.
        $this->assertStringContainsString("\nSitemap: https://eventschedule.test/sitemap.xml\n", $body);
        // Never /api/: the calendar renders from it.
        $this->assertStringNotContainsString('/api', $body);
    }

    /**
     * The tenant rules checked against real URLs rather than read as strings: every secret page
     * and return page is blocked, and no event page is, whatever its slug.
     */
    public function test_a_tenant_host_blocks_its_secrets_and_returns_but_no_event_page(): void
    {
        config(['app.hosted' => true]);

        $disallows = $this->disallows($this->robots('https://tenant.eventschedule.test/robots.txt')->getContent());
        $role = $this->createRole($this->createOwner(), 'venue');

        foreach ($this->secretPaths() as $name => $path) {
            $this->assertTrue($this->blocks($disallows, $path), "{$name}: {$path}");
        }

        foreach ($this->scheduleReturnPaths($role) as $name => $path) {
            $path = $this->onScheduleHost($role, $path);
            $this->assertTrue($this->blocks($disallows, $path), "{$name}: {$path}");
        }

        foreach ($this->eventPaths($role) as $slug => $path) {
            $path = $this->onScheduleHost($role, $path);
            $this->assertFalse($this->blocks($disallows, $path), "the event slugged {$slug}: {$path}");
            $this->assertFalse($this->blocks($disallows, $path.'/photos'), "the gallery of the event slugged {$slug}");
        }
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
        $this->assertContains('/ticket/view/', $this->disallows($body));
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

            // Every schedule's promo clicks and return pages, one segment under its name.
            foreach (self::SELFHOST_SCHEDULE_PATHS as $rule) {
                $this->assertContains($rule, $this->disallows($body), $url);
            }
        }
    }

    /** The selfhost rules checked against real URLs, as for a tenant host above. */
    public function test_selfhost_blocks_every_schedules_secrets_and_returns_but_no_event_page(): void
    {
        config(['app.hosted' => false]);

        $disallows = $this->disallows($this->robots('https://eventschedule.test/robots.txt')->getContent());
        $role = $this->createRole($this->createOwner(), 'venue');

        foreach ($this->secretPaths() + $this->scheduleReturnPaths($role) as $name => $path) {
            $this->assertTrue($this->blocks($disallows, $path), "{$name}: {$path}");
        }

        foreach ($this->eventPaths($role) as $slug => $path) {
            $this->assertFalse($this->blocks($disallows, $path), "the event slugged {$slug}: {$path}");
            $this->assertFalse($this->blocks($disallows, $path.'/photos'), "the gallery of the event slugged {$slug}");
        }

        // And the schedule's own page.
        $this->assertFalse($this->blocks($disallows, '/'.$role->subdomain));
    }
}
