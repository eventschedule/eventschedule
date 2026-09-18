<?php

namespace Tests\Unit;

use App\Http\Middleware\ThrottleRequests;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Routing\Exceptions\MissingRateLimiterException;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * The repo's ThrottleRequests subclass has to preserve ARITY, not just parameters.
 *
 * Laravel's Illuminate\Routing\Middleware\ThrottleRequests::handle() decides between its two modes
 * with `func_num_args() === 3`: exactly three arguments means `throttle:some_name` and it looks the
 * name up as a NAMED limiter; anything else means `throttle:60,1` and it treats the third argument
 * as a number. A subclass that forwards all five positional parameters unconditionally makes the
 * parent see five every time, so the named-limiter branch is unreachable, `resolveMaxAttempts()`
 * gets a non-numeric name, and it THROWS MissingRateLimiterException - a 500 on a public route.
 *
 * That was live for `throttle:audience_unsubscribe` and `throttle:newsletter_unsubscribe`, i.e. the
 * RFC 8058 one-click unsubscribe endpoints, whose own route comments exist to keep them returning
 * anything other than an error.
 *
 * This is a UNIT test on the middleware and not a route test on purpose. The subclass returns early
 * when config('app.is_testing') is true, and phpunit.xml pins that true for the whole suite - so the
 * flag that would hide the bug from a feature test is the flag under which every feature test runs.
 * Here the middleware is driven directly, with the flag off.
 */
class ThrottleRequestsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Off, deliberately: the early return it drives is what made this bug invisible.
        config(['app.is_testing' => false]);
    }

    private function middleware(): ThrottleRequests
    {
        return $this->app->make(ThrottleRequests::class);
    }

    /**
     * A request with a route bound to it.
     *
     * resolveRequestSignature() needs one - both the parent's implementation and this repo's
     * override reach for $request->route() - and Request::create() alone leaves the resolver null,
     * which fails as "Unable to generate the request signature" long before the arity question.
     */
    private function request(string $uri = '/sub/m/token-here'): Request
    {
        $request = Request::create($uri, 'GET');
        $route = (new Route('GET', $uri, fn () => null))->bind($request);

        $request->setRouteResolver(fn () => $route);

        return $request;
    }

    private function passThrough(): \Closure
    {
        return fn () => response('ok');
    }

    public function test_a_named_limiter_is_honoured_rather_than_read_as_a_number(): void
    {
        RateLimiter::for('test_named_limiter', fn () => Limit::perMinute(10));

        $response = $this->middleware()->handle($this->request(), $this->passThrough(), 'test_named_limiter');

        $this->assertSame('ok', $response->getContent());
        // Proof the NAMED path ran: only handleRequestUsingNamedLimiter attaches these.
        $this->assertTrue($response->headers->has('X-RateLimit-Limit'));
        $this->assertSame('10', $response->headers->get('X-RateLimit-Limit'));
    }

    public function test_an_unregistered_named_limiter_still_reports_itself_clearly(): void
    {
        // Not a silent pass: a name nobody registered is a bug, and the framework's own exception
        // names it. This pins that we have not swallowed that signal while fixing the arity.
        $this->expectException(MissingRateLimiterException::class);

        $this->middleware()->handle($this->request(), $this->passThrough(), 'no_such_limiter_registered');
    }

    public function test_the_positional_forms_still_throttle(): void
    {
        // throttle:1,1 - the shape used by most routes in this repo.
        $middleware = $this->middleware();
        $request = $this->request();

        $first = $middleware->handle($request, $this->passThrough(), 1, 1);
        $this->assertSame('1', $first->headers->get('X-RateLimit-Limit'));
        $this->assertSame('0', $first->headers->get('X-RateLimit-Remaining'));

        try {
            $middleware->handle($request, $this->passThrough(), 1, 1);
            $this->fail('a second request over a limit of 1 should have been throttled');
        } catch (\Illuminate\Http\Exceptions\ThrottleRequestsException $e) {
            $this->assertSame(429, $e->getStatusCode());
        }
    }

    public function test_the_prefixed_positional_form_still_throttles(): void
    {
        // throttle:1,1,some_prefix - the shape /sub/c and /sub/account use. Its own bucket, so it
        // must not collide with the unprefixed one exercised above.
        $middleware = $this->middleware();
        $request = $this->request();

        $response = $middleware->handle($request, $this->passThrough(), 1, 1, 'unit_test_prefix');
        $this->assertSame('1', $response->headers->get('X-RateLimit-Limit'));

        try {
            $middleware->handle($request, $this->passThrough(), 1, 1, 'unit_test_prefix');
            $this->fail('a second prefixed request over a limit of 1 should have been throttled');
        } catch (\Illuminate\Http\Exceptions\ThrottleRequestsException $e) {
            $this->assertSame(429, $e->getStatusCode());
        }
    }

    public function test_testing_mode_still_bypasses_everything(): void
    {
        // The Dusk 429 fix this subclass exists for.
        config(['app.is_testing' => true]);

        $response = $this->middleware()->handle($this->request(), $this->passThrough(), 'no_such_limiter_registered');

        $this->assertSame('ok', $response->getContent());
    }
}
