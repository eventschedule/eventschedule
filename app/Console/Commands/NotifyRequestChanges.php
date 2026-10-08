<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Services\RequestNotifier;
use Illuminate\Console\Command;

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
     * The noon summary: whatever is waiting that no mail has told of yet. A request sent through
     * one of the public forms is usually told at once (EventController::tellOwnersOfPendingRequest());
     * this is for one that arrived within a quarter of an hour of another, for one that came by
     * another road (a schedule adding its own event to this one, an appointment waiting to be
     * confirmed), and for one whose mail could not be sent.
     *
     * Until 2026-10 the rule was "more waiting than the last mail counted", which sent a bare
     * "2 pending" the noon after an owner answered one of three, and nothing at all when one
     * arrived while another was answered.
     */
    public function handle(RequestNotifier $notifier)
    {
        // Pending requests are events where is_accepted is null in the event_role pivot table.
        // Only schedules that take requests and review them, as before; and not a deleted one,
        // whose members used to be written to about a schedule that is gone.
        $roles = Role::where('accept_requests', true)
            ->where('require_approval', true)
            ->where('is_deleted', false)
            ->whereHas('events', function ($query) {
                $query->whereNull('event_role.is_accepted')->whereNull('event_role.request_notified_at');
            })->get();

        $notifiedCount = 0;

        foreach ($roles as $role) {
            try {
                if ($notifier->announce($role)) {
                    $notifiedCount++;
                }
            } catch (\Throwable $e) {
                // One schedule's trouble must not cost every schedule after it its summary.
                report($e);
            }
        }

        $this->info("Notified {$notifiedCount} roles with new requests.");
    }
}
