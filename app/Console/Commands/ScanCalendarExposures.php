<?php

namespace App\Console\Commands;

use App\Services\CalendarExposureWatch;
use App\Services\MeetingLinkVenues;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * The sweep behind /admin/private-calendars: the next window of events read for meeting details
 * and email threads (CalendarExposureWatch), and the next window of venues for placeholders a
 * calendar import made out of a meeting link (MeetingLinkVenues). Every fifteen minutes on both
 * cron rails; each run reads a bounded window and moves its cursor on.
 */
class ScanCalendarExposures extends Command
{
    protected $signature = 'calendar:scan-exposures';

    protected $description = 'Look for schedules publishing a private calendar, and remove venues made out of meeting links';

    public function handle(): int
    {
        // One lock for both cron rails: withoutOverlapping() only serialises a rail against
        // itself, and two runs at once race on the same window and its cursor.
        $lock = Cache::lock('calendar-exposure-scan-run', 600);
        if (! $lock->get()) {
            $this->info('Another scan is running.');

            return Command::SUCCESS;
        }

        try {
            $done = CalendarExposureWatch::sweep();
            $venues = MeetingLinkVenues::sweep();
        } finally {
            $lock->release();
        }

        $this->info("Read {$done['read']} events: {$done['flagged']} flagged, {$done['removed']} cleared. Removed {$venues} meeting-link venues.");

        return Command::SUCCESS;
    }
}
