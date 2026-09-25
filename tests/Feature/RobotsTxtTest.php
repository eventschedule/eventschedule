<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
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
 * The secret-bearing paths (a booking, a ticket, an order, a subscription's links, a newsletter's
 * tracking links) are blocked on every host, each by its full prefix: a bare /appointment/,
 * /ticket/ or /sub/ would also block an event slugged "appointment", "ticket" or "sub", whose page
 * on its schedule's host is /ticket/{id}. Three more (feedback, a schedule transfer, a signed
 * unsubscribe) are blocked only off a schedule's host, where a rule can name them exactly; their
 * pages carry noindex wherever they answer (SecretPageNoindexTest).
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
        '/appointment/view/', '/appointment/cancel/', '/appointment/pay/', '/appointment/checkout/',
        '/appointment/ical/', '/appointment/reschedule/',
        '/gift-card/view/', '/installment/view/',
        '/ticket/view/', '/ticket/qr_code/', '/ticket/wallet/', '/ticket/order/',
        '/sub/c/', '/sub/m/', '/sub/u/', '/int/u/',
        '/nl/o/', '/nl/c/', '/nl/u/',
        '/ne/c/', '/ne/u/',
        '/promo/',
    ];

    /**
     * Blocked off a schedule's own host only. There /feedback/{id} is an event slugged
     * "feedback", and the other two never reach their pages.
     */
    private const APP_SECRET_PATHS = ['/feedback/', '/schedule-transfer/', '/user/unsubscribe'];

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
        '/appointment/', '/ticket/', '/sub/', '/nl/', '/int/', '/installment/', '/gift-card/', '/user/',
        '/*/', '/*/checkout/', '/*/payment/', '/*/gift-cards/',
    ];

    /** Words that are both the start of a secret or return path and a slug an event can have. */
    private const EVENT_SLUGS = [
        'checkout', 'payment', 'gift-cards', 'appointment', 'ticket', 'sub', 'nl', 'int', 'installment',
        'gift-card', 'feedback', 'schedule-transfer', 'user',
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

        foreach (['/auth/', '/admin-edit-event/', ...self::SECRET_PATHS, ...self::APP_SECRET_PATHS] as $rule) {
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
            'appointments.manage_cancel' => ['event_id' => 'aBc1', 'secret' => 's3cret'],
            'appointments.pay' => ['event_id' => 'aBc1', 'secret' => 's3cret'],
            'appointments.checkout_success' => ['sale_id' => 'aBc1'],
            'appointments.ical' => ['event_id' => 'aBc1', 'secret' => 's3cret'],
            'appointments.reschedule' => ['event_id' => 'aBc1', 'secret' => 's3cret'],
            'appointments.reschedule_slots' => ['event_id' => 'aBc1', 'secret' => 's3cret'],
            'subscriber.show_confirm' => ['token' => 't0ken'],
            'subscriber.show_manage' => ['token' => 't0ken'],
            'subscriber.show_unsubscribe' => ['token' => 't0ken'],
            'event.interest.show_unsubscribe' => ['token' => 't0ken'],
            'newsletter.track_open' => ['token' => 't0ken'],
            'newsletter.track_click' => ['token' => 't0ken', 'encodedUrl' => 'aHR0cHM6Ly9leGFtcGxlLmNvbQ'],
            'newsletter.show_unsubscribe' => ['token' => 't0ken'],
            'notification_email.show_confirm' => ['role' => 'aBc1', 'token' => 't0ken'],
            'notification_email.show_unsubscribe' => ['role' => 'aBc1', 'token' => 't0ken'],
        ] as $name => $params) {
            $paths[$name] = route($name, $params, false);
        }

        return $paths;
    }

    /**
     * The secret-bearing links blocked off a schedule's own host only (APP_SECRET_PATHS).
     *
     * @return array<string, string> route name => path
     */
    private function appSecretPaths(): array
    {
        return [
            'feedback.show' => route('feedback.show', ['event_id' => 'aBc1', 'secret' => 's3cret'], false),
            'role.transfer.show' => route('role.transfer.show', ['token' => 't0ken'], false),
            'user.unsubscribe' => route('user.unsubscribe', ['email' => 'YUBleGFtcGxlLmNvbQ==', 'sig' => 's1g'], false),
        ];
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
            $this->assertFalse($this->blocks($disallows, $path.'/2026-10-24'), "a date of the event slugged {$slug}");
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

        foreach ($this->secretPaths() + $this->appSecretPaths() + $this->scheduleReturnPaths($role) as $name => $path) {
            $this->assertTrue($this->blocks($disallows, $path), "{$name}: {$path}");
        }

        foreach ($this->eventPaths($role) as $slug => $path) {
            $this->assertFalse($this->blocks($disallows, $path), "the event slugged {$slug}: {$path}");
            $this->assertFalse($this->blocks($disallows, $path.'/photos'), "the gallery of the event slugged {$slug}");
        }

        // And the schedule's own page.
        $this->assertFalse($this->blocks($disallows, '/'.$role->subdomain));
    }

    /** The apex, as for a tenant host above: every secret link, the off-host three included. */
    public function test_the_apex_blocks_every_secret_link(): void
    {
        config(['app.hosted' => true]);

        $disallows = $this->disallows($this->robots('https://eventschedule.test/robots.txt')->getContent());

        foreach ($this->secretPaths() + $this->appSecretPaths() as $name => $path) {
            $this->assertTrue($this->blocks($disallows, $path), "{$name}: {$path}");
        }
    }

    /**
     * Read off the route table rather than the lists above, so a new route fails here until
     * robots.txt names it:
     *  - every GET route off a schedule's address whose path carries a {secret} or a {token} is
     *    blocked on the apex and on selfhost;
     *  - every /appointment/ route is blocked on every host, a schedule's own included, now that
     *    the rule is a list of exact prefixes rather than /appointment/ itself.
     */
    public function test_every_secret_bearing_route_is_blocked(): void
    {
        $bodies = [];

        foreach (['hosted apex' => [true, 'https://eventschedule.test/robots.txt'], 'tenant' => [true, 'https://tenant.eventschedule.test/robots.txt'], 'selfhost' => [false, 'https://eventschedule.test/robots.txt']] as $host => [$hosted, $url]) {
            config(['app.hosted' => $hosted]);
            $bodies[$host] = $this->disallows($this->robots($url)->getContent());
        }

        $secret = 0;
        $appointment = 0;

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();

            // A schedule's own routes (the path-based copies tests register) are its host's.
            if ($route->getDomain() || str_starts_with($uri, '{subdomain}')) {
                continue;
            }

            $path = '/'.preg_replace('~\{[^}]+\}~', 'x1', $uri);

            if (in_array('GET', $route->methods(), true) && preg_match('~\{(?:secret|token)\??\}~', $uri)) {
                $secret++;

                foreach (['hosted apex', 'selfhost'] as $host) {
                    $this->assertTrue($this->blocks($bodies[$host], $path), "{$host}: {$uri}");
                }
            }

            if (str_starts_with($uri, 'appointment/')) {
                $appointment++;

                foreach ($bodies as $host => $disallows) {
                    $this->assertTrue($this->blocks($disallows, $path), "{$host}: {$uri}");
                }
            }
        }

        // Without these the loop can pass by reading nothing.
        $this->assertGreaterThanOrEqual(15, $secret, 'fixture: the secret-bearing routes were found');
        $this->assertGreaterThanOrEqual(6, $appointment, 'fixture: the /appointment/ routes were found');
    }
}
