<?php

namespace App\Jobs;

use App\Models\Role;
use App\Services\PlaceLookupService;
use App\Services\VenueMap;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * The first pass over a schedule's venues when its owner switches the venue map on, so the map is
 * on the page in under a minute instead of after a quarter of an hour of four-a-minute.
 *
 * One request a second, the search service's ceiling, and at most BURST_BATCH of them, so the
 * queue worker is held for seconds and not minutes; a schedule with more venues than that is
 * finished by app:place-venues. Shares PlaceLookupService::run()'s lock with it, so the two never
 * ask at once: if the timer holds it, this run asks nothing and the timer carries on.
 *
 * Not dispatched on the sync queue driver (RoleController::update()): there it would run inside
 * the owner's save. The timer does the whole job on such an install.
 */
class PlaceScheduleVenues implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 240;

    public int $uniqueFor = 300;

    public function __construct(public int $roleId) {}

    public function uniqueId(): string
    {
        return (string) $this->roleId;
    }

    public function handle(): void
    {
        $role = Role::find($this->roleId);

        if (! $role || $role->is_deleted || ! VenueMap::enabledFor($role)) {
            return;
        }

        $waiting = VenueMap::waitingHashes($role);

        if ($waiting) {
            PlaceLookupService::run(PlaceLookupService::BURST_BATCH, 1000000, $waiting);
        }

        VenueMap::refreshReady($role->fresh());
    }
}
