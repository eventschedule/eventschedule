<?php

namespace App\Console\Commands;

use App\Models\EventPoll;
use App\Models\Role;
use App\Notifications\NewPollOptionsNotification;
use Illuminate\Console\Command;

class NotifyPollOptionChanges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-poll-option-changes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify team members when their schedule has new pending poll option suggestions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Check email settings
        if (config('app.hosted')) {
            // Hosted: each role checked individually below
        } else {
            // Selfhosted: check that a real mailer is configured
            if (in_array(config('mail.default'), [null, 'log', 'array'])) {
                $this->info('No mailer configured. Skipping poll option notifications.');

                return;
            }
        }

        // Get all roles that have events with polls containing pending options
        $roles = Role::whereHas('events', function ($query) {
            $query->whereHas('polls', function ($pollQuery) {
                $pollQuery->whereNotNull('pending_options');
            });
        })->get();

        $notifiedCount = 0;

        foreach ($roles as $role) {
            // On hosted, skip roles without email settings
            if (config('app.hosted') && ! $role->hasEmailSettings()) {
                continue;
            }

            // Count current pending poll options for this role
            $currentCount = EventPoll::whereHas('event', function ($query) use ($role) {
                $query->whereHas('roles', function ($roleQuery) use ($role) {
                    $roleQuery->where('event_role.role_id', $role->id);
                });
            })->whereNotNull('pending_options')
                ->get()
                ->sum(function ($poll) {
                    return count($poll->pending_options ?? []);
                });

            // Get last notified count (default to 0 if null)
            $lastNotifiedCount = $role->last_notified_poll_option_count ?? 0;

            // Only notify if current count is greater than last notified count
            if ($currentCount > $lastNotifiedCount) {

                $editors = $role->getEditorsWantingNotification('new_poll_option');

                if ($editors->isNotEmpty()) {
                    foreach ($editors as $editor) {
                        $editor->notify(new NewPollOptionsNotification($role, $currentCount));
                    }

                    $role->last_notified_poll_option_count = $currentCount;
                    $role->save();

                    $notifiedCount++;
                }
            }
        }

        $this->info("Notified {$notifiedCount} roles with new poll option suggestions.");
    }
}
