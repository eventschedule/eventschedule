<?php

namespace Tests\Feature;

use App\Mail\BackupExportComplete;
use App\Mail\BackupImportComplete;
use App\Mail\BoostBudgetAlert;
use App\Mail\BoostCompleted;
use App\Mail\BoostCreated;
use App\Mail\BoostRejected;
use App\Mail\PromotionDecision;
use App\Mail\ReferralCreditEarned;
use App\Mail\SetPassword;
use App\Mail\SubscriptionPaymentFailed;
use App\Mail\SubscriptionRenewal;
use App\Mail\SubscriptionTrialEnding;
use App\Models\BoostCampaign;
use App\Models\Referral;
use App\Services\PromotionBillingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Mailable;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The account, billing and platform mails that no other test renders. Each is built on
 * <x-email.layout>, so a bad variable inside a component would otherwise surface only as a failed
 * queued job. Rendered in English and in an RTL locale, and held to the layout's promises: one <h1>,
 * every translation key resolved, and well under Gmail's 102KB clip.
 */
class EmailAccountRenderTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    private function assertRendersWell(Mailable $mail, string $label): void
    {
        foreach (['en', 'he'] as $locale) {
            app()->setLocale($locale);
            $html = $mail->render();

            $this->assertSame(1, substr_count($html, '<h1'), "{$label} ({$locale}) must have exactly one <h1>");
            $this->assertStringNotContainsString('messages.', $html, "{$label} ({$locale}) has an unresolved key");
            $this->assertLessThan(80 * 1024, strlen($html), "{$label} ({$locale}) is too large");
            $this->assertSame($locale === 'he', str_contains($html, 'dir="rtl"'), "{$label} ({$locale}) direction");
        }
        app()->setLocale('en');
    }

    private function campaign(array $attrs = []): BoostCampaign
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['name' => 'Jazz Night']);

        $campaign = BoostCampaign::create(array_merge([
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'channel' => 'network',
            'name' => 'Promo',
            'status' => 'active',
            'moderation_status' => 'approved',
            'billing_status' => 'charged',
            'user_budget' => 100,
            'total_charged' => 100,
            'pricing_model' => 'cpm',
            'unit_rate_micros' => PromotionBillingService::toMicros(2.00),
            'budget_micros' => PromotionBillingService::toMicros(100),
        ], $attrs));
        $campaign->forceFill(['markup_rate' => 0, 'impressions' => 1200, 'reach' => 900, 'clicks' => 40, 'actual_spend' => 60, 'conversions' => 3])->save();

        return $campaign->fresh();
    }

    public function test_set_password_renders(): void
    {
        $this->assertRendersWell(new SetPassword(url('/reset-password/abc'), 'sam@example.com'), 'SetPassword');
    }

    public function test_subscription_billing_mails_render(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'The Blue Note']);

        $this->assertRendersWell(new SubscriptionPaymentFailed($role), 'SubscriptionPaymentFailed');
        $this->assertRendersWell(new SubscriptionRenewal($role, '$15.00', 'Pro', 'October 15, 2026', true), 'SubscriptionRenewal');
        $this->assertRendersWell(new SubscriptionRenewal($role, '$15.00', 'Pro', 'October 15, 2026', false), 'SubscriptionRenewal (no card)');
        $this->assertRendersWell(new SubscriptionTrialEnding($role, '$15.00', 'Pro', 'October 15, 2026', true), 'SubscriptionTrialEnding');
        $this->assertRendersWell(new SubscriptionTrialEnding($role, '$15.00', 'Pro', 'October 15, 2026', false, true), 'SubscriptionTrialEnding (wind-down)');
    }

    public function test_referral_credit_renders(): void
    {
        $referral = Referral::create([
            'referrer_user_id' => $this->createOwner()->id,
            'referred_user_id' => $this->createOwner()->id,
            'plan_type' => 'pro',
            'status' => 'credited',
        ]);

        $this->assertRendersWell(new ReferralCreditEarned($referral->fresh()), 'ReferralCreditEarned');
    }

    public function test_boost_and_promotion_mails_render(): void
    {
        $this->assertRendersWell(new BoostBudgetAlert($this->campaign()), 'BoostBudgetAlert');
        $this->assertRendersWell(new BoostCompleted($this->campaign(['billing_status' => 'partially_refunded'])), 'BoostCompleted');
        $this->assertRendersWell(new BoostCreated($this->campaign(['scheduled_end' => now()->addDays(14)])), 'BoostCreated');
        $this->assertRendersWell(new BoostRejected($this->campaign(['meta_rejection_reason' => 'Too much text.']), true), 'BoostRejected (refunded)');
        $this->assertRendersWell(new BoostRejected($this->campaign(), false), 'BoostRejected (refund pending)');
        $this->assertRendersWell(new PromotionDecision($this->campaign(), true), 'PromotionDecision (approved)');
        $this->assertRendersWell(new PromotionDecision($this->campaign(['moderation_notes' => 'Wrong event.']), false), 'PromotionDecision (rejected)');
    }

    public function test_backup_mails_render(): void
    {
        $this->assertRendersWell(new BackupExportComplete(url('/backup/download/abc'), ['The Blue Note', 'Jazz Collective'], now()->addDays(7)), 'BackupExportComplete');

        $this->assertRendersWell(new BackupImportComplete([
            [
                'name' => 'The Blue Note',
                'subdomain' => 'bluenote',
                'schedules' => ['success' => 1, 'failed' => 0],
                'events' => ['success' => 42, 'failed' => 12, 'failures' => array_map(fn ($i) => "Event {$i} failed", range(1, 12))],
            ],
            ['name' => 'Jazz Collective', 'error' => 'The backup file could not be read.'],
        ]), 'BackupImportComplete');
    }
}
