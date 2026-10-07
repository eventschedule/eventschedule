<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Utils\MoneyUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The schedule page's list: cards from a tablet up, rows on a phone, never both in the page,
 * and what either says about tickets.
 *
 * Every event used to be drawn twice, a compact card for a phone and a large one for a laptop
 * (performers, agenda preview, polls), one of them hidden: a busy schedule's page was 62,000
 * elements. And neither said what anything cost or that it had sold out.
 *
 * For a short while the rows were the list at every width. On a wide screen they read as a
 * table and lost what the cards have, so the cards are back there and say what the rows learnt
 * to: test_the_wide_list_is_cards_again_and_a_card_says_what_tickets_cost.
 */
class GuestListRowsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        $this->role = $this->createRole($this->createOwner());
    }

    private function event(array $attrs = []): Event
    {
        return $this->createEvent($this->role, $attrs + [
            'starts_at' => now()->addDays(7)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'tickets_enabled' => true,
            'ticket_currency_code' => 'USD',
            'creator_role_id' => $this->role->id,
        ]);
    }

    /** The row as the live page gets it: the JSON endpoint, the only builder that runs there. */
    private function row(Event $event): array
    {
        $when = now()->addDays(7);
        $events = $this->getJson('/'.$this->role->subdomain.'/api/calendar-events?year='.$when->year.'&month='.$when->month)->assertOk()->json('events');
        $row = collect($events)->firstWhere('name', $event->name);
        $this->assertNotNull($row, 'the event is in the month');

        return $row;
    }

    public function test_a_guest_page_holds_one_list_at_a_time_and_a_row_is_a_link(): void
    {
        $html = $this->get('/'.$this->role->subdomain)->assertOk()->getContent();

        // The cards from a tablet up, the rows on a phone: each is v-if on the width, so an
        // event is in the page once. Both used to be there, one hidden by CSS.
        $this->assertSame(1, substr_count($html, '<div v-if="currentView === \'list\' && !isLoadingEvents && !isNarrow"'));
        $this->assertSame(1, substr_count($html, '<div v-if="currentView === \'list\' && !isLoadingEvents && isNarrow"'));
        $this->assertStringNotContainsString('v-show="currentView === \'list\' && !isLoadingEvents"', $html);
        $this->assertStringContainsString('class="gk-days"', $html);
        $this->assertSame(2, substr_count($html, 'class="gk-row gk-row-press"'), 'one row, drawn by the phone\'s list and by the phone\'s month');

        $row = file_get_contents(resource_path('views/role/partials/guest-row.blade.php'));
        // The event's name is a real link inside a heading, as it is on a card: a reader can
        // move by heading, a crawler has an address to follow.
        $this->assertSame(1, preg_match('/<h3[^>]*>.*<a :href="getEventUrl\(event\)" :target="eventLinkTarget\(\)" @click="onEventLinkClick\(event, \$event\)" v-text="event\.name"><\/a>\s*<\/h3>/s', $row));
        // The day above it is a heading too.
        $this->assertSame(1, preg_match('/<h2 class="gk-dayhead-title" v-text="formatDateHeader\(group\.date\)"/', $html));
        // The picture's link repeats the name's, so it is out of the tab order and unannounced.
        $this->assertSame(1, preg_match('/data-reveal-media>\s*<a :href="getEventUrl\(event\)"[^>]*tabindex="-1" aria-hidden="true">\s*<img /s', $row));
        // The short description and the venue's own page are on the row, as they are on a card.
        $this->assertStringContainsString('v-text="event.short_description"', $row);
        $this->assertStringContainsString('<a v-if="event.venue_guest_url" :href="event.venue_guest_url"', $row);
    }

    /**
     * The cards a wide schedule page always had: the big title, the date tile, the place, the
     * performers and the fan buttons. They said "Free entry" for a sign-up and the price an
     * owner typed for an event sold elsewhere, and nothing about tickets sold here.
     */
    public function test_the_wide_list_is_cards_again_and_a_card_says_what_tickets_cost(): void
    {
        $html = $this->get('/'.$this->role->subdomain.'?layout=list')->assertOk()->getContent();

        // The cards, keyed as they always were, under a date set between two lines.
        $this->assertStringContainsString("'list-d-' + group.date", $html);
        $this->assertStringContainsString('v-list-reveal:d="event.uniqueKey"', $html);
        // Today and Tomorrow are said on that date, by the schedule's clock.
        $this->assertSame(1, preg_match('/<span v-if="dayWord\(group\.date\)" class="[^"]*" v-text="dayWord\(group\.date\)"><\/span>\s*<span class="font-semibold text-xl/', $html));

        // One line for our own tickets in each of the card's two shapes (with a flyer, without).
        $this->assertSame(2, substr_count($html, 'data-card-tickets'));
        $badge = file_get_contents(resource_path('views/role/partials/card-ticket-badge.blade.php'));
        $this->assertStringContainsString('<div v-if="cardHasTickets(event)"', $badge);
        $this->assertSame(1, preg_match('/<span v-if="rowSoldOut\(event\)">.*<span v-else-if="event\.ticket_free">.*<bdi v-else v-text="rowPrice\(event\)"><\/bdi>/s', $badge));
        $this->assertStringContainsString('<span v-if="!rowSoldOut(event) && rowLow(event)"', $badge);
        // Never beside the sign-up's own badge or behind a password, and nothing once it is over.
        $this->assertStringContainsString('if (event._isPast || event.is_password_protected || event.rsvp_enabled) return false;', $html);

        // The admin's list and the dashboard's are as they were: no ticket line, both lists
        // in the page with the CSS switch.
        $this->assertStringContainsString("@includeWhen(\$guestRows, 'role/partials/card-ticket-badge')", file_get_contents(resource_path('views/role/partials/calendar.blade.php')));

        // The next-event card stands aside for these cards: the first of them IS the next event.
        $this->assertStringContainsString('[data-lead-wrap][data-view="list"] { display: none; }', $html);
        $this->assertStringContainsString('leadWrap.dataset.view = view;', $html);
        $event = $this->event(['name' => 'Coming Up']);
        $this->assertSame(1, preg_match('/data-lead-wrap data-view="list"/', $this->get('/'.$this->role->subdomain.'?layout=list')->assertOk()->getContent()));
        $this->assertSame(1, preg_match('/data-lead-wrap data-view="calendar"/', $this->get('/'.$this->role->subdomain.'?layout=calendar')->assertOk()->getContent()));
    }

    /**
     * The six list animations are written against a shape (resources/css/list-reveal.css): the
     * card is a div under the revealed element, the title's heading sits in a mask, the
     * picture's motion goes on a link inside its column. A row that is one <a> with a bare
     * <img> matched none of it: Shine had nothing to sweep and Curtain no panels to draw.
     */
    public function test_a_row_has_the_shape_the_list_animations_are_written_for(): void
    {
        $row = file_get_contents(resource_path('views/role/partials/guest-row.blade.php'));
        $css = file_get_contents(resource_path('css/list-reveal.css'));

        // What the stylesheet selects, so this fails if either side moves.
        foreach (['[data-list-reveal] > div::after', '[data-reveal-media] > a', '[data-reveal-title] > :is(h2, h3)', '[data-reveal-media]::before', '[data-reveal-body] > :not('] as $selector) {
            $this->assertStringContainsString($selector, $css, 'fixture: the animations still select '.$selector);
        }

        $this->assertSame(1, preg_match('/<li [^>]*v-list-reveal:m="event\.uniqueKey"[^>]*>\s*<div class="gk-row gk-row-press"/s', $row), 'a div directly under the revealed element');
        $this->assertSame(1, preg_match('/data-reveal-title>\s*<h3 /s', $row), 'the heading directly inside the title mask');
        $this->assertSame(1, preg_match('/<div [^>]*data-reveal-media>\s*<a /s', $row), 'a link directly inside the picture column, which is an element that can carry ::before');
        $this->assertStringContainsString('class="gk-row-body" data-reveal-body', $row);
        $this->assertStringNotContainsString('<img class="gk-row-img" data-reveal-media', $row);

        // And the phone month's rows are inside an animated list too, as the list's are.
        $html = $this->get('/'.$this->role->subdomain.'?layout=calendar')->assertOk()->getContent();
        $start = strpos($html, 'data-phone-month');
        $this->assertSame(1, preg_match('/class="gk-days" :data-list-anim="activeListAnimation !== \'none\' \? activeListAnimation : null"/', substr($html, $start, 9000)));
    }

    public function test_the_next_event_is_drawn_by_the_server_above_the_list(): void
    {
        $url = '/'.$this->role->subdomain;
        $lead = function (string $html): ?string {
            $start = strpos($html, 'id="gp-next-event"');

            return $start === false ? null : substr($html, $start, strpos($html, '</a>', $start) - $start);
        };

        // Nothing coming: no card, and no empty box where one would be.
        $this->assertNull($lead($this->get($url)->assertOk()->getContent()));

        $later = $this->event(['name' => 'Later Show', 'starts_at' => now()->addDays(9)->setTime(12, 0)->format('Y-m-d H:i:s')]);
        $this->createTicket($later, ['price' => 30, 'quantity' => 50]);
        // Sooner, and behind a password: it is not what the page leads with.
        $this->event(['name' => 'Members Only', 'event_password' => 'secret', 'is_private' => true, 'starts_at' => now()->addDays(2)->setTime(12, 0)->format('Y-m-d H:i:s')]);
        $next = $this->event(['name' => 'Next Show', 'starts_at' => now()->addDays(3)->setTime(12, 0)->format('Y-m-d H:i:s')]);
        $this->createTicket($next, ['price' => 15, 'quantity' => 2])->updateSold(now()->addDays(3)->format('Y-m-d'), 2);

        $html = $this->get($url)->assertOk()->getContent();
        $card = $lead($html);
        $this->assertNotNull($card, 'the page says what is next before its script has fetched anything');
        $this->assertStringContainsString('href="'.e($next->fresh()->getGuestUrl($this->role->subdomain)).'"', $card);
        $this->assertStringContainsString('Next Show', $card);
        $this->assertStringNotContainsString('Members Only', $card);
        $this->assertStringContainsString(__('messages.sold_out'), $card, 'and what a row would say about its tickets');
        // It comes before the list's own app in the page, and never asks for the page's one
        // high-priority picture.
        $this->assertLessThan(strpos($html, 'id="gp-calendar"'), strpos($html, 'id="gp-next-event"'));
        $this->assertStringNotContainsString('fetchpriority', $card);

        // An embed is the list alone, and the picture ?graphic=1 renders has neither the card
        // nor the chips.
        $this->assertNull($lead($this->get($url.'?embed=true')->assertOk()->getContent()));
        $graphic = $this->get($url.'?graphic=1')->assertOk()->getContent();
        $this->assertNull($lead($graphic));
        $this->assertStringNotContainsString('class="gk-pills ', $graphic);

        // ?category[]=x used to be cast to a string: an error page.
        $this->get($url.'?category[]=3')->assertOk();
        // An address narrowed by something the card was not chosen for gets no card, rather
        // than the schedule's next event whatever room it is in.
        $this->assertNull($lead($this->get($url.'?custom_1=room+b')->assertOk()->getContent()));
        // Its link keeps the category the page was asked for, as a row's does.
        $next->update(['category_id' => 4]);
        $this->assertStringContainsString('category=4', (string) $lead($this->get($url.'?category=4')->assertOk()->getContent()));
    }

    /**
     * A series is dated by its next occurrence from today, and today's counts after it has
     * begun. The first row of a schedule with a morning class was that class, hours over,
     * leading the page ahead of tonight's show.
     */
    public function test_the_next_event_is_never_one_that_has_already_begun(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['timezone' => 'UTC']);
        $this->travelTo(now('UTC')->addDay()->setTime(18, 0));
        $repo = app(\App\Repos\EventRepo::class);

        $class = $this->createEvent($role, [
            'name' => 'Morning Class', 'creator_role_id' => $role->id,
            'starts_at' => now('UTC')->subDays(10)->setTime(9, 0)->format('Y-m-d H:i:s'),
            'days_of_week' => '1111111', 'recurring_frequency' => 'daily',
        ]);
        $today = now('UTC')->format('Y-m-d');
        $tomorrow = now('UTC')->addDay()->format('Y-m-d');

        // On its own it still leads, as TOMORROW's class: there is nothing else to say.
        $upcoming = $repo->upcomingForGuest($role->fresh(), null, 12);
        $this->assertSame($today, $upcoming->first()['date'], 'fixture: the list itself dates it today');
        $lead = $repo->leadOf($upcoming);
        $this->assertSame([$class->id, $tomorrow], [$lead['event']->id, $lead['date']]);

        // Tonight's show is what is next.
        \Illuminate\Support\Facades\Cache::flush();
        $show = $this->createEvent($role, [
            'name' => 'Tonight Show', 'creator_role_id' => $role->id,
            'starts_at' => now('UTC')->setTime(21, 0)->format('Y-m-d H:i:s'), 'duration' => 3,
        ]);
        $lead = $repo->leadOf($repo->upcomingForGuest($role->fresh(), null, 12));
        $this->assertSame([$show->id, $today], [$lead['event']->id, $lead['date']]);

        $html = $this->get('/'.$role->subdomain)->assertOk()->getContent();
        $start = strpos($html, 'id="gp-next-event"');
        $this->assertNotFalse($start);
        $card = substr($html, $start, strpos($html, '</a>', $start) - $start);
        $this->assertStringContainsString('Tonight Show', $card);
        $this->assertStringNotContainsString('Morning Class', $card);

        // Nothing but an event already under way (one over several days: a shorter one is
        // not listed once it has begun): it leads rather than nothing.
        \Illuminate\Support\Facades\Cache::flush();
        $class->delete();
        $show->update(['duration' => 48]);
        $this->travelTo(now('UTC')->setTime(21, 30));
        $lead = $repo->leadOf($repo->upcomingForGuest($role->fresh(), null, 12));
        $this->assertSame($show->id, $lead['event']->id ?? null);
    }

    /** The day on the card is written in the order the language writes it, as the list's are. */
    public function test_a_day_is_written_in_the_languages_own_order(): void
    {
        $day = \Carbon\Carbon::create(now()->year, 10, 9, 20, 0, 0, 'UTC');
        if (now()->gt($day)) {
            $day->addYear();
        }
        $this->travelTo($day->copy()->subDays(3));

        $english = \App\Utils\DateUtils::dayLabel($day, 'en');
        $this->assertStringContainsString('October 9', $english);
        $this->assertStringNotContainsString((string) $day->year, $english, 'this year is not said');

        if (class_exists(\IntlDatePatternGenerator::class)) {
            $this->assertStringContainsString('9 octobre', \App\Utils\DateUtils::dayLabel($day, 'fr'), 'not "octobre 9"');
            $this->assertStringContainsString('9. Oktober', \App\Utils\DateUtils::dayLabel($day, 'de'));
        }
        // Another year is said.
        $this->assertStringContainsString((string) ($day->year + 1), \App\Utils\DateUtils::dayLabel($day->copy()->addYear(), 'en'));
    }

    public function test_the_filter_most_visitors_want_is_a_row_of_chips_above_the_list(): void
    {
        $html = $this->get('/'.$this->role->subdomain)->assertOk()->getContent();

        $start = strpos($html, '<div v-if="quickChips.length > 1"');
        $this->assertNotFalse($start);
        $row = substr($html, $start, strpos($html, '</div>', $start) - $start);
        // Sub-schedules where the schedule has them, its categories otherwise: one press each.
        $this->assertStringContainsString('class="gk-pills ', $row);
        $this->assertStringContainsString('@click="pickQuickChip(chip.value, $event)"', $row);
        $this->assertStringContainsString(':aria-pressed="quickChipValue === chip.value', $row, 'which one is chosen is said, not only coloured');
        // A name is the owner's text and this is inside the list's Vue mount: drawn from data.
        $this->assertStringContainsString('<span v-text="chip.name"></span>', $row);
        $this->assertStringNotContainsString('{{ chip.name }}', $row);
        $this->assertStringContainsString("if (this.groups && this.groups.length > 1) return 'group';", $html);

        // The next event steps aside by WHAT is chosen: one sub-schedule to another keeps the
        // number of filters at one.
        $this->assertStringContainsString("(lead.closest('[data-lead-wrap]') || lead).hidden = key !== this.leadFilterKeyAtLoad;", $html);
        // "As it loaded" is read when the app starts, never on the first change: read then, a
        // page that loads already filtered took the filtered state for the unfiltered one and
        // hid the card exactly when nothing was chosen.
        $this->assertSame(1, preg_match('/created\(\) \{\s*this\.leadFilterKeyAtLoad = this\.leadFilterKey;\s*\}/', $html));
        $this->assertStringContainsString('data-lead-wrap', file_get_contents(resource_path('views/role/show-guest.blade.php')));

        // An embed is the list alone.
        $this->assertStringNotContainsString('class="gk-pills ', $this->get('/'.$this->role->subdomain.'?embed=true')->assertOk()->getContent());
    }

    public function test_a_phone_gets_a_month_it_can_pick_a_day_from(): void
    {
        $html = $this->get('/'.$this->role->subdomain.'?layout=calendar')->assertOk()->getContent();

        $start = strpos($html, 'data-phone-month');
        $this->assertNotFalse($start, 'a phone used to get the month as a plain list, with nothing to jump to a date by');
        $month = substr($html, $start, strpos($html, '@click="phoneShowPast = true"', $start) - $start);

        // Which month it is and the way to its neighbours, in the panel itself.
        $this->assertStringContainsString('<span v-text="phoneMonthTitle" aria-live="polite"></span>', $month);
        $this->assertSame(1, preg_match('/@click="navigateMonth\(-1\)" aria-label="'.preg_quote(e(__('messages.previous_month')), '/').'"/', $month));
        // A day is a button that says its date and how many events it has; a day with none
        // cannot be pressed; today is said to be today.
        $this->assertStringContainsString(':disabled="cell.count === 0"', $month);
        $this->assertStringContainsString(":aria-label=\"formatDateHeader(cell.date) + (cell.count ? ' (' + cell.count + ')' : '')\"", $month);
        $this->assertStringContainsString(":aria-current=\"cell.today ? 'date' : null\"", $month);
        $this->assertStringContainsString('@click="pickPhoneDay(cell.date)"', $month);
        // Picked, the month folds to a line that opens it again, and focus goes with it both
        // ways: the pressed day is removed with the month it was in.
        $this->assertStringContainsString('class="gk-month-fold" ref="phoneFold" @click="unfoldPhoneMonth"', $month);
        $this->assertStringContainsString('this.$refs.phoneFold.focus({ preventScroll: true });', $html);
        $this->assertStringContainsString("document.querySelector('[data-phone-month] [data-day=\"' + date + '\"]')", $html);

        // Its rows are the list's own row, and the old phone list (#mobileEventsList) is the
        // admin's and the dashboard's now. Only the view on screen is in the page (v-if).
        $this->assertSame(2, substr_count($html, 'class="gk-row gk-row-press"'), 'the list and the phone month draw the same row');
        $this->assertStringNotContainsString('id="mobileEventsList"', $html);
        $this->assertStringContainsString('<div v-if="isNarrow && currentView === \'calendar\' && !isLoadingEvents" class="gk-list" data-phone-month>', $html);

        // It reads the month as the laptop's grid does, from the server's map of it: the list
        // of UPCOMING occurrences it used to read has no day that is over, no earlier month,
        // and a series for six months only.
        $this->assertSame(1, preg_match('/phoneMonthByDay\(\) \{.{0,400}Object\.keys\(this\.eventsMap \|\| \{\}\)\.filter\(date => date\.startsWith\(prefix\)\)\.forEach\(date => \{\s*const rows = this\.getEventsForDate\(date\)/s', $html));
        $this->assertStringNotContainsString('this.allMobileOccurrences.forEach(event => {', $html);
        // So the filters count the month it draws, and say so when nothing in it matches.
        $this->assertStringContainsString("return this.currentView === 'calendar' && (!this.isNarrow || this.phoneMonth);", $html);
        $this->assertStringContainsString('phoneMonth: true,', $html);
        $this->assertSame(1, preg_match('/narrowingFilterCount > 0 && monthMatchCount === 0"\s*class="flex mb-4/', $html), 'the "nothing matches this month" notice is a phone\'s too');
        // A month with nothing on is not an empty schedule: the next one is a press away.
        $this->assertSame(1, preg_match('/gk-month-none[^>]*>\s*<span>'.preg_quote(e(__('messages.no_events')), '/').'<\/span>\s*<button type="button" class="gk-pill" @click="navigateMonth\(1\)">/', $html));
        // Back to another month inside the page leaves no folded day from the month that was left.
        $this->assertSame(1, preg_match('/this\.pageYear = year;\s*this\.phoneDay = \'\';\s*this\.phoneShowPast = false;/', $html));

        // An embed in a narrow frame is not a phone: every upcoming day, as it always was
        // there, with its way on and no month to page through.
        $embed = $this->get('/'.$this->role->subdomain.'?embed=true&layout=calendar')->assertOk()->getContent();
        $this->assertStringContainsString('phoneMonth: false,', $embed);
        $this->assertStringNotContainsString('class="gk-month-title"', $embed);
        $start = strpos($embed, 'data-phone-month');
        $this->assertNotFalse($start);
        $this->assertStringContainsString('data-list-more', substr($embed, $start, 12000));
    }

    public function test_the_list_is_put_back_as_it_was_left_when_a_visitor_comes_back(): void
    {
        $html = $this->get('/'.$this->role->subdomain)->assertOk()->getContent();

        // Noted as the page is put away, under this page's own address, for this tab only.
        $this->assertStringContainsString("window.addEventListener('pagehide', () => this.rememberList());", $html);
        $this->assertStringContainsString("return 'es_list_' + window.location.pathname + window.location.search;", $html);
        $this->assertStringContainsString('y: Math.round(window.scrollY), limit: this.listRowLimit, day: this.phoneDay, past: this.phoneShowPast, at: Date.now(),', $html);
        // Put back only on the browser's Back or Forward, never on a fresh visit or a reload.
        $this->assertStringContainsString("entry.type === 'back_forward' && left && Date.now() - left.at < 30 * 60 * 1000", $html);
        // And only once the rows are in: before that there is nothing to scroll to.
        $this->assertSame(1, preg_match('/isLoadingEvents\(loading\) \{\s*if \(loading \|\| !this\.listToRestore\) \{ return; \}/', $html));
        $this->assertStringContainsString('this.listRowLimit = Math.max(this.listRowLimit, left.limit || 0);', $html);

        // A venue, Free, Online and a search are not in the address: Back returns the list
        // without them, so a place measured in the narrowed list is not kept.
        $this->assertSame(1, preg_match('/if \(this\.selectedVenue \|\| this\.showFreeOnly \|\| this\.showOnlineOnly \|\| this\.isSearching\) \{\s*sessionStorage\.removeItem\(this\.listMemoryKey\(\)\);\s*return;/', $html));

        // The admin's list and the dashboard's are not the visitor's to come back to.
        $this->assertStringContainsString("if (this.route === 'guest') {\n            this.recallList();", $html);
    }

    public function test_today_and_tomorrow_are_the_schedules_not_the_visitors(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['timezone' => 'Asia/Tokyo']);
        $html = $this->get('/'.$role->subdomain)->assertOk()->getContent();

        // The clock the headings read is the one the rows are put into days by.
        $this->assertStringContainsString("userTimezone: 'Asia/Tokyo',", $html);
        $this->assertStringContainsString('dayWords: { today: '.json_encode(__('messages.today')).', tomorrow: '.json_encode(__('messages.tomorrow')).' }', $html);
        $this->assertSame(1, preg_match('/scheduleDay\(offset\) \{\s*const there = this\.userTimezone \? new Date\(new Date\(\)\.toLocaleString\(\'en-US\', \{ timeZone: this\.userTimezone \}\)\)/', $html));
        $this->assertStringContainsString('<span v-if="dayWord(group.date)" class="gk-dayhead-word" v-text="dayWord(group.date)"></span>', $html);
    }

    public function test_a_row_says_what_tickets_cost(): void
    {
        $priced = $this->event(['name' => 'Two Prices']);
        $this->createTicket($priced, ['price' => 10, 'quantity' => 50]);
        $this->createTicket($priced, ['price' => 25, 'quantity' => 50]);
        $row = $this->row($priced);
        $this->assertSame(__('messages.price_from', ['price' => MoneyUtils::format(10, 'USD')]), $row['ticket_from']);
        $this->assertFalse($row['ticket_free']);

        $one = $this->event(['name' => 'One Price']);
        $this->createTicket($one, ['price' => 20, 'quantity' => 50]);
        $this->assertSame(MoneyUtils::format(20, 'USD'), $this->row($one)['ticket_from']);

        $free = $this->event(['name' => 'No Charge']);
        $this->createTicket($free, ['price' => 0, 'quantity' => 50]);
        $row = $this->row($free);
        $this->assertTrue($row['ticket_free']);
        $this->assertNull($row['ticket_from']);

        // Nothing of ours to sell: nothing said.
        $plain = $this->event(['name' => 'Just A Date', 'tickets_enabled' => false]);
        $row = $this->row($plain);
        $this->assertFalse($row['ticket_free']);
        $this->assertNull($row['ticket_from']);
    }

    public function test_a_row_says_sold_out_and_few_left_by_day_and_never_how_many(): void
    {
        $date = now()->addDays(7)->format('Y-m-d');

        $gone = $this->event(['name' => 'All Gone']);
        $ticket = $this->createTicket($gone, ['price' => 10, 'quantity' => 2]);
        $ticket->updateSold($date, 2);
        $row = $this->row($gone);
        $this->assertSame([$date], $row['sold_out_dates']);
        $this->assertSame([], $row['low_stock_dates']);

        $nearly = $this->event(['name' => 'Nearly Gone']);
        $ticket = $this->createTicket($nearly, ['price' => 10, 'quantity' => 20]);
        $ticket->updateSold($date, 18);
        $row = $this->row($nearly);
        $this->assertSame([], $row['sold_out_dates']);
        $this->assertSame([$date], $row['low_stock_dates']);
        foreach (['remaining', 'seats_left', 'available', 'quantity'] as $count) {
            $this->assertArrayNotHasKey($count, $row, 'a row never carries a number of seats');
        }

        $plenty = $this->event(['name' => 'Plenty Left']);
        $this->createTicket($plenty, ['price' => 10, 'quantity' => 20])->updateSold($date, 3);
        $row = $this->row($plenty);
        $this->assertSame([[], []], [$row['sold_out_dates'], $row['low_stock_dates']]);

        // No ceiling, so nothing is running out.
        $open = $this->event(['name' => 'No Ceiling']);
        $this->createTicket($open, ['price' => 10, 'quantity' => 0])->updateSold($date, 500);
        $this->assertSame([], $this->row($open)['sold_out_dates']);
    }

    public function test_a_locked_event_and_one_with_a_pass_say_nothing(): void
    {
        $date = now()->addDays(7)->format('Y-m-d');

        $locked = $this->event(['name' => 'Members Night', 'event_password' => 'secret']);
        $this->createTicket($locked, ['price' => 10, 'quantity' => 1])->updateSold($date, 1);
        $this->assertSame(Event::NO_CARD_TICKET_FIELDS, $locked->fresh()->cardTicketFields());

        // A pass draws on its own pool and can hold seats on other dates: more than the rows
        // already loaded can answer, so the row leaves it to the event's page.
        $season = $this->event(['name' => 'Season Opener']);
        $this->createTicket($season, ['price' => 10, 'quantity' => 1])->updateSold($date, 1);
        $this->createTicket($season, ['price' => 90, 'quantity' => 10, 'is_pass' => true]);
        $this->assertSame([], $season->fresh()->cardTicketFields()['sold_out_dates']);
        $this->assertNull($season->fresh()->cardTicketFields()['ticket_from']);
    }

    /**
     * Where a row agreed with nobody: "From $10" for an early-bird type that had sold out, a
     * price beside a show whose sales had closed when it began, and no "Sold out" where the
     * only type on sale was gone while a type not yet on sale still had stock.
     */
    public function test_a_row_says_only_what_the_events_own_page_would(): void
    {
        $date = now()->addDays(7)->format('Y-m-d');

        $early = $this->event(['name' => 'Early Bird Gone']);
        $this->createTicket($early, ['price' => 10, 'quantity' => 5])->updateSold($date, 5);
        $this->createTicket($early, ['price' => 25, 'quantity' => 50]);
        $row = $this->row($early);
        $this->assertSame(MoneyUtils::format(25, 'USD'), $row['ticket_from'], 'the price somebody can still pay');
        $this->assertSame([], $row['sold_out_dates']);

        $staged = $this->event(['name' => 'Second Release Later']);
        $this->createTicket($staged, ['price' => 10, 'quantity' => 5])->updateSold($date, 5);
        $this->createTicket($staged, ['price' => 20, 'quantity' => 50, 'sales_start_at' => now()->addDays(3)->format('Y-m-d H:i:s')]);
        $this->assertFalse($staged->fresh()->tickets->every(fn ($ticket) => ! $ticket->isSalesNotStarted()), 'fixture: one type is not on sale yet');
        $this->assertSame([$date], $this->row($staged)['sold_out_dates'], 'what is on sale is gone, whatever is not on sale yet holds');

        // Sales close when it starts: once it has, the row says nothing of a price.
        $begun = $this->event(['name' => 'Already Started', 'starts_at' => now()->subHour()->format('Y-m-d H:i:s'), 'duration' => 3]);
        $this->createTicket($begun, ['price' => 20, 'quantity' => 50]);
        $this->assertSame(Event::NO_CARD_TICKET_FIELDS, $begun->fresh()->cardTicketFields());
        // Unless it sells after it starts, which the row is told so a series can be judged by day.
        $begun->update(['sell_after_start' => true]);
        $facts = $begun->fresh()->cardTicketFields();
        $this->assertSame([MoneyUtils::format(20, 'USD'), true], [$facts['ticket_from'], $facts['sells_after_start']]);

        // The row's own script: the day tickets are sold under is the event's, not the day a
        // running event is listed on, and a night that has begun says nothing.
        $html = $this->get('/'.$this->role->subdomain)->assertOk()->getContent();
        $this->assertSame(1, preg_match('/rowDate\(event\) \{\s*if \(event\.days_of_week && event\.days_of_week\.length\) \{\s*return event\._originalOccurrenceDate \|\| event\.occurrenceDate \|\| null;\s*\}\s*return event\.local_date \|\| null;/', $html));
        $this->assertStringContainsString("if (!event.sells_after_start && !event.is_multi_day) { return now >= date + ' ' + time; }", $html);
        $this->assertStringContainsString('return event.ticket_from && !this.rowSalesOver(event) ? event.ticket_from : null;', $html);
    }

    /**
     * What a row or a card says about tickets is worked out from what the list already loaded:
     * a longer list asks the database nothing more.
     */
    public function test_what_a_list_says_about_tickets_costs_no_query_an_event(): void
    {
        $when = now()->addDays(7);
        $url = '/'.$this->role->subdomain.'/api/calendar-events?year='.$when->year.'&month='.$when->month;
        $date = $when->format('Y-m-d');
        $add = function (int $n) use ($date) {
            $event = $this->event(['name' => 'Night '.$n]);
            $this->createTicket($event, ['price' => 10 * $n, 'quantity' => 10])->updateSold($date, 9);
        };
        $count = function () use ($url) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $rows = $this->getJson($url)->assertOk()->json('events');
            $queries = count(DB::getQueryLog());
            DB::disableQueryLog();

            return [$queries, $rows];
        };

        $add(1);
        $add(2);
        $this->getJson($url)->assertOk();
        [$two, $rows] = $count();

        foreach ($rows as $row) {
            $this->assertNotNull($row['ticket_from']);
            $this->assertSame([$date], $row['low_stock_dates']);
        }

        foreach (range(3, 8) as $n) {
            $add($n);
        }
        [$eight, $rows] = $count();
        $this->assertCount(8, $rows);
        $this->assertSame($two, $eight, 'eight events ask what two did');
    }
}
