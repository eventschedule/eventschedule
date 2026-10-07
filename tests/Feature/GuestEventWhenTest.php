<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The When block of an event page.
 *
 * It said a weekday and two times. Not the year (an event next October read as this October's),
 * not the zone the hours are in, and nothing about a series: the page of one Thursday of a weekly
 * night offered no way to any other Thursday. And a show that ran past midnight was drawn as a
 * two-day event, "16-17" in the tile with a date range under it.
 */
class GuestEventWhenTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        // A fixed evening, so "this year", "tomorrow" and the weekdays below are known.
        $this->travelTo(Carbon::parse('2026-10-07 15:00:00', 'UTC'));
        $this->role = $this->createRole($this->createOwner(), 'venue', ['timezone' => 'America/New_York']);
    }

    /** starts_at is stored in UTC: 9 PM in New York in October is 01:00 the next day. */
    private function at(string $localDay, string $localTime = '19:00'): string
    {
        return Carbon::parse($localDay.' '.$localTime, 'America/New_York')->utc()->format('Y-m-d H:i:s');
    }

    private function when(Event $event, ?string $date = null): string
    {
        $html = $this->get($event->fresh()->getGuestUrl($this->role->subdomain, $date))->assertOk()->getContent();
        $start = strpos($html, 'id="gp-event-date"');
        $this->assertNotFalse($start);

        return substr($html, $start, strpos($html, 'id="gp-event-', $start + 20) - $start);
    }

    private function text(string $html): string
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags($html)));
    }

    public function test_the_hours_say_which_zone_they_are_in_and_next_year_says_its_year(): void
    {
        $soon = $this->createEvent($this->role, ['starts_at' => $this->at('2026-10-16'), 'duration' => 3, 'creator_role_id' => $this->role->id]);
        $when = $this->when($soon);

        $this->assertStringContainsString('<bdi dir="ltr" data-event-zone>EDT</bdi>', $when, 'the schedule\'s zone, not the server\'s and not the viewer\'s');
        $this->assertStringContainsString('7:00 PM - 10:00 PM', $when);
        $this->assertStringNotContainsString('2026', $this->text($when), 'this year goes without saying');

        $nextYear = $this->createEvent($this->role, ['starts_at' => $this->at('2027-01-15'), 'duration' => 3, 'creator_role_id' => $this->role->id]);
        $when = $this->when($nextYear);
        $this->assertStringContainsString('Friday, January 15, 2027', $when);
        $this->assertStringContainsString('data-event-zone>EST</bdi>', $when, 'and the zone is the one in force that day');
    }

    public function test_a_show_that_runs_past_midnight_is_one_night(): void
    {
        $late = $this->createEvent($this->role, ['starts_at' => $this->at('2026-10-16', '22:00'), 'duration' => 4, 'creator_role_id' => $this->role->id]);
        $this->assertTrue($late->fresh()->isMultiDay(), 'fixture: it does cross midnight');

        $when = $this->when($late);
        $text = $this->text($when);

        $this->assertStringContainsString('10:00 PM - 2:00 AM', $text);
        $this->assertMatchesRegularExpression('/\bOct\s+16\s+Friday\b/', $text, 'the tile is one day and the line is its weekday');
        $this->assertStringNotContainsString('16-17', $text);
        $this->assertStringNotContainsString('October 16 - 17', $text);
        $this->assertStringNotContainsString('data-event-ends', $when, 'two in the morning needs no day beside it');

        // Into the next morning proper, the end says which day it is.
        $long = $this->createEvent($this->role, ['starts_at' => $this->at('2026-10-16', '20:00'), 'duration' => 14, 'creator_role_id' => $this->role->id]);
        $this->assertStringContainsString('<span data-event-ends>(Sat)</span>', $this->when($long));

        // A day or longer is still a range.
        $festival = $this->createEvent($this->role, ['starts_at' => $this->at('2026-10-16', '12:00'), 'duration' => 48, 'creator_role_id' => $this->role->id]);
        $this->assertStringContainsString('16-18', $this->text($this->when($festival)));
    }

    public function test_a_weekly_night_says_so_and_links_its_next_dates(): void
    {
        // Thursdays at 8, from 1 October. days_of_week is Sunday first.
        $weekly = $this->createEvent($this->role, [
            'starts_at' => $this->at('2026-10-01', '20:00'), 'duration' => 2,
            'days_of_week' => '0000100', 'recurring_frequency' => 'weekly', 'creator_role_id' => $this->role->id,
        ]);

        $when = $this->when($weekly, '2026-10-15');
        $this->assertSame('Weekly · Thursday', $this->text(preg_match('/<span[^>]*data-event-repeats>(.*?)<\/span>/s', $when, $m) ? $m[1] : ''));

        preg_match_all('/<a href="([^"]+)" class="gk-link">([^<]+)<\/a>/', $when, $links);
        $this->assertSame(['Thu, Oct 22', 'Thu, Oct 29', 'Thu, Nov 5'], $links[2], 'the next three, after the one being shown');
        $this->assertSame($weekly->fresh()->getGuestUrl($this->role->subdomain, '2026-10-22'), html_entity_decode($links[1][0]));
        $this->assertStringContainsString(__('messages.more_dates'), $when);

        // The page of a Thursday long gone offers what is still to come, never a day that is over.
        // (Asked from before the series began: unclamped, the first answer would be 1 October, six days ago.)
        $this->assertSame(['2026-10-08', '2026-10-15', '2026-10-22'], $weekly->fresh()->occurrencesAfter('2026-09-20'));

        // An event that happens once says nothing of the kind.
        $once = $this->createEvent($this->role, ['starts_at' => $this->at('2026-10-16'), 'creator_role_id' => $this->role->id]);
        $when = $this->when($once);
        $this->assertStringNotContainsString('data-event-repeats', $when);
        $this->assertStringNotContainsString('data-event-more-dates', $when);
    }

    public function test_a_next_date_with_nothing_left_says_so(): void
    {
        $weekly = $this->createEvent($this->role, [
            'starts_at' => $this->at('2026-10-01', '20:00'), 'duration' => 2, 'tickets_enabled' => true,
            'days_of_week' => '0000100', 'recurring_frequency' => 'weekly', 'creator_role_id' => $this->role->id,
        ]);
        $ticket = $this->createTicket($weekly, ['price' => 10, 'quantity' => 2]);
        $this->createSale($weekly, $this->role, ['status' => 'paid', 'event_date' => '2026-10-22'], $ticket, 2);
        $this->assertSame('sold_out', $weekly->fresh()->ticketSaleState('2026-10-22'), 'fixture');
        $this->assertSame('open', $weekly->fresh()->ticketSaleState('2026-10-29'), 'fixture');

        $text = $this->text($this->when($weekly, '2026-10-15'));

        $this->assertStringContainsString('Thu, Oct 22 ('.__('messages.sold_out').')', $text);
        $this->assertStringContainsString('Thu, Oct 29 Thu, Nov 5', $text, 'and only that one');
    }

    public function test_how_an_event_repeats_is_a_label_and_a_list(): void
    {
        $series = fn (array $attrs) => (new Event)->forceFill($attrs + ['starts_at' => '2026-10-01 20:00:00', 'days_of_week' => '1111111']);

        $this->assertNull((new Event)->forceFill(['starts_at' => '2026-10-01 20:00:00'])->recurrenceSummary(), 'an event that happens once');
        $this->assertSame(['label' => 'Weekly', 'days' => ['Monday', 'Wednesday'], 'until' => null],
            $series(['recurring_frequency' => 'weekly', 'days_of_week' => '0101000'])->recurrenceSummary());
        $this->assertSame(['label' => 'Daily', 'days' => [], 'until' => null],
            $series(['recurring_frequency' => 'weekly'])->recurrenceSummary(), 'every day of the week is daily');
        $this->assertSame(['label' => 'Every 2 weeks', 'days' => ['Friday'], 'until' => '2026-12-18'],
            $series(['recurring_frequency' => 'every_n_weeks', 'recurring_interval' => 2, 'days_of_week' => '0000010', 'recurring_end_type' => 'on_date', 'recurring_end_value' => '2026-12-18'])->recurrenceSummary());
        // saveEvent() writes '1111111' for the rhythms that do not read the days: they are not named.
        $this->assertSame(['label' => 'Monthly', 'days' => [], 'until' => null], $series(['recurring_frequency' => 'monthly_weekday'])->recurrenceSummary());
        $this->assertSame(['label' => 'Yearly', 'days' => [], 'until' => null], $series(['recurring_frequency' => 'yearly'])->recurrenceSummary());
        // A legacy series has no frequency at all, and is weekly.
        $this->assertSame('Weekly', $series(['days_of_week' => '0000100'])->recurrenceSummary()['label']);
        // No day left on a weekly series: nothing to say (it has only dates added by hand).
        $this->assertNull($series(['recurring_frequency' => 'weekly', 'days_of_week' => '0000000'])->recurrenceSummary());

        // A restored backup never went through saveEvent(): what cannot be read is not a 500.
        $this->assertNull($series(['recurring_frequency' => 'weekly', 'recurring_end_type' => 'on_date', 'recurring_end_value' => 'soon', 'days_of_week' => '0000100'])->recurrenceSummary());
        $this->assertSame([], $series(['recurring_frequency' => 'every_n_weeks', 'recurring_interval' => 0, 'days_of_week' => '0000100'])->occurrencesAfter('2026-10-08'));
    }

    public function test_the_next_dates_stop_where_the_series_does(): void
    {
        $role = $this->role;
        $series = fn (array $attrs) => $this->createEvent($role, $attrs + ['starts_at' => $this->at('2026-10-01', '20:00'), 'days_of_week' => '0000100', 'recurring_frequency' => 'weekly', 'creator_role_id' => $role->id])->fresh();

        $ending = $series(['recurring_end_type' => 'on_date', 'recurring_end_value' => '2026-10-22']);
        $this->assertSame(['2026-10-15', '2026-10-22'], $ending->occurrencesAfter('2026-10-08'));
        $this->assertSame([], $ending->occurrencesAfter('2026-10-22'));

        $skipping = $series(['recurring_exclude_dates' => ['2026-10-15']]);
        $this->assertSame(['2026-10-22', '2026-10-29', '2026-11-05'], $skipping->occurrencesAfter('2026-10-08'));

        // A year's scan for each date is three years of days: a yearly event says its next one.
        $yearly = $series(['recurring_frequency' => 'yearly', 'days_of_week' => '1111111']);
        $this->assertSame(['2027-10-01'], $yearly->occurrencesAfter('2026-10-01'));
    }
}
