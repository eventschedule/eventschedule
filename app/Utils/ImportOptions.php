<?php

namespace App\Utils;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * The places events can be brought in from, as the first-event form's band and the Schedule tab of
 * an empty schedule offer them (`<x-import-options>`), and the one answer to which of them this
 * install and this person can use, and when (scheduleIsEmpty()). The import page asks the same two questions, so a tile never leads to a page
 * that does not have what it promised.
 *
 * A source that cannot be used is left out, never shown locked: the band is an invitation, and a
 * locked tile on someone's first screen reads as a wall.
 */
class ImportOptions
{
    /**
     * Google Calendar, read on the import page. The schedule's Google connection is its owner's,
     * so nobody else is offered it; and not where Google is not set up, or in the demo.
     */
    public static function googleAvailable(Role $role, ?User $user): bool
    {
        // Cast as GoogleCalendarController casts, so the tile and the server answer alike.
        return (bool) config('services.google.client_id')
            && $user !== null
            && (int) $user->id === (int) $role->user_id
            && ! is_demo_mode();
    }

    /**
     * Whether the schedule has no events at all: none accepted onto it, on any date or on none,
     * which is what its Schedule tab's calendar shows. The band and the Schedule tab are offered
     * only then. Asking whether the PERSON has made an event is a different question: a team
     * member's first event on a busy schedule is not an empty schedule.
     */
    public static function scheduleIsEmpty(Role $role): bool
    {
        return ! DB::table('event_role')
            ->where('role_id', $role->id)
            ->where('is_accepted', true)
            ->exists();
    }

    /** A flyer, a screenshot or pasted text needs the model. A link does not. */
    public static function aiAvailable(): bool
    {
        return (bool) (config('services.google.gemini_key') || config('services.openai.api_key'));
    }

    /**
     * The tiles, in the order this schedule's type would reach for them: a venue usually keeps a
     * calendar, a performer has a website and a poster, a curator lists what is published
     * elsewhere.
     *
     * @return list<array{key: string, title: string, description: string, href: string}>
     */
    public static function for(Role $role, ?User $user): array
    {
        $import = fn (array $query = []) => route('event.show_import_ai', ['subdomain' => $role->subdomain, ...$query]);

        $sources = [
            'link' => [
                'title' => __('messages.import_source_link'),
                'description' => __('messages.import_source_link_description'),
                'href' => $import(),
            ],
        ];

        if (self::aiAvailable()) {
            $sources['flyer'] = [
                'title' => __('messages.import_source_flyer'),
                'description' => __('messages.import_source_flyer_description'),
                'href' => $import(),
            ];
        }

        if (self::googleAvailable($role, $user)) {
            $sources['google'] = [
                'title' => __('messages.import_source_google'),
                'description' => __('messages.import_google_tile_description'),
                'href' => $import(['source' => 'google']),
            ];
        }

        $order = match ($role->type) {
            'venue' => ['google', 'link', 'flyer'],
            'curator' => ['link', 'google', 'flyer'],
            default => ['link', 'flyer', 'google'],
        };

        $tiles = [];
        foreach ($order as $key) {
            if (isset($sources[$key])) {
                $tiles[] = ['key' => $key, ...$sources[$key]];
            }
        }

        return $tiles;
    }
}
