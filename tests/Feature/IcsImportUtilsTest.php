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
}
