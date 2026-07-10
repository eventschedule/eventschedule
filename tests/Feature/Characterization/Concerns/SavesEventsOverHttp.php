<?php

namespace Tests\Feature\Characterization\Concerns;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;

/**
 * Drives EventRepo::saveEvent() through the real event.store / event.update
 * HTTP routes, the way the AP event form does.
 *
 * Characterization support for the refactor campaign (REFACTOR_PLAN.md P11):
 * these helpers must submit the same server-visible field shapes as the
 * interactive form, so the pinned behavior matches production traffic.
 */
trait SavesEventsOverHttp
{
    /**
     * Minimal AP event-form payload. starts_at is schedule-local wall clock
     * ('Y-m-d H:i:s'); saveEvent converts it to UTC using the schedule timezone.
     */
    protected function eventPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Characterized Event',
            'starts_at' => '2026-08-15 20:00:00',
            'duration' => 2.5,
            'schedule_type' => 'one_time',
        ], $overrides);
    }

    protected function postCreateEvent(User $user, Role $role, array $overrides = [])
    {
        return $this->actingAs($user)->post(
            route('event.store', ['subdomain' => $role->subdomain]),
            $this->eventPayload($overrides)
        );
    }

    protected function putUpdateEvent(User $user, Role $role, Event $event, array $overrides = [])
    {
        return $this->actingAs($user)->put(
            route('event.update', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]),
            $this->eventPayload($overrides)
        );
    }

    /** The event the last postCreateEvent created. */
    protected function latestEvent(): Event
    {
        return Event::query()->orderByDesc('id')->firstOrFail();
    }

    /** The admin-calendar redirect event.store/update both land on. */
    protected function adminCalendarUrl(Role $role, int $month, int $year): string
    {
        return route('role.view_admin', [
            'subdomain' => $role->subdomain,
            'tab' => 'schedule',
            'month' => $month,
            'year' => $year,
        ]);
    }
}
