<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * The Events graphic at a fixed public address, for a screen or a website that shows a picture
 * from an address (Enterprise, switched on under Public link on the Events graphic page).
 *
 * The picture is drawn ahead of time (on a save and hourly by app:send-graphic-emails, on both
 * cron rails) and the address only hands out the stored file: nothing is drawn inside a request
 * anybody can make. It is served by the app and not straight from storage, because a web server
 * tells a browser to keep a .png for a month (public/.htaccess) and a screen would never see the
 * next one.
 *
 * The address holds a random key and no schedule name. The key lives in graphic_settings
 * (`link_key`, beside `link_enabled`), which no form fill reaches, and a backup restore drops both:
 * a restore is a new schedule, and two schedules on one key would draw over each other's picture.
 */
class GraphicLink
{
    public const KEY_PATTERN = '[a-z0-9]{40}';

    /** How long the address may go on answering after the link is switched off or the schedule is deleted. */
    private const LIVE_SECONDS = 300;

    public static function on(Role $role): bool
    {
        $settings = $role->graphic_settings ?? [];

        // The plan last: the hourly command asks this of every schedule with graphic settings.
        return ! $role->is_deleted
            && ! empty($settings['link_enabled'])
            && self::validKey($settings['link_key'] ?? null)
            && $role->isEnterprise();
    }

    public static function url(Role $role): ?string
    {
        return self::on($role) ? route('graphic.link', ['key' => $role->graphic_settings['link_key']]) : null;
    }

    public static function validKey(mixed $key): bool
    {
        return is_string($key) && preg_match('/^'.self::KEY_PATTERN.'$/', $key) === 1;
    }

    public static function path(string $key): string
    {
        return 'graphic_links/'.$key.'.png';
    }

    /**
     * Bring the stored key in line with the switch, on the settings about to be saved: a key for a
     * link that is on, none (and no picture) for one that is off. Off and on again is a new address.
     */
    public static function settle(array $settings): array
    {
        $key = $settings['link_key'] ?? null;

        if (! empty($settings['link_enabled'])) {
            if (! self::validKey($key)) {
                $settings['link_key'] = strtolower(Str::random(40));
            }

            return $settings;
        }

        if (self::validKey($key)) {
            Storage::delete(self::path($key));
            Cache::forget(self::cacheKey($key));
        }
        unset($settings['link_key']);

        return $settings;
    }

    /**
     * Draw the picture again and store it. False when there is nothing to draw (no upcoming event
     * with a flyer): the last picture stays.
     */
    public static function refresh(Role $role): bool
    {
        if (! self::on($role) || is_demo_role($role)) {
            return false;
        }

        $image = app(GraphicEmailService::class)->posterImage($role);

        if (! $image) {
            return false;
        }

        Storage::put(self::path($role->graphic_settings['link_key']), $image);

        return true;
    }

    /** The picture behind a key, or null when no live schedule publishes one there. */
    public static function image(string $key): ?string
    {
        if (! self::validKey($key)) {
            return null;
        }

        // Asked of the schedule and not only of the file, so a deleted or blocked schedule's
        // picture stops being handed out; remembered, because a screen asks again all day.
        $live = Cache::remember(self::cacheKey($key), self::LIVE_SECONDS, function () use ($key) {
            $role = Role::where('is_deleted', false)->where('graphic_settings->link_key', $key)->first();

            return $role && self::on($role);
        });

        return $live ? Storage::get(self::path($key)) : null;
    }

    private static function cacheKey(string $key): string
    {
        return 'graphic_link:'.$key;
    }
}
