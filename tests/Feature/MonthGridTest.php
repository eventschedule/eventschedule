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

    /**
     * One month for the guest page and the embed (role/partials/month), with the card and the
     * day's panel that stand over it (month-peek) and the script that writes each day
     * (month-script). The grid that was there, and its hover popup, are gone from these pages.
     */
    public function test_the_guest_page_and_the_embed_draw_the_one_month(): void
    {
        foreach (['?layout=calendar', '?embed=true&layout=calendar'] as $query) {
            $html = $this->get('/'.$this->role->subdomain.$query)->assertOk()->getContent();

            $this->assertSame(1, substr_count($html, ' data-month '), $query.': one month');
            $this->assertSame(1, substr_count($html, 'id="event-popup"'), $query.': one card, under the id the popup had');
            $this->assertStringContainsString('<div id="event-popup" ref="monthPeekEl" class="gk-peek"', $html);
            $this->assertStringContainsString('class="gk-dayp"', $html);
            $this->assertStringNotContainsString('event-link-popup"', $html, $query.': the old grid is not drawn');
            $this->assertStringNotContainsString('class="event-popup"', $html);
            // The kit, and the script as a mixin of the calendar's app with its directive.
            $this->assertStringContainsString('.gk-cal-week {', $html);
            $this->assertStringContainsString('mixins: [window.monthMixin],', $html);
            $this->assertStringContainsString("calendarApp.directive('clamp', window.monthClamp);", $html);
        }

        // What an embed's card may not offer: its guide tells owners that adding to a calendar
        // and sharing live on the event page, and share is withheld from embeds everywhere else.
        $guest = $this->get('/'.$this->role->subdomain.'?layout=calendar')->getContent();
        $embed = $this->get('/'.$this->role->subdomain.'?embed=true&layout=calendar')->getContent();
        $this->assertStringContainsString('const CAN = {"calendar":true,"share":true,"guest":true};', $guest);
        $this->assertStringContainsString('const CAN = {"calendar":false,"share":false,"guest":true};', $embed);
        // An embed opens an event in a new tab, as it always has; the page itself does not.
        $this->assertStringContainsString(':data-ev="chip.id" :data-date="day.date" :aria-label="chip.label">', $guest);
        $this->assertSame(1, preg_match('/<a class="gk-cal-ev" :class="chip\.cls" :href="chip\.url"\s+target="_blank" rel="noopener"/', $embed));
        $this->assertSame(0, preg_match('/<a class="gk-cal-ev" :class="chip\.cls" :href="chip\.url"\s+target="_blank"/', $guest));
    }

    /**
     * The month is inside the calendar's Vue mount, where anything printed as a text node is
     * compiled as a template. Nothing in it prints an event's own text that way: a name is set
     * by the v-clamp directive (as text, at run time), everything else by v-text, and the
     * script builds no markup from a name.
     */
    public function test_nobodys_text_reaches_the_month_as_markup(): void
    {
        $month = file_get_contents(resource_path('views/role/partials/month.blade.php'));
        $peek = file_get_contents(resource_path('views/role/partials/month-peek.blade.php'));
        $script = file_get_contents(resource_path('views/role/partials/month-script.blade.php'));

        $this->assertStringContainsString('<span class="gk-cal-nm" :dir="chip.dir" v-clamp="chip.name"></span>', $month);
        $this->assertStringContainsString('<bdi v-text="monthPeek.name"></bdi>', $peek);
        $this->assertStringContainsString('<bdi v-text="row.name"></bdi>', $peek);
        foreach ([$month, $peek] as $view) {
            $this->assertStringNotContainsString('v-html', $view);
            // No Vue mustache anywhere: what Blade prints in these two views is our own words.
            $this->assertStringNotContainsString('@{{', $view);
        }
        $this->assertStringNotContainsString('innerHTML', $script);
        $this->assertStringNotContainsString('insertAdjacentHTML', $script);
        // The directive sets text, and only when the name itself changes: the script shortens
        // what is on the page, and a redraw for any other reason must leave that alone.
        $this->assertStringContainsString("el.textContent = binding.value == null ? '' : String(binding.value);", $script);
        $this->assertStringContainsString('if (binding.value === binding.oldValue) return;', $script);
        // The values a card and a row are drawn from carry a DAY: the list's own helpers read
        // the day off the row, and the shared row of a series holds its first date.
        $this->assertStringContainsString('const row = Object.assign({}, e, { occurrenceDate: first, _originalOccurrenceDate: first });', $script);
    }

    /**
     * The card's "add to calendar" for Google and Outlook: the event's own .ics address with
     * ?to=, answered with that calendar's "new entry" page. Behind the gate the .ics has, with
     * nothing in the address taken from the request, and without the description, which has no
     * length cap and would ride in the Location header.
     */
    public function test_the_calendar_links_answer_where_the_ics_does_and_go_only_to_the_two_calendars(): void
    {
        $event = $this->event(['name' => 'Harvest Supper', 'tickets_enabled' => false, 'description_html' => '<p>'.str_repeat('A long evening. ', 400).'</p>']);
        $ical = $this->guestEventUrl($this->role, $event).'/ical';

        $google = $this->get($ical.'?to=google')->assertRedirect();
        $to = $google->headers->get('Location');
        $this->assertStringStartsWith('https://calendar.google.com/calendar/r/eventedit?text='.urlencode($event->getTitle()).'&dates=', $to);
        $this->assertStringNotContainsString('long+evening', $to, 'the description is not in the address');
        $this->assertStringContainsString(urlencode($event->getGuestUrl($this->role->subdomain)), $to, 'the event page is, as in the mails');
        $this->assertLessThan(1200, strlen($to));
        $this->assertStringContainsString('no-store', $google->headers->get('Cache-Control'));
        $this->assertStringContainsString('noindex', $google->headers->get('X-Robots-Tag'));

        $outlook = $this->get($ical.'?to=outlook')->assertRedirect()->headers->get('Location');
        $this->assertStringStartsWith('https://outlook.live.com/calendar/0/deeplink/compose?subject='.urlencode($event->getTitle()), $outlook);

        // Anything else is not a destination: the .ics, as before.
        foreach (['?to=https://evil.example', '?to=yahoo', ''] as $other) {
            $file = $this->get($ical.$other)->assertOk();
            $this->assertStringContainsString('BEGIN:VCALENDAR', $file->getContent());
        }

        // And nothing where the .ics answers nothing: a draft, to a visitor.
        $draft = $this->event(['name' => 'Not Yet', 'tickets_enabled' => false, 'is_draft' => true]);
        $draftIcal = $this->guestEventUrl($this->role, $draft).'/ical';
        $this->get($draftIcal)->assertNotFound();
        $this->get($draftIcal.'?to=google')->assertNotFound();
    }
}
