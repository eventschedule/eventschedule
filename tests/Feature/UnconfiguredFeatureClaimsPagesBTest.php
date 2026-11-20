<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stops the marketing site describing a feature this install has switched off.
 *
 * Outlook sync, web push, the Stay22 map, the wallets, ads and promotions, and Facebook Login are
 * each opt-in per INSTALL: with their credentials unset the app shows none of them, and
 * eventschedule.com ran for a while with several unset while these pages described them as plain
 * facts. Each claim now reads the predicate the feature itself does (outlook_sync_enabled(),
 * OneSignalService::isConfigured(), Stay22Service::isEnabled(), the wallet services, the ads
 * config, facebook_login_enabled()).
 *
 * Two halves per feature, as in GoogleWalletMarketingClaimTest: the off half fails when a claim is
 * pasted forward ungated, and the on half fails when a claim is deleted instead of gated. Each
 * phrase is one only the gated sentence prints, because most of these pages also name Outlook as
 * an "Add to calendar" link, which is not the integration and stays.
 */
class UnconfiguredFeatureClaimsPagesBTest extends TestCase
{
    use RefreshDatabase;

    /** Route name => a phrase (lower case) that only the gated Outlook sync claim prints. */
    private const OUTLOOK_PAGES = [
        // Not 'microsoft 365' alone: the layout's SoftwareApplication node (SeoUtils) names it on
        // every marketing page, and that node is not this page's own copy.
        'marketing.index' => 'microsoft 365 both sync',
        'marketing.integrations' => 'microsoft graph',
        'marketing.google_calendar' => 'outlook and caldav connect',
        'marketing.for_sports_leagues' => 'google, outlook and caldav sync for the coaches',
        'marketing.for_talent' => 'outlook and caldav calendars work the same way',
        'marketing.for_theater_performers' => 'google, outlook and caldav',
        'marketing.for_theaters' => 'google, outlook and caldav',
        'marketing.for_venues' => 'google, outlook and caldav',
        'marketing.for_visual_artists' => 'google, outlook and caldav',
        'marketing.for_watch_parties' => 'outlook or microsoft 365',
        'marketing.for_webinars' => 'create a teams meeting',
        'marketing.for_workshop_instructors' => 'google, outlook and caldav',
        'marketing.invoiceninja' => 'google, outlook and caldav',
        'marketing.newsletters' => 'google, outlook and caldav',
        'marketing.online_events' => 'create teams meetings for online events',
        'marketing.private_events' => 'sensitivity on outlook',
        'marketing.recurring_events' => 'outlook and microsoft 365',
        'marketing.replace' => 'google, outlook and caldav',
        'marketing.replace_google_forms' => 'google, outlook and caldav',
        'marketing.search' => 'google calendar, outlook and caldav',
        'marketing.stackapella' => 'google calendar or outlook',
        'marketing.stripe' => 'google and outlook calendars',
        'marketing.sub_schedules' => 'google, outlook and caldav',
        'marketing.team_scheduling' => 'outlook and caldav included',
        'marketing.use_cases' => 'google calendar, outlook or caldav',
        'marketing.why_create_account' => 'google, outlook and caldav',
        'marketing.wordpress_event_calendar' => 'google calendar or outlook',
        'marketing.privacy' => 'outlook calendar data',
    ];

    private const PUSH_PAGES = [
        'marketing.integrations' => 'onesignal',
        'marketing.team_scheduling' => 'unless push notifications are set up',
        'marketing.privacy' => 'onesignal',
    ];

    private const STAY22_PAGES = [
        'marketing.integrations' => 'stay22',
        'marketing.privacy' => 'stay22',
    ];

    private const ADS_PAGES = [
        'marketing.private_events' => 'on-network promotion',
        'marketing.privacy' => '__gads',
    ];

    public function test_outlook_sync_is_not_claimed_where_it_is_not_set_up(): void
    {
        $this->outlook(false);
        $this->assertPhrases(self::OUTLOOK_PAGES, false, 'Outlook sync');
    }

    public function test_outlook_sync_is_claimed_where_it_is_set_up(): void
    {
        $this->outlook(true);
        $this->assertPhrases(self::OUTLOOK_PAGES, true, 'Outlook sync');
    }

    public function test_web_push_follows_onesignal(): void
    {
        config(['services.onesignal.app_id' => null, 'services.onesignal.rest_api_key' => null]);
        $this->assertPhrases(self::PUSH_PAGES, false, 'web push');

        config(['services.onesignal.app_id' => 'app-id', 'services.onesignal.rest_api_key' => 'rest-key']);
        $this->assertPhrases(self::PUSH_PAGES, true, 'web push');
    }

    public function test_the_accommodation_map_follows_stay22(): void
    {
        config(['stay22.enabled' => false]);
        $this->assertPhrases(self::STAY22_PAGES, false, 'the Stay22 map');

        config(['stay22.enabled' => true]);
        $this->assertPhrases(self::STAY22_PAGES, true, 'the Stay22 map');
    }

    public function test_ads_and_promotions_follow_the_ads_switches(): void
    {
        config(['ads.enabled' => false, 'ads.native_enabled' => false, 'ads.adsense_enabled' => false]);
        $this->assertPhrases(self::ADS_PAGES, false, 'ads');
        $this->get(route('marketing.private_events'))->assertOk()
            ->assertDontSee('shows ads, none are ever rendered');

        config(['ads.enabled' => true, 'ads.native_enabled' => true, 'ads.adsense_enabled' => true]);
        $this->assertPhrases(self::ADS_PAGES, true, 'ads');
        $this->get(route('marketing.private_events'))->assertOk()
            ->assertSee('shows ads, none are ever rendered');
    }

    public function test_facebook_login_follows_its_credentials(): void
    {
        config(['services.facebook.client_id' => null, 'services.facebook.client_secret' => null]);
        $this->assertPhrases(['marketing.privacy' => 'google or facebook'], false, 'Facebook Login');

        config(['services.facebook.client_id' => 'fb-id', 'services.facebook.client_secret' => 'fb-secret']);
        $this->assertPhrases(['marketing.privacy' => 'google or facebook'], true, 'Facebook Login');
    }

    public function test_the_integrations_page_names_only_the_wallets_that_are_set_up(): void
    {
        // AppleWalletService::isConfigured() reads real certificates, so only Apple's off half is
        // held here; Google's is held both ways.
        config(['services.google.wallet_issuer_id' => null, 'services.google.wallet_service_account' => null]);
        $this->assertPhrases(['marketing.integrations' => 'google wallet'], false, 'Google Wallet');
        $this->assertPhrases(['marketing.integrations' => 'apple wallet'], false, 'Apple Wallet');

        config([
            'services.google.wallet_issuer_id' => '3388000000012345678',
            'services.google.wallet_service_account' => base64_encode(json_encode([
                'client_email' => 'wallet@example-project.iam.gserviceaccount.com',
                'private_key' => 'not-a-real-key',
            ])),
        ]);
        $this->assertPhrases(['marketing.integrations' => 'google wallet'], true, 'Google Wallet');
        $this->assertPhrases(['marketing.integrations' => 'apple wallet'], false, 'Apple Wallet');
    }

    public function test_the_integrations_page_counts_only_the_ports_it_shows(): void
    {
        $this->allOff();
        $off = $this->get(route('marketing.integrations'))->assertOk();
        $off->assertSee('Eleven ports', false)->assertSee('Port 12')->assertSee('Two ports the site operator', false);

        $this->outlook(true);
        config([
            'services.onesignal.app_id' => 'app-id', 'services.onesignal.rest_api_key' => 'rest-key',
            'stay22.enabled' => true,
        ]);
        $on = $this->get(route('marketing.integrations'))->assertOk();
        $on->assertSee('Fourteen ports', false)->assertSee('Port 15')->assertSee('Four ports the site operator', false);
    }

    public function test_the_structured_data_parses_either_way(): void
    {
        foreach ([false, true] as $live) {
            $live ? $this->allOn() : $this->allOff();

            foreach (array_unique(array_merge(array_keys(self::OUTLOOK_PAGES), ['marketing.private_events'])) as $name) {
                preg_match_all('~<script type="application/ld\+json"[^>]*>(.*?)</script>~s', $this->get(route($name))->getContent(), $matches);

                foreach ($matches[1] as $i => $raw) {
                    $this->assertIsArray(json_decode($raw, true), "JSON-LD block {$i} on {$name} did not decode with the features ".($live ? 'on' : 'off'));
                }
            }
        }
    }

    /** @param array<string, string> $pages */
    private function assertPhrases(array $pages, bool $present, string $feature): void
    {
        foreach ($pages as $name => $phrase) {
            $body = strtolower(html_entity_decode($this->get(route($name))->assertOk()->getContent(), ENT_QUOTES));

            if ($present) {
                $this->assertStringContainsString($phrase, $body, "{$name} says nothing about {$feature} with it switched on, so its claim was deleted rather than gated");
            } else {
                $this->assertStringNotContainsString($phrase, $body, "{$name} claims {$feature} on an install that has it switched off");
            }
        }
    }

    private function outlook(bool $on): void
    {
        config([
            'services.microsoft.client_id' => $on ? 'azure-client-id' : null,
            'services.microsoft.client_secret' => $on ? 'azure-client-secret' : null,
        ]);
    }

    private function allOff(): void
    {
        $this->outlook(false);
        config([
            'services.onesignal.app_id' => null, 'services.onesignal.rest_api_key' => null,
            'stay22.enabled' => false,
            'ads.enabled' => false, 'ads.native_enabled' => false, 'ads.adsense_enabled' => false,
            'services.facebook.client_id' => null, 'services.facebook.client_secret' => null,
            'services.google.wallet_issuer_id' => null, 'services.google.wallet_service_account' => null,
        ]);
    }

    private function allOn(): void
    {
        $this->outlook(true);
        config([
            'services.onesignal.app_id' => 'app-id', 'services.onesignal.rest_api_key' => 'rest-key',
            'stay22.enabled' => true,
            'ads.enabled' => true, 'ads.native_enabled' => true, 'ads.adsense_enabled' => true,
            'services.facebook.client_id' => 'fb-id', 'services.facebook.client_secret' => 'fb-secret',
        ]);
    }
}
