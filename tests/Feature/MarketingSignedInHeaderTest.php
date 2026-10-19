<?php

namespace Tests\Feature;

use App\Http\Middleware\MarkSignedIn;
use App\Models\User;
use App\Services\DemoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Cookie;
use Tests\TestCase;

/**
 * A signed-in visitor sees Dashboard in the marketing header, not Sign In and Get Started.
 *
 * The server's @auth branch cannot do it alone: the edge hands a signed-in visitor the one
 * anonymous copy of the page (docs/CACHING.md), so that copy carries both pairs and the browser
 * picks one from the es_signed_in cookie, which MarkSignedIn keeps in step with the session.
 */
class MarketingSignedInHeaderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->pinAppUrl('https://eventschedule.test');
    }

    private function marker(\Illuminate\Testing\TestResponse $response): ?Cookie
    {
        foreach ($response->headers->getCookies() as $cookie) {
            if ($cookie->getName() === MarkSignedIn::COOKIE) {
                return $cookie;
            }
        }

        return null;
    }

    public function test_the_anonymous_copy_carries_both_pairs_and_the_script_that_picks_one(): void
    {
        $html = $this->get('/pricing')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/class="es-auth-guest[^"]*"[^>]*>\s*Sign In/', $html);
        $this->assertMatchesRegularExpression('/class="es-auth-user[^"]*"[^>]*>\s*Dashboard/', $html);
        $this->assertStringContainsString('html:not(.es-signed-in) .es-auth-user { display: none !important; }', $html);
        $this->assertStringContainsString('es_signed_in=1', $html);
    }

    public function test_the_anonymous_copy_is_still_cookie_free(): void
    {
        $response = $this->get('/pricing');

        $this->assertNull($this->marker($response));
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
    }

    public function test_a_signed_in_request_sets_a_marker_script_can_read(): void
    {
        $response = $this->actingAs(User::factory()->create())->get('/pricing');

        $cookie = $this->marker($response);
        $this->assertNotNull($cookie);
        $this->assertSame('1', $cookie->getValue(), 'The marker must not be encrypted, or the page cannot read it.');
        $this->assertFalse($cookie->isHttpOnly());
        $this->assertGreaterThan(time(), $cookie->getExpiresTime());

        // The server-rendered branch still says it on its own.
        $response->assertSee('Dashboard');
        $response->assertDontSee('Get Started');
    }

    public function test_a_remembered_visitor_keeps_the_marker_as_long_as_the_remember_cookie(): void
    {
        $user = User::factory()->create();
        $recaller = auth()->guard('web')->getRecallerName();

        $response = $this->actingAs($user)->withUnencryptedCookie($recaller, 'x')->get('/pricing');

        $this->assertGreaterThan(time() + 300 * 86400, $this->marker($response)->getExpiresTime());
    }

    public function test_a_stale_marker_is_removed_once_nobody_is_signed_in(): void
    {
        $response = $this->withUnencryptedCookie(MarkSignedIn::COOKIE, '1')->get('/pricing');

        $cookie = $this->marker($response);
        $this->assertNotNull($cookie);
        $this->assertLessThan(time(), $cookie->getExpiresTime());
    }

    public function test_a_stale_marker_costs_one_private_page_and_not_the_edge(): void
    {
        $response = $this->withUnencryptedCookie(MarkSignedIn::COOKIE, '1')->get('/pricing');

        $this->assertStringContainsString('private', $response->headers->get('Cache-Control'), 'A response that deletes a cookie must never be stored for everyone.');
    }

    public function test_logging_out_removes_the_marker(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->withUnencryptedCookie(MarkSignedIn::COOKIE, '1')
            ->post('/logout');

        $this->assertLessThan(time(), $this->marker($response)->getExpiresTime());
    }

    /**
     * MarkSignedIn sits outside EnsureAccountNotBlocked so it sees the sign-out that check makes.
     * Moved inside it, a blocked account's browser would keep being offered a dashboard.
     */
    public function test_a_blocked_account_loses_the_marker(): void
    {
        $user = User::factory()->create(['blocked_at' => now()]);

        $response = $this->actingAs($user)
            ->withUnencryptedCookie(MarkSignedIn::COOKIE, '1')
            ->get('/pricing');

        $this->assertLessThan(time(), $this->marker($response)->getExpiresTime());
    }

    public function test_the_marker_is_removed_on_the_domain_it_was_set_on(): void
    {
        config(['session.domain' => '.eventschedule.test']);

        $set = $this->marker($this->actingAs(User::factory()->create())->get('/pricing'));

        auth()->logout();
        $this->flushSession();
        $removed = $this->marker($this->withUnencryptedCookie(MarkSignedIn::COOKIE, '1')->get('/pricing'));

        $this->assertSame('.eventschedule.test', $set->getDomain());
        $this->assertSame([$set->getDomain(), $set->getPath()], [$removed->getDomain(), $removed->getPath()]);
    }

    public function test_the_demo_account_is_not_offered_a_dashboard(): void
    {
        $demo = User::factory()->create(['email' => DemoService::DEMO_EMAIL]);

        $response = $this->actingAs($demo)->get('/pricing');

        $this->assertNull($this->marker($response));
        $response->assertSee('Get Started');
    }
}
