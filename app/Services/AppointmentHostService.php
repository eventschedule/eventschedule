<?php

namespace App\Services;

use App\Jobs\SendQueuedEmail;
use App\Mail\AppointmentHostReleased;
use App\Models\AppointmentType;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Who an appointment type is booked with, and what happens when that person leaves.
 *
 * A type may name one host: an owner or admin of its schedule. Viewers are left out because the
 * Appointments tab is closed to them (RoleController::viewAdmin()), so a viewer host could never
 * see the bookings made with them. The host's other appointments, on any schedule, and their
 * days away on this one are what block the type's times (AppointmentService::busyIntervals());
 * the schedule's own events do not.
 *
 * Offering the choice is Enterprise, like the team itself. A schedule that drops from Enterprise
 * keeps its host types bookable (a booking is not team access) and loses only the Host select.
 */
class AppointmentHostService
{
    /** The levels a host may hold on the type's schedule. */
    public const LEVELS = ['owner', 'admin'];

    /**
     * Whether $userId may host a type on schedule $roleId: an owner or admin of it, on an account
     * an operator has not shut out (AccountBlockService), which must not be booked or mailed.
     */
    public static function isEligible(int $roleId, int $userId): bool
    {
        return RoleUser::where('role_id', $roleId)
            ->where('user_id', $userId)
            ->whereIn('level', self::LEVELS)
            ->whereExists(fn ($account) => $account->selectRaw('1')
                ->from('users')
                ->whereColumn('users.id', 'role_user.user_id')
                ->whereNull('users.blocked_at'))
            ->exists();
    }

    /**
     * The people a type on this schedule may be hosted by, by name.
     *
     * @return Collection<int, User>
     */
    public function candidates(Role $role): Collection
    {
        return $role->members()
            ->wherePivotIn('level', self::LEVELS)
            ->whereNull('users.blocked_at')
            ->get()
            ->values();
    }

    /**
     * Whether this schedule may choose hosts at all. Enterprise, as the team is; selfhost passes
     * through isEnterprise().
     */
    public static function offered(Role $role): bool
    {
        return $role->isEnterprise();
    }

    /**
     * $user stopped running $role (left, was removed, was made a viewer, deleted the account, or
     * was dropped by a transfer): every type they host there is switched off and marked, and the
     * owner is told. Their bookings keep them as host (a booking is the time two people agreed),
     * and the owner can move or cancel each from the Bookings list, which the mail links to.
     *
     * Returns how many types were switched off. Never throws: every caller is on its way to doing
     * something else the person asked for.
     */
    public function releaseHost(Role $role, User|int $user, bool $leaving = false): int
    {
        $userId = $user instanceof User ? (int) $user->id : (int) $user;

        try {
            // Someone who is still an owner or admin here is not leaving: a transfer that keeps
            // the previous owner as an admin, or a call made twice. An account being deleted is
            // still a member until the cascade runs, so that caller says so ($leaving).
            if (! $leaving && self::isEligible((int) $role->id, $userId)) {
                return 0;
            }

            $types = AppointmentType::where('role_id', $role->id)
                ->where('host_user_id', $userId)
                ->where('is_deleted', false)
                ->whereNull('host_released_at')
                ->get();

            if ($types->isEmpty()) {
                return 0;
            }

            $now = now();
            foreach ($types as $type) {
                $type->is_active = false;
                $type->host_released_at = $now;
                $type->save();
            }

            $this->tellOwner($role, $userId, $types);

            return $types->count();
        } catch (\Throwable $e) {
            report($e);

            return 0;
        }
    }

    /**
     * An account is being deleted: release it from every schedule it hosts on and does not own.
     * The schedules it owns go with it.
     */
    public function releaseEverywhere(User $user): void
    {
        $roleIds = AppointmentType::where('host_user_id', $user->id)
            ->where('is_deleted', false)
            ->whereNull('host_released_at')
            ->distinct()
            ->pluck('role_id');

        foreach (Role::whereIn('id', $roleIds)->where('user_id', '!=', $user->id)->get() as $role) {
            $this->releaseHost($role, $user, leaving: true);
        }
    }

    /**
     * Upcoming, live bookings on this schedule still made with $userId: the rows the Bookings
     * list shows under Upcoming (or, with $pending, under Pending) for that host, counted by the
     * same rules (RoleController::appointmentsTabData()), so a link that names a number opens a
     * list of that many.
     */
    public static function futureBookingCount(Role $role, int $userId, bool $pending = false): int
    {
        $waiting = fn ($q) => $q->selectRaw('1')
            ->from('event_role')
            ->whereColumn('event_role.event_id', 'events.id')
            ->whereColumn('event_role.role_id', 'events.creator_role_id')
            ->whereNull('event_role.is_accepted');

        return Sale::query()
            ->join('events', 'events.id', '=', 'sales.event_id')
            ->where('events.creator_role_id', $role->id)
            ->where('events.appointment_host_user_id', $userId)
            ->whereNotNull('events.appointment_type_id')
            ->where('sales.is_deleted', false)
            ->whereNotIn('sales.status', ['cancelled', 'refunded', 'expired'])
            ->where('events.is_cancelled', false)
            ->when(! $pending, fn ($q) => $q->where('events.starts_at', '>=', now('UTC')->format('Y-m-d H:i:s'))->whereNotExists($waiting))
            ->when($pending, fn ($q) => $q->whereExists($waiting))
            ->count();
    }

    /** @param  Collection<int, AppointmentType>  $types */
    protected function tellOwner(Role $role, int $hostId, Collection $types): void
    {
        $owner = $role->user;
        if (! $owner || ! $owner->email || is_demo_role($role)) {
            return;
        }

        $host = User::find($hostId);

        SendQueuedEmail::dispatch(
            new AppointmentHostReleased(
                $role,
                $host?->name ?: __('messages.appointments_host_former'),
                $types->pluck('name')->all(),
                self::futureBookingCount($role, $hostId),
                \App\Utils\UrlUtils::encodeId($hostId),
            ),
            $owner->email,
            null,
            $owner->language_code,
        );
    }
}
