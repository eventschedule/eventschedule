<?php

namespace App\Jobs;

use App\Models\CalendarSync;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

/**
 * Re-pushes the schedule's events to the currently-selected Google Calendar.
 *
 * Production runs queue:work inside the /translate_data HTTP request, so each
 * invocation must finish well within the gateway timeout. The job processes a
 * small batch of events per invocation and dispatches a follow-up if more
 * remain - the chain advances minute-by-minute via the cron-driven worker.
 *
 * On the sync connection (QUEUE_CONNECTION=sync, the selfhost default) the
 * follow-up is dispatched afterResponse() instead, so it starts only once this
 * batch has released its overlap lock. Dispatched on the spot it met the lock
 * still held, dontRelease() dropped it without a word, and every sync resync
 * stopped after ten events. The batches now run back to back in the one PHP
 * process, after the page has responded.
 *
 * Per-event idempotency: if an event already has a CalendarSync row pointing
 * to the currently-selected calendar, it is left untouched. Otherwise the old
 * copy (if any) is deleted from its previous calendar and a fresh copy is
 * pushed to the current calendar. Repeated clicks therefore only re-do work
 * that's actually still needed - a stalled chain can be re-triggered safely.
 */
class ForceResyncGoogleCalendar implements ShouldQueue
{
    use Queueable;

    public const CHUNK_SIZE = 10;

    public $deleteWhenMissingModels = true;

    public $timeout = 90;

    public $tries = 1;

    protected User $user;

    protected Role $role;

    // Class-level default so older serialized payloads (failed_jobs from
    // pre-chunking deploys) deserialize cleanly into the new field.
    protected int $cursor = 0;

    public function __construct(User $user, Role $role, int $cursor = 0)
    {
        $this->user = $user;
        $this->role = $role;
        $this->cursor = $cursor;
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("force-resync-google-{$this->user->id}-{$this->role->id}"))
                ->dontRelease()
                ->expireAfter(120),
        ];
    }

    public function handle(GoogleCalendarService $googleCalendarService): void
    {
        $context = [
            'user_id' => $this->user->id,
            'role_id' => $this->role->id,
            'cursor' => $this->cursor,
        ];

        try {
            if (! $googleCalendarService->ensureValidToken($this->user)) {
                Log::error('ForceResyncGoogleCalendar: token refresh failed', $context);

                return;
            }

            $currentCalendarId = $this->role->getGoogleCalendarId();
            $totalEvents = $this->eventsQuery()->count();
            $eventsRemaining = $this->eventsQuery()->where('events.id', '>', $this->cursor)->count();

            Log::info('ForceResyncGoogleCalendar chunk start', $context + [
                'current_calendar_id' => $currentCalendarId,
                'total_events' => $totalEvents,
                'events_remaining' => $eventsRemaining,
            ]);

            $events = $this->eventsQuery()
                ->where('events.id', '>', $this->cursor)
                ->orderBy('events.id')
                ->limit(self::CHUNK_SIZE)
                ->get();

            $skipped = 0;
            $migrated = 0;
            $created = 0;
            $errors = 0;
            $lastId = $this->cursor;

            foreach ($events as $event) {
                $lastId = $event->id;

                $existing = CalendarSync::where('user_id', $this->user->id)
                    ->where('event_id', $event->id)
                    ->where('role_id', $this->role->id)
                    ->first();

                // Already on the right calendar - leave as-is so repeated
                // clicks (or partial re-runs) don't churn unchanged events.
                if ($existing?->google_event_id && $existing->google_calendar_id === $currentCalendarId) {
                    $skipped++;

                    continue;
                }

                // Was on a different calendar - delete that old copy first.
                // Skip the API call when google_calendar_id is unknown
                // (legacy NULLs); the orphan stays on the unknown old
                // calendar, but the new copy will land correctly.
                if ($existing?->google_event_id && $existing->google_calendar_id) {
                    try {
                        $googleCalendarService->deleteEvent(
                            $existing->google_event_id,
                            $existing->google_calendar_id,
                            $this->role->id
                        );
                    } catch (\Throwable $e) {
                        Log::warning('ForceResyncGoogleCalendar: old-calendar delete failed', $context + [
                            'event_id' => $event->id,
                            'old_calendar_id' => $existing->google_calendar_id,
                            'old_google_event_id' => $existing->google_event_id,
                            'exception_class' => get_class($e),
                            'error' => $e->getMessage(),
                        ]);
                    }
                }

                $googleEvent = $googleCalendarService->createEvent($event, $this->role, $currentCalendarId);

                if ($googleEvent) {
                    CalendarSync::updateOrCreate(
                        ['user_id' => $this->user->id, 'event_id' => $event->id, 'role_id' => $this->role->id],
                        ['google_event_id' => $googleEvent->getId(), 'google_calendar_id' => $currentCalendarId]
                    );
                    $existing?->google_event_id ? $migrated++ : $created++;
                } else {
                    $errors++;
                }
            }

            $eventsRemainingAfter = $this->eventsQuery()->where('events.id', '>', $lastId)->count();

            Log::info('ForceResyncGoogleCalendar chunk done', $context + [
                'last_id' => $lastId,
                'processed' => $events->count(),
                'skipped' => $skipped,
                'migrated' => $migrated,
                'created' => $created,
                'errors' => $errors,
                'events_remaining' => $eventsRemainingAfter,
            ]);

            if ($events->count() >= self::CHUNK_SIZE) {
                // The follow-up must not start until this batch has released its overlap lock. A
                // real queue gives it that for free: the worker picks it up after handle() has
                // returned. The sync connection runs a dispatch on the spot, inside this handle()
                // and under the lock, so dontRelease() dropped it and the resync stopped after ten
                // events. afterResponse() defers it to Application::terminate(), after the
                // middleware has released the lock, and terminate() also runs the callback each
                // later batch appends, so the chain reaches the end in this one process.
                if ($this->onSyncConnection()) {
                    self::dispatch($this->user, $this->role, $lastId)->afterResponse();
                } else {
                    self::dispatch($this->user, $this->role, $lastId);
                }

                return;
            }

            Log::info('ForceResyncGoogleCalendar completed', [
                'user_id' => $this->user->id,
                'role_id' => $this->role->id,
                'total_events' => $totalEvents,
            ]);
        } catch (\Throwable $e) {
            report($e);
            Log::error('ForceResyncGoogleCalendar chunk failed', $context + [
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e->getMessage(),
            ]);

            // A batch deferred with afterResponse() runs inside Application::terminate(), where a
            // throw skips every terminating callback after it, the same request's webhooks among
            // them. report() above has the error, so the chain ends here instead. Everywhere else
            // the throw stays: the button's own first batch turns it into an error the owner sees,
            // and a real queue's worker records the failure.
            if ($this->runsAfterResponse()) {
                return;
            }

            throw $e;
        }
    }

    /**
     * Whether a dispatch from this batch would run on the spot rather than on a worker. The
     * configured default covers the button's first batch; the job's own connection covers a
     * batch that was itself deferred with afterResponse(), which pins it to 'sync'.
     */
    private function onSyncConnection(): bool
    {
        return config('queue.default') === 'sync'
            || $this->connection === 'sync'
            || $this->job?->getConnectionName() === 'sync';
    }

    /**
     * afterResponse() pins the job to the sync connection (it dispatches through dispatchSync()),
     * while a plain dispatch() leaves $connection null even when the default connection is sync.
     * So this is true for a batch deferred into Application::terminate(), and false for the
     * button's own first batch.
     */
    private function runsAfterResponse(): bool
    {
        return $this->connection === 'sync';
    }

    private function eventsQuery(): Builder
    {
        return Event::whereHas('roles', function ($query) {
            $query->where('roles.id', $this->role->id);
        })->where('is_draft', false);
    }
}
