<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Newsletter;
use App\Models\Role;
use App\Services\NewsletterService;
use App\Utils\MoneyUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The month: the grid of a schedule's events and the card an event opens, on the guest page,
 * the embed, the admin's Schedule tab and the dashboard.
 *
 * What the month and its card say comes from the same feed the list reads
 * (CalendarDataTrait::calendarEventToVueArray()), so the first things held here are about
 * that feed and the words it is given.
 */
class MonthGridTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        $this->role = $this->createRole($this->createOwner(), 'venue', ['timezone' => 'America/New_York']);
    }

    private function event(array $attrs = [], ?Role $role = null): Event
    {
        $role ??= $this->role;

        return $this->createEvent($role, $attrs + [
            'starts_at' => now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
            'ticket_currency_code' => 'USD',
            'creator_role_id' => $role->id,
        ]);
    }

    /** The row as the live page gets it: the JSON endpoint the month and the list both read. */
    private function row(Event $event, ?Role $role = null): array
    {
        $when = now()->addDays(7);
        $events = $this->getJson('/'.($role ?? $this->role)->subdomain.'/api/calendar-events?year='.$when->year.'&month='.$when->month)->assertOk()->json('events');
        $row = collect($events)->firstWhere('name', $event->name);
        $this->assertNotNull($row, 'the event is in the month');

        return $row;
    }

    /**
     * A free ticket type beside paid ones (free entry, a paid table) was said as "From $0" on
     * the card, on the event page and in a newsletter: three builders, each reading "the
     * lowest price and whether there is a higher one". They ask one rule now.
     */
    public function test_a_free_ticket_beside_paid_ones_is_free_entry_and_never_from_zero(): void
    {
        $this->assertSame('free', Event::priceWording(0, 20));
        $this->assertSame('free', Event::priceWording(0, 0));
        $this->assertSame('from', Event::priceWording(10, 20));
        $this->assertSame('price', Event::priceWording(20, 20));

        $event = $this->event(['name' => 'Free And A Table']);
        $this->createTicket($event, ['price' => 0, 'quantity' => 50]);
        $this->createTicket($event, ['price' => 20, 'quantity' => 50]);
        $date = now()->addDays(7)->format('Y-m-d');
        $zero = MoneyUtils::format(0, 'USD');

        // The card, the list's rows and the month.
        $row = $this->row($event);
        $this->assertTrue($row['ticket_free']);
        $this->assertNull($row['ticket_from']);

        // The event page's price line.
        $summary = $event->fresh()->ticketPriceSummary($date);
        $this->assertSame('free', $summary['says']);
        $this->assertFalse($summary['free'], 'not every type is free: there is still something to pay for');
        $html = $this->get($this->guestEventUrl($this->role, $event))->assertOk()->getContent();
        $this->assertSame(1, preg_match('/data-ticket-price>\s*(.*?)\s*<\/span>/s', $html, $line));
        $this->assertSame($this->role->customLabel('free_entry'), trim($line[1]));
        $this->assertStringNotContainsString($zero, $line[1]);

        // A newsletter's row for it.
        $newsletter = new Newsletter([
            'role_id' => $this->role->id,
            'subject' => 'This week',
            'blocks' => [['id' => 'events', 'type' => 'events', 'data' => ['layout' => 'cards', 'useAllEvents' => true, 'eventIds' => []]]],
            'template' => 'minimal',
            'style_settings' => Newsletter::templateDefaults('minimal'),
        ]);
        $newsletter->setRelation('role', $this->role);
        $mailed = collect(app(NewsletterService::class)->processBlocks($newsletter)[0]['data']['resolvedEvents'])->firstWhere('name', 'Free And A Table');
        $this->assertSame(__('messages.free'), $mailed['price']);
        $this->assertTrue($mailed['buy'], 'and its button still goes to the tickets');

        // Where every type costs something the three say what they said before.
        $paid = $this->event(['name' => 'Two Prices']);
        $this->createTicket($paid, ['price' => 10, 'quantity' => 50]);
        $this->createTicket($paid, ['price' => 25, 'quantity' => 50]);
        $from = __('messages.price_from', ['price' => MoneyUtils::format(10, 'USD')]);
        $this->assertSame($from, $this->row($paid)['ticket_from']);
        $this->assertSame('from', $paid->fresh()->ticketPriceSummary($date)['says']);
        $this->assertStringContainsString($from, $this->get($this->guestEventUrl($this->role, $paid))->assertOk()->getContent());
    }

    /**
     * "Now", "over today" and the zone a card names are worked out on the event's own clock.
     * The feed sent that clock only with an event whose tickets are on sale, so every other
     * event was timed by the page's: the viewer's on the dashboard, the curator's on a
     * curator's page.
     */
    public function test_a_row_carries_its_schedules_clock_whether_or_not_it_sells_tickets(): void
    {
        $la = $this->createRole($this->createOwner(), 'venue', ['timezone' => 'America/Los_Angeles']);
        $plain = $this->event(['name' => 'Just A Date', 'tickets_enabled' => false], $la);
        $selling = $this->event(['name' => 'With Tickets'], $la);
        $this->createTicket($selling, ['price' => 20, 'quantity' => 50]);

        $this->assertSame('America/Los_Angeles', $this->row($selling, $la)['zone']);
        $this->assertSame('America/Los_Angeles', $this->row($plain, $la)['zone']);
        // The card's own fields are as they were: nothing to say about tickets, and no clock.
        $this->assertSame(Event::NO_CARD_TICKET_FIELDS, $plain->fresh()->cardTicketFields());
    }

    /**
     * The dashboard's month asked for events from the 1st, so the days of the month before
     * that its first week shows were always empty there, while a schedule's own month
     * (which asks from the grid's first day) showed the same events.
     */
    public function test_the_dashboards_month_holds_the_days_before_the_first(): void
    {
        $owner = $this->role->users()->first();
        // October 2026 begins on a Thursday: its first week shows Sunday 27 to Wednesday 30 September.
        $this->event([
            'name' => 'Last Tuesday Of September',
            'tickets_enabled' => false,
            'starts_at' => Carbon::parse('2026-09-29 20:00', 'America/New_York')->utc()->format('Y-m-d H:i:s'),
        ]);

        $json = $this->actingAs($owner)->getJson('/dashboard/api/calendar-events?year=2026&month=10')->assertOk()->json();

        $this->assertContains('Last Tuesday Of September', array_column($json['events'], 'name'));
        $this->assertArrayHasKey('2026-09-29', $json['eventsMap']);
    }
}
