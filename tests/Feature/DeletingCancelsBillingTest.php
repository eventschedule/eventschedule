<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Services\AdminAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Stripe\StripeClient;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Removing a schedule has to stop what it is paying for.
 *
 * The Cashier customer is the Role, and subscriptions.role_id carries no foreign key, so no path
 * that removed a schedule - deleting the account, deleting the schedule, the API, an admin
 * mark-deleted - ever reached Stripe. A customer who deleted their account on 10 Sep 2026 was
 * charged again on 27 Sep, with nothing left in the app for them or us to cancel from.
 *
 * Stripe is faked at the container: Cashier resolves its client with app(StripeClient::class), so
 * the real Subscription::cancelNow() runs, and only the HTTP call is replaced.
 */
class DeletingCancelsBillingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private object $stripeSubscriptions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stripeSubscriptions = new class
        {
            /** @var array<int, string> */
            public array $cancelled = [];

            public ?\Throwable $failWith = null;

            /** What retrieve() reports after a refused cancel. */
            public string $remoteStatus = 'active';

            public function cancel($id, $params = null, $opts = null)
            {
                if ($this->failWith) {
                    throw $this->failWith;
                }

                $this->cancelled[] = $id;

                return \Stripe\Subscription::constructFrom(['id' => $id, 'status' => 'canceled']);
            }

            public function retrieve($id, $params = null, $opts = null)
            {
                return \Stripe\Subscription::constructFrom(['id' => $id, 'status' => $this->remoteStatus]);
            }
        };

        $client = new class($this->stripeSubscriptions)
        {
            public function __construct(public object $subscriptions) {}
        };

        $this->app->bind(StripeClient::class, fn () => $client);
    }

    private function subscribe(Role $role, array $attributes = []): string
    {
        if (! $role->stripe_id) {
            DB::table('roles')->where('id', $role->id)->update(['stripe_id' => 'cus_'.Str::random(14)]);
        }

        $stripeId = 'sub_'.Str::random(14);

        DB::table('subscriptions')->insert(array_merge([
            'role_id' => $role->id,
            'type' => 'default',
            'stripe_id' => $stripeId,
            'stripe_status' => 'active',
            'stripe_price' => 'price_pro_monthly',
            'quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attributes));

        return $stripeId;
    }

    private function statusOf(string $stripeId): string
    {
        return DB::table('subscriptions')->where('stripe_id', $stripeId)->value('stripe_status');
    }

    private function apiKey(User $user): string
    {
        $raw = 'testapikey_'.Str::random(24);
        $user->api_key = substr(hash('sha256', $raw), 0, 8);
        $user->api_key_hash = Hash::make($raw);
        $user->save();

        return $raw;
    }

    /** The reported case. */
    public function test_deleting_an_account_cancels_every_owned_schedules_subscription(): void
    {
        $owner = $this->createOwner();
        $first = $this->createRole($owner);
        $second = $this->createRole($owner, 'talent');
        $firstSub = $this->subscribe($first);
        $secondSub = $this->subscribe($second);

        $this->actingAs($owner)->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertRedirect('/');

        $this->assertNull(User::find($owner->id), 'the account is gone');
        $this->assertEqualsCanonicalizing([$firstSub, $secondSub], $this->stripeSubscriptions->cancelled,
            'both schedules vanish with the account, so both subscriptions must be cancelled in Stripe');
        $this->assertSame('canceled', $this->statusOf($firstSub));
        $this->assertSame('canceled', $this->statusOf($secondSub));
    }

    /**
     * User::roles() filters out soft-deleted schedules, but the database cascade takes them too -
     * and one soft-deleted before this fix may still be billing.
     */
    public function test_deleting_an_account_cancels_a_soft_deleted_schedules_subscription(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $stripeId = $this->subscribe($role);
        DB::table('roles')->where('id', $role->id)->update(['is_deleted' => true]);

        $this->actingAs($owner)->delete(route('profile.destroy'), ['password' => 'password']);

        $this->assertSame([$stripeId], $this->stripeSubscriptions->cancelled);
    }

    public function test_a_stripe_failure_aborts_the_account_deletion(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $stripeId = $this->subscribe($role);
        $this->stripeSubscriptions->failWith = new \Stripe\Exception\ApiConnectionException('Stripe is down');

        $this->actingAs($owner)->delete(route('profile.destroy'), ['password' => 'password'])
            ->assertSessionHas('error', __('messages.delete_subscription_cancel_failed'));

        $this->assertNotNull(User::find($owner->id), 'nothing may be deleted while the plan is still billing');
        $this->assertNotNull(Role::find($role->id));
        $this->assertSame('active', $this->statusOf($stripeId));
    }

    public function test_deleting_a_schedule_cancels_its_subscription(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $stripeId = $this->subscribe($role);

        $this->actingAs($owner)->delete(route('role.delete', ['subdomain' => $role->subdomain]))
            ->assertRedirect();

        $this->assertNull(Role::find($role->id));
        $this->assertSame([$stripeId], $this->stripeSubscriptions->cancelled);
        $this->assertSame('canceled', $this->statusOf($stripeId));
    }

    public function test_a_stripe_failure_aborts_the_schedule_deletion(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->subscribe($role);
        $this->stripeSubscriptions->failWith = new \Stripe\Exception\ApiConnectionException('Stripe is down');

        $this->actingAs($owner)->delete(route('role.delete', ['subdomain' => $role->subdomain]))
            ->assertSessionHas('error', __('messages.delete_subscription_cancel_failed'));

        $this->assertNotNull(Role::find($role->id));
    }

    /**
     * Stripe already ended it (a missed webhook, or cancelled from the dashboard) and only our
     * row is stale. Refusing to delete over a charge that can no longer happen would leave the
     * schedule undeletable forever.
     */
    public function test_a_subscription_stripe_already_ended_does_not_block_deletion(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $stripeId = $this->subscribe($role);
        $this->stripeSubscriptions->failWith = \Stripe\Exception\InvalidRequestException::factory('This subscription is already canceled');
        $this->stripeSubscriptions->remoteStatus = 'canceled';

        $this->actingAs($owner)->delete(route('role.delete', ['subdomain' => $role->subdomain]))
            ->assertSessionMissing('error');

        $this->assertNull(Role::find($role->id));
        $this->assertSame('canceled', $this->statusOf($stripeId));
    }

    /**
     * Fails closed. "No such subscription" is also what Stripe says to the other mode's key, so
     * only Stripe confirming the subscription is over counts.
     */
    public function test_a_refused_cancel_on_a_still_live_subscription_blocks_deletion(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->subscribe($role);
        $this->stripeSubscriptions->failWith = \Stripe\Exception\InvalidRequestException::factory('No such subscription');
        $this->stripeSubscriptions->remoteStatus = 'active';

        $this->actingAs($owner)->delete(route('role.delete', ['subdomain' => $role->subdomain]))
            ->assertSessionHas('error', __('messages.delete_subscription_cancel_failed'));

        $this->assertNotNull(Role::find($role->id));
    }

    public function test_deleting_a_schedule_through_the_api_cancels_its_subscription(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $stripeId = $this->subscribe($role);

        $this->deleteJson('/api/schedules/'.$role->subdomain, [], ['X-API-Key' => $this->apiKey($owner)])
            ->assertSuccessful();

        $this->assertTrue((bool) $role->fresh()->is_deleted);
        $this->assertSame([$stripeId], $this->stripeSubscriptions->cancelled,
            'cancelled once: markDeleted() repeating the call must find nothing live');
    }

    public function test_an_admin_marking_a_schedule_deleted_cancels_its_subscription(): void
    {
        config(['app.hosted' => true]);

        if (! Route::has('admin.schedules.mark_deleted')) {
            $this->markTestSkipped('Hosted-only admin routes are not registered in this environment.');
        }

        $role = $this->createRole($this->createOwner());
        $stripeId = $this->subscribe($role);

        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($this->createOwner(true))
            ->post(route('admin.schedules.mark_deleted', ['role' => $role->encodeId()]))
            ->assertRedirect();

        $this->assertTrue((bool) $role->fresh()->is_deleted);
        $this->assertSame([$stripeId], $this->stripeSubscriptions->cancelled);
    }

    /** Already ending at period end: Stripe will not charge again, so no call is needed. */
    public function test_a_subscription_already_cancelled_at_period_end_is_left_alone(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->subscribe($role, ['ends_at' => now()->addDays(10)]);

        $this->actingAs($owner)->delete(route('role.delete', ['subdomain' => $role->subdomain]));

        $this->assertNull(Role::find($role->id));
        $this->assertSame([], $this->stripeSubscriptions->cancelled);
    }

    public function test_a_schedule_that_never_subscribed_makes_no_stripe_call(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->stripeSubscriptions->failWith = new \RuntimeException('Stripe must not be called');

        $this->actingAs($owner)->delete(route('role.delete', ['subdomain' => $role->subdomain]))
            ->assertSessionMissing('error');

        $this->assertNull(Role::find($role->id));
    }

    /**
     * Refused, not cancelled on the owner's behalf: a merge soft-deletes the source, and ending a
     * paid plan should not be a side effect of tidying up duplicates.
     */
    public function test_merging_away_a_schedule_that_is_still_billing_is_refused(): void
    {
        $owner = $this->createOwner();
        $source = $this->createRole($owner);
        $target = $this->createRole($owner);
        Role::where('id', $source->id)->update(['email_verified_at' => null]);
        $this->subscribe($source->fresh());

        $this->actingAs($owner)->post(route('role.merge', ['subdomain' => $source->subdomain]), [
            'target_subdomain' => $target->subdomain,
        ])->assertSessionHas('error', __('messages.merge_source_has_subscription'));

        $this->assertFalse((bool) $source->fresh()->is_deleted);
        $this->assertSame([], $this->stripeSubscriptions->cancelled);
    }

    public function test_the_merge_preview_refuses_a_source_that_is_still_billing(): void
    {
        $owner = $this->createOwner();
        $source = $this->createRole($owner);
        $target = $this->createRole($owner);
        Role::where('id', $source->id)->update(['email_verified_at' => null]);
        $this->subscribe($source->fresh());

        $this->actingAs($owner)
            ->getJson(route('role.merge_preview', ['subdomain' => $source->subdomain, 'target_subdomain' => $target->subdomain]))
            ->assertStatus(422)
            ->assertJson(['error' => __('messages.merge_source_has_subscription')]);
    }

    /** The curator's bulk merge skips a billing source like it skips a claimed one. */
    public function test_the_curator_bulk_merge_skips_a_source_that_is_still_billing(): void
    {
        $owner = $this->createOwner();
        $curator = $this->createRole($owner, 'curator');
        // Same name and empty city/country, so venueDuplicateGroups() pairs them.
        $target = $this->createRole($owner, 'venue', ['name' => 'Dup Venue']);
        $source = $this->createRole($owner, 'venue', ['name' => 'Dup Venue', 'email_verified_at' => null]);
        $this->createEvent($curator)->roles()->attach($source->id, ['is_accepted' => false]);
        $this->createEvent($curator)->roles()->attach($target->id, ['is_accepted' => false]);
        $this->subscribe($source);

        $this->actingAs($owner)->post(route('role.merge_venues_group', ['subdomain' => $curator->subdomain]), [
            'target_id' => \App\Utils\UrlUtils::encodeId($target->id),
            'source_ids' => [\App\Utils\UrlUtils::encodeId($source->id)],
        ]);

        $this->assertFalse((bool) $source->fresh()->is_deleted, 'a schedule still paying for a plan must not be merged away');
        $this->assertSame([], $this->stripeSubscriptions->cancelled);
    }

    /** The same guard on the account-level "merge my venues" page. */
    public function test_the_account_bulk_merge_skips_a_source_that_is_still_billing(): void
    {
        $owner = $this->createOwner();
        $real = $this->createRole($owner, 'venue', ['name' => 'Ozen Bar', 'city' => 'Tel Aviv', 'country_code' => 'il']);

        $orphan = new Role;
        $orphan->subdomain = 'stub'.strtolower(Str::random(10));
        $orphan->type = 'venue';
        $orphan->name = 'Ozen Bar';
        $orphan->address1 = 'Ozen Bar';
        $orphan->city = 'Tel Aviv';
        $orphan->country_code = 'il';
        $orphan->save();
        $this->followRole($owner, $orphan);
        $this->subscribe($orphan);

        $this->actingAs($owner)->post(route('following.merge_venues_group'), [
            'target_id' => \App\Utils\UrlUtils::encodeId($real->id),
            'source_ids' => [\App\Utils\UrlUtils::encodeId($orphan->id)],
        ]);

        $this->assertFalse((bool) $orphan->fresh()->is_deleted);
    }

    /**
     * Cashier finds the local row through the schedule, by CUSTOMER id, so for a hard-deleted
     * schedule its handler finds nothing - and the orphan alert would stay red after the
     * operator cancelled the subscription in Stripe.
     */
    public function test_the_subscription_deleted_webhook_closes_a_row_whose_schedule_is_gone(): void
    {
        config(['cashier.webhook.secret' => null]);

        $role = $this->createRole($this->createOwner());
        $stripeId = $this->subscribe($role);
        $customer = Role::whereKey($role->id)->value('stripe_id');
        DB::table('roles')->where('id', $role->id)->delete();

        $this->postJson(route('stripe.subscription_webhook'), [
            'type' => 'customer.subscription.deleted',
            'data' => ['object' => ['id' => $stripeId, 'customer' => $customer, 'status' => 'canceled']],
        ])->assertOk();

        $this->assertSame('canceled', $this->statusOf($stripeId));
        AdminAlertService::flush();
        $this->assertNull(AdminAlertService::items()->firstWhere('type', 'subscriptions_orphaned'));
    }

    private function adminActing()
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($this->createOwner(true));
    }

    private function orphanRowId(string $stripeId): string
    {
        return \App\Utils\UrlUtils::encodeId(DB::table('subscriptions')->where('stripe_id', $stripeId)->value('id'));
    }

    public function test_the_admin_can_cancel_an_orphaned_subscription_in_stripe(): void
    {
        $role = $this->createRole($this->createOwner());
        $stripeId = $this->subscribe($role);
        DB::table('roles')->where('id', $role->id)->delete();

        $this->adminActing()
            ->post(route('admin.subscriptions.cancel_orphaned', ['subscription' => $this->orphanRowId($stripeId)]))
            ->assertRedirect()
            ->assertSessionHas('message');

        $this->assertSame([$stripeId], $this->stripeSubscriptions->cancelled);
        $this->assertSame('canceled', $this->statusOf($stripeId));

        // Counted as churn, against the schedule that is already gone.
        $row = \App\Models\SubscriptionCancellation::sole();
        $this->assertSame('admin', $row->source);
        $this->assertSame($role->id, $row->role_id);
    }

    /** The button must never end the plan of a schedule that still exists. */
    public function test_the_admin_cancel_refuses_a_subscription_that_is_not_orphaned(): void
    {
        $role = $this->createRole($this->createOwner());
        $stripeId = $this->subscribe($role);

        $this->adminActing()
            ->post(route('admin.subscriptions.cancel_orphaned', ['subscription' => $this->orphanRowId($stripeId)]))
            ->assertSessionHas('error', __('messages.orphaned_subscription_not_found'));

        $this->assertSame([], $this->stripeSubscriptions->cancelled);
        $this->assertSame('active', $this->statusOf($stripeId));
    }

    public function test_a_stripe_failure_on_the_admin_cancel_leaves_the_row_alone(): void
    {
        $role = $this->createRole($this->createOwner());
        $stripeId = $this->subscribe($role);
        DB::table('roles')->where('id', $role->id)->delete();
        $this->stripeSubscriptions->failWith = new \Stripe\Exception\ApiConnectionException('Stripe is down');

        $this->adminActing()
            ->post(route('admin.subscriptions.cancel_orphaned', ['subscription' => $this->orphanRowId($stripeId)]))
            ->assertSessionHas('error', __('messages.orphaned_subscription_cancel_failed'));

        $this->assertSame('active', $this->statusOf($stripeId), 'still billing, so it must stay on the list');
    }

    public function test_non_admins_cannot_cancel_an_orphaned_subscription(): void
    {
        $role = $this->createRole($this->createOwner());
        $stripeId = $this->subscribe($role);
        DB::table('roles')->where('id', $role->id)->delete();

        $this->actingAs($this->createOwner())
            ->post(route('admin.subscriptions.cancel_orphaned', ['subscription' => $this->orphanRowId($stripeId)]));

        $this->assertSame([], $this->stripeSubscriptions->cancelled);
        $this->assertSame('active', $this->statusOf($stripeId));
    }

    public function test_the_delete_confirmations_warn_about_the_paid_plan(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->subscribe($role);

        $this->actingAs($owner)->get(route('profile.edit'))
            ->assertOk()
            ->assertSee(__('messages.delete_account_subscription_warning'));

        $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->assertOk()
            ->assertSee(__('messages.delete_schedule_subscription_warning'));
    }

    public function test_no_warning_without_a_paid_plan(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);

        $this->actingAs($owner)->get(route('profile.edit'))
            ->assertOk()
            ->assertDontSee(__('messages.delete_account_subscription_warning'));

        $this->actingAs($owner)->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule']))
            ->assertOk()
            ->assertDontSee(__('messages.delete_schedule_subscription_warning'));
    }

    /**
     * The backlog. Everyone deleted before this fix is still being charged, and the operator
     * cannot query production - so the admin panel has to list them.
     */
    public function test_the_admin_alert_lists_subscriptions_still_billing_for_deleted_schedules(): void
    {
        $admin = $this->createOwner(true);

        $hardDeleted = $this->createRole($this->createOwner());
        $hardSub = $this->subscribe($hardDeleted);
        DB::table('roles')->where('id', $hardDeleted->id)->delete();

        $softDeleted = $this->createRole($this->createOwner());
        $softSub = $this->subscribe($softDeleted);
        DB::table('roles')->where('id', $softDeleted->id)->update(['is_deleted' => true]);

        // Not orphaned: a live schedule, an ended subscription, one ending at period end.
        $this->subscribe($this->createRole($this->createOwner()));
        $ended = $this->createRole($this->createOwner());
        $this->subscribe($ended, ['stripe_status' => 'canceled', 'ends_at' => now()->subDay()]);
        DB::table('roles')->where('id', $ended->id)->delete();
        $ending = $this->createRole($this->createOwner());
        $this->subscribe($ending, ['ends_at' => now()->addDays(5)]);
        DB::table('roles')->where('id', $ending->id)->delete();

        AdminAlertService::flush();
        $row = AdminAlertService::items()->firstWhere('type', 'subscriptions_orphaned');

        $this->assertNotNull($row);
        $this->assertSame(2, $row['count']);
        $this->assertStringContainsString('#orphaned-subscriptions', $row['url']);

        $html = $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($admin)
            ->get(route('admin.revenue'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="orphaned-subscriptions"', $html);
        $this->assertStringContainsString($hardSub, $html, 'the subscription ID is the only handle left to cancel it by');
        $this->assertStringContainsString($softSub, $html);
    }
}
