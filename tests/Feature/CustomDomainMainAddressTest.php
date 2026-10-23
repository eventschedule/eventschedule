<?php

namespace Tests\Feature;

use App\Models\CustomDomainSetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\Feature\Concerns\ForcesEnvironment;
use Tests\TestCase;

/**
 * A schedule served directly on its own domain may make its subdomain the main address
 * (Schedule > Settings, the custom domain block): the custom domain keeps serving every page, and
 * search engines and every link the app hands out name the subdomain instead.
 *
 * The suite is path-routed, where a custom host lands on the domain-less routes and "the
 * subdomain" is /{subdomain}. What depends on real hosts (the body rewrite, the custom host's
 * robots.txt, which address the page names on each host) runs inside hosted(), which registers the
 * hosted subdomain routes as RouteLoadTest does.
 */
class CustomDomainMainAddressTest extends TestCase
{
    use CreatesScheduleData;
    use ForcesEnvironment;
    use RefreshDatabase;

    private const HOST = 'main-address.test';

    private User $owner;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.hosted' => true]);
        $this->fixture();
    }

    private function fixture(): void
    {
        $this->owner = $this->createOwner();
        $this->role = $this->createRole($this->owner, 'talent', [
            'name' => 'Main Address Band',
            'custom_domain' => 'https://'.self::HOST,
            'custom_domain_host' => self::HOST,
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);
    }

    private function subdomainFirst(?Role $role = null): Role
    {
        CustomDomainSetting::store($role ?? $this->role, CustomDomainSetting::SUBDOMAIN);

        return ($role ?? $this->role)->fresh();
    }

    /** The URL of the schedule on its subdomain, with no custom domain in play. */
    private function subdomainUrl(): string
    {
        return route('role.view_guest', ['subdomain' => $this->role->subdomain]);
    }

    /**
     * Run $body with the hosted subdomain routes registered, on a fresh fixture. The app is
     * rebuilt, so RefreshDatabase's transaction is rolled back first and this one is closed here
     * (see RouteLoadTest::test_hosted_gp_routes_load()).
     */
    private function hosted(callable $body): void
    {
        $this->app['db']->connection()->rollBack();
        $this->forceEnv('IS_HOSTED', 'true');
        $this->forceEnv('APP_TESTING', 'false');
        $this->refreshApplication();
        $this->app['db']->connection()->beginTransaction();

        try {
            $this->assertNotNull(app('router')->getRoutes()->match(\Illuminate\Http\Request::create('https://x.'._base_domain().'/'))->getDomain(), 'fixture: the hosted subdomain routes answer');
            \App\Models\CustomDomainSetting::flush();
            $this->fixture();
            $body();
        } finally {
            $connection = $this->app['db']->connection();
            if ($connection->getPdo() && $connection->getPdo()->inTransaction()) {
                $connection->rollBack();
            }
            $connection->disconnect();
        }
    }

    /**
     * What each host says, with the hosted subdomain routes registered. One test, because each
     * rebuild of the app costs the next test a fresh migration (about fifteen seconds).
     *
     *  1. Nothing stored, or "custom" stored, is today's page byte for byte, and the custom host
     *     keeps its own sitemap.
     *  2. With the subdomain as main: the canonical and og:url name it on both hosts.
     *  3. On the custom host the page's own links stay there, while the structured data, the
     *     canonical, og:url and hreflang are spared the rewrite and name the subdomain.
     *  4. The custom host points crawlers at the global sitemap and has none of its own.
     *
     * Mutations: return true from Role::mainAddressIsCustomDomain() whenever the schedule is served
     * on its domain (2); drop the masking from ResolveCustomDomain (3); make it unconditional (1);
     * drop the custom_domain_subdomain_is_main check from sitemap_url() (4).
     */
    public function test_what_each_host_says(): void
    {
        $this->hosted(function () {
            $this->createEvent($this->role, ['name' => 'Harbor Night', 'creator_role_id' => $this->role->id]);
            $custom = 'https://'.self::HOST;
            $ownSitemap = 'Sitemap: '.$custom.route('sitemap', [], false);
            $strip = fn ($html) => preg_replace('#<meta name="csrf-token" content="[^"]*">|nonce="[^"]*"#', '', $html);

            // 1. The domain is the main address until the owner chooses otherwise.
            $before = $this->get($custom.'/')->assertOk()->getContent();
            $this->assertStringContainsString('<link rel="canonical" href="'.$custom.'">', $before);
            $this->assertStringNotContainsString('https://'.$this->role->subdomain.'.', $before);
            $this->assertStringContainsString($ownSitemap, $this->get($custom.'/robots.txt')->assertOk()->getContent());

            CustomDomainSetting::store($this->role, CustomDomainSetting::CUSTOM);
            $this->assertSame($strip($before), $strip($this->get($custom.'/')->assertOk()->getContent()), '"custom" stored is the page as it was');

            // 2. The subdomain is canonical on both hosts.
            $this->subdomainFirst();
            $main = $this->role->getCanonicalUrl();
            $this->assertSame('https://'.$this->role->subdomain.'.'._base_domain(), $main);

            foreach ([$main.'/', $custom.'/'] as $url) {
                $html = $this->get($url)->assertOk()->getContent();
                $this->assertStringContainsString('<link rel="canonical" href="'.$main.'">', $html, $url);
                $this->assertStringContainsString('<meta property="og:url" content="'.$main.'">', $html, $url);
                $this->assertStringNotContainsString('<link rel="canonical" href="'.$custom, $html, $url);
            }

            // 3. Links stay on the custom host; what names the page does not.
            $html = $this->get($custom.'/')->assertOk()->getContent();
            preg_match_all('#<script type="application/ld\+json"[^>]*>(.*?)</script>#s', $html, $blocks);
            $this->assertNotEmpty($blocks[1], 'the page carries structured data');
            foreach ($blocks[1] as $block) {
                $this->assertStringNotContainsString(self::HOST, $block, 'structured data names the main address');
            }
            $this->assertTrue(str_contains(implode('', $blocks[1]), $main) || str_contains(implode('', $blocks[1]), str_replace('/', '\/', $main)), 'structured data names the subdomain');

            $outside = preg_replace(['#<script type="application/ld\+json"[^>]*>.*?</script>#s', '#<link\b[^>]*\b(rel="canonical"|hreflang=)[^>]*>#', '#<meta property="og:url"[^>]*>#'], '', $html);
            $this->assertStringNotContainsString($main, $outside, 'every other link on the page stays on the custom domain');
            $this->assertStringContainsString($custom, $outside);

            // 4. Crawlers on the custom host are sent to the global sitemap.
            $robots = $this->get($custom.'/robots.txt')->assertOk()->getContent();
            $this->assertStringContainsString("\nSitemap: ".config('app.url').route('sitemap', [], false)."\n", $robots);
            $this->assertStringNotContainsString($ownSitemap, $robots);
            $this->assertMatchesRegularExpression('#<link rel="sitemap"[^>]*href="'.preg_quote(config('app.url'), '#').'#', $html);
            $this->get($custom.'/sitemap.xml')->assertNotFound();
        });
    }

    /**
     * Every link the app hands out (mail, shares, the QR code, the admin's copy button) comes from
     * getGuestUrl(true) and Event::getGuestUrl(..., true), and redirect mode keeps its domain.
     * Mutation: leave the new rule out of Role::handedOutCustomDomain().
     */
    public function test_the_links_the_app_hands_out_name_the_main_address(): void
    {
        $event = $this->createEvent($this->role, ['name' => 'Harbor Night', 'creator_role_id' => $this->role->id]);

        $this->assertSame('https://'.self::HOST, $this->role->getGuestUrl(true));
        $this->assertStringStartsWith('https://'.self::HOST.'/', $event->fresh()->getGuestUrl($this->role->subdomain, false, true));

        $role = $this->subdomainFirst();
        $this->assertSame($this->subdomainUrl(), $role->getGuestUrl(true));
        $this->assertStringNotContainsString(self::HOST, $event->fresh()->getGuestUrl($role->subdomain, false, true));

        $redirect = $this->createRole($this->owner, 'talent', [
            'custom_domain' => 'https://redirected.test',
            'custom_domain_mode' => 'redirect',
        ]);
        $this->subdomainFirst($redirect);
        $this->assertSame('https://redirected.test', $redirect->fresh()->getGuestUrl(true), 'redirect mode keeps handing out its domain, which forwards to the subdomain');
    }

    /**
     * The global sitemap lists the schedule and its events once the subdomain is main.
     * Mutation: keep servesOnCustomDomain() in Event::canonicalTarget().
     */
    public function test_the_global_sitemap_lists_it_at_subdomain_urls(): void
    {
        $event = $this->createEvent($this->role, ['name' => 'Harbor Night', 'creator_role_id' => $this->role->id]);

        $before = $this->get('/sitemap-events-1.xml')->assertOk()->streamedContent();
        $this->assertStringNotContainsString($event->slug, $before);

        $this->subdomainFirst();
        \Illuminate\Support\Facades\Cache::flush();

        $events = $this->get('/sitemap-events-1.xml')->assertOk()->streamedContent();
        $this->assertStringContainsString($event->slug, $events);
        $this->assertStringNotContainsString(self::HOST, $events);
        $schedules = $this->get('/sitemap-schedules-1.xml')->assertOk()->streamedContent();
        $this->assertStringContainsString('<loc>'.$this->subdomainUrl().'</loc>', $schedules);

    }

    /**
     * A page that lists many events of such a schedule asks for the setting once, not per event:
     * QueryGuard fails this test on a query or a lazy load per row.
     */
    public function test_a_list_of_its_events_asks_once(): void
    {
        $this->subdomainFirst();
        $curator = $this->createCurator($this->createOwner(), ['name' => 'City Guide']);
        foreach (range(1, 4) as $n) {
            $event = $this->createEvent($this->role, ['name' => 'Night '.$n, 'creator_role_id' => $this->role->id, 'starts_at' => now()->addDays($n)->setTime(19, 0)->format('Y-m-d H:i:s')]);
            $event->roles()->attach($curator->id, ['is_accepted' => true]);
        }

        $html = $this->get('/'.$curator->subdomain)->assertOk()->getContent();

        $this->assertStringNotContainsString(self::HOST, $html);
    }

    private function save(array $fields): TestResponse
    {
        return $this->actingAs($this->owner)
            ->from(route('role.edit', ['subdomain' => $this->role->subdomain]))
            ->put(route('role.update', ['subdomain' => $this->role->subdomain]), array_merge([
                'name' => $this->role->name,
                'email' => $this->role->email,
                'timezone' => $this->role->timezone,
                'new_subdomain' => $this->role->subdomain,
                'custom_domain' => 'https://'.self::HOST,
                'custom_domain_mode' => 'direct',
            ], $fields));
    }

    /**
     * The form stores the choice; a save that did not show it, a bad value and a schedule off
     * Enterprise change nothing.
     * Mutation: drop the _submitted check in RoleController::update().
     */
    public function test_the_form_stores_the_choice_only_when_it_showed_it(): void
    {
        $this->save(['custom_domain_main_address' => 'subdomain'])->assertRedirect();
        $this->assertSame(CustomDomainSetting::CUSTOM, CustomDomainSetting::mainAddressOf($this->role), 'no marker, no change');

        $this->save(['custom_domain_main_address' => 'subdomain', 'custom_domain_main_address_submitted' => '1'])->assertRedirect();
        CustomDomainSetting::flush();
        $this->assertSame(CustomDomainSetting::SUBDOMAIN, CustomDomainSetting::mainAddressOf($this->role));

        $this->save(['custom_domain_main_address' => 'elsewhere', 'custom_domain_main_address_submitted' => '1'])->assertSessionHasErrors('custom_domain_main_address');
        CustomDomainSetting::flush();
        $this->assertSame(CustomDomainSetting::SUBDOMAIN, CustomDomainSetting::mainAddressOf($this->role));

        $this->role->forceFill(['plan_type' => 'pro'])->save();
        $this->save(['custom_domain_main_address' => 'custom', 'custom_domain_main_address_submitted' => '1']);
        CustomDomainSetting::flush();
        $this->assertSame(CustomDomainSetting::SUBDOMAIN, CustomDomainSetting::mainAddressOf($this->role), 'off Enterprise the domain fields are frozen, this one with them');
    }

    /**
     * ResolveCustomDomain keeps the custom host's row, subdomain included, for ten minutes. With
     * the subdomain as main the form shows the subdomain field in Direct mode, so a rename is an
     * ordinary save, and it must forget that row: kept, the domain routed to the old name (a 404,
     * or the schedule of whoever claimed it next).
     * Mutation: drop the forget after $role->save() in RoleController::update().
     */
    public function test_a_rename_forgets_the_custom_hosts_cached_row(): void
    {
        $this->role->forceFill(['plan_type' => 'enterprise'])->save();
        $key = 'custom_domain:'.self::HOST;
        $prime = fn () => \Illuminate\Support\Facades\Cache::put($key, Role::where('custom_domain_host', self::HOST)->first(['id', 'subdomain', 'custom_domain_host']), 600);

        $prime();
        $this->save([])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertTrue(\Illuminate\Support\Facades\Cache::has($key), 'a save that renames nothing keeps the row');

        $this->save(['new_subdomain' => 'main-address-renamed'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('main-address-renamed', $this->role->fresh()->subdomain, 'fixture: the save renamed the schedule');
        $this->assertFalse(\Illuminate\Support\Facades\Cache::has($key), 'the rename forgot the row holding the old subdomain');
    }
}
