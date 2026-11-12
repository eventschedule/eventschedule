<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\MarketingDailyStat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Why an organizer came to sign up (users.arrival_intent, signups.arrival_intent in the growth
 * export). Organizers who arrived from a ticketing, comparison or pricing page made a paid ticket
 * type at about four times the homepage's rate, and every sign-up link on those pages was a bare
 * /sign_up, so nothing after the click knew. The seller pages now pass ?intent=, a plan's button
 * on /pricing ?intent=pro|enterprise, and the guest page's "Create your own" card ?ref=gp.
 */
class SignupArrivalIntentTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_the_sign_up_page_keeps_the_intent_the_link_carried(): void
    {
        $this->get(route('sign_up', ['intent' => 'sell']))->assertOk();
        $this->assertSame('sell', session('signup_arrival_intent'));

        $this->flushSession();
        $this->get(route('sign_up', ['ref' => 'gp']))->assertOk();
        $this->assertSame('guest_page', session('signup_arrival_intent'));
    }

    public function test_anything_outside_the_vocabulary_is_ignored(): void
    {
        foreach (['buy', 'SELL', '<script>', ''] as $value) {
            $this->flushSession();
            $this->get(route('sign_up', ['intent' => $value]))->assertOk();
            $this->assertNull(session('signup_arrival_intent'), var_export($value, true).' was kept');
        }

        $this->flushSession();
        $this->get(route('sign_up').'?intent[]=sell')->assertOk();
        $this->assertNull(session('signup_arrival_intent'), 'an array was kept');
    }

    public function test_signing_up_records_it_on_the_account(): void
    {
        $this->withSession(['signup_arrival_intent' => 'pro'])
            ->post('/sign_up', [
                'terms' => '1',
                'name' => 'Seller',
                'email' => 'seller@gmail.com',
                'password' => 'password',
            ])->assertSessionHasNoErrors();

        $this->assertSame('pro', User::whereEmail('seller@gmail.com')->firstOrFail()->arrival_intent);
    }

    public function test_signing_up_with_google_records_it_too(): void
    {
        $socialUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
        $socialUser->shouldReceive('getId')->andReturn('google-oauth-intent');
        $socialUser->shouldReceive('getEmail')->andReturn('googleseller@gmail.com');
        $socialUser->shouldReceive('getName')->andReturn('Google Seller');
        $socialUser->shouldReceive('getAvatar')->andReturn(null);
        $socialUser->user = ['locale' => 'en'];

        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->withSession(['signup_arrival_intent' => 'sell'])->get(route('auth.google.callback'));

        $this->assertSame('sell', User::whereEmail('googleseller@gmail.com')->firstOrFail()->arrival_intent);
    }

    public function test_the_login_page_forgets_it(): void
    {
        $this->withSession(['signup_arrival_intent' => 'sell'])->get(route('login'))->assertOk();

        $this->assertNull(session('signup_arrival_intent'));
    }

    /** The links themselves, on the pages a seller arrives at. */
    public function test_the_seller_pages_link_to_sign_up_with_their_intent(): void
    {
        config(['marketing.wall_cache_seconds' => 0]);

        foreach (['marketing.ticketing', 'marketing.stripe', 'marketing.compare'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('sign_up?intent=sell', false);
        }

        $this->get(route('marketing.pricing'))->assertOk()
            ->assertSee('sign_up?intent=pro', false)
            ->assertSee('sign_up?intent=enterprise', false);
    }

    /**
     * A schedule named "Testing- PBWDA" put its event on the homepage wall: the test-name rule is
     * anchored at both ends. A dash or an underscore after "test" or "testing" now marks it; a
     * space alone does not, because those are real names.
     */
    public function test_a_test_schedule_with_a_label_after_it_is_kept_off_discovery(): void
    {
        $names = [
            'Testing- PBWDA' => true,
            'test_venue' => true,
            'Test - 2' => true,
            'Test Kitchen' => false,
            'Testing Grounds' => false,
            'Testival' => false,
        ];

        foreach ($names as $name => $hidden) {
            $role = $this->createRole($this->createOwner(), 'venue', ['name' => $name]);
            $event = $this->createEvent($role, ['name' => 'Fall Fair '.$name]);

            $shown = Event::query()->whereKey($event->id)->excludeLikelyTest()->exists();

            $this->assertSame(! $hidden, $shown, $name.($hidden ? ' was shown' : ' was hidden'));
        }
    }

    /**
     * The sign-up page's Google exit (marketing_daily_stats.signup_google_starts): about 78% of
     * the people who open /sign_up never make an account, and only the email path was counted.
     */
    public function test_pressing_google_on_the_sign_up_page_is_counted_once_a_day(): void
    {
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('scopes')->andReturnSelf();
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('redirect')->andReturn(new RedirectResponse('https://accounts.google.com/o/oauth2/auth'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $browser = ['User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0 Safari/537.36'];

        $this->withHeaders($browser + ['Referer' => route('sign_up')])->get(route('auth.google'))->assertRedirect();
        $this->withHeaders($browser + ['Referer' => route('sign_up')])->get(route('auth.google'))->assertRedirect();
        $this->assertSame(1, (int) MarketingDailyStat::sum('signup_google_starts'), 'one visitor, one count');

        // Signing in with Google from /login is not a sign-up.
        $this->withHeaders(['User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) Firefox/131.0', 'Referer' => route('login')])
            ->get(route('auth.google'))->assertRedirect();
        $this->assertSame(1, (int) MarketingDailyStat::sum('signup_google_starts'));
    }
}
