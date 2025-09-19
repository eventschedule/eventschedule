<?php

namespace App\Utils;

use App\Models\Event;
use App\Models\Role;
use Illuminate\Support\Collection;

class NotificationUtils
{
    /**
     * Get members of a role who are subscribed to notifications.
     */
    public static function roleMembers(Role $role): Collection
    {
        return $role->members()
            ->whereNotNull('users.email')
            ->where(function ($query) {
                $query->whereNull('users.is_subscribed')
                    ->orWhere('users.is_subscribed', true);
            })
            ->get();
    }

    /**
     * Retrieve the organizer-facing users for an event.
     */
    public static function organizerUsers(Event $event): Collection
    {
        $event->loadMissing(['creatorRole.members', 'venue.members', 'user']);

        $users = collect();

        if ($event->creatorRole) {
            $users = $users->merge(self::roleMembers($event->creatorRole));
        }

        if ($event->venue) {
            $users = $users->merge(self::roleMembers($event->venue));
        }

        if ($event->user && $event->user->email && $event->user->is_subscribed !== false) {
            $users->push($event->user);
        }

        return $users->unique('id')->values();
    }

    /**
     * Retrieve the talent-facing users for an event.
     */
    public static function talentUsers(Event $event): Collection
    {
        $event->loadMissing('roles.members');

        return $event->roles
            ->filter(fn (Role $role) => $role->isTalent())
            ->flatMap(fn (Role $role) => self::roleMembers($role))
            ->unique('id')
            ->values();
    }

    /**
     * Retrieve the purchaser-facing users (venue team) for an event.
     */
    public static function purchaserUsers(Event $event): Collection
    {
        $event->loadMissing('venue.members');

        if (! $event->venue) {
            return collect();
        }

        return self::roleMembers($event->venue)->unique('id')->values();
    }

    /**
     * Retrieve ticket purchaser email addresses for an event.
     */
    public static function purchaserEmails(Event $event): Collection
    {
        $event->loadMissing('sales');

        return $event->sales
            ->pluck('email')
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Provide a consistent event display name.
     */
    public static function eventDisplayName(Event $event): string
    {
        if ($event->name) {
            return $event->name;
        }

        $talent = optional($event->role())->getDisplayName();
        $venue = $event->getVenueDisplayName();

        if ($talent && $venue) {
            return __('messages.event_title', ['role' => $talent, 'venue' => $venue]);
        }

        return $talent ?: ($venue ?: __('messages.event'));
    }
}

