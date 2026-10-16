<?php

namespace Tests\Feature;

use App\Models\TicketPreset;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * 2026_10_16_000000 copies every schedule's roles.default_tickets (the old "Save as default") into a
 * preset, the default, with no name and no way of signing up, so new events start as they did.
 */
class TicketPresetMigrationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function runMigration(): void
    {
        $migration = require database_path('migrations/2026_10_16_000000_create_ticket_presets_table.php');
        $migration->up();
    }

    public function test_the_old_default_becomes_the_default_preset(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue');
        DB::table('roles')->where('id', $role->id)->update(['default_tickets' => json_encode([
            'currency_code' => 'EUR', 'payment_method' => 'cash', 'ticket_notes' => 'Bring ID',
            'tickets' => [['type' => 'Entry', 'price' => 5, 'is_pass' => true, 'pass_scope' => 'this_event', 'seating_band' => 'A']],
            'promo_codes' => [['code' => 'HALF', 'type' => 'percentage', 'value' => 50, 'ticket_ids' => [12, 13]]],
        ])]);

        $this->runMigration();

        $preset = TicketPreset::where('role_id', $role->id)->sole();
        $this->assertNull($preset->name);
        $this->assertNull($preset->kind);
        $this->assertTrue($preset->is_default);
        $this->assertSame(__('messages.ticket_preset_usual'), $preset->label());
        $this->assertSame('Bring ID', $preset->settings['ticket_notes']);
        $this->assertTrue($preset->settings['tickets'][0]['is_pass'], 'kept as saved: a new event opens on it as it did');
        $this->assertSame([], $preset->ticketRows(), 'and a preset laid over an event never turns a pass into a ticket that admits once');
        $this->assertArrayNotHasKey('ticket_ids', $preset->settings['promo_codes'][0], 'another event\'s ticket ids would make a code that applies to nothing');
    }

    /**
     * What a schedule that used "Save as default" sees on its next new event is what it saw before:
     * checked against the old code side by side (the same form data, field for field) when this
     * was written, and pinned here on the fields that differ most easily.
     */
    public function test_a_new_event_opens_on_the_old_default_as_it_did(): void
    {
        config(['app.hosted' => false, 'payments.default_method' => 'stripe', 'services.stripe_platform.secret' => 'sk_test_x', 'services.stripe_platform.key' => 'pk_test_x']);
        $owner = $this->createOwner();
        $venue = $this->createRole($owner, 'venue', ['country_code' => 'de']);
        // The last event was free registration, and that is the mode a new event starts in.
        $this->createEvent($venue, ['creator_role_id' => $venue->id, 'rsvp_enabled' => true, 'tickets_enabled' => false]);
        DB::table('roles')->where('id', $venue->id)->update(['default_tickets' => json_encode([
            'currency_code' => 'EUR', 'payment_instructions' => 'Pay at the door', 'ask_phone' => true,
            'tickets' => [['type' => 'Entry', 'price' => 5, 'quantity' => 100], ['type' => 'Reduced', 'price' => 3]],
            'promo_codes' => [['code' => 'HALF', 'type' => 'percentage', 'value' => 50, 'is_active' => true]],
        ])]);

        $this->runMigration();

        $response = $this->actingAs($owner)->get(route('event.create', ['subdomain' => $venue->subdomain]))->assertOk();
        $event = $response->viewData('event');

        $this->assertStringContainsString('ticketMode: "rsvp"', $response->getContent());
        $this->assertSame('EUR', $event->ticket_currency_code);
        $this->assertSame('stripe', $event->payment_method, 'a default that named no method keeps DEFAULT_PAYMENT_METHOD, as before');
        $this->assertSame('Pay at the door', $event->payment_instructions);
        $this->assertTrue((bool) $event->ask_phone);
        $this->assertSame([['Entry', 5.0, 100], ['Reduced', 3.0, null]], collect($event->tickets)->map(fn ($t) => [$t->type, (float) $t->price, $t->quantity])->all());
        $this->assertSame('HALF', $response->viewData('defaultPromoCodes')[0]['code']);
    }

    public function test_it_skips_what_is_not_json_and_can_run_twice(): void
    {
        $broken = $this->createRole($this->createOwner(), 'venue');
        $fine = $this->createRole($this->createOwner(), 'venue');
        DB::table('roles')->where('id', $broken->id)->update(['default_tickets' => '{not json']);
        DB::table('roles')->where('id', $fine->id)->update(['default_tickets' => json_encode(['currency_code' => 'USD'])]);

        $this->runMigration();
        $this->runMigration();

        $this->assertSame(0, TicketPreset::where('role_id', $broken->id)->count());
        $this->assertSame(1, TicketPreset::where('role_id', $fine->id)->count());
    }
}
