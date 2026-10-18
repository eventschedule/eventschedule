<?php

namespace App\Services;

use App\Models\CalendarExposure;
use App\Models\Event;
use App\Models\Role;
use App\Utils\CalendarPrivacy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;

/**
 * Finds schedules that are publishing somebody's private calendar, for the operator to look at
 * (/admin/private-calendars). It never acts: the page's steps do, one press each.
 *
 * A calendar import once published a person's whole work calendar: Teams dial-in codes, Zoom
 * links with the meeting's password, an email thread about a child's doctor. The imports hold
 * such entries back now (CalendarArrival), but what was published before them, what an older
 * import made with no source recorded, and what a feed or a one-time import brought in are only
 * found by looking. So each candidate event's text is read once, when it changes, and what
 * CalendarPrivacy finds is written to calendar_exposures: the list and the admin alert read that
 * table, never thousands of descriptions on every page view.
 *
 * The sweep walks the events a window of ids at a time from a cursor kept in the cache (a Setting
 * would throw away every request's cached settings each time it moved), and starts over past the
 * last id, so an event edited later is read again on the next pass. A new version of the patterns
 * is a new cursor key: its first run re-checks what is already flagged before anything else.
 */
class CalendarExposureWatch
{
    /** How many event ids one run of the sweep reads. */
    public const WINDOW = 2000;

    /** Where the events a calendar can bring in came from (Event::IMPORT_*). */
    public const SOURCES = [Event::IMPORT_GOOGLE, Event::IMPORT_MICROSOFT, Event::IMPORT_CALDAV, Event::IMPORT_ICS, Event::IMPORT_FEED];

    /**
     * import_source arrived on 2026-10-04 with no backfill: an event a sync read in before then
     * carries none and is known only by its mapping row. One made after then with none is one made
     * here and pushed out, which is the owner's own words.
     */
    public const UNLABELLED_BEFORE = '2026-10-05 00:00:00';

    private const CURSOR = 'calendar_exposure.cursor.v1';

    private const COUNT_KEY = 'calendar_exposure.listed_count';

    /** How fresh the alert's count is, then how long it may be served while it is worked out again. */
    private const FRESH_SECONDS = 60;

    private const STALE_SECONDS = 600;

    private static bool $tablesReady = false;

    public static function tablesReady(): bool
    {
        return self::$tablesReady = self::$tablesReady
            || (Schema::hasTable('calendar_exposures') && Schema::hasTable('calendar_exposure_reviews'));
    }

    /**
     * The events the sweep reads: a schedule's own, that a calendar brought in. Drafts too: a
     * hidden event keeps its row, so putting it back and marking the schedule reviewed does not
     * list it again as a new match, and a held draft the owner publishes is listed as it was found.
     * What is listed is only what is on a page (listed()).
     */
    public static function candidates(): Builder
    {
        return Event::query()
            ->whereNotNull('events.creator_role_id')
            ->where(function ($q) {
                $q->whereIn('events.import_source', self::SOURCES)
                    ->orWhere(fn ($unlabelled) => $unlabelled
                        ->whereNull('events.import_source')
                        ->where('events.created_at', '<', self::UNLABELLED_BEFORE)
                        ->where(fn ($mapped) => $mapped
                            ->whereExists(fn ($s) => $s->selectRaw('1')->from('calendar_syncs')->whereColumn('calendar_syncs.event_id', 'events.id'))
                            ->orWhereExists(fn ($s) => $s->selectRaw('1')->from('microsoft_calendar_syncs')->whereColumn('microsoft_calendar_syncs.event_id', 'events.id'))
                            ->orWhereExists(fn ($s) => $s->selectRaw('1')->from('event_role')->whereColumn('event_role.event_id', 'events.id')->whereNotNull('event_role.caldav_event_uid'))));
            });
    }

    /**
     * Reads one event again and writes what it found, or removes its row when there is nothing
     * (any more) to find. For a caller that has just written the event's text; the sweep reaches
     * it otherwise. $location is the calendar's location for it, when the caller has it.
     */
    public static function record(Event $event, ?string $location = null): ?CalendarExposure
    {
        if (! self::tablesReady() || ! $event->exists) {
            return null;
        }

        if (! self::candidates()->whereKey($event->id)->exists()) {
            self::forget([$event->id]);

            return null;
        }

        return self::write($event, $location ?? self::locationOf($event));
    }

    /**
     * One run: the next window of event ids. Returns how many events it read and how many rows it
     * wrote or removed.
     *
     * @return array{read: int, flagged: int, removed: int}
     */
    public static function sweep(): array
    {
        $done = ['read' => 0, 'flagged' => 0, 'removed' => 0];
        if (! self::tablesReady()) {
            return $done;
        }

        $from = Cache::get(self::CURSOR);
        if ($from === null) {
            // A new version of the patterns, or a cleared cache: what is flagged now is read again
            // first, so a pattern that was dropped takes its rows with it at once.
            CalendarExposure::query()->orderBy('id')->chunkById(500, function ($rows) use (&$done) {
                foreach ($rows as $row) {
                    $event = Event::find($row->event_id);
                    $kept = $event ? self::record($event) : null;
                    if (! $kept) {
                        $row->delete();
                        $done['removed']++;
                    }
                }
            });
            $from = 0;
        }

        $from = (int) $from;
        $to = $from + self::WINDOW;

        $events = self::candidates()
            ->where('events.id', '>', $from)
            ->where('events.id', '<=', $to)
            ->with(['roles' => fn ($q) => $q->where('roles.type', 'venue')->whereNull('roles.email')])
            ->get();

        $flagged = [];
        foreach ($events as $event) {
            $done['read']++;
            if (self::write($event, self::locationOf($event))) {
                $flagged[] = $event->id;
                $done['flagged']++;
            }
        }

        // What this window had flagged and no longer does: fixed, deleted, or not a candidate any
        // more.
        $done['removed'] += CalendarExposure::where('event_id', '>', $from)
            ->where('event_id', '<=', $to)
            ->when($flagged, fn ($q) => $q->whereNotIn('event_id', $flagged))
            ->delete();

        Cache::forever(self::CURSOR, $to >= (int) Event::max('id') ? 0 : $to);

        if ($done['flagged'] || $done['removed']) {
            self::flushCount();
        }

        return $done;
    }

    /**
     * Rows the operator should look at: an event still on the schedule's public page (Unlisted
     * included, since its address is public), the schedule's own, the schedule up, its owner not
     * blocked and not the demo, and the match newer than the schedule's last review.
     */
    public static function listed(): QueryBuilder
    {
        return DB::table('calendar_exposures')
            ->join('events', 'events.id', '=', 'calendar_exposures.event_id')
            ->join('roles', 'roles.id', '=', 'calendar_exposures.role_id')
            ->join('users', 'users.id', '=', 'roles.user_id')
            ->leftJoin('calendar_exposure_reviews as reviews', 'reviews.role_id', '=', 'calendar_exposures.role_id')
            ->whereColumn('events.creator_role_id', 'calendar_exposures.role_id')
            ->where('events.is_draft', false)
            ->where('roles.is_deleted', false)
            ->whereNull('users.blocked_at')
            ->where('users.email', '!=', DemoService::DEMO_EMAIL)
            ->where(fn ($q) => $q
                ->whereNull('reviews.reviewed_at')
                ->orWhereColumn('calendar_exposures.matched_at', '>', 'reviews.reviewed_at'));
    }

    /** The listed schedules, most still public first: role_id, public_now, latest. */
    public static function schedules(): QueryBuilder
    {
        return self::listed()
            ->select('calendar_exposures.role_id')
            ->selectRaw('COUNT(*) as public_now')
            ->selectRaw('MAX(calendar_exposures.matched_at) as latest')
            ->groupBy('calendar_exposures.role_id')
            ->orderByDesc('public_now')
            ->orderByDesc('latest');
    }

    /** How many schedules are listed, for the alert on every admin page. */
    public static function count(): int
    {
        if (! self::tablesReady()) {
            return 0;
        }

        return (int) Cache::flexible(
            self::COUNT_KEY,
            [self::FRESH_SECONDS, self::STALE_SECONDS],
            fn () => (int) self::listed()->distinct()->count('calendar_exposures.role_id'),
            ['seconds' => 60],
        );
    }

    /** Every step on the page drains or refills the alert at once, not a minute later. */
    public static function flushCount(): void
    {
        Cache::forget(self::COUNT_KEY);
    }

    /**
     * A schedule's flagged events, with what was found in each, read again from the event as it
     * is now. $publicOnly leaves out what is off the page already.
     *
     * @return Collection<int, array{event: Event, kinds: list<string>, findings: array}>
     */
    public static function eventsFor(Role $role, bool $publicOnly = false, ?int $limit = null): Collection
    {
        $rows = CalendarExposure::where('role_id', $role->id)
            ->with(['event.roles' => fn ($q) => $q->where('roles.type', 'venue')])
            ->orderByDesc('matched_at')
            ->when($limit, fn ($q) => $q->limit($limit))
            ->get();

        return $rows
            ->filter(fn (CalendarExposure $row) => $row->event && (int) $row->event->creator_role_id === (int) $role->id)
            ->filter(fn (CalendarExposure $row) => ! $publicOnly || ! $row->event->is_draft)
            ->map(function (CalendarExposure $row) {
                $event = $row->event;
                $findings = CalendarPrivacy::findings($event->name, $event->description, self::locationOf($event));

                return ['event' => $event, 'kinds' => CalendarPrivacy::flags($findings), 'findings' => $findings];
            })
            ->values();
    }

    /**
     * Up to $limit lines of what was found, each the field's text cut around the match with the
     * match marked, for the list's cell and the review page.
     *
     * @param  Collection<int, array{event: Event, kinds: list<string>, findings: array}>  $events
     * @return list<array{kind: string, html: HtmlString}>
     */
    public static function snippets(Collection $events, int $limit = 2, int $max = 90): array
    {
        $lines = [];
        $seen = [];

        foreach ($events as $item) {
            foreach (CalendarPrivacy::FLAGS as $kind) {
                foreach ($item['findings'][$kind] ?? [] as $match) {
                    if (count($lines) >= $limit) {
                        return $lines;
                    }
                    if (isset($seen[$kind])) {
                        continue 2;
                    }
                    $seen[$kind] = true;
                    $text = match ($match['field']) {
                        'name' => (string) $item['event']->name,
                        'description' => (string) $item['event']->description,
                        default => (string) self::locationOf($item['event']),
                    };
                    $lines[] = ['kind' => $kind, 'html' => CalendarPrivacy::highlight($text, $match['match'], $max)];

                    continue 2;
                }
            }
        }

        return $lines;
    }

    /** What a kind is called on the operator's pages. */
    public static function kindName(string $kind): string
    {
        return match ($kind) {
            CalendarPrivacy::LINK_PASSWORD => __('messages.calendar_privacy_kind_link_password'),
            CalendarPrivacy::ACCESS_CODES => __('messages.calendar_privacy_kind_access_codes'),
            default => __('messages.calendar_privacy_kind_email_thread'),
        };
    }

    /**
     * The calendars this schedule reads (or did, before a pause), as the pages name them.
     *
     * @return list<array{provider: string, label: string, direction: string, main: bool, paused: bool}>
     */
    public static function syncs(Role $role): array
    {
        $settings = CalendarArrival::tablesReady() ? CalendarArrival::settings($role) : null;
        $rows = [];

        foreach ([
            CalendarArrival::GOOGLE => ['sync_direction', __('messages.google_calendar_sync'), __('messages.from_google_calendar')],
            CalendarArrival::MICROSOFT => ['microsoft_sync_direction', __('messages.microsoft_calendar'), __('messages.from_microsoft_calendar')],
            CalendarArrival::CALDAV => ['caldav_sync_direction', __('messages.caldav_calendar'), __('messages.from_caldav')],
        ] as $provider => [$column, $label, $from]) {
            $direction = $role->{$column};
            $paused = $settings && $settings->{"{$provider}_stopped_reason"} === 'admin';
            $reading = in_array($direction, ['from', 'both'], true);

            if (! $reading && ! $paused) {
                continue;
            }

            $was = $paused ? $settings->{"{$provider}_stopped_direction"} : $direction;

            $rows[] = [
                'provider' => $provider,
                'label' => $label,
                'direction' => $was === 'both' ? __('messages.bidirectional_sync') : $from,
                'main' => match ($provider) {
                    CalendarArrival::GOOGLE => $role->getGoogleCalendarId() === 'primary',
                    CalendarArrival::MICROSOFT => $role->getMicrosoftCalendarId() === null,
                    default => false,
                },
                'paused' => $paused && ! $reading,
            ];
        }

        return $rows;
    }

    /**
     * Where a calendar sync put an event's location: the address of the placeholder venue it made
     * from it (no email, nobody running it), which is where a Zoom link with its password ended up.
     */
    public static function locationOf(Event $event): ?string
    {
        $venues = $event->relationLoaded('roles')
            ? $event->roles->where('type', 'venue')
            : $event->roles()->where('roles.type', 'venue')->get();

        foreach ($venues as $venue) {
            if (! $venue->email && ! $venue->hasRealOwner() && trim((string) $venue->address1) !== '') {
                return (string) $venue->address1;
            }
        }

        return null;
    }

    /** @param list<int> $eventIds */
    private static function forget(array $eventIds): void
    {
        if (CalendarExposure::whereIn('event_id', $eventIds)->delete()) {
            self::flushCount();
        }
    }

    private static function write(Event $event, ?string $location): ?CalendarExposure
    {
        $kinds = CalendarPrivacy::flags(CalendarPrivacy::findings($event->name, $event->description, $location));

        if (! $kinds) {
            self::forget([$event->id]);

            return null;
        }

        $hash = CalendarPrivacy::textHash($event->name, $event->description, $location);
        $row = CalendarExposure::firstOrNew(['event_id' => $event->id]);
        $new = ! $row->exists;

        $row->role_id = $event->creator_role_id;
        if ($new || $row->kinds !== $kinds || $row->text_hash !== $hash) {
            $row->kinds = $kinds;
            $row->text_hash = $hash;
            $row->matched_at = now();
        }

        if ($row->isDirty()) {
            $row->save();
            self::flushCount();
        }

        return $row;
    }
}
