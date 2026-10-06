<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\MarketingDailyStat;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The three counters behind the public "Submit your event" page.
 *
 * Nothing counted this page before 2026-10-06: not who saw the form, who reached the emailed
 * code, or who got an event through. Each counter is one visitor per day with the sign-up
 * counters' bot filters, so the three compare with each other; and none of them may touch the
 * sign-up funnel's own counters or daily slots (SignupCodeFunnelTest holds the other half).
 */
class GuestSubmitFunnelTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function curator(): Role
    {
        return $this->createCurator($this->createOwner(), [
            'accept_requests' => true,
            'require_account' => true,
            'require_approval' => true,
        ]);
    }

    /**
     * A browser that looks like a browser, so isBot/isSuspiciousRequest let the request through.
     * The page's own requests ask for JSON, as its fetch() calls do; the page itself is a document.
     */
    private function browser(string $ip = '203.0.113.9', bool $json = true): array
    {
        return [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120 Safari/537.36',
            'HTTP_ACCEPT' => $json ? 'application/json' : 'text/html,application/xhtml+xml',
            'HTTP_ACCEPT_LANGUAGE' => 'en-GB,en;q=0.9',
            'HTTP_CF_CONNECTING_IP' => $ip,
        ];
    }

    private function stat(string $column): int
    {
        return (int) (MarketingDailyStat::where('date', now()->toDateString())->value($column) ?? 0);
    }

    private function body(): array
    {
        return [
            'name' => 'Jazz Night',
            'starts_at' => now()->addDays(10)->format('Y-m-d').' 19:15:00',
            'ticket_currency_code' => 'USD',
            'coupon_discount_type' => Event::DEFAULT_COUPON_DISCOUNT_TYPE,
            'custom_field_values' => [],
            'account_mode' => 'register',
            'account_name' => 'New Person',
            'account_email' => 'newperson'.random_int(10000, 99999).'@gmail.com',
            'account_password' => 'password123',
            'terms' => true,
            'website' => '',
            'venue_name' => 'The Blue Room',
        ];
    }

    public function test_seeing_the_form_counts_a_visitor_once_a_day(): void
    {
        $url = route('event.guest_submit', ['subdomain' => $this->curator()->subdomain]);

        $this->get($url, $this->browser(json: false))->assertOk();
        $this->get($url, $this->browser(json: false))->assertOk();
        $this->assertSame(1, $this->stat('guest_submit_views'), 'the same visitor twice is one visitor');

        $this->get($url, $this->browser('203.0.113.77', false))->assertOk();
        $this->assertSame(2, $this->stat('guest_submit_views'));
    }

    public function test_a_bot_is_not_a_visitor(): void
    {
        $url = route('event.guest_submit', ['subdomain' => $this->curator()->subdomain]);

        // A browser's headers in every way but the name it gives. With no Accept-Language the
        // request was turned away as "suspicious" before the bot check was ever asked, and this
        // passed with that check deleted.
        $this->get($url, ['HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'] + $this->browser('203.0.113.5', false))->assertOk();
        $this->assertSame(0, $this->stat('guest_submit_views'));

        // The same request under a browser's name is a visitor: the name was the only reason.
        $this->get($url, $this->browser('203.0.113.5', false))->assertOk();
        $this->assertSame(1, $this->stat('guest_submit_views'));
    }

    /** The daily slot is a cache write, on hosted a database write. A counter is never worth a page. */
    public function test_a_counter_that_cannot_be_kept_does_not_cost_the_page(): void
    {
        $url = route('event.guest_submit', ['subdomain' => $this->curator()->subdomain]);
        Cache::partialMock()->shouldReceive('add')->andThrow(new \RuntimeException('the cache is down'));

        $this->get($url, $this->browser(json: false))->assertOk();

        $this->assertSame(0, $this->stat('guest_submit_views'));
    }

    public function test_asking_for_the_code_counts_here_and_not_as_a_sign_up(): void
    {
        config(['app.hosted' => true]);
        $url = route('event.guest_send_code', ['subdomain' => $this->curator()->subdomain]);

        $this->postJson($url, ['email' => 'guest@eventschedule-test.org'], $this->browser())->assertOk();
        $this->postJson($url, ['email' => 'guest@eventschedule-test.org'], $this->browser())->assertOk();

        $this->assertSame(1, $this->stat('guest_submit_code_requests'), 'a resend is the same visitor');
        $this->assertSame(0, $this->stat('signup_code_requests'), 'a guest submission is not a step in the sign-up funnel');
    }

    public function test_a_sign_up_does_not_count_as_a_guest_code_request(): void
    {
        config(['app.hosted' => true]);

        $this->postJson(route('sign_up.send_code'), ['email' => 'organizer@eventschedule-test.org', 'terms' => true], $this->browser())->assertOk();

        $this->assertSame(1, $this->stat('signup_code_requests'));
        $this->assertSame(0, $this->stat('guest_submit_code_requests'));
    }

    public function test_an_event_that_gets_through_counts_its_visitor_once(): void
    {
        $curator = $this->curator();
        $url = route('event.guest_import.store', ['subdomain' => $curator->subdomain]);

        $this->postJson($url, $this->body(), $this->browser())->assertOk()->assertJsonPath('success', true);
        // The same person, now signed in, sends a second event.
        $this->postJson($url, $this->body(), $this->browser())->assertOk()->assertJsonPath('success', true);

        $this->assertSame(2, Event::count());
        $this->assertSame(1, $this->stat('guest_submit_submissions'), 'two events from one visitor are one visitor who submitted');
    }

    public function test_a_refused_submission_is_not_counted(): void
    {
        $body = $this->body();
        $body['name'] = '';

        $this->postJson(route('event.guest_import.store', ['subdomain' => $this->curator()->subdomain]), $body, $this->browser())
            ->assertStatus(422);

        $this->assertSame(0, $this->stat('guest_submit_submissions'));
    }
}
