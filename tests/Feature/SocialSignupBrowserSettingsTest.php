<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A Google or Facebook sign-up takes its timezone and language from the browser_timezone and
 * browser_language cookies, which the login and sign-up pages write from JavaScript.
 *
 * Until they were exempted in bootstrap/app.php, EncryptCookies dropped both as undecryptable, so
 * every social sign-up was stored as America/New_York - over half of all new organizers - and the
 * first schedule copied it. withUnencryptedCookie() sends exactly what a browser script writes; a
 * plain withCookie() would be encrypted by the test client and pass with the exemption removed.
 */
class SocialSignupBrowserSettingsTest extends TestCase
{
    use RefreshDatabase;

    private function completeGoogleSignIn(string $email, ?string $locale = null): void
    {
        $socialUser = \Mockery::mock(\Laravel\Socialite\Two\User::class);
        $socialUser->shouldReceive('getId')->andReturn('google-'.md5($email));
        $socialUser->shouldReceive('getEmail')->andReturn($email);
        $socialUser->shouldReceive('getName')->andReturn('Browser Person');
        $socialUser->shouldReceive('getAvatar')->andReturn(null);
        $socialUser->user = ['locale' => $locale];

        $provider = \Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($socialUser);

        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('auth.google.callback'));
    }

    public function test_the_browser_timezone_reaches_a_google_sign_up(): void
    {
        $this->withUnencryptedCookie('browser_timezone', 'Europe/Berlin')
            ->withUnencryptedCookie('browser_language', 'de');

        $this->completeGoogleSignIn('berliner@eventschedule-test.org');

        $user = User::where('email', 'berliner@eventschedule-test.org')->firstOrFail();

        $this->assertSame('Europe/Berlin', $user->timezone);
        $this->assertSame('de', $user->language_code);
        $this->assertTrue((bool) $user->use_24_hour_time);
    }

    /** The cookie holds whatever the browser reported, so it is canonicalized like any other input. */
    public function test_a_browser_alias_is_stored_as_the_listed_name(): void
    {
        $this->withUnencryptedCookie('browser_timezone', 'Asia/Calcutta');

        $this->completeGoogleSignIn('kolkata@eventschedule-test.org', 'en');

        $this->assertSame('Asia/Kolkata', User::where('email', 'kolkata@eventschedule-test.org')->value('timezone'));
    }

    /** Anything that is not a timezone falls back as before, rather than reaching the column. */
    public function test_a_junk_cookie_falls_back(): void
    {
        $this->withUnencryptedCookie('browser_timezone', 'Not/AZone');

        $this->completeGoogleSignIn('junk@eventschedule-test.org', 'en');

        $this->assertSame('America/New_York', User::where('email', 'junk@eventschedule-test.org')->value('timezone'));
    }
}
