<?php

namespace App\Utils;

use App\Models\User;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;

/**
 * Whether a person is at the other end of the support chat.
 *
 * Two independent conditions, both of which must hold before a visitor or customer is told
 * someone is there:
 *
 * - ONLINE: the admin switched chat on and has confirmed it within the last hour. The AP asks
 *   "Still available for chat?" every CONFIRM_EVERY minutes and an unanswered prompt lapses
 *   GRACE minutes later. This replaced a fixed four-hour cache TTL that nobody remembered to
 *   switch off before it ran out.
 * - PRESENT: an AP tab of THAT admin's has pinged within AWAY_AFTER minutes. Closing the laptop
 *   or quitting the browser stops the pings, so the WP widget disappears within minutes instead
 *   of advertising "Online now" for up to an hour with nobody there.
 *
 * The admin who switched chat on is "the agent": visitors see their name and photo, only their
 * pings count as presence, and only they are asked the hourly question. Another admin's open tab
 * must not keep someone else looking available.
 *
 * isAvailable() is the conjunction, and is what every visitor- or customer-facing surface reads.
 * isOnline() alone drives the admin's own UI (the toggle and the hourly prompt), because an admin
 * looking at the AP is by definition present.
 *
 * Expiry is computed from the stored timestamps rather than trusted to the cache TTL, which is
 * only garbage collection: a file-store TTL is rounded, and a test can travel() past a timestamp
 * but not past a TTL the store already wrote.
 */
class SupportPresence
{
    public const KEY = 'support_presence';

    public const HEARTBEAT_KEY = 'support_presence_heartbeat';

    /** The key this class replaced: a bare `true` with a fixed four-hour TTL. */
    public const LEGACY_KEY = 'support_available';

    public const CONFIRM_EVERY_MINUTES = 60;

    public const GRACE_MINUTES = 10;

    public const AWAY_AFTER_MINUTES = 5;

    public static function goOnline(User $user): void
    {
        self::locked(function () use ($user) {
            $now = now()->getTimestamp();

            self::write([
                'user_id' => $user->id,
                'confirmed_at' => $now,
                'since' => $now,
            ]);
        });

        self::heartbeat($user);
        Cache::forget(self::LEGACY_KEY);
    }

    /**
     * Restart the hourly clock. Deliberately a no-op once the window has lapsed (a stale tab
     * answering a prompt the admin never saw must not quietly put them back online), and for
     * anyone but the agent.
     *
     * Read and written under the same lock as goOffline(): without it a confirm that read the
     * state just before another tab switched chat off would write it straight back.
     */
    public static function confirm(User $user): bool
    {
        return self::locked(function () use ($user) {
            $data = self::data();

            if (! $data || (int) $data['user_id'] !== (int) $user->id || ! self::isOnline()) {
                return false;
            }

            $data['confirmed_at'] = now()->getTimestamp();
            self::write($data);

            return true;
        });
    }

    public static function goOffline(): void
    {
        self::locked(function () {
            $userId = self::data()['user_id'] ?? null;

            Cache::forget(self::KEY);
            Cache::forget(self::LEGACY_KEY);

            if ($userId) {
                Cache::forget(self::heartbeatKey((int) $userId));
            }
        });
    }

    public static function heartbeat(User $user): void
    {
        Cache::put(self::heartbeatKey($user->id), now()->getTimestamp(), now()->addMinutes(self::AWAY_AFTER_MINUTES + 5));
    }

    public static function isOnline(): bool
    {
        $data = self::data();

        return $data && now()->getTimestamp() < self::expiresAt($data);
    }

    public static function isPresent(): bool
    {
        $data = self::data();

        if (! $data) {
            return false;
        }

        $beat = Cache::get(self::heartbeatKey((int) $data['user_id']));

        return is_numeric($beat) && now()->getTimestamp() - (int) $beat < self::AWAY_AFTER_MINUTES * 60;
    }

    public static function isAvailable(): bool
    {
        return self::isOnline() && self::isPresent();
    }

    /**
     * Whether $user is the admin who switched chat on.
     */
    public static function isAgent(?User $user): bool
    {
        $data = self::data();

        return $user && $data && (int) $data['user_id'] === (int) $user->id;
    }

    /**
     * Relative seconds rather than timestamps, so a browser whose clock disagrees with the
     * server's still counts down to the right moment.
     *
     * With a $viewer, also says whether they are the agent: only the agent is asked the hourly
     * question, and another admin is told who is online instead.
     */
    public static function state(?User $viewer = null): array
    {
        $data = self::data();
        $online = self::isOnline();
        $now = now()->getTimestamp();

        return [
            'online' => $online,
            'available' => $online && self::isPresent(),
            'confirm_in' => $online ? max(0, (int) $data['confirmed_at'] + self::CONFIRM_EVERY_MINUTES * 60 - $now) : null,
            'expires_in' => $online ? max(0, self::expiresAt($data) - $now) : null,
            'is_agent' => $online && self::isAgent($viewer),
            'agent_name' => $online ? (self::agent()['name'] ?? null) : null,
        ];
    }

    /**
     * The person a visitor is chatting with: whoever switched chat on, else the first admin.
     */
    public static function agentUser(): ?User
    {
        $data = self::data();
        $user = $data ? User::find($data['user_id']) : null;

        return $user ?? self::primaryAdmin();
    }

    /**
     * The platform's first admin account. Every new chat message is emailed here, whoever is
     * online, so there is always one inbox that holds every conversation.
     */
    public static function primaryAdmin(): ?User
    {
        return User::where('is_admin', true)->orderBy('id')->first();
    }

    public static function agent(): ?array
    {
        return self::describe(self::agentUser());
    }

    /**
     * How a visitor sees an admin: first name, photo, initials.
     */
    public static function describe(?User $user): ?array
    {
        if (! $user) {
            return null;
        }

        // firstName() answers 'there' for a nameless account, which is a greeting, not a name.
        $name = $user->name ? $user->firstName() : 'Event Schedule';

        $initials = collect(preg_split('/\s+/u', trim((string) ($user->name ?: 'Event Schedule'))))
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');

        return [
            'name' => $name,
            'avatar_url' => $user->profile_image_url ?: null,
            'initials' => $initials ?: 'ES',
        ];
    }

    private static function heartbeatKey(int $userId): string
    {
        return self::HEARTBEAT_KEY.'_'.$userId;
    }

    private static function data(): ?array
    {
        $data = Cache::get(self::KEY);

        return is_array($data) && isset($data['confirmed_at'], $data['user_id']) ? $data : null;
    }

    private static function expiresAt(array $data): int
    {
        return (int) $data['confirmed_at'] + (self::CONFIRM_EVERY_MINUTES + self::GRACE_MINUTES) * 60;
    }

    private static function write(array $data): void
    {
        Cache::put(self::KEY, $data, now()->addMinutes(self::CONFIRM_EVERY_MINUTES + self::GRACE_MINUTES + 5));
    }

    /**
     * Serialises the read-modify-write paths. Waits briefly, and if the lock still cannot be had
     * runs anyway rather than failing: every caller is a person clicking something (or replying
     * to a customer), and an error there is worse than the rare race the lock guards against.
     */
    private static function locked(callable $callback)
    {
        try {
            return Cache::lock(self::KEY.'_lock', 5)->block(3, $callback);
        } catch (LockTimeoutException $e) {
            report($e);

            return $callback();
        }
    }
}
