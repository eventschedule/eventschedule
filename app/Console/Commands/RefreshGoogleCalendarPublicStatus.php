<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\RoleUser;
use App\Services\GoogleCalendarService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RefreshGoogleCalendarPublicStatus extends Command
{
    protected $signature = 'google:refresh-public-status
                            {--role= : Refresh status for a specific role ID or subdomain}';

    protected $description = 'Refresh the cached "is public" flag for each Google-Calendar-syncing schedule by checking the calendar ACL';

    protected $googleCalendarService;

    public function __construct(GoogleCalendarService $googleCalendarService)
    {
        parent::__construct();
        $this->googleCalendarService = $googleCalendarService;
    }

    public function handle()
    {
        $this->info('Refreshing Google Calendar public-status flags...');

        $query = Role::whereIn('sync_direction', ['to', 'both']);

        $roleFilter = $this->option('role');
        if ($roleFilter) {
            if (is_numeric($roleFilter)) {
                $query->where('id', $roleFilter);
            } else {
                $query->where('subdomain', $roleFilter);
            }
        }

        $roles = $query->get();

        if ($roles->isEmpty()) {
            $this->info('No schedules with Google Calendar sync to refresh.');

            return 0;
        }

        $this->info("Checking {$roles->count()} schedule(s).");

        $updated = 0;
        $unchanged = 0;
        $skipped = 0;

        foreach ($roles as $role) {
            $pivot = RoleUser::where('role_id', $role->id)
                ->where('user_id', $role->user_id)
                ->first();

            if (! $pivot || ! $pivot->google_calendar_id || $pivot->google_calendar_id === 'primary') {
                $skipped++;

                continue;
            }

            $owner = $role->user;
            if (! $owner || ! $owner->google_token) {
                $skipped++;

                continue;
            }

            try {
                $isPublic = $this->googleCalendarService->isCalendarPublic($owner, $pivot->google_calendar_id);

                if ($isPublic === null) {
                    $skipped++;

                    continue;
                }

                if ($pivot->google_calendar_is_public === $isPublic) {
                    $unchanged++;

                    continue;
                }

                $pivot->update(['google_calendar_is_public' => $isPublic]);
                $updated++;
            } catch (\Throwable $e) {
                Log::error('Failed to refresh Google Calendar public status', [
                    'role_id' => $role->id,
                    'subdomain' => $role->subdomain,
                    'error' => $e->getMessage(),
                ]);
                $skipped++;
            }
        }

        $this->info("Updated: {$updated}, unchanged: {$unchanged}, skipped: {$skipped}");

        return 0;
    }
}
