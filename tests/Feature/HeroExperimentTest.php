<?php

namespace Tests\Feature;

use App\Models\MarketingExperimentStat;
use App\Models\Setting;
use App\Models\User;
use App\Utils\HeroExperiment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * The homepage headline A/B test end to end: what the page ships, what the beacon counts, how a
 * signup is credited, and how the test locks its winner. The allocation maths and the per-variant
 * copy rules are in Tests\Unit\HeroExperimentTest.
 */
class HeroExperimentTest extends TestCase
{
    use RefreshDatabase;

    private const REAL_UA = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36';

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.is_nexus' => true]);
        Cache::flush();
        mt_srand(20260928);
    }

    public function test_a_guest_homepage_ships_every_variant_and_renders_the_default(): void
    {
        $default = HeroExperiment::VARIANTS[HeroExperiment::DEFAULT];

        $response = $this->get('/')->assertOk();

        $response->assertSee('data-hero="l1">'.e($default['line1']).'<', false);
        $response->assertSee('data-hero="sub">'.e($default['subtitle']).'<', false);
        $response->assertSee('var variants =', false);

        foreach (HeroExperiment::VARIANTS as $copy) {
            $response->assertSee(json_encode($copy['line1']), false);
        }
    }

    /**
     * The whole design rests on this: the browser picks, so the page must stay cacheable.
     * A server-set variant cookie would make responseIsAnonymous() refuse it.
     */
    public function test_the_homepage_stays_cacheable_with_the_test_running(): void
    {
        $response = $this->get('/')->assertOk();

        $this->assertStringContainsString('s-maxage=600', (string) $response->headers->get('Cache-Control'));
        $this->assertSame([], $response->headers->getCookies());
    }

    public function test_a_locked_winner_is_rendered_by_the_server_with_no_picker(): void
    {
        Setting::set(HeroExperiment::WINNER_SETTING, HeroExperiment::setHash().'|crowd|2026-09-01');

        $response = $this->get('/')->assertOk();

        $response->assertSee('data-hero="l1">'.e(HeroExperiment::VARIANTS['crowd']['line1']).'<', false);
        $response->assertDontSee('var variants =', false);
    }

    public function test_a_winner_from_a_different_variant_set_is_ignored(): void
    {
        Setting::set(HeroExperiment::WINNER_SETTING, 'stalehash000|crowd|2026-09-01');

        $this->get('/')->assertOk()->assertSee('var variants =', false);
    }

    public function test_the_beacon_counts_views_and_clicks_per_variant(): void
    {
        $this->beacon(['variant' => 'plan', 'event' => 'view'])->assertNoContent();
        $this->beacon(['variant' => 'plan', 'event' => 'view'], ['CF-Connecting-IP' => '203.0.113.21'])->assertNoContent();
        $this->beacon(['variant' => 'plan', 'event' => 'click'])->assertNoContent();

        $row = MarketingExperimentStat::where('variant', 'plan')->firstOrFail();

        $this->assertSame(HeroExperiment::EXPERIMENT, $row->experiment);
        $this->assertSame(2, $row->visitors);
        $this->assertSame(1, $row->clicks);
    }

    /**
     * The browser sends each event once per session, but its click flag is per tab and the
     * endpoint is open, so the server has to be the one that dedups.
     */
    public function test_the_beacon_counts_each_visitor_once_per_day(): void
    {
        foreach (range(1, 3) as $i) {
            $this->beacon(['variant' => 'crowd', 'event' => 'view'])->assertNoContent();
            $this->beacon(['variant' => 'crowd', 'event' => 'click'])->assertNoContent();
        }

        $row = MarketingExperimentStat::where('variant', 'crowd')->firstOrFail();

        $this->assertSame(1, $row->visitors);
        $this->assertSame(1, $row->clicks);

        // The dedup is per variant: the same visitor in a later session with another variant
        // did see that one too.
        $this->beacon(['variant' => 'sells', 'event' => 'view'])->assertNoContent();
        $this->assertSame(1, MarketingExperimentStat::where('variant', 'sells')->value('visitors'));
    }

    public function test_the_beacon_refuses_unknown_variants_and_events(): void
    {
        $this->beacon(['variant' => 'nope', 'event' => 'view'])->assertStatus(422);
        $this->beacon(['variant' => 'plan', 'event' => 'purchase'])->assertStatus(422);

        $this->assertSame(0, MarketingExperimentStat::count());
    }

    public function test_the_beacon_ignores_bots_and_signed_in_users(): void
    {
        $this->beacon(['variant' => 'plan', 'event' => 'view'], ['User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)'])->assertNoContent();
        $this->beacon(['variant' => 'plan', 'event' => 'view'], ['Accept-Language' => ''])->assertNoContent();

        $this->actingAs(User::factory()->create());
        $this->beacon(['variant' => 'plan', 'event' => 'view'])->assertNoContent();

        $this->assertSame(0, MarketingExperimentStat::count());
    }

    public function test_the_beacon_does_not_start_a_session(): void
    {
        $response = $this->beacon(['variant' => 'plan', 'event' => 'view'])->assertNoContent();

        $names = array_map(fn ($cookie) => $cookie->getName(), $response->headers->getCookies());

        $this->assertNotContains(config('session.cookie'), $names);
    }

    public function test_the_beacon_is_nexus_only(): void
    {
        config(['app.is_nexus' => false]);

        $this->beacon(['variant' => 'plan', 'event' => 'view'])->assertNotFound();
    }

    public function test_a_signup_is_credited_to_the_variant_in_es_attribution(): void
    {
        config(['app.hosted' => true]);

        $this->withUnencryptedCookie('es_attribution', json_encode(['landing' => '/', 'hero' => 'sellout']))
            ->post('/sign_up', [
                'terms' => '1',
                'name' => 'Headline Visitor',
                'email' => 'headline@gmail.com',
                'password' => 'password',
            ]);

        $user = User::where('email', 'headline@gmail.com')->firstOrFail();

        $this->assertSame('sellout', $user->hero_variant);
        $this->assertSame('/', $user->landing_page, 'the variant must ride alongside the attribution, not replace it');
        $this->assertSame(1, HeroExperiment::stats()['sellout']['signups']);
    }

    /**
     * Google sign-up is roughly half of all accounts, and its controller reads attribution from
     * the session seed, which copies the utm_* values but not the variant.
     */
    public function test_a_google_signup_is_credited_to_the_variant(): void
    {
        config(['app.hosted' => true]);

        $socialUser = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $socialUser->shouldReceive('getId')->andReturn('google-hero-1');
        $socialUser->shouldReceive('getEmail')->andReturn('google-hero@eventschedule-test.org');
        $socialUser->shouldReceive('getName')->andReturn('Google Visitor');
        $socialUser->shouldReceive('getAvatar')->andReturn(null);
        $socialUser->user = ['locale' => 'en'];

        $provider = \Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);

        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->withUnencryptedCookie('es_attribution', json_encode(['landing' => '/', 'hero' => 'crowd']))
            ->get(route('auth.google.callback'));

        $this->assertSame('crowd', User::where('email', 'google-hero@eventschedule-test.org')->firstOrFail()->hero_variant);
    }

    public function test_an_unknown_variant_in_es_attribution_is_not_stored(): void
    {
        config(['app.hosted' => true]);

        $this->withUnencryptedCookie('es_attribution', json_encode(['landing' => '/', 'hero' => '<script>']))
            ->post('/sign_up', [
                'terms' => '1',
                'name' => 'Forged Visitor',
                'email' => 'forged@gmail.com',
                'password' => 'password',
            ]);

        $this->assertNull(User::where('email', 'forged@gmail.com')->firstOrFail()->hero_variant);
    }

    /**
     * A clear signup leader becomes the candidate, holds for WIN_HOLD_DAYS, and is then locked,
     * after which it gets all the traffic. Before that, nothing is locked however far ahead it is.
     */
    public function test_a_signup_leader_is_locked_only_after_holding_for_the_hold_period(): void
    {
        $stats = $this->stats(visitors: 2000, clicks: 100, signups: 20);
        $stats['plan']['signups'] = 80;

        $this->travelTo('2026-10-01 12:00:00');
        $first = HeroExperiment::evaluate($stats);

        $this->assertSame('plan', $first['candidate']['key']);
        $this->assertNull($first['winner']);

        $this->travelTo('2026-10-05 12:00:00');
        $this->assertNull(HeroExperiment::evaluate($stats)['winner'], 'locked before the hold period ended');

        $this->travelTo('2026-10-08 12:00:00');
        $locked = HeroExperiment::evaluate($stats);

        $this->assertSame('plan', $locked['winner']['key']);
        $this->assertSame(1.0, $locked['weights']['plan']);
        $this->assertSame(0.0, $locked['weights']['booked']);
    }

    /**
     * The winner is decided on signups alone. A variant far ahead on clicks with no signup edge
     * never becomes a candidate.
     */
    public function test_a_click_leader_is_never_the_candidate(): void
    {
        $stats = $this->stats(visitors: 2000, clicks: 100, signups: 30);
        $stats['crowd']['clicks'] = 600;

        $this->assertNull(HeroExperiment::evaluate($stats)['candidate']);
    }

    public function test_no_candidate_until_every_variant_has_enough_visitors(): void
    {
        $stats = $this->stats(visitors: 2000, clicks: 100, signups: 20);
        $stats['plan']['signups'] = 80;
        $stats['crowd']['visitors'] = HeroExperiment::WIN_MIN_VISITORS - 1;

        $this->assertNull(HeroExperiment::evaluate($stats)['candidate']);
    }

    public function test_a_candidate_that_loses_the_lead_is_dropped(): void
    {
        $stats = $this->stats(visitors: 2000, clicks: 100, signups: 20);
        $stats['plan']['signups'] = 80;

        HeroExperiment::evaluate($stats);
        $this->assertNotNull(Setting::get(HeroExperiment::CANDIDATE_SETTING));

        $stats['plan']['signups'] = 20;
        $stats['sells']['signups'] = 21;

        $this->assertNull(HeroExperiment::evaluate($stats)['candidate']);
        $this->assertNull(Setting::get(HeroExperiment::CANDIDATE_SETTING));
    }

    public function test_the_admin_growth_page_shows_the_test(): void
    {
        config(['app.hosted' => true]);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->forceFill(['is_admin' => true])->save();

        // The admin group re-auths; without this key the request bounces to confirm-password.
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($admin)
            ->get('/admin/growth')
            ->assertOk()
            ->assertSee(__('messages.hero_test'))
            ->assertSee(HeroExperiment::VARIANTS['plan']['line1']);
    }

    private function beacon(array $payload, array $headers = [])
    {
        $server = [];
        foreach (array_merge([
            'User-Agent' => self::REAL_UA,
            'Accept-Language' => 'en-US,en;q=0.9',
            'Accept' => '*/*',
            'Content-Type' => 'application/json',
            'CF-Connecting-IP' => '203.0.113.20',
        ], $headers) as $name => $value) {
            $server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
        }
        $server['CONTENT_TYPE'] = 'application/json';

        return $this->call('POST', '/marketing/hero', [], [], [], $server, json_encode($payload));
    }

    private function stats(int $visitors, int $clicks, int $signups): array
    {
        return array_map(
            fn () => ['visitors' => $visitors, 'clicks' => $clicks, 'signups' => $signups],
            HeroExperiment::VARIANTS
        );
    }
}
