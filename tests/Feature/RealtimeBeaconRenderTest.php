<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Which pages carry the /admin/realtime beacon, and what context they hand it.
 *
 * Asserts on `api\/realtime` (the beacon's URL inside @json), never on sendBeacon: the marketing
 * layout already calls sendBeacon for marketing.visit, so that would pass with this beacon gone.
 */
class RealtimeBeaconRenderTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pinAppUrl('https://eventschedule.test');
        Cache::flush();
        Setting::set('realtime_enabled', '1');
    }

    public function test_marketing_pages_carry_it_for_guests_with_no_user_and_stay_edge_cacheable(): void
    {
        $this->freezeTime();

        $first = $this->get('/pricing')->assertOk();
        $second = $this->get('/pricing')->assertOk();

        $context = $this->context($first);
        $this->assertSame('wp', $context['s']);
        $this->assertSame('/pricing', $context['p']);
        $this->assertSame('', $context['u']);
        $this->assertSame($context, $this->context($second), 'every anonymous visitor must get the same cached copy');
        $this->assertStringContainsString('public', $second->headers->get('Cache-Control'));
        $this->assertSame([], $second->headers->all('set-cookie'));
    }

    public function test_a_signed_in_render_names_the_user(): void
    {
        $user = $this->createOwner();

        $context = $this->context($this->actingAs($user)->get('/pricing')->assertOk());

        $this->assertNotSame('', $context['u']);
        $this->assertSame(0, $context['a']);
    }

    public function test_schedule_pages_carry_the_schedule_and_are_the_gp_surface(): void
    {
        $role = $this->createRole($this->createOwner());

        $context = $this->context($this->get('/'.$role->subdomain)->assertOk());

        $this->assertSame('gp', $context['s']);
        $this->assertSame(\App\Utils\UrlUtils::encodeId($role->id), $context['r']);
        $this->assertSame('', $context['e']);
    }

    public function test_app_pages_carry_it_and_admin_pages_do_not(): void
    {
        $admin = $this->createOwner(true);
        $this->createRole($admin);

        $context = $this->context($this->actingAs($admin)->get(route('home'))->assertOk());
        $this->assertSame('ap', $context['s']);
        $this->assertSame(1, $context['a']);

        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin)
            ->get('/admin/dashboard')->assertOk()->assertDontSee('api\/realtime', false);
    }

    public function test_an_app_page_hands_over_its_route_template_for_a_visitor_who_declines(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $context = $this->context($this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))->assertOk());

        $this->assertSame('/'.$role->subdomain.'/schedule', $context['p']);
        $this->assertSame('/{subdomain}/{tab}', $context['pt'], 'the path names their schedule; the template does not');
    }

    public function test_sign_in_pages_are_their_own_surface(): void
    {
        $this->assertSame('auth', $this->context($this->get('/login')->assertOk())['s']);
        $this->assertSame('auth', $this->context($this->get('/sign_up')->assertOk())['s']);
    }

    public function test_pages_on_the_bare_app_shell_carry_it(): void
    {
        $user = $this->createOwner();

        $this->assertSame('ap', $this->context($this->actingAs($user)->get(route('getting-started'))->assertOk())['s']);
    }

    public function test_error_pages_and_graphics_do_not_carry_it(): void
    {
        $this->get('/this-page-does-not-exist-'.uniqid())->assertNotFound()->assertDontSee('api\/realtime', false);

        $role = $this->createRole($this->createOwner());
        $this->get('/'.$role->subdomain.'?graphic=1')->assertOk()->assertDontSee('api\/realtime', false);
    }

    public function test_nothing_renders_while_tracking_is_off(): void
    {
        Setting::set('realtime_enabled', '0');

        $this->get('/pricing')->assertOk()->assertDontSee('api\/realtime', false);
    }

    public function test_the_beacon_url_has_no_host_so_a_custom_domain_page_posts_same_origin(): void
    {
        config(['app.hosted' => true]);
        $role = $this->createRole($this->createOwner());
        DB::table('roles')->where('id', $role->id)->update([
            'custom_domain_host' => 'events.jazzclub.example',
            'custom_domain_mode' => 'direct',
            'custom_domain_status' => 'active',
        ]);

        $html = $this->get('https://events.jazzclub.example/')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/var url = "\\\\\\/api\\\\\\/realtime";/', $html);
    }

    public function test_the_script_checks_consent_before_identifying_anyone(): void
    {
        $html = $this->get('/pricing')->getContent();

        $this->assertStringContainsString("getItem('cookie_consent') === 'granted'", $html);
        $this->assertStringContainsString('globalPrivacyControl', $html);
        $this->assertStringContainsString("'es:consent-change'", $html);

        // Withdrawal: the privacy page's "change your choice" announces null, not 'denied', so
        // anything but 'granted' must withdraw; and every heartbeat re-reads the stored choice, so a
        // withdrawal in another tab is noticed. Both were missing once, and the policy promises them.
        $this->assertStringContainsString("} else if (value !== 'granted') {", $html);
        $this->assertMatchesRegularExpression('/var beat = function \(\) \{\s*if \(!stillConsented\(\)/', $html);
        // An embed never runs in full mode.
        $this->assertStringContainsString('var full = !embed && consented();', $html);
        // A tab opened in the background re-reads the choice before its first identified page
        // view, and another tab's withdrawal arrives through the storage event.
        $this->assertStringContainsString('if (full && !pageViewSent && stillConsented()) {', $html);
        $this->assertMatchesRegularExpression("/addEventListener\('storage'.*?!consented\(\)\) withdraw\(\);/s", $html);
    }

    public function test_realtime_alone_puts_no_cookie_banner_in_an_embedded_calendar(): void
    {
        config(['services.google.analytics' => null, 'ads.enabled' => false, 'stay22.enabled' => false, 'app.cookie_consent_banner' => false]);
        $role = $this->createRole($this->createOwner());

        $this->get('/'.$role->subdomain)->assertOk()->assertSee('data-cookie-consent-action', false);
        $this->get('/'.$role->subdomain.'?embed=true')->assertOk()->assertDontSee('data-cookie-consent-action', false);

        // Anything else consent-gated still shows it there, as before.
        config(['app.cookie_consent_banner' => true]);
        $this->get('/'.$role->subdomain.'?embed=true')->assertOk()->assertSee('data-cookie-consent-action', false);
    }

    public function test_turning_realtime_on_brings_the_cookie_banner_even_with_nothing_else_consent_gated(): void
    {
        config(['services.google.analytics' => null, 'ads.enabled' => false, 'stay22.enabled' => false, 'app.cookie_consent_banner' => false]);

        $this->get('/pricing')->assertOk()->assertSee('data-cookie-consent-action', false);

        Setting::set('realtime_enabled', '0');
        $this->get('/pricing')->assertOk()->assertDontSee('data-cookie-consent-action', false);
        $this->assertFalse(cookie_banner_required());
    }

    private function context(TestResponse $response): array
    {
        $matched = preg_match('/var ctx = (\{.*?\});/', $response->getContent(), $match);
        $this->assertSame(1, $matched, 'the page carries no realtime beacon');

        return json_decode($match[1], true);
    }
}
