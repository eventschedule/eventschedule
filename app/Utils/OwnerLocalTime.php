<?php

namespace App\Utils;

use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;

/**
 * "Now" for an owner we are about to email, in their own timezone.
 *
 * The scheduled owner mail (activation nudges, the weekly digest) runs hourly and sends to each
 * owner only inside their local morning, rather than at one UTC hour that is midnight somewhere.
 * An owner's zone is users.timezone, then their own first schedule's, then the app's. A value PHP does not
 * recognise falls through rather than throwing: one bad row must not stop a run for everyone.
 */
class OwnerLocalTime
{
    /** First and last local hour (inclusive) that counts as morning. Three hours, so one missed
     * hourly tick does not push an owner to the next day. */
    public const MORNING_FROM = 9;

    public const MORNING_TO = 11;

    /**
     * The owner's zone: users.timezone, then their own lowest-id live schedule's, then the app's.
     *
     * Deliberately NOT "the schedule this email is about": the nudges and the digest must agree
     * on which day it is for an owner (Monday is the digest's), and an owner with schedules in
     * two zones and no zone of their own would otherwise have two Mondays.
     */
    public static function timezone(User $user): string
    {
        $candidates = [$user->timezone];

        if (! $user->timezone || ! self::isValid($user->timezone)) {
            $candidates[] = Role::where('user_id', $user->id)
                ->where('is_deleted', false)
                ->orderBy('id')
                ->value('timezone');
        }

        $candidates[] = config('app.timezone');

        foreach ($candidates as $zone) {
            if ($zone && self::isValid($zone)) {
                return $zone;
            }
        }

        return 'UTC';
    }

    public static function now(User $user): Carbon
    {
        return Carbon::now(self::timezone($user));
    }

    public static function isMorning(User $user): bool
    {
        $hour = self::now($user)->hour;

        return $hour >= self::MORNING_FROM && $hour <= self::MORNING_TO;
    }

    private static function isValid(string $zone): bool
    {
        try {
            new \DateTimeZone($zone);

            return true;
        } catch (\Exception) {
            return false;
        }
    }
}
