<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\MarketingDailyStat;
use App\Models\Role;
use App\Services\DemoService;
use App\Utils\GuestFunnel;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The seven daily counts of what visitors do on guest pages (App\Utils\GuestFunnel): what each
 * one counts, that it counts a visitor once a day, and who is left out.
 */
class GuestFunnelTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = $this->createRole($this->createOwner());
        $this->event = $this->createEvent($this->role, [
            'starts_at' => now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'creator_role_id' => $this->role->id,
        ]);
    }

    /** A real browser's headers, from one address. A beacon and a fetch do not ask for a document. */
    private function browser(string $ip = '203.0.113.9', bool $document = true): array
    {
        return [
            'HTTP_USER_AGENT' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 Chrome/120 Safari/537.36',
            'HTTP_ACCEPT' => $document ? 'text/html,application/xhtml+xml' : '*/*',
            'HTTP_ACCEPT_LANGUAGE' => 'en-GB,en;q=0.9',
            'HTTP_CF_CONNECTING_IP' => $ip,
        ];
    }

    private function stat(string $column): int
    {
        return (int) (MarketingDailyStat::where('date', now()->toDateString())->value($column) ?? 0);
    }

    private function eventUrl(?Event $event = null, ?Role $role = null): string
    {
        return ($event ?? $this->event)->fresh()->getGuestUrl(($role ?? $this->role)->subdomain);
    }

    private function beacon(string $body, array $server = []): TestResponse
    {
        return $this->call('POST', '/api/guest-count', [], [], [], $server + $this->browser(document: false), $body);
    }

    public function test_every_stage_has_a_counter_that_is_written_and_dated(): void
    {
        $this->assertCount(7, GuestFunnel::STAGES);

        foreach (GuestFunnel::STAGES as $stage => $column) {
            $this->assertContains($column, MarketingDailyStat::COLUMNS, $stage);
            $this->assertArrayHasKey($column, MarketingDailyStat::COLUMN_TRACKED_FROM, $stage);
            $this->assertContains($column, (new MarketingDailyStat)->getFillable(), $stage);
        }

        foreach (GuestFunnel::BEACON_STAGES as $stage) {
            $this->assertArrayHasKey($stage, GuestFunnel::STAGES);
        }
    }

    public function test_opening_an_event_page_counts_a_visitor_once_a_day(): void
    {
        $this->get($this->eventUrl(), $this->browser())->assertOk();
        $this->get($this->eventUrl(), $this->browser())->assertOk();
        $this->assertSame(1, $this->stat('gp_event_visitors'), 'the same visitor, twice');

        $this->get($this->eventUrl(), $this->browser('203.0.113.77'))->assertOk();
        $this->assertSame(2, $this->stat('gp_event_visitors'));

        // The schedule's own page is not an event page.
        $this->get('/'.$this->role->subdomain, $this->browser('203.0.113.80'))->assertOk();
        $this->assertSame(2, $this->stat('gp_event_visitors'));
    }

    public function test_the_schedules_own_team_demo_schedules_embeds_and_bots_are_left_out(): void
    {
        // Its owner, signed in.
        $this->actingAs($this->role->user)->get($this->eventUrl(), $this->browser('203.0.113.1'))->assertOk();
        $this->assertSame(0, $this->stat('gp_event_visitors'), 'the owner looking at their own page');
        auth()->logout();

        // An embed is a different page with different traffic.
        $this->get($this->eventUrl().'?embed=true', $this->browser('203.0.113.2'));
        $this->assertSame(0, $this->stat('gp_event_visitors'), 'an embed');

        // A crawler that says what it is, and a client that sends no Accept-Language.
        $this->get($this->eventUrl(), ['HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'] + $this->browser('203.0.113.3'))->assertOk();
        $this->get($this->eventUrl(), ['HTTP_ACCEPT_LANGUAGE' => ''] + $this->browser('203.0.113.4'))->assertOk();
        $this->assertSame(0, $this->stat('gp_event_visitors'), 'bots');

        // A demo schedule: its visitors come from the marketing site.
        $demo = $this->createRole($this->createOwner(), 'venue', ['email' => DemoService::DEMO_EMAIL]);
        $demoEvent = $this->createEvent($demo, ['creator_role_id' => $demo->id]);
        $this->assertTrue($demo->fresh()->isDemoContent());
        $this->get($this->eventUrl($demoEvent, $demo), $this->browser('203.0.113.5'))->assertOk();
        $this->assertSame(0, $this->stat('gp_event_visitors'), 'a demo schedule');

        // And a real visitor still counts, so the zeros above are not a broken counter.
        $this->get($this->eventUrl(), $this->browser('203.0.113.6'))->assertOk();
        $this->assertSame(1, $this->stat('gp_event_visitors'));
    }

    public function test_the_beacon_counts_the_three_stages_a_browser_reports(): void
    {
        foreach (['list_tap' => 'gp_list_taps', 'form_open' => 'gp_form_opens', 'calendar_add' => 'gp_calendar_adds'] as $stage => $column) {
            $this->beacon(json_encode(['s' => $stage]))->assertNoContent();
            $this->beacon(json_encode(['s' => $stage]))->assertNoContent();
            $this->assertSame(1, $this->stat($column), $stage.': the same visitor, twice');

            $this->beacon(json_encode(['s' => $stage]), ['HTTP_CF_CONNECTING_IP' => '203.0.113.77'])->assertNoContent();
            $this->assertSame(2, $this->stat($column), $stage);
        }
    }

    public function test_the_beacon_refuses_what_only_the_server_may_count(): void
    {
        // A posted "checkout_done" would be a number anybody could raise.
        foreach (['event_view', 'checkout_start', 'checkout_done', 'follow', 'visitors', ''] as $stage) {
            $this->beacon(json_encode(['s' => $stage]))->assertStatus(422);
        }
        $this->beacon('not json')->assertStatus(422);
        $this->beacon(json_encode(['s' => ['list_tap']]))->assertStatus(422);
        $this->beacon(json_encode(['s' => 'list_tap', 'pad' => str_repeat('x', 400)]))->assertStatus(422);

        // Another site making its visitors' browsers post here: answered, not counted.
        $this->beacon(json_encode(['s' => 'list_tap']), ['HTTP_SEC_FETCH_SITE' => 'cross-site'])->assertNoContent();
        // A crawler.
        $this->beacon(json_encode(['s' => 'list_tap']), ['HTTP_USER_AGENT' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'])->assertNoContent();

        foreach (GuestFunnel::STAGES as $column) {
            $this->assertSame(0, $this->stat($column), $column);
        }
    }

    public function test_a_checkout_counts_its_start_and_its_end(): void
    {
        $this->event->update(['tickets_enabled' => true]);
        $ticket = $this->createTicket($this->event, ['price' => 0, 'quantity' => 50]);

        $this->post(route('event.checkout', ['subdomain' => $this->role->subdomain]), [
            'event_id' => UrlUtils::encodeId($this->event->id),
            'event_date' => Carbon::parse($this->event->starts_at)->format('Y-m-d'),
            'name' => 'Free Buyer',
            'email' => 'free-buyer@gmail.com',
            'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
        ], $this->browser())->assertRedirect();

        $this->assertSame(1, $this->stat('gp_checkout_starts'));
        $this->assertSame(1, $this->stat('gp_checkouts_done'), 'a free order ends on its ticket in the same request');
    }

    public function test_a_refused_checkout_counts_nothing(): void
    {
        $this->event->update(['tickets_enabled' => true]);
        $ticket = $this->createTicket($this->event, ['price' => 0, 'quantity' => 50]);

        // No name and no email: the form comes back.
        $this->post(route('event.checkout', ['subdomain' => $this->role->subdomain]), [
            'event_id' => UrlUtils::encodeId($this->event->id),
            'event_date' => Carbon::parse($this->event->starts_at)->format('Y-m-d'),
            'tickets' => [UrlUtils::encodeId($ticket->id) => 1],
        ], $this->browser());

        $this->assertSame(0, $this->stat('gp_checkout_starts'));
        $this->assertSame(0, $this->stat('gp_checkouts_done'));
    }

    public function test_a_sign_up_counts_as_a_checkout_too(): void
    {
        $this->event->update(['rsvp_enabled' => true]);

        $this->post(route('event.rsvp', ['subdomain' => $this->role->subdomain]), [
            'event_id' => UrlUtils::encodeId($this->event->id),
            'event_date' => Carbon::parse($this->event->starts_at)->format('Y-m-d'),
            'name' => 'Guest Name',
            'email' => 'guest-name@gmail.com',
        ], $this->browser())->assertRedirect();

        $this->assertSame(1, $this->stat('gp_checkout_starts'));
        $this->assertSame(1, $this->stat('gp_checkouts_done'));
    }

    public function test_a_follow_and_a_mailing_list_sign_up_count(): void
    {
        $fan = $this->createOwner();

        $this->actingAs($fan)->get(route('role.follow', ['subdomain' => $this->role->subdomain]), $this->browser());
        $this->assertTrue($fan->fresh()->isConnected($this->role->subdomain));
        $this->assertSame(1, $this->stat('gp_follows'));

        // Pressing Follow again changes nothing, so it counts nothing (from another address, so
        // this is not the once-a-day rule answering).
        $this->actingAs($fan)->get(route('role.follow', ['subdomain' => $this->role->subdomain]), $this->browser('203.0.113.50'));
        $this->assertSame(1, $this->stat('gp_follows'));
        auth()->logout();

        $this->post(route('role.audience.join', ['subdomain' => $this->role->subdomain]), [
            'name' => 'Reader', 'email' => 'reader@gmail.com',
        ], $this->browser('203.0.113.51'));
        $this->assertSame(2, $this->stat('gp_follows'), 'a new name on the mailing list');
    }

    public function test_the_page_prints_the_beacon_only_for_a_visit_that_counts(): void
    {
        $html = $this->get($this->eventUrl(), $this->browser())->assertOk()->getContent();
        $this->assertStringContainsString('"\/api\/guest-count"', $html);
        $this->assertSame(1, substr_count($html, "count('form_open')"), 'only the listener: the form is closed');

        $list = $this->get('/'.$this->role->subdomain, $this->browser())->assertOk()->getContent();
        $this->assertStringContainsString('"\/api\/guest-count"', $list);
        $this->assertStringContainsString("window.esGuestFunnel('list_tap')", $list);

        // The beacon cannot tell the team from the audience, so their page does not carry it.
        $own = $this->actingAs($this->role->user)->get($this->eventUrl(), $this->browser())->assertOk()->getContent();
        $this->assertStringNotContainsString('guest-count', $own);
    }

    public function test_a_form_that_opens_with_the_page_is_counted_by_the_page(): void
    {
        $this->event->update(['tickets_enabled' => true]);
        $this->createTicket($this->event, ['price' => 10, 'quantity' => 50]);

        $html = $this->get($this->eventUrl().'?tickets=true', $this->browser())->assertOk()->getContent();

        $this->assertSame(2, substr_count($html, "count('form_open')"), 'the listener, and the call made as the page loads');
    }

    public function test_every_add_to_calendar_link_says_what_it_is(): void
    {
        $html = $this->get($this->eventUrl(), $this->browser())->assertOk()->getContent();

        $links = preg_match_all('/<a [^>]*href="[^"]*calendar\.google\.com[^"]*"/', $html, $matches);
        $this->assertGreaterThan(0, $links, 'the page offers Add to calendar');

        foreach ($matches[0] as $tag) {
            $this->assertStringContainsString('data-funnel="calendar_add"', $tag);
        }
    }
}
