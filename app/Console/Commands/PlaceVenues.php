<?php

namespace App\Console\Commands;

use App\Models\VenueMapSetting;
use App\Services\PlaceLookupService;
use App\Services\VenueMap;
use Illuminate\Console\Command;

/**
 * Asks the venue map's address search about the addresses that are waiting, a few at a time.
 *
 * Every minute on BOTH cron rails, ungated: any install whose operator has named an address search
 * (config services.map.geocoder_url) has venue maps, and one that has not does nothing here. At
 * most four requests a run, a second apart: the search service's limit for a script on a timer.
 * One runner at a time across both rails and the queued job, by PlaceLookupService::run()'s lock.
 *
 * Then any schedule whose map is switched on and whose venues have all been asked is marked ready,
 * which is what lets its band show: a map is never published half-placed.
 */
class PlaceVenues extends Command
{
    protected $signature = 'app:place-venues';

    protected $description = 'Look up the addresses of venues waiting for a pin on a venue map';

    public function handle(): int
    {
        if (! PlaceLookupService::enabled()) {
            return self::SUCCESS;
        }

        $result = PlaceLookupService::run(PlaceLookupService::TIMER_BATCH);

        // Few rows and short-lived: a schedule leaves this set the first time its queue is empty.
        // Reading its venues also registers any address nobody has noted yet, so a map switched
        // on while the queue was down still gets its lookups.
        VenueMapSetting::query()
            ->where('enabled', true)
            ->whereNull('ready_at')
            ->orderBy('id')
            ->limit(20)
            ->with('role')
            ->get()
            ->each(fn (VenueMapSetting $setting) => $setting->role && ! $setting->role->is_deleted
                ? VenueMap::refreshReady($setting->role)
                : null);

        $this->info('Asked '.$result['asked'].($result['paused'] ? ' (paused after a failure)' : '').($result['busy'] ? ' (another runner holds the lock)' : ''));

        return self::SUCCESS;
    }
}
