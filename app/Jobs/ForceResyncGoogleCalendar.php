<?php

namespace App\Jobs;

use App\Models\CalendarSync;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

/**
 * Re-pushes every non-draft event of a schedule to the currently-selected
 * Google Calendar, deleting any leftover copies on previously-selected
 * calendars first.
 *
 * Production runs queue:work inside an HTTP request to /translate_data, so any
 * single invocation must finish well within the gateway timeout (~30-60s).
 * That makes a "process every event in one shot" job impossible for big
 * schedules. Instead, each invocation processes a small batch and dispatches
 * a follow-up if there's more to do, so the chain advances minute-by-minute
 * via the regular cron-driven worker.
 *
 * Two phases run in sequence per chain:
 *   1. cleanup — for each existing CalendarSync row with a known
 *      google_calendar_id, delete the event from that (possibly previous)
 *      calendar on Google, then drop the row. Rows missing google_calendar_id
 *      (legacy data from before that column existed) are dropped without an
 *      API call since we don't know which calendar they live on.
 *   2. create — for each non-draft Event of the role with no surviving
 *      CalendarSync row, push it to the currently-selected calendar and
 *      record the new mapping.
 */
class ForceResyncGoogleCalendar implements ShouldQueue
{
    use Queueable;

    public const PHASE_CLEANUP = 'cleanup';

    public const PHASE_CREATE = 'create';

    public const CHUNK_SIZE = 10;

    public $deleteWhenMissingModels = true;

    public $timeout = 90; // each chunk is small; this is just a safety net

    public $tries = 1; // chunks are idempotent at the chain level; we don't auto-retry partials

    protected User $user;

    protected Role $role;

    // Class-level defaults so old serialized payloads (failed_jobs from a
    // pre-chunking deploy) deserialize cleanly without the new fields.
    protected string $phase = self::PHASE_CLEANUP;

    protected int $cursor = 0;

    public function __construct(User $user, Role $role, string $phase = self::PHASE_CLEANUP, int $cursor = 0)
    {
        $this->user = $user;
        $this->role = $role;
        $this->phase = $phase;
        $this->cursor = $cursor;
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("force-resync-google-{$this->user->id}-{$this->role->id}"))
                ->dontRelease(),
        ];
    }

    public function handle(GoogleCalendarService $googleCalendarService): void
    {
        $context = [
            'user_id' => $this->user->id,
            'role_id' => $this->role->id,
            'phase' => $this->phase,
            'cursor' => $this->cursor,
        ];

        try {
            if (! $googleCalendarService->ensureValidToken($this->user)) {
                Log::error('ForceResyncGoogleCalendar: token refresh failed', $context);

                return;
            }

            if ($this->phase === self::PHASE_CLEANUP) {
                $this->runCleanupChunk($googleCalendarService);
            } else {
                $this->runCreateChunk($googleCalendarService);
            }
        } catch (\Throwable $e) {
            report($e);
            Log::error('ForceResyncGoogleCalendar chunk failed', $context + [
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function runCleanupChunk(GoogleCalendarService $googleCalendarService): void
    {
        $rows = CalendarSync::where('user_id', $this->user->id)
            ->where('role_id', $this->role->id)
            ->whereNotNull('google_event_id')
            ->whereNotNull('google_calendar_id')
            ->where('id', '>', $this->cursor)
            ->orderBy('id')
            ->limit(self::CHUNK_SIZE)
            ->get();

        $lastId = $this->cursor;

        foreach ($rows as $row) {
            try {
                $googleCalendarService->deleteEvent($row->google_event_id, $row->google_calendar_id, $this->role->id);
            } catch (\Throwable $e) {
                Log::warning('ForceResyncGoogleCalendar: orphan delete failed', [
                    'user_id' => $this->user->id,
                    'role_id' => $this->role->id,
                    'google_calendar_id' => $row->google_calendar_id,
                    'google_event_id' => $row->google_event_id,
                    'exception_class' => get_class($e),
                    'error' => $e->getMessage(),
                ]);
            }
            $row->delete();
            $lastId = $row->id;
        }

        if ($rows->count() >= self::CHUNK_SIZE) {
            // More cleanup chunks to process; advance the cursor and queue another.
            self::dispatch($this->user, $this->role, self::PHASE_CLEANUP, $lastId);

            return;
        }

        // Cleanup phase done. Drop any legacy rows missing google_calendar_id
        // (we can't safely deletes those from Google without knowing the
        // calendar, so the Google copies are knowingly orphaned).
        CalendarSync::where('user_id', $this->user->id)
            ->where('role_id', $this->role->id)
            ->delete();

        self::dispatch($this->user, $this->role, self::PHASE_CREATE, 0);
    }

    private function runCreateChunk(GoogleCalendarService $googleCalendarService): void
    {
        $calendarId = $this->role->getGoogleCalendarId();

        $events = Event::whereHas('roles', function ($query) {
            $query->where('roles.id', $this->role->id);
        })
            ->where('is_draft', false)
            ->where('id', '>', $this->cursor)
            ->orderBy('id')
            ->limit(self::CHUNK_SIZE)
            ->get();

        $lastId = $this->cursor;
        $created = 0;
        $errors = 0;

        foreach ($events as $event) {
            $lastId = $event->id;

            // Skip events that already have a sync row for this user/role —
            // covers the case where a previous chain partially completed and
            // we re-entered after a re-click.
            $existing = CalendarSync::where('user_id', $this->user->id)
                ->where('event_id', $event->id)
                ->where('role_id', $this->role->id)
                ->first();
            if ($existing?->google_event_id) {
                continue;
            }

            $googleEvent = $googleCalendarService->createEvent($event, $this->role, $calendarId);

            if ($googleEvent) {
                CalendarSync::updateOrCreate(
                    ['user_id' => $this->user->id, 'event_id' => $event->id, 'role_id' => $this->role->id],
                    ['google_event_id' => $googleEvent->getId(), 'google_calendar_id' => $calendarId]
                );
                $created++;
            } else {
                $errors++;
            }
        }

        Log::info('ForceResyncGoogleCalendar create chunk', [
            'user_id' => $this->user->id,
            'role_id' => $this->role->id,
            'cursor' => $this->cursor,
            'last_id' => $lastId,
            'created' => $created,
            'errors' => $errors,
        ]);

        if ($events->count() >= self::CHUNK_SIZE) {
            self::dispatch($this->user, $this->role, self::PHASE_CREATE, $lastId);

            return;
        }

        Log::info('ForceResyncGoogleCalendar completed', [
            'user_id' => $this->user->id,
            'role_id' => $this->role->id,
        ]);
    }
}
