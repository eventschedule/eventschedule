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

    /**
     * The Availability tab keeps the grid a team member marks their days on; it is not the month.
     * On the Schedule tab the days people are away are told by name: as text, by the name as it
     * was written, and one entry a member. They were printed as markup in a tooltip, and keyed
     * by the escaped name, which folds two members who share a name into one.
     */
    public function test_who_is_away_is_told_as_text_and_the_availability_grid_stays(): void
    {
        $owner = $this->role->users()->first();
        $day = now()->addDays(3)->format('Y-m-d');
        foreach (['Dana <b>Bold</b>', 'Sam Rivers', 'Sam Rivers'] as $name) {
            $member = \App\Models\User::factory()->create(['name' => $name, 'email_verified_at' => now()]);
            $this->role->users()->attach($member->id, ['level' => 'admin', 'dates_unavailable' => json_encode([$day])]);
        }

        $schedule = $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'schedule']))->assertOk()->getContent();
        $this->assertSame(1, preg_match('/const AWAY = (\[.*?\]);\n/', $schedule, $found));
        $away = json_decode($found[1], true);
        $this->assertSame(['Dana <b>Bold</b>', 'Sam Rivers', 'Sam Rivers'], array_column($away, 'name'), 'by name as written, one entry a member');
        $this->assertSame([[$day], [$day], [$day]], array_column($away, 'dates'));
        $this->assertStringNotContainsString('<b>Bold</b>', $schedule, 'no member\'s name is in the page as markup');
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
        $this->assertStringContainsString('grid-cols-7 grid-rows-', $availability, 'its own grid of days is there');
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
}
