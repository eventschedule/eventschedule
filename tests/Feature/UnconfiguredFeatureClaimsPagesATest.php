<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stops the marketing site describing integrations the install has not switched on.
 *
 * Outlook / Microsoft 365 sync (an Azure app), web push (OneSignal) and the ads and promotions
 * network (ADS_ENABLED, PROMOTIONS_ENGINE_ENABLED) are each opt-in per INSTALL, and
 * eventschedule.com ran without some of them while these pages claimed them as plain facts.
 * Every claim now reads the same env-only predicate the feature itself does, so it is safe in
 * edge-cached HTML: outlook_sync_enabled(), OneSignalService::isConfigured(), config('ads.*').
 *
 * Two halves per feature, because either alone proves nothing (see
 * GoogleWalletMarketingClaimTest). The off half fails when a claim is pasted forward ungated;
 * the on half fails when a claim is deleted instead of gated.
 *
 * Left alone on purpose: "Add to Outlook" links and the .ics download, subscribing to a feed,
 * importing a published Outlook calendar's link, and pasting a Teams meeting link on an online
 * event. None of them needs the integration.
 */
class UnconfiguredFeatureClaimsPagesATest extends TestCase
{
    use RefreshDatabase;

    /** Every page that claimed Outlook sync. */
    private const OUTLOOK_PAGES = [
        'marketing.about',
        'marketing.ai',
        'marketing.appointments',
        'marketing.availability',
        'marketing.caldav',
        'marketing.calendar_sync',
        'marketing.community_event_calendar',
        'marketing.compare',
        'marketing.demos',
        'marketing.embed_calendar',
        'marketing.faq',
        'marketing.features',
        'marketing.for_ai_agents',
        'marketing.for_bars',
        'marketing.for_breweries_and_wineries',
        'marketing.for_circus_acrobatics',
        'marketing.for_comedians',
        'marketing.for_community_centers',
        'marketing.for_curators',
        'marketing.for_dance_groups',
        'marketing.for_djs',
        'marketing.for_farmers_markets',
        'marketing.for_fitness_and_yoga',
        'marketing.for_hotels_and_resorts',
        'marketing.for_libraries',
        'marketing.for_live_concerts',
        'marketing.for_live_qa_sessions',
        'marketing.for_magicians',
        'marketing.for_museums',
        'marketing.for_music_venues',
        'marketing.for_musicians',
        'marketing.for_nightclubs',
        'marketing.for_nonprofits',
        'marketing.for_online_classes',
        'marketing.for_schools',
        'marketing.for_spoken_word',
    ];

    /**
     * The ways those pages said it. None is an Add to Calendar menu, a feed or an import, which
     * name Outlook as a calendar app and stay either way.
     */
    private const OUTLOOK_PHRASES = [
        'Google, Outlook and CalDAV',
        'Google, Outlook or CalDAV',
        'Google, Outlook, and CalDAV',
        'Google, Outlook, or CalDAV',
        'Google Calendar, Outlook and',
        // Not bare "Google Calendar, Outlook or": the CalDAV page lists Outlook among the apps that subscribe to a feed.
        'Google Calendar, Outlook or CalDAV',
        'Google Calendar, Outlook or any CalDAV',
        'Google Calendar or Outlook, two-way',
        // Not bare "Google or Outlook calendar": the AI page imports one's public link.
        'Google or Outlook calendar,',
        'Google or Outlook calendar you',
        'Google and Outlook',
        'Google or Microsoft',
        'Outlook and CalDAV',
        'Outlook and Microsoft 365 sync',
        'Outlook Calendar',
        'Outlook calendar sync',
        'Outlook / Microsoft 365',
        'Microsoft both',
        'Teams meeting',
        'Outlook can push',
        'Disconnect on Outlook',
        'in Outlook',
        'to Outlook for',
    ];

    private const PROMOTION_PHRASES = ['on-network', 'On-network', 'promotions network', 'Channel B'];

    public function test_an_install_without_an_azure_app_does_not_claim_outlook_sync(): void
    {
        $this->outlook(false);

        foreach (self::OUTLOOK_PAGES as $name) {
            $body = $this->withoutKeepExploring($this->get(route($name))->assertOk()->getContent());

            foreach (self::OUTLOOK_PHRASES as $phrase) {
                $this->assertStringNotContainsString($phrase, $body, "{$name} still says \"{$phrase}\" on an install that cannot sync with Outlook");
            }
        }

        foreach (['marketing.features', 'marketing.calendar_sync', 'marketing.caldav'] as $name) {
            $body = $this->withoutKeepExploring($this->get(route($name))->assertOk()->getContent());
            $this->assertStringNotContainsString('/outlook-calendar"', $body, "{$name} links to the Outlook page on an install that cannot sync with Outlook");
        }
    }

    public function test_an_install_with_an_azure_app_claims_it_on_every_page(): void
    {
        $this->outlook(true);

        foreach (self::OUTLOOK_PAGES as $name) {
            $body = $this->withoutKeepExploring($this->get(route($name))->assertOk()->getContent());
            $found = array_filter(self::OUTLOOK_PHRASES, fn (string $phrase) => str_contains($body, $phrase));

            $this->assertNotEmpty($found, "{$name} says nothing about Outlook sync with it switched on, so its claim was deleted rather than gated");
        }
    }

    public function test_the_calendar_sync_page_drops_the_outlook_carrier_whole(): void
    {
        $this->outlook(false);
        $this->get(route('marketing.calendar_sync'))->assertOk()
            ->assertSee('Two carriers.')
            ->assertSee('Drafts stay home')
            ->assertDontSee('Three carriers.')
            ->assertDontSee('Outlook &amp; CalDAV', false);

        $this->outlook(true);
        $this->get(route('marketing.calendar_sync'))->assertOk()
            ->assertSee('Three carriers.')
            ->assertSee('Outlook / Microsoft 365')
            ->assertDontSee('Drafts stay home');
    }

    public function test_web_push_is_claimed_only_where_onesignal_is_configured(): void
    {
        $this->push(false);
        $this->get(route('marketing.feedback'))->assertOk()
            ->assertDontSee('web push')
            ->assertDontSee('A push, straight away');
        // The small-print row gives its place to another true one.
        $this->get(route('marketing.features'))->assertOk()
            ->assertDontSee('mirroring your email alerts')
            ->assertSee('Automatic translation');

        $this->push(true);
        $this->get(route('marketing.feedback'))->assertOk()
            ->assertSee('web push')
            ->assertSee('A push, straight away');
        $this->get(route('marketing.features'))->assertOk()
            ->assertSee('Browser and mobile web push mirroring your email alerts');
    }

    public function test_ads_and_promotions_are_described_only_where_the_install_runs_them(): void
    {
        $this->ads(false);

        $boost = $this->get(route('marketing.boost'))->assertOk()->getContent();
        foreach (self::PROMOTION_PHRASES as $phrase) {
            $this->assertStringNotContainsString($phrase, $boost, "the Boost page says \"{$phrase}\" with the promotions network off");
        }
        $this->get(route('marketing.embed_calendar'))->assertOk()->assertDontSee('An embed never does, by design.');
        $this->get(route('marketing.custom_domain'))->assertOk()
            ->assertDontSee('Paid plans never carry ads')
            ->assertDontSee('Paid plans carry no ads');
        $this->get(route('marketing.for_hotels_and_resorts'))->assertOk()->assertDontSee('footer or ads');

        $this->ads(true);

        $boost = $this->get(route('marketing.boost'))->assertOk()->getContent();
        foreach (self::PROMOTION_PHRASES as $phrase) {
            $this->assertStringContainsString($phrase, $boost, "the Boost page lost \"{$phrase}\" with the promotions network on");
        }
        $this->get(route('marketing.embed_calendar'))->assertOk()->assertSee('An embed never does, by design.');
        $this->get(route('marketing.custom_domain'))->assertOk()
            ->assertSee('Paid plans never carry ads')
            ->assertSee('Paid plans carry no ads');
        $this->get(route('marketing.for_hotels_and_resorts'))->assertOk()->assertSee('footer or ads');
    }

    public function test_apple_wallet_is_not_named_without_a_pass_certificate(): void
    {
        // phpunit.xml pins the Apple Wallet settings empty, and isConfigured() wants a real
        // certificate, so only the off half can be held here.
        foreach (['marketing.features', 'marketing.faq'] as $name) {
            $this->get(route($name))->assertOk()->assertDontSee('Apple Wallet');
        }
    }

    public function test_the_structured_data_parses_either_way(): void
    {
        // The FAQ JSON-LD is built from the same arrays the visible FAQ is, so a gated answer
        // must still leave every block decodable.
        foreach ([false, true] as $live) {
            $this->outlook($live);
            $this->ads($live);
            $this->push($live);

            foreach (['marketing.calendar_sync', 'marketing.caldav', 'marketing.boost', 'marketing.custom_domain', 'marketing.faq', 'marketing.feedback'] as $name) {
                $blocks = $this->jsonLdBlocks($this->get(route($name))->assertOk()->getContent());
                $this->assertNotEmpty($blocks, "{$name} emits no JSON-LD at all");

                foreach ($blocks as $i => $block) {
                    $this->assertIsArray($block, "JSON-LD block {$i} on {$name} did not decode");
                }
            }
        }
    }

    /**
     * Two pieces these pages print but do not own, both of which still name Outlook: the "Keep
     * exploring" strip (<x-marketing.related-pages />, from config/marketing_related.php) and
     * the plan rate card row /faq shares with /pricing (App\Utils\PlanRateCard). This file
     * holds the pages' own copy.
     */
    private function withoutKeepExploring(string $html): string
    {
        $html = preg_replace('~<section class="bg-gray-50 dark:bg-\[#0f0f14\] border-t[^"]*">.*?</section>~s', '', $html, 1) ?? $html;

        return str_replace('Two-way Google, Outlook and CalDAV sync', '', $html);
    }

    private function outlook(bool $on): void
    {
        config([
            'services.microsoft.client_id' => $on ? 'test-client-id' : null,
            'services.microsoft.client_secret' => $on ? 'test-client-secret' : null,
        ]);
    }

    private function push(bool $on): void
    {
        config([
            'services.onesignal.app_id' => $on ? 'test-app-id' : null,
            'services.onesignal.rest_api_key' => $on ? 'test-rest-key' : null,
        ]);
    }

    private function ads(bool $on): void
    {
        config(['ads.enabled' => $on, 'ads.native_enabled' => $on]);
    }

    /**
     * Same extraction as MarketingStructuredDataTest::jsonLdBlocks().
     *
     * @return array<int, mixed>
     */
    private function jsonLdBlocks(string $html): array
    {
        preg_match_all('~<script type="application/ld\+json"[^>]*>(.*?)</script>~s', $html, $matches);

        return array_map(fn (string $raw) => json_decode($raw, true), $matches[1]);
    }
}
