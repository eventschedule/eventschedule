<?php

namespace App\Console\Commands;

use App\Models\VenueMapSetting;
use App\Services\PlaceLookupService;
use App\Services\VenueMap;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Asks the venue map's address search about the addresses that are waiting, a few at a time.
 *
 * Every minute on BOTH cron rails, ungated: any install whose operator has named an address search
 * (config services.map.geocoder_url) has venue maps, and one that has not does nothing here. At
 * most four requests a MINUTE, a second apart: the search service's limit for a script on a timer.
 * Four a run was not that: both rails tick on some installs, and a cron may call /translate_data
 * more than once a minute, so the asking is behind a key that one run a minute can take.
 * One runner at a time across both rails and the queued job, by PlaceLookupService::run()'s lock.
 *
 * Then any schedule whose map is switched on and whose venues have all been asked is marked ready,
 * which is what lets its band show: a map's FIRST pass is whole. (A venue added later is on the
 * map as soon as it is placed.)
 *
 * And once an hour, the addresses no map has read for PlaceLookupService::KEEP_DAYS are deleted.
 */
class PlaceVenues extends Command
{
    protected $signature = 'app:place-venues';

    protected $description = 'Look up the addresses of venues waiting for a pin on a venue map';

    private const MINUTE_KEY = 'place_lookups.timer_minute';

    private const PRUNE_KEY = 'place_lookups.pruned';

    public function handle(): int
    {
        if (! PlaceLookupService::enabled()) {
            return self::SUCCESS;
        }

        // 55 seconds, not 60: the next minute's tick must find the key gone.
        $result = Cache::add(self::MINUTE_KEY, true, now()->addSeconds(55))
            ? PlaceLookupService::run(PlaceLookupService::TIMER_BATCH)
            : ['asked' => 0, 'busy' => false, 'paused' => false, 'failed' => false, 'waited' => true];

        // Few rows and short-lived: a schedule leaves this set the first time its queue is empty.
        // Reading its venues also registers any address nobody has noted yet, so a map switched
        // on while the queue was down still gets its lookups. A deleted schedule is left out in
        // the query itself: skipped only afterwards, twenty of them filled this window for good
        // and no new map was ever marked ready.
        VenueMapSetting::query()
            ->where('enabled', true)
            ->whereNull('ready_at')
            ->whereHas('role', fn ($q) => $q->where('is_deleted', false))
            ->orderBy('id')
            ->limit(20)
            ->with('role')
            ->get()
            ->each(fn (VenueMapSetting $setting) => VenueMap::refreshReady($setting->role));

        if (Cache::add(self::PRUNE_KEY, true, now()->addHour())) {
            PlaceLookupService::prune();
        }

        $this->info('Asked '.$result['asked']
            .(! empty($result['waited']) ? ' (already asked this minute)' : '')
            .($result['paused'] ? ' (paused after a failure)' : '')
            .($result['busy'] ? ' (another runner holds the lock)' : ''));

        return self::SUCCESS;
    }
}
