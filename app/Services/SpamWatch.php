<?php

namespace App\Services;

use App\Models\Role;
use App\Models\Setting;
use App\Models\SpamClearance;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

/**
 * Accounts that may be spam (/admin/blocked#suspects): an account with one of the operator's
 * watched words ("paypal", "bitcoin") in the name or subdomain of a live schedule it owns, in the
 * name of an event it made, or in its own name or email address once it owns a live schedule.
 * The last condition is what keeps ticket buyers and followers off the list: they have accounts
 * too, and a buyer called "PayPal Buyer" or writing from coinbase.com can publish nothing.
 *
 * It decides nothing. A match is a reason to look, and the operator then blocks the account
 * (AccountBlockService) or marks it not spam (spam_clearances). Not spam is for good: an
 * account that runs a bitcoin meetup would otherwise be back on the list with every new event.
 * AdminAlertService counts what is left, so the row drains.
 *
 * A word matches anywhere in the text, in the database's own comparison, which ignores case and
 * accents ("Bítcoin" is caught); in a subdomain a space is also read as a hyphen. The words are a
 * setting (/admin/blocked); never saved, they are DEFAULT_WORDS, and an empty list watches
 * nothing.
 *
 * The scan is three full table scans, since a LIKE that starts with % cannot use an index, and
 * the alert row is counted on every admin page. So the ids it finds are cached under a key that
 * carries the words: fresh for ten minutes, then served for up to an hour while one process scans
 * again after its response (Cache::flexible), so no admin page but the very first waits on it.
 * Who is blocked, an admin or cleared is asked live, so a decision shows at once and the badge
 * and the list it links to read the same rows. A new match shows within ten minutes, or one
 * page load later when the admin pages have been idle for longer than that.
 */
class SpamWatch
{
    public const SETTING = 'spam_watch_words';

    /**
     * What a payment or crypto support scam calls itself. Legitimate uses get marked not spam.
     * A word is found inside other words, so none here may sit inside an ordinary name: "zelle"
     * was in Gazelle and Rozelle, and a Zelle support line says "helpline" or "customer service".
     */
    public const DEFAULT_WORDS = [
        'paypal',
        'bitcoin',
        'crypto',
        'coinbase',
        'binance',
        'metamask',
        'cash app',
        'cashapp',
        'venmo',
        'quickbooks',
        'customer service',
        'customer care',
        'helpline',
        'toll free',
    ];

    public const MAX_WORDS = 50;

    /** Shorter, a word is inside too many ordinary names to mean anything. */
    public const MIN_LENGTH = 3;

    public const MAX_LENGTH = 50;

    public const ACCOUNT = 'account';

    public const EMAIL = 'email';

    public const SCHEDULE = 'schedule';

    public const EVENT = 'event';

    /** How long a scan is fresh, and how long a stale one is served while the next runs. */
    private const FRESH_SECONDS = 600;

    private const STALE_SECONDS = 3600;

    /** @return list<string> */
    public static function words(): array
    {
        $stored = Setting::get(self::SETTING);

        return $stored === null ? self::DEFAULT_WORDS : self::parse((string) $stored);
    }

    /**
     * The words in what an operator typed: split on commas and lines, lowercased, spaces
     * collapsed, each once.
     *
     * @return list<string>
     */
    public static function parse(string $input): array
    {
        return collect(preg_split('/[,،，\r\n]+/u', mb_strtolower($input)) ?: [])
            ->map(fn ($word) => trim((string) preg_replace('/\s+/u', ' ', $word)))
            ->filter(fn ($word) => $word !== '')
            ->unique()
            ->values()
            ->all();
    }

    /** @param  list<string>  $words */
    public static function acceptable(array $words): bool
    {
        if (count($words) > self::MAX_WORDS) {
            return false;
        }

        foreach ($words as $word) {
            $length = mb_strlen($word);

            if ($length < self::MIN_LENGTH || $length > self::MAX_LENGTH) {
                return false;
            }
        }

        return true;
    }

    /**
     * Saved as typed, except that the default list is saved as nothing, so a later change to
     * DEFAULT_WORDS reaches an install that never changed it.
     *
     * @param  list<string>  $words
     */
    public static function saveWords(array $words, User $actor): void
    {
        $before = self::words();

        Setting::set(self::SETTING, $words === self::DEFAULT_WORDS ? null : implode(', ', $words));

        AuditService::log(
            AuditService::ADMIN_SETTINGS_UPDATE,
            $actor->id,
            null,
            null,
            [self::SETTING => implode(', ', $before)],
            [self::SETTING => implode(', ', $words)],
        );
    }

    /**
     * Whether an account can be on the list at all: never a blocked one, an administrator or the
     * demo. suspects() asks the same in SQL; the account's page asks this before it shows a card.
     */
    public static function canBeFlagged(User $user): bool
    {
        return ! $user->isBlocked() && ! $user->isAdmin() && $user->email !== DemoService::DEMO_EMAIL;
    }

    /** The accounts that may be spam now: matched, and not blocked, an admin, the demo or cleared. */
    public static function suspects(): Builder
    {
        return User::query()
            ->whereIntegerInRaw('users.id', self::matchedIds())
            ->whereNull('users.blocked_at')
            ->where('users.is_admin', false)
            ->where('users.email', '!=', DemoService::DEMO_EMAIL)
            ->whereNotExists(fn ($q) => $q->selectRaw('1')
                ->from('spam_clearances')
                ->whereColumn('spam_clearances.user_id', 'users.id'));
    }

    public static function count(): int
    {
        return self::suspects()->count();
    }

    /**
     * Every account a watched word turns up for, cached.
     *
     * @return list<int>
     */
    public static function matchedIds(): array
    {
        $words = self::words();

        if ($words === []) {
            return [];
        }

        return Cache::flexible(
            'spam_watch.ids.'.md5(implode("\n", $words)),
            [self::FRESH_SECONDS, self::STALE_SECONDS],
            fn () => self::scan($words),
            // Without seconds the refresh lock is taken for a day (the database store) or for
            // good (the file store), and a scan that dies holding it stops every later refresh.
            ['seconds' => 120],
        );
    }

    /**
     * Where the words are, for each of these accounts: the account's name and address first,
     * then its schedules, then its events (the newest), up to $limit, with how many there are
     * in all. Read live, for the rows on screen.
     *
     * @param  Collection<int, User>  $users
     * @return array<int, array{items: list<array{kind: string, text: string, word: ?string}>, total: int}>
     */
    public static function matches(Collection $users, int $limit = 3): array
    {
        $words = self::words();

        if ($words === [] || $users->isEmpty()) {
            return [];
        }

        $ids = $users->pluck('id')->map(fn ($id) => (int) $id)->all();
        $found = array_fill_keys($ids, ['items' => [], 'total' => 0]);

        $add = function (int $userId, string $kind, ?string $text) use (&$found, $words, $limit) {
            $found[$userId]['total']++;

            if (count($found[$userId]['items']) < $limit) {
                $found[$userId]['items'][] = ['kind' => $kind, 'text' => (string) $text, 'word' => self::wordIn((string) $text, $words)];
            }
        };

        // The database's comparison, not PHP's, and the scan's condition, so what is listed is
        // what the scan found.
        foreach (['name' => self::ACCOUNT, 'email' => self::EMAIL] as $column => $kind) {
            self::owners()
                ->whereIn('users.id', $ids)
                ->where(fn ($q) => self::whereContains($q, ['users.'.$column], $words))
                ->toBase()
                ->get(['users.id', 'users.'.$column])
                ->each(fn ($row) => $add((int) $row->id, $kind, $row->{$column}));
        }

        self::schedules($words)
            ->whereIn('roles.user_id', $ids)
            ->orderBy('roles.name')
            ->toBase()
            ->get(['roles.user_id', 'roles.name', 'roles.subdomain'])
            ->each(function ($row) use ($add, $words) {
                // The name, unless the word is only in the subdomain.
                $inName = self::wordIn((string) $row->name, $words) !== null;
                $inSubdomain = self::wordIn((string) $row->subdomain, $words) !== null;

                $add((int) $row->user_id, self::SCHEDULE, ! $inName && $inSubdomain ? $row->subdomain : $row->name);
            });

        // Counted in one query and listed per account, newest first: an account that runs a
        // weekly meetup can have hundreds of them.
        $eventCounts = self::events($words)
            ->whereIn('user_id', $ids)
            ->groupBy('user_id')
            ->selectRaw('user_id, COUNT(*) AS matched')
            ->pluck('matched', 'user_id');

        foreach ($eventCounts as $userId => $count) {
            $userId = (int) $userId;
            $room = $limit - count($found[$userId]['items']);

            $names = $room > 0
                ? self::events($words)->where('user_id', $userId)->orderByDesc('id')->limit($room)->pluck('name')
                : [];

            foreach ($names as $name) {
                $found[$userId]['items'][] = ['kind' => self::EVENT, 'text' => (string) $name, 'word' => self::wordIn((string) $name, $words)];
            }

            $found[$userId]['total'] += (int) $count;
        }

        return $found;
    }

    public static function clearance(User $user): ?SpamClearance
    {
        return SpamClearance::with('clearer')->where('user_id', $user->id)->first();
    }

    /** Not spam: the account is not flagged again. */
    public static function clear(User $user, User $actor): void
    {
        // createOrFirst: a second press racing the first meets the unique key and is answered
        // with the row the first one made, instead of a 500.
        $clearance = SpamClearance::createOrFirst(['user_id' => $user->id], ['cleared_by' => $actor->id]);

        if ($clearance->wasRecentlyCreated) {
            AuditService::log(AuditService::ADMIN_SPAM_CLEAR, $actor->id, 'App\\Models\\User', $user->id, null, null, 'Marked account '.$user->id.' not spam');
        }
    }

    /** Flag again: the account is back on the list while a word still matches. */
    public static function unclear(User $user, User $actor): void
    {
        if (SpamClearance::where('user_id', $user->id)->delete()) {
            AuditService::log(AuditService::ADMIN_SPAM_UNCLEAR, $actor->id, 'App\\Models\\User', $user->id, null, null, 'Flagged account '.$user->id.' again');
        }
    }

    /** What a match is in, as the pages name it. */
    public static function kindName(string $kind): string
    {
        return match ($kind) {
            self::ACCOUNT => __('messages.spam_watch_kind_account'),
            self::EMAIL => __('messages.email'),
            self::SCHEDULE => __('messages.schedule'),
            default => __('messages.event'),
        };
    }

    /**
     * The text, escaped, with the word marked. A long text is cut with the word in the middle.
     */
    public static function highlight(string $text, ?string $word, int $max = 120): HtmlString
    {
        $at = $word !== null && $word !== '' ? mb_stripos($text, $word) : false;
        $length = $at === false ? 0 : mb_strlen($word);

        if (mb_strlen($text) > $max) {
            $start = $at === false ? 0 : max(0, min($at - intdiv(max(0, $max - $length), 2), mb_strlen($text) - $max));
            $cut = mb_substr($text, $start, $max);
            $text = ($start > 0 ? '…' : '').$cut.($start + $max < mb_strlen($text) ? '…' : '');

            if ($at !== false) {
                $at = $at - $start + ($start > 0 ? 1 : 0);
            }
        }

        if ($at === false) {
            return new HtmlString(e($text));
        }

        return new HtmlString(
            e(mb_substr($text, 0, $at))
            .'<mark class="spam-word">'.e(mb_substr($text, $at, $length)).'</mark>'
            .e(mb_substr($text, $at + $length))
        );
    }

    /**
     * @param  list<string>  $words
     * @return list<int>
     */
    private static function scan(array $words): array
    {
        $accounts = self::owners()
            ->where(fn ($q) => self::whereContains($q, ['users.name', 'users.email'], $words))
            ->pluck('users.id');

        $schedules = self::schedules($words)->distinct()->pluck('roles.user_id');

        $events = self::events($words)->distinct()->pluck('user_id');

        return $accounts->merge($schedules)->merge($events)
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /** Accounts that own a live schedule: the only ones whose own name and address are read. */
    private static function owners(): Builder
    {
        return User::query()->whereHas('createdRoles', fn ($role) => $role->owned()->where('roles.is_deleted', false));
    }

    /** Live schedules an account owns whose name or subdomain holds a word. */
    private static function schedules(array $words): Builder
    {
        return Role::query()
            ->owned()
            ->where('roles.is_deleted', false)
            ->where(function ($q) use ($words) {
                self::whereContains($q, ['roles.name'], $words);
                self::whereContains($q, ['roles.subdomain'], self::withHyphens($words));
            });
    }

    /**
     * The words as a subdomain writes them too: it has no spaces, so "cash app" is cash-app there.
     *
     * @param  list<string>  $words
     * @return list<string>
     */
    private static function withHyphens(array $words): array
    {
        return array_values(array_unique(array_merge($words, array_map(fn ($word) => str_replace(' ', '-', $word), $words))));
    }

    /** Events an account made whose name holds a word. */
    private static function events(array $words): \Illuminate\Database\Query\Builder
    {
        return DB::table('events')
            ->whereNotNull('user_id')
            ->where(fn ($q) => self::whereContains($q, ['name'], $words));
    }

    private static function whereContains($query, array $columns, array $words): void
    {
        foreach ($columns as $column) {
            foreach ($words as $word) {
                $query->orWhere($column, 'like', '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $word).'%');
            }
        }
    }

    /**
     * The first word in the text, as PHP compares (and as a subdomain writes it), or null when
     * only the database's comparison found one. It picks what is marked; it decides nothing.
     */
    private static function wordIn(string $text, array $words): ?string
    {
        foreach (self::withHyphens($words) as $word) {
            if (mb_stripos($text, $word) !== false) {
                return $word;
            }
        }

        return null;
    }
}
