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

        // Subtitles too: a challenger can share the control's headline and differ only below it.
        foreach (HeroExperiment::VARIANTS as $copy) {
            $response->assertSee(json_encode($copy['line1']), false);
            $response->assertSee(json_encode($copy['subtitle']), false);
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
        Setting::set(HeroExperiment::WINNER_SETTING, HeroExperiment::setHash().'|promote_sell|2026-09-01');

        $response = $this->get('/')->assertOk();

        $response->assertSee('data-hero="l1">'.e(HeroExperiment::VARIANTS['promote_sell']['line1']).'<', false);
        $response->assertDontSee('var variants =', false);
    }

    /**
     * What a search result prints for the homepage is the headline and subtitle the page opens on,
     * built by HeroExperiment::meta() rather than typed a second time in a lang file. The same two
     * slots feed the Open Graph pair, so a shared link reads the same.
     */
    public function test_the_search_result_carries_the_default_headline_and_subtitle(): void
    {
        $meta = HeroExperiment::meta(HeroExperiment::VARIANTS[HeroExperiment::DEFAULT]);

        $this->assertSame($meta, $this->searchResult($this->get('/')->assertOk()->getContent()));
    }

    public function test_the_search_result_follows_a_locked_winner(): void
    {
        Setting::set(HeroExperiment::WINNER_SETTING, HeroExperiment::setHash().'|promote_sell|2026-09-01');

        $meta = HeroExperiment::meta(HeroExperiment::VARIANTS['promote_sell']);

        $this->assertNotSame($meta, HeroExperiment::meta(HeroExperiment::VARIANTS[HeroExperiment::DEFAULT]));
        $this->assertSame($meta, $this->searchResult($this->get('/')->assertOk()->getContent()));
    }

    /**
     * The page's <title> and meta description, after checking the Open Graph pair says the same.
     *
     * @return array{title: string, description: string}
     */
    private function searchResult(string $html): array
    {
        $read = function (string $pattern) use ($html): string {
            $this->assertSame(1, preg_match($pattern, $html, $m), "the homepage has no {$pattern}");

            return html_entity_decode(trim($m[1]), ENT_QUOTES | ENT_HTML5);
        };

        $result = [
            'title' => $read('~<title>(.*?)</title>~s'),
            'description' => $read('~<meta name="description" content="(.*?)">~s'),
        ];

        $this->assertSame($result['title'], $read('~<meta property="og:title" content="(.*?)">~s'));
        $this->assertSame($result['description'], $read('~<meta property="og:description" content="(.*?)">~s'));

        return $result;
    }

    public function test_a_winner_from_a_different_variant_set_is_ignored(): void
    {
        Setting::set(HeroExperiment::WINNER_SETTING, 'stalehash000|promote_sell|2026-09-01');

        $this->get('/')->assertOk()->assertSee('var variants =', false);
    }

    public function test_the_beacon_counts_views_and_clicks_per_variant(): void
    {
        $this->beacon(['variant' => 'plan_sell', 'event' => 'view'])->assertNoContent();
        $this->beacon(['variant' => 'plan_sell', 'event' => 'view'], ['CF-Connecting-IP' => '203.0.113.21'])->assertNoContent();
        $this->beacon(['variant' => 'plan_sell', 'event' => 'click'])->assertNoContent();

        $row = MarketingExperimentStat::where('variant', 'plan_sell')->firstOrFail();

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
            $this->beacon(['variant' => 'plan_sell_short', 'event' => 'view'])->assertNoContent();
            $this->beacon(['variant' => 'plan_sell_short', 'event' => 'click'])->assertNoContent();
        }

        $row = MarketingExperimentStat::where('variant', 'plan_sell_short')->firstOrFail();

        $this->assertSame(1, $row->visitors);
        $this->assertSame(1, $row->clicks);

        // The dedup is per variant: the same visitor in a later session with another variant
        // did see that one too.
        $this->beacon(['variant' => 'promote_sell', 'event' => 'view'])->assertNoContent();
        $this->assertSame(1, MarketingExperimentStat::where('variant', 'promote_sell')->value('visitors'));
    }

    public function test_the_beacon_refuses_unknown_variants_and_events(): void
    {
        $this->beacon(['variant' => 'nope', 'event' => 'view'])->assertStatus(422);
        $this->beacon(['variant' => 'plan_sell', 'event' => 'purchase'])->assertStatus(422);

        $this->assertSame(0, MarketingExperimentStat::count());
    }

    public function test_the_beacon_ignores_bots_and_signed_in_users(): void
    {
        $this->beacon(['variant' => 'plan_sell', 'event' => 'view'], ['User-Agent' => 'Googlebot/2.1 (+http://www.google.com/bot.html)'])->assertNoContent();
        $this->beacon(['variant' => 'plan_sell', 'event' => 'view'], ['Accept-Language' => ''])->assertNoContent();

        $this->actingAs(User::factory()->create());
        $this->beacon(['variant' => 'plan_sell', 'event' => 'view'])->assertNoContent();

        $this->assertSame(0, MarketingExperimentStat::count());
    }

    public function test_the_beacon_does_not_start_a_session(): void
    {
        $response = $this->beacon(['variant' => 'plan_sell', 'event' => 'view'])->assertNoContent();

        $names = array_map(fn ($cookie) => $cookie->getName(), $response->headers->getCookies());

        $this->assertNotContains(config('session.cookie'), $names);
    }

    public function test_the_beacon_is_nexus_only(): void
    {
        config(['app.is_nexus' => false]);

        $this->beacon(['variant' => 'plan_sell', 'event' => 'view'])->assertNotFound();
    }

    public function test_a_signup_is_credited_to_the_variant_in_es_attribution(): void
    {
        config(['app.hosted' => true]);

        $this->withUnencryptedCookie('es_attribution', json_encode(['landing' => '/', 'hero' => 'promote_sell']))
            ->post('/sign_up', [
                'terms' => '1',
                'name' => 'Headline Visitor',
                'email' => 'headline@gmail.com',
                'password' => 'password',
            ]);

        $user = User::where('email', 'headline@gmail.com')->firstOrFail();

        $this->assertSame('promote_sell', $user->hero_variant);
        $this->assertSame('/', $user->landing_page, 'the variant must ride alongside the attribution, not replace it');
        $this->assertSame(1, HeroExperiment::stats()['promote_sell']['signups']);
    }

    /**
     * Google sign-up is roughly half of all accounts, and its controller reads attribution from
     * the session seed, which copies the utm_* values but not the variant: that comes from
     * CaptureUtmParameters::heroVariant(), here with only the cookie to read.
     */
    public function test_a_google_signup_is_credited_to_the_variant(): void
    {
        config(['app.hosted' => true]);

        $this->fakeGoogleUser('google-hero@eventschedule-test.org');

        $this->withUnencryptedCookie('es_attribution', json_encode(['landing' => '/', 'hero' => 'promote_sell']))
            ->get(route('auth.google.callback'));

        $this->assertSame('promote_sell', User::where('email', 'google-hero@eventschedule-test.org')->firstOrFail()->hero_variant);
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
     * The carrier that needs no consent. The homepage adds ?hero=<key> to a sign-up link as it is
     * pressed, and CaptureUtmParameters keeps it in the session until the account exists: no
     * cookie is sent here at all, which is every visitor who never answers the banner.
     */
    public function test_a_signup_is_credited_to_the_variant_on_the_sign_up_link(): void
    {
        config(['app.hosted' => true]);

        $this->get('/sign_up?hero=promote_sell', $this->fromOurSite());

        $this->assertSame('promote_sell', session(HeroExperiment::SESSION_KEY));

        $this->post('/sign_up', [
            'terms' => '1',
            'name' => 'Linked Visitor',
            'email' => 'linked@gmail.com',
            'password' => 'password',
        ]);

        $this->assertSame('promote_sell', User::where('email', 'linked@gmail.com')->firstOrFail()->hero_variant);
        $this->assertSame(1, HeroExperiment::stats()['promote_sell']['signups']);
        $this->assertNull(session(HeroExperiment::SESSION_KEY), 'used once, like the utm_* keys beside it');
    }

    /**
     * Its own branch in RegisteredUserController::store(): an address that already followed a
     * schedule or was invited is upgraded in place, not created.
     */
    public function test_a_stub_account_finishing_sign_up_is_credited_to_the_link(): void
    {
        config(['app.hosted' => true]);

        $stub = User::factory()->create(['email' => 'stub-upgrade@gmail.com']);
        $stub->forceFill(['password' => null, 'google_id' => null, 'google_oauth_id' => null, 'facebook_id' => null])->save();
        $this->assertTrue($stub->fresh()->isStub());

        $this->get('/sign_up?hero=promote_sell', $this->fromOurSite());

        $this->post('/sign_up', [
            'terms' => '1',
            'name' => 'Stub Visitor',
            'email' => 'stub-upgrade@gmail.com',
            'password' => 'password',
        ]);

        $this->assertFalse($stub->fresh()->isStub(), 'the sign-up did not go through');
        $this->assertSame('promote_sell', $stub->fresh()->hero_variant);
    }

    /** The session carries it through the OAuth round trip, like the claimed schedule name. */
    public function test_a_google_signup_is_credited_to_the_variant_on_the_sign_up_link(): void
    {
        config(['app.hosted' => true]);

        $this->fakeGoogleUser('google-linked@eventschedule-test.org');

        $this->withSession([HeroExperiment::SESSION_KEY => 'promote_sell'])
            ->get(route('auth.google.callback'));

        $this->assertSame('promote_sell', User::where('email', 'google-linked@eventschedule-test.org')->firstOrFail()->hero_variant);
        $this->assertNull(session(HeroExperiment::SESSION_KEY), 'used once, like the utm_* keys beside it');
    }

    /**
     * A link can be copied, which a cookie cannot. ?hero= stays in the address bar, so a pasted,
     * bookmarked or autocompleted URL would credit a headline to someone who never saw the
     * homepage. Only a click from our own site counts; those arrive with no Referer or a
     * stranger's.
     */
    public function test_a_sign_up_link_from_anywhere_but_our_own_site_is_not_credited(): void
    {
        foreach ([[], ['Referer' => ''], ['Referer' => 'https://example.org/sign-up-here'], ['Referer' => 'not a url']] as $headers) {
            $this->flushSession();

            $this->get('/sign_up?hero=promote_sell', $headers);

            $this->assertNull(session(HeroExperiment::SESSION_KEY), json_encode($headers).' was credited');
        }
    }

    /**
     * The shape production has and no other test here does: the homepage on the apex, sign-up on
     * the app host. Under is_testing the two are one host, so a check that compared hosts wrongly
     * would pass every test above and credit nobody once deployed.
     *
     * And "our own site" has to mean the marketing site, not the base domain. A failed sign-in
     * redirects back to its own URL, which then arrives with itself as the Referer, so a tagged
     * URL the browser autocompleted would be credited on the second request. A schedule's page is
     * on the base domain too, and its owner writes the links on it.
     */
    public function test_on_hosted_only_the_marketing_host_can_credit_a_link(): void
    {
        config([
            'app.hosted' => true,
            'app.is_testing' => false,
            'app.env' => 'production',
            'app.url' => 'https://eventschedule.test',
        ]);

        $cases = [
            'https://eventschedule.test/' => 'promote_sell',
            'https://www.eventschedule.test/' => 'promote_sell',
            'https://EVENTSCHEDULE.test/' => 'promote_sell',
            'https://app.eventschedule.test/login?hero=promote_sell' => null,
            'https://someschedule.eventschedule.test/' => null,
            'https://blog.eventschedule.test/' => null,
            'https://noteventschedule.test/' => null,
            'https://eventschedule.test.example.org/' => null,
        ];

        foreach ($cases as $referer => $expected) {
            $this->flushSession();

            $this->get('https://app.eventschedule.test/login?hero=promote_sell', ['Referer' => $referer])->assertOk();

            $this->assertSame($expected, session(HeroExperiment::SESSION_KEY), $referer);
        }
    }

    /**
     * The header's "Sign In" is tagged too: a social sign-in from /login creates accounts. And
     * /login must not forget it afterwards, the way it forgets the claimed schedule name.
     */
    public function test_the_sign_in_link_carries_the_variant_and_login_keeps_it(): void
    {
        $this->get('/login?hero=plan_sell_short', $this->fromOurSite());

        $this->assertSame('plan_sell_short', session(HeroExperiment::SESSION_KEY));

        $this->get('/login');

        $this->assertSame('plan_sell_short', session(HeroExperiment::SESSION_KEY));
    }

    public function test_only_a_current_variant_on_the_link_is_kept(): void
    {
        // 'plan' is a real key from round two: retired keys are not credited either.
        foreach (['hero=%3Cscript%3E', 'hero=plan', 'hero=', 'hero[]=plan_sell'] as $query) {
            $this->flushSession();

            $this->get('/sign_up?'.$query, $this->fromOurSite());

            $this->assertNull(session(HeroExperiment::SESSION_KEY), $query.' was kept');
        }
    }

    /**
     * While the cookie holds a variant the homepage shows that one instead of rolling another, so
     * every link pressed since carries the same key. The two can differ only when the link is the
     * OLDER of them: pressed before cookies were allowed, with a new headline shown afterwards.
     * So the cookie is the headline the visitor saw last.
     */
    public function test_the_cookie_wins_over_an_earlier_link(): void
    {
        config(['app.hosted' => true]);

        $this->get('/sign_up?hero=plan_sell_short', $this->fromOurSite());

        $this->withUnencryptedCookie('es_attribution', json_encode(['landing' => '/', 'hero' => 'promote_sell']))
            ->post('/sign_up', [
                'terms' => '1',
                'name' => 'Both Carriers',
                'email' => 'both@gmail.com',
                'password' => 'password',
            ]);

        $this->assertSame('promote_sell', User::where('email', 'both@gmail.com')->firstOrFail()->hero_variant);
    }

    /** A cookie from an earlier round holds a retired key, which is no pick at all. */
    public function test_the_link_is_used_when_the_cookie_holds_no_current_variant(): void
    {
        config(['app.hosted' => true]);

        $this->get('/sign_up?hero=plan_sell_short', $this->fromOurSite());

        $this->withUnencryptedCookie('es_attribution', json_encode(['landing' => '/', 'hero' => 'plan']))
            ->post('/sign_up', [
                'terms' => '1',
                'name' => 'Retired Cookie',
                'email' => 'retired@gmail.com',
                'password' => 'password',
            ]);

        $this->assertSame('plan_sell_short', User::where('email', 'retired@gmail.com')->firstOrFail()->hero_variant);
    }

    /** The test only runs on the nexus, so nowhere else has a headline to credit. */
    public function test_the_link_is_ignored_off_the_nexus(): void
    {
        config(['app.is_nexus' => false]);

        $this->get('/sign_up?hero=promote_sell', $this->fromOurSite());

        $this->assertNull(session(HeroExperiment::SESSION_KEY));
    }

    /**
     * The half the server cannot see: the homepage has to put the variant on the link. The
     * literal is also what docs/NEXUS_RELEASE.md tells the operator to look for in view-source.
     */
    public function test_the_homepage_tags_its_sign_up_and_sign_in_links(): void
    {
        $response = $this->get('/')->assertOk();

        $response->assertSee('var param = '.json_encode(HeroExperiment::LINK_PARAMETER).';', false);
        $response->assertSee('var targets = ['.json_encode(app_url('/sign_up')).', '.json_encode(app_url('/login')).'];', false);
        $response->assertSee('url.searchParams.set(param, key)', false);
        $response->assertSee("document.addEventListener('pointerdown', tag, true)", false);
        $response->assertSee("document.addEventListener('click', tag, true)", false);

        // The targets are compared whole. A substring selector would also tag a showcase card
        // for a schedule called "loginlounge" and send its page a query string.
        $response->assertSee('targets.indexOf(url.origin + url.pathname)', false);
        $response->assertDontSee('a[href*="/login"]', false);
    }

    /**
     * A click from the marketing site: its origin is all strict-origin-when-cross-origin sends.
     * Under is_testing that host and the app's are the same one.
     */
    private function fromOurSite(): array
    {
        return ['Referer' => rtrim((string) config('app.url'), '/').'/'];
    }

    private function fakeGoogleUser(string $email): void
    {
        $socialUser = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $socialUser->shouldReceive('getId')->andReturn('google-'.md5($email));
        $socialUser->shouldReceive('getEmail')->andReturn($email);
        $socialUser->shouldReceive('getName')->andReturn('Google Visitor');
        $socialUser->shouldReceive('getAvatar')->andReturn(null);
        $socialUser->user = ['locale' => 'en'];

        $provider = \Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);

        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    /**
     * A clear signup leader becomes the candidate, holds for WIN_HOLD_DAYS, and is then locked,
     * after which it gets all the traffic. Before that, nothing is locked however far ahead it is.
     */
    public function test_a_signup_leader_is_locked_only_after_holding_for_the_hold_period(): void
    {
        $stats = $this->stats(visitors: 2000, clicks: 100, signups: 20);
        $stats['plan_sell']['signups'] = 80;

        $this->travelTo('2026-10-01 12:00:00');
        $first = HeroExperiment::evaluate($stats);

        $this->assertSame('plan_sell', $first['candidate']['key']);
        $this->assertNull($first['winner']);

        $this->travelTo('2026-10-05 12:00:00');
        $this->assertNull(HeroExperiment::evaluate($stats)['winner'], 'locked before the hold period ended');

        $this->travelTo('2026-10-08 12:00:00');
        $locked = HeroExperiment::evaluate($stats);

        $this->assertSame('plan_sell', $locked['winner']['key']);
        $this->assertSame(1.0, $locked['weights']['plan_sell']);
        $this->assertSame(0.0, $locked['weights']['plan_sell_short']);
    }

    /**
     * The winner is decided on signups alone. A variant far ahead on clicks with no signup edge
     * never becomes a candidate.
     */
    public function test_a_click_leader_is_never_the_candidate(): void
    {
        $stats = $this->stats(visitors: 2000, clicks: 100, signups: 30);
        $stats['promote_sell']['clicks'] = 600;

        $this->assertNull(HeroExperiment::evaluate($stats)['candidate']);
    }

    /** Every variant has to have finished burn-in before anything can be the candidate. */
    public function test_no_candidate_while_any_variant_is_still_in_burn_in(): void
    {
        $stats = $this->stats(visitors: 2000, clicks: 100, signups: 20);
        $stats['plan_sell']['signups'] = 80;
        $stats['promote_sell'] = ['visitors' => HeroExperiment::BURN_IN_VISITORS - 1, 'clicks' => 3, 'signups' => 2];

        $this->assertNull(HeroExperiment::evaluate($stats)['candidate']);
    }

    /**
     * After burn-in a trailing variant sits at FLOOR, a couple of visitors a day at this traffic,
     * so it may never reach WIN_MIN_VISITORS. It must not hold the lock back forever when the
     * leader has clearly won: that rule made the test impossible to finish.
     */
    public function test_a_floored_variant_short_of_the_win_minimum_does_not_block_the_leader(): void
    {
        $stats = $this->stats(visitors: 2000, clicks: 100, signups: 20);
        $stats['plan_sell']['signups'] = 80;
        $stats['promote_sell'] = ['visitors' => HeroExperiment::BURN_IN_VISITORS, 'clicks' => 15, 'signups' => 3];

        $this->assertLessThan(HeroExperiment::WIN_MIN_VISITORS, $stats['promote_sell']['visitors']);
        $this->assertSame('plan_sell', HeroExperiment::evaluate($stats)['candidate']['key']);
    }

    /** The leader itself still needs WIN_MIN_VISITORS, however far ahead it looks. */
    public function test_no_candidate_until_the_leader_has_enough_visitors(): void
    {
        $stats = $this->stats(visitors: 2000, clicks: 100, signups: 20);
        $stats['plan_sell'] = ['visitors' => HeroExperiment::WIN_MIN_VISITORS - 1, 'clicks' => 100, 'signups' => 80];

        $this->assertNull(HeroExperiment::evaluate($stats)['candidate']);
    }

    public function test_a_candidate_that_loses_the_lead_is_dropped(): void
    {
        $stats = $this->stats(visitors: 2000, clicks: 100, signups: 20);
        $stats['plan_sell']['signups'] = 80;

        HeroExperiment::evaluate($stats);
        $this->assertNotNull(Setting::get(HeroExperiment::CANDIDATE_SETTING));

        $stats['plan_sell']['signups'] = 20;
        $stats['promote_sell']['signups'] = 21;

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
            ->assertSee(HeroExperiment::VARIANTS['plan_sell']['line1'])
            ->assertSee(__('messages.hero_test_reset'))
            ->assertDontSee(__('messages.hero_test_since', ['date' => now()->toDateString()]));
    }

    /**
     * A reset starts every count from zero and restarts the decision, but keeps the history: only
     * the reset day's rows go, because a day's counter cannot be split at the moment of the reset.
     */
    public function test_a_reset_starts_the_counts_from_zero_and_keeps_history(): void
    {
        $this->travelTo('2026-10-01 12:00:00');

        MarketingExperimentStat::create(['experiment' => HeroExperiment::EXPERIMENT, 'variant' => 'plan_sell', 'date' => '2026-09-30', 'visitors' => 40, 'clicks' => 4]);
        MarketingExperimentStat::create(['experiment' => HeroExperiment::EXPERIMENT, 'variant' => 'plan_sell', 'date' => '2026-10-01', 'visitors' => 10, 'clicks' => 1]);
        User::factory()->create(['hero_variant' => 'plan_sell']);
        Setting::set(HeroExperiment::CANDIDATE_SETTING, HeroExperiment::setHash().'|plan_sell|2026-09-30');
        Setting::set(HeroExperiment::WINNER_SETTING, HeroExperiment::setHash().'|plan_sell|2026-09-30');

        $this->assertSame(['visitors' => 50, 'clicks' => 5, 'signups' => 1], HeroExperiment::stats()['plan_sell']);

        $this->travelTo('2026-10-01 15:00:00');
        $this->resetAsAdmin(['range' => 'last_7_days'])
            ->assertRedirect(route('admin.growth', ['range' => 'last_7_days']).'#hero-test')
            ->assertSessionHas('message', __('messages.hero_test_reset_done'));

        foreach (HeroExperiment::stats() as $key => $row) {
            $this->assertSame(['visitors' => 0, 'clicks' => 0, 'signups' => 0], $row, "{$key} was not reset");
        }
        $this->assertNull(Setting::get(HeroExperiment::CANDIDATE_SETTING));
        $this->assertNull(Setting::get(HeroExperiment::WINNER_SETTING));
        $this->assertTrue(HeroExperiment::forPage()['running'], 'a locked winner must not survive a reset');

        // History stays; only the day the reset split is gone.
        $this->assertTrue(MarketingExperimentStat::whereDate('date', '2026-09-30')->exists());
        $this->assertFalse(MarketingExperimentStat::whereDate('date', '2026-10-01')->exists());
        $this->assertSame(1, User::where('hero_variant', 'plan_sell')->count());

        // Everything after the reset counts, on the reset day and after it. Signed out first: the
        // beacon ignores signed-in users, and the client is still the admin who reset.
        auth()->logout();
        $this->travelTo('2026-10-01 16:00:00');
        $this->beacon(['variant' => 'plan_sell', 'event' => 'view'])->assertNoContent();
        User::factory()->create(['hero_variant' => 'plan_sell']);

        $this->travelTo('2026-10-02 09:00:00');
        $this->beacon(['variant' => 'plan_sell', 'event' => 'view'], ['CF-Connecting-IP' => '203.0.113.21'])->assertNoContent();

        $this->assertSame(['visitors' => 2, 'clicks' => 0, 'signups' => 1], HeroExperiment::stats()['plan_sell']);
        $this->assertSame('2026-10-01', HeroExperiment::report()['reset_at']);
    }

    public function test_the_growth_page_says_when_the_counts_start(): void
    {
        $this->travelTo('2026-10-01 15:00:00');
        $this->resetAsAdmin();

        $this->get('/admin/growth')
            ->assertOk()
            ->assertSee(__('messages.hero_test_since', ['date' => '2026-10-01']));
    }

    public function test_only_an_admin_can_reset_and_only_on_the_nexus(): void
    {
        config(['app.hosted' => true]);
        MarketingExperimentStat::create(['experiment' => HeroExperiment::EXPERIMENT, 'variant' => 'plan_sell', 'date' => now()->toDateString(), 'visitors' => 10, 'clicks' => 1]);

        $this->actingAs(User::factory()->create(['email_verified_at' => now()]))
            ->post('/admin/growth/hero-test/reset')
            ->assertRedirect();

        // A selfhosted SaaS: hosted, but not the install whose homepage runs the test.
        config(['app.is_nexus' => false]);
        $this->resetAsAdmin()->assertNotFound();

        // A plain selfhost.
        config(['app.is_nexus' => false]);
        $this->resetAsAdmin(hosted: false)->assertNotFound();

        $this->assertNull(Setting::get(HeroExperiment::RESET_SETTING));
        $this->assertSame(1, MarketingExperimentStat::count());
    }

    private function resetAsAdmin(array $data = [], bool $hosted = true)
    {
        config(['app.hosted' => $hosted]);

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $admin->forceFill(['is_admin' => true])->save();

        // The admin group re-auths; without this key the request bounces to confirm-password.
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($admin)
            ->post('/admin/growth/hero-test/reset', $data);
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
