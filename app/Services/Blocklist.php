<?php

namespace App\Services;

use App\Models\BlocklistEntry;
use App\Models\User;
use App\Utils\RealtimeTracker;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\IpUtils;

/**
 * The operator's list of what a NEW account is refused for (/admin/blocked): an email address,
 * a whole email domain, or a network address.
 *
 * It is asked where an account a person can sign in to is made (the sign-up form and its code,
 * Google and Facebook, the API's register, the account the guest forms offer) and where an
 * account changes its address. It is not asked at sign-in: an account that already exists is
 * shut out by blocking it (AccountBlockService), and a list entry never touches one. It is not
 * asked at checkout or on a newsletter form either, where no such account is made.
 *
 * Three things here are load-bearing:
 *
 * - The address a visitor comes from is RealtimeTracker::clientIp(), never a bare
 *   $request->ip() and never audit_logs.ip_address. Behind Cloudflare the socket's peer can be
 *   an edge address that thousands of visitors share, and refusing one refuses them all.
 * - An address is compared in its canonical form (canonicalEmail()): a "+tag", and for Gmail a
 *   dot or the googlemail.com spelling, do not make a new address. A domain entry covers its
 *   subdomains.
 * - A lookup that cannot be made (the tables are not there yet in the middle of an update)
 *   refuses nobody. Sign-up must not depend on this list being readable.
 */
class Blocklist
{
    public const EMAIL = 'email';

    public const DOMAIN = 'domain';

    public const IP = 'ip';

    public const TYPES = [self::EMAIL, self::DOMAIN, self::IP];

    /** The widest range one entry may name. Wider, a single line refuses a whole provider. */
    private const WIDEST_V4 = 16;

    private const WIDEST_V6 = 32;

    /** An IPv6 visitor is handed a /64 and moves about inside it, so that is what is refused. */
    private const V6_VISITOR = 64;

    /**
     * The entry that refuses this sign-up, or null. A refusal is counted on the entry.
     *
     * Pass the request where the account is being made from a browser or an API call, so the
     * address it comes from is checked too; leave it out where only the email is in question
     * (an account changing its address).
     */
    public static function refusal(?string $email, ?Request $request = null): ?BlocklistEntry
    {
        try {
            $entry = (is_string($email) && $email !== '' ? self::forEmail($email) : null)
                ?? ($request ? self::forAddress(self::address($request)) : null);
        } catch (QueryException $e) {
            report($e);

            return null;
        }

        if ($entry) {
            self::count($entry);
        }

        return $entry;
    }

    /** The address a request comes from, as every check and every record here reads it. */
    public static function address(Request $request): ?string
    {
        $ip = RealtimeTracker::clientIp($request);

        return filter_var($ip, FILTER_VALIDATE_IP) !== false ? $ip : null;
    }

    public static function forEmail(string $email): ?BlocklistEntry
    {
        $canonical = self::canonicalEmail($email);

        if ($canonical === null) {
            return null;
        }

        $domains = self::domainChain(self::asciiDomain(substr(strrchr(mb_strtolower(trim($email)), '@'), 1)));

        return BlocklistEntry::query()
            ->where(fn ($q) => $q->where('type', self::EMAIL)->where('match_key', $canonical))
            ->orWhere(fn ($q) => $q->where('type', self::DOMAIN)->whereIn('match_key', $domains))
            ->orderBy('id')
            ->first();
    }

    public static function forAddress(?string $ip): ?BlocklistEntry
    {
        if (! $ip || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return null;
        }

        // Ranges cannot be matched in SQL without storing them as numbers, and the list is an
        // operator's, a few hundred lines at most, read only when an account is being made.
        foreach (BlocklistEntry::where('type', self::IP)->orderBy('id')->get() as $entry) {
            if (IpUtils::checkIp($ip, $entry->match_key)) {
                return $entry;
            }
        }

        return null;
    }

    /**
     * The form two spellings of one mailbox share: lower case, without a "+tag", and for Gmail
     * without dots and under gmail.com. Null for something that is not an address.
     */
    public static function canonicalEmail(string $email): ?string
    {
        $email = mb_strtolower(trim($email));
        $at = strrpos($email, '@');

        if ($at === false || $at === 0 || $at === strlen($email) - 1) {
            return null;
        }

        $local = substr($email, 0, $at);
        $domain = self::asciiDomain(substr($email, $at + 1));

        $plus = strpos($local, '+');
        if ($plus !== false && $plus > 0) {
            $local = substr($local, 0, $plus);
        }

        if (in_array($domain, ['gmail.com', 'googlemail.com'], true)) {
            $local = str_replace('.', '', $local);
            $domain = 'gmail.com';
        }

        return $local === '' ? null : $local.'@'.$domain;
    }

    /**
     * What to refuse so that the visitor behind this address is refused: the address itself, or
     * for IPv6 the /64 it sits in.
     */
    public static function visitorRange(?string $ip): ?string
    {
        $packed = is_string($ip) ? @inet_pton($ip) : false;

        if ($packed === false) {
            return null;
        }

        return strlen($packed) === 16
            ? inet_ntop(self::mask($packed, self::V6_VISITOR)).'/'.self::V6_VISITOR
            : inet_ntop($packed);
    }

    /**
     * Put something on the list. An entry that is already there is returned as it is.
     *
     * @throws \InvalidArgumentException whose message is the key of the sentence to show
     */
    public static function add(string $type, string $value, ?string $note = null, ?int $createdBy = null, ?int $accountBlockId = null): BlocklistEntry
    {
        $normal = self::normalize($type, $value);

        return BlocklistEntry::firstOrCreate(
            ['type' => $type, 'match_key' => $normal['match_key']],
            [
                'value' => $normal['value'],
                'note' => ($note = trim((string) $note)) !== '' ? mb_substr($note, 0, 255) : null,
                'created_by' => $createdBy,
                'account_block_id' => $accountBlockId,
            ],
        );
    }

    /**
     * What is stored for something an operator typed.
     *
     * @return array{value: string, match_key: string}
     *
     * @throws \InvalidArgumentException whose message is the key of the sentence to show
     */
    public static function normalize(string $type, string $value): array
    {
        $value = trim($value);

        return match ($type) {
            self::EMAIL => self::normalizeEmail($value),
            self::DOMAIN => self::normalizeDomain($value),
            self::IP => self::normalizeAddress($value),
            default => throw new \InvalidArgumentException('messages.invalid_request'),
        };
    }

    /**
     * How many accounts already match an entry. They are not touched by it (the list is asked
     * only when an account is made), which is why the operator is told the number: a domain
     * with thousands of accounts is a mail provider, not a spammer.
     *
     * Null for a range of addresses, which cannot be counted in SQL.
     */
    public static function existingAccounts(string $type, string $matchKey): ?int
    {
        return match ($type) {
            self::DOMAIN => User::where(function ($q) use ($matchKey) {
                $like = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $matchKey);
                $q->where('email', 'like', '%@'.$like)->orWhere('email', 'like', '%.'.$like);
            })->count(),
            self::IP => str_contains($matchKey, '/') ? null : User::where('signup_ip', $matchKey)->count(),
            self::EMAIL => User::where('email', $matchKey)->count(),
            default => null,
        };
    }

    private static function normalizeEmail(string $value): array
    {
        if ($value === '' || mb_strlen($value) > 255 || filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
            throw new \InvalidArgumentException('messages.blocklist_invalid_email');
        }

        return ['value' => mb_strtolower($value), 'match_key' => self::canonicalEmail($value)];
    }

    private static function normalizeDomain(string $value): array
    {
        $domain = mb_strtolower($value);
        // What gets pasted: a link, an address, "@domain", "*.domain".
        $domain = preg_replace('#^[a-z][a-z0-9+.-]*://#', '', $domain);
        $domain = explode('/', $domain, 2)[0];
        if (str_contains($domain, '@')) {
            $domain = substr(strrchr($domain, '@'), 1);
        }
        $domain = self::asciiDomain(ltrim($domain, '*.'));

        if (strlen($domain) > 253 || ! preg_match('/^(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z][a-z0-9-]{0,61}[a-z0-9]$/', $domain)) {
            throw new \InvalidArgumentException('messages.blocklist_invalid_domain');
        }

        return ['value' => $domain, 'match_key' => $domain];
    }

    private static function normalizeAddress(string $value): array
    {
        [$ip, $prefix] = array_pad(explode('/', $value, 2), 2, null);
        $packed = @inet_pton(trim($ip));

        if ($packed === false) {
            throw new \InvalidArgumentException('messages.blocklist_invalid_ip');
        }

        $v6 = strlen($packed) === 16;
        $bits = $v6 ? 128 : 32;

        if ($prefix === null) {
            $prefix = $bits;
        } elseif (! ctype_digit($prefix) || (int) $prefix > $bits) {
            throw new \InvalidArgumentException('messages.blocklist_invalid_ip');
        } elseif ((int) $prefix < ($v6 ? self::WIDEST_V6 : self::WIDEST_V4)) {
            throw new \InvalidArgumentException('messages.blocklist_range_too_wide');
        }

        $prefix = (int) $prefix;
        $text = inet_ntop(self::mask($packed, $prefix));
        $entry = $prefix === $bits ? $text : $text.'/'.$prefix;

        return ['value' => $entry, 'match_key' => $entry];
    }

    /** The first $prefix bits of an address, the rest cleared. */
    private static function mask(string $packed, int $prefix): string
    {
        $masked = '';

        foreach (str_split($packed) as $i => $byte) {
            $keep = max(0, min(8, $prefix - $i * 8));
            $masked .= chr(ord($byte) & (0xFF << (8 - $keep)) & 0xFF);
        }

        return $masked;
    }

    /** A domain and every domain above it, never the last label alone. */
    private static function domainChain(string $domain): array
    {
        $labels = explode('.', $domain);
        $chain = [];

        for ($i = 0; $i < count($labels) - 1; $i++) {
            $chain[] = implode('.', array_slice($labels, $i));
        }

        return $chain ?: [$domain];
    }

    /** A domain as it is written on the wire, so the two spellings of one name are one entry. */
    private static function asciiDomain(string $domain): string
    {
        $domain = rtrim(trim($domain), '.');

        if ($domain !== '' && function_exists('idn_to_ascii') && preg_match('/[^\x00-\x7F]/', $domain)) {
            $ascii = idn_to_ascii($domain, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

            return $ascii !== false ? strtolower($ascii) : $domain;
        }

        return $domain;
    }

    /** Not through Eloquent: counting a refusal must not move the entry's updated_at. */
    private static function count(BlocklistEntry $entry): void
    {
        try {
            DB::table('blocklist_entries')->where('id', $entry->id)->update([
                'refused_count' => DB::raw('refused_count + 1'),
                'last_refused_at' => now(),
            ]);
        } catch (QueryException $e) {
            report($e);
        }
    }
}
