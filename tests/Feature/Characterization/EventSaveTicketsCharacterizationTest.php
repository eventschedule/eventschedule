<?php

namespace Tests\Feature\Characterization;

use App\Models\PromoCode;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Characterization\Concerns\SavesEventsOverHttp;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Characterizes the tickets / add-ons / promo-codes / event-parts sections of
 * EventRepo::saveEvent() (REFACTOR_PLAN.md P11), including the soft-delete vs
 * hard-delete split (tickets/addons flip is_deleted; promo codes and parts are
 * deleted outright) that a decomposition could easily blur.
 */
class EventSaveTicketsCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;
    use SavesEventsOverHttp;

    public function test_create_with_two_ticket_types_pins_full_rows(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        $this->postCreateEvent($owner, $role, [
            'tickets_enabled' => '1',
            'tickets' => [
                ['type' => 'General', 'quantity' => '100', 'price' => '25'],
                ['type' => 'VIP', 'quantity' => '10', 'price' => '75.50'],
            ],
        ])->assertRedirect();

        $event = $this->latestEvent();
        $this->assertDatabaseHas('events', ['id' => $event->id, 'tickets_enabled' => 1]);

        // Non-pass defaults pinned. Note: pass_usage_type defaults to
        // per_occurrence even for non-pass tickets, and the "per_occurrence
        // only applies to recurring events" coercion then rewrites it to
        // 'total' on a one-time event - so plain tickets store 'total' here.
        $this->assertDatabaseHas('tickets', [
            'event_id' => $event->id,
            'type' => 'General',
            'quantity' => 100,
            'price' => 25,
            'is_pass' => 0,
            'is_addon' => 0,
            'is_deleted' => 0,
            'pass_usage_type' => 'total',
            'pass_scope' => 'this_event',
            'max_per_order' => null,
        ]);
        $this->assertDatabaseHas('tickets', [
            'event_id' => $event->id,
            'type' => 'VIP',
            'quantity' => 10,
            'price' => 75.50,
        ]);
        $this->assertSame(2, Ticket::where('event_id', $event->id)->count());
    }

    public function test_update_edits_adds_and_soft_deletes_tickets_in_one_payload(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $keep = $this->createTicket($event, ['type' => 'Keep', 'price' => 10, 'quantity' => 5]);
        $drop = $this->createTicket($event, ['type' => 'Drop']);

        $this->putUpdateEvent($owner, $role, $event, [
            'tickets_enabled' => '1',
            'tickets' => [
                ['id' => $keep->id, 'type' => 'Keep', 'quantity' => '8', 'price' => '12'],
                ['type' => 'Added', 'quantity' => '20', 'price' => '0'],
            ],
        ])->assertRedirect();

        // Edited in place.
        $this->assertDatabaseHas('tickets', [
            'id' => $keep->id, 'quantity' => 8, 'price' => 12, 'is_deleted' => 0,
        ]);
        // Added.
        $this->assertDatabaseHas('tickets', [
            'event_id' => $event->id, 'type' => 'Added', 'quantity' => 20, 'is_deleted' => 0,
        ]);
        // Omitted id -> soft-deleted, never hard-deleted (sales reference it).
        $this->assertDatabaseHas('tickets', ['id' => $drop->id, 'is_deleted' => 1]);
    }

    public function test_disabling_tickets_soft_deletes_all_tickets(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event);

        $this->putUpdateEvent($owner, $role, $event, [
            'tickets_enabled' => '0',
        ])->assertRedirect();

        $this->assertDatabaseHas('events', ['id' => $event->id, 'tickets_enabled' => 0]);
        $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'is_deleted' => 1]);
    }

    public function test_pass_on_one_time_event_coerces_per_occurrence_to_total_without_max_uses(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        // validatePassConfiguration accepts per_occurrence with no max uses;
        // the coercion to 'total' (no days_of_week) happens AFTER validation,
        // so the stored pass ends up usage=total with pass_max_uses NULL.
        $this->postCreateEvent($owner, $role, [
            'tickets_enabled' => '1',
            'individual_tickets' => '1',
            'tickets' => [
                ['type' => 'Pass', 'quantity' => '10', 'price' => '50', 'is_pass' => '1'],
            ],
        ])->assertRedirect();

        $event = $this->latestEvent();
        $this->assertDatabaseHas('tickets', [
            'event_id' => $event->id,
            'type' => 'Pass',
            'is_pass' => 1,
            'pass_usage_type' => 'total',
            'pass_scope' => 'this_event',
            'pass_max_uses' => null,
            'pass_admits_per_event' => 1,
            'max_per_order' => 1, // passes are capped at one per order
        ]);

        // A pass disables per-person individual ticketing.
        $this->assertDatabaseHas('events', ['id' => $event->id, 'individual_tickets' => 0]);
    }

    public function test_addons_are_upserted_and_soft_deleted_as_addon_tickets(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $this->createTicket($event);
        $dropAddon = $this->createTicket($event, ['type' => 'Old Addon', 'is_addon' => true]);

        $this->putUpdateEvent($owner, $role, $event, [
            'tickets_enabled' => '1',
            'tickets' => [['type' => 'General', 'quantity' => '50', 'price' => '0']],
            'addons' => [
                ['type' => 'Parking', 'quantity' => '30', 'price' => '5', 'url' => 'https://park.example.com'],
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('tickets', [
            'event_id' => $event->id,
            'type' => 'Parking',
            'is_addon' => 1,
            'quantity' => 30,
            'price' => 5,
            'url' => 'https://park.example.com',
            'is_deleted' => 0,
        ]);
        $this->assertDatabaseHas('tickets', ['id' => $dropAddon->id, 'is_deleted' => 1]);
    }

    public function test_promo_codes_are_uppercased_and_removed_codes_hard_deleted(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $this->createTicket($event);

        $stale = PromoCode::create([
            'event_id' => $event->id,
            'code' => 'STALE',
            'type' => 'percentage',
            'value' => 10,
            'is_active' => true,
        ]);

        $this->putUpdateEvent($owner, $role, $event, [
            'tickets_enabled' => '1',
            'tickets' => [['type' => 'General', 'quantity' => '50', 'price' => '10']],
            'promo_codes' => [
                ['code' => ' early bird ', 'type' => 'percentage', 'value' => '15', 'is_active' => '1'],
            ],
        ])->assertRedirect();

        // Code normalized: trimmed and uppercased.
        $this->assertDatabaseHas('promo_codes', [
            'event_id' => $event->id,
            'code' => 'EARLY BIRD',
            'type' => 'percentage',
            'value' => 15,
            'is_active' => 1,
            'max_uses' => null,
            'expires_at' => null,
        ]);

        // Unlike tickets, removed promo codes are HARD deleted.
        $this->assertDatabaseMissing('promo_codes', ['id' => $stale->id]);
    }

    public function test_event_parts_are_upserted_with_sort_order_and_removed_parts_hard_deleted(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role);
        $stale = $event->parts()->create(['name' => 'Stale Part', 'sort_order' => 0]);

        $this->putUpdateEvent($owner, $role, $event, [
            'event_parts' => [
                ['name' => 'Doors Open', 'start_time' => '19:00'],
                ['name' => 'Main Act', 'description' => 'Headliner', 'start_time' => '20:00', 'end_time' => '22:00'],
                ['name' => ''], // nameless rows are skipped
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('event_parts', [
            'event_id' => $event->id, 'name' => 'Doors Open', 'sort_order' => 0, 'description' => null,
        ]);
        $this->assertDatabaseHas('event_parts', [
            'event_id' => $event->id, 'name' => 'Main Act', 'sort_order' => 1, 'description' => 'Headliner',
        ]);
        $this->assertDatabaseMissing('event_parts', ['id' => $stale->id]);
        $this->assertSame(2, $event->parts()->count());
    }
}
