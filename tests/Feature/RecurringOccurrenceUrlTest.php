<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A date in a guest event URL must be a real occurrence of that event.
 *
 * The route only constrains the SHAPE of {date} ('\d{4}-\d{2}-\d{2}'), so before
 * RoleController::viewGuest() guarded with Event::matchesDate() every well-formed date rendered a
 * distinct, self-canonical, index,follow page carrying identical content and a synthesized
 * startDate. 1999-01-03 and 2099-12-25 both returned 200. That is an unbounded duplicate-content
 * space, and it is what Google had crawled its way into: ~164k "Crawled - currently not indexed"
 * URLs against a sitemap advertising ~5k.
 *
 * It redirects rather than 404s. Stored dates build user-facing URLs - Sale::getEventUrl() on the
 * buyer's tickets page and the owner's sales table, the Stripe cancel URL, waitlist mail, the
 * ticket-confirmation push - and they stop matching the moment an owner edits the recurrence, so a
 * 404 would break a paying customer's own confirmation link. Removing the 200 is all the crawl
 * problem needed, and a 302 does that without stranding anyone.
 */
class RecurringOccurrenceUrlTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** The Sunday two weeks out, at noon, so the UTC and schedule-local calendar dates agree. */
    private function nextSunday(int $addWeeks = 0): Carbon
    {
        return Carbon::now()->startOfWeek(Carbon::SUNDAY)->addWeeks(2 + $addWeeks)->setTime(12, 0);
    }

    /** Weekly event that occurs on Sundays only (days_of_week is Carbon-indexed, 0 = Sunday). */
    private function sundayEvent(array $attrs = []): array
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $start = $this->nextSunday();
        $event = $this->createRecurringEvent($role, array_merge([
            'name' => 'Sunday Yin Yoga',
            'days_of_week' => '1000000',
            'recurring_frequency' => 'weekly',
            'starts_at' => $start->format('Y-m-d H:i:s'),
        ], $attrs));

        return [$role, $event, $start];
    }

    /** A non-occurrence must 302 to the same event without the date, never 404 and never render. */
    private function assertBouncesToUndatedEvent($role, $event, string $date): void
    {
        $this->get($this->guestEventUrl($role, $event, $date))
            ->assertStatus(302)
            ->assertRedirect($event->getGuestUrl($role->subdomain));
    }

    public function test_real_occurrence_renders(): void
    {
        [$role, $event] = $this->sundayEvent();

        $this->get($this->guestEventUrl($role, $event, $this->nextSunday(2)->format('Y-m-d')))
            ->assertOk();
    }

    public function test_wrong_weekday_bounces(): void
    {
        [$role, $event] = $this->sundayEvent();

        // The Monday after a genuine occurrence: well-formed, adjacent, and not an occurrence.
        $this->assertBouncesToUndatedEvent($role, $event, $this->nextSunday(2)->addDay()->format('Y-m-d'));
    }

    public function test_date_before_the_event_starts_bounces(): void
    {
        [$role, $event] = $this->sundayEvent();

        // A Sunday, so the weekday matches - only the start date rules it out.
        $this->assertBouncesToUndatedEvent($role, $event, $this->nextSunday(-4)->format('Y-m-d'));
    }

    public function test_absurd_past_and_future_dates_bounce(): void
    {
        [$role, $event] = $this->sundayEvent();

        // The two dates that returned 200 in production. 1999-01-03 is a Sunday (so the weekday
        // matches and only the start date rejects it); 2099-12-25 is a Friday.
        $this->assertBouncesToUndatedEvent($role, $event, '1999-01-03');
        $this->assertBouncesToUndatedEvent($role, $event, '2099-12-25');
    }

    public function test_date_after_the_recurrence_ends_bounces(): void
    {
        $endsAt = $this->nextSunday(3);
        [$role, $event] = $this->sundayEvent([
            'recurring_end_type' => 'on_date',
            'recurring_end_value' => $endsAt->format('Y-m-d'),
        ]);

        // On the end date it still occurs; the Sunday after it does not.
        $this->get($this->guestEventUrl($role, $event, $endsAt->format('Y-m-d')))->assertOk();
        $this->assertBouncesToUndatedEvent($role, $event, $this->nextSunday(4)->format('Y-m-d'));
    }

    public function test_excluded_date_bounces(): void
    {
        $skipped = $this->nextSunday(2)->format('Y-m-d');
        [$role, $event] = $this->sundayEvent(['recurring_exclude_dates' => [$skipped]]);

        $this->assertBouncesToUndatedEvent($role, $event, $skipped);
        // The following Sunday is unaffected, so the exclusion is not just breaking the event.
        $this->get($this->guestEventUrl($role, $event, $this->nextSunday(3)->format('Y-m-d')))
            ->assertOk();
    }

    public function test_non_recurring_event_only_matches_its_own_date(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        $start = Carbon::now()->addDays(10)->setTime(12, 0);
        $event = $this->createEvent($role, ['starts_at' => $start->format('Y-m-d H:i:s')]);

        $this->get($this->guestEventUrl($role, $event, $start->format('Y-m-d')))->assertOk();
        $this->assertBouncesToUndatedEvent($role, $event, $start->copy()->addDay()->format('Y-m-d'));
    }

    /**
     * The regression an earlier abort(404) would have shipped: a ticket is sold for an occurrence,
     * the owner then cancels that one date (which is exactly what recurring_exclude_dates is), and
     * the buyer opens the link in their confirmation. Sale::getEventUrl() carries the stored date,
     * so it no longer matches - and it still has to reach the event.
     */
    public function test_a_ticket_holders_link_survives_the_occurrence_being_cancelled(): void
    {
        $sold = $this->nextSunday(2)->format('Y-m-d');
        [$role, $event] = $this->sundayEvent();

        $sale = $this->createSale($event, $role, ['event_date' => $sold]);
        $this->get($sale->getEventUrl())->assertOk();

        // Owner cancels that single occurrence after the sale.
        $event->recurring_exclude_dates = [$sold];
        $event->save();

        $this->get($sale->getEventUrl())
            ->assertStatus(302)
            ->assertRedirect($event->getGuestUrl($role->subdomain));
    }

    public function test_the_bounce_preserves_the_query_string(): void
    {
        [$role, $event] = $this->sundayEvent();

        // TicketController's Stripe cancel URL appends ?tickets=true to a dated event URL. Losing
        // it would drop an abandoning buyer on the event with the tickets panel closed.
        $this->get($this->guestEventUrl($role, $event, '2099-12-25').'?tickets=true')
            ->assertRedirect($event->getGuestUrl($role->subdomain).'?tickets=true');
    }

    public function test_query_param_date_is_dropped_rather_than_bounced(): void
    {
        [$role, $event] = $this->sundayEvent();

        // The query form is not a crawlable, self-canonical URL, and a malformed ?date= must not
        // break an otherwise valid event page. So it renders - but the non-occurrence must not
        // survive into the canonical, or the canonical would advertise a URL that redirects away.
        $content = $this->get($this->guestEventUrl($role, $event).'?date=2099-12-25')
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('2099-12-25', $content);
    }

    public function test_unparseable_query_date_does_not_break_the_event(): void
    {
        [$role, $event] = $this->sundayEvent();

        // date('Y-m-d', strtotime('nonsense')) is '1970-01-01'. That is an artifact of the parse
        // failing, not a date anyone asked for, so the event still renders.
        $this->get($this->guestEventUrl($role, $event).'?date=nonsense')->assertOk();
    }
}
