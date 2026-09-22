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

    public function test_two_unprefixed_guest_routes_share_one_bucket(): void
    {
        // This is the defect the named prefixes in routes/auth.php exist to avoid, pinned as
        // framework behaviour so the reason for those prefixes cannot be read as decoration.
        //
        // App\Http\Middleware\ThrottleRequests::resolveRequestSignature() scopes by route name only
        // when $request->user() is set. A GUEST falls through to the parent, which keys on
        // `$route->getDomain().'|'.$request->ip()` - no route name, no URI. routes/auth.php
        // declares no domain, so unprefixed every POST in it answered to the same counter.
        $middleware = $this->middleware();

        $sendCode = $this->request('/sign_up/send-code');
        $login = $this->request('/login');

        $middleware->handle($sendCode, $this->passThrough(), 1, 1);

        // A different route, its own limit of 1, never used - and already exhausted.
        try {
            $middleware->handle($login, $this->passThrough(), 1, 1);
            $this->fail('two unprefixed guest routes should share a bucket; if this now passes, the framework changed and the prefixes below may be redundant');
        } catch (\Illuminate\Http\Exceptions\ThrottleRequestsException $e) {
            $this->assertSame(429, $e->getStatusCode());
        }
    }

    public function test_distinct_prefixes_give_each_guest_route_its_own_bucket(): void
    {
        $middleware = $this->middleware();

        $sendCode = $this->request('/sign_up/send-code');
        $login = $this->request('/login');

        $first = $middleware->handle($sendCode, $this->passThrough(), 1, 1, 'unit_signup_code');
        $this->assertSame('0', $first->headers->get('X-RateLimit-Remaining'));

        // Same IP, same (absent) domain, different prefix: a full allowance of its own.
        $second = $middleware->handle($login, $this->passThrough(), 1, 1, 'unit_login');
        $this->assertSame('0', $second->headers->get('X-RateLimit-Remaining'));
        $this->assertSame('ok', $second->getContent());
    }

    /**
     * The routes that must have their own bucket, named explicitly.
     *
     * NOT "every guest route": an earlier version filtered on `in_array('guest', $middleware)`,
     * which only ever matches routes/auth.php's group. The tenant-scoped guest routes in
     * routes/web.php carry no `guest` alias, so that scan was structurally blind to them while
     * reading as though it covered everything reachable signed-out.
     *
     * These are the ones prefixed so far: the auth group, plus the guest-portal POSTs that share
     * the funnel with it. The `submit_comment` and `submit_photo` pair matter most - their decay
     * is 60 MINUTES, so one of them could set an hour-long timer on a bucket the sign-up and
     * checkout routes were sharing.
     *
     * Other throttled tenant routes remain unprefixed and still share a bucket. That is a known
     * gap, not a covered one, and this list is where it gets closed a route at a time.
     */
    private const MUST_BE_PREFIXED = [
        'sign_up.send_code',
        'event.guest_send_code',
        'event.guest_import.store',
        'event.check_email',
        'event.booking_request.store',
        'event.submit_comment',
        'event.submit_photo',
        'event.checkout',
        'event.rsvp',
    ];

    public function test_the_named_routes_each_carry_their_own_throttle_bucket(): void
    {
        $unprefixed = [];
        $seen = [];

        foreach (app('router')->getRoutes() as $route) {
            $middleware = $route->gatherMiddleware();
            $isAuthGroup = in_array('guest', $middleware, true);
            $named = in_array($route->getName(), self::MUST_BE_PREFIXED, true);

            if (! $isAuthGroup && ! $named) {
                continue;
            }

            if ($named) {
                $seen[$route->getName()] = true;
            }

            foreach ($middleware as $entry) {
                if (! is_string($entry) || ! str_starts_with($entry, 'throttle:')) {
                    continue;
                }

                // throttle:5,1 has two arguments; throttle:5,1,name has the third we require.
                // A single argument is a NAMED limiter, which already has its own bucket.
                $args = explode(',', substr($entry, strlen('throttle:')));

                if (count($args) === 2) {
                    $unprefixed[] = implode('|', $route->methods()).' '.$route->uri().' ('.$entry.')';
                }
            }
        }

        $this->assertSame([], $unprefixed, 'these routes share one per-IP throttle bucket with every other unprefixed route on the host: '.implode(', ', $unprefixed));

        // A renamed or deleted route must not silently drop out of the list above.
        $missing = array_diff(self::MUST_BE_PREFIXED, array_keys($seen));
        $this->assertSame([], array_values($missing),
            'these route names are in MUST_BE_PREFIXED but no longer exist: '.implode(', ', $missing));
    }

    public function test_testing_mode_still_bypasses_everything(): void
    {
        // The Dusk 429 fix this subclass exists for.
        config(['app.is_testing' => true]);

        $response = $this->middleware()->handle($this->request(), $this->passThrough(), 'no_such_limiter_registered');

        $this->assertSame('ok', $response->getContent());
    }
}
