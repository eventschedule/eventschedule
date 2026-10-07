<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use App\Utils\PlatformPricing;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Each kind of install sees the /admin it can act on.
 *
 * A plain selfhost (IS_HOSTED=false) has no plans, no subscriptions and no boost billing: every
 * schedule is enterprise whatever roles.plan_type says (Role::actualPlanTier()), and a boost
 * charges nothing. So the plan cards, columns and forms, the plan funnel stages, and the boost
 * markup, credit, limit and billing cards are hosted only, and the actions behind the forms 404.
 *
 * Marketing visitors are counted on the nexus alone, the one install with a marketing site, so
 * the visitor parts of the Users funnel and the Realtime "cached marketing pages" copy are
 * nexus only. Both halves are asserted, so a gate that hides a section everywhere fails too.
 */
class AdminInstallKindTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $admin;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = $this->createOwner(true);
        $this->role = $this->createRole($this->admin);
    }

    public function test_a_plain_selfhost_sees_no_billing_or_marketing_ui(): void
    {
        config(['app.hosted' => false, 'app.is_nexus' => false]);

        $this->page('/admin/dashboard')
            ->assertDontSee(__('messages.admin_dash_outside_stripe'))
            ->assertDontSee(__('messages.boost_markup_revenue'));

        $this->page('/admin/users')
            ->assertDontSee(__('messages.funnel_stage_hit_ticket_paywall'))
            ->assertDontSee(__('messages.funnel_stage_subscribed'))
            ->assertDontSee(__('messages.funnel_stage_visited'))
            ->assertDontSee(__('messages.funnel_visitor_to_event'))
            ->assertSee(__('messages.funnel_onboarding_subtitle_signup'));

        $this->page('/admin/usage')->assertDontSee('data-col="plan"', false);

        $this->page('/admin/schedules')
            ->assertDontSee('name="plan_type"', false)
            ->assertDontSee('name="source"', false)
            ->assertDontSee('value="trial"', false)
            ->assertDontSee(__('messages.stripe_paid'))
            ->assertSee(__('messages.unverified'));

        $this->page(route('admin.schedules.edit', ['role' => UrlUtils::encodeId($this->role->id)]))
            ->assertDontSee(__('messages.current_subscription_status'))
            ->assertDontSee('id="plan_expires"', false);

        $this->page('/admin/boost')
            ->assertDontSee(__('messages.markup_revenue'))
            ->assertDontSee(__('messages.grant_boost_credit'))
            ->assertDontSee(__('messages.set_spending_limit'))
            ->assertDontSee(__('messages.recent_billing_records'))
            ->assertDontSee(__('messages.revenue_trend'))
            ->assertSee(__('messages.total_ad_spend'));

        $this->page('/admin/newsletter-segments')->assertDontSee('value="plan_tier"', false);

        $this->page('/admin/realtime')->assertSee(__('messages.realtime_waiting_body_app'));
    }

    public function test_a_plain_selfhost_refuses_the_billing_actions(): void
    {
        config(['app.hosted' => false, 'app.is_nexus' => false]);

        $this->admin()->put(route('admin.schedules.update', ['role' => UrlUtils::encodeId($this->role->id)]), [
            'plan_type' => 'pro',
            'plan_term' => 'year',
            'plan_expires' => now()->addYear()->format('Y-m-d'),
        ])->assertNotFound();

        $this->admin()->post('/admin/boost/grant-credit', ['subdomain' => $this->role->subdomain, 'amount' => 50])
            ->assertNotFound();
        $this->admin()->post('/admin/boost/set-limit', ['subdomain' => $this->role->subdomain, 'amount' => 50])
            ->assertNotFound();

        $this->admin()->post('/admin/newsletter-segments', ['name' => 'Free plans', 'type' => 'plan_tier'])
            ->assertSessionHasErrors('type');

        $this->admin()->post(route('admin.settings.update_plan_pricing'), ['plan_price_pro_monthly' => '149'])
            ->assertRedirect(route('admin.settings'))
            ->assertSessionHas('error');
        $this->assertNull(PlatformPricing::stored('pro', 'monthly'));

        $this->role->refresh();
        $this->assertNotSame('pro', $this->role->plan_type);
        $this->assertSame(0.0, (float) $this->role->boost_credit);
        $this->assertNull($this->role->boost_max_budget);
    }

    /** The other half: the same sections still render where they apply. */
    public function test_the_nexus_sees_all_of_it(): void
    {
        config(['app.hosted' => true, 'app.is_nexus' => true]);

        $this->page('/admin/dashboard')
            ->assertSee(__('messages.admin_dash_outside_stripe'))
            ->assertSee(__('messages.boost_markup_revenue'));

        $this->page('/admin/users')
            ->assertSee(__('messages.funnel_stage_subscribed'))
            ->assertSee(__('messages.funnel_stage_visited'))
            ->assertSee(__('messages.funnel_visitor_to_event'))
            ->assertSee(__('messages.funnel_onboarding_subtitle'));

        $this->page('/admin/schedules')
            ->assertSee('name="plan_type"', false)
            ->assertSee('name="source"', false)
            ->assertSee(__('messages.stripe_paid'));

        $this->page(route('admin.schedules.edit', ['role' => UrlUtils::encodeId($this->role->id)]))
            ->assertSee(__('messages.current_subscription_status'))
            ->assertSee('id="plan_expires"', false);

        $this->page('/admin/boost')
            ->assertSee(__('messages.grant_boost_credit'))
            ->assertSee(__('messages.set_spending_limit'))
            ->assertSee(__('messages.recent_billing_records'))
            ->assertSee(__('messages.revenue_trend'));

        $this->page('/admin/newsletter-segments')->assertSee('value="plan_tier"', false);

        $this->page('/admin/realtime')->assertSee(__('messages.realtime_waiting_body'));
    }

    /**
     * A selfhosted SaaS has plans of its own, so it keeps the billing UI, but no marketing site,
     * so it loses the visitor parts.
     */
    public function test_a_selfhosted_saas_keeps_plans_but_not_marketing_visitors(): void
    {
        config(['app.hosted' => true, 'app.is_nexus' => false]);

        $this->page('/admin/users')
            ->assertSee(__('messages.funnel_stage_subscribed'))
            ->assertDontSee(__('messages.funnel_stage_visited'))
            ->assertDontSee(__('messages.funnel_visitor_to_event'));

        $this->page('/admin/schedules')->assertSee('name="plan_type"', false);

        $this->page('/admin/realtime')->assertSee(__('messages.realtime_waiting_body_app'));
    }

    private function admin(): self
    {
        // EnsureUserIsAdmin gates every /admin route on a confirmed password this session.
        return $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($this->admin);
    }

    private function page(string $url)
    {
        return $this->admin()->get($url)->assertOk();
    }
}
