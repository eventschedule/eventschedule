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
        $this->assertSame(1, preg_match('/data-ticket-price>\s*(.*?)\s*<\/span>/s', $this->get($this->guestEventUrl($this->role, $paid))->assertOk()->getContent(), $paidLine));
        $this->assertSame($from, trim($paidLine[1]), 'the price line itself, not some other mention of the price');
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

        // The two pages the clock was wrong on. A curator in New York that lists the event: its
        // page's clock is the curator's, the event's is still Los Angeles.
        $curator = $this->createRole($this->createOwner(), 'curator', ['timezone' => 'America/New_York']);
        $plain->roles()->attach($curator->id, ['is_accepted' => true]);
        $this->assertSame('America/Los_Angeles', $this->row($plain, $curator)['zone']);
        // And the dashboard of the venue's owner, whose own clock is neither.
        $owner = $la->users()->first();
        $owner->timezone = 'Europe/Berlin';
        $owner->save();
        $when = now()->addDays(7);
        $mine = collect($this->actingAs($owner)->getJson('/dashboard/api/calendar-events?year='.$when->year.'&month='.$when->month)->assertOk()->json('events'))->firstWhere('name', 'Just A Date');
        $this->assertSame('America/Los_Angeles', $mine['zone']);
        auth()->logout();
    }

    /**
     * Edit Event is offered where the page it leads to will open. With no schedule in hand (the
     * dashboard) the link is event.edit_admin, which wants a schedule the event is on that the
     * person edits: an event somebody made on a schedule they do not edit was offered it, on the
     * card's main button, and the page answered 403.
     */
    public function test_the_dashboard_offers_edit_only_where_the_edit_page_opens(): void
    {
        $owner = $this->role->users()->first();
        $someoneElses = $this->createRole($this->createOwner(), 'venue', ['timezone' => 'America/New_York']);
        $this->event(['name' => 'On My Own Schedule', 'tickets_enabled' => false]);
        $sent = $this->event(['name' => 'Sent To Another Schedule', 'tickets_enabled' => false, 'user_id' => $owner->id], $someoneElses);

        $when = now()->addDays(7);
        $rows = collect($this->actingAs($owner)->getJson('/dashboard/api/calendar-events?year='.$when->year.'&month='.$when->month)->assertOk()->json('events'))->keyBy('name');

        $this->assertTrue($rows['On My Own Schedule']['can_edit']);
        $this->assertNotNull($rows['On My Own Schedule']['edit_url']);
        $this->get($rows['On My Own Schedule']['edit_url'])->assertRedirect();

        $this->assertTrue($rows->has('Sent To Another Schedule'), 'what the person made is still on their dashboard');
        $this->assertFalse($rows['Sent To Another Schedule']['can_edit'], 'but nothing offers to edit it from there');
        $this->assertNull($rows['Sent To Another Schedule']['edit_url']);
        $this->get(route('event.edit_admin', ['hash' => \App\Utils\UrlUtils::encodeId($sent->id)]))->assertForbidden();
    }

    /**
     * The plus on a day of the dashboard's month goes where the dashboard's own Add Event goes:
     * a schedule the person EDITS. It went to the first schedule they belong to by name, which
     * can be one they only view, and the form turned them away.
     */
    public function test_the_dashboards_plus_goes_to_a_schedule_the_person_edits(): void
    {
        $owner = $this->role->users()->first();
        $this->role->forceFill(['name' => 'Zeta Hall'])->saveQuietly();
        $viewed = $this->createRole($this->createOwner(), 'venue', ['name' => 'Alpha Rooms']);
        $viewed->users()->attach($owner->id, ['level' => 'viewer']);

        $home = $this->actingAs($owner)->get(route('home'))->assertOk()->getContent();
        $this->assertSame(1, preg_match('/const CAN = \{.*"add":"([^"]*)"\};/', $home, $found));
        $add = stripslashes($found[1]);
        $this->assertStringContainsString($this->role->subdomain, $add);
        $this->assertStringNotContainsString($viewed->subdomain, $add);
        $this->assertStringEndsWith('date=MONTH-DATE', $add);
        $this->get(str_replace('MONTH-DATE', now()->addDays(3)->format('Y-m-d'), $add))->assertOk();
    }

    /**
     * Draft, Internal and Cancelled are said in the month wherever the feed sends such events:
     * to the schedule's own people, and to nobody else.
     */
    public function test_a_draft_is_sent_to_its_schedules_people_and_to_nobody_else(): void
    {
        $owner = $this->role->users()->first();
        $this->event(['name' => 'Announced', 'tickets_enabled' => false]);
        $this->event(['name' => 'Not Announced Yet', 'tickets_enabled' => false, 'is_draft' => true]);
        $when = now()->addDays(7);
        $query = '?year='.$when->year.'&month='.$when->month;

        $visitor = collect($this->getJson('/'.$this->role->subdomain.'/api/calendar-events'.$query)->assertOk()->json('events'))->keyBy('name');
        $this->assertTrue($visitor->has('Announced'));
        $this->assertFalse($visitor->has('Not Announced Yet'), 'a visitor is not sent a draft');

        $mine = collect($this->actingAs($owner)->getJson(route('role.admin_calendar_events', ['subdomain' => $this->role->subdomain]).$query)->assertOk()->json('events'))->keyBy('name');
        $this->assertTrue((bool) $mine['Not Announced Yet']['is_draft'], 'its owner is, marked as one');
        $this->assertFalse((bool) $mine['Announced']['is_draft']);

        // And the month says so on every day the event is written, one that is over too: the
        // mark is not among the things kept for a day still to come.
        $script = file_get_contents(resource_path('views/role/partials/month-script.blade.php'));
        $this->assertStringContainsString("if (mark) notes.push({ k: 'mark', cls: 'gk-cal-note-say', text: mark[1], mark: mark[2] });", $script);
        $this->assertSame(0, preg_match('/if \(opt\.sub !== \'none\'\) \{\s*if \(mark\)/', $script));
        $this->assertStringContainsString('(told || f.cancelled)', $script);
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

        // The grid's first cell, Sunday 27 September. The bound is the VIEWER's midnight and an
        // event is placed on its schedule's day: eight in the morning in Berlin is 06:00 UTC,
        // an hour before midnight in Los Angeles is over, and the cell was empty.
        $berlin = $this->createRole($owner, 'venue', ['timezone' => 'Europe/Berlin']);
        $this->event([
            'name' => 'Sunday Morning In Berlin',
            'tickets_enabled' => false,
            'starts_at' => Carbon::parse('2026-09-27 08:00', 'Europe/Berlin')->utc()->format('Y-m-d H:i:s'),
        ], $berlin);
        $owner->timezone = 'America/Los_Angeles';
        $owner->save();
        $json = $this->actingAs($owner)->getJson('/dashboard/api/calendar-events?year=2026&month=10')->assertOk()->json();
        $this->assertContains('Sunday Morning In Berlin', array_column($json['events'], 'name'));
        $this->assertArrayHasKey('2026-09-27', $json['eventsMap']);
    }

    /**
     * One month for the four pages that show one (role/partials/month): the guest page, the
     * embed, the admin's Schedule tab and the dashboard, with the card and the day's panel that
     * stand over it (month-peek) and the script that writes each day (month-script). The grid
     * that was written twice, and its hover popup, are gone.
     */
    public function test_the_four_pages_draw_the_one_month(): void
    {
        $owner = $this->role->users()->first();
        $pages = [
            'the guest page' => fn () => $this->get('/'.$this->role->subdomain.'?layout=calendar'),
            'the embed' => fn () => $this->get('/'.$this->role->subdomain.'?embed=true&layout=calendar'),
            'the Schedule tab' => fn () => $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'schedule'])),
            'the dashboard' => fn () => $this->actingAs($owner)->get(route('home')),
        ];
        foreach ($pages as $query => $page) {
            $html = $page()->assertOk()->getContent();

            $this->assertSame(1, substr_count($html, ' data-month '), $query.': one month');
            $this->assertSame(1, substr_count($html, 'id="event-popup"'), $query.': one card, under the id the popup had');
            $this->assertStringContainsString('<div id="event-popup" ref="monthPeekEl" class="gk-peek"', $html);
            $this->assertStringContainsString('class="gk-dayp"', $html);
            foreach (['event-link-popup', 'class="event-popup"', 'initPopups', 'calendar-day-navigate', 'has-tooltip', 'id="tooltip"'] as $gone) {
                $this->assertStringNotContainsString($gone, $html, $query.': '.$gone.' went with the old grid');
            }
            // The kit, and the script as a mixin of the calendar's app with its directive.
            $this->assertStringContainsString('.gk-cal-week {', $html);
            $this->assertStringContainsString('mixins: [window.monthMixin],', $html);
            $this->assertStringContainsString("calendarApp.directive('clamp', window.monthClamp);", $html);
        }

        auth()->logout();
        // What an embed's card may not offer: its guide tells owners that adding to a calendar
        // and sharing live on the event page, and share is withheld from embeds everywhere else.
        $guest = $this->get('/'.$this->role->subdomain.'?layout=calendar')->getContent();
        $embed = $this->get('/'.$this->role->subdomain.'?embed=true&layout=calendar')->getContent();
        $this->assertStringContainsString('const CAN = {"calendar":true,"share":true,"guest":true,"add":null};', $guest);
        $this->assertStringContainsString('const CAN = {"calendar":false,"share":false,"guest":true,"add":null};', $embed);
        // The admin's pages stand on the portal's own tokens, which the guest pages must not get.
        $admin = $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'schedule']))->getContent();
        $this->assertStringContainsString('--gk-solid: rgb(var(--ap-surface));', $admin);
        $this->assertStringNotContainsString('--gk-solid: rgb(var(--ap-surface));', $guest);
        auth()->logout();
        // The kit is printed where a calendar is drawn and nowhere else: it is 34 KB, and the
        // event page, the checkout and the rest of the portal have no month.
        $supper = $this->event(['name' => 'Kit Check', 'tickets_enabled' => false]);
        $eventPage = $this->get($this->guestEventUrl($this->role, $supper))->assertOk()->getContent();
        $this->assertStringNotContainsString('.gk-cal-week {', $eventPage);
        $settings = $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'followers']))->assertOk()->getContent();
        $this->assertStringNotContainsString('.gk-cal-week {', $settings);
        $this->assertStringContainsString('.gk-cal-week {', $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'availability']))->assertOk()->getContent());
        auth()->logout();
        // Its notes are for whoever edits it and are not sent.
        $from = strpos($guest, '.gk-cal {');
        $kitAsSent = substr($guest, $from, strpos($guest, '</style>', $from) - $from);
        $this->assertGreaterThan(20000, strlen($kitAsSent));
        $this->assertStringNotContainsString('/*', $kitAsSent);
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

        // A series: the night in the address is the night of the entry, at the venue's hour.
        $first = Carbon::now('America/New_York')->subWeeks(3)->setTime(19, 30, 0);
        $series = $this->event([
            'name' => 'Weekly Session', 'tickets_enabled' => false,
            'starts_at' => $first->copy()->utc()->format('Y-m-d H:i:s'),
            'days_of_week' => str_pad(str_repeat('0', $first->dayOfWeek).'1', 7, '0'), 'recurring_frequency' => 'weekly',
        ]);
        $night = $first->copy()->addWeeks(5);
        $stamp = fn (Carbon $at) => $at->copy()->utc()->format('Ymd\THis\Z');
        $entry = $this->get($this->guestEventUrl($this->role, $series, $night->format('Y-m-d')).'/ical?to=google')->assertRedirect()->headers->get('Location');
        $this->assertStringContainsString('&dates='.$stamp($night).'/', $entry, 'the night that was asked for');
        // A day the series is not on is nobody's night: the entry is the series' own start.
        $offDay = $night->copy()->addDay();
        $entry = $this->get($this->guestEventUrl($this->role, $series, $offDay->format('Y-m-d')).'/ical?to=google')->assertRedirect()->headers->get('Location');
        $this->assertStringContainsString('&dates='.$stamp($first).'/', $entry);

        // A date that is no date (the route lets any eight digits through) is nobody's night
        // either. It was a 500 here for a moment, where the file itself has always ignored it.
        $entry = $this->get($this->guestEventUrl($this->role, $series, '2026-13-45').'/ical?to=google')->assertRedirect()->headers->get('Location');
        $this->assertStringContainsString('&dates='.$stamp($first).'/', $entry);
        $this->get($this->guestEventUrl($this->role, $series, '2026-13-45').'/ical')->assertOk();
        // And with no night, the page the entry names is the series' own, with no date in it.
        $this->assertStringContainsString(urlencode($series->getUndatedGuestUrl($this->role->subdomain, true)), $entry);
        $this->assertStringNotContainsString(urlencode('/'.$first->format('Y-m-d')), $entry);

        // A cancelled event is left to the file, which can say it is cancelled.
        $off = $this->event(['name' => 'Rained Off', 'tickets_enabled' => false, 'is_cancelled' => true]);
        $file = $this->get($this->guestEventUrl($this->role, $off).'/ical?to=google')->assertOk()->getContent();
        $this->assertStringContainsString('STATUS:CANCELLED', $file);

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

    /**
     * The Availability tab keeps the grid a team member marks their days on; it is not the month.
     * On the Schedule tab the days people are away are told by name: as text, by the name as it
     * was written, and one entry a member. They were printed as markup in a tooltip, and keyed
     * by the escaped name, which folds two members who share a name into one.
     */
    public function test_who_is_away_is_told_as_text_and_the_availability_grid_stays(): void
    {
        $owner = $this->role->users()->first();
        // Today: on the month's page whatever the day of the month the suite runs on.
        $day = now()->format('Y-m-d');
        $longAgo = now()->subYears(2)->format('Y-m-d');
        $swallows = 'Dana <b>Bold</b> <!--<script';
        foreach ([$swallows, 'Sam Rivers', 'Sam Rivers'] as $name) {
            $member = \App\Models\User::factory()->create(['name' => $name, 'email_verified_at' => now()]);
            $this->role->users()->attach($member->id, ['level' => 'admin', 'dates_unavailable' => json_encode([$longAgo, $day])]);
        }

        $schedule = $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'schedule']))->assertOk()->getContent();
        $this->assertSame(1, preg_match('/const AWAY = (\[.*?\]);\n/', $schedule, $found));
        $away = json_decode($found[1], true);
        $this->assertSame([$swallows, 'Sam Rivers', 'Sam Rivers'], array_column($away, 'name'), 'by name as written, one entry a member');
        $this->assertSame([[$day], [$day], [$day]], array_column($away, 'dates'), 'the days this page shows, not every day a member ever marked');
        // The name is in a script block. Written by @json from a bare variable, its "<" is
        // \u003C there: handed an expression with a comma the directive drops that escaping, and
        // a name holding "<!--<script" keeps the block's own closing tag from ending it.
        $this->assertStringNotContainsString('<!--<script', $schedule, 'a name cannot swallow the script it is printed in');
        $this->assertStringContainsString('Dana \u003Cb\u003EBold\u003C\/b\u003E \u003C!--\u003Cscript', $found[1]);
        // Printed through bindings: an attribute and a text node, never markup.
        $month = file_get_contents(resource_path('views/role/partials/month.blade.php'));
        $this->assertStringContainsString('<span v-if="day.away" class="gk-cal-away" tabindex="0" role="img" :title="day.away" :aria-label="day.away">', $month);
        $this->assertStringContainsString('<bdi v-text="monthDayPanel.away"></bdi>', file_get_contents(resource_path('views/role/partials/month-peek.blade.php')));

        // A plus on a day for whoever may add an event; a viewer may not.
        $this->assertSame(1, preg_match('/const CAN = \{.*"add":"[^"]*add-event\?date=MONTH-DATE"\};/', $schedule));
        $viewer = \App\Models\User::factory()->create(['email_verified_at' => now()]);
        $this->role->users()->attach($viewer->id, ['level' => 'viewer']);
        $asViewer = $this->actingAs($viewer)->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'schedule']))->assertOk()->getContent();
        $this->assertSame(1, preg_match('/const CAN = \{.*"add":null\};/', $asViewer));

        $availability = $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'availability']))->assertOk()->getContent();
        $this->assertSame(0, substr_count($availability, ' data-month '), 'the Availability tab is not the month');
        $this->assertSame(0, substr_count($availability, 'id="event-popup"'));
        // Its own grid of days is there, in the month's style: every day a thing that can be
        // pressed and that says whether it is, with the names its script and the browser tests
        // know it by (.day-element, data-date, .day-x).
        $this->assertSame(1, substr_count($availability, 'class="gk-cal gk-cal-pick" data-availability-grid'));
        preg_match_all('/<div class="gk-cal-day[^"]* day-element" data-date="(\d{4}-\d{2}-\d{2})"\s+role="button" tabindex="0" aria-pressed="(true|false)"/', $availability, $days);
        $this->assertContains(count($days[1]), [28, 35, 42], 'whole weeks of days');
        $this->assertSame(count($days[1]), substr_count($availability, 'day-element" data-date='), 'every day can be pressed and says its state');
        $this->assertSame(count($days[1]) / 7, substr_count($availability, '<div class="gk-cal-week">'), 'seven days to a week');
        foreach (['grid-cols-7 grid-rows-', 'bg-gray-200 dark:bg-gray-700 text-xs leading-6', 'rgba(239, 68, 68, 0.1)'] as $old) {
            $this->assertStringNotContainsString($old, $availability, 'the old grid\'s look is gone: '.$old);
        }
        $this->assertStringContainsString('.day-x { position: absolute; inset: 0;', $availability, 'the mark is the kit\'s');
        $this->assertStringContainsString('content: attr(data-label);', $availability, 'and its word comes from the page, never from the stylesheet');

        // A day the owner marked is drawn marked, and says so.
        $this->role->users()->updateExistingPivot($owner->id, ['dates_unavailable' => json_encode([$day])]);
        $marked = $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'availability']))->assertOk()->getContent();
        $this->assertSame(1, preg_match('/day-element" data-date="'.$day.'"\s+role="button" tabindex="0" aria-pressed="true"[^>]*>.*?<div class="day-x" data-label="'.preg_quote(e(__('messages.unavailable')), '/').'"><\/div>/s', $marked));
        $this->assertSame(1, substr_count($marked, 'aria-pressed="true"'));
        $this->assertSame(1, substr_count($marked, '<div class="day-x" data-label='));

        // While the month loads, the page shows the month's own frame.
        $this->assertSame(1, substr_count($schedule, 'class="gk-cal gk-cal-wait hidden md:block animate-pulse"'));
        $this->assertSame(1, substr_count($availability, 'class="gk-cal gk-cal-wait gk-cal-pick animate-pulse"'));
    }

    /**
     * The calendar's wrapper sets the schedule's accent inline AS THE OWNER TYPED IT, for the
     * older parts inside it. The label that goes on a fill (--es-accent-text) is worked out by
     * GuestTheme for the fill GuestTheme chose, which is another colour for a grey, a near-white
     * or a near-black accent: read from inside the wrapper, a black accent on a dark page drew
     * today's number and a bar's label black on black. So the month takes its fill from the
     * page (--cal-fill, declared on body, where the theme's own tokens are), never --es-accent.
     */
    public function test_the_month_takes_its_fill_from_the_page_and_not_from_the_calendars_wrapper(): void
    {
        $kit = file_get_contents(resource_path('views/partials/month-kit-styles.blade.php'));

        $this->assertSame(1, preg_match('/^\s*body \{ --cal-fill: var\(--es-accent, #4E81FA\); \}$/m', $kit), 'the fill is read where the theme declares it');
        preg_match_all('/^\s*(?::where\(\.dark\) )?(\.gk-cal[^{]*)\{([^}]*)\}/m', $kit, $rules, PREG_SET_ORDER);
        $this->assertGreaterThan(100, count($rules));
        foreach ($rules as [, $selector, $body]) {
            $this->assertStringNotContainsString('var(--es-accent)', $body, trim($selector).' reads the wrapper\'s accent');
        }
        // Nor does anything else in the kit: the card's filled button is also the empty month's
        // "Go to November", which stands INSIDE the wrapper, and was white on white there.
        $this->assertSame(0, substr_count($kit, 'var(--es-accent)'), 'no rule of the kit reads the accent as typed');
        $this->assertStringContainsString('.gk-peek-btn-primary { background: var(--cal-fill); color: var(--es-accent-text);', $kit);
        // The pair that was unreadable: both halves now come from the same place.
        $this->assertSame(1, preg_match('/\.gk-cal-day-today \.gk-cal-num \{ background: var\(--cal-fill\); color: var\(--es-accent-text\);/', $kit));
        $this->assertSame(1, preg_match('/\.gk-cal-span \{[^}]*background: var\(--cal-fill\);[^}]*color: var\(--es-accent-text\);/', $kit));

        // And the page does carry the theme's tokens on body, with a grey moved to ink.
        $this->role->forceFill(['accent_color' => '#888888'])->saveQuietly();
        $html = $this->get('/'.$this->role->subdomain.'?layout=calendar')->assertOk()->getContent();
        $theme = \App\Utils\GuestTheme::fromAccent('#888888');
        $this->assertNotSame('#888888', strtolower($theme->fill), 'the theme does not fill with a mid grey');
        $this->assertStringContainsString('body { --es-accent: '.$theme->fill.';', $html);
        $this->assertStringContainsString('style="--es-accent: #888888;', $html, 'the wrapper still carries the accent as typed');
    }

    /**
     * A clock time is read left to right in every language: in a Hebrew month "7 PM", left to
     * itself, is drawn "PM 7". The card and the day's panel wrapped theirs from the start; an
     * event's own time in the month did not. The wrap is INSIDE the element that is placed at
     * the end of the line, because a direction set on that element itself would turn its own
     * start and end around.
     */
    public function test_a_clock_time_in_the_month_is_read_left_to_right(): void
    {
        $month = file_get_contents(resource_path('views/role/partials/month.blade.php'));
        $peek = file_get_contents(resource_path('views/role/partials/month-peek.blade.php'));

        $this->assertSame(2, substr_count($month, '<bdi dir="ltr" v-text="chip.time"></bdi>'), 'an event\'s time, in both of its places');
        $this->assertSame(0, preg_match('/class="gk-cal-time[^"]*"[^>]*v-text=/', $month), 'never printed bare');
        $this->assertSame(0, preg_match('/class="gk-cal-time[^"]*"[^>]*\sdir=/', $month), 'and the direction is not on the placed element');
        $this->assertStringContainsString('<bdi dir="ltr" class="gk-cal-more-from"', $month);
        $this->assertStringContainsString('<bdi dir="ltr" v-text="row.time"></bdi>', $peek);
        $this->assertGreaterThanOrEqual(3, substr_count($peek, 'dir="ltr" class="gk-peek-clock"'));
    }

    /**
     * Small text in the month holds 4.5:1 on whatever it stands on, for any accent and in each
     * of the admin's six palettes. These are the rules that were measured (the numbers are in
     * the stylesheet's own comments); a browser is what proves them, this keeps them from being
     * tidied away.
     */
    public function test_the_quieter_inks_step_up_where_an_event_is_tinted(): void
    {
        $kit = file_get_contents(resource_path('views/partials/month-kit-styles.blade.php'));

        // Pointed at: the second ink. Its card open: the full ink, a state's colour included.
        $this->assertStringContainsString('.gk-cal-ev, .gk-cal-more { --cal-quiet: var(--gk-ink-3); }', $kit);
        $this->assertStringContainsString('.gk-cal-ev:hover, .gk-cal-more:hover { --cal-quiet: var(--gk-ink-2); }', $kit);
        $this->assertStringContainsString('.gk-cal-ev.gk-cal-ev-on, .gk-cal-ev.gk-cal-ev-on:hover { --cal-quiet: var(--gk-ink); --cal-say: var(--gk-ink); }', $kit);
        // Nothing an event prints is pinned to the third ink any more.
        preg_match_all('/^\s*(\.gk-cal-(?:time|flag|note|more-from|ev-past \.gk-cal-name|ev-off \.gk-cal-name)[^{]*)\{([^}]*)\}/m', $kit, $rules, PREG_SET_ORDER);
        $this->assertGreaterThanOrEqual(6, count($rules));
        foreach ($rules as [, $selector, $body]) {
            $this->assertSame(0, preg_match('/color: var\(--gk-ink-3\)/', $body), trim($selector).' is fixed to the third ink');
        }
        foreach (['warn' => '--gk-warn', 'ok' => '--gk-ok'] as $state => $token) {
            $this->assertStringContainsString('.gk-cal-note-'.$state.' { color: var(--cal-say, var('.$token.'));', $kit);
        }
        // A past bar is a band its words read on, and stays that band while its card is open.
        $this->assertStringContainsString('.gk-cal-span-past { background: color-mix(in srgb, var(--cal-fill) 22%, var(--gk-solid)); color: var(--gk-ink-2); }', $kit);
        $this->assertStringContainsString('.gk-cal-span-past.gk-cal-ev-on { background: color-mix(in srgb, var(--cal-fill) 22%, var(--gk-solid)); }', $kit);
        // The admin's month: a third ink and a readable blue that the portal's own are not.
        $this->assertStringContainsString('--gk-ink-3: color-mix(in srgb, rgb(var(--ap-ink-2)) 50%, rgb(var(--ap-ink-3)));', $kit);
        $this->assertStringContainsString('--es-accent-readable: color-mix(in srgb, var(--brand-blue) 70%, rgb(var(--ap-ink)));', $kit);
    }

    /**
     * What a reading of the page's scripts found after the month was on its four pages, each
     * pinned where the server can see it (a browser is what proves the behaviour).
     */
    public function test_what_the_ship_review_found_stays_fixed(): void
    {
        $owner = $this->role->users()->first();
        $script = file_get_contents(resource_path('views/role/partials/month-script.blade.php'));
        $month = file_get_contents(resource_path('views/role/partials/month.blade.php'));

        // A name is on the page before the month is fitted around it: a watcher that waits for
        // the draw runs ahead of a directive's mounted and updated hooks.
        $this->assertSame(1, preg_match('/window\.monthClamp = \{.*?beforeMount\(el, binding\).*?beforeUpdate\(el, binding\)/s', $script));
        $this->assertSame(0, preg_match('/window\.monthClamp = \{[^}]*?\n    (mounted|updated)\(/s', $script));

        // "Nothing scheduled in October" is a statement about the schedule: not said while a
        // filter is what hid the month's events.
        $this->assertStringContainsString('v-if="!isLoadingEvents && !loadFailed && monthIsBare && narrowingFilterCount === 0" class="gk-cal-empty"', $month);

        // A night that runs past midnight is not over at midnight.
        $this->assertStringContainsString('const past = start ? ended : last < now.slice(0, 10);', $script);

        // The dashboard is the person's own page: a Hebrew reader's month runs right to left and
        // writes its dates in Hebrew. It was forced left to right, in English.
        $owner->language_code = 'he';
        $owner->save();
        $home = $this->actingAs($owner)->get(route('home'))->assertOk()->getContent();
        $this->assertSame(1, preg_match('/\bisRtl: true,/', $home));
        $this->assertStringContainsString("languageCode: 'he',", $home);
        // In Blade's eyes as well as Vue's: the wrappers of the calendar are told the same.
        $this->assertSame(1, preg_match('/<header class="rtl"\s/', $home));
        $owner->language_code = 'en';
        $owner->save();
        app()->setLocale('en');

        // Edit Event stays in the tab, as Add Event does; View Event and a guest's embed open another.
        $admin = $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'schedule']))->assertOk()->getContent();
        $this->assertStringContainsString(':target="monthPeek.go.edit ? null : \'_blank\'"', $admin);
        $this->assertStringContainsString('data-month-top class="sticky top-0', $admin, 'the card is kept under the portal\'s own bar');
        auth()->logout();
        $guest = $this->get('/'.$this->role->subdomain.'?layout=calendar')->assertOk()->getContent();
        $this->assertStringNotContainsString('monthPeek.go.edit ? null', $guest, 'a guest\'s page opens an event in its own tab');

        // A viewer cannot mark a day (the script is not given to one), so no day says it is a button.
        $viewer = \App\Models\User::factory()->create(['email_verified_at' => now()]);
        $this->role->users()->attach($viewer->id, ['level' => 'viewer']);
        $asViewer = $this->actingAs($viewer)->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'availability']))->assertOk()->getContent();
        $this->assertSame(1, substr_count($asViewer, 'data-availability-grid'));
        $this->assertSame(0, substr_count($asViewer, 'role="button" tabindex="0" aria-pressed'), 'a viewer\'s days are not buttons that do nothing');
        $this->assertSame(0, substr_count($asViewer, 'day-element" data-date='));
    }

    /**
     * A page that is handed its events with the page (?graphic=1) reads the month before the
     * mixin's created() has run. The month's clock is asked through monthNow(), which must
     * answer without what created() makes.
     */
    public function test_the_months_clock_answers_before_the_month_is_set_up(): void
    {
        $script = file_get_contents(resource_path('views/role/partials/month-script.blade.php'));

        $this->assertSame(1, preg_match('/monthNow\(zone\) \{.*?const m = this\.monthHands, stamp = [^;]+;\s+if \(!m\) return this\.scheduleNow\(zone\);/s', $script));
        // The day an hour belongs to is the day it is said with: a series that ends on the
        // stroke of midnight ended "Fri 12:00 AM" on the Friday it began.
        $this->assertStringContainsString('day(f.end ? f.endDay : f.last)', $script);
        // The calendar menu's hold is the menu's: another event's card does not inherit it.
        $this->assertStringContainsString('m.pinned = !!opts.pin; m.held = false;', $script);
    }
}
