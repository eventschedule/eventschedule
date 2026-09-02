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
 *
 * The second half of this file covers `es_attribution`, which is a different cookie with a
 * different justification: the browser writes it, it holds exactly what the server session
 * used to hold for the marketing-to-signup hop, and it exists because anonymous marketing
 * HTML is now served from the CDN and so has no server session at all (docs/CACHING.md).
 * That makes it strictly necessary in the same sense the session cookie it replaces was, so
 * it is deliberately not consent-gated - and the tests below pin that it is the LAST
 * fallback, never an override of the session or the consented cookies.
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
    public function test_the_page_publishes_the_domain_the_consent_cookie_belongs_on(): void
    {
        config(['app.cookie_consent_banner' => true, 'session.domain' => '.eventschedule.com']);

        $this->get('/')->assertOk()
            ->assertSee('<meta name="cookie-domain" content=".eventschedule.com">', false);
    }

    /**
     * And it must be published even where the BANNER is not. cookie-consent.js re-asserts the
     * stored choice on every page load, so a page without the domain writes a second, host-only
     * cookie_consent beside the domain-scoped one - both get sent, PHP keeps whichever comes
     * last, and a later withdrawal clears only one of them.
     */
    public function test_the_domain_is_published_even_when_the_banner_is_hidden(): void
    {
        config(['app.cookie_consent_banner' => false, 'session.domain' => '.eventschedule.com']);

        $this->get('/')->assertOk()
            ->assertDontSee('data-cookie-consent', false)
            ->assertSee('<meta name="cookie-domain" content=".eventschedule.com">', false);
    }

    private function assertExpired(?Cookie $cookie, string $name): void
    {
        $this->assertNotNull($cookie, 'The response must carry a delete for the stale '.$name.' cookie');
        $this->assertLessThan(now()->timestamp, $cookie->getExpiresTime(), $name.' must be expired, not refreshed');
    }

    /**
     * The cookie counts as first-touch evidence in its own right, not just the session.
     *
     * Sessions last 2 hours and an edge-cacheable page has no persistent session at all, so a
     * guard that only consults the session rewrites the 30-day cookie with whatever page the
     * visitor happens to be on - turning first-touch attribution into last-touch.
     */
    public function test_the_attribution_cookie_is_not_rewritten_on_a_later_page(): void
    {
        $this->flushSession();

        $response = $this->withUnencryptedCookie('cookie_consent', 'granted')
            ->withCookie('utm_landing_page', 'for-musicians')
            ->withCookie('utm_referrer_url', 'https://first.example.org/')
            ->withHeader('Referer', 'https://later.example.org/')
            ->get('/pricing')
            ->assertOk();

        $this->assertNull($this->cookie($response, 'utm_landing_page'), 'first-touch landing page must survive');
        $this->assertNull($this->cookie($response, 'utm_referrer_url'), 'first-touch referrer must survive');
    }

    // -----------------------------------------------------------------
    // es_attribution: the browser-written fallback for edge-cached pages.
    // -----------------------------------------------------------------

    public function test_the_marketing_layout_ships_the_attribution_writer(): void
    {
        $this->get('/pricing')
            ->assertOk()
            ->assertSee('es_attribution=', false);
    }

    public function test_the_client_attribution_cookie_is_read_at_signup(): void
    {
        config(['app.hosted' => true]);

        $this->withUnencryptedCookie('es_attribution', $this->clientAttribution([
            'landing' => 'for-musicians',
            'referrer' => 'https://news.example.org/post',
            'utm_source' => 'newsletter',
            'utm_medium' => 'email',
            'utm_campaign' => 'spring',
        ]))->post('/sign_up', [
            'name' => 'Cached Visitor',
            'email' => 'cached@gmail.com',
            'password' => 'password',
        ]);

        $user = \App\Models\User::where('email', 'cached@gmail.com')->firstOrFail();

        $this->assertSame('newsletter', $user->utm_source);
        $this->assertSame('email', $user->utm_medium);
        $this->assertSame('spring', $user->utm_campaign);
        $this->assertSame('for-musicians', $user->landing_page);
        $this->assertSame('https://news.example.org/post', $user->referrer_url);
    }

    /**
     * referral_code was session-only before this, so a referred visitor who landed on a
     * cached page would have credited nobody.
     */
    public function test_a_referral_code_in_the_client_cookie_is_credited(): void
    {
        config(['app.hosted' => true]);

        $referrer = \App\Models\User::factory()->create(['referral_code' => 'REF12345']);

        $this->withUnencryptedCookie('es_attribution', $this->clientAttribution([
            'landing' => '/',
            'ref' => 'REF12345',
        ]))->post('/sign_up', [
            'name' => 'Referred Visitor',
            'email' => 'referred@gmail.com',
            'password' => 'password',
        ]);

        $user = \App\Models\User::where('email', 'referred@gmail.com')->firstOrFail();

        $this->assertSame($referrer->id, $user->referred_by_user_id);
        $this->assertDatabaseHas('referrals', [
            'referrer_user_id' => $referrer->id,
            'referred_user_id' => $user->id,
        ]);
    }

    /**
     * It is the LAST fallback. A visitor who was served a dynamic page has a session, and the
     * session must win - otherwise a stale first-touch cookie from an earlier visit would
     * overwrite the attribution of the visit that actually converted.
     */
    public function test_the_session_wins_over_the_client_attribution_cookie(): void
    {
        config(['app.hosted' => true]);

        $this->withUnencryptedCookie('es_attribution', $this->clientAttribution([
            'landing' => 'stale-page',
            'utm_source' => 'stale',
        ]))->withSession([
            'utm_params' => ['utm_source' => 'live', 'utm_medium' => null, 'utm_campaign' => null, 'utm_content' => null, 'utm_term' => null],
            'utm_landing_page' => 'live-page',
        ])->post('/sign_up', [
            'name' => 'Session Visitor',
            'email' => 'session@gmail.com',
            'password' => 'password',
        ]);

        $user = \App\Models\User::where('email', 'session@gmail.com')->firstOrFail();

        $this->assertSame('live', $user->utm_source);
        $this->assertSame('live-page', $user->landing_page);
    }

    /**
     * The value is client-controlled, so every shape of garbage has to fall through to null
     * rather than throw on the sign-up path.
     */
    public function test_a_malformed_client_attribution_cookie_is_ignored(): void
    {
        config(['app.hosted' => true]);

        $index = 0;

        foreach (['not json at all', '[]', '"a string"', '{"landing":{"nested":true},"utm_source":["array"]}', ''] as $raw) {
            // Registering signs the visitor in, and /sign_up is guest-only, so each round has
            // to start from a clean slate or every case after the first silently redirects.
            auth()->logout();
            $this->flushSession();

            $email = 'garbage'.($index++).'@gmail.com';

            $this->withUnencryptedCookie('es_attribution', $raw)->post('/sign_up', [
                'name' => 'Garbage Visitor',
                'email' => $email,
                'password' => 'password',
            ]);

            $user = \App\Models\User::where('email', $email)->firstOrFail();

            $this->assertNull($user->utm_source, 'Malformed cookie: '.$raw);
            $this->assertNull($user->landing_page, 'Malformed cookie: '.$raw);
            $this->assertNull($user->referrer_url, 'Malformed cookie: '.$raw);
        }
    }

    /**
     * Written by the browser, so Laravel has nothing to decrypt. Dropping the bootstrap
     * exemption would make every read null and the whole fallback silently dead.
     */
    public function test_the_client_attribution_cookie_is_readable_unencrypted(): void
    {
        $payload = $this->clientAttribution(['landing' => 'pricing', 'utm_source' => 'plain']);

        $request = \Illuminate\Http\Request::create('/sign_up', 'POST');
        $request->cookies->set('es_attribution', $payload);

        $attribution = \App\Http\Middleware\CaptureUtmParameters::clientAttribution($request);

        $this->assertSame('plain', $attribution['utm_params']['utm_source']);
        $this->assertSame('pricing', $attribution['utm_landing_page']);

        // ...and the exemption really is in place, so a real request reads the same value.
        $this->withUnencryptedCookie('es_attribution', $payload)
            ->get('/pricing')
            ->assertOk();

        $this->assertNotContains('es_attribution', array_map(
            fn ($cookie) => $cookie->getName(),
            $this->app['cookie']->getQueuedCookies()
        ), 'The server must never write this cookie itself.');
    }

    // -----------------------------------------------------------------
    // Seeding the session from es_attribution, before anything can record
    // a first touch of its own (CaptureUtmParameters::handle).
    // -----------------------------------------------------------------

    /**
     * The whole point of the cookie, from the read sites' side.
     *
     * Anonymous marketing pages are served from the edge, so the FIRST request with a real
     * session is /sign_up itself. Its session had no landing page, so the first-touch capture
     * stored `sign_up` - and `sign_up` then beat the cookie at every `session ?? cookie ??
     * es_attribution` read site, recording the sign-up form as the page that won the visitor.
     */
    public function test_the_client_cookie_beats_the_sign_up_page_as_the_landing_page(): void
    {
        config(['app.hosted' => true]);

        $this->withUnencryptedCookie('es_attribution', $this->clientAttribution([
            'landing' => 'for-musicians',
            'referrer' => 'https://news.example.org/post',
            'utm_source' => 'newsletter',
            'ref' => 'REF12345',
        ]));

        // The visitor arrives at the form the way a real one does, from a cached page.
        $this->get('/sign_up')->assertOk();

        $this->post('/sign_up', [
            'name' => 'Cached Visitor',
            'email' => 'landed@gmail.com',
            'password' => 'password',
        ]);

        $user = \App\Models\User::where('email', 'landed@gmail.com')->firstOrFail();

        $this->assertSame('for-musicians', $user->landing_page);
        $this->assertSame('https://news.example.org/post', $user->referrer_url);
        $this->assertSame('newsletter', $user->utm_source);
    }

    /**
     * Seeding covers the read sites that have NO cookie fallback of their own:
     * SocialAuthController::handleGoogleCallback() and TicketController's two stub-account
     * paths all read `session(...) ?? request()->cookie(...)` and stop there. Asserting the
     * session directly is what pins that, rather than each of their own flows.
     */
    public function test_the_session_is_seeded_for_the_read_sites_that_only_look_at_it(): void
    {
        $this->withUnencryptedCookie('es_attribution', $this->clientAttribution([
            'landing' => 'for-venues',
            'referrer' => 'https://blog.example.org/why',
            'utm_source' => 'newsletter',
            'utm_medium' => 'email',
            'ref' => 'REF12345',
        ]))->get('/sign_up')->assertOk();

        $this->assertSame('for-venues', session('utm_landing_page'));
        $this->assertSame('https://blog.example.org/why', session('utm_referrer_url'));
        $this->assertSame('newsletter', session('utm_params.utm_source'));
        $this->assertSame('email', session('utm_params.utm_medium'));
        $this->assertSame('REF12345', session('referral_code'));
    }

    /**
     * ...and the Google sign-up path end to end, because it is the one account-creation flow
     * with no clientAttribution() fallback at all. Before the seeding it recorded
     * `auth/google/callback` as the landing page of every visitor who arrived from the edge.
     */
    public function test_google_sign_up_is_attributed_from_the_client_cookie(): void
    {
        config(['app.hosted' => true]);

        $googleUser = (new \Laravel\Socialite\Two\User)->map([
            'id' => 'google-oauth-id-1',
            'name' => 'Google Visitor',
            'email' => 'google-visitor@gmail.com',
            'avatar' => null,
        ]);
        $googleUser->user = [];

        $provider = \Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('redirectUrl')->andReturnSelf();
        $provider->shouldReceive('user')->andReturn($googleUser);
        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $referrer = \App\Models\User::factory()->create(['referral_code' => 'REF12345']);

        $this->withUnencryptedCookie('es_attribution', $this->clientAttribution([
            'landing' => 'for-musicians',
            'referrer' => 'https://news.example.org/post',
            'utm_source' => 'newsletter',
            'ref' => 'REF12345',
        ]))->get('/auth/google/callback');

        $user = \App\Models\User::where('email', 'google-visitor@gmail.com')->firstOrFail();

        $this->assertSame('for-musicians', $user->landing_page);
        $this->assertSame('https://news.example.org/post', $user->referrer_url);
        $this->assertSame('newsletter', $user->utm_source);
        $this->assertSame($referrer->id, $user->referred_by_user_id);
    }

    /**
     * Priority is unchanged: the session still wins, because a visitor who was served a
     * dynamic page recorded their first touch there.
     */
    public function test_seeding_never_overwrites_a_session_the_visitor_already_has(): void
    {
        $this->withUnencryptedCookie('es_attribution', $this->clientAttribution([
            'landing' => 'stale-page',
            'referrer' => 'https://stale.example.org/',
        ]))->withSession([
            'utm_landing_page' => 'live-page',
        ])->get('/sign_up')->assertOk();

        $this->assertSame('live-page', session('utm_landing_page'));

        // Only the key that was already there is protected; the rest is still seeded.
        $this->assertSame('https://stale.example.org/', session('utm_referrer_url'));
    }

    /**
     * And the consented 30-day cookies still win over it, because they carry an EARLIER first
     * touch (up to 30 days back) while es_attribution is scoped to the browser session. Same
     * order every read site applies; seeding just applies it sooner.
     */
    public function test_seeding_defers_to_the_consented_thirty_day_cookies(): void
    {
        $this->withUnencryptedCookie('cookie_consent', 'granted')
            ->withUnencryptedCookie('es_attribution', $this->clientAttribution([
                'landing' => 'this-visit',
                'referrer' => 'https://this-visit.example.org/',
            ]))
            ->withCookie('utm_landing_page', 'three-weeks-ago')
            ->withCookie('utm_referrer_url', 'https://three-weeks-ago.example.org/')
            ->get('/sign_up')
            ->assertOk();

        $this->assertNull(session('utm_landing_page'), 'the 30-day cookie is the answer, so nothing is seeded over it');
        $this->assertNull(session('utm_referrer_url'));
    }

    /**
     * Not on an edge-cacheable page: that session is an in-memory store that is thrown away,
     * and seeding it would suppress the consented 30-day cookie the visitor's first marketing
     * page is meant to write. The guard is the request attribute
     * CacheableMarketingResponse sets, not the driver name - phpunit.xml runs the whole suite
     * on the `array` driver, so a driver check would be true everywhere.
     */
    public function test_a_cacheable_marketing_page_is_not_seeded(): void
    {
        config(['app.url' => 'https://eventschedule.test']);

        $response = $this->withUnencryptedCookie('cookie_consent', 'granted')
            ->withUnencryptedCookie('es_attribution', $this->clientAttribution(['landing' => 'for-musicians']))
            ->get('/faq')
            ->assertOk();

        $this->assertNotNull($this->cookie($response, 'utm_landing_page'), 'the consented cookie must still be written');
    }

    /**
     * The beacon and the docs search index are fetched BY a marketing page, so neither is a
     * landing page. Recording one would be wrong in the session and worse in a 30-day cookie:
     * the JSON route answers `public, max-age=3600`, so a Set-Cookie on it would be handed to
     * every visitor after the first.
     */
    public function test_the_non_page_routes_never_record_a_landing_page(): void
    {
        config(['app.url' => 'https://eventschedule.test']);

        $index = $this->withUnencryptedCookie('cookie_consent', 'granted')->get('/docs/search-index.json');

        $index->assertOk();
        $this->assertNull($this->cookie($index, 'utm_landing_page'));
        $this->assertNull(session('utm_landing_page'));

        $beacon = $this->withUnencryptedCookie('cookie_consent', 'granted')
            ->postJson('/marketing/visit', ['route' => 'marketing.pricing']);

        $beacon->assertNoContent();
        $this->assertNull($this->cookie($beacon, 'utm_landing_page'));
        $this->assertNull(session('utm_landing_page'));
    }

    private function clientAttribution(array $data): string
    {
        return json_encode($data);
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
