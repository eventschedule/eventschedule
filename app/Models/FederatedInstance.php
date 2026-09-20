<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * An EventSchedule install that federates its public events to this one.
 * Only populated on the nexus app (eventschedule.com), where an admin approves
 * an instance once and its events then publish automatically.
 */
class FederatedInstance extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_SUSPENDED = 'suspended';

    /**
     * The first selfhost release with one-click schedule listing (the dashboard prompt and the
     * checklist on the network settings card). The welcome email tells an install on this
     * version or later to use it, and an older one how to do it by hand.
     *
     * MUST match the release that ships the feature. If that release goes out under a different
     * number, change this with it - the email keys its step-one copy and its update tip off it.
     */
    public const ONE_CLICK_LISTING_VERSION = 'v1.0.132';

    protected $hidden = ['secret'];

    protected $fillable = [
        'instance_id',
        'site_url',
        'reported_site_url',
        'name',
        'contact_email',
        'secret',
        'app_version',
        'status',
        'approved_by',
        'approved_at',
        'last_seen_at',
        'flagged_at',
        'welcomed_at',
        'welcomed_email',
        'locale',
    ];

    protected $casts = [
        // Encrypted rather than hashed: verifying a request HMAC needs the plaintext.
        'secret' => 'encrypted',
        'approved_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'flagged_at' => 'datetime',
        'welcomed_at' => 'datetime',
    ];

    public function events()
    {
        return $this->hasMany(FederatedEvent::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Does this install's reported version have one-click schedule listing?
     *
     * An empty or unparseable version counts as older: saying "click the button on your
     * dashboard" to an install that has no such button is the worse mistake.
     */
    public function supportsOneClickListing(): bool
    {
        return self::versionAtLeast($this->app_version, self::ONE_CLICK_LISTING_VERSION);
    }

    /**
     * version_compare() over "v1.0.132"-style strings. False for anything that is not a plain
     * dotted number once the leading "v" is gone, so "dev" or "" never compares as newer.
     */
    public static function versionAtLeast(?string $version, string $minimum): bool
    {
        if (! self::isVersionString($version)) {
            return false;
        }

        return version_compare(ltrim(trim((string) $version), 'vV'), ltrim($minimum, 'vV'), '>=');
    }

    /** "v1.0.132" or "1.0.132": an optional v, then dotted numbers, and nothing else. */
    public static function isVersionString(?string $version): bool
    {
        return (bool) preg_match('/^[vV]?\d+(\.\d+)*$/', trim((string) $version));
    }

    /**
     * The reported version, or null when it is not a plain version number. app_version is
     * whatever the registrant sent, so anything else is never printed in mail this site sends.
     */
    public function displayVersion(): ?string
    {
        return self::isVersionString($this->app_version) ? trim($this->app_version) : null;
    }

    /**
     * The install's host for printing in mail: a zero-width space after every dot, so mail
     * clients do not turn it into a link. site_url comes from an unauthenticated registration,
     * so the only links in mail this site sends are the ones it chose. Plain text - escape it
     * like any other value in HTML.
     */
    public function displayHost(): string
    {
        return str_replace('.', ".\u{200B}", (string) ($this->host() ?? $this->site_url));
    }

    /**
     * The language to email this operator in: theirs when the install reported one, the app's
     * fallback otherwise. Never config('app.locale') - SetUserLanguage rewrites that to the
     * language of whichever admin is making the request.
     */
    public function mailLocale(): string
    {
        return is_valid_language_code($this->locale)
            ? $this->locale
            : (string) config('app.fallback_locale', 'en');
    }

    /**
     * Where this install's listings can be seen on this site. Built here, on the nexus, because
     * the id in it is encoded with this app's key - an install cannot work it out for itself, so
     * the API hands it over in every response.
     */
    public function listingsUrl(): string
    {
        return marketing_url('/browse').'?instance='.\App\Utils\UrlUtils::encodeId($this->id).'#network';
    }

    /**
     * The host events from this instance must live on. Guards against an approved
     * instance being used to publish backlinks to somewhere else entirely.
     */
    public function host(): ?string
    {
        $host = parse_url((string) $this->site_url, PHP_URL_HOST);

        return $host ? strtolower($host) : null;
    }

    /**
     * Does this URL live on the host the instance registered? Exact host, or a
     * subdomain of it.
     *
     * The rule the event backlink has always had to pass, lifted out of the intake
     * controller so a second surface cannot drift from it. It guards every
     * instance-supplied URL this app is willing to render as a link, which is now
     * more than one: schedule_url reaches the review screen.
     *
     * parse_url returns no host for a scheme-relative or javascript: string, so
     * those fail here too.
     */
    public function ownsUrl(?string $url): bool
    {
        $parts = parse_url((string) $url) ?: [];

        if (! in_array(strtolower($parts['scheme'] ?? ''), ['http', 'https'], true)) {
            return false;
        }

        $host = strtolower($parts['host'] ?? '');
        $expected = $this->host();

        return $host !== '' && $expected !== null
            && ($host === $expected || str_ends_with($host, '.'.$expected));
    }

    /**
     * Is this row claiming an address an admin could actually adopt?
     *
     * The single predicate behind BOTH the "Accept new address" button in the review
     * screen and AdminFederationController::settleFlag()'s refusal to settle a live
     * claim, so those two cannot drift.
     *
     * They did drift, and it stranded rows. The push path stores whatever an install
     * reports with no URL validation at all (ApiFederationController only checks it is a
     * non-empty string, then truncates to 255), while acceptAddress() holds the value to
     * registration's own rule. So an install with a misconfigured APP_URL could report
     * junk: the view offered Accept, acceptAddress() refused it as invalid, and the flag
     * could not be settled either because the column was merely non-null. Suspend was the
     * only exit, and since the stored value never changed, flagged_at never re-stamped -
     * a dashboard alert pinned open forever, which is exactly what AdminAlertService
     * forbids. Reading junk as "nothing to adopt" sends those rows to the review branch,
     * where confirming the address on record settles them.
     *
     * The empty string is the same story: a push of "/" rtrims to '', which differs from
     * the record, so it flags - and would re-flag hourly once cleared.
     */
    public function hasAdoptableAddress(): bool
    {
        $reported = $this->reported_site_url;

        if (! is_string($reported) || $reported === '') {
            return false;
        }

        $fails = Validator::make(
            ['site_url' => $reported],
            ['site_url' => ['required', 'url', 'max:255']]
        )->fails();

        return ! $fails && (bool) parse_url($reported, PHP_URL_HOST);
    }
}
