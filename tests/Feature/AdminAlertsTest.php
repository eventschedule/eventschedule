<?php

namespace Tests\Feature;

use App\Models\FederatedInstance;
use App\Models\TranslationSuggestion;
use App\Models\User;
use App\Services\AdminAlertService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The "Needs attention" panel on /admin/dashboard and the matching nav badges.
 * Before this existed, a queue like federation approvals or translation review sat
 * unnoticed until someone happened to open its page.
 *
 * Note phpunit pins IS_NEXUS=true and leaves IS_HOSTED unset, so the nexus-only
 * queues are live here and the hosted-only ones are not.
 */
class AdminAlertsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function adminActing(User $admin)
    {
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin);
    }

    private function makeInstance(array $attributes = []): FederatedInstance
    {
        return FederatedInstance::create(array_merge([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://operator.test',
            'name' => 'Operator',
            'contact_email' => 'ops@operator.test',
            'secret' => str_repeat('a', 40),
            'status' => FederatedInstance::STATUS_PENDING,
        ], $attributes));
    }

    private function makeSuggestion(array $overrides = []): TranslationSuggestion
    {
        return TranslationSuggestion::create(array_merge([
            'instance_id' => (string) Str::uuid(),
            'locale' => 'fr',
            'group' => 'messages',
            'key' => 'home',
            'suggested_value' => 'Maison',
            'shipped_value' => 'Accueil',
            'app_version' => 'v1.0.118',
            'status' => TranslationSuggestion::STATUS_PENDING,
        ], $overrides));
    }

    /**
     * Seed a subscription row directly: Cashier's model is not used for reads anywhere in the
     * alert path, and the four price IDs are what the assertion is about.
     */
    private function makeSubscription(string $priceId, string $status = 'active'): void
    {
        $role = $this->createRole($this->createOwner());

        DB::table('subscriptions')->insert([
            'role_id' => $role->id,
            'type' => 'default',
            'stripe_id' => 'sub_'.Str::random(14),
            'stripe_status' => $status,
            'stripe_price' => $priceId,
            'quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function configureCurrentPrices(): void
    {
        config([
            'services.stripe_platform.price_monthly' => 'price_current_pro_monthly',
            'services.stripe_platform.price_yearly' => 'price_current_pro_yearly',
            'services.stripe_platform.enterprise_price_monthly' => 'price_current_ent_monthly',
            'services.stripe_platform.enterprise_price_yearly' => 'price_current_ent_yearly',
        ]);
    }

    /**
     * The row exists because PlanPriceUtils resolves a tier ONLY by exact match against the four
     * configured price IDs. A live subscription on any other ID keeps being charged while
     * hasActiveEnterpriseSubscription() returns false and ARR counts it at zero, and before this
     * row nothing but a Log::warning said so.
     */
    public function test_a_live_subscription_on_a_retired_price_is_surfaced(): void
    {
        $this->createOwner(true);
        $this->configureCurrentPrices();

        $this->makeSubscription('price_current_pro_monthly');
        $this->makeSubscription('price_retired_from_2024');

        AdminAlertService::flush();

        $row = AdminAlertService::items()->firstWhere('type', 'subscriptions_unrecognized');

        $this->assertNotNull($row, 'A subscription on an unconfigured price must raise the alert.');
        $this->assertSame(1, $row['count'], 'Only the retired price counts; the configured one is fine.');
        $this->assertSame('red', $row['color']);
        $this->assertStringContainsString('#unrecognized-subscriptions', $row['url'],
            'The row must land on its own panel, not the amount-mismatch sales table.');
    }

    /**
     * Cancelled means nothing is being charged and no tier is being withdrawn, so it is not the
     * condition this row is about. past_due IS, because Stripe is still retrying and the role
     * keeps access through the dunning window.
     */
    public function test_a_cancelled_subscription_on_a_retired_price_is_ignored(): void
    {
        $this->createOwner(true);
        $this->configureCurrentPrices();

        $this->makeSubscription('price_retired_from_2024', 'canceled');

        AdminAlertService::flush();

        $this->assertNull(AdminAlertService::items()->firstWhere('type', 'subscriptions_unrecognized'));
    }

    public function test_a_past_due_subscription_on_a_retired_price_is_surfaced(): void
    {
        $this->createOwner(true);
        $this->configureCurrentPrices();

        $this->makeSubscription('price_retired_from_2024', 'past_due');

        AdminAlertService::flush();

        $this->assertSame(1, AdminAlertService::items()->firstWhere('type', 'subscriptions_unrecognized')['count']);
    }

    /**
     * An install that sells no plans has no configured price IDs, and counting every
     * subscription as stranded there would badge a selfhost install permanently.
     */
    public function test_no_configured_prices_means_no_alert(): void
    {
        $this->createOwner(true);

        config([
            'services.stripe_platform.price_monthly' => null,
            'services.stripe_platform.price_yearly' => null,
            'services.stripe_platform.enterprise_price_monthly' => null,
            'services.stripe_platform.enterprise_price_yearly' => null,
        ]);

        $this->makeSubscription('price_whatever');

        AdminAlertService::flush();

        $this->assertNull(AdminAlertService::items()->firstWhere('type', 'subscriptions_unrecognized'));
    }

    /**
     * The panel the alert links to, rendered with a row in it.
     *
     * The existing revenue-page tests only ever render it EMPTY - with no stranded subscription
     * the @if is false and the whole block is skipped - so a mistake inside the loop would
     * surface for the first time on production, on the day the alert fires. This is the only
     * test that compiles that markup.
     */
    public function test_the_revenue_page_lists_the_unrecognized_subscriptions(): void
    {
        $admin = $this->createOwner(true);
        $this->configureCurrentPrices();

        $this->makeSubscription('price_current_pro_monthly');
        $this->makeSubscription('price_retired_from_2024', 'past_due');

        $html = $this->adminActing($admin)->get(route('admin.revenue'))->assertOk()->getContent();

        $this->assertStringContainsString('id="unrecognized-subscriptions"', $html,
            'The alert links to this anchor, so the panel has to carry it.');
        $this->assertStringContainsString('price_retired_from_2024', $html);
        $this->assertStringNotContainsString('price_current_pro_monthly', $html,
            'A subscription on a configured price is not stranded and must not be listed.');
    }

    public function test_nothing_pending_renders_no_panel_and_no_badges(): void
    {
        $admin = $this->createOwner(true);

        $this->adminActing($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee(__('messages.needs_attention'));

        $this->assertTrue(AdminAlertService::items()->isEmpty());
        $this->assertSame([], AdminAlertService::badges()['nav']);
    }

    public function test_pending_federation_instances_surface_on_the_dashboard(): void
    {
        $admin = $this->createOwner(true);
        $this->makeInstance();
        $this->makeInstance(['instance_id' => (string) Str::uuid(), 'site_url' => 'https://two.test']);
        // Approved instances are done with; they must not be counted.
        $this->makeInstance([
            'instance_id' => (string) Str::uuid(),
            'site_url' => 'https://three.test',
            'status' => FederatedInstance::STATUS_APPROVED,
        ]);

        $this->adminActing($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(__('messages.needs_attention'))
            ->assertSee(trans_choice('messages.admin_alert_federation', 2, ['count' => 2]))
            ->assertSee(route('admin.federation'));

        $this->assertSame(2, AdminAlertService::badges()['tab']['federation']['count']);
    }

    public function test_pending_translation_suggestions_surface_on_the_dashboard(): void
    {
        $admin = $this->createOwner(true);
        $this->makeSuggestion();
        $this->makeSuggestion(['key' => 'about', 'status' => TranslationSuggestion::STATUS_APPROVED]);

        $this->adminActing($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(trans_choice('messages.admin_alert_translation_suggestions', 1, ['count' => 1]))
            ->assertSee(route('admin.translations.suggestions'));

        $this->assertSame(1, AdminAlertService::badges()['tab']['translations']['count']);
    }

    public function test_failed_jobs_surface_on_the_dashboard(): void
    {
        $admin = $this->createOwner(true);

        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'boom',
            'failed_at' => now(),
        ]);

        $this->adminActing($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(trans_choice('messages.admin_alert_jobs_failed', 1, ['count' => 1]))
            ->assertSee(route('admin.queue'));

        $this->assertSame(1, AdminAlertService::badges()['tab']['queue']['count']);
    }

    public function test_rows_are_ordered_with_breakage_before_review_queues(): void
    {
        $this->createOwner(true);

        $this->makeInstance();
        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'boom',
            'failed_at' => now(),
        ]);

        $types = AdminAlertService::items()->pluck('type')->all();

        $this->assertSame(['jobs_failed', 'federation'], $types);
    }

    public function test_an_approved_instance_that_changed_address_is_surfaced(): void
    {
        $admin = $this->createOwner(true);

        // ApiFederationController's sync path flags a moved host without downgrading
        // the status, so the federation queue's default status=pending filter hides it.
        $this->makeInstance([
            'status' => FederatedInstance::STATUS_APPROVED,
            'flagged_at' => now(),
        ]);

        $this->adminActing($admin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee(trans_choice('messages.admin_alert_federation_flagged', 1, ['count' => 1]))
            ->assertSee(route('admin.federation', ['status' => 'approved']));

        $badge = AdminAlertService::badges()['tab']['federation'];
        $this->assertSame(1, $badge['count']);
        $this->assertSame('red', $badge['color']);
    }

    public function test_a_flagged_suspended_instance_is_not_resurfaced(): void
    {
        $this->createOwner(true);

        // Re-surfacing a suspended instance would hand it an escape from moderation.
        $this->makeInstance([
            'status' => FederatedInstance::STATUS_SUSPENDED,
            'flagged_at' => now(),
        ]);

        $this->assertNotContains('federation_flagged', AdminAlertService::items()->pluck('type')->all());
    }

    public function test_nav_badges_take_the_highest_severity_in_their_group(): void
    {
        $this->createOwner(true);

        // Pending federation registrations are amber, not red: nothing is broken.
        $this->makeInstance();

        $this->assertSame('amber', AdminAlertService::badges()['nav']['system']['color']);

        // A failed job in the same dropdown escalates the whole group to red.
        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'boom',
            'failed_at' => now(),
        ]);
        AdminAlertService::flush();

        $this->assertSame('red', AdminAlertService::badges()['nav']['system']['color']);
    }

    public function test_nexus_only_queues_are_absent_off_the_nexus(): void
    {
        $this->createOwner(true);

        $this->makeInstance();
        $this->makeSuggestion();

        config(['app.is_nexus' => false]);
        AdminAlertService::flush();

        $types = AdminAlertService::items()->pluck('type')->all();

        $this->assertNotContains('federation', $types);
        $this->assertNotContains('translation_suggestions', $types);
    }

    /**
     * The panel is for queues waiting on a site admin. An unverified schedule is
     * waiting on its owner and never drains, so it lives on /admin/schedules as a stat
     * instead - see AdminSchedulesUnverifiedCountTest.
     */
    public function test_unverified_schedules_are_not_an_alert(): void
    {
        $owner = $this->createOwner(true);
        $this->createRole($owner, 'venue', ['email_verified_at' => null]);

        config(['app.hosted' => true]);
        AdminAlertService::flush();

        $types = AdminAlertService::items()->pluck('type')->all();

        $this->assertNotContains('schedules_unverified', $types);
        $this->assertArrayNotHasKey('schedules', AdminAlertService::badges()['tab']);
    }

    public function test_hosted_only_queues_are_absent_on_a_selfhosted_install(): void
    {
        $this->createOwner(true);

        config(['app.hosted' => false]);
        AdminAlertService::flush();

        $types = AdminAlertService::items()->pluck('type')->all();

        $this->assertNotContains('support_unread', $types);
        $this->assertNotContains('domains_pending', $types);
        $this->assertNotContains('domains_failed', $types);
    }

    public function test_every_row_resolves_to_a_real_url(): void
    {
        $this->createOwner(true);

        $this->makeInstance();
        $this->makeSuggestion();
        DB::table('failed_jobs')->insert([
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => '{}',
            'exception' => 'boom',
            'failed_at' => now(),
        ]);

        $items = AdminAlertService::items();

        $this->assertNotEmpty($items);

        foreach ($items as $item) {
            $this->assertNotEmpty($item['url'], "Row {$item['type']} has no url");
            $this->assertStringContainsString('/admin/', $item['url']);
            $this->assertNotEmpty($item['title']);
            // A missing lang key would render as the raw key.
            $this->assertStringNotContainsString('messages.admin_alert_', $item['title']);
        }
    }

    public function test_non_admins_cannot_reach_the_admin_dashboard(): void
    {
        $user = $this->createOwner();

        $this->actingAs($user)->get(route('admin.dashboard'))
            ->assertRedirect();
    }
}
