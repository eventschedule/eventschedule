<?php

namespace Tests\Feature;

use App\Models\MarketingDailyStat;
use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * signup_code_requests and signup_code_verified are a pair: the ratio between them is the
 * conversion of the 6-digit code wall, which is the step they were added to measure.
 *
 * A pair only means something if both sides count the same thing. Requests are deduped per
 * IP + user agent per UTC day; verifications were not deduped at all, so two people behind one
 * NAT on the same browser version contributed one request and two verifications - and the
 * reported conversion rate could exceed 100%.
 */
class SignupCodeFunnelTest extends TestCase
{
    use RefreshDatabase;

    private function stat(string $column): int
    {
        return (int) (MarketingDailyStat::where('date', now()->toDateString())->value($column) ?? 0);
    }

    public function test_both_sides_of_the_pair_dedupe_the_same_way(): void
    {
        $ip = '203.0.113.7';
        $agent = 'Mozilla/5.0 (Macintosh) TestBrowser/1.0';

        // Two visitors sharing one egress IP and one browser version, the same day.
        foreach ([1, 2] as $ignored) {
            if (PageView::isFirstDailyVisit('signup_code_request', $ip, $agent)) {
                MarketingDailyStat::record('signup_code_requests');
            }

            if (PageView::isFirstDailyVisit('signup_code_verified', $ip, $agent)) {
                MarketingDailyStat::record('signup_code_verified');
            }
        }

        $this->assertSame(1, $this->stat('signup_code_requests'));
        $this->assertSame(1, $this->stat('signup_code_verified'),
            'undeduped, this reads 2 against 1 request - a conversion rate over 100%');
    }

    /** The two buckets are independent, so a request never suppresses the matching verification. */
    public function test_the_two_buckets_do_not_share_a_key(): void
    {
        $ip = '203.0.113.8';
        $agent = 'TestBrowser/2.0';

        $this->assertTrue(PageView::isFirstDailyVisit('signup_code_request', $ip, $agent));
        $this->assertTrue(PageView::isFirstDailyVisit('signup_code_verified', $ip, $agent),
            'a verification must still count after the request for the same visitor did');
    }

    /** A browser that looks like a browser, so isBot/isSuspiciousRequest let the request through. */
    private function browserHeaders(string $ip = '203.0.113.9'): array
    {
        return [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120 Safari/537.36',
            'HTTP_ACCEPT' => 'text/html,application/json',
            'HTTP_ACCEPT_LANGUAGE' => 'en-GB,en;q=0.9',
            'HTTP_CF_CONNECTING_IP' => $ip,
        ];
    }

    /**
     * The guest-add flow shares this controller method but is not a signup.
     *
     * Counting it did more than inflate the numerator. isFirstDailyVisit CLAIMS the day's slot
     * for that IP + user agent, and guest-add never reaches signup_code_verified, so one guest
     * submission both added a phantom request and suppressed the real signup request from the
     * same browser - moving the code-wall conversion rate in both directions at once.
     */
    public function test_the_guest_add_flow_does_not_count_as_a_signup_code_request(): void
    {
        config(['app.hosted' => true]);

        $this->postJson(route('event.guest_send_code', ['subdomain' => 'a-venue']), ['email' => 'guest@eventschedule-test.org'], $this->browserHeaders())
            ->assertOk();

        $this->assertSame(0, $this->stat('signup_code_requests'),
            'a guest event submission is not a step in the signup funnel');
    }

    /**
     * And it must not eat the day's slot, or a real signup goes uncounted behind it.
     *
     * Asserted as 0 THEN 1, not just "1 at the end". Sharing the bucket also produces 1 at the
     * end - from the guest call, with the real signup suppressed behind it - so a single
     * closing assertion passes on the broken code for precisely the wrong reason.
     */
    public function test_a_guest_submission_does_not_suppress_a_later_signup_request(): void
    {
        config(['app.hosted' => true]);
        $headers = $this->browserHeaders();

        $this->postJson(route('event.guest_send_code', ['subdomain' => 'a-venue']), ['email' => 'guest@eventschedule-test.org'], $headers)
            ->assertOk();
        $this->assertSame(0, $this->stat('signup_code_requests'), 'the guest call contributes nothing');

        $this->postJson(route('sign_up.send_code'), ['email' => 'organizer@eventschedule-test.org'], $headers)
            ->assertOk();
        $this->assertSame(1, $this->stat('signup_code_requests'),
            'and it left the day\'s slot free for the real signup request behind it');
    }

    /**
     * Both sides have to drop bots, or the pair counts different populations.
     *
     * The request side already filtered; the VERIFIED side did not, so anything that got past
     * the code wall without a plausible user agent counted on one side only and pushed the
     * conversion rate up. Driven as a control/variant pair against the same endpoint, because
     * "0 verifications" on its own is also what a request that simply failed looks like.
     */
    public function test_a_bot_user_agent_is_dropped_on_the_verified_side(): void
    {
        // The verified counter lives behind hosted && ! is_testing, which phpunit pins on.
        config(['app.hosted' => true, 'app.is_testing' => false]);

        $botAgent = 'Mozilla/5.0 (compatible; SomeCrawler/2.1; +http://crawler.test/bot)';
        $realAgent = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120 Safari/537.36';

        $register = function (string $email, string $agent, string $ip) {
            $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            Cache::put('signup_code_email_'.$code, $email, now()->addMinutes(10));

            // app_url(), not route(): with is_testing off, RedirectToAppSubdomain bounces any
            // host that does not start with "app." and the request never reaches the counter.
            return $this->post(app_url('/sign_up'), [
                'name' => 'Test Person',
                'email' => $email,
                'password' => 'correct-horse-battery',
                'verification_code' => $code,
            ], [
                'HTTP_USER_AGENT' => $agent,
                'HTTP_ACCEPT' => 'text/html',
                'HTTP_ACCEPT_LANGUAGE' => 'en-GB,en;q=0.9',
                'HTTP_CF_CONNECTING_IP' => $ip,
            ]);
        };

        $register('crawler@eventschedule-test.org', $botAgent, '203.0.113.10');
        $this->assertDatabaseHas('users', ['email' => 'crawler@eventschedule-test.org']);
        $this->assertSame(0, $this->stat('signup_code_verified'),
            'a bot user agent counts on neither side of the pair');

        // A successful signup logs the account in, and the sign_up POST is behind `guest`, so
        // without this the control below is bounced to /dashboard and never runs the counter -
        // which reads as "0, filter works" for entirely the wrong reason.
        auth()->logout();
        $this->flushSession();

        // Control: the identical request from a real browser DOES count, which is what proves
        // the assertion above is measuring the filter and not a broken request.
        $register('person@eventschedule-test.org', $realAgent, '203.0.113.11');
        $this->assertDatabaseHas('users', ['email' => 'person@eventschedule-test.org']);
        $this->assertSame(1, $this->stat('signup_code_verified'));
    }
}
