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
     * Bring the stored key in line with the switch, on settings about to be saved: a key for a
     * link that is on, none for one that is off. Off and on again is a new address. Nothing is
     * touched here: the save may still be refused, and the picture of a link that stays on must
     * outlive a refused save. The caller closes the old address with close() once the save is in.
     */
    public static function settle(array $settings): array
    {
        if (empty($settings['link_enabled'])) {
            unset($settings['link_key']);
        } elseif (! self::validKey($settings['link_key'] ?? null)) {
            $settings['link_key'] = strtolower(Str::random(40));
        }

        return $settings;
    }

    /** Take the picture off an address that a save has just retired. */
    public static function close(mixed $key): void
    {
        if (self::validKey($key)) {
            Storage::delete(self::path($key));
            Cache::forget(self::cacheKey($key));
        }
    }

    /** Whether the address has a picture to hand out yet (none until an upcoming event has a flyer). */
    public static function ready(Role $role): bool
    {
        return self::on($role) && Storage::exists(self::path($role->graphic_settings['link_key']));
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

        // Private, and never to be kept: on the hosted install the default disk is a public
        // bucket behind a CDN that serves any object by its key for a year (config/filesystems.php,
        // where every other object is immutable). Left at the disk's defaults this picture would
        // be handed out there past the plan, the switch and a deleted schedule, and the hourly
        // redraw would never show. Only publicImage() hands it out.
        $stored = Storage::put(self::path($role->graphic_settings['link_key']), $image, [
            'visibility' => 'private',
            'CacheControl' => 'private, no-store',
        ]);

        // put() answers false on a disk that does not throw; a save must not say "published" over it.
        if (! $stored) {
            throw new \RuntimeException('The events graphic could not be stored for its public link (schedule '.$role->id.').');
        }

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
            // By text and not by JSON path: graphic_settings is a TEXT column, and on MySQL one
            // row that is not JSON (an empty string from a hand-edited backup) makes json_extract()
            // raise an error for the whole query, which would be every public link on the install.
            // The key is forty letters and digits (validKey() above), so it is safe in a LIKE; the
            // row is then read through the model, which is what decides.
            return Role::where('is_deleted', false)
                ->where('graphic_settings', 'like', '%'.$key.'%')
                ->get()
                ->contains(fn (Role $role) => ($role->graphic_settings['link_key'] ?? null) === $key && self::on($role));
        });

        return $live ? Storage::get(self::path($key)) : null;
    }

    private static function cacheKey(string $key): string
    {
        return 'graphic_link:'.$key;
    }
}
