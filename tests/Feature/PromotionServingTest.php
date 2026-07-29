<?php

namespace Tests\Feature;

use App\Models\AnalyticsPromotionsDaily;
use App\Models\BoostCampaign;
use App\Models\Event;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\PromotionBillingService;
use App\Services\PromotionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Which promotion serves where, and - the part that costs real money if it is wrong - which
 * ones stop serving.
 */
class PromotionServingTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $host;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.hosted' => true, 'app.is_nexus' => false, 'ads.enabled' => true]);
        Setting::set('ads_native_enabled', '1');
        Setting::set('ads_native_priority', '1');
        Cache::flush();

        // A free-tier schedule is the inventory.
        $this->host = $this->createRole($this->createOwner(), 'venue', [
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
        ]);
    }

    private function advertiserCampaign(array $campaignAttrs = [], array $eventAttrs = [], ?User $owner = null): BoostCampaign
    {
        $owner ??= $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, array_merge([
            'name' => 'Advertised Show',
            'starts_at' => now()->addDays(10),
        ], $eventAttrs));

        // createEvent attaches the pivot; is_accepted is the universal visibility gate.
        $role->events()->updateExistingPivot($event->id, ['is_accepted' => true]);

        // spent_micros is intentionally NOT fillable - only the atomic debit may write it -
        // so a test that wants a partly-spent campaign has to force it.
        $spent = $campaignAttrs['spent_micros'] ?? null;
        unset($campaignAttrs['spent_micros']);

        $campaign = BoostCampaign::create(array_merge([
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'channel' => 'network',
            'name' => 'Promo',
            'status' => 'active',
            'moderation_status' => 'approved',
            'billing_status' => 'charged',
            'user_budget' => 10,
            'pricing_model' => 'cpm',
            'unit_rate_micros' => PromotionBillingService::toMicros(2.00),
            'budget_micros' => PromotionBillingService::toMicros(10),
        ], $campaignAttrs));

        if ($spent !== null) {
            $campaign->forceFill(['spent_micros' => $spent])->save();
        }

        return $campaign;
    }

    private function visitorRequest(): Request
    {
        $request = Request::create('https://host.eventschedule.test/', 'GET');
        $request->headers->set('Accept-Language', 'en-US,en;q=0.9');
        $request->headers->set('Accept', 'text/html');
        $request->headers->set('User-Agent', 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/120 Safari/537.36');
        $request->server->set('REMOTE_ADDR', '203.0.113.7');

        return $request;
    }

    private function pick(): ?array
    {
        app(PromotionService::class)->forgetCandidates();

        return app(PromotionService::class)->pick($this->host, null, $this->visitorRequest());
    }

    public function test_an_approved_campaign_serves_and_records_an_impression(): void
    {
        $campaign = $this->advertiserCampaign();

        $promo = $this->pick();

        $this->assertNotNull($promo);
        $this->assertSame($campaign->id, $promo['campaign_id']);
        $this->assertSame('Advertised Show', $promo['headline']);

        $this->assertDatabaseHas('analytics_promotions_daily', [
            'boost_campaign_id' => $campaign->id,
            'host_role_id' => $this->host->id,
            'impressions' => 1,
        ]);

        // ...and it was billed.
        $this->assertGreaterThan(0, (int) $campaign->fresh()->spent_micros);
    }

    public function test_the_event_flyer_fallback_is_a_loadable_url_not_a_bare_filename(): void
    {
        // events.flyer_image_url holds a bare filename; the model accessor is what turns it
        // into a URL. candidates() reads it out of a raw query, so without the conversion the
        // fallback creative renders <img src="something.jpg"> and every promoted card that
        // relies on the event's own flyer shows a broken image.
        $campaign = $this->advertiserCampaign();
        $campaign->event->forceFill(['flyer_image_url' => 'demo_flyer_special.jpg'])->save();

        $promo = $this->pick();

        $this->assertNotNull($promo);
        $this->assertStringStartsWith('http', $promo['image_url']);
        $this->assertStringEndsWith('demo_flyer_special.jpg', $promo['image_url']);
    }

    public function test_rotating_the_user_agent_does_not_defeat_the_impression_spend_cap(): void
    {
        // The frequency cap is keyed on visitorHash, which mixes in the client-chosen
        // User-Agent, and guest pages carry no route throttle - so before the IP cap existed,
        // rotating the UA billed the advertiser without limit.
        config(['ads.native_ip_impression_cap' => 5]);

        $campaign = $this->advertiserCampaign();

        for ($i = 0; $i < 20; $i++) {
            $request = $this->visitorRequest();
            $request->headers->set('User-Agent', "Mozilla/5.0 (Macintosh) Chrome/{$i}.0.{$i} Safari/537.36");

            app(PromotionService::class)->forgetCandidates();
            app(PromotionService::class)->pick($this->host, null, $request);
        }

        $this->assertSame(
            $campaign->impressionCostMicros() * 5,
            (int) $campaign->fresh()->spent_micros,
            'Spend must be bounded by the IP cap, not by the number of distinct User-Agents.'
        );
    }

    public function test_an_unresolvable_ip_is_never_billed(): void
    {
        // With no address there is no key to cap spend on. This used to fall through with an
        // empty $seen array, which meant no cap at all.
        $campaign = $this->advertiserCampaign();

        $request = $this->visitorRequest();
        $request->server->remove('REMOTE_ADDR');

        $this->assertNull(app(PromotionService::class)->pick($this->host, null, $request));
        $this->assertSame(0, (int) $campaign->fresh()->spent_micros);
    }

    public function test_a_schedule_never_advertises_to_itself(): void
    {
        // Campaign owned by the host schedule itself.
        $event = $this->createEvent($this->host, ['starts_at' => now()->addDays(5)]);
        $this->host->events()->updateExistingPivot($event->id, ['is_accepted' => true]);

        BoostCampaign::create([
            'event_id' => $event->id,
            'role_id' => $this->host->id,
            'user_id' => $this->host->user_id,
            'channel' => 'network',
            'name' => 'Self promo',
            'status' => 'active',
            'moderation_status' => 'approved',
            'billing_status' => 'charged',
            'user_budget' => 10,
            'pricing_model' => 'cpm',
            'unit_rate_micros' => PromotionBillingService::toMicros(2.00),
            'budget_micros' => PromotionBillingService::toMicros(10),
        ]);

        $this->assertNull($this->pick());
    }

    public function test_a_pending_campaign_never_serves(): void
    {
        $this->advertiserCampaign(['moderation_status' => 'pending', 'status' => 'pending_review']);

        $this->assertNull($this->pick(), 'Approve-before-serve is the whole point of the review queue.');
    }

    public function test_an_exhausted_campaign_stops_serving(): void
    {
        $this->advertiserCampaign(['spent_micros' => PromotionBillingService::toMicros(10)]);

        $this->assertNull($this->pick());
    }

    public function test_a_host_that_opted_out_carries_no_promotions(): void
    {
        $this->advertiserCampaign();
        $this->host->update(['promotions_opt_out' => true]);

        $this->assertNull($this->pick());
    }

    /**
     * The money-losing case: none of these touch the campaign row, so without the
     * event-visibility predicates the promotion keeps serving - and charging - while
     * pointing at a page the visitor cannot open.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('hiddenEventProvider')]
    public function test_a_promotion_stops_when_its_event_stops_being_visible(array $eventAttrs): void
    {
        $campaign = $this->advertiserCampaign([], $eventAttrs);

        $this->assertNull($this->pick());
        $this->assertSame(0, (int) $campaign->fresh()->spent_micros, 'A hidden event must not burn budget.');
    }

    public static function hiddenEventProvider(): array
    {
        return [
            'draft' => [['is_draft' => true]],
            'unlisted' => [['is_private' => true]],
            'cancelled' => [['is_cancelled' => true]],
            'already over' => [['starts_at' => '2020-01-01 12:00:00']],
        ];
    }

    public function test_a_promotion_stops_when_the_pivot_is_no_longer_accepted(): void
    {
        $campaign = $this->advertiserCampaign();
        $campaign->role->events()->updateExistingPivot($campaign->event_id, ['is_accepted' => false]);

        $this->assertNull($this->pick());
    }

    public function test_the_frequency_cap_limits_repeat_impressions_to_one_visitor(): void
    {
        config(['ads.native_frequency_cap' => 2]);
        $this->advertiserCampaign();

        $service = app(PromotionService::class);
        $request = $this->visitorRequest();

        $this->assertNotNull($service->pick($this->host, null, $request));
        $this->assertNotNull($service->pick($this->host, null, $request));
        // Third view of the same campaign by the same visitor on the same day.
        $this->assertNull($service->pick($this->host, null, $request));
    }

    public function test_country_targeting_excludes_a_visitor_whose_country_is_unknown(): void
    {
        // Selfhost ships no GeoIP database, so lookups return null there. Unknown must mean
        // EXCLUDED - the opposite default would deliver worldwide impressions to an
        // advertiser who paid for one country.
        $this->advertiserCampaign(['network_targeting' => ['visitor_countries' => ['ZA']]]);

        $this->assertNull($this->pick());
    }

    public function test_untargeted_campaigns_serve_regardless_of_country(): void
    {
        $this->advertiserCampaign(['network_targeting' => []]);

        $this->assertNotNull($this->pick());
    }

    public function test_host_schedule_type_targeting_is_respected(): void
    {
        $this->advertiserCampaign(['network_targeting' => ['schedule_types' => ['curator']]]);
        $this->assertNull($this->pick(), 'The host is a venue, not a curator.');

        Cache::flush();
        BoostCampaign::query()->update(['network_targeting' => json_encode(['schedule_types' => ['venue']])]);
        $this->assertNotNull($this->pick());
    }

    public function test_the_promotion_is_not_shown_on_the_event_it_advertises(): void
    {
        $campaign = $this->advertiserCampaign();
        $event = Event::find($campaign->event_id);

        app(PromotionService::class)->forgetCandidates();

        $this->assertNull(app(PromotionService::class)->pick($this->host, $event, $this->visitorRequest()));
    }

    public function test_a_spoofed_google_ads_crawler_is_never_billed_a_native_impression(): void
    {
        // The crawler exemption exists so AdSense can read the page for contextual targeting.
        // It must not extend to the native branch, which bills a real advertiser - the
        // User-Agent is chosen by the client, so that would be a free budget drain.
        $campaign = $this->advertiserCampaign();

        $request = $this->visitorRequest();
        $request->headers->set('User-Agent', 'Mozilla/5.0 (compatible; Mediapartners-Google/2.1; +http://www.google.com/bot.html)');

        app(PromotionService::class)->forgetCandidates();
        $slot = app(\App\Services\AdsService::class)->resolveSlot($this->host, null, $request);

        $this->assertNotSame('native', $slot['type'] ?? null, 'A crawler must not take the billed native branch.');
        $this->assertSame(0, (int) $campaign->fresh()->spent_micros);
        $this->assertDatabaseMissing('analytics_promotions_daily', ['boost_campaign_id' => $campaign->id]);
    }

    public function test_nothing_serves_when_the_operator_has_not_enabled_the_network(): void
    {
        Setting::set('ads_native_enabled', '0');
        $this->advertiserCampaign();

        $this->assertNull($this->pick());
    }

    public function test_impressions_accumulate_into_one_row_per_campaign_host_and_day(): void
    {
        $campaign = $this->advertiserCampaign();
        config(['ads.native_frequency_cap' => 10]);

        $service = app(PromotionService::class);
        for ($i = 0; $i < 3; $i++) {
            $service->pick($this->host, null, $this->visitorRequest());
        }

        $rows = AnalyticsPromotionsDaily::forCampaign($campaign->id)->get();

        $this->assertCount(1, $rows, 'The upsert must accumulate, not insert a row per impression.');
        $this->assertSame(3, (int) $rows->first()->impressions);
        // Same visitor all three times.
        $this->assertSame(1, (int) $rows->first()->unique_visitors);
    }
}
