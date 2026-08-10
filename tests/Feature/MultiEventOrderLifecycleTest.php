<?php

namespace Tests\Feature;

use App\Models\AnalyticsEventsDaily;
use App\Models\PromoCode;
use App\Models\Sale;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * What happens to a multi-event order after checkout: expiry, deletion, admin approval and the
 * counters that report it.
 *
 * These are the paths the world can change underneath an order. The order itself is written once
 * and then lives for weeks, so each of them is reachable in ordinary use.
 */
class MultiEventOrderLifecycleTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    /**
     * Two unpaid legs of one order, on the cash rail - the shape a cart writes.
     *
     * @return array{0: Sale, 1: Sale, 2: \App\Models\Event, 3: \App\Models\Event, 4: \App\Models\Role}
     */
    private function createTwoLegOrder(string $paymentMethod = 'cash'): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $base = ['tickets_enabled' => true, 'payment_method' => $paymentMethod, 'ticket_currency_code' => 'USD'];
        $eventA = $this->createEvent($role, $base);
        $eventB = $this->createEvent($role, $base);

        $legA = $this->createSale($eventA, $role, [
            'email' => 'lifecycle@gmail.com', 'payment_amount' => 50,
            'payment_method' => $paymentMethod, 'status' => 'unpaid',
        ], $this->createTicket($eventA, ['price' => 50, 'quantity' => 50]));

        $legB = $this->createSale($eventB, $role, [
            'email' => 'lifecycle@gmail.com', 'payment_amount' => 30,
            'payment_method' => $paymentMethod, 'status' => 'unpaid',
        ], $this->createTicket($eventB, ['price' => 30, 'quantity' => 50]));

        $legA->order_id = $legA->id;
        $legA->saveQuietly();
        $legB->order_id = $legA->id;
        $legB->saveQuietly();

        return [$legA->fresh(), $legB->fresh(), $eventA, $eventB, $role];
    }

    private function revenueFor(int $eventId): float
    {
        return (float) (AnalyticsEventsDaily::where('event_id', $eventId)
            ->where('date', now()->toDateString())
            ->value('revenue') ?? 0);
    }

    public function test_expiring_an_order_leaves_an_already_paid_leg_alone(): void
    {
        [$legA, $legB, , $eventB] = $this->createTwoLegOrder();

        // The organizer collected for event B at the door. A leg is not the anchor and has no
        // group, so nothing cascades and the anchor stays unpaid - which is what later drags the
        // whole order into the expiry sweep.
        $legB->status = 'paid';
        $legB->save();
        $this->assertSame('unpaid', $legA->fresh()->status, 'paying a leg must not settle the order');

        $legA->refresh();
        $legA->status = 'expired';
        $legA->save();

        // Expiry releases what nobody paid for. Flipping the collected leg to expired handed its
        // seats back, re-credited its gift-card share and left the money uncounted, with no refund.
        $this->assertSame('paid', $legB->fresh()->status,
            'expiry must never release a leg the buyer has already paid for');
        $this->assertSame('expired', $legA->fresh()->status);
    }

    public function test_cancelling_an_order_still_reaches_a_paid_leg(): void
    {
        [$legA, $legB] = $this->createTwoLegOrder();

        $legB->status = 'paid';
        $legB->save();

        $legA->refresh();
        $legA->status = 'cancelled';
        $legA->save();

        // The guard above is specific to expiry: cancelling or refunding an order is meant to
        // reach every leg, paid ones included.
        $this->assertSame('cancelled', $legB->fresh()->status);
    }

    public function test_deleting_the_anchors_event_leaves_the_other_leg_releasable(): void
    {
        [$legA, $legB, $eventA, $eventB] = $this->createTwoLegOrder();

        // sales.event_id cascades on delete, so this destroys the anchor row outright.
        $eventA->delete();
        $this->assertNull(Sale::find($legA->id), 'the anchor row is destroyed with its event');

        // The self-referencing foreign key drops the dangling pointer, so the survivor becomes an
        // ordinary standalone sale. Left dangling, ReleaseTickets resolved order_id to a missing
        // row and skipped it on every run - holding its seats forever.
        $legB->refresh();
        $this->assertNull($legB->order_id, 'a survivor must not keep pointing at a destroyed anchor');

        $eventB->expire_unpaid_tickets = 1;
        $eventB->save();
        Sale::where('id', $legB->id)->update(['created_at' => now()->subHours(5)]);

        $this->artisan('app:release-tickets');

        $this->assertSame('expired', $legB->fresh()->status,
            'the survivor must still be released once its own window elapses');
    }

    public function test_a_leg_left_pointing_at_a_missing_anchor_is_still_released(): void
    {
        [$legA, $legB, , $eventB] = $this->createTwoLegOrder();

        // Rows written before the foreign key existed can still carry a dangling order_id, and no
        // migration can invent the anchor back. Recreated here by deleting the anchor with the
        // constraint suspended, which is the state those installs are already in.
        \DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Sale::where('id', $legA->id)->delete();
        \DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->assertSame($legA->id, (int) $legB->fresh()->order_id, 'the dangling pointer survives');

        $eventB->expire_unpaid_tickets = 1;
        $eventB->save();
        Sale::where('id', $legB->id)->update(['created_at' => now()->subHours(5)]);

        $this->artisan('app:release-tickets');

        // Resolving order_id to a row that is not there used to bail silently, so these seats and
        // any gift-card hold were held on every run, forever.
        $this->assertSame('expired', $legB->fresh()->status);
    }

    public function test_an_event_with_paid_sales_cannot_be_deleted(): void
    {
        [$legA, , $eventA, , $role] = $this->createTwoLegOrder();

        $legA->status = 'paid';
        $legA->save();

        $this->actingAs($role->user)
            ->delete(route('event.delete', [
                'subdomain' => $role->subdomain,
                'hash' => \App\Utils\UrlUtils::encodeId($eventA->id),
            ]));

        // Deleting would destroy the buyers' sale rows through the FK cascade: no refund trail, no
        // inventory release, no notice to anyone who paid. Cancelling is the supported route.
        $this->assertNotNull($eventA->fresh(), 'an event with paid sales must survive a delete');
    }

    public function test_approving_a_mismatched_order_credits_every_events_revenue(): void
    {
        [$legA, , $eventA, $eventB] = $this->createTwoLegOrder('stripe');

        // Only the order primary reaches this queue: the Stripe webhook writes amount_mismatch onto
        // the row that carried the whole session.
        $legA->status = 'amount_mismatch';
        $legA->saveQuietly();

        // The site-admin routes sit behind a re-confirmed password as well as the admin gate.
        $admin = $this->createOwner(true);
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);
        $this->post(route('admin.sale.approve', ['sale' => $legA->id]));

        $this->assertSame('paid', $legA->fresh()->status);
        $this->assertSame('paid', $legA->fresh()->orderSales()->where('id', '!=', $legA->id)->first()->status);

        // Crediting only the anchoring leg left event B at zero while a later refund - which does
        // work per leg - decremented it anyway, taking the difference out of other buyers' sales.
        $this->assertEqualsWithDelta(50.0, $this->revenueFor($eventA->id), 0.001);
        $this->assertEqualsWithDelta(30.0, $this->revenueFor($eventB->id), 0.001);

        // Approving again is a no-op. This exercises the status check BEFORE the transaction; the
        // matching re-check inside the lock guards the genuinely concurrent case - two admins whose
        // requests both read 'amount_mismatch' before either commits - which sequential test
        // requests cannot reproduce, so nothing here pins it.
        $this->post(route('admin.sale.approve', ['sale' => $legA->id]));

        $this->assertEqualsWithDelta(50.0, $this->revenueFor($eventA->id), 0.001,
            'a second approval must not book the order again');
        $this->assertEqualsWithDelta(30.0, $this->revenueFor($eventB->id), 0.001);
    }

    /**
     * An order's legs do not have to agree: a cart leg primary has no group_id, so the AP lets an
     * owner mark one leg paid on its own - the cash-at-the-door case Sale.php's expiry cascade
     * already documents. Marking the anchor paid afterwards then cascades only to rows that are
     * NOT already paid, so crediting every leg booked that leg's revenue a second time.
     */
    public function test_marking_the_anchor_paid_does_not_re_credit_a_leg_already_paid(): void
    {
        [$legA, $legB, $eventA, $eventB, $role] = $this->createTwoLegOrder();

        $this->actingAs($role->users()->first());

        $this->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($legB->id)]), ['action' => 'mark_paid']);
        $this->assertEqualsWithDelta(30.0, $this->revenueFor($eventB->id), 0.001, 'precondition: leg B is booked once');

        $this->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($legA->id)]), ['action' => 'mark_paid']);

        $this->assertEqualsWithDelta(50.0, $this->revenueFor($eventA->id), 0.001);
        $this->assertEqualsWithDelta(30.0, $this->revenueFor($eventB->id), 0.001,
            'leg B was already paid, so the paid cascade skipped it and it must not be booked twice');
    }

    /**
     * The mirror. The cancel cascade deliberately DOES reach a paid sibling (only expiry is
     * narrowed to unpaid), but the reversal was gated on the SUBJECT's status - so cancelling an
     * unpaid anchor released the paid leg and left its revenue on the books for good.
     */
    public function test_cancelling_an_unpaid_anchor_reverses_the_paid_legs_revenue(): void
    {
        [$legA, $legB, , $eventB, $role] = $this->createTwoLegOrder();

        $this->actingAs($role->users()->first());

        $this->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($legB->id)]), ['action' => 'mark_paid']);
        $this->assertEqualsWithDelta(30.0, $this->revenueFor($eventB->id), 0.001);

        $this->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($legA->id)]), ['action' => 'cancel']);

        $this->assertSame('cancelled', $legB->fresh()->status, 'precondition: the cascade releases the paid leg');
        $this->assertEqualsWithDelta(0.0, $this->revenueFor($eventB->id), 0.001,
            'a cancelled sale must not leave revenue behind');
    }

    /** And the other direction: an unpaid leg was never credited, so it must not be debited. */
    public function test_cancelling_a_paid_anchor_does_not_debit_an_unpaid_leg(): void
    {
        [$legA, , $eventA, $eventB, $role] = $this->createTwoLegOrder();

        $this->actingAs($role->users()->first());

        // Only the anchor is paid; the cascade marks leg B paid too, so both are booked.
        $this->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($legA->id)]), ['action' => 'mark_paid']);
        $this->assertEqualsWithDelta(50.0, $this->revenueFor($eventA->id), 0.001);
        $this->assertEqualsWithDelta(30.0, $this->revenueFor($eventB->id), 0.001);

        $this->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($legA->id)]), ['action' => 'cancel']);

        $this->assertEqualsWithDelta(0.0, $this->revenueFor($eventA->id), 0.001);
        $this->assertEqualsWithDelta(0.0, $this->revenueFor($eventB->id), 0.001);
    }

    /**
     * approveSale() re-derived the per-leg loop and dropped the released-leg filter the trait
     * documents, so a leg the owner had already cancelled was credited anyway - the paid cascade
     * correctly leaves it cancelled, so nothing ever nets it out.
     */
    public function test_approving_a_mismatched_order_skips_a_leg_the_owner_cancelled(): void
    {
        [$legA, $legB, $eventA, $eventB, $role] = $this->createTwoLegOrder();

        $this->actingAs($role->users()->first());
        $this->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($legB->id)]), ['action' => 'cancel']);
        $this->assertSame('cancelled', $legB->fresh()->status);

        $legA->refresh();
        $legA->status = 'amount_mismatch';
        $legA->saveQuietly();

        $admin = $this->createOwner(true);
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);
        $this->post(route('admin.sale.approve', ['sale' => $legA->id]));

        $this->assertEqualsWithDelta(50.0, $this->revenueFor($eventA->id), 0.001);
        $this->assertEqualsWithDelta(0.0, $this->revenueFor($eventB->id), 0.001,
            'a cancelled leg does not transition with the order, so it must not be credited');
    }

    public function test_a_cancelled_order_returns_the_promo_code_it_took(): void
    {
        [$legA, $legB, $eventA, , $role] = $this->createTwoLegOrder();

        $promo = new PromoCode;
        $promo->event_id = $eventA->id;
        $promo->code = 'CART10';
        $promo->type = 'fixed';
        $promo->value = 10;
        $promo->times_used = 0;
        $promo->is_active = true;
        $promo->save();

        // Leg A also uses per-attendee tickets, so it carries guest rows - the shape that makes
        // the counters diverge. checkout() increments once per LEG (2 here), while the decrement
        // in Sale::booted runs for every ROW, and the cancel cascade saves each guest row
        // individually to release its inventory. Ungated that is 4 give-backs for 2 takes.
        $guestOne = $this->createSale($eventA, $role, [
            'email' => 'guest1@gmail.com', 'payment_amount' => 25, 'status' => 'unpaid',
        ]);
        $guestTwo = $this->createSale($eventA, $role, [
            'email' => 'guest2@gmail.com', 'payment_amount' => 25, 'status' => 'unpaid',
        ]);

        $promo->increment('times_used');
        $promo->increment('times_used');

        $legA->group_id = $legA->id;
        $legA->promo_code_id = $promo->id;
        $legA->status = 'paid';
        $legA->saveQuietly();

        foreach ([$guestOne, $guestTwo] as $guest) {
            $guest->group_id = $legA->id;
            $guest->order_id = $legA->id;
            $guest->promo_code_id = $promo->id;
            $guest->status = 'paid';
            $guest->saveQuietly();
        }

        $legB->promo_code_id = $promo->id;
        $legB->status = 'paid';
        $legB->saveQuietly();

        $this->assertSame(2, (int) $promo->fresh()->times_used);

        $legA->refresh();
        $legA->status = 'cancelled';
        $legA->save();

        // Four rows change status, but only two redemptions were ever consumed. Ungated this went
        // to -2, and a max_uses cap read from times_used could then be redeemed past its limit.
        $this->assertSame(0, (int) $promo->fresh()->times_used,
            'a cancelled order must return exactly the redemptions it consumed');
    }

    public function test_boost_conversions_count_the_purchase_not_its_legs(): void
    {
        [$legA, $legB, $eventA] = $this->createTwoLegOrder();

        $campaign = new \App\Models\BoostCampaign;
        $campaign->event_id = $eventA->id;
        $campaign->user_id = $eventA->user_id;
        $campaign->role_id = $eventA->roles->first()->id;
        $campaign->name = 'Cart boost';
        $campaign->status = 'active';
        $campaign->budget_micros = 50000000;
        $campaign->user_budget = 50;
        $campaign->save();

        // newSaleForLeg() stamps the same campaign on every leg from one session's UTM.
        foreach ([$legA, $legB] as $leg) {
            $leg->boost_campaign_id = $campaign->id;
            $leg->status = 'paid';
            $leg->saveQuietly();
        }

        $conversions = app(\App\Services\PromotionAnalyticsService::class)->conversions($campaign);

        $this->assertSame(1, $conversions['count'], 'one boosted checkout is one conversion');
        $this->assertEqualsWithDelta(80.0, $conversions['revenue'], 0.001, 'revenue still spans both legs');
    }
}
