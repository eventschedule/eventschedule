<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Notifications\NewFanContentNotification;
use Illuminate\Console\Command;

class NotifyFanContentChanges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:notify-fan-content-changes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Notify event owners when their event has new pending fan content';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        \Log::info('Checking for new fan content changes...');

        // Get all events that have pending fan content (videos, comments, or photos)
        $events = Event::where(function ($query) {
            $query->whereHas('pendingVideos')
                ->orWhereHas('pendingComments')
                ->orWhereHas('pendingPhotos');
        })->with(['user', 'roles', 'pendingVideos', 'pendingComments', 'pendingPhotos'])->get();

        $notifiedCount = 0;

        foreach ($events as $event) {
            // Count current pending fan content
            $currentCount = $event->pendingVideos->count() + $event->pendingComments->count() + $event->pendingPhotos->count();

            // Get last notified count (default to 0 if null)
            $lastNotifiedCount = $event->last_notified_fan_content_count ?? 0;

            // Only notify if current count is greater than last notified count
            if ($currentCount > $lastNotifiedCount) {

                // Skip if event has no user
                if (! $event->user) {
                    continue;
                }

                // Skip if event has no associated roles (no valid URL)
                if ($event->roles->isEmpty()) {
                    continue;
                }

                $role = $event->roles->first();
                $subdomain = $role->subdomain;

                // Check if event creator has opted in to fan content notifications
                $editors = $role->getEditorsWantingNotification('new_fan_content');
                if (! $editors->contains('id', $event->user->id)) {
                    continue;
                }

                $event->user->notify(new NewFanContentNotification($event, $currentCount, $subdomain));

                $event->last_notified_fan_content_count = $currentCount;
                $event->save();

                $notifiedCount++;
                \Log::info("Notified event owner for event {$event->name} ({$subdomain}) - {$currentCount} pending fan content items");
            }
        }

        \Log::info("Fan content notification check completed. Notified {$notifiedCount} events.");
        $this->info("Notified {$notifiedCount} events with new fan content.");
    }
}
