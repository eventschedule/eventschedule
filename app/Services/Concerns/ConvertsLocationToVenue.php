<?php

namespace App\Services\Concerns;

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

            // Reuse an existing venue among the importing user's own venues (owned or followed),
            // matched on the normalized name/address. Rank claimed venues first so events attach to
            // the real venue rather than an auto-created stub. User::roles() bakes in orderBy('name'),
            // so reorder() before the ranking.
            $venue = null;
            if ($names && $role->user) {
                $venue = $role->user->roles()
                    ->wherePivotIn('level', ['owner', 'follower'])
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
