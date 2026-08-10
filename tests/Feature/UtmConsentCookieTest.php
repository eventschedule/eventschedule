<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

/**
 * Consent gating for the UTM attribution cookies (issue #111).
 *
 * The three 30-day cookies CaptureUtmParameters writes are marketing cookies, not strictly
 * necessary ones, so ePrivacy Art. 5(3) puts them behind consent. Two things have to hold:
 * nothing is written before the visitor accepts, and declining costs cross-session
 * attribution ONLY - the session copy that every consumer falls back to must survive, or a
 * decline would quietly stop crediting sales made in the same visit.
 */
class UtmConsentCookieTest extends TestCase
{
    use RefreshDatabase;

    private const ATTRIBUTION = ['utm_params', 'utm_referrer_url', 'utm_landing_page'];

    public function test_no_attribution_cookies_are_set_without_a_consent_choice(): void
    {
        $response = $this->withHeader('Referer', 'https://example.org/blog')
            ->get('/?utm_source=newsletter&utm_medium=email')
            ->assertOk();

        foreach (self::ATTRIBUTION as $name) {
            $this->assertNull($this->cookie($response, $name), $name.' must not be set before consent');
        }

        // Not vacuous: the middleware did run and did capture the attribution, just in the session.
        $this->assertSame('newsletter', session('utm_params.utm_source'));
        $this->assertSame('https://example.org/blog', session('utm_referrer_url'));
        $this->assertNotNull(session('utm_landing_page'));
    }

    public function test_attribution_cookies_are_written_once_consent_is_granted(): void
    {
        $response = $this->withUnencryptedCookie('cookie_consent', 'granted')
            ->withHeader('Referer', 'https://example.org/blog')
            ->get('/?utm_source=newsletter&utm_medium=email')
            ->assertOk();

        foreach (self::ATTRIBUTION as $name) {
            $cookie = $this->cookie($response, $name);

            $this->assertNotNull($cookie, $name.' must be written once consent is granted');
            $this->assertTrue($cookie->isHttpOnly(), $name.' must stay out of reach of scripts');
            $this->assertSame('lax', strtolower((string) $cookie->getSameSite()));
            $this->assertGreaterThan(now()->addDays(29)->timestamp, $cookie->getExpiresTime());
        }
    }

    /**
     * The consent cookie is written by cookie-consent.js, so Laravel has nothing to decrypt.
     * If the bootstrap/app.php exemption is ever dropped this reads as null and the gate is
     * shut for everyone, which is why it is asserted separately from the case above.
     */
    public function test_the_consent_cookie_is_readable_unencrypted(): void
    {
        $this->withUnencryptedCookie('cookie_consent', 'granted')->get('/')->assertOk();

        $this->assertSame('granted', request()->cookie('cookie_consent'));
    }

    public function test_a_cookie_set_before_the_gate_existed_is_cleared(): void
    {
        $response = $this->withCookie('utm_params', json_encode(['utm_source' => 'stale']))
            ->get('/')
            ->assertOk();

        $this->assertExpired($this->cookie($response, 'utm_params'), 'utm_params');
    }

    public function test_withdrawing_consent_clears_the_attribution_cookies(): void
    {
        $response = $this->withUnencryptedCookie('cookie_consent', 'denied')
            ->withCookie('utm_landing_page', 'some/page')
            ->get('/?utm_source=newsletter')
            ->assertOk();

        $this->assertExpired($this->cookie($response, 'utm_landing_page'), 'utm_landing_page');
    }

    public function test_declining_leaves_in_session_attribution_intact(): void
    {
        $this->withUnencryptedCookie('cookie_consent', 'denied')
            ->get('/?utm_source=newsletter&utm_medium=email')
            ->assertOk();

        $this->assertSame('newsletter', session('utm_params.utm_source'));

        // A later page in the same visit still carries it: this is what the checkout
        // controllers read before they fall back to the cookie.
        $this->get('/')->assertOk();

        $this->assertSame('newsletter', session('utm_params.utm_source'));
    }

    public function test_the_banner_appears_only_when_something_needs_consent(): void
    {
        config([
            'services.google.analytics' => null,
            'ads.enabled' => false,
            'stay22.enabled' => false,
            'app.cookie_consent_banner' => false,
        ]);

        $this->assertFalse(consent_required());

        foreach (['app.cookie_consent_banner', 'ads.enabled', 'stay22.enabled'] as $key) {
            config([$key => true]);
            $this->assertTrue(consent_required(), $key.' must raise the banner');
            config([$key => false]);
        }

        config(['services.google.analytics' => 'G-XXXXXXX']);
        $this->assertTrue(consent_required());
    }

    public function test_the_banner_markup_follows_the_gate(): void
    {
        config([
            'services.google.analytics' => null,
            'ads.enabled' => false,
            'stay22.enabled' => false,
            'app.cookie_consent_banner' => false,
        ]);

        $this->get('/')->assertOk()->assertDontSee('data-cookie-consent', false);

        config(['app.cookie_consent_banner' => true]);

        $this->get('/')->assertOk()->assertSee('data-cookie-consent', false);
    }

    /**
     * A Set-Cookie deletion only matches on (name, DOMAIN, path).
     *
     * rememberAttribution() writes these through Laravel's CookieJar with a null domain, and the
     * jar substitutes config('session.domain') - which on hosted is '.<base domain>'. The
     * withdrawal used Symfony's clearCookie(), which takes the null literally and emits a
     * host-only expiry, so it never matched the cookie it was trying to delete and the visitor
     * kept being tracked for the full 30 days. Every other test here runs with session.domain
     * unset, where both sides are null and the mismatch is invisible.
     */
    public function test_withdrawal_clears_on_the_same_domain_the_cookie_was_set_on(): void
    {
        config(['session.domain' => '.eventschedule.com']);

        // The CookieJar takes its defaults at resolve time, which already happened.
        app('cookie')->setDefaultPathAndDomain('/', '.eventschedule.com', true, 'Lax');

        // No consent cookie, so this request is a withdrawal: the stale attribution cookie must
        // be expired. withCookie, not withUnencryptedCookie - EncryptCookies drops a cookie it
        // cannot decrypt, and forgetAttributionCookies only acts on one the request carries.
        $response = $this->withCookie('utm_params', json_encode(['utm_source' => 'news']))->get('/');
        $cleared = $this->cookie($response, 'utm_params');

        $this->assertExpired($cleared, 'utm_params');
        $this->assertSame('.eventschedule.com', $cleared->getDomain(),
            'the delete has to name the same domain the jar wrote the cookie on, or it matches nothing');
    }

    /** The other half of the pair: what the jar actually writes it on. */
    public function test_the_attribution_cookie_is_written_on_the_session_domain(): void
    {
        config(['session.domain' => '.eventschedule.com']);
        app('cookie')->setDefaultPathAndDomain('/', '.eventschedule.com', true, 'Lax');

        $response = $this->withUnencryptedCookie('cookie_consent', 'granted')->get('/?utm_source=news');

        $this->assertSame('.eventschedule.com', $this->cookie($response, 'utm_params')?->getDomain());
    }

    /** The consent cookie itself has to span the install the same way, or the server never sees it. */
    public function test_the_banner_publishes_the_domain_the_consent_cookie_belongs_on(): void
    {
        config(['app.cookie_consent_banner' => true, 'session.domain' => '.eventschedule.com']);

        $this->get('/')->assertOk()->assertSee('data-cookie-domain=".eventschedule.com"', false);
    }

    private function assertExpired(?Cookie $cookie, string $name): void
    {
        $this->assertNotNull($cookie, 'The response must carry a delete for the stale '.$name.' cookie');
        $this->assertLessThan(now()->timestamp, $cookie->getExpiresTime(), $name.' must be expired, not refreshed');
    }

    private function cookie(TestResponse $response, string $name): ?Cookie
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === $name) {
                return $cookie;
            }
        }

        return null;
    }
}
