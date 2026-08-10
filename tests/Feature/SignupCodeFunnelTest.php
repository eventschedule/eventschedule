<?php

namespace Tests\Feature;

use App\Models\MarketingDailyStat;
use App\Models\PageView;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
