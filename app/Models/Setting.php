<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Global, platform-wide key/value settings configured by a super-admin
 * in the /admin panel (e.g. custom header/footer code). This is operator-only
 * data, never editable by individual schedule owners.
 */
class Setting extends Model
{
    protected $fillable = [
        'key',
        'value',
    ];

    private const CACHE_KEY = 'site_settings';

    /**
     * Get a setting value by key. Reads from a single cached key => value map,
     * so a request needs at most one cache hit (one query on a cold cache).
     */
    public static function get(string $key, $default = null)
    {
        try {
            $map = Cache::rememberForever(self::CACHE_KEY, function () {
                return static::query()->pluck('value', 'key')->all();
            });
        } catch (\Throwable $e) {
            // Settings table or cache backend unavailable (e.g. during a deploy
            // before migrations run). Fail open so public pages still render.
            return $default;
        }

        return $map[$key] ?? $default;
    }

    /**
     * Create or update a setting and invalidate the cached map.
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        Cache::forget(self::CACHE_KEY);
    }
}
