<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A schedule that was only INVITED to somebody else's event has no say over its sales
 * (GHSA-4pvq-gfvr-g847).
 *
 * The creator names a venue or a performer and the pivot waits at is_accepted = null until that
 * schedule answers. User::canViewEventData() turned away a decline but let the invitation through,
 * and only `refund` asked the stricter userMayMoveMoneyOn(), so the invited schedule's owner could
 * mark paid, cancel and delete the creator's sales by id (Sqids over sequential integers) and read
 * its buyers through every page that asks canViewEventData().
 */
class InvitedScheduleSaleAccessTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** @return array{0: Sale, 1: Sale, 2: Event, 3: User, 4: Role} */
    private function eventWithInvitedSchedule(?bool $accepted = null): array
    {
        $creator = $this->createOwner();
        $role = $this->createRole($creator);
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'ticket_currency_code' => 'USD']);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 50]);

        $unpaid = $this->createSale($event, $role, ['payment_amount' => 20, 'status' => 'unpaid'], $ticket);
        $paid = $this->createSale($event, $role, ['payment_amount' => 20, 'status' => 'paid'], $ticket);

        $invitedOwner = $this->createOwner();
        $invited = $this->createRole($invitedOwner, 'talent');
        $event->roles()->attach($invited->id, ['is_accepted' => $accepted]);

        return [$unpaid->fresh(), $paid->fresh(), $event->fresh(), $invitedOwner, $invited];
    }

    private function action(User $user, Sale $sale, string $action)
    {
        return $this->actingAs($user)->postJson(
            route('sales.action', ['sale_id' => UrlUtils::encodeId($sale->id)]),
            ['action' => $action]
        );
    }

    public function test_an_invited_schedule_cannot_mark_paid_cancel_or_delete_the_creators_sales(): void
    {
        [$unpaid, $paid, , $invitedOwner] = $this->eventWithInvitedSchedule();

        $this->action($invitedOwner, $unpaid, 'mark_paid')->assertStatus(403);
        $this->action($invitedOwner, $paid, 'cancel')->assertStatus(403);
        $this->action($invitedOwner, $paid, 'delete')->assertStatus(403);

        $this->assertSame('unpaid', $unpaid->fresh()->status);
        $this->assertSame('paid', $paid->fresh()->status);
        $this->assertFalse((bool) $paid->fresh()->is_deleted);
    }

    public function test_an_invited_schedule_cannot_change_a_sale_through_the_api(): void
    {
        [$unpaid, $paid, , $invitedOwner] = $this->eventWithInvitedSchedule();

        $raw = 'testapikey_'.Str::random(24);
        $invitedOwner->api_key = substr(hash('sha256', $raw), 0, 8);
        $invitedOwner->api_key_hash = Hash::make($raw);
        $invitedOwner->save();

        $this->withHeaders(['X-API-Key' => $raw])
            ->putJson('/api/sales/'.UrlUtils::encodeId($unpaid->id), ['action' => 'mark_paid'])
            ->assertStatus(403);
        $this->withHeaders(['X-API-Key' => $raw])
            ->putJson('/api/sales/'.UrlUtils::encodeId($paid->id), ['action' => 'cancel'])
            ->assertStatus(403);
        $this->withHeaders(['X-API-Key' => $raw])
            ->getJson('/api/sales/'.UrlUtils::encodeId($paid->id))
            ->assertStatus(403);

        $this->assertSame('unpaid', $unpaid->fresh()->status);
        $this->assertSame('paid', $paid->fresh()->status);
    }

    public function test_only_an_accept_opens_somebody_elses_event_data(): void
    {
        [, , $pending, $pendingOwner] = $this->eventWithInvitedSchedule(null);
        [, , $declined, $declinedOwner] = $this->eventWithInvitedSchedule(false);
        [, , $accepted, $acceptedOwner] = $this->eventWithInvitedSchedule(true);

        $this->assertFalse($pendingOwner->canViewEventData($pending), 'an invitation is not an answer');
        $this->assertFalse($declinedOwner->canViewEventData($declined));
        $this->assertTrue($acceptedOwner->canViewEventData($accepted));

        $this->assertFalse($pendingOwner->canViewEventTraffic($pending));
        $this->assertTrue($acceptedOwner->canViewEventTraffic($accepted));
    }

    public function test_an_accepted_schedule_still_runs_the_sales(): void
    {
        [$unpaid, , , $acceptedOwner] = $this->eventWithInvitedSchedule(true);

        $this->action($acceptedOwner, $unpaid, 'mark_paid')->assertStatus(302);
        $this->assertSame('paid', $unpaid->fresh()->status);
    }

    /** An appointment booking waits at null on the schedule's OWN event, and its owner sees it. */
    public function test_a_schedules_own_event_waiting_for_approval_stays_its_owners(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        // Made by somebody else (the guest who booked), so the maker's own shortcut does not
        // answer for the owner, and owned by the schedule as a booking is.
        $guest = $this->createOwner();
        $event = $this->createEvent($role, ['user_id' => $guest->id]);
        $event->creator_role_id = $role->id;
        $event->save();
        $event->roles()->updateExistingPivot($role->id, ['is_accepted' => null]);

        $this->assertTrue($owner->fresh()->canViewEventData($event->fresh()));
        $this->assertFalse($guest->fresh()->canViewEventData($event->fresh()));
    }
}
