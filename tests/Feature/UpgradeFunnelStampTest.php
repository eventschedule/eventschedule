<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * users.subscribe_form_viewed_at - the "reached checkout" milestone.
 *
 * Only completed subscriptions were ever recorded, so the plan funnel had no denominator and
 * "reached checkout and did not buy" was unanswerable. At roughly one conversion a month the
 * bottom of that funnel can never move detectably, which is exactly why the stage above it has
 * to exist.
 *
 * The stamp sits AFTER every redirect in SubscriptionController::show(), so it means "saw the
 * checkout form", not "hit the URL". These tests pin the negative half of that, which is the
 * part a refactor would silently break: rendering the page needs a live Stripe SetupIntent, so
 * the positive path is not reachable from the suite. The column -> funnel half is covered by
 * GrowthExportTest::test_the_funnel_carries_the_money_stages.
 */
class UpgradeFunnelStampTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    /**
     * An already-subscribed owner is bounced back to the plan tab before the form renders.
     * Stamping them would inflate the stage with people who never saw a checkout.
     */
    public function test_a_bounced_owner_is_not_counted_as_having_reached_checkout(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['plan_type' => 'pro']);

        $role->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_'.Str::random(14),
            'stripe_status' => 'active',
            'stripe_price' => config('services.stripe_platform.price_monthly') ?: 'price_test_monthly',
            'quantity' => 1,
        ]);

        $this->actingAs($owner)
            ->get(route('role.subscribe', ['subdomain' => $role->subdomain], false))
            ->assertRedirect();

        $this->assertNull(
            $owner->fresh()->subscribe_form_viewed_at,
            'redirected away without seeing the form, so it must not count as reaching checkout'
        );
    }

    /**
     * A non-owner is refused outright. Same reasoning, different guard - and this one runs
     * before the role is even theirs to subscribe.
     */
    public function test_a_non_owner_is_not_counted(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['plan_type' => 'free']);
        $stranger = $this->createOwner();

        $this->actingAs($stranger)
            ->get(route('role.subscribe', ['subdomain' => $role->subdomain], false))
            ->assertRedirect();

        $this->assertNull($stranger->fresh()->subscribe_form_viewed_at);
        $this->assertNull($owner->fresh()->subscribe_form_viewed_at);
    }

    /** The column exists and is nullable, so a fresh account starts outside the stage. */
    public function test_a_fresh_account_has_not_reached_checkout(): void
    {
        $user = User::factory()->create();

        $this->assertNull($user->subscribe_form_viewed_at);
    }
}
