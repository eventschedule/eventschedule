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
