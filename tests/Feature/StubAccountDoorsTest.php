<?php

namespace Tests\Feature;

use App\Mail\SetPassword;
use App\Models\Role;
use App\Models\User;
use App\Utils\StubAccountUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The ways back in for somebody who has an account but has never had a password.
 *
 * Several paths mint one - a confirmed newsletter subscription, an owner's newsletter import, a
 * team invite - and until this existed every one of them was a dead end at the login form, because
 * EloquentUserProvider::validateCredentials() returns false on a null hash and "wrong password" was
 * the only thing the app could say.
 */
class StubAccountDoorsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    private function stub(string $email = 'fan@fans.test'): User
    {
        return User::factory()->create([
            'email' => $email,
            'password' => null,
            'email_verified_at' => null,
            'signup_intent' => 'subscriber',
        ]);
    }

    // -------------------------------------------------------------------------------------
    // The login door.
    // -------------------------------------------------------------------------------------

    public function test_login_tells_a_passwordless_account_that_it_has_no_password(): void
    {
        $this->stub();

        $this->from(route('login'))
            ->post(route('login'), ['email' => 'fan@fans.test', 'password' => 'anything'])
            ->assertSessionHasErrors(['email' => __('messages.login_no_password_yet')]);

        Mail::assertSent(SetPassword::class, 1);
        $this->assertGuest();
    }

    public function test_login_still_says_nothing_useful_about_a_real_account(): void
    {
        User::factory()->create(['email' => 'real@fans.test', 'password' => bcrypt('correct-horse')]);

        $this->from(route('login'))
            ->post(route('login'), ['email' => 'real@fans.test', 'password' => 'wrong'])
            ->assertSessionHasErrors(['email' => trans('auth.failed')]);

        Mail::assertNothingSent();
    }

    public function test_login_says_nothing_useful_about_an_address_with_no_account(): void
    {
        $this->from(route('login'))
            ->post(route('login'), ['email' => 'nobody@fans.test', 'password' => 'anything'])
            ->assertSessionHasErrors(['email' => trans('auth.failed')]);

        Mail::assertNothingSent();
    }

    public function test_the_login_door_shares_one_budget_with_forgot_password(): void
    {
        // Three separate doors must not add up to three separate budgets of three mails. The bucket
        // key is PasswordResetLinkController's, deliberately.
        $this->stub();

        for ($i = 0; $i < 3; $i++) {
            RateLimiter::hit(StubAccountUtils::EMAIL_BUCKET.'fan@fans.test', 600);
        }

        $this->from(route('login'))
            ->post(route('login'), ['email' => 'fan@fans.test', 'password' => 'anything'])
            ->assertSessionHasErrors(['email' => __('messages.login_password_link_already_sent')]);

        // And it does not claim a send that did not happen.
        Mail::assertNothingSent();
    }

    public function test_a_throttled_broker_never_claims_a_fresh_send(): void
    {
        // PasswordBroker refuses with RESET_THROTTLED while recentlyCreatedToken() is true, which
        // config/auth.php sets to 60 seconds - and claimState() mints a token on EVERY confirm. So
        // this is the ordinary state for a minute after somebody confirms a subscription.
        $stub = $this->stub();
        Password::createToken($stub);

        $this->from(route('login'))
            ->post(route('login'), ['email' => 'fan@fans.test', 'password' => 'anything'])
            ->assertSessionHasErrors(['email' => __('messages.login_password_link_already_sent')]);

        Mail::assertNothingSent();
    }

    // -------------------------------------------------------------------------------------
    // The mail.
    // -------------------------------------------------------------------------------------

    public function test_a_stub_is_told_to_set_a_password_not_to_reset_one(): void
    {
        $stub = $this->stub();

        Password::sendResetLink(['email' => $stub->email]);

        Mail::assertSent(SetPassword::class, function (SetPassword $mail) {
            return str_contains($mail->resetUrl, '/update-password/')
                && $mail->email === 'fan@fans.test';
        });
    }

    public function test_a_real_account_still_gets_the_stock_reset_notification(): void
    {
        $user = User::factory()->create(['email' => 'real@fans.test', 'password' => bcrypt('correct-horse')]);

        \Illuminate\Support\Facades\Notification::fake();

        Password::sendResetLink(['email' => $user->email]);

        Mail::assertNotSent(SetPassword::class);
        \Illuminate\Support\Facades\Notification::assertSentTo(
            $user,
            \Illuminate\Auth\Notifications\ResetPassword::class
        );
    }

    // -------------------------------------------------------------------------------------
    // The emailed link, redeemed.
    // -------------------------------------------------------------------------------------

    public function test_the_link_verifies_a_subscriber_stub_and_signs_them_in(): void
    {
        $stub = $this->stub();
        $token = Password::createToken($stub);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => $stub->email,
            'password' => 'sup3rsecret',
        ])->assertRedirect(app_url(route('following', [], false)));

        $stub->refresh();
        $this->assertNotNull($stub->password);
        $this->assertNotNull($stub->email_verified_at, 'EnsureEmailIsVerified is global, so this is load-bearing');
        $this->assertAuthenticatedAs($stub);
    }

    public function test_the_link_does_not_verify_a_real_account_that_was_never_verified(): void
    {
        // The boundary NewPasswordController has always drawn, and this change must not move it: a
        // reset must not become an email-verification bypass for an account that chose a password
        // at signup and never confirmed the address.
        $user = User::factory()->create([
            'email' => 'unverified@fans.test',
            'password' => bcrypt('correct-horse'),
            'email_verified_at' => null,
        ]);
        $token = Password::createToken($user);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'sup3rsecret',
        ])->assertRedirect(route('login'));

        $this->assertNull($user->fresh()->email_verified_at);
        $this->assertGuest();
    }

    public function test_an_elevated_stub_is_not_verified_or_signed_in(): void
    {
        // A team invite sits at admin level, and PasswordResetLinkController has no stub filter, so
        // anyone can have a token minted for one. For a privileged account "proved the mailbox
        // twice" is worth keeping: it gets the password and today's flow, nothing more.
        $stub = $this->stub('invitee@fans.test');
        $role = $this->createRole($this->createOwner());
        $role->users()->attach($stub->id, ['level' => 'admin', 'created_at' => now()]);

        $this->assertFalse($stub->mayClaimByEmailLink());

        $token = Password::createToken($stub);

        $this->post(route('password.store'), [
            'token' => $token,
            'email' => $stub->email,
            'password' => 'sup3rsecret',
        ])->assertRedirect(route('login'));

        $stub->refresh();
        $this->assertNotNull($stub->password, 'the password is still set');
        $this->assertNull($stub->email_verified_at, 'but the address is not verified off the link');
        $this->assertGuest();
    }

    public function test_a_follower_pivot_does_not_disqualify_a_stub(): void
    {
        $stub = $this->stub();
        $role = $this->createRole($this->createOwner());
        $role->users()->attach($stub->id, ['level' => 'follower', 'created_at' => now()]);

        $this->assertTrue($stub->fresh()->mayClaimByEmailLink());
    }

    // -------------------------------------------------------------------------------------
    // The durable link in the email footers.
    // -------------------------------------------------------------------------------------

    private function subscriber(string $email = 'fan@fans.test', array $attrs = []): \App\Models\RoleSubscriber
    {
        $role = $attrs['role'] ?? $this->createRole($this->createOwner());
        unset($attrs['role']);

        return \App\Models\RoleSubscriber::create($attrs + [
            'role_id' => $role->id,
            'email' => $email,
            'name' => 'A Fan',
            'confirmed_at' => now(),
            'token' => \App\Models\RoleSubscriber::newToken(),
        ]);
    }

    public function test_the_manage_page_offers_a_stub_a_link_and_google(): void
    {
        config(['services.google.client_id' => 'test-client-id']);

        $this->stub();
        $sub = $this->subscriber();

        $this->get(route('subscriber.show_manage', ['token' => $sub->token]))
            ->assertOk()
            ->assertSee(__('messages.subscription_manage_email_me_a_link'), false)
            ->assertSee(__('messages.continue_with_google'), false)
            ->assertSee('fan@fans.test', false);
    }

    public function test_the_manage_page_get_mutates_nothing_and_sends_nothing(): void
    {
        // Safe Links, Proofpoint and Barracuda dereference footer links before a human sees them,
        // which is why every page in this block is GET-shows / POST-acts.
        $stub = $this->stub();
        $sub = $this->subscriber();

        $this->get(route('subscriber.show_manage', ['token' => $sub->token]))->assertOk();

        Mail::assertNothingSent();
        $this->assertTrue($stub->fresh()->isStub());
        $this->assertDatabaseCount('password_reset_tokens', 0);
    }

    public function test_the_manage_post_mails_the_address_on_the_row_not_one_posted(): void
    {
        $this->stub();
        $sub = $this->subscriber();

        $this->post(route('subscriber.send_set_password', ['token' => $sub->token]), [
            'email' => 'attacker@evil.test',
        ])->assertRedirect(route('subscriber.show_manage', ['token' => $sub->token]));

        Mail::assertSent(SetPassword::class, function (SetPassword $mail) {
            return $mail->email === 'fan@fans.test';
        });
    }

    public function test_a_tripped_honeypot_on_the_manage_form_sends_nothing(): void
    {
        $this->stub();
        $sub = $this->subscriber();

        $this->post(route('subscriber.send_set_password', ['token' => $sub->token]), [
            \App\Utils\HoneypotUtils::FIELD => 'https://example.com',
        ])->assertSessionHasErrors('email');

        Mail::assertNothingSent();
    }

    public function test_the_manage_page_resolves_a_newsletter_recipient_token_too(): void
    {
        // Newsletter footers carry a newsletter_recipients token, not a role_subscribers one, and
        // the page means the same thing for both.
        $this->stub();

        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $newsletter = \App\Models\Newsletter::create([
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'subject' => 'Hello',
            'status' => 'sent',
        ]);
        $recipient = \App\Models\NewsletterRecipient::create([
            'newsletter_id' => $newsletter->id,
            'email' => 'fan@fans.test',
            'name' => 'A Fan',
            'token' => \Illuminate\Support\Str::random(64),
            'status' => 'sent',
        ]);

        $this->get(route('subscriber.show_manage', ['token' => $recipient->token]))
            ->assertOk()
            ->assertSee(__('messages.subscription_manage_email_me_a_link'), false);
    }

    public function test_the_manage_page_offers_no_account_where_registration_is_closed(): void
    {
        config(['app.hosted' => false, 'app.allow_registration' => false]);

        $sub = $this->subscriber('nobody@fans.test');

        $this->get(route('subscriber.show_manage', ['token' => $sub->token]))
            ->assertOk()
            ->assertSee(__('messages.subscription_manage_registration_closed_body'), false)
            // RegisteredUserController::create() bounces a closed-registration sign-up to /login,
            // so linking one here would be a dead end.
            ->assertDontSee(route('sign_up'), false);
    }

    public function test_the_manage_page_tells_a_real_account_to_sign_in(): void
    {
        User::factory()->create(['email' => 'real@fans.test', 'password' => bcrypt('correct-horse')]);
        $sub = $this->subscriber('real@fans.test');

        $this->get(route('subscriber.show_manage', ['token' => $sub->token]))
            ->assertOk()
            ->assertSee(__('messages.subscription_manage_has_account_body'), false)
            ->assertDontSee(__('messages.subscription_manage_email_me_a_link'), false);
    }

    public function test_a_checkout_subscriber_is_told_what_an_account_gets_them(): void
    {
        $sub = $this->subscriber('buyer@fans.test', ['source' => 'checkout']);

        $this->get(route('subscriber.show_manage', ['token' => $sub->token]))
            ->assertOk()
            ->assertSee(__('messages.subscription_manage_no_account_checkout_body'), false);
    }

    public function test_an_unknown_manage_token_is_an_expired_link(): void
    {
        $this->get(route('subscriber.show_manage', ['token' => str_repeat('a', 64)]))
            ->assertStatus(410);
    }
}
