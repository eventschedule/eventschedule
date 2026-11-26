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
 *
 * Beside the words, four signs that need no word (signalIn(), throwawayEmail()), read only for
 * what was made or renamed in the last SIGNAL_DAYS days (by updated_at: an old schedule renamed
 * into a scam counts as new): a phone number in a schedule's or an event's name,
 * a charge written out ("$234.99 USD"), letters from another alphabet standing in for digits or
 * Latin letters ("$5ᒣ9", "PаyPal" with a Cyrillic а), and an owner's address at a throwaway
 * mail service or a Gmail address padded with groups of digits ("name.7.46.6@"). Every account
 * blocked on the nexus by 2026-11 was a support or billing scam whose schedule or event was
 * named after the scam itself, and most of them used no word on the list: a support number and
 * an amount are what they cannot leave out. Recent only, because a real schedule does put a
 * number in a name now and then and the list must not fill with years of those; an account
 * an operator looked at is cleared for good either way. They watch while the words do: an empty
 * list still watches nothing.
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
        // The support and billing scams the nexus blocked in 2026-10 and 2026-11, and the
        // document phishing after them. "billing" alone is not here: it is in Billings.
        'customer support',
        'geek squad',
        'auto-renewal',
        'renewal notice',
        'billing invoice',
        'norton',
        'mcafee',
        'secure document',
        'docusign',
    ];

    /** How far back the signs that need no word look (signalIn(), throwawayEmail()). */
    public const SIGNAL_DAYS = 90;

    public const SIGNAL_PHONE = 'phone';

    public const SIGNAL_AMOUNT = 'amount';

    public const SIGNAL_LOOKALIKE = 'lookalike';

    public const SIGNAL_THROWAWAY = 'throwaway';

    /** What a scam says beside a bare number it wants called. */
    private const OFFERS_A_NUMBER = '/\\b(?:support|call|contact|phone|tel|helpline|hotline|customer|toll|whatsapp|reach us)\\b|☎|📞/iu';

    /** CC0, from github.com/disposable-email-domains/disposable-email-domains. */
    public const THROWAWAY_DOMAINS_FILE = 'data/disposable-email-domains.txt';

    /** @var array<string, true>|null */
    private static ?array $throwawayDomains = null;

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
     *                                        A sign that needs no word (signalIn()) is listed after the words, with `signal` naming it
     *                                        and `word` null.
     * @return array<int, array{items: list<array{kind: string, text: string, word: ?string, signal: ?string}>, total: int}>
     */
    public static function matches(Collection $users, int $limit = 3, bool $withSignals = true): array
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
                $found[$userId]['items'][] = ['kind' => $kind, 'text' => (string) $text, 'word' => self::wordIn((string) $text, $words), 'signal' => null];
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
                $found[$userId]['items'][] = ['kind' => self::EVENT, 'text' => (string) $name, 'word' => self::wordIn((string) $name, $words), 'signal' => null];
            }

            $found[$userId]['total'] += (int) $count;
        }

        // The signs, after the words. Read the same way the scan reads them (signalRows()), so
        // what is listed is what the scan found.
        foreach ($withSignals ? self::signalRows($ids) : [] as [$userId, $kind, $text, $signal]) {
            $found[$userId]['total']++;

            if (count($found[$userId]['items']) < $limit) {
                $found[$userId]['items'][] = ['kind' => $kind, 'text' => $text, 'word' => null, 'signal' => $signal];
            }
        }

        return $found;
    }

    /**
     * What a sign is called on the pages.
     */
    public static function signalName(string $signal): string
    {
        return match ($signal) {
            self::SIGNAL_PHONE => __('messages.spam_watch_signal_phone'),
            self::SIGNAL_AMOUNT => __('messages.spam_watch_signal_amount'),
            self::SIGNAL_LOOKALIKE => __('messages.spam_watch_signal_lookalike'),
            default => __('messages.spam_watch_signal_throwaway'),
        };
    }

    /**
     * The sign a name carries, or null: a phone number, a charge written out, or letters from
     * another alphabet standing in for digits or Latin letters. Formats a scam's support line
     * takes ("+1 (863) 316-4088", "+1 813 934 5805", "845 573 3428"), never a date or a year:
     * "2026-11-26 19:00" has no run of three, three and four.
     */
    public static function signalIn(string $text): ?string
    {
        if (trim($text) === '') {
            return null;
        }

        // A support line: "+1 (863) 316-4088" and "(863) 316-4088" on their own; a bare
        // "845 573 3428" only beside a word that offers it, or "Room 101 102 2026" and a French
        // "100 000 2026" would be phone numbers too.
        if (preg_match('/\+\s?\d(?:[\s().\-]*\d){9,14}/u', $text)
            || preg_match('/\(\d{3}\)\s*\d{3}[\s.\-]?\d{4}/u', $text)
            || (preg_match('/(?<![\d\-.\/])(?:1[\s.\-])?\d{3}[\s.\-]\d{3}[\s.\-]\d{4}(?![\d\-.\/])/u', $text)
                && preg_match(self::OFFERS_A_NUMBER, $text))) {
            return self::SIGNAL_PHONE;
        }

        // A charge, not a price: "$234.99 USD" beside "charge", "payment" or "invoice". A
        // workshop's "49.00 USD" says none of those.
        if (preg_match('/\d[\d,]*\.\d{2}\s*(?:USD|EUR|GBP|CAD|AUD)\b|[$€£]\s?\d[\d,]*\.\d{2}/iu', $text)
            && preg_match('/\b(?:charge[ds]?|payment|purchase|invoice|refund|renewal|subscription|billing|transaction)\b/iu', $text)) {
            return self::SIGNAL_AMOUNT;
        }

        // Canadian syllabics standing in for a digit, right beside one or a "$": "$5ᒣ9",
        // "+181ᗱ9ᗱ45805". Syllabics in words (an Inuktitut name beside an English one) and
        // Japanese full-width numbers are somebody's language, not a disguise.
        if (preg_match('/[0-9$+][\x{1400}-\x{167F}]|[\x{1400}-\x{167F}][0-9]/u', $text)) {
            return self::SIGNAL_LOOKALIKE;
        }

        // A Cyrillic letter hidden in a Latin word ("PаyPal"): Latin letters outnumber the
        // Cyrillic ones. The other way round is Ukrainian typed with a Latin "i" ("Вечiр"),
        // which is everyday writing. Greek is left out: a fraternity writes its letters beside
        // Latin ones on purpose.
        if (preg_match_all('/[\p{L}\p{M}]+/u', $text, $words)) {
            foreach ($words[0] as $word) {
                $cyrillic = preg_match_all('/\p{Cyrillic}/u', $word);
                $latin = preg_match_all('/\p{Latin}/u', $word);

                if ($cyrillic > 0 && $latin > $cyrillic) {
                    return self::SIGNAL_LOOKALIKE;
                }
            }
        }

        return null;
    }

    /**
     * Whether an address is at a throwaway mail service, or is a Gmail address padded with two
     * or more groups of digits after dots ("name.7.46.6@gmail.com"), which Gmail ignores and a
     * script uses to make a new account from one inbox.
     */
    public static function throwawayEmail(?string $email): bool
    {
        $email = mb_strtolower(trim((string) $email));
        $at = strrpos($email, '@');

        if ($at === false) {
            return false;
        }

        $local = explode('+', substr($email, 0, $at), 2)[0];
        $domain = substr($email, $at + 1);

        if (in_array($domain, ['gmail.com', 'googlemail.com'], true) && preg_match('/(\.\d{1,3}){2,}$/', $local)) {
            return true;
        }

        return isset(self::throwawayDomains()[$domain]);
    }

    /** @return array<string, true> */
    private static function throwawayDomains(): array
    {
        if (self::$throwawayDomains === null) {
            $path = resource_path(self::THROWAWAY_DOMAINS_FILE);
            $lines = is_readable($path) ? (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: []) : [];

            self::$throwawayDomains = array_fill_keys(array_map(fn ($line) => mb_strtolower(trim($line)), $lines), true);
        }

        return self::$throwawayDomains;
    }

    /**
     * Every sign in what these accounts (or, with null, every account) made or changed in the
     * last SIGNAL_DAYS days, as [user id, kind, text, signal]: their live schedules' names, their
     * events' names, and an owner's own address. The SQL only narrows by date and to text with a
     * digit or a letter outside plain ASCII; signalIn() decides.
     *
     * @param  list<int>|null  $userIds
     * @return \Generator<int, array{0: int, 1: string, 2: string, 3: string}>
     */
    private static function signalRows(?array $userIds): \Generator
    {
        $since = now()->subDays(self::SIGNAL_DAYS);
        // A digit, or a character outside plain ASCII (a lookalike never is plain ASCII: its
        // bytes outnumber its characters), so most of a period's names are not read at all.
        $worthReading = "(name REGEXP '[0-9]' OR CHAR_LENGTH(name) <> LENGTH(name))";

        $schedules = Role::query()
            ->owned()
            ->where('roles.is_deleted', false)
            ->where('roles.updated_at', '>=', $since)
            ->whereRaw(str_replace('name', 'roles.name', $worthReading))
            ->when($userIds !== null, fn ($q) => $q->whereIn('roles.user_id', $userIds)->orderBy('roles.name'))
            ->toBase()
            ->select(['roles.user_id', 'roles.name']);

        foreach ($schedules->cursor() as $row) {
            if ($signal = self::signalIn((string) $row->name)) {
                yield [(int) $row->user_id, self::SCHEDULE, (string) $row->name, $signal];
            }
        }

        $events = DB::table('events')
            ->whereNotNull('user_id')
            ->where('updated_at', '>=', $since)
            ->whereRaw($worthReading)
            // Ordered only for the accounts on screen: the whole scan reads by created_at.
            ->when($userIds !== null, fn ($q) => $q->whereIn('user_id', $userIds)->orderByDesc('id'))
            ->select(['user_id', 'name']);

        foreach ($events->cursor() as $row) {
            if ($signal = self::signalIn((string) $row->name)) {
                yield [(int) $row->user_id, self::EVENT, (string) $row->name, $signal];
            }
        }

        $owners = self::owners()
            ->where('users.created_at', '>=', $since)
            ->when($userIds !== null, fn ($q) => $q->whereIn('users.id', $userIds))
            ->toBase()
            ->select(['users.id', 'users.email']);

        foreach ($owners->cursor() as $row) {
            if (self::throwawayEmail($row->email)) {
                yield [(int) $row->id, self::EMAIL, (string) $row->email, self::SIGNAL_THROWAWAY];
            }
        }
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

        // The words paused its email (AudienceMailGuard): not spam lets it back on.
        AudienceMailGuard::liftSpamWatchPause($user);
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

        $signals = collect(iterator_to_array((function () {
            foreach (self::signalRows(null) as [$userId]) {
                yield $userId;
            }
        })(), false));

        return $accounts->merge($schedules)->merge($events)->merge($signals)
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
     * Whether a watched word turns up for this account NOW: the scan's own conditions, read live
     * for this one account rather than from the cached ids, so a word added a moment ago or a
     * schedule renamed a moment ago counts at once. Not for a blocked account, an administrator,
     * the demo or an account marked not spam. AudienceMailGuard asks it before every send.
     */
    public static function flagsNow(User $user): bool
    {
        if (! self::canBeFlagged($user) || self::words() === []) {
            return false;
        }

        if (SpamClearance::where('user_id', $user->id)->exists()) {
            return false;
        }

        // The words only. A sign (signalIn()) is a reason for an operator to look and nothing
        // more: it is far less certain than a word, and a pause sticks until somebody lifts it.
        return (self::matches(collect([$user]), 1, false)[$user->id]['total'] ?? 0) > 0;
    }

    /**
     * The first watched word in a piece of text someone wrote (a newsletter's subject or body),
     * or null. Case and accents are ignored, as the database's comparison ignores them for the
     * scan: "Bítcoin" is bitcoin here too. Letters of other scripts are left as they are.
     */
    public static function wordInText(string $text): ?string
    {
        $words = self::words();

        if ($words === [] || trim($text) === '') {
            return null;
        }

        $plain = self::folded($text);

        foreach ($words as $word) {
            if (str_contains($plain, self::folded($word))) {
                return $word;
            }
        }

        return null;
    }

    /** Lower case with the accents taken off, and every run of spaces one space. */
    private static function folded(string $text): string
    {
        if (class_exists(\Normalizer::class)) {
            $text = (string) \Normalizer::normalize($text, \Normalizer::FORM_D);
            $text = (string) preg_replace('/\p{Mn}+/u', '', $text);
        }

        return (string) preg_replace('/\s+/u', ' ', mb_strtolower($text));
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
