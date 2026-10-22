<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Which address a schedule served directly on its own domain calls its main one. One row per
 * schedule whose owner ever chose; no row reads as 'custom'. See the migration (2026_10_22_000002)
 * for why this is not a column on `roles`.
 *
 * Read through subdomainFirstIds(), never through a relation: whether a schedule's address is its
 * domain is asked once per event in every list that shows its events (each event carries its own
 * copy of the schedule), and an eager load at every one of those queries is not a thing anyone
 * would keep up. One query a request answers all of them, and only a schedule served directly on an
 * active domain ever asks (Role::mainAddressIsCustomDomain()).
 */
class CustomDomainSetting extends Model
{
    public const CUSTOM = 'custom';

    public const SUBDOMAIN = 'subdomain';

    public const VALUES = [self::CUSTOM, self::SUBDOMAIN];

    protected $guarded = [];

    /** @var array<int, true>|null */
    private static ?array $subdomainFirst = null;

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /** @return array<int, true> the schedules whose main address is the subdomain, by id */
    public static function subdomainFirstIds(): array
    {
        return self::$subdomainFirst ??= self::query()
            ->where('main_address', self::SUBDOMAIN)
            ->pluck('role_id')
            ->mapWithKeys(fn ($id) => [(int) $id => true])
            ->all();
    }

    /**
     * Forget the set: after a save, before each queued job (a worker lives through many), and
     * between tests.
     */
    public static function flush(): void
    {
        self::$subdomainFirst = null;
    }

    public static function mainAddressOf(Role $role): string
    {
        return isset(self::subdomainFirstIds()[(int) $role->id]) ? self::SUBDOMAIN : self::CUSTOM;
    }

    /**
     * Store a schedule's choice. Nothing else holds it: ResolveCustomDomain asks mainAddressOf() on
     * each request rather than keeping it in its cached host lookup.
     */
    public static function store(Role $role, string $mainAddress): void
    {
        self::updateOrCreate(['role_id' => $role->id], ['main_address' => $mainAddress]);
        self::flush();
    }
}
