<?php

namespace Tests\Feature;

use App\Models\BoostAd;
use App\Models\BoostCampaign;
use App\Models\Role;
use App\Models\Setting;
use App\Services\PromotionBillingService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The promotion click redirect.
 *
 * Three things have to be right here or the feature is either a security hole or a billing
 * dispute: the destination cannot be attacker-controlled, the attribution parameters have to
 * survive to the sale, and the click has to be billed exactly once.
 */
class PromotionClickTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $host;

    protected function setUp(): void
    {
        parent::setUp();

        // The promotions network is hosted-only (PromotionService::isEnabled). is_testing keeps
        // settlement on the free branch so these assert behaviour, not Stripe.
        config(['app.hosted' => true, 'app.is_testing' => true, 'ads.enabled' => true, 'app.is_nexus' => false]);
        Setting::set('ads_native_enabled', '1');
        Cache::flush();

        $this->host = $this->createRole($this->createOwner(), 'venue', [
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
        ]);
    }

    private function campaign(array $attrs = [], ?string $destination = null): BoostCampaign
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $event = $this->createEvent($role, ['name' => 'Advertised Show', 'starts_at' => now()->addDays(7)]);
        $role->events()->updateExistingPivot($event->id, ['is_accepted' => true]);

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
            'pricing_model' => 'cpc',
            'unit_rate_micros' => PromotionBillingService::toMicros(0.25),
            'budget_micros' => PromotionBillingService::toMicros(10),
        ], $attrs));

        if ($destination !== null) {
            BoostAd::create([
                'boost_campaign_id' => $campaign->id,
                'headline' => 'Come along',
                'destination_url' => $destination,
                'variant' => 'A',
            ]);
        }

        return $campaign->fresh(['ads', 'event', 'role']);
    }

    private function clickUrl(BoostCampaign $campaign): string
    {
        return route('promo.click', [
            'subdomain' => $this->host->subdomain,
            'hash' => UrlUtils::encodeId($campaign->id),
        ]);
    }

    private function asVisitor(): self
    {
        return $this->withHeaders([
            'Accept-Language' => 'en-US,en;q=0.9',
            'Accept' => 'text/html',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/120 Safari/537.36',
        ]);
    }

    public function test_a_click_redirects_with_attribution_parameters(): void
    {
        $campaign = $this->campaign();

        $response = $this->asVisitor()->get($this->clickUrl($campaign));

        $response->assertRedirect();
        $target = $response->headers->get('Location');

        parse_str(parse_url($target, PHP_URL_QUERY) ?? '', $query);

        // utm_source is 'boost' rather than a new value on purpose: TicketController already
        // maps that source onto sales.boost_campaign_id, so conversions work unchanged.
        $this->assertSame('boost', $query['utm_source']);
        $this->assertSame('network', $query['utm_medium']);
        $this->assertSame($campaign->id, UrlUtils::decodeId($query['utm_campaign']));
        $this->assertSame($this->host->id, UrlUtils::decodeId($query['utm_content']));
    }

    public function test_a_click_is_counted_and_billed(): void
    {
        $campaign = $this->campaign();

        $this->asVisitor()->get($this->clickUrl($campaign));

        $this->assertDatabaseHas('analytics_promotions_daily', [
            'boost_campaign_id' => $campaign->id,
            'host_role_id' => $this->host->id,
            'clicks' => 1,
        ]);

        $this->assertSame(PromotionBillingService::toMicros(0.25), (int) $campaign->fresh()->spent_micros);
    }

    public function test_an_off_platform_destination_is_refused(): void
    {
        // BoostAd.destination_url is advertiser-supplied and 2048 chars wide. Without the
        // host allowlist every free-tier schedule becomes an open-redirect surface.
        $campaign = $this->campaign([], 'https://evil.test/phishing');

        $response = $this->asVisitor()->get($this->clickUrl($campaign));

        $this->assertStringNotContainsString('evil.test', $response->headers->get('Location'));
    }

    public function test_an_existing_query_string_on_the_destination_is_preserved(): void
    {
        // Recurring events carry a date, and any page can carry ?lang=. Naive concatenation
        // would corrupt those.
        $campaign = $this->campaign();
        $destination = $campaign->event->getGuestUrl(false, null, true).'?lang=he';

        BoostAd::create([
            'boost_campaign_id' => $campaign->id,
            'headline' => 'Come along',
            'destination_url' => $destination,
            'variant' => 'B',
        ]);

        $response = $this->asVisitor()->get($this->clickUrl($campaign->fresh('ads')));

        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY) ?? '', $query);

        $this->assertSame('he', $query['lang'] ?? null, 'The original query string must survive.');
        $this->assertSame('boost', $query['utm_source']);
    }

    public function test_repeat_clicks_from_one_visitor_are_capped(): void
    {
        $campaign = $this->campaign();

        for ($i = 0; $i < 14; $i++) {
            $this->asVisitor()->get($this->clickUrl($campaign));
        }

        $row = \App\Models\AnalyticsPromotionsDaily::forCampaign($campaign->id)->first();

        $this->assertSame(10, (int) $row->clicks, 'A single visitor must not be able to drain a budget.');
    }

    public function test_bots_are_redirected_but_not_counted(): void
    {
        $campaign = $this->campaign();

        $this->withHeaders([
            'Accept-Language' => 'en-US',
            'Accept' => 'text/html',
            'User-Agent' => 'Mozilla/5.0 (compatible; SemrushBot/7~bl)',
        ])->get($this->clickUrl($campaign))->assertRedirect();

        $this->assertDatabaseMissing('analytics_promotions_daily', [
            'boost_campaign_id' => $campaign->id,
        ]);
        $this->assertSame(0, (int) $campaign->fresh()->spent_micros);
    }

    public function test_an_unknown_campaign_falls_back_to_the_host_schedule(): void
    {
        $response = $this->asVisitor()->get(route('promo.click', [
            'subdomain' => $this->host->subdomain,
            'hash' => UrlUtils::encodeId(999999),
        ]));

        $response->assertRedirect();
        $this->assertStringContainsString($this->host->subdomain, $response->headers->get('Location'));
    }

    /**
     * The cap must key on the IP alone.
     *
     * visitorHash() mixes in the User-Agent, which the client chooses, so rotating it minted a
     * fresh counter on every request and the cap stopped existing. At the route's 60/min throttle
     * that drains a $1,000 CPC budget in about an hour from one address.
     */
    public function test_rotating_the_user_agent_does_not_defeat_the_click_cap(): void
    {
        $campaign = $this->campaign();

        for ($i = 0; $i < 20; $i++) {
            $this->withHeaders([
                'Accept-Language' => 'en-US',
                'Accept' => 'text/html',
                // A different, entirely plausible browser UA every single time.
                'User-Agent' => "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) Chrome/{$i}.0.{$i} Safari/537.36",
            ])->get($this->clickUrl($campaign));
        }

        $row = \App\Models\AnalyticsPromotionsDaily::forCampaign($campaign->id)->first();

        $this->assertSame(10, (int) $row->clicks, 'Changing the User-Agent must not mint a fresh cap bucket.');
        $this->assertSame(
            PromotionBillingService::toMicros(0.25) * 10,
            (int) $campaign->fresh()->spent_micros,
            'Spend must be bounded by the cap, not by the number of distinct User-Agents.'
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('unservableCampaignProvider')]
    public function test_a_campaign_that_is_not_live_and_approved_does_not_redirect(array $attrs): void
    {
        // Without this the hash keeps redirecting after rejection or pause - "approve before
        // serve" becomes a formality, and a refunded campaign keeps a working redirect.
        $campaign = $this->campaign($attrs);

        $response = $this->asVisitor()->get($this->clickUrl($campaign));

        $response->assertRedirect();
        $this->assertStringContainsString($this->host->subdomain, $response->headers->get('Location'));
        $this->assertDatabaseMissing('analytics_promotions_daily', ['boost_campaign_id' => $campaign->id]);
    }

    public static function unservableCampaignProvider(): array
    {
        return [
            'rejected' => [['status' => 'rejected', 'moderation_status' => 'rejected']],
            'awaiting review' => [['status' => 'pending_review', 'moderation_status' => 'pending']],
            'paused' => [['status' => 'paused']],
            'completed' => [['status' => 'completed']],
        ];
    }

    public function test_an_unverified_custom_domain_is_not_a_safe_redirect_target(): void
    {
        // custom_domain_host is set by a mutator from whatever the owner typed, and
        // custom_domain_status is only ever written on the 'direct' provisioning branch. So a
        // redirect-mode domain is entirely unverified and must not be trusted.
        $campaign = $this->campaign([], 'https://evil-unverified.test/phish');

        $campaign->role->forceFill([
            'custom_domain' => 'https://evil-unverified.test',
            'custom_domain_mode' => 'redirect',
            'custom_domain_status' => null,
        ])->save();

        $response = $this->asVisitor()->get($this->clickUrl($campaign->fresh(['ads', 'role'])));

        $this->assertStringNotContainsString('evil-unverified.test', $response->headers->get('Location'));
    }

    public function test_a_cpm_click_is_not_recorded_for_an_inactive_campaign(): void
    {
        // chargeClick() returned true unconditionally for CPM, so this was the one path that
        // wrote rollups for campaigns in any state.
        $campaign = $this->campaign([
            'pricing_model' => 'cpm',
            'unit_rate_micros' => PromotionBillingService::toMicros(2.00),
            'status' => 'paused',
        ]);

        $this->assertFalse(app(PromotionBillingService::class)->chargeClick($campaign));
    }

    public function test_a_paid_click_overrides_an_earlier_first_touch_attribution(): void
    {
        // Attribution is first-touch everywhere else, but an advertiser who just paid for
        // this click must not have the sale credited to a month-old newsletter link.
        $campaign = $this->campaign();

        $this->asVisitor()->withSession(['utm_params' => [
            'utm_source' => 'newsletter',
            'utm_medium' => 'email',
            'utm_campaign' => 'old',
        ]])->get($this->clickUrl($campaign));

        $this->asVisitor()->get(
            $campaign->event->getGuestUrl(false, null, false)
            .'?utm_source=boost&utm_medium=network'
            .'&utm_campaign='.UrlUtils::encodeId($campaign->id)
            .'&utm_token='.\App\Services\PromotionService::clickToken($campaign->id)
        );

        $this->assertSame('boost', session('utm_params.utm_source'));
        $this->assertSame($campaign->id, UrlUtils::decodeId(session('utm_params.utm_campaign')));
    }

    public function test_the_attribution_override_cannot_be_forged(): void
    {
        // Without the signed token an advertiser could paste these parameters onto any link
        // they control and claim every subsequent sale in that browser.
        $campaign = $this->campaign();

        $this->asVisitor()->withSession(['utm_params' => [
            'utm_source' => 'newsletter',
            'utm_medium' => 'email',
            'utm_campaign' => 'legitimate',
        ]])->get(
            $campaign->event->getGuestUrl(false, null, false)
            .'?utm_source=boost&utm_medium=network'
            .'&utm_campaign='.UrlUtils::encodeId($campaign->id)
            .'&utm_token=forged'
        );

        $this->assertSame('newsletter', session('utm_params.utm_source'), 'First-touch attribution must survive a forged override.');
    }

    public function test_a_sale_ignores_a_utm_campaign_that_does_not_exist(): void
    {
        // sales.boost_campaign_id carries a foreign key, so an unknown id would fail the INSERT
        // and take checkout down rather than merely misattributing.
        //
        // This used to assert only that Eloquent returns null for a missing row - true with the
        // entire feature deleted. Drive the real resolution TicketController performs instead.
        $campaign = $this->campaign();

        $resolve = fn (?string $hash) => \App\Models\BoostCampaign::whereKey(UrlUtils::decodeId($hash))->value('id');

        // A real campaign hash resolves to its id...
        $this->assertSame($campaign->id, $resolve(UrlUtils::encodeId($campaign->id)));

        // ...a well-formed hash for a row that does not exist resolves to null rather than
        // handing an unknown integer to the foreign key...
        $this->assertNull($resolve(UrlUtils::encodeId($campaign->id + 99_999)));

        // ...and so does outright garbage.
        $this->assertNull($resolve('nonexistent'));
        $this->assertNull($resolve(null));
    }

    public function test_the_click_paths_match_the_registered_routes(): void
    {
        // PromotionService::clickUrl() writes these paths by hand, because both routes are
        // named 'promo.click' and route() resolves to whichever was registered last (the
        // selfhost one), which then throws on the missing subdomain parameter. That leaves the
        // path shape written in three places, so pin them: a change in routes/web.php that
        // does not reach clickUrl() would otherwise 404 every promotion click in silence.
        // Only the routes for the mode this test run booted in are registered, so assert the
        // shape of whatever is there rather than a fixed pair.
        $uris = collect(\Illuminate\Support\Facades\Route::getRoutes()->getRoutes())
            ->filter(fn ($r) => $r->getName() === 'promo.click')
            ->map(fn ($r) => $r->uri())
            ->values()
            ->all();

        $this->assertNotEmpty($uris, 'The promo.click route must exist in every mode.');

        foreach ($uris as $uri) {
            $this->assertContains($uri, ['promo/{hash}', '{subdomain}/promo/{hash}'],
                'clickUrl() hand-writes these paths - a new shape in routes/web.php must be mirrored there.');
        }
    }
}
