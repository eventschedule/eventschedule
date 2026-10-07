<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The schedule's other upcoming events, down the left column of the guest event page.
 *
 * It used to be a second copy of the calendar app there: a Vue mount that fetched the schedule's
 * events for the viewed event's month, sliced them to twenty cards, and had a footer link whose
 * appearance was decided half by the server and half in the browser. It is drawn by the server
 * now, from the schedule's next public events, and the link to the whole schedule is simply
 * there. (For a few days it was three rows across the foot of the page; the column is where it
 * belongs, and it carries on down it as it did.) It looks as the schedule's own list does on a
 * phone: a panel for each day, a row for each event, and what a row there says about tickets.
 *
 * The test that matters for privacy is still here: a draft, cancelled, unlisted or
 * password-protected event must never be one of the rows.
 */
class GuestEventSidebarLinkTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-03-11 12:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    private function at(int $days): string
    {
        return Carbon::now()->addDays($days)->setTime(12, 0)->format('Y-m-d H:i:s');
    }

    /** The section's own markup, so an assertion cannot be satisfied by the rest of the page. */
    private function more(string $html): string
    {
        $start = strpos($html, 'id="gp-upcoming-events"');
        $this->assertNotFalse($start, 'the page has its "more events" section');

        return substr($html, $start, strpos($html, '</section>', $start) - $start);
    }

    public function test_the_other_events_carry_on_down_the_left_column_as_links(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, ['name' => 'Tonight', 'starts_at' => $this->at(1)]);
        $others = [];
        foreach (range(1, 23) as $n) {
            // Two a day, so a day has a heading over more than one card.
            $others[$n] = $this->createEvent($role, ['name' => 'Night Number '.$n.'.', 'starts_at' => $this->at(2 + intdiv($n - 1, 2))]);
        }

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();
        $more = $this->more($html);

        $this->assertSame(20, substr_count($more, '<a class="gk-row gk-row-stack '), 'twenty, as the column always held');
        foreach ([1, 2, 20] as $n) {
            $this->assertStringContainsString('Night Number '.$n.'.', $more);
            $this->assertStringContainsString('href="'.e($others[$n]->fresh()->getGuestUrl($role->subdomain)).'"', $more, 'a real link, not a click handler');
        }
        $this->assertStringNotContainsString('Night Number 21.', $more);
        $this->assertStringNotContainsString('Tonight', $more, 'the event the visitor is already on');
        // A panel for each day, its date said once as a heading over the events of that day:
        // the schedule's own phone list (.gk-day, .gk-row), not a second kind of card.
        $this->assertSame(10, substr_count($more, 'data-up-day="'));
        $this->assertSame(10, substr_count($more, '<h3 class="gk-dayhead-title">'));
        $this->assertStringContainsString(\App\Utils\DateUtils::dayLabel(Carbon::now()->addDays(2)), $more);
        $this->assertStringNotContainsString('gk-up-card', $more);
        // A phone and a tablet get the first five and the way to the rest: three days (two
        // events each, the third day's second event is the sixth), then nothing.
        $this->assertSame(15, substr_count($more, '<li class="gk-row-item gk-up-late">'));
        $this->assertSame(7, preg_match_all('/class="gk-panel gk-panel-flush gk-day [^"]*gk-up-late"/', $more), 'a day whose events are all past the fifth is put away whole');

        // In the LEFT column, after the flyer, the performers and the venue, and not at the
        // foot of the page.
        $side = strpos($html, 'class="gk-event-col gk-event-side"');
        $main = strpos($html, 'class="gk-event-col gk-event-main"');
        $here = strpos($html, 'id="gp-upcoming-events"');
        $this->assertTrue($side < $here && $here < $main, 'inside the left column');
        // The foot holds only the free tier's "create your own", and is not drawn without it:
        // empty, it was a blank block at the end of every paid schedule's event page.
        $this->assertFalse($role->fresh()->showBranding(), 'fixture: a paid schedule');
        $this->assertStringNotContainsString('class="gk-event-foot"', $html);

        $free = $this->createFreeRole(null, 'venue');
        $freeEvent = $this->createEvent($free, ['name' => 'Free Tonight', 'starts_at' => $this->at(1)]);
        $this->createEvent($free, ['name' => 'Free Tomorrow', 'starts_at' => $this->at(2)]);
        $html = $this->get($this->guestEventUrl($free, $freeEvent))->assertOk()->getContent();
        $foot = strpos($html, 'class="gk-event-foot"');
        $this->assertNotFalse($foot);
        $this->assertStringContainsString('id="gp-create-your-own"', substr($html, $foot, 600));
        $this->assertLessThan($foot, strpos($html, 'id="gp-upcoming-events"'), 'the other events are in the column above, not in the foot');

        // Wherever the page is one column (below 64rem) the list is at its end and stops after
        // five: cut below 48rem only, a tablet got all twenty there.
        $kit = file_get_contents(resource_path('views/partials/guest-kit-styles.blade.php'));
        $this->assertStringContainsString('@media (max-width: 63.99rem) { .gk-up-late { display: none; } }', $kit);
        $this->assertStringContainsString('@media (min-width: 64rem) {', $kit, 'fixture: where the two columns start');
    }

    /**
     * A row says what the schedule's own list says about tickets (partials/guest-ticket-chips):
     * the price, Free entry, Sold out, Few left. From ONE query for the whole list's tickets:
     * read lazily it was a query a row.
     */
    public function test_a_row_says_what_it_costs_from_one_query_for_the_list(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, ['name' => 'Tonight', 'starts_at' => $this->at(1), 'creator_role_id' => $role->id]);
        $night = fn (string $name, int $days, array $attrs = []) => $this->createEvent($role, $attrs + [
            'name' => $name, 'starts_at' => $this->at($days), 'creator_role_id' => $role->id,
            'tickets_enabled' => true, 'ticket_currency_code' => 'USD',
        ]);
        $day = fn (int $days) => Carbon::now()->addDays($days)->format('Y-m-d');

        $this->createTicket($night('Priced Night', 2), ['price' => 20, 'quantity' => 50]);
        $this->createTicket($night('Gone Night', 3), ['price' => 20, 'quantity' => 2])->updateSold($day(3), 2);
        $this->createTicket($night('Free Night', 4), ['price' => 0, 'quantity' => 50]);
        $this->createTicket($night('Nearly Gone Night', 5), ['price' => 12, 'quantity' => 20])->updateSold($day(5), 18);
        $night('Plain Night', 6, ['tickets_enabled' => false]);
        // Two daily series, dated today by the list (it is noon): the morning one has begun,
        // so its tickets are no longer on sale; the evening one has not.
        $role->update(['timezone' => 'UTC']);
        $series = fn (string $name, int $hour) => $night($name, 0, [
            'starts_at' => Carbon::now()->subDays(5)->setTime($hour, 0)->format('Y-m-d H:i:s'),
            'days_of_week' => '1111111', 'recurring_frequency' => 'daily',
        ]);
        $this->createTicket($series('Morning Class', 9), ['price' => 7, 'quantity' => 0]);
        $this->createTicket($series('Evening Class', 18), ['price' => 9, 'quantity' => 0]);

        $ticketQueries = [];
        \Illuminate\Support\Facades\DB::listen(function ($query) use (&$ticketQueries) {
            if (preg_match('/\bfrom [`"]?tickets[`"]?/i', $query->sql)) {
                $ticketQueries[] = $query->sql;
            }
        });
        $more = $this->more($this->get($this->guestEventUrl($role, $event))->assertOk()->getContent());

        // The page's own event loads its tickets too; the LIST's are one more query, whatever its length.
        $forTheList = array_filter($ticketQueries, fn ($sql) => preg_match('/event_id[`"]? in \([^)]*,/i', $sql) === 1);
        $this->assertCount(1, $forTheList, implode(' | ', $ticketQueries));
        $this->assertLessThanOrEqual(3, count($ticketQueries), 'and not one a row: '.implode(' | ', $ticketQueries));

        $row = function (string $name) use ($more) {
            foreach (explode('<li class="gk-row-item', $more) as $item) {
                if (str_contains($item, $name)) {
                    return $item;
                }
            }
            $this->fail($name.' is not in the list');
        };
        $this->assertStringContainsString(\App\Utils\MoneyUtils::format(20, 'USD'), $row('Priced Night'));
        $this->assertStringContainsString('gk-chip-out', $row('Gone Night'));
        $this->assertStringContainsString(__('messages.sold_out'), $row('Gone Night'));
        $this->assertStringContainsString('gk-chip-free', $row('Free Night'));
        $this->assertStringContainsString('gk-chip-few', $row('Nearly Gone Night'));
        $this->assertStringContainsString(\App\Utils\MoneyUtils::format(12, 'USD'), $row('Nearly Gone Night'));
        $this->assertStringNotContainsString('gk-chip', $row('Plain Night'), 'nothing to sell, nothing said');
        // A series is judged by the occurrence: this morning's has begun and is not selling.
        $this->assertStringContainsString(\App\Utils\MoneyUtils::format(9, 'USD'), $row('Evening Class'));
        $this->assertStringNotContainsString('gk-chip', $row('Morning Class'), 'begun at nine, and it is noon');
    }

    /**
     * On a curator's page each row can belong to a different schedule, and what a paid ticket
     * may be sold for is that schedule's plan: its subscription, and its owner for the demo
     * check. Asked a row at a time that was up to two queries for every schedule listed; they
     * are loaded for the whole list at once, so six schedules ask what two did.
     */
    public function test_a_curators_list_does_not_ask_each_schedule_for_its_plan(): void
    {
        config(['app.hosted' => true]);
        $curator = $this->createRole($this->createOwner(), 'curator', ['name' => 'City Guide']);
        $add = function (int $n) use ($curator) {
            $venue = $this->createRole($this->createOwner(), 'venue', ['name' => 'Room '.$n]);
            $event = $this->createEvent($venue, [
                'name' => 'Show '.$n, 'starts_at' => $this->at(1 + $n), 'creator_role_id' => $venue->id,
                'tickets_enabled' => true, 'ticket_currency_code' => 'USD',
            ]);
            $this->createTicket($event, ['price' => 10 + $n, 'quantity' => 50]);
            $event->roles()->attach($curator->id, ['is_accepted' => true]);

            return $event;
        };
        $count = function (\App\Models\Event $on) use ($curator) {
            \Illuminate\Support\Facades\Cache::flush();
            \Illuminate\Support\Facades\DB::flushQueryLog();
            \Illuminate\Support\Facades\DB::enableQueryLog();
            $html = $this->get($on->fresh()->getGuestUrl($curator->subdomain))->assertOk()->getContent();
            $queries = count(\Illuminate\Support\Facades\DB::getQueryLog());
            \Illuminate\Support\Facades\DB::disableQueryLog();

            return [$queries, $this->more($html)];
        };

        $first = $add(1);
        $add(2);
        $add(3);
        $count($first);
        [$two, $more] = $count($first);
        $this->assertSame(2, substr_count($more, 'class="gk-chip"'), 'fixture: each row says its price, so each plan was asked');

        foreach (range(4, 7) as $n) {
            $add($n);
        }
        [$six, $more] = $count($first);
        $this->assertSame(6, substr_count($more, 'class="gk-chip"'));
        $this->assertSame($two, $six, 'six schedules ask what two did');
    }

    public function test_the_way_to_the_whole_schedule_is_always_there_in_the_owners_words(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', [
            'custom_labels' => ['view_full_schedule' => ['value' => 'See the whole season'], 'events' => ['value' => 'Coming up']],
        ]);
        $event = $this->createEvent($role, ['name' => 'Tonight', 'starts_at' => $this->at(1)]);
        $this->createEvent($role, ['name' => 'Next Week', 'starts_at' => $this->at(8)]);

        $more = $this->more($this->get($this->guestEventUrl($role, $event))->assertOk()->getContent());

        $this->assertStringContainsString('See the whole season', $more);
        $this->assertStringContainsString('Coming up', $more);
        $this->assertStringContainsString('href="'.e(route('role.view_guest', ['subdomain' => $role->subdomain])).'"', $more);
    }

    public function test_events_a_visitor_may_not_see_are_never_listed(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, ['name' => 'Tonight', 'starts_at' => $this->at(1)]);
        $this->createEvent($role, ['name' => 'Draft Night', 'starts_at' => $this->at(2), 'is_draft' => true]);
        $this->createEvent($role, ['name' => 'Cancelled Night', 'starts_at' => $this->at(3), 'is_cancelled' => true]);
        $this->createEvent($role, ['name' => 'Unlisted Night', 'starts_at' => $this->at(4), 'is_private' => true]);
        $this->createEvent($role, ['name' => 'Locked Night', 'starts_at' => $this->at(5), 'event_password' => 'hunter2']);
        $this->createEvent($role, ['name' => 'Yesterday Night', 'starts_at' => $this->at(-2)]);
        $this->createEvent($role, ['name' => 'Open Night', 'starts_at' => $this->at(6)]);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();
        $more = $this->more($html);

        $this->assertSame(1, substr_count($more, '<a class="gk-row gk-row-stack '));
        $this->assertStringContainsString('Open Night', $more);
        foreach (['Draft', 'Cancelled', 'Unlisted', 'Locked', 'Yesterday'] as $hidden) {
            $this->assertStringNotContainsString($hidden.' Night', $html, $hidden.' is nowhere on the page');
        }
    }

    public function test_the_list_stays_inside_the_category_the_visitor_is_browsing(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, ['name' => 'Tonight', 'starts_at' => $this->at(1), 'category_id' => 3, 'creator_role_id' => $role->id]);
        // Thirteen of another category sooner than the one of this visitor's: it used to be
        // looked for among the first twelve only.
        foreach (range(1, 13) as $n) {
            $this->createEvent($role, ['name' => 'Talk '.$n, 'starts_at' => $this->at(1 + $n), 'category_id' => 5, 'creator_role_id' => $role->id]);
        }
        $this->createEvent($role, ['name' => 'Late Concert', 'starts_at' => $this->at(30), 'category_id' => 3, 'creator_role_id' => $role->id]);
        $url = $event->fresh()->getGuestUrl($role->subdomain);

        $more = $this->more($this->get($url.'?category=3')->assertOk()->getContent());
        $this->assertStringContainsString('Late Concert', $more);
        $this->assertStringNotContainsString('Talk ', $more);
        $this->assertSame(1, preg_match('/<a class="gk-row gk-row-stack [^"]*" href="[^"]*category=3"[^>]*>(?:(?!<\/a>).)*Late Concert/s', $more), 'and the row carries it on');

        // ?category[]=x is an array, and casting one was an error page.
        $this->get($url.'?category[]=3')->assertOk();
    }

    public function test_an_event_with_nothing_after_it_has_no_empty_section(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, ['name' => 'The Only One', 'starts_at' => $this->at(1)]);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        $this->assertStringNotContainsString('id="gp-upcoming-events"', $html);
    }

    public function test_the_event_page_no_longer_carries_a_second_calendar_app(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $event = $this->createEvent($role, ['name' => 'Tonight', 'starts_at' => $this->at(1)]);
        $this->createEvent($role, ['name' => 'Next Week', 'starts_at' => $this->at(8)]);

        $html = $this->get($this->guestEventUrl($role, $event))->assertOk()->getContent();

        // The app, its footer link, and the request it made for up to 400 events.
        $this->assertStringNotContainsString('id="calendar-app"', $html);
        $this->assertStringNotContainsString('viewFullScheduleFooter', $html);
        $this->assertStringNotContainsString('api/calendar-events', $html);
    }

    public function test_a_weekly_event_does_not_list_itself_as_more(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $weekly = $this->createEvent($role, ['name' => 'Every Thursday', 'starts_at' => $this->at(1), 'days_of_week' => '1111111']);
        $this->createEvent($role, ['name' => 'One Off', 'starts_at' => $this->at(9)]);

        $more = $this->more($this->get($this->guestEventUrl($role, $weekly))->assertOk()->getContent());

        $this->assertStringNotContainsString('Every Thursday', $more);
        $this->assertStringContainsString('One Off', $more);
    }
}
