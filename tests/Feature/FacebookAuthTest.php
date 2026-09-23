<?php

namespace Tests\Feature;

use App\Models\User;
use App\Utils\SocialLoginUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\FacebookProvider;
use Laravel\Socialite\Two\GoogleProvider;
use Mockery;
use Tests\TestCase;

/**
 * "Continue with Facebook": hidden until configured, then the sign-in, linking and Settings flows.
 */
class FacebookAuthTest extends TestCase
{
    use RefreshDatabase;

    private function configureFacebook(?string $id = 'fb-app-id', ?string $secret = 'fb-app-secret'): void
    {
        config(['services.facebook.client_id' => $id, 'services.facebook.client_secret' => $secret]);
    }

    /**
     * Stand in for the Facebook round trip so the callback can be driven directly.
     */
    private function fakeFacebookUser(string $id, ?string $email, string $name = 'Facebook Person'): void
    {
        $socialUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
        $socialUser->shouldReceive('getId')->andReturn($id);
        $socialUser->shouldReceive('getEmail')->andReturn($email);
        $socialUser->shouldReceive('getName')->andReturn($name);
        $socialUser->shouldReceive('getAvatar')->andReturn('https://platform-lookaside.fbsbx.com/avatar.jpg');
        $socialUser->user = [];

        $provider = Mockery::mock(FacebookProvider::class);
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('facebook')->andReturn($provider);
    }

    private function fakeGoogleUser(string $id, string $email): void
    {
        $socialUser = Mockery::mock(\Laravel\Socialite\Two\User::class);
        $socialUser->shouldReceive('getId')->andReturn($id);
        $socialUser->shouldReceive('getEmail')->andReturn($email);
        $socialUser->shouldReceive('getName')->andReturn('Google Person');
        $socialUser->shouldReceive('getAvatar')->andReturn(null);
        $socialUser->user = ['locale' => 'en'];

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    /**
     * Every page a Facebook control can appear on, rendered for a signed-out and a signed-in
     * visitor. Selfhost with registration open, so the sign-up page renders its social block.
     */
    private function renderedSurfaces(User $user): string
    {
        config(['app.hosted' => false, 'app.allow_registration' => true]);

        $html = $this->get(route('login'))->assertOk()->getContent();
        $html .= $this->get(route('sign_up'))->assertOk()->getContent();
        $html .= $this->actingAs($user)->get(route('profile.edit'))->assertOk()->getContent();

        return $html;
    }

    public static function unconfiguredProvider(): array
    {
        return [
            'neither value' => [null, null],
            'app id without its secret' => ['fb-app-id', null],
            'secret without an app id' => [null, 'fb-app-secret'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('unconfiguredProvider')]
    public function test_no_facebook_ui_anywhere_until_both_values_are_set(?string $id, ?string $secret): void
    {
        $this->configureFacebook($id, $secret);
        $user = User::factory()->create(['facebook_id' => 'fb-linked', 'password' => null]);

        $html = $this->renderedSurfaces($user);

        $this->assertStringNotContainsString('/auth/facebook', $html);
        // Not the bare anchor name: the Help button's anchor map lists it on every AP page.
        $this->assertStringNotContainsString('id="section-facebook"', $html);
        $this->assertStringNotContainsString('data-section="section-facebook"', $html);
        $this->assertStringNotContainsString(__('messages.log_in_with_facebook'), $html);
        $this->assertStringNotContainsString(__('messages.continue_with_facebook'), $html);
        $this->assertStringNotContainsString(__('messages.verify_with_facebook'), $html);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('unconfiguredProvider')]
    public function test_every_facebook_route_404s_until_both_values_are_set(?string $id, ?string $secret): void
    {
        $this->configureFacebook($id, $secret);

        $this->get(route('auth.facebook'))->assertNotFound();
        $this->get(route('auth.facebook.callback'))->assertNotFound();

        $this->actingAs(User::factory()->create());
        $this->get(route('auth.facebook.connect'))->assertNotFound();
        $this->get(route('auth.facebook.connect.callback'))->assertNotFound();
        $this->get(route('auth.facebook.set_password'))->assertNotFound();
        $this->get(route('auth.facebook.set_password.callback'))->assertNotFound();
        $this->post(route('auth.facebook.disconnect'))->assertNotFound();
    }

    public function test_facebook_ui_appears_once_configured(): void
    {
        $this->configureFacebook();
        $user = User::factory()->create(['facebook_id' => 'fb-linked', 'password' => null]);

        $html = $this->renderedSurfaces($user);

        $this->assertStringContainsString(__('messages.log_in_with_facebook'), $html);
        $this->assertStringContainsString(__('messages.continue_with_facebook'), $html);
        $this->assertStringContainsString('id="section-facebook"', $html);
        $this->assertStringContainsString(__('messages.verify_with_facebook'), $html);
    }

    public function test_a_new_facebook_user_gets_a_verified_account(): void
    {
        $this->configureFacebook();
        $this->fakeFacebookUser('fb-new-1', 'NewFace@Example.com', 'New Face');

        $this->get(route('auth.facebook.callback'))->assertRedirect();

        $this->assertAuthenticated();
        $user = User::where('email', 'newface@example.com')->firstOrFail();
        $this->assertSame('fb-new-1', $user->facebook_id);
        $this->assertNull($user->google_oauth_id);
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);
        // The picture URL is signed and expires, so it is not kept.
        $this->assertEmpty($user->profile_image_url);
    }

    public function test_a_linked_facebook_user_signs_in_and_the_method_is_remembered(): void
    {
        $this->configureFacebook();
        $existing = User::factory()->create(['facebook_id' => 'fb-known']);
        // A changed email at Facebook must not matter: the account id is what is matched.
        $this->fakeFacebookUser('fb-known', 'someone-else@example.com');

        $this->get(route('auth.facebook.callback'))
            ->assertCookie(SocialLoginUtils::LAST_METHOD_COOKIE, 'facebook');

        $this->assertAuthenticatedAs($existing);
    }

    public function test_an_invited_placeholder_is_linked_on_email(): void
    {
        $this->configureFacebook();
        $stub = User::factory()->create(['email' => 'invited@example.com', 'password' => null, 'email_verified_at' => null]);
        $this->assertTrue($stub->isStub());
        $this->fakeFacebookUser('fb-invited', 'invited@example.com');

        $this->get(route('auth.facebook.callback'));

        $this->assertAuthenticatedAs($stub);
        $this->assertSame('fb-invited', $stub->fresh()->facebook_id);
        $this->assertNotNull($stub->fresh()->email_verified_at);
    }

    public function test_an_account_with_a_password_is_not_linked_until_its_owner_logs_in(): void
    {
        $this->configureFacebook();
        $owner = User::factory()->create(['email' => 'owner@example.com']);
        $this->assertTrue($owner->hasPassword());
        $this->fakeFacebookUser('fb-owner', 'owner@example.com');

        $this->get(route('auth.facebook.callback'))
            ->assertRedirect(route('login', ['email' => 'owner@example.com']))
            ->assertSessionHas('social_link_pending', 'facebook');

        $this->assertGuest();
        $this->assertNull($owner->fresh()->facebook_id);

        $this->post(route('login'), ['email' => 'owner@example.com', 'password' => 'password'])
            ->assertSessionHas('message', __('messages.facebook_account_connected'))
            ->assertCookie(SocialLoginUtils::LAST_METHOD_COOKIE, 'password');

        $this->assertAuthenticatedAs($owner);
        $this->assertSame('fb-owner', $owner->fresh()->facebook_id);
    }

    public function test_the_login_page_explains_the_pending_link(): void
    {
        $this->configureFacebook();
        User::factory()->create();

        $this->withSession(['social_link_pending' => 'facebook'])
            ->get(route('login'))
            ->assertSee(__('messages.facebook_link_requires_login'));
    }

    public function test_a_google_account_with_no_password_is_linked_after_google_sign_in(): void
    {
        $this->configureFacebook();
        $owner = User::factory()->create(['email' => 'gowner@example.com', 'password' => null, 'google_oauth_id' => 'g-owner']);
        $this->fakeFacebookUser('fb-gowner', 'gowner@example.com');

        $this->get(route('auth.facebook.callback'))->assertSessionHas('social_link_pending', 'facebook');
        $this->assertNull($owner->fresh()->facebook_id);

        $this->fakeGoogleUser('g-owner', 'gowner@example.com');
        $this->get(route('auth.google.callback'))
            ->assertSessionHas('message', __('messages.facebook_account_connected'));

        $this->assertSame('fb-gowner', $owner->fresh()->facebook_id);
    }

    public function test_a_pending_link_survives_the_two_factor_step(): void
    {
        $this->configureFacebook();
        $google2fa = new \PragmaRX\Google2FA\Google2FA;
        $secret = $google2fa->generateSecretKey();

        $owner = User::factory()->create(['email' => 'secure@example.com']);
        // Not fillable: TwoFactorController sets these explicitly.
        $owner->two_factor_secret = $secret;
        $owner->two_factor_confirmed_at = now();
        $owner->save();
        $this->assertTrue($owner->fresh()->hasTwoFactorEnabled());

        $this->fakeFacebookUser('fb-secure', 'secure@example.com');
        $this->get(route('auth.facebook.callback'))->assertSessionHas('social_link_pending', 'facebook');

        $this->post(route('login'), ['email' => 'secure@example.com', 'password' => 'password'])
            ->assertRedirect(route('two-factor.challenge'));
        $this->assertNull($owner->fresh()->facebook_id, 'must not link before the second factor');

        $this->post(route('two-factor.challenge'), ['code' => $google2fa->getCurrentOtp($secret)])
            ->assertSessionHas('message', __('messages.facebook_account_connected'));

        $this->assertAuthenticatedAs($owner);
        $this->assertSame('fb-secure', $owner->fresh()->facebook_id);
    }

    public function test_an_expired_pending_link_is_not_applied(): void
    {
        $owner = User::factory()->create(['email' => 'late@example.com']);
        SocialLoginUtils::stashPendingLink('facebook', 'fb-late', 'late@example.com');

        $this->travel(SocialLoginUtils::PENDING_LINK_TTL_SECONDS + 1)->seconds();

        $this->assertNull(SocialLoginUtils::consumePendingLink($owner));
        $this->assertNull($owner->fresh()->facebook_id);
    }

    public function test_a_pending_link_is_not_applied_to_a_different_account(): void
    {
        $other = User::factory()->create(['email' => 'other@example.com']);
        SocialLoginUtils::stashPendingLink('facebook', 'fb-x', 'owner@example.com');

        $this->assertNull(SocialLoginUtils::consumePendingLink($other));
        $this->assertNull($other->fresh()->facebook_id);
        // One shot: pulled even though it did not link.
        $this->assertFalse(session()->has(SocialLoginUtils::PENDING_LINK_KEY));
    }

    public function test_a_pending_link_is_not_applied_when_the_facebook_account_was_taken_meanwhile(): void
    {
        $owner = User::factory()->create(['email' => 'owner@example.com']);
        User::factory()->create(['facebook_id' => 'fb-taken']);
        SocialLoginUtils::stashPendingLink('facebook', 'fb-taken', 'owner@example.com');

        $this->assertNull(SocialLoginUtils::consumePendingLink($owner));
        $this->assertNull($owner->fresh()->facebook_id);
    }

    public function test_an_email_already_linked_to_another_facebook_account_is_refused(): void
    {
        $this->configureFacebook();
        $owner = User::factory()->create(['email' => 'owner@example.com', 'facebook_id' => 'fb-first']);
        $this->fakeFacebookUser('fb-second', 'owner@example.com');

        $this->get(route('auth.facebook.callback'))->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertSame('fb-first', $owner->fresh()->facebook_id);
    }

    public function test_no_email_from_facebook_offers_a_retry_instead_of_an_account(): void
    {
        $this->configureFacebook();
        User::factory()->create();
        $this->fakeFacebookUser('fb-phone-only', null);

        $this->get(route('auth.facebook.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHas('social_email_required', 'facebook');

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['facebook_id' => 'fb-phone-only']);

        $this->withSession(['social_email_required' => 'facebook'])
            ->get(route('login'))
            ->assertSee(__('messages.facebook_email_required'))
            ->assertSee(route('auth.facebook', ['rerequest' => 1]), false);
    }

    public function test_the_retry_asks_facebook_for_the_declined_permission_again(): void
    {
        $this->configureFacebook();

        $location = $this->get(route('auth.facebook', ['rerequest' => 1]))->headers->get('Location');
        $this->assertStringContainsString('auth_type=rerequest', $location);

        $location = $this->get(route('auth.facebook'))->headers->get('Location');
        $this->assertStringNotContainsString('auth_type=rerequest', $location);
    }

    public function test_cancelling_the_dialog_returns_without_an_error(): void
    {
        $this->configureFacebook();

        foreach (['auth.facebook.callback', 'auth.google.callback'] as $route) {
            $this->get(route($route, ['error' => 'access_denied', 'error_reason' => 'user_denied']))
                ->assertRedirect(route('login'))
                ->assertSessionHasNoErrors();
        }
    }

    public function test_a_closed_selfhost_does_not_create_accounts_through_facebook(): void
    {
        User::factory()->create();
        config([
            'app.hosted' => false,
            'app.allow_registration' => false,
            'app.is_testing' => false,
            'app.url' => config('app.url') ?: 'http://localhost',
        ]);
        $this->configureFacebook();
        $this->fakeFacebookUser('fb-stranger', 'stranger@example.com');

        $this->get(route('auth.facebook.callback'))->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'stranger@example.com']);
    }

    public function test_connect_from_settings_links_the_account(): void
    {
        $this->configureFacebook();
        $user = User::factory()->create();
        $this->fakeFacebookUser('fb-connect', 'different@example.com');

        $this->actingAs($user)->get(route('auth.facebook.connect.callback'))
            ->assertRedirect(route('profile.edit').'#section-facebook')
            ->assertSessionHas('message', __('messages.facebook_account_connected'));

        $this->assertSame('fb-connect', $user->fresh()->facebook_id);
    }

    public function test_connect_refuses_a_facebook_account_linked_to_someone_else(): void
    {
        $this->configureFacebook();
        User::factory()->create(['facebook_id' => 'fb-theirs']);
        $user = User::factory()->create();
        $this->fakeFacebookUser('fb-theirs', 'theirs@example.com');

        $this->actingAs($user)->get(route('auth.facebook.connect.callback'))
            ->assertSessionHas('error', __('messages.facebook_account_in_use'));

        $this->assertNull($user->fresh()->facebook_id);
    }

    public function test_a_google_only_user_connects_facebook_from_settings(): void
    {
        $this->configureFacebook();
        $user = User::factory()->create(['password' => null, 'google_oauth_id' => 'g-x']);
        $this->fakeFacebookUser('fb-later', 'not-the-same@example.com');

        $this->actingAs($user)->get(route('auth.facebook.connect.callback'))
            ->assertSessionHas('message', __('messages.facebook_account_connected'));

        $user->refresh();
        $this->assertSame('fb-later', $user->facebook_id);
        $this->assertSame('g-x', $user->google_oauth_id);
    }

    /**
     * Calendar sync is its own OAuth connection, behind `auth` + `verified`, and never looks at how
     * the person signed in. A Facebook signup must clear `verified` on the strength of the email
     * Facebook confirmed, or the Connect Google Calendar button leads to the verification wall.
     */
    public function test_a_facebook_signup_can_start_calendar_sync(): void
    {
        $this->configureFacebook();
        config([
            'services.google.client_id' => 'google-client-id',
            'services.google.client_secret' => 'google-client-secret',
            'services.google.redirect' => 'https://eventschedule.test/google-calendar/callback',
        ]);
        $this->fakeFacebookUser('fb-sync', 'syncer@example.com');

        $this->get(route('auth.facebook.callback'));
        $user = User::where('facebook_id', 'fb-sync')->firstOrFail();
        $this->assertAuthenticatedAs($user);

        $this->get(route('profile.edit'))
            ->assertOk()
            ->assertSee(route('google.calendar.redirect'), false);

        $location = $this->get(route('google.calendar.redirect'))->assertRedirect()->headers->get('Location');
        $this->assertStringStartsWith('https://accounts.google.com/', $location);
    }

    public function test_facebook_cannot_be_disconnected_when_it_is_the_only_way_in(): void
    {
        $this->configureFacebook();
        $user = User::factory()->create(['facebook_id' => 'fb-only', 'password' => null]);

        $this->actingAs($user)->post(route('auth.facebook.disconnect'))
            ->assertSessionHas('error', __('messages.cannot_disconnect_facebook_no_other_login'));

        $this->assertSame('fb-only', $user->fresh()->facebook_id);
    }

    public function test_facebook_can_be_disconnected_while_a_password_or_google_remains(): void
    {
        $this->configureFacebook();
        $withPassword = User::factory()->create(['facebook_id' => 'fb-a']);
        $withGoogle = User::factory()->create(['facebook_id' => 'fb-b', 'password' => null, 'google_oauth_id' => 'g-b']);

        foreach ([$withPassword, $withGoogle] as $user) {
            $this->actingAs($user)->post(route('auth.facebook.disconnect'))
                ->assertSessionHas('message', __('messages.facebook_account_disconnected'));

            $this->assertNull($user->fresh()->facebook_id);
        }
    }

    public function test_google_can_be_disconnected_while_facebook_remains(): void
    {
        $this->configureFacebook();
        $user = User::factory()->create(['google_oauth_id' => 'g-c', 'password' => null, 'facebook_id' => 'fb-c']);

        $this->actingAs($user)->post(route('auth.google.disconnect'));

        $this->assertNull($user->fresh()->google_oauth_id);
    }

    /**
     * Once Facebook login is switched off its routes 404, so a stored facebook_id is no way in.
     * Letting Google go on the strength of it would leave a password-less account with none.
     */
    public function test_google_cannot_be_disconnected_on_the_strength_of_a_disabled_facebook_link(): void
    {
        $this->configureFacebook(null, null);
        $user = User::factory()->create(['google_oauth_id' => 'g-d', 'password' => null, 'facebook_id' => 'fb-d']);

        $this->actingAs($user)->post(route('auth.google.disconnect'))
            ->assertSessionHas('error', __('messages.cannot_disconnect_google_no_password'));

        $this->assertSame('g-d', $user->fresh()->google_oauth_id);
    }

    public function test_verify_with_facebook_unlocks_setting_a_password(): void
    {
        $this->configureFacebook();
        $user = User::factory()->create(['facebook_id' => 'fb-me', 'password' => null]);
        $this->fakeFacebookUser('fb-me', $user->email);

        $this->actingAs($user)->get(route('auth.facebook.set_password.callback'))
            ->assertSessionHas('can_set_password');
    }

    public function test_verify_with_a_different_facebook_account_is_refused(): void
    {
        $this->configureFacebook();
        $user = User::factory()->create(['facebook_id' => 'fb-me', 'password' => null]);
        $this->fakeFacebookUser('fb-someone-else', $user->email);

        $this->actingAs($user)->get(route('auth.facebook.set_password.callback'))
            ->assertSessionHasErrorsIn('updatePassword', 'password')
            ->assertSessionMissing('can_set_password');
    }

    public function test_the_login_page_marks_the_last_used_button(): void
    {
        $this->configureFacebook();
        config(['services.google.client_id' => 'google-id']);
        User::factory()->create();

        $html = $this->withCookie(SocialLoginUtils::LAST_METHOD_COOKIE, 'facebook')
            ->get(route('login'))->getContent();

        $this->assertSame(1, substr_count($html, 'data-last-used'));
        $this->assertMatchesRegularExpression('#href="[^"]*/auth/facebook"[^>]*>(?:(?!</a>).)*data-last-used#s', $html);

        $html = $this->withCookie(SocialLoginUtils::LAST_METHOD_COOKIE, 'password')
            ->get(route('login'))->getContent();
        $this->assertStringNotContainsString('data-last-used', $html);
    }
}
