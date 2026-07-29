<?php

namespace Tests\Feature\Characterization;

use App\Models\BoostBillingRecord;
use App\Models\BoostCampaign;
use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Characterization of the EXISTING Meta boost purchase path.
 *
 * This pins behaviour rather than asserting it is desirable. `BoostController::store()` is a
 * 971-line controller's money path that shipped without tests, and the on-network promotions
 * work now shares `BoostBillingService` with it. These tests exist so that the next change to
 * the shared billing code cannot silently alter what the Meta channel does.
 *
 * Anything surprising below is documented as-is. If a test here starts failing, the question is
 * "did we mean to change Meta's behaviour?" - not "is the test wrong?".
 *
 * Stripe is never reached: the selfhost/testing branch settles without it, which is the only
 * part of this path reachable in a test without a mockable client. The card branch is covered
 * indirectly through BoostBillingService.
 */
class BoostStoreCharacterizationTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();   // CreateBoostCampaign would otherwise try to reach Meta.
        config(['app.hosted' => false, 'app.is_testing' => true]);
    }

    private function advertiser(array $roleAttrs = []): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');

        if ($roleAttrs !== []) {
            $role->forceFill($roleAttrs)->save();
            $role = $role->fresh();
        }

        $event = $this->createEvent($role, ['name' => 'Meta Show', 'starts_at' => now()->addDays(20)]);
        $role->events()->updateExistingPivot($event->id, ['is_accepted' => true]);

        return [$owner, $role, $event];
    }

    private function payload(Role $role, $event, array $overrides = []): array
    {
        return array_merge([
            'event_id' => UrlUtils::encodeId($event->id),
            'role_id' => UrlUtils::encodeId($role->id),
            'budget' => 25,
            'budget_type' => 'lifetime',
        ], $overrides);
    }

    public function test_selfhost_settles_without_charging_anything(): void
    {
        [$owner, $role, $event] = $this->advertiser();

        $this->actingAs($owner)->post(route('boost.store'), $this->payload($role, $event));

        $campaign = BoostCampaign::first();

        $this->assertNotNull($campaign);
        $this->assertSame('meta', $campaign->channel, 'The default channel must remain meta.');
        $this->assertSame('charged', $campaign->billing_status);
        // Selfhost records a zero charge rather than the budget.
        $this->assertSame(0.0, (float) $campaign->total_charged);
        $this->assertSame('active', $campaign->status);
    }

    public function test_the_markup_is_zero_on_selfhost_and_twenty_percent_hosted(): void
    {
        [$owner, $role, $event] = $this->advertiser();

        $this->actingAs($owner)->post(route('boost.store'), $this->payload($role, $event));
        $this->assertSame(0.0, (float) BoostCampaign::first()->markup_rate, 'Selfhost passes budget through at cost.');

        BoostCampaign::query()->delete();

        config(['app.hosted' => true, 'app.is_testing' => true]);
        [$owner2, $role2, $event2] = $this->advertiser(['boost_max_budget' => 1000]);
        $owner2->forceFill(['phone_verified_at' => now()])->save();

        $this->actingAs($owner2)->post(route('boost.store'), $this->payload($role2, $event2));

        $campaign = BoostCampaign::first();
        $this->assertSame(0.2, (float) $campaign->markup_rate);
        // The advertiser pays budget + markup; the markup is the platform's revenue.
        $this->assertSame(30.0, (float) $campaign->getTotalCost());
    }

    public function test_boost_credit_pays_and_is_recorded_as_a_billing_record(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => false]);

        [$owner, $role, $event] = $this->advertiser(['boost_max_budget' => 1000, 'boost_credit' => 100]);
        $owner->forceFill(['phone_verified_at' => now()])->save();

        $this->actingAs($owner)->post(
            preg_replace('~^(https?://)~', '$1app.', route('boost.store'), 1),
            $this->payload($role, $event)
        );

        $campaign = BoostCampaign::first();

        $this->assertNotNull($campaign);
        $this->assertSame('charged', $campaign->billing_status);
        $this->assertNull($campaign->stripe_payment_intent_id, 'A credit purchase deliberately clears the intent.');
        // 25 budget + 20% markup = 30 off a 100 balance.
        $this->assertSame(70.0, (float) $role->fresh()->boost_credit);

        $record = BoostBillingRecord::where('boost_campaign_id', $campaign->id)->where('type', 'charge')->first();
        $this->assertNotNull($record);
        $this->assertSame(30.0, (float) $record->amount);
    }

    public function test_a_reused_payment_intent_lands_the_buyer_on_the_existing_campaign(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => true]);

        [$owner, $role, $event] = $this->advertiser(['boost_max_budget' => 1000]);
        $owner->forceFill(['phone_verified_at' => now()])->save();

        $existing = BoostCampaign::create([
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'name' => 'Already bought',
            'status' => 'active',
            'billing_status' => 'charged',
            'user_budget' => 25,
            'stripe_payment_intent_id' => 'pi_reused',
        ]);

        $this->actingAs($owner)
            ->post(route('boost.store'), $this->payload($role, $event, ['payment_intent_id' => 'pi_reused']))
            ->assertRedirect(route('boost.show', ['hash' => $existing->hashedId()]));

        $this->assertSame(1, BoostCampaign::count(), 'A repeated submit must not create a second campaign.');
    }

    public function test_the_budget_ceiling_is_enforced(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => true]);

        // getBoostMaxBudget() defaults to services.meta.boost_default_limit ($10) on hosted.
        [$owner, $role, $event] = $this->advertiser();
        $owner->forceFill(['phone_verified_at' => now()])->save();

        $this->actingAs($owner)->post(route('boost.store'), $this->payload($role, $event, ['budget' => 900]));

        $this->assertSame(0, BoostCampaign::count());
    }

    public function test_the_role_must_belong_to_the_event(): void
    {
        // The guard the network flow originally omitted: role_id is constrained to the roles
        // attached to the event the caller proved they can edit.
        [$owner, , $event] = $this->advertiser();
        $unrelated = $this->createRole($this->createOwner(), 'venue');

        $this->actingAs($owner)
            ->post(route('boost.store'), $this->payload($unrelated, $event))
            ->assertForbidden();

        $this->assertSame(0, BoostCampaign::count());
    }

    public function test_a_draft_event_cannot_be_boosted(): void
    {
        [$owner, $role, $event] = $this->advertiser();
        $event->update(['is_draft' => true]);

        $this->actingAs($owner)
            ->post(route('boost.store'), $this->payload($role, $event))
            ->assertForbidden();
    }

    public function test_the_concurrency_cap_counts_meta_campaigns(): void
    {
        config(['services.meta.max_concurrent_boosts' => 1]);

        [$owner, $role, $event] = $this->advertiser();

        $this->actingAs($owner)->post(route('boost.store'), $this->payload($role, $event));
        $this->actingAs($owner)->post(route('boost.store'), $this->payload($role, $event));

        $this->assertSame(1, BoostCampaign::meta()->count());
    }

    public function test_a_network_campaign_does_not_consume_the_meta_concurrency_slot(): void
    {
        // The two channels have separate caps, so one cannot starve the other.
        config(['services.meta.max_concurrent_boosts' => 1]);

        [$owner, $role, $event] = $this->advertiser();

        BoostCampaign::create([
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'channel' => 'network',
            'name' => 'Network promo',
            'status' => 'active',
            'billing_status' => 'charged',
            'user_budget' => 25,
        ]);

        $this->actingAs($owner)->post(route('boost.store'), $this->payload($role, $event));

        $this->assertSame(1, BoostCampaign::meta()->count(), 'The Meta slot must still be available.');
    }

    public function test_the_spending_limit_cannot_be_written_by_mass_assignment(): void
    {
        // Characterization of a PRE-EXISTING bug, pinned here rather than fixed because
        // fixing it would silently raise live advertisers' spending ceilings.
        //
        // `boost_max_budget` is in Role::$casts but NOT in Role::$fillable, so both of its
        // writers are silently dropped:
        //   - ReconcileBoostCampaign's trust auto-increase (the "complete N campaigns, earn a
        //     higher limit" ladder) never actually raises anything.
        //   - AdminController::boostSetLimit()'s admin action reports success and persists
        //     nothing.
        // Every fixture in this suite therefore has to use forceFill() to set it.
        //
        // This matters for the on-network work: ReconcileBoostCampaign's completed-campaign
        // count is now scoped with ::meta() so that network completions cannot ratchet up a
        // schedule's META spending ceiling. That scoping is currently unobservable precisely
        // because the write below is dead. Whoever adds `boost_max_budget` to $fillable will
        // activate the ladder - keep the ::meta() scope when they do.
        [$owner, $role] = $this->advertiser();

        $role->forceFill(['boost_max_budget' => 10])->save();

        $role->update(['boost_max_budget' => 500]);

        $this->assertSame(10.0, (float) $role->fresh()->boost_max_budget);
    }

    public function test_one_ad_is_created_with_utm_tagged_destination(): void
    {
        [$owner, $role, $event] = $this->advertiser();

        $this->actingAs($owner)->post(route('boost.store'), $this->payload($role, $event));

        $ad = BoostCampaign::first()->ads->first();

        $this->assertNotNull($ad);
        $this->assertSame('A', $ad->variant);
        $this->assertStringContainsString('utm_source=boost', $ad->destination_url);
        // paid_social is what distinguishes a Meta placement from an on-network one.
        $this->assertStringContainsString('utm_medium=paid_social', $ad->destination_url);
    }
}
