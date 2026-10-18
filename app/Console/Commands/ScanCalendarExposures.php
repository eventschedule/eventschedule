<?php

namespace App\Console\Commands;

use App\Services\CalendarExposureWatch;
use App\Services\MeetingLinkVenues;
use Illuminate\Console\Command;

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
        $done = CalendarExposureWatch::sweep();
        $venues = MeetingLinkVenues::sweep();

        $this->info("Read {$done['read']} events: {$done['flagged']} flagged, {$done['removed']} cleared. Removed {$venues} meeting-link venues.");

        return Command::SUCCESS;
    }
}
