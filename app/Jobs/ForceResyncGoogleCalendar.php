<?php

namespace App\Jobs;

use App\Models\Role;
use App\Models\User;
use App\Services\GoogleCalendarService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Facades\Log;

class ForceResyncGoogleCalendar implements ShouldQueue
{
    use Queueable;

    public $deleteWhenMissingModels = true;

    // Must stay below the database queue's `retry_after` (1800s) so the queue
    // lock can never expire and let a second worker re-execute this job.
    public $timeout = 1700;

    public $tries = 1; // The job is idempotent but expensive; let the user re-trigger manually rather than auto-retrying a half-completed run

    public function __construct(
        protected User $user,
        protected Role $role,
    ) {}

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping("force-resync-google-{$this->user->id}-{$this->role->id}"))
                ->dontRelease(),
        ];
    }

    public function handle(GoogleCalendarService $googleCalendarService): void
    {
        $context = ['user_id' => $this->user->id, 'role_id' => $this->role->id];

        try {
            if (! $googleCalendarService->ensureValidToken($this->user)) {
                Log::error('ForceResyncGoogleCalendar: token refresh failed', $context);

                return;
            }

            $results = $googleCalendarService->syncUserEvents($this->user, $this->role, true);

            Log::info('ForceResyncGoogleCalendar completed', $context + ['results' => $results]);
        } catch (\Throwable $e) {
            report($e);
            Log::error('ForceResyncGoogleCalendar failed', $context + [
                'exception_class' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
