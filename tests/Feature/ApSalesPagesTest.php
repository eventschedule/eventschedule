<?php

namespace Tests\Feature;

use App\Models\SaleInstallment;
use App\Models\SaleInstallmentPlan;
use App\Models\TicketWaitlist;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The Sales page of the admin portal (ticket/sales and the lists of its tabs), /sales/import and
 * /waitlist, after they were rebuilt on the page kit in October 2026.
 *
 * Each test is something a person could see: an order drawn twice (a table, and cards for a
 * phone), tabs a phone could not reach, a switch that said "off" over a list that included past
 * events, an empty list that told someone searching to go and create events, a waitlist that
 * opened as bare text, a forecast in the order of the alphabet, a queue that always sent "in ~1
 * hours". What the pages look like belongs to the screenshots; these hold what they must say.
 *
 * Assertions count with substr_count()/preg_match_all() and never hand the page to a pattern
 * assertion: a failure would print 600 KB of HTML.
 */
class ApSalesPagesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // No page here may reach the network: the suite loads the developer's .env, where real
        // tokens live, and a page that asks an outside service (the Domains page asked
        // DigitalOcean) would do it for real on every run. A request nothing faked throws.
        \Illuminate\Support\Facades\Http::preventStrayRequests();

        // The Sales list hides past events by default; the fixtures stay ahead of this clock.
        $this->travelTo('2026-06-15 12:00:00');
    }

    /** An owner with one upcoming ticketed event and one paid order by a distinctive buyer. */
    private function scheduleWithASale(array $saleAttrs = []): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['name' => 'Harbor Hall']);
        $event = $this->createEvent($role, [
            'tickets_enabled' => true,
            'creator_role_id' => $role->id,
            'ticket_currency_code' => 'USD',
            'starts_at' => '2026-07-01 23:00:00',
        ]);
        $ticket = $this->createTicket($event, ['price' => 25]);
        $sale = $this->createSale($event, $role, array_merge([
            'name' => 'Zaphod Beeblebrox',
            'email' => 'zaphod@gmail.com',
            'payment_amount' => 25,
            'payment_method' => 'cash',
            'status' => 'paid',
        ], $saleAttrs), $ticket);

        return [$owner, $role, $event, $sale, $ticket];
    }

    private function salesPage(User $user, array $query = []): string
    {
        return $this->actingAs($user)->get(route('sales', $query))->assertOk()->getContent();
    }

    public function test_the_page_opens_with_one_title_row_and_draws_each_order_once(): void
    {
        [$owner] = $this->scheduleWithASale();

        $html = $this->salesPage($owner);

        $this->assertSame(1, substr_count($html, 'class="page-title"'), 'one h1, from the page header');
        $this->assertSame(1, substr_count($html, e(__('messages.sales_lead'))));
        // It was drawn twice: a table from a tablet up, and cards with a menu of their own below it.
        $this->assertSame(1, substr_count($html, 'Zaphod Beeblebrox'), 'one row per order, for every width');
        $this->assertSame(1, substr_count($html, 'data-sale-action="cancel"'));
        $this->assertSame(1, preg_match_all('/<table class="page-table[^"]*sales-list"/', $html));
        // Orders go through the one status mark, not a pill drawn here.
        $this->assertSame(1, preg_match_all('/<span class="event-status is-on">\s*'.preg_quote(__('messages.paid'), '/').'/', $html));
        // The page is plain DOM script: the phone cards were the last Alpine on it.
        $table = substr($html, strpos($html, 'id="sales-table"'), 20000);
        $this->assertStringNotContainsString('x-data', $table);
        $this->assertStringNotContainsString('@click', $table);
    }

    /**
     * The strip had no way to scroll, so on a phone the tabs past the edge of the page could not be
     * reached. One list now feeds the strip and the dropdown a phone gets.
     */
    public function test_every_tab_is_in_the_strip_and_in_the_phones_dropdown(): void
    {
        [$owner, $role, $event] = $this->scheduleWithASale();
        TicketWaitlist::create([
            'event_id' => $event->id, 'event_date' => '2026-07-01', 'name' => 'Trillian',
            'email' => 'trillian@gmail.com', 'subdomain' => $role->subdomain, 'status' => 'waiting',
        ]);

        $html = $this->salesPage($owner);

        foreach (['sales', 'waitlist', 'feedback', 'subscriptions', 'installments', 'gift-cards'] as $tab) {
            $this->assertSame(1, substr_count($html, 'id="tab-'.$tab.'"'), "the $tab tab is in the strip");
            $this->assertSame(1, substr_count($html, '<option value="tab-'.$tab.'"'), "the $tab tab is in the dropdown");
            $this->assertSame(1, substr_count($html, 'id="'.$tab.'-panel"'), "the $tab tab has its pane");
        }
        // The waitlist's count is a pill on its tab.
        $this->assertSame(1, preg_match_all('/id="tab-waitlist"[^>]*>\s*'.preg_quote(__('messages.waitlist'), '/').'\s*<span class="ap-tab-count">1<\/span>/', $html));
    }

    /**
     * A page link of the list carries include_past=1, and the page it loads listed past events
     * under a switch that read "off".
     */
    public function test_the_switch_says_what_the_list_is_showing(): void
    {
        [$owner] = $this->scheduleWithASale();

        $this->assertSame(0, preg_match_all('/id="include-past-sales"[^>]*\schecked/', $this->salesPage($owner)));
        $this->assertSame(1, preg_match_all('/id="include-past-sales"[^>]*\schecked/', $this->salesPage($owner, ['include_past' => 1])));
    }

    /**
     * "No sales found. Create events to start selling tickets." was the answer to a search with no
     * match, and to a list whose orders were all for events that had passed.
     */
    public function test_an_empty_list_says_why_it_is_empty(): void
    {
        [$owner] = $this->scheduleWithASale();

        $searched = $this->salesPage($owner, ['filter' => 'nobody-by-this-name']);
        // As the empty state's heading: the layout carries the same words for its select boxes.
        $this->assertSame(1, substr_count($searched, '<h3 v-pre>'.e(__('messages.no_results_found')).'</h3>'));
        $this->assertSame(0, substr_count($searched, e(__('messages.no_sales_description'))));

        // The same list as the page fetches it while someone types.
        $fetched = $this->actingAs($owner)
            ->get(route('sales', ['filter' => 'nobody-by-this-name']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->getContent();
        $this->assertStringNotContainsString('<html', $fetched, 'the fetched list is the list alone');
        $this->assertSame(1, substr_count($fetched, '<h3 v-pre>'.e(__('messages.no_results_found')).'</h3>'));

        $this->travelTo('2026-08-01 12:00:00');
        $upcomingOnly = $this->salesPage($owner->fresh());
        $hint = e(__('messages.no_sales_past_hint', ['label' => __('messages.include_past_events')]));
        $this->assertSame(0, substr_count($upcomingOnly, 'Zaphod Beeblebrox'), 'the event has passed');
        $this->assertSame(1, substr_count($upcomingOnly, $hint), 'and the list says where its orders went');
        $this->assertSame(0, substr_count($this->salesPage($owner->fresh(), ['include_past' => 1]), $hint));
    }

    /**
     * /waitlist answers the Waitlist tab's fetch with the list alone. Visited, it was the same
     * fragment: a table with no stylesheet, no sidebar and no way back.
     */
    public function test_the_waitlist_is_a_page_when_visited_and_a_list_when_fetched(): void
    {
        [$owner, $role, $event] = $this->scheduleWithASale();
        $entry = TicketWaitlist::create([
            'event_id' => $event->id, 'event_date' => '2026-07-01', 'name' => 'Slartibartfast',
            'email' => 'slarti@gmail.com', 'subdomain' => $role->subdomain, 'status' => 'notified',
        ]);

        $page = $this->actingAs($owner)->get(route('waitlist.index'))->assertOk()->getContent();
        $this->assertSame(1, substr_count($page, 'class="page-title"'));
        $this->assertSame(1, substr_count($page, 'id="sidebar"'), 'inside the portal, sidebar and all');
        $this->assertSame(1, preg_match_all('/<a href="'.preg_quote(e(route('sales')), '/').'" class="page-back">/', $page), 'its way back is Sales');
        $this->assertSame(1, substr_count($page, 'Slartibartfast'));
        $this->assertSame(1, substr_count($page, 'class="event-status is-warn">'.__('messages.notified')));

        $fetched = $this->actingAs($owner)
            ->get(route('waitlist.index'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->getContent();
        $this->assertStringNotContainsString('<html', $fetched);
        $this->assertStringNotContainsString('page-title', $fetched);
        $this->assertSame(1, substr_count($fetched, 'Slartibartfast'));
        $this->assertSame(1, substr_count($fetched, 'js-waitlist-remove'), 'one Remove per person, for every width');
        $this->assertSame(1, substr_count($fetched, 'data-id="'.\App\Utils\UrlUtils::encodeId($entry->id).'"'));

        // Nobody waiting for something still to come: the list says so, and where the rest are.
        $this->travelTo('2026-08-01 12:00:00');
        $later = $this->actingAs($owner->fresh())
            ->get(route('waitlist.index'), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->getContent();
        $this->assertSame(0, substr_count($later, 'Slartibartfast'));
        $this->assertSame(1, substr_count($later, e(__('messages.waitlist_empty_upcoming'))));
    }

    /**
     * The forecast was sorted by its label, so "Dec 2026" came before "Nov 2026" and "Jan 2027"
     * before both.
     */
    public function test_the_installment_forecast_runs_in_the_order_of_the_calendar(): void
    {
        [$owner, , , $sale] = $this->scheduleWithASale(['payment_method' => 'stripe', 'payment_amount' => 400]);

        $plan = SaleInstallmentPlan::create([
            'sale_id' => $sale->id, 'currency' => 'USD', 'total_amount' => 400, 'amount_paid' => 100,
            'installment_count' => 4, 'status' => 'active', 'secret' => Str::random(32),
        ]);
        foreach ([['2026-06-01', 'paid'], ['2026-11-05', 'scheduled'], ['2026-12-05', 'scheduled'], ['2027-01-05', 'scheduled']] as $i => [$due, $status]) {
            SaleInstallment::create([
                'sale_installment_plan_id' => $plan->id, 'sequence' => $i + 1, 'amount' => 100,
                'due_at' => $due.' 12:00:00', 'status' => $status,
            ]);
        }

        $html = $this->salesPage($owner);

        $november = strpos($html, 'Nov 2026');
        $december = strpos($html, 'Dec 2026');
        $january = strpos($html, 'Jan 2027');
        $this->assertNotFalse($november);
        $this->assertNotFalse($december);
        $this->assertNotFalse($january);
        $this->assertTrue($november < $december && $december < $january, 'November, December, then January');

        // And the plan is one row of the one list, with its figures named for a phone.
        $this->assertSame(1, substr_count($html, '1 / 4'));
        $this->assertSame(1, substr_count($html, 'data-label="'.e(__('messages.installments_outstanding')).'"'));
    }

    /**
     * Carbon's diffInHours() is signed, so "hours until a time still to come" was negative: every
     * queue read "in ~1 hours", and one more than a day away never showed its day.
     */
    public function test_the_feedback_queue_says_when_it_will_really_be_sent(): void
    {
        // A selfhost install sends through its one mailer, which must be a real one.
        config(['app.hosted' => false, 'mail.default' => 'smtp']);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['feedback_enabled' => true, 'feedback_delay_hours' => 24]);
        // Ended three hours ago, so the request goes out in about twenty-one.
        $event = $this->createEvent($role, [
            'tickets_enabled' => true, 'creator_role_id' => $role->id,
            'starts_at' => Carbon::now()->subHours(5)->format('Y-m-d H:i:s'), 'duration' => 2,
        ]);
        $ticket = $this->createTicket($event, ['price' => 10]);
        $this->createSale($event, $role, [
            'name' => 'Ford Prefect', 'email' => 'ford@gmail.com', 'payment_amount' => 10, 'status' => 'paid',
            'event_date' => Carbon::parse($event->starts_at)->setTimezone($role->timezone)->format('Y-m-d'),
        ], $ticket);

        $html = $this->actingAs($owner)
            ->get(route('sales', ['tab' => 'feedback']), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->getContent();

        $this->assertStringNotContainsString('<html', $html);
        $this->assertSame(1, substr_count($html, e(__('messages.feedback_pending_emails'))));
        $this->assertSame(0, substr_count($html, e(__('messages.feedback_sends_in', ['count' => 1]))), 'not "in ~1 hours"');
        $this->assertGreaterThanOrEqual(1, preg_match_all('/'.preg_quote(e(__('messages.feedback_sends_in', ['count' => 'N'])), '/').'/', str_replace(['21', '22'], 'N', $html)), 'about twenty-one hours');
        // One list for every width: the attendee is named once, in the row that opens.
        $this->assertSame(1, substr_count($html, 'Ford Prefect'));
        // The figures are the kit's strip, not four hand-made cards with icons.
        $this->assertSame(0, substr_count($html, 'dashboard-icon'));
        $this->assertSame(4, substr_count($html, 'class="page-stat"'));
    }

    public function test_the_import_page_hangs_from_sales_and_gates_a_free_schedule_by_name(): void
    {
        config(['app.hosted' => true]);

        [$owner, $role, $event] = $this->scheduleWithASale();

        $html = $this->actingAs($owner)
            ->get(route('sales.import', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'event_id' => \App\Utils\UrlUtils::encodeId($event->id)]))
            ->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'class="page-title"'));
        $this->assertSame(1, preg_match_all('/<a href="'.preg_quote(e(route('sales')), '/').'" class="page-back">/', $html), 'the way back is named Sales');
        $this->assertSame(0, preg_match_all('/>\s*'.preg_quote(__('messages.back'), '/').'\s*<\/a>/', $html), 'and is not a "Back" button');
        // The Vue apps and what the form posts are as they were.
        $this->assertSame(1, substr_count($html, 'id="event-picker-app"'));
        $this->assertSame(1, substr_count($html, 'id="import-attendees-app"'));

        $free = $this->createFreeRole($owner, 'venue', ['name' => 'Corner Cafe']);
        $this->assertFalse($free->fresh()->isPro());

        $gated = $this->actingAs($owner->fresh())
            ->get(route('sales.import', ['role_id' => \App\Utils\UrlUtils::encodeId($free->id)]))
            ->assertOk()->getContent();

        $this->assertSame(1, substr_count($gated, 'id="role-filter"'), 'another schedule can still be chosen');
        $this->assertSame(0, substr_count($gated, 'id="event-picker-app"'), 'no event to pick on a schedule without import');
        $this->assertSame(0, substr_count($gated, 'id="import-attendees-app"'));
        $this->assertGreaterThanOrEqual(1, substr_count($gated, e(route('role.subscribe', ['subdomain' => $free->subdomain, 'tier' => 'pro']))), 'the gate leads to THIS schedule\'s plan');
    }
}
