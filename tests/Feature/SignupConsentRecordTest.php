<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Accepting the terms is a legal record, and until now it was checked and then thrown away.
 *
 * RegisteredUserController::store() validates `terms => accepted` on hosted and wrote the answer
 * nowhere, so the app could not say who had agreed to what or when. SocialAuthController did not
 * ask at all, and that path is roughly half of all accounts.
 */
class SignupConsentRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_hosted_signup_records_when_consent_was_given(): void
    {
        config(['app.hosted' => true]);

        $this->post(route('sign_up'), [
            'name' => 'Test Person',
            'email' => 'organizer@eventschedule-test.org',
            'password' => 'correct-horse-battery',
            'terms' => '1',
        ]);

        $user = User::where('email', 'organizer@eventschedule-test.org')->first();

        $this->assertNotNull($user, 'the account was not created');
        $this->assertNotNull($user->terms_accepted_at, 'consent was validated and then discarded');
    }

    /**
     * Null has to keep meaning "not recorded", not "declined".
     *
     * `terms` is only required on hosted - on selfhost the first account is the operator
     * installing their own software, and a missing field there is a failed install rather than a
     * missing consent.
     */
    public function test_a_selfhost_signup_records_nothing(): void
    {
        config(['app.hosted' => false]);

        $this->post(route('sign_up'), [
            'name' => 'Operator',
            'email' => 'operator@eventschedule-test.org',
            'password' => 'correct-horse-battery',
        ]);

        $user = User::where('email', 'operator@eventschedule-test.org')->first();

        $this->assertNotNull($user);
        $this->assertNull($user->terms_accepted_at);
    }

    /**
     * The column is not fillable, so a request cannot assert its own consent date.
     *
     * Consent is written by the two controllers that collect it. Without this, posting
     * terms_accepted_at alongside the form would let the client choose the record.
     */
    public function test_consent_cannot_be_mass_assigned(): void
    {
        $forged = now()->subYears(5);

        $user = User::create([
            'name' => 'Test Person',
            'email' => 'forged@eventschedule-test.org',
            'password' => 'x',
            'terms_accepted_at' => $forged,
        ]);

        $this->assertNull($user->fresh()->terms_accepted_at);
    }

    /**
     * The consent checkbox has to LINK to what is being accepted.
     *
     * register.blade.php resolves it with str_replace([':terms', ':privacy'], ...), so a locale
     * whose translation names different placeholders renders the literal text and no links at all.
     * Estonian did exactly that in both consent strings, on hosted and on selfhost.
     */
    public function test_every_locale_uses_the_placeholders_the_view_replaces(): void
    {
        foreach (array_keys(config('app.supported_languages')) as $locale) {
            $strings = require base_path('resources/lang/'.$locale.'/messages.php');

            $this->assertStringContainsString(':terms', $strings['i_accept_the_terms_and_privacy'], $locale);
            $this->assertStringContainsString(':privacy', $strings['i_accept_the_terms_and_privacy'], $locale);
            $this->assertStringContainsString(':terms', $strings['i_accept_the_terms'], $locale);
            $this->assertStringContainsString(':terms', $strings['by_continuing_you_accept'], $locale);
            $this->assertStringContainsString(':privacy', $strings['by_continuing_you_accept'], $locale);
        }
    }

    /**
     * A rendered consent row must contain two real links, in every locale.
     *
     * This covers the HOSTED row only, which uses i_accept_the_terms_and_privacy. The selfhost row
     * is a different key and cannot be reached from here, because closing registration means
     * app.hosted = false, which routes/web.php reads at boot. That one is covered by the
     * placeholder test above - and it is the one Hebrew was broken in.
     */
    public function test_the_consent_row_renders_links_in_every_locale(): void
    {
        foreach (array_keys(config('app.supported_languages')) as $locale) {
            config(['app.hosted' => true, 'app.is_testing' => false]);
            app()->setLocale($locale);

            $html = $this->get(app_url('/sign_up'))->getContent();

            $this->assertMatchesRegularExpression(
                '/<label for="terms".*?<a href="[^"]+".*?<a href="[^"]+"/s',
                $html,
                $locale.' renders a consent box that does not link both documents'
            );
        }
    }

    /** A new Google account is a moment of consent, and recorded as one. */
    public function test_a_new_google_account_records_consent(): void
    {
        config(['app.hosted' => true]);

        $this->completeGoogleSignIn('googler@eventschedule-test.org', 'google-oauth-consent-1');

        $user = User::where('email', 'googler@eventschedule-test.org')->first();

        $this->assertNotNull($user);
        $this->assertNotNull($user->terms_accepted_at);
    }

    /**
     * Linking Google to an account that already exists is not.
     *
     * The stamp belongs on the new-account branch only. Put it on the shared path and every
     * existing user who connects Google silently acquires a consent date they never gave, which
     * is worse than having none.
     */
    public function test_linking_google_to_an_existing_account_records_nothing(): void
    {
        config(['app.hosted' => true]);

        $existing = User::factory()->create([
            'email' => 'linker@eventschedule-test.org',
            'password' => null,
            'google_oauth_id' => null,
            'terms_accepted_at' => null,
        ]);

        $this->completeGoogleSignIn('linker@eventschedule-test.org', 'google-oauth-link-1');

        $this->assertNotNull($existing->fresh()->google_oauth_id, 'the accounts were not linked, so this proves nothing');
        $this->assertNull($existing->fresh()->terms_accepted_at);
    }

    private function completeGoogleSignIn(string $email, string $googleId): void
    {
        $socialUser = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $socialUser->shouldReceive('getId')->andReturn($googleId);
        $socialUser->shouldReceive('getEmail')->andReturn($email);
        $socialUser->shouldReceive('getName')->andReturn('Test Person');
        $socialUser->shouldReceive('getAvatar')->andReturn(null);
        $socialUser->user = ['locale' => 'en'];

        $provider = \Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);

        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback'));
    }

    /**
     * The Google button creates accounts from /login too - SocialAuthController does not know
     * which page sent the visitor - so the disclosure has to be on both.
     */
    public function test_both_auth_pages_state_the_terms_beside_the_google_button(): void
    {
        foreach (['/sign_up', '/login'] as $path) {
            config(['app.hosted' => true, 'app.is_testing' => false, 'services.google.client_id' => 'x']);

            $html = $this->get(app_url($path))->getContent();

            $this->assertStringContainsString(policy_url('terms'), $html, $path);
            $this->assertStringContainsString(policy_url('privacy'), $html, $path);
        }
    }
}
