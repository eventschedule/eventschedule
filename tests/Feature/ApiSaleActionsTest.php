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

    public function test_api_mark_paid_of_a_grouped_order_books_the_whole_group(): void
    {
        [$primary, $event, $key] = $this->createGroupedPaidOrder(30.0);

        // Back to the pre-payment state: nothing booked yet, which is what mark_paid acts on.
        Sale::where('group_id', $primary->id)->update(['status' => 'unpaid']);

        $this->assertSame(0.0, $this->revenueFor($event->id));

        $this->withHeaders(['X-API-Key' => $key])
            ->putJson('/api/sales/'.UrlUtils::encodeId($primary->id), ['action' => 'mark_paid'])
            ->assertStatus(200);

        // The order is worth 60.00. Booking only the primary's own 30.00 under-reports revenue,
        // and the guest row is marked paid by the cascade so nothing else ever books its share.
        $this->assertSame(60.0, $this->revenueFor($event->id));
        $this->assertSame('paid', Sale::where('email', 'guest2@example.com')->firstOrFail()->status);
    }

    public function test_admin_approving_an_amount_mismatch_grouped_order_books_the_whole_group(): void
    {
        [$primary, $event] = $this->createGroupedPaidOrder(30.0);

        // A grouped order is exactly what lands in amount_mismatch: StripeController compares the
        // charge against the GROUP total, so it is the group total that failed the check.
        Sale::where('group_id', $primary->id)->update(['status' => 'amount_mismatch']);

        $admin = $this->createOwner();
        $admin->is_admin = true;
        $admin->save();

        // The admin middleware also gates on a password re-confirmation for the session.
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($admin)
            ->post(route('admin.sale.approve', ['sale' => $primary->id]))
            ->assertRedirect();

        $this->assertSame('paid', $primary->fresh()->status);
        $this->assertSame(60.0, $this->revenueFor($event->id));
    }

    public function test_the_api_list_returns_only_sales_its_own_detail_route_will_serve(): void
    {
        // GET /api/sales used to scope by a bare owner|admin pivot pluck while show()/update()/
        // destroy() used canViewEventData(), so a curator's admin could list a sale and then 403
        // fetching it. Both sides are on Event::managedBy() now; this pins that they agree.
        $curator = $this->createCurator($this->createOwner());
        $curatorAdmin = $this->createOwner();
        $this->followRole($curatorAdmin, $curator, 'admin');
        $key = $this->apiKey($curatorAdmin);

        // Created by the curator - visible, and actionable.
        $own = $this->createEvent($curator, ['name' => 'Curator Own', 'creator_role_id' => $curator->id]);
        $ownSale = $this->createSale($own, $curator, ['name' => 'Own Buyer', 'status' => 'paid']);

        // Only LISTED by the curator - the money settles elsewhere.
        $foreign = $this->createEvent($this->createRole($this->createOwner()), ['name' => 'Foreign']);
        $foreign->roles()->attach($curator->id, ['is_accepted' => true]);
        $foreignSale = $this->createSale($foreign, $curator, ['name' => 'Foreign Buyer', 'status' => 'paid']);

        $list = $this->getJson('/api/sales', ['X-API-Key' => $key])->assertOk();
        $names = collect($list->json('data'))->pluck('name')->all();

        $this->assertContains('Own Buyer', $names);
        $this->assertNotContains('Foreign Buyer', $names, 'a curator that only lists does not own the buyers');

        // And every listed row is fetchable - the invariant the mismatch broke.
        $this->getJson('/api/sales/'.UrlUtils::encodeId($ownSale->id), ['X-API-Key' => $key])->assertOk();
        $this->getJson('/api/sales/'.UrlUtils::encodeId($foreignSale->id), ['X-API-Key' => $key])
            ->assertStatus(403);
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
