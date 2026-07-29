<?php

namespace Tests\Feature;

use App\Models\AnalyticsPromotionsDaily;
use App\Models\BoostCampaign;
use App\Models\PromotionLocationsDaily;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\PromotionAnalyticsService;
use App\Services\PromotionBillingService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Advertiser reporting and the promo:sync housekeeping command.
 */
class PromotionAnalyticsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $owner;

    private Role $advertiser;

    private BoostCampaign $campaign;

    private Role $host;

    protected function setUp(): void
    {
        parent::setUp();

        // promo:sync and the network itself are hosted-only (PromotionService::isEnabled).
        config(['app.hosted' => true, 'app.is_testing' => true, 'ads.enabled' => true, 'app.is_nexus' => false]);
        Setting::set('ads_native_enabled', '1');
        Cache::flush();

        $this->owner = $this->createOwner();
        $this->advertiser = $this->createRole($this->owner, 'talent');
        $event = $this->createEvent($this->advertiser, ['name' => 'My Show', 'starts_at' => now()->addDays(14)]);
        $this->advertiser->events()->updateExistingPivot($event->id, ['is_accepted' => true]);

        $this->host = $this->createRole($this->createOwner(), 'venue');

        $this->campaign = BoostCampaign::create([
            'event_id' => $event->id,
            'role_id' => $this->advertiser->id,
            'user_id' => $this->owner->id,
            'channel' => 'network',
            'name' => 'Promo',
            'status' => 'active',
            'moderation_status' => 'approved',
            'billing_status' => 'charged',
            'user_budget' => 10,
            'total_charged' => 10,
            'pricing_model' => 'cpm',
            'unit_rate_micros' => PromotionBillingService::toMicros(2.00),
            'budget_micros' => PromotionBillingService::toMicros(10),
        ]);
    }

    private function seedDelivery(int $impressions, int $clicks): void
    {
        AnalyticsPromotionsDaily::create([
            'boost_campaign_id' => $this->campaign->id,
            'host_role_id' => $this->host->id,
            'date' => now()->toDateString(),
            'impressions' => $impressions,
            'unique_visitors' => (int) ($impressions / 2),
            'clicks' => $clicks,
        ]);

        $this->campaign->forceFill([
            'spent_micros' => $impressions * $this->campaign->impressionCostMicros(),
        ])->save();
    }

    public function test_summary_reports_delivery_and_effective_rates(): void
    {
        $this->seedDelivery(1000, 20);

        $summary = app(PromotionAnalyticsService::class)->summary($this->campaign->fresh());

        $this->assertSame(1000, $summary['impressions']);
        $this->assertSame(20, $summary['clicks']);
        $this->assertSame(2.0, $summary['ctr']);
        // 1000 impressions at a $2 CPM is $2.00.
        $this->assertSame(2.0, $summary['spend']);
        $this->assertSame(0.1, $summary['effective_cpc']);
        $this->assertSame(2.0, $summary['effective_cpm']);
        $this->assertSame(8.0, $summary['remaining']);
    }

    public function test_placement_reporting_never_names_the_host_schedules(): void
    {
        // A free schedule did not agree to have its traffic volume disclosed to the
        // advertiser paying to appear on it.
        $this->host->update(['name' => 'Kellys Bar']);
        $this->seedDelivery(500, 5);

        $placements = app(PromotionAnalyticsService::class)->placementSummary($this->campaign->fresh());

        $this->assertSame(1, $placements['schedule_count']);
        $this->assertSame('venue', $placements['by_type'][0]['type']);

        $encoded = json_encode($placements);
        $this->assertStringNotContainsString('Kellys Bar', $encoded);
        $this->assertStringNotContainsString($this->host->subdomain, $encoded);
    }

    public function test_the_dashboard_renders_for_the_owner(): void
    {
        $this->seedDelivery(1200, 30);
        PromotionLocationsDaily::record($this->campaign->id, 'ZA', 'impression');

        $this->actingAs($this->owner)
            ->get(route('boost.show', ['hash' => UrlUtils::encodeId($this->campaign->id)]))
            ->assertOk()
            ->assertSee(__('messages.promotion_delivery'))
            ->assertSee('1,200')
            ->assertSee('South Africa');
    }

    public function test_the_dashboard_shows_the_review_state_to_a_waiting_advertiser(): void
    {
        $this->campaign->update(['status' => 'pending_review', 'moderation_status' => 'pending']);

        $this->actingAs($this->owner)
            ->get(route('boost.show', ['hash' => UrlUtils::encodeId($this->campaign->id)]))
            ->assertOk()
            ->assertSee(__('messages.promotion_awaiting_review_help'));
    }

    public function test_conversions_come_from_the_existing_sale_attribution(): void
    {
        // No new plumbing: the click URL carries utm_source=boost, which TicketController
        // already maps onto sales.boost_campaign_id.
        $sale = $this->createSale($this->campaign->event, $this->advertiser, ['status' => 'paid', 'payment_amount' => 42.50]);
        $sale->update(['boost_campaign_id' => $this->campaign->id]);

        $conversions = app(PromotionAnalyticsService::class)->conversions($this->campaign);

        $this->assertSame(1, $conversions['count']);
        $this->assertSame(42.5, $conversions['revenue']);
    }

    public function test_sync_completes_an_exhausted_campaign_and_mirrors_spend(): void
    {
        $this->seedDelivery(5000, 40);
        $this->campaign->forceFill(['exhausted_at' => now()])->save();

        $this->artisan('promo:sync')->assertSuccessful();

        $campaign = $this->campaign->fresh();
        $this->assertSame('completed', $campaign->status);
        $this->assertSame(5000, (int) $campaign->impressions);
        $this->assertSame(40, (int) $campaign->clicks);
        // actual_spend is what the shared refund and admin revenue paths read.
        $this->assertSame(10.0, (float) $campaign->actual_spend);
    }

    /**
     * The unspent-budget refund has to actually become reachable.
     *
     * reconcileSpend() wrote 'analytics_synced_at' => now() on every fifteen-minute run, which
     * bumped updated_at - and completeFinishedCampaigns() selects campaigns to reconcile with
     * `updated_at <= now()->subDay()`. That could never be true, so no network campaign ever had
     * its unspent budget returned.
     *
     * This MUST run at the scheduler's real fifteen-minute cadence across the whole 24-hour
     * window. An earlier version of this test ran two syncs an hour apart and then jumped 25
     * hours, which left 25 hours of slack after the last write - so it stayed green even with
     * the guard removed entirely, and it did not notice when a decimal-cast comparison made the
     * guard fire on every run. The bug only shows when a sync lands INSIDE the final 24 hours.
     */
    public function test_repeated_syncs_do_not_starve_the_unspent_budget_refund(): void
    {
        \Illuminate\Support\Facades\Queue::fake();

        $this->seedDelivery(1000, 10);
        $this->campaign->update(['status' => 'completed']);

        // A full day of scheduler runs, exactly as cron would issue them. Delivery is static, so
        // a correct guard writes nothing after the first run and updated_at stops moving.
        for ($i = 0; $i < 96; $i++) {
            $this->artisan('promo:sync')->assertSuccessful();
            $this->travel(15)->minutes();
        }

        $this->artisan('promo:sync')->assertSuccessful();

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\ReconcileBoostCampaign::class);

        $this->travelBack();
    }

    public function test_a_sync_that_changes_nothing_leaves_updated_at_alone(): void
    {
        // The mechanism behind the test above, asserted directly so a regression names itself.
        // Reading $campaign->ctr returns a decimal-cast STRING ("1.0000") while the freshly
        // computed value is a float (1), so a string comparison marks every campaign dirty on
        // every run.
        $this->seedDelivery(1000, 10);
        $this->campaign->update(['status' => 'completed']);

        $this->artisan('promo:sync')->assertSuccessful();

        $after = $this->campaign->fresh()->updated_at;

        $this->travel(15)->minutes();
        $this->artisan('promo:sync')->assertSuccessful();

        $this->assertEquals($after, $this->campaign->fresh()->updated_at,
            'A run with no counter movement must not touch updated_at.');

        $this->travelBack();
    }

    public function test_sync_pauses_a_campaign_whose_event_stopped_being_public(): void
    {
        $this->campaign->event->update(['is_draft' => true]);

        $this->artisan('promo:sync')->assertSuccessful();

        $this->assertSame('paused', $this->campaign->fresh()->status);
    }

    public function test_disabling_the_network_still_settles_campaigns_that_were_paid_for(): void
    {
        // Campaigns are prepaid. If switching the network off stopped settlement, an operator
        // would silently strand every advertiser's money: never completed, never refunded.
        \Illuminate\Support\Facades\Queue::fake();

        $this->seedDelivery(1000, 10);
        $this->campaign->update(['status' => 'completed']);
        $this->travel(25)->hours();

        Setting::set('ads_native_enabled', '0');
        config(['ads.native_enabled' => false]);
        Cache::flush();

        $this->artisan('promo:sync')->assertSuccessful();

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\ReconcileBoostCampaign::class);

        $this->travelBack();
    }

    public function test_the_scheduler_still_invokes_promo_sync_when_the_network_is_switched_off(): void
    {
        // The test above proves handle() settles when disabled - but that was dead code for a
        // while, because BOTH scheduler registrations gated on PromotionService::isEnabled(),
        // the exact predicate handle() was ungated from. So the command was never invoked and
        // campaigns stayed stranded. This drives the registered schedule entry itself.
        \Illuminate\Support\Facades\Queue::fake();

        $this->seedDelivery(1000, 10);
        $this->campaign->update(['status' => 'completed']);
        $this->travel(25)->hours();

        // Network off at the admin switch, master switch still on.
        Setting::set('ads_native_enabled', '0');
        config(['ads.native_enabled' => false, 'ads.enabled' => true]);
        Cache::flush();

        $this->assertFalse(\App\Services\PromotionService::isEnabled(), 'Precondition: the network must be off.');

        $event = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->first(fn ($e) => $e->description === 'promo-sync');

        $this->assertNotNull($event, 'promo-sync must be registered with the scheduler.');

        $event->run(app());

        \Illuminate\Support\Facades\Queue::assertPushed(\App\Jobs\ReconcileBoostCampaign::class);

        $this->travelBack();
    }

    public function test_a_campaign_paused_by_housekeeping_is_still_settled(): void
    {
        // pauseUnservableCampaigns() and pauseUnderperformingCpc() both write 'paused', while
        // completeFinishedCampaigns() used to select only 'active' - so the command's own
        // housekeeping moved campaigns into a state it would never settle, stranding the budget.
        $this->campaign->update(['status' => 'paused', 'scheduled_end' => now()->subDay()]);

        $this->artisan('promo:sync')->assertSuccessful();

        $this->assertSame('completed', $this->campaign->fresh()->status);
    }

    public function test_a_deliberately_paused_campaign_inside_its_window_is_left_alone(): void
    {
        // The counterpart: including 'paused' must not retire a campaign the advertiser paused
        // themselves and still intends to resume. The end conditions are what decide.
        $this->campaign->update(['status' => 'paused', 'scheduled_end' => now()->addWeek()]);

        $this->artisan('promo:sync')->assertSuccessful();

        $this->assertSame('paused', $this->campaign->fresh()->status);
    }

    public function test_a_campaign_paused_on_a_recurring_event_cannot_be_stranded_forever(): void
    {
        // The shape no end condition could reach: paused, recurring event (so never "over"), no
        // scheduled_end, and no exhausted_at because it stopped serving before it could exhaust.
        // pauseUnservableCampaigns() produces exactly this when a recurring event goes draft.
        $this->campaign->update(['status' => 'paused', 'scheduled_end' => null]);
        $this->campaign->event->forceFill([
            'days_of_week' => '0100000',
            'starts_at' => now()->subDays(3),
            'is_draft' => true,
        ])->save();

        // Still inside the grace period: the advertiser keeps their budget.
        $this->artisan('promo:sync')->assertSuccessful();
        $this->assertSame('paused', $this->campaign->fresh()->status);

        $this->travel(31)->days();
        $this->artisan('promo:sync')->assertSuccessful();

        $this->assertSame('completed', $this->campaign->fresh()->status,
            'A campaign paused indefinitely must eventually settle so the budget can be returned.');

        $this->travelBack();
    }

    public function test_an_open_ended_campaign_completes_once_its_event_is_over(): void
    {
        // scheduled_end is optional. Without an event-based end condition such a campaign never
        // completes: candidates() stops serving it the moment the event passes, so it can never
        // exhaust its budget, so the unspent remainder is never refunded.
        $this->campaign->update(['scheduled_end' => null]);
        $this->campaign->event->update(['starts_at' => now()->subDays(3), 'duration' => 2]);

        $this->artisan('promo:sync')->assertSuccessful();

        $this->assertSame('completed', $this->campaign->fresh()->status);
    }

    public function test_a_recurring_events_campaign_is_not_completed_for_being_in_the_past(): void
    {
        // A recurring event has no single date and is never "over" - completing its promotion
        // would refund a campaign that is still perfectly servable.
        $this->campaign->update(['scheduled_end' => null]);
        // days_of_week is not in Event::$fillable, so update() would silently drop it.
        $this->campaign->event->forceFill([
            'starts_at' => now()->subDays(3),
            'duration' => 2,
            'days_of_week' => '0100000',
        ])->save();

        $this->artisan('promo:sync')->assertSuccessful();

        $this->assertSame('active', $this->campaign->fresh()->status);
    }

    public function test_sync_pauses_cpc_creative_nobody_clicks(): void
    {
        config(['ads.native_ctr_floor_min_impressions' => 100, 'ads.native_ctr_floor' => 0.01]);

        $this->campaign->update(['pricing_model' => 'cpc', 'unit_rate_micros' => PromotionBillingService::toMicros(0.25)]);
        $this->seedDelivery(500, 0);

        $this->artisan('promo:sync')->assertSuccessful();

        // Otherwise weak CPC creative consumes host inventory indefinitely for free.
        $this->assertSame('paused', $this->campaign->fresh()->status);
    }

    public function test_sync_prunes_stats_past_the_retention_window(): void
    {
        config(['ads.stats_retention_days' => 30]);

        AnalyticsPromotionsDaily::create([
            'boost_campaign_id' => $this->campaign->id,
            'host_role_id' => $this->host->id,
            'date' => now()->subDays(400)->toDateString(),
            'impressions' => 5,
        ]);

        $this->artisan('promo:sync')->assertSuccessful();

        $this->assertSame(0, AnalyticsPromotionsDaily::whereDate('date', now()->subDays(400)->toDateString())->count());
    }

    public function test_disabling_the_network_stops_delivery_optimisation_but_not_housekeeping(): void
    {
        // Was previously "sync does nothing when the network is disabled". That behaviour
        // stranded prepaid campaigns, so settlement and housekeeping now always run; only the
        // CTR-based pausing, which needs live delivery to mean anything, is skipped.
        Setting::set('ads_native_enabled', '0');
        config(['ads.native_enabled' => false]);
        Cache::flush();

        config(['ads.native_ctr_floor_min_impressions' => 100, 'ads.native_ctr_floor' => 0.01]);
        $this->campaign->update(['pricing_model' => 'cpc', 'unit_rate_micros' => PromotionBillingService::toMicros(0.25)]);
        $this->seedDelivery(500, 0);

        $this->artisan('promo:sync')->assertSuccessful();

        $this->assertSame('active', $this->campaign->fresh()->status, 'CTR pausing must not run with the network off.');

        // Housekeeping still runs.
        $this->campaign->event->update(['is_draft' => true]);
        $this->artisan('promo:sync')->assertSuccessful();

        $this->assertSame('paused', $this->campaign->fresh()->status);
    }
}
