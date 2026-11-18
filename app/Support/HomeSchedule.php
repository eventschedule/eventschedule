<?php

namespace App\Support;

use App\Models\DismissedNextStep;
use App\Models\Event;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * The schedule a selfhosted install shows at its own address.
 *
 * A selfhost install puts every schedule under a path (example.com/example), and most run one
 * schedule, so the address people know is the bare one. With a home schedule chosen at
 * /admin/settings#home-schedule, the site's root serves that schedule's page
 * (ServeHomeSchedule rewrites "/" to "/{subdomain}" before routing, so the page goes through
 * exactly the route and middleware its own path does), and getGuestUrl() hands out the root:
 * the canonical tag, shares, mail, QR codes, the sitemap and the network listing all name it.
 *
 * Only the schedule's own page moves. Its events keep their path (example.com/example/show):
 * a bare example.com/show would compete with the app's own addresses (/login, /dashboard, a
 * future page), and the old schedule address keeps working, so no link in the wild breaks.
 *
 * Selfhost only. A hosted install already gives every schedule its own host and custom domains,
 * and the nexus's root is the marketing site.
 */
class HomeSchedule
{
    public const SETTING = 'home_schedule_id';

    /** Schedules offered in the picker. A selfhost install with more is a SaaS, which has hosts. */
    public const CHOICES_LIMIT = 500;

    public static function available(): bool
    {
        return ! config('app.hosted') && ! config('app.is_nexus');
    }

    /** Container key of the per-process memo of id(). */
    private const MEMO = 'home_schedule.memo';

    /** How long a process trusts its memo: a long-running worker sees a change within a minute. */
    private const MEMO_SECONDS = 60;

    /**
     * The chosen schedule's id. Asked by every getGuestUrl(), so a list of a few hundred events
     * asks it a few hundred times, and Setting::get() reads the cache store each time: so it is
     * kept on the container (never a static, which outlives a test's app) for a minute.
     */
    public static function id(): ?int
    {
        if (! self::available()) {
            return null;
        }

        $app = app();
        $now = time();

        if ($app->bound(self::MEMO)) {
            [$id, $at] = $app->make(self::MEMO);

            if ($now - $at < self::MEMO_SECONDS) {
                return $id;
            }
        }

        $id = (int) Setting::get(self::SETTING);
        $id = $id > 0 ? $id : null;

        $app->instance(self::MEMO, [$id, $now]);

        return $id;
    }

    public static function is(Role $role): bool
    {
        $id = self::id();

        return $id !== null && (int) $role->id === $id;
    }

    /**
     * The chosen schedule while it can still be shown: not deleted, with an address and an owner. Null otherwise,
     * and the root goes back to what it did before (the sign-in page).
     */
    public static function role(): ?Role
    {
        $id = self::id();

        if ($id === null) {
            return null;
        }

        // The same rule as the picker, or the card could read "None" while the root still served
        // a schedule, and an unrelated Save there would switch it off.
        return self::live()->whereKey($id)->first();
    }

    /**
     * The schedule the dashboard suggests showing at the site's address, or null for no banner.
     *
     * For an admin of a selfhost install that has exactly ONE schedule and has not chosen a home
     * schedule: the install this option was made for, told about it once (the first dashboard
     * after the update that brought it) until they dismiss it or choose. With two or more
     * schedules which one belongs at the root is not obvious, and the settings card is there.
     */
    public static function promptFor(?User $user): ?Role
    {
        if (! $user || ! self::available() || is_demo_mode() || ! $user->isAdmin() || self::id() !== null) {
            return null;
        }

        $schedules = self::live()->limit(2)->get(['id', 'name', 'subdomain']);

        if ($schedules->count() !== 1) {
            return null;
        }

        $role = $schedules->first();

        $dismissed = DismissedNextStep::where('user_id', $user->id)
            ->where('role_id', $role->id)
            ->where('step_type', DismissedNextStep::HOME_SCHEDULE)
            ->exists();

        return $dismissed ? null : $role;
    }

    /** What the settings card offers: every live schedule with an owner, by name. */
    public static function choices(): Collection
    {
        return self::live()
            ->orderBy('name')
            ->limit(self::CHOICES_LIMIT)
            ->get(['id', 'name', 'subdomain']);
    }

    /** Schedules that can be shown: not deleted, with an address and an owner. */
    private static function live()
    {
        return Role::where('is_deleted', false)
            ->whereNotNull('subdomain')
            ->whereNotNull('user_id');
    }

    /**
     * Make $roleId the home schedule (null for none).
     *
     * A schedule's address is part of what the network lists, so the events of the schedule that
     * moved, both ways, are sent again with it. A query-builder update, which fires no events.
     * events() and not ownEvents() on purpose: a listing credits the schedule that shares it,
     * which can be one that merely lists the event, and this only clears sync markers.
     */
    public static function set(?int $roleId): void
    {
        $previous = self::id();

        Setting::set(self::SETTING, $roleId ? (string) $roleId : null);
        app()->forgetInstance(self::MEMO);

        foreach (array_filter(array_unique([$previous, $roleId])) as $id) {
            $role = Role::find($id);

            if (! $role) {
                continue;
            }

            Event::whereIn('id', $role->events()->pluck('events.id'))
                ->where(fn ($q) => $q->whereNotNull('federated_at')->orWhereNotNull('federated_skipped_at'))
                ->toBase()
                ->update(['federated_at' => null, 'federated_skipped_at' => null]);
        }
    }
}
