<?php

namespace Tests\Feature;

use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * The account-wide email opt-out, users.is_subscribed, behind the signed /user/unsubscribe link.
 *
 * The GET used to write on its own, which a corporate mail scanner fetching the link would trigger
 * for somebody who never clicked. It now renders a confirm button and the POST acts - the same
 * split every other unsubscribe in the app already had.
 */
class UserUnsubscribeTest extends TestCase
{
    use RefreshDatabase;

    private function subscribedUser(array $attrs = []): User
    {
        return User::factory()->create(array_merge(['is_subscribed' => true], $attrs));
    }

    public function test_the_get_renders_a_confirm_page_and_writes_nothing(): void
    {
        $user = $this->subscribedUser();

        $html = $this->get(UrlUtils::userUnsubscribeUrl($user->email))
            ->assertOk()
            ->assertSee(__('messages.account_unsubscribe_heading'))
            ->assertSee(__('messages.account_unsubscribe_body'))
            ->getContent();

        $this->assertTrue((bool) $user->refresh()->is_subscribed, 'a GET must never unsubscribe');
        $this->assertMatchesRegularExpression('~<meta name="robots" content="[^"]*noindex~', $html);
    }

    public function test_the_confirm_form_posts_to_the_signed_url_and_unsubscribes(): void
    {
        $user = $this->subscribedUser();

        $html = $this->get(UrlUtils::userUnsubscribeUrl($user->email))->getContent();

        preg_match('~<form method="POST" action="([^"]+)"~', $html, $m);
        $this->assertNotEmpty($m, 'the confirm page must render a form');

        $this->post(html_entity_decode($m[1]))
            ->assertOk()
            ->assertSee(__('messages.subscription_unsubscribed_heading'))
            ->assertSee(route('profile.edit'), false);

        $this->assertFalse((bool) $user->refresh()->is_subscribed);
    }

    /** RFC 8058 one-click: the mail provider POSTs to the List-Unsubscribe URL itself. */
    public function test_a_one_click_post_to_the_link_unsubscribes(): void
    {
        $user = $this->subscribedUser();

        $this->post(UrlUtils::userUnsubscribeUrl($user->email), ['List-Unsubscribe' => 'One-Click'])->assertOk();

        $this->assertFalse((bool) $user->refresh()->is_subscribed);
    }

    /** Tests skip CSRF altogether, so the exemption the one-click POST depends on is pinned here. */
    public function test_the_unsubscribe_post_is_exempt_from_csrf(): void
    {
        $middleware = $this->app->make(ValidateCsrfToken::class);
        $inExceptArray = new \ReflectionMethod($middleware, 'inExceptArray');

        $this->assertTrue($inExceptArray->invoke($middleware, Request::create('/user/unsubscribe', 'POST')));
    }

    /**
     * Keyed per signed address, not per IP: one-click POSTs arrive from the mail provider's shared
     * egress hosts, and a per-IP budget there is what turns an unsubscribe into a 429. The
     * throttle middleware is off under APP_TESTING, so the limiter is driven directly.
     */
    public function test_the_limiter_keys_on_the_address(): void
    {
        $limiter = RateLimiter::limiter('user_unsubscribe');

        $a = $limiter(Request::create('/user/unsubscribe?email=YQ%3D%3D&sig=x', 'POST'));
        $b = $limiter(Request::create('/user/unsubscribe?email=Yg%3D%3D&sig=x', 'POST'));

        $this->assertNotSame($a->key, $b->key);
        // An array value must not throw inside the limiter, before the controller can refuse it.
        $this->assertNotEmpty($limiter(Request::create('/user/unsubscribe?email[]=x', 'POST'))->key);
    }

    public function test_a_bad_signature_writes_nothing_and_says_why(): void
    {
        $user = $this->subscribedUser();
        $email = base64_encode($user->email);

        foreach (['get', 'post'] as $method) {
            $this->$method(route('user.unsubscribe', ['email' => $email, 'sig' => 'forged']))
                ->assertRedirect(route('role.show_unsubscribe'))
                ->assertSessionHasErrors(['email' => __('messages.invalid_unsubscribe_link')]);
        }

        $this->assertTrue((bool) $user->refresh()->is_subscribed);
    }

    /** ?email[]= used to reach a string-typed verifier and 500. */
    public function test_an_array_email_is_refused_not_a_500(): void
    {
        $this->get('/user/unsubscribe?email[]=x&sig=y')->assertRedirect(route('role.show_unsubscribe'));
        $this->post('/user/unsubscribe?email[]=x&sig=y')->assertRedirect(route('role.show_unsubscribe'));
    }

    public function test_the_honeypot_blocks_the_post(): void
    {
        $user = $this->subscribedUser();

        $this->post(UrlUtils::userUnsubscribeUrl($user->email), ['website' => 'http://spam.example'])
            ->assertRedirect(route('role.show_unsubscribe'));

        $this->assertTrue((bool) $user->refresh()->is_subscribed);
    }

    public function test_the_page_renders_in_the_language_the_link_carries(): void
    {
        $user = $this->subscribedUser();

        $this->get(UrlUtils::userUnsubscribeUrl($user->email, 'he'))
            ->assertOk()
            ->assertSee(trans('messages.account_unsubscribe_heading', [], 'he'));
    }

    /**
     * The old footer links in event request emails were unsigned GETs carrying ?email=, and the
     * page reported success on the parameter alone. They must not claim anything any more.
     */
    public function test_an_old_unsigned_link_no_longer_claims_success(): void
    {
        $this->get(route('role.show_unsubscribe', ['email' => base64_encode('someone@example.com')]))
            ->assertOk()
            ->assertDontSee(__('messages.unsubscribed_message'))
            ->assertSee('name="email"', false);
    }

    public function test_the_schedule_level_form_still_reports_success(): void
    {
        $this->followingRedirects()
            ->post(route('role.unsubscribe'), ['email' => 'someone@example.com'])
            ->assertOk()
            ->assertSee(__('messages.unsubscribed_message'));
    }

    /** The way back: nothing else ever sets is_subscribed to true again. */
    public function test_the_profile_toggle_round_trips(): void
    {
        $user = User::factory()->create(['is_subscribed' => false, 'email_verified_at' => now()]);

        $payload = [
            'name' => $user->name,
            'email' => $user->email,
            'timezone' => 'America/New_York',
            'language_code' => 'en',
        ];

        $this->actingAs($user)->patch(route('profile.update'), $payload + ['is_subscribed' => '1']);
        $this->assertTrue((bool) $user->refresh()->is_subscribed);

        $this->actingAs($user)->patch(route('profile.update'), $payload + ['is_subscribed' => '0']);
        $this->assertFalse((bool) $user->refresh()->is_subscribed);

        // A save from a form without the field leaves it alone.
        $user->update(['is_subscribed' => true]);
        $this->actingAs($user)->patch(route('profile.update'), $payload);
        $this->assertTrue((bool) $user->refresh()->is_subscribed);
    }
}
