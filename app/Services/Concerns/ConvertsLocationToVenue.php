<?php

namespace App\Services\Concerns;

use App\Models\Event;
use App\Models\Role;
use App\Utils\GeminiUtils;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Shared by the calendar-sync services (Google + CalDAV) to turn an inbound
 * free-text location into a venue Role.
 *
 * Matching reuses the same normalized columns the rest of the app dedupes on
 * (GeminiUtils::normalizeForMatch + roles.*_normalized), scoped to the importing
 * user's own venues, so string variants ("Patrick's Caesarea" vs "Patrick's,
 * Caesarea") no longer each spawn a new venue and events land on the user's real
 * (owned) venue instead of an auto-created stub.
 */
trait ConvertsLocationToVenue
{
    /**
     * Resolve an inbound location onto a venue and attach it to the event. Returns whether a
     * venue was attached (the update paths count that as a change even when the Event row
     * itself is clean).
     *
     * The "does this event already have a venue" test has to happen BEFORE the conversion,
     * because convertLocationToVenue() PERSISTS a venue and follower-attaches the user. Two
     * ways that used to leave an orphan - a venue Role with no events, cluttering the user's
     * venue dropdown and their Following list:
     *
     *  1. The schedule being synced is itself a venue, so it is already attached to the event
     *     as its venue and nothing we make here can ever be used. One orphan per distinct
     *     location string, starting the moment the schedule's calendar is first synced.
     *  2. The location changed on an event that already has a venue. We do not re-point the
     *     event (that would fight a venue the user set by hand), so again nothing is used.
     */
    protected function attachLocationVenue(Event $event, Role $role, ?string $location): bool
    {
        if (! $location) {
            return false;
        }

        if ($event->roles()->where('roles.type', 'venue')->exists()) {
            return false;
        }

        $venue = $this->convertLocationToVenue($role, $location);

        if (! $venue) {
            return false;
        }

        // false is the DECLINED state, not "not yet decided": guest listings filter
        // is_accepted = true and the Requests tab filters whereNull, so a false row is invisible
        // on the venue's page AND absent from the queue its owner would approve from, with
        // nothing that ever revisits it. isMember() counts owner/admin/viewer while the lookup
        // above deliberately includes follower, so a venue the user merely FOLLOWS landed there.
        //
        // isEditableBy() first, because pending is only a better answer than declined when
        // somebody can actually clear it. It cannot here: accept() requires isEditor(), and an
        // UNCLAIMED venue has no owner or admin - the app's own rule (Role::isEditableBy) is that
        // a follower may edit such a role, so their own imported event belongs on it. That covers
        // both the stub this import just created and one an earlier import left behind.
        //
        // Otherwise autoAcceptsEventFrom(), the one acceptance rule in the app; `?: null` turns
        // its "no" into pending, so a CLAIMED third party's venue is asked rather than overruled.
        $accepted = $venue->isEditableBy($role->user)
            || $venue->autoAcceptsEventFrom($role->user, $role);

        $event->roles()->attach($venue->id, [
            'is_accepted' => $accepted ?: null,
        ]);

        return true;
    }

    protected function convertLocationToVenue(Role $role, string $location): ?Role
    {
        // Guard: cannot create venue without a user
        if (! $role->user_id) {
            Log::warning('Cannot create venue: role has no user', ['role_id' => $role->id]);

            return null;
        }

        $location = trim($location);

        if (! $location) {
            return null;
        }

        // Truncate location if it exceeds the address1 column limit
        if (strlen($location) > 255) {
            Log::warning('Import location truncated for venue creation', [
                'role_id' => $role->id,
                'original_length' => strlen($location),
            ]);
            $location = substr($location, 0, 255);
        }

        return DB::transaction(function () use ($role, $location) {
            $normFull = GeminiUtils::normalizeForMatch($location);
            // The first comma segment bridges "Patrick's, Caesarea" to a venue named "Patrick's".
            $normFirst = GeminiUtils::normalizeForMatch(trim(explode(',', $location)[0]));
            $names = array_values(array_unique(array_filter(
                [$normFull, $normFirst],
                fn ($n) => strlen($n) >= 2
            )));

            // Reuse an existing venue among the importing user's own venues, matched on the
            // normalized name/address. Rank claimed venues first so events attach to the real venue
            // rather than an auto-created stub. User::roles() bakes in orderBy('name'), so reorder()
            // before the ranking.
            //
            // Levels: owner/admin/follower, deliberately NOT viewer. The caller stamps the pivot
            // is_accepted from User::isMember(), which counts viewer - so matching a viewer's
            // read-only venue here would publish the event straight onto that venue's public page.
            // Leaving them on the create path keeps a viewer's import in their own stub instead.
            $venue = null;
            if ($names && $role->user) {
                $venue = $role->user->roles()
                    ->wherePivotIn('level', ['owner', 'admin', 'follower'])
                    ->where('roles.type', 'venue')
                    ->where(function ($q) use ($names, $normFull) {
                        $q->whereIn('roles.name_normalized', $names);
                        if ($normFull !== '') {
                            $q->orWhere('roles.address1_normalized', $normFull);
                        }
                    })
                    ->withCount('events')
                    ->reorder()
                    ->orderByRaw('CASE WHEN roles.email IS NOT NULL THEN 0 ELSE 1 END')
                    ->orderByDesc('events_count')
                    ->orderBy('roles.id')
                    ->first();
            }

            if ($venue) {
                return $venue;
            }

            // Create new venue with a unique subdomain
            // generateSubdomain already handles uniqueness, but retry in case of a race condition
            $subdomain = Role::generateSubdomain($location);
            $attempts = 0;
            while (Role::where('subdomain', $subdomain)->exists() && $attempts < 10) {
                $subdomain = Role::generateSubdomain($location.'-'.++$attempts);
            }

            $venue = new Role;
            $venue->type = 'venue';
            $venue->user_id = $role->user_id;
            $venue->subdomain = $subdomain;
            $venue->name = $location;
            $venue->address1 = $location;
            $venue->country_code = $role->country_code;
            $venue->save();

            $venue->members()->attach($role->user_id, ['level' => 'follower', 'created_at' => now()]);

            return $venue;
        });
    }
}
