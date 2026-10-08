<?php

namespace Tests\Unit;

use App\Services\Feeds\FeedTime;
use PHPUnit\Framework\TestCase;

/**
 * A time as a provider writes it, where nothing says how. The shapes such a field comes in are
 * read; anything else is refused, because a date read day-first that was written month-first is
 * an event on the wrong day with nothing to show it.
 */
class FeedTimeTest extends TestCase
{
    private const ZONE = 'Europe/Vienna';

    private function utc(mixed $value): ?string
    {
        return FeedTime::parse($value, self::ZONE)['at']?->utc()->format('Y-m-d H:i') ?? null;
    }

    public function test_a_time_with_no_zone_is_read_on_the_feeds_clock(): void
    {
        // May: Vienna is two hours ahead of UTC.
        $this->assertSame('2026-05-09 15:30', $this->utc('2026-05-09 17:30:00'));
        $this->assertSame('2026-05-09 15:30', $this->utc('2026-05-09 17:30'));
        $this->assertSame('2026-05-09 15:30', $this->utc('2026-05-09T17:30:00'));
        $this->assertSame('2026-05-09 15:30', $this->utc('09.05.2026 17:30'));
        $this->assertSame('2026-05-09 15:30', $this->utc('9.5.2026, 17:30:00'));
        // January: one hour.
        $this->assertSame('2026-01-09 16:30', $this->utc('2026-01-09 17:30:00'));

        $this->assertFalse(FeedTime::parse('2026-05-09 17:30:00', self::ZONE)['states_zone']);
        $this->assertFalse(FeedTime::parse('2026-05-09 17:30:00', self::ZONE)['all_day']);
    }

    public function test_a_time_that_says_where_or_when_is_taken_at_its_word(): void
    {
        $offset = FeedTime::parse('2026-05-09T17:30:00+02:00', self::ZONE);
        $this->assertSame('2026-05-09 15:30', $offset['at']->utc()->format('Y-m-d H:i'));
        $this->assertTrue($offset['states_zone']);

        $newYork = FeedTime::parse('2026-05-09T17:30:00.000-0400', self::ZONE);
        $this->assertSame('2026-05-09 21:30', $newYork['at']->utc()->format('Y-m-d H:i'));
        $this->assertTrue($newYork['states_zone']);

        // "Z" says when, not where.
        $zulu = FeedTime::parse('2026-05-09T17:30:00Z', self::ZONE);
        $this->assertSame('2026-05-09 17:30', $zulu['at']->utc()->format('Y-m-d H:i'));
        $this->assertFalse($zulu['states_zone']);

        // Seconds and milliseconds since 1970.
        $this->assertSame('2026-05-09 17:30', $this->utc(1778347800));
        $this->assertSame('2026-05-09 17:30', $this->utc('1778347800000'));
    }

    public function test_a_day_with_no_time_is_a_whole_day_there(): void
    {
        foreach (['2026-05-09', '09.05.2026', '9.5.2026'] as $written) {
            $day = FeedTime::parse($written, self::ZONE);

            $this->assertTrue($day['all_day'], $written);
            $this->assertSame('2026-05-09 00:00', $day['at']->format('Y-m-d H:i'), $written);
            $this->assertSame(self::ZONE, $day['at']->getTimezone()->getName(), $written);
        }
    }

    public function test_what_cannot_be_read_for_certain_is_not_read(): void
    {
        foreach ([
            null, '', '   ', [], true,
            'tomorrow', 'next Friday', 'Sa, 9. Mai 2026, 17:30 Uhr', '17:30',
            // Day first or month first? Not ours to guess.
            '05/09/2026 17:30', '09/05/2026',
            // Not dates at all.
            '2026-13-45', '2026-02-30 10:00', '32.01.2026', '09.05.2026 25:00', '2026-05-09 17:61',
            '123456', '2026',
        ] as $value) {
            $this->assertNull(FeedTime::parse($value, self::ZONE), var_export($value, true));
        }
    }

    /**
     * A reader's row is a wall-clock time on the schedule's clock, whichever kind of time it
     * was at the source: the readers put it there (ImportedTime::onClock()).
     */
    public function test_a_row_starts_at_its_time_on_the_schedules_clock(): void
    {
        $row = ['event_date_time' => '2026-11-14 19:30'];

        $this->assertSame('2026-11-14 18:30:00', FeedTime::startOf($row, 'Europe/Vienna')->format('Y-m-d H:i:s'));
        $this->assertSame('2026-11-14 19:30:00', FeedTime::startOf($row, 'Europe/London')->format('Y-m-d H:i:s'));
        $this->assertSame('UTC', FeedTime::startOf($row, 'Europe/Vienna')->getTimezone()->getName());
        // An event's own clock, kept, and a whole day read the same way: they are on that clock too.
        $this->assertSame('2026-11-13 23:00:00', FeedTime::startOf(['event_date_time' => '2026-11-14 00:00', 'is_all_day' => true, 'local_time_zone' => 'America/New_York'], 'Europe/Vienna')->format('Y-m-d H:i:s'));
    }

    /**
     * What ImportedTime::onClock() is for. "Times are read as" is the clock of a source's unzoned
     * times; the schedule's zone is the clock everything is placed on. Deciding to keep an
     * event's own clock against the first of those moved every entry that names its zone when
     * somebody changed the setting.
     */
    public function test_times_are_placed_on_the_schedules_clock_whatever_clock_unzoned_times_are_read_on(): void
    {
        $vienna = new \DateTimeImmutable('2026-11-14 19:30', new \DateTimeZone('Europe/Vienna'));
        $place = fn (string $readOn, bool $statesZone, bool $keep) => \App\Utils\ImportedTime::onClock('Europe/Vienna', fn () => \App\Utils\ImportedTime::place($vienna, $statesZone, $readOn, $keep));

        // The schedule is in Vienna. An entry that says "19:30, Vienna" reads 19:30 here, and
        // names no other zone, whatever unzoned times are read as.
        foreach (['Europe/Vienna', 'Europe/London', 'Pacific/Auckland'] as $readOn) {
            [$placed, $other] = $place($readOn, true, true);
            $this->assertSame(['2026-11-14 19:30', null], [$placed->format('Y-m-d H:i'), $other], $readOn);
        }

        // An unzoned 19:30 read as London's is 20:30 on the schedule.
        $london = new \DateTimeImmutable('2026-11-14 19:30', new \DateTimeZone('Europe/London'));
        [$placed] = \App\Utils\ImportedTime::onClock('Europe/Vienna', fn () => \App\Utils\ImportedTime::place($london, false, 'Europe/London', true));
        $this->assertSame('2026-11-14 20:30', $placed->format('Y-m-d H:i'));

        // Outside a feed's read nothing changes: the zone handed in is the clock.
        [$placed, $other] = \App\Utils\ImportedTime::place($vienna, true, 'Europe/London', true);
        $this->assertSame(['2026-11-14 19:30', 'Europe/Vienna'], [$placed->format('Y-m-d H:i'), $other]);
        [$placed] = \App\Utils\ImportedTime::place($vienna, true, 'Europe/London', false);
        $this->assertSame('2026-11-14 18:30', $placed->format('Y-m-d H:i'));
    }
}
