<?php

namespace App\Utils;

use App\Models\Event;
use App\Models\Role;

/**
 * The address of a schedule's or an event's guest page, for the operator's overview pages
 * (/admin/dashboard, /admin/realtime), which put a link to it beside each name
 * (components/public-page-link). Null where the link would open a page nobody is shown.
 */
class PublicPageUrl
{
    /**
     * A schedule's guest page: not for a deleted schedule, and getGuestUrl() answers nothing for
     * one that is not claimed. Reads user_id, subdomain, is_deleted and the two verified stamps.
     */
    public static function schedule(?Role $role): ?string
    {
        return $role && ! $role->is_deleted ? ($role->getGuestUrl() ?: null) : null;
    }

    /**
     * An event's guest page on a schedule that serves it (Event::canonicalTarget()): not for a
     * draft, nor for an event no schedule has accepted yet, where the address would 404. Needs
     * the event's roles loaded, or each call queries them.
     */
    public static function event(?Event $event): ?string
    {
        if (! $event || $event->is_draft) {
            return null;
        }

        [$url, $home] = $event->canonicalTarget();

        return $home && $url ? $url : null;
    }
}
