<?php

namespace App\Jobs;

use App\Models\Role;
use App\Services\PlaceLookupService;
use App\Services\VenueMap;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * A head start on a schedule's venues when its owner switches the venue map on: a dozen or so are
 * placed at once, where the timer alone would take three minutes over them.
 *
 * One request a second, the search service's ceiling, for BURST_SECONDS and no longer. The queue
 * is drained by one worker inside the scheduler's own process (routes/console.php,
 * process-queue), so every second here is a second a ticket email waits behind this job: it used
 * to be allowed 25 requests however long they took, which was half a minute on a good day and
 * most of four when the service was slow. What is left is finished by app:place-venues, four a
 * minute. Shares PlaceLookupService::run()'s lock with it, so the two never ask at once: if the
 * timer holds it, this run asks nothing and the timer carries on.
 *
 * Not dispatched on the sync queue driver (RoleController::update()): there it would run inside
 * the owner's save. The timer does the whole job on such an install.
 */
class PlaceScheduleVenues implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    // The budget plus one request's own timeout, with room. Past this the worker kills its
    // process, which here is the scheduler's.
    public int $timeout = 60;

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
            PlaceLookupService::run(PlaceLookupService::BURST_BATCH, 1000000, $waiting, PlaceLookupService::BURST_SECONDS);
        }

        VenueMap::refreshReady($role->fresh());
    }
}
