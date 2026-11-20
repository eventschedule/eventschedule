<?php

namespace Tests\Feature;

use App\Http\Controllers\MarketingController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The user guide describes a feature an operator switches on with credentials only where it can
 * be used.
 *
 * Outlook sync, Facebook login, web push, the two wallets, the nearby-accommodation map and the
 * promotions network each need keys the operator sets. On hosted, the admin portal hides the panel
 * of one that is not set up, and eventschedule.com ships several of them off, so a guide that
 * described them as plain fact sent customers looking for screens they would never find. A
 * selfhost operator reads about each either way: the admin portal there says how to set it up,
 * which is the rule outlook_sync_offered() already encodes.
 *
 * Three halves, because each alone proves nothing. Hosted and off fails when a passage is pasted
 * forward ungated; hosted and on fails when one is deleted instead of gated; selfhost fails when
 * the gate forgets the operator. The guide's search follows the same rule, or a result opens a
 * section that is not on the page.
 */
class UnconfiguredFeatureClaimsGuideTest extends TestCase
{
    use RefreshDatabase;

    /**
     * What each feature's passages say, page by page. Every string is something only the gated
     * passage says: "Outlook" alone is not, because "Add to Outlook", an iCal feed in Outlook and
     * importing a published Outlook calendar are not the integration and stay.
     */
    private const CLAIMS = [
        'outlook' => [
            'marketing.docs.account_settings' => ['id="microsoft"', 'Connect Outlook Calendar', 'Microsoft Graph'],
            'marketing.docs.creating_schedules' => ['id="integrations-microsoft"', 'Select Outlook Calendar', 'To Outlook Calendar', 'Google and Outlook rows', 'Google Calendar, Outlook or CalDAV normally'],
            'marketing.docs.creating_events' => ['Google or Outlook calendar', 'Google, Outlook and CalDAV calendars', '#integrations-microsoft'],
            'marketing.docs.getting_started' => ['Google Calendar, Outlook Calendar and CalDAV Calendar'],
            'marketing.docs.appointments' => ['Google Calendar, Outlook or CalDAV'],
            'marketing.docs.developer.api' => ['Outlook sync subscriptions', 'Outlook calendar and CalDAV calendar'],
        ],
        'facebook' => [
            'marketing.docs.account_settings' => ['id="facebook"', 'Continue with Facebook', 'Verify with Facebook', 'Google or Facebook', 'connected Facebook account'],
        ],
        'push' => [
            'marketing.docs.creating_schedules' => ['id="notifications-push"', 'OneSignal', 'push notification'],
            'marketing.docs.tickets' => ['push notification'],
            'marketing.docs.boost' => ['web push'],
            'marketing.docs.referral_program' => ['push notification'],
        ],
        'stay22' => [
            'marketing.docs.creating_schedules' => ['id="accommodation"', 'Stay22', 'accommodation'],
            'marketing.docs.schedule_styling' => ['#gp-accommodation'],
        ],
        'promotions' => [
            'marketing.docs.boost' => ['id="on-network"', 'On this site', 'on-network', 'promoted card'],
            'marketing.docs.creating_schedules' => ["Do not show other schedules' promotions", 'promotions network'],
            'marketing.docs.account_settings' => ['on-network promotions'],
            'marketing.docs.sharing' => ['No ads, ever'],
            'marketing.docs.schedule_styling' => ['where a platform runs them on free schedules'],
        ],
        'google_wallet' => [
            'marketing.docs.tickets' => ['id="wallet-passes"', 'Add to Google Wallet', 'Google Wallet archives'],
            'marketing.docs.subscriptions' => ['Add to Google Wallet'],
            'marketing.docs.allocated_seating' => ['on a wallet pass'],
        ],
    ];

    /** Index rows ("Page|Section") that open a gated section, by feature. */
    private const SEARCH_ROWS = [
        'outlook' => ['Creating Schedules|Outlook Calendar', 'Account Settings|Outlook Calendar'],
        'facebook' => ['Account Settings|Facebook Settings'],
        'push' => ['Creating Schedules|Push Notifications'],
        'stay22' => ['Creating Schedules|Accommodation'],
        'promotions' => ['Creating Schedules|Do Not Show Other Schedules\' Promotions', 'Boost|On-Network Promotions', 'Boost|Hosting Other Schedules\' Promotions'],
        'google_wallet' => ['Selling Tickets|Wallet Passes'],
    ];

    public function test_hosted_without_the_credentials_the_guide_does_not_describe_them(): void
    {
        config(['app.hosted' => true]);
        $this->featuresOff();

        foreach (self::CLAIMS as $feature => $pages) {
            foreach ($pages as $route => $phrases) {
                $html = $this->page($route);

                foreach ($phrases as $phrase) {
                    $this->assertFalse(str_contains(strtolower($html), strtolower($phrase)), "{$route} describes {$feature} on a hosted install that has not set it up ({$phrase})");
                }
            }
        }

        // Neither wallet is set up, so neither is described on the tickets page. (The docs sidebar
        // still names the selfhost wallet setup pages, which are an operator's guides.)
        $this->assertFalse(str_contains($this->page('marketing.docs.tickets'), 'Apple Wallet raises'));
        // A section the Plan tab shows only where the install runs ads.
        $this->assertStringNotContainsString('id="plan-ads"', $this->page('marketing.docs.managing_schedules'));
    }

    public function test_hosted_with_the_credentials_the_guide_describes_them(): void
    {
        config(['app.hosted' => true]);
        $this->featuresOn();

        foreach (self::CLAIMS as $feature => $pages) {
            foreach ($pages as $route => $phrases) {
                $html = $this->page($route);

                foreach ($phrases as $phrase) {
                    $this->assertTrue(str_contains(strtolower($html), strtolower($phrase)), "{$route} says nothing of {$feature} with it set up, so its passage was deleted rather than gated ({$phrase})");
                }
            }
        }

        // Apple Wallet needs a signing certificate this test cannot make, so here only Google is
        // set up: the page names the one wallet, and says nothing of buttons for the other.
        $tickets = $this->page('marketing.docs.tickets');
        $this->assertFalse(str_contains($tickets, 'Apple Wallet raises'));
        $this->assertFalse(str_contains($tickets, 'Apple Wallet greys'));
        $this->assertTrue(str_contains($tickets, 'Google Wallet archives it once that date passes'));
        $this->assertStringContainsString('Ticket buyers can save their ticket into Google Wallet from the ticket page', $tickets);

        $this->assertStringContainsString('id="plan-ads"', $this->page('marketing.docs.managing_schedules'));
    }

    public function test_on_selfhost_the_guide_describes_every_feature_the_operator_can_set_up(): void
    {
        config(['app.hosted' => false]);
        $this->featuresOff();

        foreach (self::CLAIMS as $feature => $pages) {
            foreach ($pages as $route => $phrases) {
                $html = $this->page($route);

                foreach ($phrases as $phrase) {
                    $this->assertTrue(str_contains(strtolower($html), strtolower($phrase)), "{$route} hides {$feature} from a selfhost operator, who can set it up ({$phrase})");
                }
            }
        }

        $this->assertTrue(str_contains($this->page('marketing.docs.tickets'), 'Google Wallet archives it and Apple Wallet greys it out'));
    }

    public function test_the_guides_search_leaves_out_what_the_guide_leaves_out(): void
    {
        config(['app.hosted' => true]);
        $this->featuresOff();
        $rows = $this->searchRows();

        foreach (self::SEARCH_ROWS as $feature => $keys) {
            foreach ($keys as $key) {
                $this->assertNotContains($key, $rows, "the search offers {$key}, a {$feature} section the hosted guide leaves out");
            }
        }
        $this->assertStringNotContainsStringIgnoringCase('outlook', $this->searchText('Creating Schedules|Calendar Sync'));
        $this->assertStringNotContainsStringIgnoringCase('apple', $this->searchText('Creating Events|Calendar Sync').$this->searchText('Creating Schedules|Page Content'));

        foreach ([true, false] as $hosted) {
            config(['app.hosted' => $hosted]);
            $hosted ? $this->featuresOn() : $this->featuresOff();
            $rows = $this->searchRows();

            foreach (self::SEARCH_ROWS as $feature => $keys) {
                foreach ($keys as $key) {
                    $this->assertContains($key, $rows, ($hosted ? 'hosted with it set up' : 'selfhost').": the search lost {$key}");
                }
            }
        }
    }

    public function test_the_structured_data_parses_either_way(): void
    {
        foreach ([true, false] as $on) {
            config(['app.hosted' => true]);
            $on ? $this->featuresOn() : $this->featuresOff();

            foreach (['marketing.docs.creating_schedules', 'marketing.docs.boost', 'marketing.docs.getting_started'] as $route) {
                preg_match_all('~<script type="application/ld\+json"[^>]*>(.*?)</script>~s', $this->page($route), $blocks);
                $this->assertNotEmpty($blocks[1], "{$route} emits no JSON-LD");

                foreach ($blocks[1] as $i => $raw) {
                    $this->assertIsArray(json_decode($raw, true), "JSON-LD block {$i} on {$route} does not decode");
                }
            }
        }
    }

    private function page(string $route): string
    {
        return $this->get(route($route))->assertOk()->getContent();
    }

    /** @return array<int, string> */
    private function searchRows(): array
    {
        return array_map(fn ($row) => $row['page'].'|'.$row['section'], $this->searchIndex());
    }

    private function searchText(string $key): string
    {
        foreach ($this->searchIndex() as $row) {
            if ($row['page'].'|'.$row['section'] === $key) {
                return $row['description'].' '.$row['keywords'];
            }
        }

        $this->fail("the search has no row {$key}");
    }

    private function searchIndex(): array
    {
        return (new \ReflectionMethod(MarketingController::class, 'getDocSearchIndex'))->invoke(app(MarketingController::class));
    }

    /** Explicit both ways, so a developer's own .env cannot set a feature up under the test. */
    private function featuresOff(): void
    {
        config([
            'services.microsoft.client_id' => null,
            'services.microsoft.client_secret' => null,
            'services.facebook.client_id' => null,
            'services.facebook.client_secret' => null,
            'services.onesignal.app_id' => null,
            'services.onesignal.rest_api_key' => null,
            'stay22.enabled' => false,
            'ads.enabled' => false,
            'services.google.wallet_issuer_id' => null,
            'services.google.wallet_service_account' => null,
            'services.apple.wallet_pass_type_id' => null,
            'services.apple.wallet_team_id' => null,
        ]);
    }

    private function featuresOn(): void
    {
        config([
            'services.microsoft.client_id' => 'test-client-id',
            'services.microsoft.client_secret' => 'test-secret',
            'services.facebook.client_id' => 'test-facebook-id',
            'services.facebook.client_secret' => 'test-facebook-secret',
            'services.onesignal.app_id' => 'test-onesignal-app',
            'services.onesignal.rest_api_key' => 'test-onesignal-key',
            'stay22.enabled' => true,
            'ads.enabled' => true,
            // isConfigured() only checks that the key decodes and names both fields; nothing is signed.
            'services.google.wallet_issuer_id' => '3388000000012345678',
            'services.google.wallet_service_account' => base64_encode(json_encode([
                'client_email' => 'wallet@example-project.iam.gserviceaccount.com',
                'private_key' => 'not-a-real-key',
            ])),
            'services.apple.wallet_pass_type_id' => null,
            'services.apple.wallet_team_id' => null,
        ]);
    }
}
