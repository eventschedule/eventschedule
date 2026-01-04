<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Role;
use App\Models\Event;
use App\Notifications\NewRequestsNotification;

class NotifyRequestChanges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-request-changes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify team members when their schedule has new pending requests';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Log::info('Checking for new request changes...');

        // Get all roles that have pending requests
        // Pending requests are events where is_accepted is null in the event_role pivot table
        $roles = Role::whereHas('events', function ($query) {
            $query->whereNull('event_role.is_accepted');
        })->get();

        $notifiedCount = 0;

        foreach ($roles as $role) {
            // Count current pending requests for this role
            $currentRequestCount = Event::whereHas('roles', function ($query) use ($role) {
                $query->where('event_role.role_id', $role->id)
                    ->whereNull('event_role.is_accepted');
            })->count();

            // Get last notified count (default to 0 if null)
            $lastNotifiedCount = $role->last_notified_request_count ?? 0;

            // Check that role requires approving requests
            if (! $role->accept_requests || ! $role->require_approval) {
                continue;
            }

            // Only notify if current count is greater than last notified count
            if ($currentRequestCount > $lastNotifiedCount) {
                                
                // Get all team members (users with level != 'follower')
                $teamMembers = $role->members()->get();

                if ($teamMembers->count() > 0) {
                    $owner = $role->user;
                    $ccEmails = $teamMembers->map(function($member) use ($owner) {
                        return $member->id !== $owner->id ? $member->email : null;
                    })->filter()->values()->toArray();

                    $owner->notify(new NewRequestsNotification($role, $currentRequestCount, $ccEmails));
                    
                    $role->last_notified_request_count = $currentRequestCount;
                    $role->save();

                    $notifiedCount++;
                    \Log::info("Notified team members for role {$role->name} ({$role->subdomain}) - {$currentRequestCount} pending requests");
                }
            }
        }

        \Log::info("Request notification check completed. Notified {$notifiedCount} roles.");
        $this->info("Notified {$notifiedCount} roles with new requests.");
    }
}

