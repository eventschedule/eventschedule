<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Stops the marketing site promising an "Add to Google Wallet" button the install cannot render.
 *
 * Wallet passes are opt-in per INSTALL: GoogleWalletService::isConfigured() gates every badge, the
 * route handler and the confirmation email, and with GOOGLE_WALLET_* unset none of them appear.
 * These eight pages used to describe the button as a plain fact regardless, so a production app
 * spec without the keys - which is how eventschedule.com shipped it - meant eight pages promising
 * something no buyer could find. Each claim now reads the same predicate, the way the privacy
 * page's processor register already did.
 *
 * Two halves, because either one alone proves nothing. The off half fails when a claim is pasted
 * forward ungated. The on half fails when a claim is deleted instead of gated, which would pass the
 * off half and lose the copy for good once the keys are set.
 */
class GoogleWalletMarketingClaimTest extends TestCase
{
    use RefreshDatabase;

    /** Every page that described the button as a plain fact. */
    private const PAGES = [
        'marketing.passes',
        'marketing.ticketing',
        'marketing.for_nightclubs',
        'marketing.for_live_concerts',
        'marketing.for_theaters',
        'marketing.white_label',
        'marketing.faq',
        'marketing.check_in',
    ];

    public function test_an_install_without_wallet_credentials_does_not_advertise_the_button(): void
    {
        $this->walletOff();

        foreach (self::PAGES as $name) {
            $body = strtolower($this->get(route($name))->assertOk()->getContent());

            $this->assertStringNotContainsString(
                'google wallet',
                $body,
                "{$name} advertises Google Wallet on an install that cannot issue a pass"
            );
        }
    }

    public function test_an_install_with_wallet_credentials_advertises_it_on_every_page(): void
    {
        $this->walletOn();

        foreach (self::PAGES as $name) {
            $body = strtolower($this->get(route($name))->assertOk()->getContent());

            $this->assertStringContainsString(
                'google wallet',
                $body,
                "{$name} says nothing about Google Wallet with it switched on, so its claim was deleted rather than gated"
            );
        }
    }

    public function test_the_structured_data_parses_either_way(): void
    {
        // The featureList lines are gated by an @if inside the JSON itself, the one place a stray
        // comma would silently invalidate the whole block. MarketingStructuredDataTest renders
        // these pages too, but only ever in the default state, which is off.
        foreach ([false, true] as $live) {
            $live ? $this->walletOn() : $this->walletOff();
            $state = $live ? 'on' : 'off';

            foreach (['marketing.passes', 'marketing.ticketing'] as $name) {
                $blocks = $this->jsonLdBlocks($this->get(route($name))->assertOk()->getContent());
                $this->assertNotEmpty($blocks, "{$name} emits no JSON-LD at all");

                $features = [];
                foreach ($blocks as $i => $block) {
                    $this->assertIsArray($block, "JSON-LD block {$i} on {$name} did not decode with wallet {$state}");
                    $features = array_merge($features, $block['featureList'] ?? []);
                }

                $walletFeatures = array_filter(
                    $features,
                    fn ($feature) => str_contains(strtolower((string) $feature), 'google wallet')
                );

                $this->assertSame(
                    $live,
                    $walletFeatures !== [],
                    "{$name}'s featureList does not follow the gate with wallet {$state}"
                );
            }
        }
    }

    public function test_the_ticketing_door_side_swaps_its_wallet_row_rather_than_losing_one(): void
    {
        // The sale side and the door side are drawn as the two halves of one turnstile, ten rows
        // each (see the comment above $saleSide), so a ragged column reads as a broken machine.
        $row = '<dt class="es-turn-ink text-sm font-bold">';

        $this->walletOff();
        $off = $this->get(route('marketing.ticketing'))->assertOk()->getContent();

        $this->walletOn();
        $on = $this->get(route('marketing.ticketing'))->assertOk()->getContent();

        $this->assertSame(substr_count($on, $row), substr_count($off, $row));
        $this->assertStringContainsString('Free registrations too', $off);
        $this->assertStringNotContainsString('Free registrations too', $on);
    }

    public function test_the_white_label_count_follows_the_gate(): void
    {
        // Section 03 counts what stays of our branding. The pass-logo fallback is one of those
        // things only on an install that can issue a pass at all.
        $this->walletOff();
        $this->get(route('marketing.white_label'))->assertOk()
            ->assertSee('Here are all three')
            ->assertDontSee('Here are all four');

        $this->walletOn();
        $this->get(route('marketing.white_label'))->assertOk()
            ->assertSee('Here are all four')
            ->assertDontSee('Here are all three');
    }

    private function walletOff(): void
    {
        // Explicit, so a developer's own .env cannot switch the feature on under the test.
        config([
            'services.google.wallet_issuer_id' => null,
            'services.google.wallet_service_account' => null,
        ]);
    }

    private function walletOn(): void
    {
        // isConfigured() only checks that the key decodes and names both fields. Nothing here
        // signs anything, so no real RSA key is needed to render the marketing pages.
        config([
            'services.google.wallet_issuer_id' => '3388000000012345678',
            'services.google.wallet_service_account' => base64_encode(json_encode([
                'client_email' => 'wallet@example-project.iam.gserviceaccount.com',
                'private_key' => 'not-a-real-key',
            ])),
        ]);
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
