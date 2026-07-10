<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

/**
 * An event's date and time are properties of its venue, not of whoever is looking.
 *
 * These surfaces render into emails and structured data. EmailService sends synchronously from the
 * admin portal, so a mailable can render inside an authenticated request - meaning a viewer-dependent
 * value gets mailed to the buyer. Guarding this needs no database: the models are built in memory.
 */
class EventVenueTimeRenderingTest extends TestCase
{
    /** A 9:00 PM America/New_York show. Its UTC date is the following day. */
    private function eveningShow(): Event
    {
        $event = new Event;
        $event->name = 'Evening Show';
        $event->starts_at = '2026-07-11 01:00:00';
        $event->duration = 2;
        $event->setRelation('creatorRole', new Role(['timezone' => 'America/New_York']));
        // The calendar URLs read getTitle(), whose venue accessor lazy-loads roles.
        $event->setRelation('roles', collect());

        return $event;
    }

    /** @return array<string, ?string> label => viewer timezone (null = unauthenticated) */
    private function viewers(): array
    {
        return [
            'guest / queue worker' => null,
            'admin in the venue timezone' => 'America/New_York',
            'admin with a UTC profile' => 'UTC',
            'admin east of UTC' => 'Asia/Tokyo',
        ];
    }

    private function actAs(?string $timezone): void
    {
        if ($timezone === null) {
            Auth::logout();

            return;
        }

        Auth::setUser(new User(['timezone' => $timezone]));
    }

    public function test_start_end_time_is_the_venues_clock_for_every_viewer(): void
    {
        $event = $this->eveningShow();

        foreach ($this->viewers() as $label => $timezone) {
            $this->actAs($timezone);

            $this->assertSame(
                '9:00 PM - 11:00 PM',
                $event->getStartEndTime('2026-07-10'),
                "getStartEndTime() must render the venue's clock for: {$label}"
            );
        }
    }

    public function test_schema_start_date_is_the_same_instant_for_every_viewer(): void
    {
        $event = $this->eveningShow();
        $expected = Carbon::parse('2026-07-11 01:00:00', 'UTC');

        foreach ($this->viewers() as $label => $timezone) {
            $this->actAs($timezone);

            $this->assertTrue(
                Carbon::parse($event->getSchemaStartDate('2026-07-10'))->eq($expected),
                "getSchemaStartDate() must emit one absolute instant for: {$label}"
            );
        }
    }

    public function test_occurrence_date_line_is_the_venues_calendar_date_for_every_viewer(): void
    {
        $event = $this->eveningShow();

        foreach ($this->viewers() as $label => $timezone) {
            $this->actAs($timezone);

            $this->assertSame(
                'Jul 10, 2026',
                $event->getStartDateTime('2026-07-10', true)->format('M j, Y'),
                "the rendered date must equal the stored venue date for: {$label}"
            );
        }
    }

    public function test_sale_event_date_is_the_venue_date_not_the_utc_date(): void
    {
        $event = $this->eveningShow();

        $this->assertSame('2026-07-10', $event->saleEventDateFromStartsAt());
        $this->assertSame('2026-07-11', Carbon::parse($event->starts_at)->format('Y-m-d'));
    }

    /**
     * RSVP must stay open until midnight AT THE VENUE. Parsing the occurrence date in the app
     * timezone closed it at UTC midnight - an hour before doors for a 9pm New York show.
     */
    public function test_rsvp_closes_at_the_venues_midnight_not_utc_midnight(): void
    {
        $event = $this->eveningShow();
        $event->days_of_week = '1111111';
        $event->recurring_frequency = 'daily';
        $event->rsvp_enabled = true;
        $event->is_cancelled = false;

        // 6:00 PM New York, three hours before doors. UTC has not rolled over yet.
        Carbon::setTestNow(Carbon::parse('2026-07-10 22:00:00', 'UTC'));
        $this->assertTrue($event->canAcceptRsvp('2026-07-10'), 'RSVP must be open before doors');

        // 11:30 PM New York, still the venue's day. UTC is already 2026-07-11.
        Carbon::setTestNow(Carbon::parse('2026-07-11 03:30:00', 'UTC'));
        $this->assertTrue($event->canAcceptRsvp('2026-07-10'), 'RSVP must stay open until the venue midnight');

        // 1:00 AM New York, the next venue day.
        Carbon::setTestNow(Carbon::parse('2026-07-11 05:00:00', 'UTC'));
        $this->assertFalse($event->canAcceptRsvp('2026-07-10'), 'RSVP must close after the venue midnight');

        Carbon::setTestNow();
    }

    /**
     * Feeds and "Add to Calendar" links stamp UTC, so a dated occurrence must be rebuilt from
     * the venue's time-of-day. Using the UTC time-of-day put recurring events a day early.
     */
    public function test_dated_occurrence_instants_agree_across_every_calendar_surface(): void
    {
        $event = $this->eveningShow();
        $event->days_of_week = '0000010'; // Fridays

        $expected = Carbon::parse('2026-07-18 01:00:00', 'UTC'); // 9pm Fri Jul 17 New York

        $this->assertTrue($event->occurrenceStartUtc('2026-07-17')->eq($expected));

        preg_match('/dates=([^&\/]+)/', $event->getGoogleCalendarUrl('2026-07-17'), $google);
        $this->assertSame($expected->format('Ymd\THis\Z'), $google[1]);

        preg_match('/startdt=([^&]+)/', $event->getMicrosoftCalendarUrl('2026-07-17'), $outlook);
        $this->assertSame($expected->format('Y-m-d\TH:i:s\Z'), urldecode($outlook[1]));
    }

    protected function tearDown(): void
    {
        Auth::logout();

        parent::tearDown();
    }
}
