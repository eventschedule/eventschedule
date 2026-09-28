<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\SubscriptionCancellation;
use App\Services\GrowthExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Stripe\StripeClient;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * subscription_cancellations: why subscribers leave.
 *
 * On 2026-08-30 there were 18 cancelled subscriptions against 6 active, and not one reason: the
 * in-app cancel asked "are you sure", and a cancel in the Stripe portal left nothing but
 * subscriptions.ends_at. Every cancel path now writes one row per subscription.
 *
 * Stripe is faked at the container, as in DeletingCancelsBillingTest: Cashier resolves its client
 * with app(StripeClient::class), so the real Subscription::cancel() and resume() run.
 */
class SubscriptionCancellationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private object $stripeSubscriptions;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true, 'cashier.webhook.secret' => null]);

        $this->stripeSubscriptions = new class
        {
            public array $updates = [];

            public function update($id, $params = null, $opts = null)
            {
                $this->updates[] = [$id, $params];

                return \Stripe\Subscription::constructFrom([
                    'id' => $id,
                    'status' => 'active',
                    'cancel_at_period_end' => (bool) ($params['cancel_at_period_end'] ?? false),
                    'current_period_end' => now()->addDays(20)->timestamp,
                ]);
            }

            public function cancel($id, $params = null, $opts = null)
            {
                return \Stripe\Subscription::constructFrom(['id' => $id, 'status' => 'canceled']);
            }
        };

        $client = new class($this->stripeSubscriptions)
        {
            public function __construct(public object $subscriptions) {}
        };

        $this->app->bind(StripeClient::class, fn () => $client);
    }

    /** An owner with a live Pro subscription; returns [owner, role, stripe subscription id]. */
    private function subscriber(): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['plan_type' => 'pro', 'plan_term' => 'month', 'plan_expires' => null]);
        DB::table('roles')->where('id', $role->id)->update(['stripe_id' => 'cus_'.Str::random(14)]);

        $stripeId = 'sub_'.Str::random(14);
        DB::table('subscriptions')->insert([
            'role_id' => $role->id,
            'type' => 'default',
            'stripe_id' => $stripeId,
            'stripe_status' => 'active',
            'stripe_price' => 'price_pro_monthly',
            'quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return [$owner, $role->fresh(), $stripeId];
    }

    private function cancelInApp($owner, Role $role, array $input = [])
    {
        return $this->actingAs($owner)
            ->from(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan']))
            ->post(route('subscription.cancel', ['subdomain' => $role->subdomain]), $input);
    }

    /** Cashier's own updated handler reads the first item, so a real payload always has one. */
    private function items(): array
    {
        return ['data' => [[
            'id' => 'si_'.Str::random(10),
            'price' => ['id' => 'price_pro_monthly', 'product' => 'prod_test'],
            'quantity' => 1,
            'current_period_end' => now()->addDays(20)->timestamp,
        ]]];
    }

    private function webhook(string $type, array $object, array $previous = [])
    {
        $data = ['object' => $object];
        if ($previous) {
            $data['previous_attributes'] = $previous;
        }

        return $this->postJson(route('stripe.subscription_webhook'), ['type' => $type, 'data' => $data])->assertOk();
    }

    public function test_the_in_app_cancel_records_the_reason_and_comment(): void
    {
        [$owner, $role, $stripeId] = $this->subscriber();

        $this->cancelInApp($owner, $role, ['reason' => 'too_expensive', 'comment' => 'Only need it in summer'])
            ->assertRedirect();

        $row = SubscriptionCancellation::sole();
        $this->assertSame('app', $row->source);
        $this->assertSame('too_expensive', $row->reason);
        $this->assertSame('Only need it in summer', $row->comment);
        $this->assertSame($stripeId, $row->stripe_subscription_id);
        $this->assertSame('pro', $row->plan_type);
        $this->assertSame($owner->id, $row->user_id);
    }

    /** Asking why must never stand between someone and cancelling. */
    public function test_the_reason_is_optional(): void
    {
        [$owner, $role] = $this->subscriber();

        $this->cancelInApp($owner, $role)->assertRedirect()->assertSessionHasNoErrors();

        $this->assertNull(SubscriptionCancellation::sole()->reason);
        $this->assertNotNull(DB::table('subscriptions')->where('role_id', $role->id)->value('ends_at'), 'it really cancelled');
    }

    public function test_an_unknown_reason_is_refused_before_anything_is_cancelled(): void
    {
        [$owner, $role] = $this->subscriber();

        $this->cancelInApp($owner, $role, ['reason' => 'rm -rf'])->assertSessionHasErrors('reason');

        $this->assertSame(0, SubscriptionCancellation::count());
        $this->assertSame([], $this->stripeSubscriptions->updates, 'Stripe was never asked to cancel');
    }

    /**
     * The in-app cancel triggers its own subscription.updated webhook, which can arrive either
     * side of the row. Either order ends as ONE row, sourced 'app', with the reason given.
     */
    public function test_the_webhook_an_in_app_cancel_triggers_does_not_add_a_second_row(): void
    {
        [$owner, $role, $stripeId] = $this->subscriber();
        $object = ['id' => $stripeId, 'customer' => $role->stripe_id, 'status' => 'active', 'cancel_at_period_end' => true,
            'current_period_end' => now()->addDays(20)->timestamp, 'items' => $this->items()];

        // Webhook first, then the request that caused it writes its row.
        $this->webhook('customer.subscription.updated', $object, ['cancel_at_period_end' => false]);
        $this->cancelInApp($owner, $role, ['reason' => 'season_over']);

        $row = SubscriptionCancellation::sole();
        $this->assertSame('app', $row->source);
        $this->assertSame('season_over', $row->reason);
    }

    public function test_a_portal_cancel_records_stripes_feedback(): void
    {
        [, $role, $stripeId] = $this->subscriber();

        $this->webhook('customer.subscription.updated', [
            'id' => $stripeId, 'customer' => $role->stripe_id, 'status' => 'active',
            'cancel_at_period_end' => true, 'current_period_end' => now()->addDays(20)->timestamp,
            'items' => $this->items(),
            'cancellation_details' => ['reason' => 'cancellation_requested', 'feedback' => 'missing_features', 'comment' => 'No seating'],
        ], ['cancel_at_period_end' => false]);

        $row = SubscriptionCancellation::sole();
        $this->assertSame('portal', $row->source);
        $this->assertSame('missing_feature', $row->reason);
        $this->assertSame('No seating', $row->comment);
    }

    /**
     * Every renewal and card change is also a subscription.updated with cancel_at_period_end
     * false. Reading "not cancelling" off those would resume real cancellations.
     */
    public function test_an_unrelated_update_neither_records_nor_resumes(): void
    {
        [, $role, $stripeId] = $this->subscriber();
        SubscriptionCancellation::record(['role_id' => $role->id, 'stripe_subscription_id' => $stripeId, 'source' => 'app']);

        $this->webhook('customer.subscription.updated', [
            'id' => $stripeId, 'customer' => $role->stripe_id, 'status' => 'active',
            'cancel_at_period_end' => false, 'items' => $this->items(),
        ], ['current_period_end' => now()->timestamp]);

        $this->assertNull(SubscriptionCancellation::sole()->resumed_at);
    }

    public function test_resuming_in_app_takes_the_cancel_back(): void
    {
        [$owner, $role] = $this->subscriber();

        $this->cancelInApp($owner, $role, ['reason' => 'not_using']);
        $this->actingAs($owner)->post(route('subscription.resume', ['subdomain' => $role->subdomain]))->assertRedirect();

        $this->assertNotNull(SubscriptionCancellation::sole()->resumed_at);
    }

    public function test_resuming_in_the_portal_takes_the_cancel_back(): void
    {
        [, $role, $stripeId] = $this->subscriber();
        SubscriptionCancellation::record(['role_id' => $role->id, 'stripe_subscription_id' => $stripeId, 'source' => 'portal']);

        $this->webhook('customer.subscription.updated', [
            'id' => $stripeId, 'customer' => $role->stripe_id, 'status' => 'active',
            'cancel_at_period_end' => false, 'items' => $this->items(),
        ], ['cancel_at_period_end' => true]);

        $this->assertNotNull(SubscriptionCancellation::sole()->resumed_at);
    }

    /** Payments that failed for good are involuntary churn, and counted apart. */
    public function test_a_subscription_that_ended_on_failed_payments_is_counted(): void
    {
        [, $role, $stripeId] = $this->subscriber();

        $this->webhook('customer.subscription.deleted', [
            'id' => $stripeId, 'customer' => $role->stripe_id, 'status' => 'canceled',
            'cancellation_details' => ['reason' => 'payment_failed'],
        ]);

        $this->assertSame('payment_failed', SubscriptionCancellation::sole()->source);
    }

    /** The period ending after an in-app cancel is the same cancellation, not a second one. */
    public function test_the_period_ending_after_a_cancel_adds_no_row(): void
    {
        [$owner, $role, $stripeId] = $this->subscriber();
        $this->cancelInApp($owner, $role, ['reason' => 'too_expensive']);

        $this->webhook('customer.subscription.deleted', [
            'id' => $stripeId, 'customer' => $role->stripe_id, 'status' => 'canceled',
            'cancellation_details' => ['reason' => 'cancellation_requested'],
        ]);

        $row = SubscriptionCancellation::sole();
        $this->assertSame('app', $row->source);
        $this->assertSame('too_expensive', $row->reason);
    }

    public function test_deleting_the_schedule_counts_its_subscription(): void
    {
        [, $role, $stripeId] = $this->subscriber();

        $role->cancelBillingForDeletion();

        $row = SubscriptionCancellation::sole();
        $this->assertSame('schedule_deleted', $row->source);
        $this->assertSame($stripeId, $row->stripe_subscription_id);
    }

    /** A schedule changing hands cancels the old owner's plan, but nobody left. */
    public function test_a_transfer_is_recorded_as_a_transfer_and_kept_out_of_churn(): void
    {
        [, $role, $stripeId] = $this->subscriber();

        $service = app(\App\Services\ScheduleTransferService::class);
        $cancel = new \ReflectionMethod($service, 'cancelSubscription');
        $cancel->setAccessible(true);
        $this->assertTrue($cancel->invoke($service, $role->fresh()));

        // The webhook that cancel triggers must not relabel it.
        $this->webhook('customer.subscription.updated', [
            'id' => $stripeId, 'customer' => $role->stripe_id, 'status' => 'active',
            'cancel_at_period_end' => true, 'current_period_end' => now()->addDays(20)->timestamp,
            'items' => $this->items(),
        ], ['cancel_at_period_end' => false]);

        $this->assertSame('transfer', SubscriptionCancellation::sole()->source);

        $churn = app(GrowthExportService::class)->build(now()->subDays(30), now(), now()->subDays(60), now()->subDays(31))['churn'];
        $this->assertSame(0, $churn['cancelled']);
        $this->assertSame(1, $churn['transferred']);
    }

    /** Stripe first, row second: when the webhook lands in between, the known source still wins. */
    public function test_a_schedule_deletion_replaces_the_webhooks_portal_guess(): void
    {
        [, $role, $stripeId] = $this->subscriber();

        $this->webhook('customer.subscription.deleted', [
            'id' => $stripeId, 'customer' => $role->stripe_id, 'status' => 'canceled',
            'cancellation_details' => ['reason' => 'cancellation_requested'],
        ]);
        $this->assertSame('portal', SubscriptionCancellation::sole()->source);

        SubscriptionCancellation::record(['role_id' => $role->id, 'stripe_subscription_id' => $stripeId, 'source' => 'schedule_deleted']);

        $this->assertSame('schedule_deleted', SubscriptionCancellation::sole()->source);
    }

    /** A schedule already gone is still named, from the local subscription row. */
    public function test_the_deleted_webhook_names_a_schedule_that_is_already_gone(): void
    {
        [, $role, $stripeId] = $this->subscriber();
        $customer = $role->stripe_id;
        DB::table('roles')->where('id', $role->id)->delete();

        $this->webhook('customer.subscription.deleted', [
            'id' => $stripeId, 'customer' => $customer, 'status' => 'canceled',
            'cancellation_details' => ['reason' => 'cancellation_requested'],
        ]);

        $this->assertSame($role->id, SubscriptionCancellation::sole()->role_id);
    }

    public function test_a_disputed_payment_is_its_own_source(): void
    {
        [, $role, $stripeId] = $this->subscriber();

        $this->webhook('customer.subscription.deleted', [
            'id' => $stripeId, 'customer' => $role->stripe_id, 'status' => 'canceled',
            'cancellation_details' => ['reason' => 'payment_disputed'],
        ]);

        $this->assertSame('payment_disputed', SubscriptionCancellation::sole()->source);
    }

    /** A subscription that never started is not churn when its schedule is deleted. */
    public function test_deleting_a_schedule_with_a_never_paid_subscription_records_nothing(): void
    {
        [, $role] = $this->subscriber();
        DB::table('subscriptions')->where('role_id', $role->id)->update(['stripe_status' => 'incomplete']);

        $role->fresh()->cancelBillingForDeletion();

        $this->assertSame(0, SubscriptionCancellation::count());
    }

    /** The words someone wrote about leaving go with their account; the count stays. */
    public function test_deleting_the_account_clears_the_comment_but_keeps_the_row(): void
    {
        [$owner, $role] = $this->subscriber();
        $this->cancelInApp($owner, $role, ['reason' => 'too_expensive', 'comment' => 'my own words']);

        $owner->fresh()->delete();

        $row = SubscriptionCancellation::sole();
        $this->assertNull($row->comment);
        $this->assertSame('too_expensive', $row->reason);
    }

    public function test_the_plan_tab_asks_why(): void
    {
        [$owner, $role] = $this->subscriber();

        $html = $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('messages.cancel_reason_question'), $html);
        foreach (SubscriptionCancellation::REASONS as $reason) {
            $this->assertStringContainsString('value="'.$reason.'"', $html);
            $this->assertStringNotContainsString('messages.cancel_reason_'.$reason, $html);
        }
    }

    public function test_the_export_reports_churn_without_the_comments(): void
    {
        SubscriptionCancellation::record(['stripe_subscription_id' => 'sub_a', 'source' => 'app', 'reason' => 'too_expensive', 'comment' => 'private words']);
        SubscriptionCancellation::record(['stripe_subscription_id' => 'sub_b', 'source' => 'portal']);
        SubscriptionCancellation::record(['stripe_subscription_id' => 'sub_c', 'source' => 'app', 'reason' => 'season_over']);
        SubscriptionCancellation::markResumed('sub_c');

        $export = app(GrowthExportService::class)->build(now()->subDays(30), now(), now()->subDays(60), now()->subDays(31));

        $this->assertSame(2, $export['churn']['cancelled']);
        $this->assertSame(1, $export['churn']['resumed']);
        $this->assertSame(1, $export['churn']['with_reason']);
        $this->assertEquals(['too_expensive' => 1, 'none' => 1], collect($export['churn']['by_reason'])->all());
        $this->assertStringNotContainsString('private words', json_encode($export));
    }
}
