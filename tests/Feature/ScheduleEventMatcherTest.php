<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Services\ScheduleEventMatcher;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Which event on the schedule a row read from somewhere else already is.
 *
 * The link import only ever asked whether, and its own tests hold that (LinkImportParseTest).
 * A feed asks which: it stands beside the event the owner already has instead of adding a
 * second, and has to know which one that is.
 */
class ScheduleEventMatcherTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const ZONE = 'America/New_York';

    private function utc(string $local): string
    {
        return Carbon::parse($local, self::ZONE)->utc()->format('Y-m-d H:i:s');
    }

    private function schedule(): Role
    {
        return $this->createRole($this->createOwner(), 'talent', ['timezone' => self::ZONE]);
    }

    public function test_it_says_which_event_a_row_already_is(): void
    {
        $role = $this->schedule();
        $single = $this->createEvent($role, ['creator_role_id' => $role->id, 'name' => 'Jazz Night', 'starts_at' => $this->utc('2026-10-20 20:00')]);
        // The same name at the same time, added later: the older one is the one that is matched.
        $this->createEvent($role, ['creator_role_id' => $role->id, 'name' => 'Jazz Night', 'starts_at' => $this->utc('2026-10-20 20:00')]);
        $weekly = $this->createEvent($role, [
            'creator_role_id' => $role->id,
            'name' => 'Monday Class',
            'starts_at' => $this->utc('2026-09-07 18:00'),
            'days_of_week' => '0100000',
            'recurring_frequency' => 'weekly',
        ]);

        $matcher = new ScheduleEventMatcher($role, self::ZONE);

        // The plain case, however the name is cased and spaced.
        $this->assertSame($single->id, $matcher->match(['event_name' => '  jazz NIGHT ', 'event_date_time' => '2026-10-20 20:00']));
        $this->assertNull($matcher->match(['event_name' => 'Jazz Night', 'event_date_time' => '2026-10-20 21:00']));
        $this->assertNull($matcher->match(['event_name' => 'Jazz Afternoon', 'event_date_time' => '2026-10-20 20:00']));

        // A repeating row whose NEXT date is an event of that name.
        $this->assertSame($single->id, $matcher->match([
            'event_name' => 'Jazz Night',
            'event_date_time' => '2026-09-01 20:00',
            'sort_at' => '2026-10-20 20:00',
            'recurrence' => ['frequency' => 'weekly'],
        ]));
        $this->assertNull($matcher->match([
            'event_name' => 'Jazz Night',
            'event_date_time' => '2026-09-01 20:00',
            'sort_at' => '2026-10-27 20:00',
            'recurrence' => ['frequency' => 'weekly'],
        ]));

        // A dated row that a repeating event of that name covers: that day, at that time.
        $this->assertSame($weekly->id, $matcher->match(['event_name' => 'Monday Class', 'event_date_time' => '2026-10-19 18:00']));
        $this->assertNull($matcher->match(['event_name' => 'Monday Class', 'event_date_time' => '2026-10-20 18:00']), 'a Tuesday');
        $this->assertNull($matcher->match(['event_name' => 'Monday Class', 'event_date_time' => '2026-10-19 19:00']), 'another time');
        $this->assertNull($matcher->match(['event_name' => 'Monday Class', 'event_date_time' => '2026-08-31 18:00']), 'before it began');
    }

    /** An event the schedule lists and somebody else owns is theirs: it is never what a row "already is". */
    public function test_only_the_schedules_own_events_are_matched(): void
    {
        $role = $this->schedule();
        $other = $this->schedule();
        $theirs = $this->createEvent($other, ['creator_role_id' => $other->id, 'name' => 'Elsewhere', 'starts_at' => $this->utc('2026-10-20 20:00')]);
        $theirs->roles()->attach($role->id, ['is_accepted' => true]);

        $row = ['event_name' => 'Elsewhere', 'event_date_time' => '2026-10-20 20:00'];

        // Nothing of its own at all: no match, and nothing read that was not needed.
        $this->assertNull((new ScheduleEventMatcher($role, self::ZONE))->match($row));
        $this->assertSame($theirs->id, (new ScheduleEventMatcher($other, self::ZONE))->match($row));

        // With an event of its own, still not theirs.
        $this->createEvent($role, ['creator_role_id' => $role->id, 'name' => 'Ours', 'starts_at' => $this->utc('2026-10-21 20:00')]);
        $this->assertNull((new ScheduleEventMatcher($role, self::ZONE))->match($row));
    }

    /** A row's time is a wall-clock time in the zone the matcher was built with. */
    public function test_a_rows_time_is_read_in_the_zone_it_was_given(): void
    {
        $role = $this->schedule();
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'name' => 'Jazz Night', 'starts_at' => $this->utc('2026-10-20 20:00')]);
        $row = ['event_name' => 'Jazz Night', 'event_date_time' => '2026-10-20 20:00'];

        $this->assertSame($event->id, (new ScheduleEventMatcher($role, self::ZONE))->match($row));
        $this->assertNull((new ScheduleEventMatcher($role, 'Europe/Vienna'))->match($row));
        $this->assertSame($event->id, (new ScheduleEventMatcher($role, 'Europe/Vienna'))->match(['event_date_time' => '2026-10-21 02:00'] + $row));
    }
}
