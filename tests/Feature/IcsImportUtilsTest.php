<?php

namespace Tests\Feature;

use App\Utils\IcsImportUtils;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * IcsImportUtils reads a calendar feed into import preview rows. Pure, so every case is a feed
 * written out here and read back, as of noon on 10 October 2026 in New York.
 */
class IcsImportUtilsTest extends TestCase
{
    private const ZONE = 'America/New_York';

    private function feed(string ...$events): string
    {
        return "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:-//Test//EN\r\n"
            .implode('', array_map(fn ($event) => "BEGIN:VEVENT\r\n".str_replace("\n", "\r\n", trim($event))."\r\nEND:VEVENT\r\n", $events))
            ."END:VCALENDAR\r\n";
    }

    private function read(string $feed, bool $keepLocalClock = false): array
    {
        return IcsImportUtils::read($feed, self::ZONE, $keepLocalClock, Carbon::parse('2026-10-10 12:00', self::ZONE));
    }

    private function rows(string $feed, bool $keepLocalClock = false): array
    {
        return $this->read($feed, $keepLocalClock)['rows'];
    }

    /** A feed read for a schedule somewhere else, as of the same noon there. */
    private function rowsIn(string $zone, string $event, bool $keepLocalClock = false): array
    {
        return IcsImportUtils::read($this->feed($event), $zone, $keepLocalClock, Carbon::parse('2026-10-10 12:00', $zone))['rows'];
    }

    /** One row per line: the date, its weekday, and how the row repeats. */
    private function shape(array $rows, int $first = 3): array
    {
        $days = ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];

        return array_map(fn ($row) => Carbon::parse($row['event_date_time'])->format('D Y-m-d H:i').' '.($row['recurrence']
            ? $row['recurrence']['frequency'].'['.implode('', array_map(fn ($day) => $days[$day], $row['recurrence']['days'])).']'
            : ($row['series'] ? 'listed' : 'once')), array_slice($rows, 0, $first));
    }

    public function test_a_time_lands_in_the_schedules_zone_however_the_feed_states_it(): void
    {
        $rows = $this->rows($this->feed(
            "UID:utc\nSUMMARY:In UTC\nDTSTART:20261021T000000Z\nDTEND:20261021T020000Z",
            "UID:zoned\nSUMMARY:In Los Angeles\nDTSTART;TZID=America/Los_Angeles:20261022T200000\nDTEND;TZID=America/Los_Angeles:20261022T213000",
            "UID:floating\nSUMMARY:No zone at all\nDTSTART:20261023T200000\nDURATION:PT45M",
        ));

        // 00:00 UTC is 20:00 the evening before in New York.
        $this->assertSame('2026-10-20 20:00', $rows[0]['event_date_time']);
        $this->assertSame(2.0, $rows[0]['event_duration']);
        // A venue's events happen at the venue: 20:00 in Los Angeles is 23:00 there.
        $this->assertSame('2026-10-22 23:00', $rows[1]['event_date_time']);
        $this->assertSame(1.5, $rows[1]['event_duration']);
        $this->assertNull($rows[1]['local_time_zone']);
        // A time with no zone is the clock time it says.
        $this->assertSame('2026-10-23 20:00', $rows[2]['event_date_time']);
        $this->assertSame(0.75, $rows[2]['event_duration']);
    }

    public function test_a_touring_schedule_keeps_the_clock_time_of_where_the_event_is(): void
    {
        $rows = $this->rows($this->feed(
            "UID:la\nSUMMARY:Los Angeles show\nDTSTART;TZID=America/Los_Angeles:20261022T200000",
            "UID:home\nSUMMARY:Home show\nDTSTART;TZID=America/New_York:20261023T200000",
            "UID:utc\nSUMMARY:In UTC\nDTSTART:20261024T000000Z",
        ), keepLocalClock: true);

        $this->assertSame('2026-10-22 20:00', $rows[0]['event_date_time']);
        $this->assertSame('America/Los_Angeles', $rows[0]['local_time_zone']);
        // Its own zone is the schedule's: nothing to note.
        $this->assertSame('2026-10-23 20:00', $rows[1]['event_date_time']);
        $this->assertNull($rows[1]['local_time_zone']);
        // UTC says when, not where, so it is still converted.
        $this->assertSame('2026-10-23 20:00', $rows[2]['event_date_time']);
        $this->assertNull($rows[2]['local_time_zone']);
    }

    public function test_an_all_day_entry_runs_from_midnight_to_the_end_of_its_last_day(): void
    {
        $rows = $this->rows($this->feed(
            "UID:one\nSUMMARY:Open day\nDTSTART;VALUE=DATE:20261020\nDTEND;VALUE=DATE:20261021",
            "UID:three\nSUMMARY:Festival\nDTSTART;VALUE=DATE:20261023\nDTEND;VALUE=DATE:20261026",
            "UID:bare\nSUMMARY:No end\nDTSTART;VALUE=DATE:20261028",
        ));

        $this->assertSame('2026-10-20 00:00', $rows[0]['event_date_time']);
        $this->assertTrue($rows[0]['is_all_day']);
        $this->assertSame(23.983, $rows[0]['event_duration']);
        $this->assertSame(71.983, $rows[1]['event_duration']);
        $this->assertSame(23.983, $rows[2]['event_duration']);
    }

    public function test_a_weekly_entry_is_one_repeating_row(): void
    {
        $rows = $this->rows($this->feed(
            // Started in September. One Monday was called off.
            "UID:yoga\nSUMMARY:Yoga Flow\nDTSTART;TZID=America/New_York:20260907T180000\nDTEND;TZID=America/New_York:20260907T190000\n"
            ."RRULE:FREQ=WEEKLY;BYDAY=MO\nEXDATE;TZID=America/New_York:20261019T180000\nLOCATION:Studio A",
        ));

        $this->assertCount(1, $rows);
        $row = $rows[0];
        $this->assertSame('Yoga Flow', $row['event_name']);
        // A repeating event's date is its first one.
        $this->assertSame('2026-09-07 18:00', $row['event_date_time']);
        // It is listed by when it next happens: Monday the 12th.
        $this->assertSame('2026-10-12 18:00', $row['sort_at']);
        $this->assertSame('weekly', $row['recurrence']['frequency']);
        $this->assertSame([1], $row['recurrence']['days']);
        $this->assertSame(['2026-10-19'], $row['recurrence']['fields']['recurring_exclude_dates']);
        $this->assertSame(1.0, $row['event_duration']);
        $this->assertNull($row['series']);
    }

    public function test_a_rule_the_app_cannot_repeat_is_listed_as_its_next_dates(): void
    {
        $rows = $this->rows($this->feed(
            "UID:last-friday\nSUMMARY:Last Friday Social\nDTSTART;TZID=America/New_York:20260130T190000\nRRULE:FREQ=MONTHLY;BYDAY=-1FR",
            "UID:trio\nSUMMARY:Three nights\nDTSTART;TZID=America/New_York:20261014T200000\nRRULE:FREQ=DAILY;INTERVAL=3;COUNT=3",
            "UID:thirds\nSUMMARY:Every third day\nDTSTART;TZID=America/New_York:20261011T100000\nRRULE:FREQ=DAILY;INTERVAL=3",
        ));

        // A rule with far more dates in the year than are offered: the first twelve, and a flag
        // saying there are more.
        $thirds = array_values(array_filter($rows, fn ($row) => $row['event_name'] === 'Every third day'));
        $this->assertCount(IcsImportUtils::SERIES_DATES, $thirds);
        $this->assertSame('2026-11-13 10:00', $thirds[11]['event_date_time']);
        $this->assertTrue($thirds[0]['series']['more']);

        $social = array_values(array_filter($rows, fn ($row) => $row['event_name'] === 'Last Friday Social'));
        $this->assertCount(IcsImportUtils::SERIES_DATES, $social);
        $this->assertSame('2026-10-30 19:00', $social[0]['event_date_time']);
        $this->assertSame('2026-11-27 19:00', $social[1]['event_date_time']);
        $this->assertNull($social[0]['recurrence']);
        $this->assertSame(['position' => 1, 'count' => 12, 'more' => false], array_diff_key($social[0]['series'], ['id' => 0]));
        $this->assertSame($social[0]['series']['id'], $social[11]['series']['id']);
        $this->assertSame(12, $social[11]['series']['position']);

        $trio = array_values(array_filter($rows, fn ($row) => $row['event_name'] === 'Three nights'));
        $this->assertSame(['2026-10-14 20:00', '2026-10-17 20:00', '2026-10-20 20:00'], array_column($trio, 'event_date_time'));
        $this->assertSame(3, $trio[0]['series']['count']);
        $this->assertNotSame($social[0]['series']['id'], $trio[0]['series']['id']);
    }

    public function test_a_series_with_an_edited_date_is_listed_with_the_edit(): void
    {
        $rows = $this->rows($this->feed(
            "UID:trivia\nSUMMARY:Trivia Night\nDTSTART;TZID=America/New_York:20261006T190000\nRRULE:FREQ=WEEKLY;COUNT=5",
            // The night of the 20th was moved to the 21st at 8, and renamed.
            "UID:trivia\nRECURRENCE-ID;TZID=America/New_York:20261020T190000\nSUMMARY:Trivia Night (moved)\nDTSTART;TZID=America/New_York:20261021T200000",
        ));

        $this->assertSame(
            ['2026-10-13 19:00', '2026-10-21 20:00', '2026-10-27 19:00', '2026-11-03 19:00'],
            array_column($rows, 'event_date_time')
        );
        $this->assertSame('Trivia Night (moved)', $rows[1]['event_name']);
        $this->assertNull($rows[0]['recurrence']);
        $this->assertSame(4, $rows[0]['series']['count']);
        // Across the clocks going back on 1 November it is still 7 PM.
        $this->assertSame('2026-11-03 19:00', $rows[3]['event_date_time']);
    }

    public function test_what_is_over_out_of_range_cancelled_or_private_is_left_out_and_counted(): void
    {
        $result = $this->read($this->feed(
            "UID:yesterday\nSUMMARY:Yesterday\nDTSTART:20261009T230000Z",
            "UID:this-morning\nSUMMARY:This morning\nDTSTART;TZID=America/New_York:20261010T090000",
            "UID:ongoing\nSUMMARY:Still on\nDTSTART;VALUE=DATE:20261008\nDTEND;VALUE=DATE:20261012",
            "UID:far\nSUMMARY:Too far ahead\nDTSTART;VALUE=DATE:20280101",
            "UID:ended\nSUMMARY:Ended series\nDTSTART;TZID=America/New_York:20260105T180000\nRRULE:FREQ=WEEKLY;UNTIL=20260301T000000Z",
            "UID:off\nSUMMARY:Called off\nDTSTART:20261020T230000Z\nSTATUS:CANCELLED",
            "UID:mine\nSUMMARY:Dentist\nDTSTART:20261020T230000Z\nCLASS:PRIVATE",
            "UID:broken\nSUMMARY:No start",
        ));

        // Earlier today still counts as today; a multi-day entry that has begun is still going.
        $this->assertSame(['Still on', 'This morning'], array_column($result['rows'], 'event_name'));
        $this->assertSame(['past' => 3, 'cancelled' => 1, 'private' => 1, 'unreadable' => 1], $result['skipped']);
    }

    public function test_where_what_and_the_rest_are_read_off_the_entry(): void
    {
        $rows = $this->rows($this->feed(
            "UID:a\nSUMMARY:Named place\nDTSTART:20261020T230000Z\nLOCATION:The Blue Room\\, 12 Main St\\, Austin\n"
                ."URL:https://example.org/tickets\nCATEGORIES:Music,Jazz\nDESCRIPTION:<p>Doors at <b>seven</b>.</p>\n"
                .'ATTACH;FMTTYPE=image/jpeg:https://example.org/poster.jpg',
            "UID:b\nSUMMARY:Address only\nDTSTART:20261021T230000Z\nLOCATION:12 Main St\\, Austin\nDESCRIPTION:Line one\\nLine two",
            "UID:c\nSUMMARY:Online\nDTSTART:20261022T230000Z\nLOCATION:https://example.org/stream",
            "UID:d\nSUMMARY:Just a name\nDTSTART:20261023T230000Z\nLOCATION:Town Hall\nATTACH:https://example.org/minutes.pdf",
            "SUMMARY:No uid\nDTSTART:20261024T230000Z",
        ));

        $this->assertSame('The Blue Room', $rows[0]['venue_name']);
        $this->assertSame('12 Main St, Austin', $rows[0]['event_address']);
        $this->assertSame('https://example.org/tickets', $rows[0]['registration_url']);
        $this->assertSame('Music', $rows[0]['category_name']);
        $this->assertSame('Doors at **seven**.', $rows[0]['event_details']);
        $this->assertSame('https://example.org/poster.jpg', $rows[0]['image_url']);
        $this->assertSame('a', $rows[0]['source_uid']);

        $this->assertSame('', $rows[1]['venue_name']);
        $this->assertSame('12 Main St, Austin', $rows[1]['event_address']);
        $this->assertSame("Line one\nLine two", $rows[1]['event_details']);

        $this->assertSame('', $rows[2]['venue_name']);
        $this->assertSame('https://example.org/stream', $rows[2]['registration_url']);

        $this->assertSame('Town Hall', $rows[3]['venue_name']);
        $this->assertSame('', $rows[3]['event_address']);
        // A PDF is not a picture.
        $this->assertNull($rows[3]['image_url']);

        $this->assertSame('No uid', $rows[4]['event_name']);
        $this->assertNotSame('', $rows[4]['source_uid']);
    }

    public function test_rows_come_back_in_the_order_they_next_happen(): void
    {
        $rows = $this->rows($this->feed(
            "UID:later\nSUMMARY:Later\nDTSTART:20261120T230000Z",
            "UID:weekly\nSUMMARY:Weekly\nDTSTART;TZID=America/New_York:20260105T180000\nRRULE:FREQ=WEEKLY",
            "UID:sooner\nSUMMARY:Sooner\nDTSTART:20261011T230000Z",
        ));

        // The weekly one started in January and is next on Monday the 12th.
        $this->assertSame(['Sooner', 'Weekly', 'Later'], array_column($rows, 'event_name'));
    }

    public function test_text_that_is_not_a_calendar_is_refused(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        IcsImportUtils::read('<html><body>Our events</body></html>', self::ZONE, false);
    }

    public function test_a_series_that_began_years_ago_is_still_read(): void
    {
        // The calendar library stops counting at 3,500 dates and throws. A daily class since
        // 2012 is that many dates before it reaches today, and used to vanish as "unreadable".
        $read = $this->read($this->feed(
            "UID:daily\nSUMMARY:Open every day\nDTSTART;TZID=America/New_York:20120102T090000\nDTEND;TZID=America/New_York:20120102T100000\nRRULE:FREQ=DAILY",
            "UID:weekdays\nSUMMARY:Weekday class\nDTSTART;TZID=America/New_York:20100104T180000\nRRULE:FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR",
        ));

        $this->assertSame(['Open every day', 'Weekday class'], array_column($read['rows'], 'event_name'));
        $this->assertSame('daily', $read['rows'][0]['recurrence']['frequency']);
        $this->assertSame('2026-10-10 09:00', $read['rows'][0]['sort_at']);
        $this->assertSame(0, $read['skipped']['unreadable']);
    }

    public function test_a_rule_that_is_not_about_days_is_left_out_and_counted(): void
    {
        $read = $this->read($this->feed(
            "UID:tick\nSUMMARY:Every second\nDTSTART:20261001T000000Z\nRRULE:FREQ=SECONDLY",
            "UID:hour\nSUMMARY:Every hour\nDTSTART:20261001T000000Z\nRRULE:FREQ=HOURLY",
            "UID:ok\nSUMMARY:A real event\nDTSTART;TZID=America/New_York:20261020T190000",
        ));

        $this->assertSame(['A real event'], array_column($read['rows'], 'event_name'));
        $this->assertSame(2, $read['skipped']['unreadable']);
    }

    public function test_added_dates_do_not_replace_the_rule(): void
    {
        // The library follows RDATE instead of RRULE when an entry has both.
        $rows = $this->rows($this->feed(
            "UID:both\nSUMMARY:Mondays and one more\nDTSTART;TZID=America/New_York:20260105T180000\nRRULE:FREQ=WEEKLY;BYDAY=MO\nRDATE;TZID=America/New_York:20261015T180000",
        ));

        $this->assertCount(1, $rows);
        $this->assertSame('weekly', $rows[0]['recurrence']['frequency']);
        $this->assertSame(['2026-10-15'], $rows[0]['recurrence']['fields']['recurring_include_dates']);
        // Listed by the next Monday, not by the added Thursday.
        $this->assertSame('2026-10-12 18:00', $rows[0]['sort_at']);

        // Added dates and no rule: the start and each added date, as one listed series.
        $rows = $this->rows($this->feed(
            "UID:dates\nSUMMARY:Three nights\nDTSTART;TZID=America/New_York:20261013T200000\nDTEND;TZID=America/New_York:20261013T220000\nRDATE;TZID=America/New_York:20261020T200000,20261117T200000",
        ));
        $this->assertSame(['2026-10-13 20:00', '2026-10-20 20:00', '2026-11-17 20:00'], array_column($rows, 'event_date_time'));
        $this->assertSame([2.0, 2.0, 2.0], array_column($rows, 'event_duration'));
        $this->assertSame(3, $rows[0]['series']['count']);

        // The same when the start itself is over.
        $rows = $this->rows($this->feed(
            "UID:late\nSUMMARY:Later dates\nDTSTART;TZID=America/New_York:20260901T200000\nRDATE;TZID=America/New_York:20261020T200000,20261117T200000",
        ));
        $this->assertSame(['2026-10-20 20:00', '2026-11-17 20:00'], array_column($rows, 'event_date_time'));
    }

    public function test_a_rule_is_only_repeated_when_its_clock_time_holds_all_year(): void
    {
        // 23:00 UTC every Tuesday is 6 PM in New York in winter and 7 PM in summer. One repeating
        // event has one clock time, so this is listed by date instead of saved an hour wrong.
        $rows = $this->rows($this->feed(
            "UID:utc\nSUMMARY:Anchored in UTC\nDTSTART:20260106T230000Z\nRRULE:FREQ=WEEKLY",
        ));
        $this->assertNull($rows[0]['recurrence']);
        $this->assertNotNull($rows[0]['series']);
        $this->assertSame('2026-10-13 19:00', $rows[0]['event_date_time']);
        $this->assertContains('2026-11-03 18:00', array_column($rows, 'event_date_time'));

        // Another zone with other clock-change dates, on a venue schedule that converts.
        $rows = $this->rows($this->feed(
            "UID:london\nSUMMARY:From London\nDTSTART;TZID=Europe/London:20260105T180000\nRRULE:FREQ=WEEKLY",
        ));
        $this->assertNull($rows[0]['recurrence']);

        // The schedule's own zone, a floating time, and a kept local clock all hold.
        foreach ([
            ["UID:own\nSUMMARY:Own zone\nDTSTART;TZID=America/New_York:20260105T180000\nRRULE:FREQ=WEEKLY", false],
            ["UID:float\nSUMMARY:No zone\nDTSTART:20260105T180000\nRRULE:FREQ=WEEKLY", false],
            ["UID:kept\nSUMMARY:Kept clock\nDTSTART;TZID=Europe/London:20260105T180000\nRRULE:FREQ=WEEKLY", true],
        ] as [$event, $keepLocalClock]) {
            $rows = $this->rows($this->feed($event), $keepLocalClock);
            $this->assertCount(1, $rows, $event);
            $this->assertSame('weekly', $rows[0]['recurrence']['frequency'], $event);
        }
    }

    public function test_a_rule_whose_days_are_named_on_another_clock_is_not_repeated_on_the_wrong_days(): void
    {
        // Monday to Friday at 08:00 in Tokyo, written in UTC: the start is Sunday 23:00 and the
        // days named are Sunday to Thursday. Read onto Tokyo's clock the start is a Monday,
        // which is one of the days named, so nothing looked wrong and it was saved as one
        // repeating event on Sunday to Thursday.
        $rows = $this->rowsIn('Asia/Tokyo', "UID:class\nSUMMARY:Morning class\nDTSTART:20260104T230000Z\nRRULE:FREQ=WEEKLY;BYDAY=SU,MO,TU,WE,TH");
        $this->assertSame(['Mon 2026-10-12 08:00 listed', 'Tue 2026-10-13 08:00 listed', 'Wed 2026-10-14 08:00 listed'], $this->shape($rows));
        $this->assertSame(['Mon', 'Tue', 'Wed', 'Thu', 'Fri'], array_values(array_unique(array_map(fn ($row) => Carbon::parse($row['event_date_time'])->format('D'), $rows))));

        // The other way round: Thursday to Saturday at 21:00 in Mexico City is 03:00 the next
        // day in UTC, and a feed whose first show was a Friday starts on its Saturday.
        $rows = $this->rowsIn('America/Mexico_City', "UID:shows\nSUMMARY:Show\nDTSTART:20260905T030000Z\nRRULE:FREQ=WEEKLY;BYDAY=FR,SA,SU");
        $this->assertSame(['Sat 2026-10-10 21:00 listed', 'Thu 2026-10-15 21:00 listed', 'Fri 2026-10-16 21:00 listed'], $this->shape($rows));

        // "The 1st of the month" at 02:00 UTC is the last day of the month before in Phoenix,
        // which is the 31st, the 30th or the 28th: not a date a monthly event can be given.
        $rows = $this->rowsIn('America/Phoenix', "UID:first\nSUMMARY:Monthly\nDTSTART:20260101T020000Z\nRRULE:FREQ=MONTHLY");
        $this->assertSame(['Sat 2026-10-31 19:00 listed', 'Mon 2026-11-30 19:00 listed', 'Thu 2026-12-31 19:00 listed'], $this->shape($rows));
        // The 28th seen from the east is the 29th, which February does not always have; and
        // the 31st is the 1st of months that follow a month of 31 days only.
        $this->assertStringEndsWith('listed', $this->shape($this->rowsIn('Asia/Tokyo', "UID:m28\nSUMMARY:Monthly\nDTSTART:20260128T230000Z\nRRULE:FREQ=MONTHLY"))[0]);
        $this->assertStringEndsWith('listed', $this->shape($this->rowsIn('Asia/Tokyo', "UID:m31\nSUMMARY:Monthly\nDTSTART:20260131T230000Z\nRRULE:FREQ=MONTHLY"))[0]);
        // 1 March seen from the west is 28 February this year and the 29th in a leap year. Both
        // days are "up to the 28th", so it is the month that gives this one away.
        $this->assertSame(
            ['Sat 2026-10-31 19:00 listed', 'Mon 2026-11-30 19:00 listed', 'Thu 2026-12-31 19:00 listed'],
            $this->shape($this->rowsIn('America/Phoenix', "UID:m1\nSUMMARY:Monthly\nDTSTART:20260301T020000Z\nRRULE:FREQ=MONTHLY"))
        );
        // A yearly date beside the end of February is a different date in a leap year.
        $this->assertSame(['Mon 2027-03-01 08:00 once'], $this->shape($this->rowsIn('Asia/Tokyo', "UID:y\nSUMMARY:Yearly\nDTSTART:20200228T230000Z\nRRULE:FREQ=YEARLY")));
        $this->assertSame(['Sun 2027-02-28 19:00 once'], $this->shape($this->rowsIn('America/Phoenix', "UID:y\nSUMMARY:Yearly\nDTSTART:20210301T020000Z\nRRULE:FREQ=YEARLY")));
    }

    public function test_a_rule_that_moves_whole_onto_another_day_is_still_one_repeating_event(): void
    {
        // Nothing here names a day, so the whole rule is simply a day over, and stays a rule.
        foreach ([
            ['America/Phoenix', "UID:w\nSUMMARY:Weekly\nDTSTART:20260107T020000Z\nRRULE:FREQ=WEEKLY", 'Tue 2026-01-06 19:00 weekly[Tu]'],
            ['America/Phoenix', "UID:f\nSUMMARY:Fortnightly\nDTSTART:20260107T020000Z\nRRULE:FREQ=WEEKLY;INTERVAL=2", 'Tue 2026-01-06 19:00 every_n_weeks[Tu]'],
            ['Asia/Tokyo', "UID:d\nSUMMARY:Daily\nDTSTART:20260104T230000Z\nRRULE:FREQ=DAILY", 'Mon 2026-01-05 08:00 daily[]'],
            // The 15th at 02:00 UTC is the 14th in Phoenix, every month.
            ['America/Phoenix', "UID:m\nSUMMARY:Monthly\nDTSTART:20260115T020000Z\nRRULE:FREQ=MONTHLY", 'Wed 2026-01-14 19:00 monthly_date[]'],
            // New Year's Eve at 23:30 UTC is half past midnight on the 1st in Berlin, every year.
            ['Europe/Berlin', "UID:y\nSUMMARY:Yearly\nDTSTART:20201231T233000Z\nRRULE:FREQ=YEARLY", 'Fri 2021-01-01 00:30 yearly[]'],
            // Beside the leap day but not across it: 27 February is always the 28th in Tokyo,
            // and 1 March always the 2nd.
            ['Asia/Tokyo', "UID:y27\nSUMMARY:Yearly\nDTSTART:20200227T230000Z\nRRULE:FREQ=YEARLY", 'Fri 2020-02-28 08:00 yearly[]'],
            ['Asia/Tokyo', "UID:y01\nSUMMARY:Yearly\nDTSTART:20210301T230000Z\nRRULE:FREQ=YEARLY", 'Tue 2021-03-02 08:00 yearly[]'],
        ] as [$zone, $event, $expected]) {
            $this->assertSame([$expected], $this->shape($this->rowsIn($zone, $event)), $event);
        }

        // And a rule that names its days is still one when the day does not move: on the
        // schedule's own clock, on a kept local clock, and across zones that share a day.
        foreach ([
            ['Asia/Tokyo', "UID:own\nSUMMARY:Own\nDTSTART;TZID=Asia/Tokyo:20260105T080000\nRRULE:FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR", false, 'Mon 2026-01-05 08:00 weekly[MoTuWeThFr]'],
            ['America/New_York', "UID:kept\nSUMMARY:Kept\nDTSTART;TZID=America/Los_Angeles:20260906T220000\nRRULE:FREQ=WEEKLY;BYDAY=SU", true, 'Sun 2026-09-06 22:00 weekly[Su]'],
            ['America/Phoenix', "UID:same\nSUMMARY:Same day\nDTSTART:20260105T200000Z\nRRULE:FREQ=WEEKLY;BYDAY=MO,WE", false, 'Mon 2026-01-05 13:00 weekly[MoWe]'],
        ] as [$zone, $event, $keepLocalClock, $expected]) {
            $this->assertSame([$expected], $this->shape($this->rowsIn($zone, $event, $keepLocalClock)), $event);
        }
    }

    public function test_a_removed_or_moved_date_written_in_utc_is_read_on_the_series_own_clock(): void
    {
        // On a touring schedule the series keeps its own clock. A removed date written in UTC
        // (midnight UTC on the 13th is 8 PM on the 12th in New York) is still the 12th.
        $rows = $this->rows($this->feed(
            "UID:show\nSUMMARY:Monday show\nDTSTART;TZID=America/Chicago:20260907T190000\nRRULE:FREQ=WEEKLY;BYDAY=MO\nEXDATE:20261013T000000Z",
        ), true);
        $this->assertSame(['2026-10-12'], $rows[0]['recurrence']['fields']['recurring_exclude_dates']);

        // And a moved date written in UTC lands at its Chicago time, not at UTC's.
        $rows = $this->rows($this->feed(
            "UID:jam\nSUMMARY:Jam\nDTSTART;TZID=America/Chicago:20261005T190000\nRRULE:FREQ=WEEKLY;BYDAY=MO;COUNT=3",
            "UID:jam\nSUMMARY:Jam (late)\nRECURRENCE-ID;TZID=America/Chicago:20261012T190000\nDTSTART:20261013T020000Z",
        ), true);
        $this->assertSame(['2026-10-12 21:00', '2026-10-19 19:00'], array_column($rows, 'event_date_time'));
        $this->assertSame(['America/Chicago', 'America/Chicago'], array_column($rows, 'local_time_zone'));
    }

    public function test_entries_that_share_an_id_are_each_read(): void
    {
        $read = $this->read($this->feed(
            "UID:same\nSUMMARY:Show A\nDTSTART;TZID=America/New_York:20261020T190000",
            "UID:same\nSUMMARY:Show B\nDTSTART;TZID=America/New_York:20261021T190000",
            "UID:same\nSUMMARY:Show C\nDTSTART;TZID=America/New_York:20261022T190000",
            // The same entry twice is one event: a feed that repeats itself, or a revision.
            "UID:twice\nSUMMARY:Once\nDTSTART;TZID=America/New_York:20261023T190000",
            "UID:twice\nSUMMARY:Once\nDTSTART;TZID=America/New_York:20261023T190000",
        ));

        $this->assertSame(['Show A', 'Show B', 'Show C', 'Once'], array_column($read['rows'], 'event_name'));
    }

    public function test_two_events_with_one_id_at_one_time_are_two_events(): void
    {
        // Two stages, one export that numbers nothing. By start alone the second was dropped,
        // and not counted as left out either.
        $read = $this->read($this->feed(
            "UID:event\nSUMMARY:Main stage: Band A\nDTSTART;TZID=America/New_York:20261101T200000",
            "UID:event\nSUMMARY:Room 2: DJ B\nDTSTART;TZID=America/New_York:20261101T200000",
            "UID:event\nSUMMARY:Main stage: Band A\nDTSTART;TZID=America/New_York:20261101T200000",
        ));

        $this->assertSame(['Main stage: Band A', 'Room 2: DJ B'], array_column($read['rows'], 'event_name'));
    }

    public function test_a_moved_date_finds_its_series_among_entries_that_share_an_id(): void
    {
        $oneOff = "SUMMARY:Quiz\nDTSTART;TZID=America/New_York:20261015T190000";
        $series = "SUMMARY:Jam night\nDTSTART;TZID=America/New_York:20261005T190000\nRRULE:FREQ=WEEKLY;BYDAY=MO;COUNT=4";
        $moved = "SUMMARY:Jam night\nRECURRENCE-ID;TZID=America/New_York:20261012T190000\nDTSTART;TZID=America/New_York:20261013T200000";
        $cancelled = "SUMMARY:Jam night\nRECURRENCE-ID;TZID=America/New_York:20261012T190000\nDTSTART;TZID=America/New_York:20261012T190000\nSTATUS:CANCELLED";

        // The change belongs to the entry that repeats. Left with the first entry of that id,
        // a one-off, the series stayed one repeating event with the date as it was.
        // An id of digits and one of letters: PHP keeps the first as an integer array key.
        foreach (['1', 'jam@example.com'] as $id) {
            foreach ([[$oneOff, $series], [$series, $oneOff]] as $order) {
                $with = fn (string $change) => array_map(fn ($event) => "UID:{$id}\n".$event, [...$order, $change]);

                $rows = $this->rows($this->feed(...$with($moved)));
                $this->assertSame(
                    ['2026-10-13 20:00 Jam night', '2026-10-15 19:00 Quiz', '2026-10-19 19:00 Jam night', '2026-10-26 19:00 Jam night'],
                    array_map(fn ($row) => $row['event_date_time'].' '.$row['event_name'], $rows),
                    "id {$id}"
                );

                $rows = $this->rows($this->feed(...$with($cancelled)));
                $this->assertSame(
                    ['2026-10-15 19:00 Quiz', '2026-10-19 19:00 Jam night', '2026-10-26 19:00 Jam night'],
                    array_map(fn ($row) => $row['event_date_time'].' '.$row['event_name'], $rows),
                    "id {$id}"
                );
            }
        }
    }

    public function test_a_zone_nobody_knows_is_read_as_the_schedules_own_clock(): void
    {
        // Exchange writes "Customized Time Zone" and the like. Reading it as the server's zone
        // put a 7 PM event at 3 PM.
        $rows = $this->rows($this->feed(
            "UID:odd\nSUMMARY:Odd zone\nDTSTART;TZID=Customized Time Zone:20261020T190000\nDTEND;TZID=Customized Time Zone:20261020T210000",
        ));

        $this->assertSame('2026-10-20 19:00', $rows[0]['event_date_time']);
        $this->assertSame(2.0, $rows[0]['event_duration']);
        $this->assertNull($rows[0]['local_time_zone']);
    }

    public function test_a_rule_with_no_frequency_is_one_event(): void
    {
        $rows = $this->rows($this->feed(
            "UID:bad\nSUMMARY:Broken rule\nDTSTART;TZID=America/New_York:20261020T190000\nRRULE:BYDAY=MO",
        ));

        $this->assertCount(1, $rows);
        $this->assertNull($rows[0]['series']);
        $this->assertNull($rows[0]['recurrence']);
    }

    public function test_a_long_feed_is_read_in_time_that_grows_with_its_length(): void
    {
        // Each repeating entry used to make the library search the whole feed for its id, so
        // 8,000 entries took 23 seconds. It is handed the entry instead.
        $events = [];
        for ($n = 0; $n < 6000; $n++) {
            $events[] = "UID:n{$n}\nSUMMARY:Entry {$n}\nDTSTART;TZID=America/New_York:20261020T190000\nRRULE:FREQ=WEEKLY";
        }
        $feed = $this->feed(...$events);

        $started = microtime(true);
        $read = $this->read($feed);
        $seconds = microtime(true) - $started;

        $this->assertCount(6000, $read['rows']);
        $this->assertLessThan(6.0, $seconds, 'Reading 6,000 repeating entries took '.round($seconds, 1).'s');
    }
}
