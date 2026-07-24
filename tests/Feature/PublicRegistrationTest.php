<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Who may create an account (issue #108).
 *
 * A plain selfhost is single user - the first account is the instance admin - unless the
 * operator sets ALLOW_REGISTRATION. Hosted installs are always open.
 *
 * The /sign_up and /api/register gates themselves cannot be exercised here: each carries a
 * `! config('app.is_testing')` escape and phpunit.xml sets APP_TESTING=true, which is also
 * why RegistrationTest::test_registration_redirect_to_login_page is skipped. What is covered
 * is the shared policy helper every one of those gates now calls.
 */
class PublicRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public static function policyProvider(): array
    {
        return [
            'hosted is always open' => [true, false, true],
            'hosted ignores the selfhost flag' => [true, true, true],
            'selfhost is closed by default' => [false, false, false],
            'selfhost opens with ALLOW_REGISTRATION' => [false, true, true],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('policyProvider')]
    public function test_registration_policy(bool $hosted, bool $allow, bool $expected): void
    {
        config(['app.hosted' => $hosted, 'app.allow_registration' => $allow]);

        $this->assertSame($expected, public_registration_enabled());
    }

    /**
     * Stand in for the Google OAuth round-trip so the callback can be driven directly.
     */
    private function fakeGoogleUser(string $email, string $googleId): void
    {
        $googleUser = \Mockery::mock(\Laravel\Socialite\Contracts\User::class);
        $googleUser->shouldReceive('getEmail')->andReturn($email);
        $googleUser->shouldReceive('getId')->andReturn($googleId);
        $googleUser->shouldReceive('getName')->andReturn('Google Person');
        $googleUser->shouldReceive('getAvatar')->andReturn(null);
        $googleUser->user = ['locale' => 'en'];

        $provider = \Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($googleUser);

        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    public function test_google_oauth_cannot_create_an_account_on_a_closed_selfhost(): void
    {
        // Google sign-in is an account-creation path like the sign-up form. Before this it had
        // no selfhost gate at all, so a selfhost with Google configured could be registered
        // into by anyone (issue #108).
        User::factory()->create();
        config(['app.hosted' => false, 'app.allow_registration' => false, 'app.is_testing' => false]);

        $this->fakeGoogleUser('stranger@example.com', 'google-stranger');

        $this->get(route('auth.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('users', ['email' => 'stranger@example.com']);
        $this->assertSame(1, User::count());
    }

    public function test_google_oauth_creates_an_account_when_registration_is_open(): void
    {
        User::factory()->create();
        config(['app.hosted' => false, 'app.allow_registration' => true, 'app.is_testing' => false]);

        $this->fakeGoogleUser('newcomer@example.com', 'google-newcomer');

        $this->get(route('auth.google.callback'));

        $this->assertDatabaseHas('users', ['email' => 'newcomer@example.com']);
        $this->assertAuthenticated();
    }

    public function test_google_oauth_still_logs_in_an_existing_user_on_a_closed_selfhost(): void
    {
        // The gate sits after both existing-user branches, so a linked account keeps working.
        $existing = User::factory()->create(['email' => 'owner@example.com', 'google_oauth_id' => 'google-owner']);
        config(['app.hosted' => false, 'app.allow_registration' => false, 'app.is_testing' => false]);

        $this->fakeGoogleUser('owner@example.com', 'google-owner');

        $this->get(route('auth.google.callback'));

        $this->assertAuthenticatedAs($existing);
        $this->assertSame(1, User::count());
    }

    public function test_login_page_hides_sign_up_when_registration_is_closed(): void
    {
        // With no users at all, selfhost treats /login as the first-run setup wizard and
        // redirects to sign-up, so seed the instance admin first.
        User::factory()->create();
        config(['app.hosted' => false, 'app.allow_registration' => false]);

        $response = $this->get(route('login'));
        $response->assertOk();
        $response->assertDontSee(__('messages.create_new_account'));
        // Password reset is not a registration path and must stay reachable.
        $response->assertSee(__('messages.reset_password'));
    }

    public function test_login_page_offers_sign_up_when_registration_is_open(): void
    {
        // With no users at all, selfhost treats /login as the first-run setup wizard and
        // redirects to sign-up, so seed the instance admin first.
        User::factory()->create();
        config(['app.hosted' => false, 'app.allow_registration' => true]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee(__('messages.create_new_account'))
            ->assertSee(__('messages.reset_password'));
    }
}
