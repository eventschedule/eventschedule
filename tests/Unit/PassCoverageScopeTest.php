<?php

namespace Tests\Unit;

use App\Models\Event;
use App\Models\Ticket;
use Tests\TestCase;

/**
 * A pass's coverage resolves within its home schedule. When that schedule cannot be resolved
 * (`events.creator_role_id` is nullable and CheckData backfills it), there is nothing to check the
 * stored ids against, so coverage must deny.
 *
 * A permissive fallback would let a pass cover any event id in the system, and would let a booking
 * be written for an event outside the pass's schedules, where Event::computePassReservedSeats()
 * cannot see it - reselling a seat that is already held.
 *
 * These assertions need no database: covers() short-circuits before touching a relation.
 */
class PassCoverageScopeTest extends TestCase
{
    private function foreignEvent(): Event
    {
        $event = new Event;
        $event->id = 99999;

        return $event;
    }

    private function pass(string $scope, array $attributes = []): Ticket
    {
        $ticket = new Ticket;
        $ticket->is_pass = true;
        $ticket->pass_scope = $scope;

        foreach ($attributes as $key => $value) {
            $ticket->{$key} = $value;
        }

        return $ticket;
    }

    public function test_no_scope_covers_a_foreign_event_without_a_home_schedule(): void
    {
        $foreign = $this->foreignEvent();

        $scopes = [
            'this_event' => ['event_id' => 12345],
            'all_events' => [],
            'sub_schedule' => ['pass_scope_group_id' => 7],
            'specific_events' => ['pass_event_ids' => [99999]],
        ];

        foreach ($scopes as $scope => $attributes) {
            $this->assertFalse(
                $this->pass($scope, $attributes)->covers($foreign, null),
                "pass_scope '{$scope}' must not cover an arbitrary event id with no home schedule"
            );
        }
    }

    public function test_covered_event_ids_are_empty_without_a_home_schedule(): void
    {
        $pass = $this->pass('specific_events', ['pass_event_ids' => [99999, 88888]]);

        $this->assertSame([], $pass->coveredEventIds(null));
    }

    public function test_this_event_scope_still_covers_its_own_event(): void
    {
        $own = new Event;
        $own->id = 12345;

        $this->assertTrue(
            $this->pass('this_event', ['event_id' => 12345])->covers($own, null),
            'a this_event pass never consults the schedule and must keep working'
        );
    }
}
