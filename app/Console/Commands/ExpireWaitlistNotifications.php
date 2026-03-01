<?php

namespace App\Console\Commands;

use App\Jobs\NotifyWaitlist;
use App\Models\TicketWaitlist;
use Illuminate\Console\Command;

class ExpireWaitlistNotifications extends Command
{
    protected $signature = 'app:expire-waitlist';

    protected $description = 'Expire waitlist notifications that have passed their 24-hour window and notify next in line';

    public function handle()
    {
        $expired = TicketWaitlist::where('status', 'notified')
            ->where('expires_at', '<', now())
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No expired waitlist notifications found.');

            return 0;
        }

        $count = 0;
        $eventDates = [];

        foreach ($expired as $entry) {
            $entry->update(['status' => 'expired']);
            $count++;

            $key = $entry->event_id.':'.$entry->event_date;
            $eventDates[$key] = [
                'event_id' => $entry->event_id,
                'event_date' => $entry->event_date,
            ];
        }

        $this->info("Expired {$count} waitlist notifications.");

        // Notify next person for each unique event/date
        foreach ($eventDates as $data) {
            NotifyWaitlist::dispatch($data['event_id'], $data['event_date']);
        }

        $this->info('Dispatched '.count($eventDates).' notify-next jobs.');

        return 0;
    }
}
