<?php

namespace Tests\Feature;

use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The plan gate on POST /api/sales.
 *
 * This endpoint had NO per-row check at all: it gated the event once on canSellTickets() and then
 * validated each ticket for sales windows only. Because canSellTickets() stays true while any $0
 * row survives, a free schedule could add one zero-price tier and sell priced tickets through the
 * API without limit - the web path's own per-row loop was the only thing stopping it, and the API
 * does not go through it.
 *
 * Nothing exercised POST /api/sales before this file, so the gate and the reordering it needed
 * (event_date is resolved above the gates now) were both unpinned.
 */
class ApiSaleCreateGateTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.hosted' => true]);
    }

    /** Configure an API key on the user and return the raw key for the X-API-Key header. */
    private function apiKey(User $user): string
    {
        $raw = 'testapikey_'.Str::random(24);
        $user->api_key = substr(hash('sha256', $raw), 0, 8);
        $user->api_key_hash = Hash::make($raw);
        $user->save();

        return $raw;
    }

    private function postSale(string $key, $event, $ticket, int $quantity = 1)
    {
        return $this->withHeaders(['X-API-Key' => $key])->postJson('/api/sales', [
            'event_id' => UrlUtils::encodeId($event->id),
            'name' => 'API Buyer',
            'email' => 'buyer@gmail.com',
            'payment_method' => 'cash',
            'tickets' => [UrlUtils::encodeId($ticket->id) => $quantity],
        ]);
    }

    public function test_a_free_schedule_cannot_sell_a_priced_ticket_through_the_api(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 50]);

        $this->postSale($this->apiKey($owner), $event, $ticket)->assertStatus(422);

        $this->assertDatabaseCount('sales', 0);
    }

    /**
     * The bypass this gate closes: one $0 row keeps canSellTickets() true for the whole event, so
     * without a PER-ROW check the priced row rides through on its coat-tails.
     */
    public function test_a_zero_price_row_does_not_carry_a_priced_row_through_the_api(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $free = $this->createTicket($event, ['price' => 0, 'quantity' => 50]);
        $paid = $this->createTicket($event, ['price' => 20, 'quantity' => 50]);

        $key = $this->apiKey($owner);

        $this->assertTrue($event->fresh()->canSellTickets(), 'the $0 row keeps the event selling');

        $this->postSale($key, $event, $paid)->assertStatus(422);
        $this->assertDatabaseCount('sales', 0);

        // ...and the free row still sells, which is the promise the $0 carve-out exists for.
        $this->postSale($key, $event, $free)->assertSuccessful();
        $this->assertDatabaseCount('sales', 1);
    }

    /**
     * Add-ons are a separate pass because $event->tickets is scoped where('is_addon', false), so an
     * add-on never appears in the per-ticket loop. The persist loop does not gate them either, so
     * without an explicit check a free schedule sells an arbitrarily priced add-on alongside a
     * free admission row.
     */
    public function test_a_free_schedule_cannot_sell_an_addon_through_the_api(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $free = $this->createTicket($event, ['price' => 0, 'quantity' => 50]);
        $addon = $this->createTicket($event, ['price' => 15, 'quantity' => 50, 'is_addon' => true]);

        $response = $this->withHeaders(['X-API-Key' => $this->apiKey($owner)])->postJson('/api/sales', [
            'event_id' => UrlUtils::encodeId($event->id),
            'name' => 'API Buyer',
            'email' => 'buyer@gmail.com',
            'payment_method' => 'cash',
            'tickets' => [UrlUtils::encodeId($free->id) => 1],
            'addons' => [UrlUtils::encodeId($addon->id) => 1],
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('sales', 0);
    }

    public function test_a_pro_schedule_sells_a_priced_ticket_through_the_api(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 50]);

        $this->postSale($this->apiKey($owner), $event, $ticket)->assertSuccessful();

        $this->assertDatabaseCount('sales', 1);
    }

    public function test_a_grandfathered_event_still_sells_through_the_api(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 50]);
        $event->forceFill(['tickets_grandfathered_at' => now()])->saveQuietly();

        $this->postSale($this->apiKey($owner), $event, $ticket)->assertSuccessful();

        $this->assertDatabaseCount('sales', 1);
    }
}
