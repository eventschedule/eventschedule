<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Whether an event read from somewhere else is one the schedule already has, and which.
 *
 * Among the events the schedule created itself. The same name at the same start is the plain
 * case. The other two are what reading a source a second time runs into, because a series does
 * not always come back in the shape it was saved in: once somebody moves one of its dates the
 * source lists it date by date, and when that date has passed it is a rule again.
 *
 *  - A dated row is already there when a repeating event of that name falls on that day at that
 *    time. Otherwise twelve single events were offered on top of the repeating one.
 *  - A repeating row is already there when an event of that name exists at its next date.
 *
 * The link import asks only whether (it leaves such rows out of its preview). A feed asks which,
 * so that it can stand beside the event the owner already has instead of adding a second.
 *
 * Built once for a run of rows: the schedule's events are read when it is made, and its
 * repeating ones only if a row gets that far.
 */
class ScheduleEventMatcher
{
    /** @var array<string, int> */
    private array $exact = [];

    /** @var Collection<string, Collection<int, Event>>|null */
    private ?Collection $repeating = null;

    public function __construct(private Role $role, private string $timezone)
    {
        $events = DB::table('events')
            ->where('creator_role_id', $role->id)
            ->whereNotNull('starts_at')
            ->orderBy('id')
            ->get(['id', 'name', 'starts_at']);

        foreach ($events as $event) {
            // Two events of one name at one time: the older one is the one that is matched.
            $this->exact[$this->key($event->name, Carbon::parse($event->starts_at)->format('Y-m-d H:i'))] ??= (int) $event->id;
        }
    }

    /**
     * The id of the event this row already is on the schedule, or null.
     *
     * @param  array{event_name?: mixed, event_date_time: string, sort_at?: string, recurrence?: mixed}  $row  A reader's row: its time is a wall-clock time in the zone this was built with.
     * @param  ?string  $zone  The zone this row's time is on, where a caller's rows are not all on one clock (a feed: FeedTime::zoneOf()).
     */
    public function match(array $row, ?string $zone = null): ?int
    {
        if (! $this->exact) {
            return null;
        }

        $zone ??= $this->timezone;

        $name = $row['event_name'] ?? '';
        // The row's time is a wall-clock time the save reads in the schedule's zone.
        $start = Carbon::parse($row['event_date_time'], $zone);

        if ($id = $this->exact[$this->key($name, $start->copy()->utc()->format('Y-m-d H:i'))] ?? null) {
            return $id;
        }

        if (! empty($row['recurrence'])) {
            $next = Carbon::parse($row['sort_at'] ?? $row['event_date_time'], $zone)->utc()->format('Y-m-d H:i');

            return $this->exact[$this->key($name, $next)] ?? null;
        }

        foreach ($this->repeating()[mb_strtolower(trim((string) $name))] ?? [] as $event) {
            if (Carbon::parse($event->starts_at, 'UTC')->setTimezone($zone)->format('H:i') === $start->format('H:i')
                && $event->matchesDate($start->format('Y-m-d'), $zone)) {
                return (int) $event->id;
            }
        }

        return null;
    }

    private function key(mixed $name, string $utc): string
    {
        return mb_strtolower(trim((string) $name)).'|'.$utc;
    }

    /** @return Collection<string, Collection<int, Event>> */
    private function repeating(): Collection
    {
        // Whole models, not a narrowed select: Event::matchesDate() reads a dozen columns.
        return $this->repeating ??= Event::where('creator_role_id', $this->role->id)
            ->whereNotNull('starts_at')
            ->whereNotNull('days_of_week')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (Event $event) => mb_strtolower(trim((string) $event->name)));
    }
}
