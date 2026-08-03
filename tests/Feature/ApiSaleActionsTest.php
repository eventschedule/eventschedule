<?php

namespace Tests\Feature;

use App\Models\AnalyticsEventsDaily;
use App\Models\Sale;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The sale status actions exposed over the API (PUT/DELETE /api/sales/{id}).
 *
 * These used to carry their own copy of the admin portal's logic and had drifted: analytics were
 * decremented by the row's own payment_amount rather than the group total, so refunding a grouped
 * order through the API left the guests' revenue on the books. Both paths now share
 * App\Traits\HandlesSaleStatusActions.
 */
class ApiSaleActionsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /** Configure an API key on the user and return the raw key for the X-API-Key header. */
    private function apiKey(User $user): string
    {
        $raw = 'testapikey_'.Str::random(24);
        $user->api_key = substr(hash('sha256', $raw), 0, 8);
        $user->api_key_hash = Hash::make($raw);
        $user->save();

        return $raw;
    }

    /**
     * A grouped order: a primary seat and one guest seat, each carrying half the money, both paid.
     * Mirrors what individual-tickets checkout writes.
     *
     * @return array{0: Sale, 1: \App\Models\Event, 2: string}
     */
    private function createGroupedPaidOrder(float $perSeat = 30.0): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['price' => $perSeat, 'quantity' => 50]);

        $primary = $this->createSale($event, $role, [
            'name' => 'Primary Buyer',
            'email' => 'primary@example.com',
            'payment_amount' => $perSeat,
            'payment_method' => 'stripe',
            'status' => 'paid',
        ], $ticket);
        $primary->group_id = $primary->id;
        $primary->saveQuietly();

        $guest = $this->createSale($event, $role, [
            'name' => 'Guest Two',
            'email' => 'guest2@example.com',
            'payment_amount' => $perSeat,
            'payment_method' => 'stripe',
            'status' => 'paid',
        ], $ticket);
        $guest->group_id = $primary->id;
        $guest->saveQuietly();

        return [$primary->fresh(), $event, $this->apiKey($owner)];
    }

    private function revenueFor(int $eventId): float
    {
        return (float) (AnalyticsEventsDaily::where('event_id', $eventId)
            ->where('date', now()->toDateString())
            ->value('revenue') ?? 0);
    }

    public function test_api_refund_of_a_grouped_order_decrements_the_whole_group(): void
    {
        [$primary, $event, $key] = $this->createGroupedPaidOrder(30.0);

        // Checkout books the whole order's revenue against the primary, so a refund has to give
        // all of it back. Decrementing only the primary's own 30.00 would strand the guest's share.
        AnalyticsEventsDaily::incrementSale($event->id, 60.0);
        $this->assertSame(60.0, $this->revenueFor($event->id));

        $response = $this->withHeaders(['X-API-Key' => $key])
            ->putJson('/api/sales/'.UrlUtils::encodeId($primary->id), ['action' => 'refund']);

        $response->assertStatus(200);
        $this->assertSame('refunded', $primary->fresh()->status);

        // The buggy version left 30.00 here.
        $this->assertSame(0.0, $this->revenueFor($event->id));
    }

    public function test_api_refund_cascades_to_guest_rows(): void
    {
        [$primary, $event, $key] = $this->createGroupedPaidOrder();

        $this->withHeaders(['X-API-Key' => $key])
            ->putJson('/api/sales/'.UrlUtils::encodeId($primary->id), ['action' => 'refund'])
            ->assertStatus(200);

        $guest = Sale::where('email', 'guest2@example.com')->firstOrFail();
        $this->assertSame('refunded', $guest->status);
    }

    public function test_api_delete_of_a_grouped_order_decrements_the_whole_group(): void
    {
        [$primary, $event, $key] = $this->createGroupedPaidOrder(30.0);

        AnalyticsEventsDaily::incrementSale($event->id, 60.0);

        $this->withHeaders(['X-API-Key' => $key])
            ->deleteJson('/api/sales/'.UrlUtils::encodeId($primary->id))
            ->assertStatus(200);

        $this->assertSame(0.0, $this->revenueFor($event->id));

        $guest = Sale::where('email', 'guest2@example.com')->firstOrFail();
        $this->assertTrue((bool) $guest->is_deleted, 'is_deleted must cascade to guest rows');
    }

    public function test_api_refund_rejects_a_sale_that_is_not_paid(): void
    {
        [$primary, , $key] = $this->createGroupedPaidOrder();
        $primary->status = 'unpaid';
        $primary->saveQuietly();

        $this->withHeaders(['X-API-Key' => $key])
            ->putJson('/api/sales/'.UrlUtils::encodeId($primary->id), ['action' => 'refund'])
            ->assertStatus(422);
    }
}
