<?php

namespace Tests\Feature;

use App\Utils\GoogleImportUtils;
use App\Utils\IcsImportUtils;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * GoogleImportUtils turns what Google's API returns into what the import already reads. Pure:
 * every case is a list of entries in Google's shape, read back through the feed reader as of
 * noon on 10 October 2026 in New York, which is how the import page reads them.
 */
class GoogleImportUtilsTest extends TestCase
{
    private const ZONE = 'America/New_York';

    private function entry(array $overrides = []): array
    {
        return array_merge([
            'id' => 'evt-'.substr(md5(json_encode($overrides)), 0, 8),
            'status' => 'confirmed',
            'summary' => 'Untitled',
            'description' => null,
            'location' => null,
            'visibility' => null,
            'eventType' => 'default',
            'start' => ['date' => null, 'dateTime' => '2026-10-20T18:00:00-04:00', 'timeZone' => null],
            'end' => ['date' => null, 'dateTime' => '2026-10-20T19:30:00-04:00', 'timeZone' => null],
            'recurrence' => [],
            'recurringEventId' => null,
            'originalStartTime' => null,
        ], $overrides);
    }

    private function read(array $entries, bool $keepLocalClock = false, ?string $calendarZone = self::ZONE): array
    {
        return IcsImportUtils::read(
            GoogleImportUtils::toCalendarText($entries, $calendarZone),
            self::ZONE,
            $keepLocalClock,
            Carbon::parse('2026-10-10 12:00', self::ZONE)
        );
    }

    public function test_the_calendars_offered_leave_out_googles_own_and_put_the_main_one_last(): void
    {
        $choices = GoogleImportUtils::calendarChoices([
            ['id' => 'owner@example.com', 'name' => 'owner@example.com', 'color' => '#9FE1E7', 'primary' => true, 'access' => 'owner'],
            ['id' => 'en.usa#holiday@group.v.calendar.google.com', 'name' => 'Holidays in United States', 'color' => '#16a765', 'primary' => false, 'access' => 'reader'],
            ['id' => 'addressbook#contacts@group.v.calendar.google.com', 'name' => 'Birthdays', 'color' => '#92e1c0', 'primary' => false, 'access' => 'reader'],
            ['id' => 'e_2_en#weeknum@group.v.calendar.google.com', 'name' => 'Week Numbers', 'color' => null, 'primary' => false, 'access' => 'reader'],
            ['id' => 'shared123@group.calendar.google.com', 'name' => 'Town hall', 'color' => 'red; background: url(x)', 'primary' => false, 'access' => 'reader'],
            ['id' => 'zclasses@group.calendar.google.com', 'name' => 'Studio classes', 'color' => '#F691B2', 'primary' => false, 'access' => 'owner'],
            ['id' => 'agigs@group.calendar.google.com', 'name' => '', 'color' => '#abc', 'primary' => false, 'access' => 'writer'],
        ]);

        // Owned first, then shared (by name), then the account's main calendar.
        $this->assertSame(
            ['zclasses@group.calendar.google.com', 'agigs@group.calendar.google.com', 'shared123@group.calendar.google.com', 'owner@example.com'],
            array_column($choices, 'id')
        );
        $this->assertSame([false, false, true, false], array_column($choices, 'read_only'));
        $this->assertSame([false, false, false, true], array_column($choices, 'primary'));
        // A colour goes into a style attribute: a plain hex value or nothing.
        $this->assertSame(['#f691b2', null, null, '#9fe1e7'], array_column($choices, 'color'));
        // A calendar with no name is shown by its address rather than as a blank row.
        $this->assertSame('agigs@group.calendar.google.com', $choices[1]['name']);
        $this->assertArrayNotHasKey('rank', $choices[0]);

        $this->assertSame('owner@example.com', GoogleImportUtils::accountOf([
            ['id' => 'zclasses@group.calendar.google.com', 'primary' => false],
            ['id' => 'owner@example.com', 'primary' => true],
        ]));
        $this->assertNull(GoogleImportUtils::accountOf([['id' => 'zclasses@group.calendar.google.com', 'primary' => false]]));
    }

    public function test_a_timed_entry_and_an_all_day_one_arrive_as_a_feed_would_give_them(): void
    {
        $rows = $this->read([
            $this->entry(['id' => 'timed', 'summary' => 'Open mic', 'location' => 'The Blue Note, 131 W 3rd St, New York']),
            $this->entry([
                'id' => 'allday', 'summary' => 'Street fair',
                'start' => ['date' => '2026-10-24', 'dateTime' => null, 'timeZone' => null],
                'end' => ['date' => '2026-10-26', 'dateTime' => null, 'timeZone' => null],
            ]),
        ])['rows'];

        $this->assertSame(['Open mic', 'Street fair'], array_column($rows, 'event_name'));
        $this->assertSame('2026-10-20 18:00', $rows[0]['event_date_time']);
        $this->assertSame(1.5, $rows[0]['event_duration']);
        $this->assertFalse($rows[0]['is_all_day']);
        $this->assertSame('The Blue Note', $rows[0]['venue_name']);
        $this->assertSame('timed', $rows[0]['source_uid']);

        $this->assertTrue($rows[1]['is_all_day']);
        $this->assertSame('2026-10-24 00:00', $rows[1]['event_date_time']);
    }

    public function test_a_time_is_read_in_the_zone_google_names_or_the_calendars_own(): void
    {
        // The same instant, said three ways. A venue's events happen at the venue: 20:00 in
        // Los Angeles is 23:00 in New York.
        $rows = $this->read([
            $this->entry(['id' => 'named', 'summary' => 'Named zone',
                'start' => ['date' => null, 'dateTime' => '2026-10-21T20:00:00-07:00', 'timeZone' => 'America/Los_Angeles'],
                'end' => ['date' => null, 'dateTime' => '2026-10-21T21:00:00-07:00', 'timeZone' => 'America/Los_Angeles']]),
            $this->entry(['id' => 'bad', 'summary' => 'Unusable zone',
                'start' => ['date' => null, 'dateTime' => '2026-10-22T20:00:00-07:00', 'timeZone' => "America/Los_Angeles\r\nSUMMARY:Injected"],
                'end' => ['date' => null, 'dateTime' => '2026-10-22T21:00:00-07:00', 'timeZone' => null]]),
        ])['rows'];

        $this->assertSame('2026-10-21 23:00', $rows[0]['event_date_time']);
        // A zone that is not one falls back to the calendar's, and never reaches the text.
        $this->assertSame('2026-10-22 23:00', $rows[1]['event_date_time']);
        $this->assertSame('Unusable zone', $rows[1]['event_name']);

        // A touring schedule keeps the clock time of where the event is, and says where.
        $touring = $this->read([
            $this->entry(['id' => 'la', 'summary' => 'LA show',
                'start' => ['date' => null, 'dateTime' => '2026-10-21T20:00:00-07:00', 'timeZone' => 'America/Los_Angeles'],
                'end' => ['date' => null, 'dateTime' => '2026-10-21T21:00:00-07:00', 'timeZone' => 'America/Los_Angeles']]),
        ], true)['rows'];
        $this->assertSame('2026-10-21 20:00', $touring[0]['event_date_time']);
        $this->assertSame('America/Los_Angeles', $touring[0]['local_time_zone']);
    }

    public function test_a_zone_given_as_an_offset_does_not_cost_the_entry(): void
    {
        // PHP accepts "GMT+02:00" as a zone and names it "+02:00", which is no zone the calendar
        // reader knows: written into the text, the entry was dropped. Names PHP lists are
        // used, old ones included; anything else falls back to the calendar's zone, and the
        // moment is the same one either way because it carries its own offset.
        $at = fn (?string $zone) => $this->entry(['id' => 'z-'.md5((string) $zone), 'summary' => (string) $zone,
            'start' => ['date' => null, 'dateTime' => '2026-10-28T19:00:00+01:00', 'timeZone' => $zone],
            'end' => ['date' => null, 'dateTime' => '2026-10-28T21:00:00+01:00', 'timeZone' => $zone]]);

        $zones = ['GMT+02:00', '+02:00', 'Not/AZone', 'Europe/Berlin', 'Asia/Calcutta', 'EST5EDT', 'Etc/GMT-2', 'UTC'];
        $rows = $this->read(array_map($at, $zones))['rows'];

        $this->assertCount(count($zones), $rows, 'no entry is lost to its zone name');
        // 19:00 in Berlin on the 28th is 14:00 in New York, whatever the entry called its zone.
        $this->assertSame(['2026-10-28 14:00'], array_values(array_unique(array_column($rows, 'event_date_time'))));

        $text = GoogleImportUtils::toCalendarText(array_map($at, ['GMT+02:00', '+02:00', 'EST5EDT']), 'Europe/Berlin');
        $this->assertStringNotContainsString('TZID=+02:00', $text);
        $this->assertSame(2, substr_count($text, 'DTSTART;TZID=Europe/Berlin:20261028T190000'));
        $this->assertStringContainsString('DTSTART;TZID=EST5EDT:20261028T140000', $text);
    }

    public function test_a_repeating_entry_with_an_offset_for_a_zone_repeats_on_its_own_days(): void
    {
        // The moment is the same on any clock. A rule is not: "every Monday" at 08:00 +10:00
        // is Sunday afternoon in Los Angeles, and written on the calendar's clock there it came
        // back as Monday once and Tuesday ever after.
        $weekly = fn (string $at, string $zone) => $this->entry(['id' => 'class', 'summary' => 'Monday class',
            'start' => ['date' => null, 'dateTime' => $at, 'timeZone' => $zone],
            'end' => ['date' => null, 'dateTime' => $at, 'timeZone' => $zone],
            'recurrence' => ['RRULE:FREQ=WEEKLY;BYDAY=MO']]);
        $readIn = fn (string $schedule, array $entry, string $calendar) => IcsImportUtils::read(
            GoogleImportUtils::toCalendarText([$entry], $calendar), $schedule, false, Carbon::parse('2026-10-10 12:00', $schedule)
        )['rows'];

        // A whole number of hours has a zone of its own, whose sign runs backwards.
        $text = GoogleImportUtils::toCalendarText([$weekly('2026-10-12T08:00:00+10:00', 'GMT+10:00')], 'America/Los_Angeles');
        $this->assertStringContainsString('DTSTART;TZID=Etc/GMT-10:20261012T080000', $text);
        $rows = $readIn('Australia/Brisbane', $weekly('2026-10-12T08:00:00+10:00', 'GMT+10:00'), 'America/Los_Angeles');
        $this->assertCount(1, $rows);
        $this->assertSame('2026-10-12 08:00', $rows[0]['event_date_time']);
        $this->assertSame(['weekly', [1]], [$rows[0]['recurrence']['frequency'], $rows[0]['recurrence']['days']]);

        // The calendar's own zone, when it is at that offset then: it is the better answer,
        // because it goes on to change its clocks as the calendar does.
        $this->assertStringContainsString(
            'DTSTART;TZID=America/Los_Angeles:20261012T080000',
            GoogleImportUtils::toCalendarText([$weekly('2026-10-12T08:00:00-07:00', 'GMT-07:00')], 'America/Los_Angeles')
        );
        $this->assertStringContainsString('DTSTART:20261012T080000Z', GoogleImportUtils::toCalendarText([$weekly('2026-10-12T08:00:00+00:00', 'GMT+00:00')], 'America/Los_Angeles'));

        // An offset no listed zone keeps, on a calendar somewhere else: there is no clock to
        // repeat it on, and on the wrong one it would repeat on the wrong days. Left out.
        $this->assertSame([], $readIn('Asia/Kolkata', $weekly('2026-10-12T08:00:00+05:30', 'GMT+05:30'), 'America/Los_Angeles'));
        // The same entry on a calendar that is at that offset is that calendar's.
        $rows = $readIn('Asia/Kolkata', $weekly('2026-10-12T08:00:00+05:30', 'GMT+05:30'), 'Asia/Kolkata');
        $this->assertSame(['2026-10-12 08:00', 'weekly'], [$rows[0]['event_date_time'], $rows[0]['recurrence']['frequency']]);

        // And a zone Google names properly is used as it always was.
        $rows = $readIn('Australia/Brisbane', $weekly('2026-10-12T08:00:00+10:00', 'Australia/Brisbane'), 'America/Los_Angeles');
        $this->assertSame(['2026-10-12 08:00', [1]], [$rows[0]['event_date_time'], $rows[0]['recurrence']['days']]);
    }

    public function test_an_invitation_the_calendars_owner_declined_is_left_out(): void
    {
        $accepted = $this->entry(['id' => 'yes', 'summary' => 'Open mic']);
        $declined = $this->entry(['id' => 'no', 'summary' => 'Board meeting', 'declined' => true,
            'start' => ['date' => null, 'dateTime' => '2026-10-21T15:00:00-04:00', 'timeZone' => null],
            'end' => ['date' => null, 'dateTime' => '2026-10-21T16:00:00-04:00', 'timeZone' => null]]);

        $read = $this->read([$accepted, $declined]);
        $this->assertSame(['Open mic'], array_column($read['rows'], 'event_name'));
        // Not one of the calendar's events at all, so not counted as one left out either.
        $this->assertSame(0, array_sum($read['skipped']));
    }

    public function test_one_declined_date_of_a_series_is_an_exclusion_and_a_declined_series_goes_whole(): void
    {
        $weekly = fn (string $id, string $name, array $extra = []) => $this->entry($extra + [
            'id' => $id, 'summary' => $name,
            'start' => ['date' => null, 'dateTime' => '2026-10-05T18:00:00-04:00', 'timeZone' => 'America/New_York'],
            'end' => ['date' => null, 'dateTime' => '2026-10-05T19:00:00-04:00', 'timeZone' => 'America/New_York'],
            'recurrence' => ['RRULE:FREQ=WEEKLY;BYDAY=MO'],
        ]);
        // Declining one date makes it an entry of its own, still "confirmed", with the reply.
        $oneDate = fn (string $series, string $day, array $extra = []) => $this->entry($extra + [
            'id' => $series.'_'.str_replace('-', '', $day).'T220000Z', 'summary' => 'One date',
            'start' => ['date' => null, 'dateTime' => $day.'T18:00:00-04:00', 'timeZone' => 'America/New_York'],
            'end' => ['date' => null, 'dateTime' => $day.'T19:00:00-04:00', 'timeZone' => 'America/New_York'],
            'recurringEventId' => $series,
            'originalStartTime' => ['date' => null, 'dateTime' => $day.'T18:00:00-04:00', 'timeZone' => 'America/New_York'],
        ]);

        $read = $this->read([
            $weekly('class', 'Monday class'),
            $oneDate('class', '2026-10-19', ['declined' => true]),
            $weekly('standup', 'Declined standup', ['declined' => true]),
            // A date of the declined series that was moved: it goes with its series.
            $oneDate('standup', '2026-10-26'),
        ]);

        $this->assertCount(1, $read['rows']);
        $row = $read['rows'][0];
        $this->assertSame('Monday class', $row['event_name']);
        $this->assertSame('weekly', $row['recurrence']['frequency'], 'a declined date must not cost the series its rule');
        $this->assertSame(['2026-10-19'], $row['recurrence']['fields']['recurring_exclude_dates']);
        $this->assertSame(0, array_sum($read['skipped']));
    }

    public function test_googles_own_models_become_the_arrays_this_reads(): void
    {
        // An answer in the shape events.list gives, through the library's own classes.
        $page = new \Google\Service\Calendar\Events(['items' => [
            ['id' => 'invited', 'status' => 'confirmed', 'summary' => 'Board meeting', 'eventType' => 'default',
                'start' => ['dateTime' => '2026-10-21T15:00:00-04:00', 'timeZone' => 'America/New_York'],
                'end' => ['dateTime' => '2026-10-21T16:00:00-04:00', 'timeZone' => 'America/New_York'],
                'attendees' => [['email' => 'owner@example.com', 'self' => true, 'responseStatus' => 'declined']]],
            ['id' => 'going', 'status' => 'confirmed', 'summary' => 'Open mic', 'eventType' => 'default',
                'start' => ['dateTime' => '2026-10-22T19:00:00-04:00'], 'end' => ['dateTime' => '2026-10-22T21:00:00-04:00'],
                'attendees' => [['email' => 'owner@example.com', 'self' => true, 'responseStatus' => 'accepted']]],
            // Somebody else's "no" on an entry of one's own is not one's own.
            ['id' => 'hosting', 'status' => 'confirmed', 'summary' => 'Workshop', 'eventType' => 'default',
                'start' => ['dateTime' => '2026-10-23T10:00:00-04:00'], 'end' => ['dateTime' => '2026-10-23T12:00:00-04:00'],
                'attendees' => [['email' => 'guest@example.com', 'responseStatus' => 'declined']]],
            ['id' => 'allday', 'status' => 'confirmed', 'summary' => 'Fair', 'start' => ['date' => '2026-10-24'], 'end' => ['date' => '2026-10-25']],
            // A date deleted from a series: no start, no end, no summary.
            ['id' => 'class_20261019T220000Z', 'status' => 'cancelled', 'recurringEventId' => 'class',
                'originalStartTime' => ['dateTime' => '2026-10-19T18:00:00-04:00', 'timeZone' => 'America/New_York']],
        ]]);

        $entries = array_map([\App\Services\GoogleCalendarService::class, 'importEntry'], $page->getItems());

        $this->assertSame([true, false, false, false, false], array_column($entries, 'declined'));
        $this->assertSame(['date' => null, 'dateTime' => '2026-10-21T15:00:00-04:00', 'timeZone' => 'America/New_York'], $entries[0]['start']);
        $this->assertSame(['date' => '2026-10-24', 'dateTime' => null, 'timeZone' => null], $entries[3]['start']);
        $this->assertNull($entries[4]['start']);
        $this->assertSame('class', $entries[4]['recurringEventId']);
        $this->assertSame([], $entries[1]['recurrence']);

        $this->assertSame(['Open mic', 'Workshop', 'Fair'], array_column($this->read($entries)['rows'], 'event_name'));
    }

    public function test_a_weekly_entry_is_one_repeating_row_and_a_removed_date_is_an_exclusion(): void
    {
        $weekly = $this->entry([
            'id' => 'class', 'summary' => 'Monday class',
            'start' => ['date' => null, 'dateTime' => '2026-10-05T18:00:00-04:00', 'timeZone' => 'America/New_York'],
            'end' => ['date' => null, 'dateTime' => '2026-10-05T19:00:00-04:00', 'timeZone' => 'America/New_York'],
            'recurrence' => ['RRULE:FREQ=WEEKLY;BYDAY=MO'],
        ]);
        // Google reports a date deleted from a series as a cancelled entry of its own.
        $removed = $this->entry([
            'id' => 'class_20261019T220000Z', 'status' => 'cancelled', 'summary' => null,
            'start' => null, 'end' => null,
            'recurringEventId' => 'class',
            'originalStartTime' => ['date' => null, 'dateTime' => '2026-10-19T18:00:00-04:00', 'timeZone' => 'America/New_York'],
        ]);

        $read = $this->read([$weekly, $removed]);

        $this->assertCount(1, $read['rows']);
        $row = $read['rows'][0];
        $this->assertSame('Monday class', $row['event_name']);
        $this->assertNull($row['series'], 'a removed date must not cost the series its repeat rule');
        $this->assertSame('weekly', $row['recurrence']['frequency']);
        $this->assertSame([1], $row['recurrence']['days']);
        $this->assertSame(['2026-10-19'], $row['recurrence']['fields']['recurring_exclude_dates']);
        // The removed date is an exclusion, not a cancelled event left out and counted.
        $this->assertSame(0, $read['skipped']['cancelled']);
    }

    public function test_a_moved_date_lists_the_series_as_its_dates_with_the_move(): void
    {
        $weekly = $this->entry([
            'id' => 'jam', 'summary' => 'Friday jam',
            'start' => ['date' => null, 'dateTime' => '2026-10-09T20:00:00-04:00', 'timeZone' => 'America/New_York'],
            'end' => ['date' => null, 'dateTime' => '2026-10-09T22:00:00-04:00', 'timeZone' => 'America/New_York'],
            'recurrence' => ['RRULE:FREQ=WEEKLY;BYDAY=FR;COUNT=4'],
        ]);
        $moved = $this->entry([
            'id' => 'jam_20261024T000000Z', 'summary' => 'Friday jam (Saturday this week)',
            'start' => ['date' => null, 'dateTime' => '2026-10-24T20:00:00-04:00', 'timeZone' => 'America/New_York'],
            'end' => ['date' => null, 'dateTime' => '2026-10-24T22:00:00-04:00', 'timeZone' => 'America/New_York'],
            'recurringEventId' => 'jam',
            'originalStartTime' => ['date' => null, 'dateTime' => '2026-10-23T20:00:00-04:00', 'timeZone' => 'America/New_York'],
        ]);

        $rows = $this->read([$weekly, $moved])['rows'];

        // 16, 24 (moved from the 23rd) and 30 October: the 9th is past.
        $this->assertSame(['2026-10-16 20:00', '2026-10-24 20:00', '2026-10-30 20:00'], array_column($rows, 'event_date_time'));
        $this->assertNotNull($rows[0]['series']);
        $this->assertSame(1, count(array_unique(array_column(array_column($rows, 'series'), 'id'))));
        $this->assertNull($rows[0]['recurrence']);
    }

    public function test_what_is_private_cancelled_or_not_an_event_is_left_out(): void
    {
        $read = $this->read([
            $this->entry(['id' => 'keep', 'summary' => 'Public show']),
            $this->entry(['id' => 'private', 'summary' => 'Dentist', 'visibility' => 'private']),
            $this->entry(['id' => 'confidential', 'summary' => 'Board call', 'visibility' => 'confidential']),
            $this->entry(['id' => 'cancelled', 'summary' => 'Called off', 'status' => 'cancelled']),
            $this->entry(['id' => 'birthday', 'summary' => 'Sam\'s birthday', 'eventType' => 'birthday']),
            $this->entry(['id' => 'ooo', 'summary' => 'Out of office', 'eventType' => 'outOfOffice']),
            $this->entry(['id' => 'office', 'summary' => 'Office', 'eventType' => 'workingLocation']),
            $this->entry(['id' => 'flight', 'summary' => 'Flight to Austin', 'eventType' => 'fromGmail']),
            $this->entry(['id' => 'nostart', 'summary' => 'Broken', 'start' => null]),
        ]);

        $this->assertSame(['Public show'], array_column($read['rows'], 'event_name'));
        $this->assertSame(2, $read['skipped']['private']);
        $this->assertSame(1, $read['skipped']['cancelled']);
    }

    public function test_text_survives_and_nothing_in_it_becomes_a_calendar_line(): void
    {
        $text = GoogleImportUtils::toCalendarText([
            $this->entry([
                'id' => 'tricky',
                'summary' => "Jazz; blues, and soul\r\nEND:VEVENT\r\nBEGIN:VEVENT\r\nSUMMARY:Injected",
                'description' => "Line one\nLine two; with, marks \\ and a slash",
                'recurrence' => [
                    "RRULE:FREQ=WEEKLY;BYDAY=TU\r\nBEGIN:VEVENT",
                    'ATTACH:https://example.com/x.png',
                    'RRULE:FREQ=WEEKLY;BYDAY=TU',
                ],
            ]),
        ], self::ZONE);

        // Counted as lines: the words are still there, inside the one line that is the name.
        $this->assertSame(1, preg_match_all('/^BEGIN:VEVENT\r?$/m', $text));
        $this->assertSame(1, preg_match_all('/^END:VEVENT\r?$/m', $text));
        $this->assertSame(0, preg_match_all('/^ATTACH/m', $text));
        $this->assertSame(1, preg_match_all('/^RRULE:/m', $text));

        $rows = IcsImportUtils::read($text, self::ZONE, false, Carbon::parse('2026-10-10 12:00', self::ZONE))['rows'];
        $this->assertCount(1, $rows);
        $this->assertStringStartsWith('Jazz; blues, and soul', $rows[0]['event_name']);
        $this->assertStringContainsString('Line two; with, marks \\ and a slash', $rows[0]['event_details']);
        $this->assertSame('weekly', $rows[0]['recurrence']['frequency']);
    }

    public function test_an_empty_calendar_is_still_a_calendar(): void
    {
        $read = $this->read([]);

        $this->assertSame([], $read['rows']);
    }
}
