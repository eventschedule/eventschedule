<?php

namespace Tests\Feature;

use App\Mail\PromotionDecision;
use App\Models\BoostCampaign;
use App\Models\Role;
use App\Models\Setting;
use App\Services\PromotionBillingService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Buying and moderating an on-network promotion.
 *
 * The purchase deliberately does not share BoostController's endpoints - that path hardcodes
 * the Meta markup rate, so reusing it would charge the card 1.2x the quoted price and flag
 * every single network purchase as an amount mismatch.
 */
class PromotionPurchaseTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Selfhost/testing settles payment without Stripe, which is what makes the purchase
        // path testable at all.
        // The promotions network is hosted-only (PromotionService::isEnabled). is_testing keeps
        // settlement on the free branch so these assert behaviour, not Stripe.
        config(['app.hosted' => true, 'app.is_testing' => true, 'ads.enabled' => true, 'app.is_nexus' => false]);
        Setting::set('ads_native_enabled', '1');
        Cache::flush();
    }

    /**
     * An established advertiser.
     *
     * boost_max_budget is set explicitly because getBoostMaxBudget() defaults to $10 on hosted
     * (services.meta.boost_default_limit) - the deliberately low starting limit for a brand-new
     * schedule. Tests that are about the limit itself override it via hostedWithTrustLimit().
     */
    private function advertiser(): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent');
        $role->forceFill(['boost_max_budget' => 1000])->save();
        $event = $this->createEvent($role, ['name' => 'My Show', 'starts_at' => now()->addDays(20)]);
        $role->events()->updateExistingPivot($event->id, ['is_accepted' => true]);

        return [$owner, $role->fresh(), $event];
    }

    private function payload(Role $role, $event, array $overrides = []): array
    {
        return array_merge([
            'event_id' => UrlUtils::encodeId($event->id),
            'role_id' => UrlUtils::encodeId($role->id),
            'budget' => 25,
            'pricing_model' => 'cpm',
            'headline' => 'Come to my show',
        ], $overrides);
    }

    /**
     * The role being billed must belong to the event AND to the caller.
     *
     * `canEditEvent()` authorizes the event, not the role, so before this was fixed an attacker
     * could pair an event they own with any Pro schedule's id and have settlePayment() debit that
     * schedule's admin-granted boost_credit. Role ids travel as reversible `UrlUtils::encodeId()`
     * hashes, not secrets, so they are trivially obtainable from a victim's own public pages.
     */
    public function test_a_user_cannot_charge_a_schedule_they_do_not_belong_to(): void
    {
        [$attacker, , $attackerEvent] = $this->advertiser();

        $victim = $this->createRole($this->createOwner(), 'venue');
        $victim->forceFill(['boost_credit' => 500])->save();

        $this->actingAs($attacker)
            ->post(route('promotions.store'), $this->payload($victim, $attackerEvent))
            ->assertForbidden();

        $this->assertSame(0, BoostCampaign::network()->count());
        $this->assertSame(500.0, (float) $victim->fresh()->boost_credit, 'The victim\'s wallet must be untouched.');
    }

    public function test_the_payment_intent_endpoint_has_the_same_guard(): void
    {
        [$attacker, , $attackerEvent] = $this->advertiser();
        $victim = $this->createRole($this->createOwner(), 'venue');

        $this->actingAs($attacker)
            ->post(route('promotions.payment_intent'), [
                'event_id' => UrlUtils::encodeId($attackerEvent->id),
                'role_id' => UrlUtils::encodeId($victim->id),
                'budget' => 25,
            ])
            ->assertForbidden();
    }

    public function test_a_role_the_caller_owns_but_that_is_not_on_the_event_is_refused(): void
    {
        // Being a member is not enough - the schedule must actually be on the event, or the
        // campaign would be billed to and bylined with an unrelated schedule of the caller's.
        [$owner, , $event] = $this->advertiser();
        $otherRole = $this->createRole($owner, 'venue');

        $this->actingAs($owner)
            ->post(route('promotions.store'), $this->payload($otherRole, $event))
            ->assertForbidden();
    }

    /**
     * getBoostMaxBudget() is a hosted-only anti-fraud control - off-hosted it returns the global
     * config maximum - so these two must run hosted. is_testing keeps settlement on the free
     * branch so the assertion is about the cap, not about Stripe.
     */
    private function hostedWithTrustLimit(float $limit): array
    {
        config(['app.hosted' => true, 'app.is_testing' => true]);

        [$owner, $role, $event] = $this->advertiser();
        $role->forceFill(['boost_max_budget' => $limit])->save();

        return [$owner, $role->fresh(), $event];
    }

    public function test_the_per_schedule_trust_limit_is_enforced_on_submit(): void
    {
        // create() only uses getBoostMaxBudget() for the input's max attribute; a direct POST was
        // bounded solely by the global config maximum.
        [$owner, $role, $event] = $this->hostedWithTrustLimit(10);

        $this->actingAs($owner)
            ->post(route('promotions.store'), $this->payload($role, $event, ['budget' => 900]))
            ->assertSessionHas('error');

        $this->assertSame(0, BoostCampaign::network()->count());
    }

    public function test_a_budget_within_the_trust_limit_is_accepted(): void
    {
        [$owner, $role, $event] = $this->hostedWithTrustLimit(100);

        $this->actingAs($owner)->post(route('promotions.store'), $this->payload($role, $event, ['budget' => 25]));

        $this->assertSame(1, BoostCampaign::network()->count());
    }

    /**
     * The buy form mounts Vue with the in-DOM compiler, so a server-rendered text node inside it
     * has its mustaches compiled and executed. Blade escapes <>&"' but not braces, so a schedule
     * name is a template-injection sink without v-pre.
     */
    public function test_the_schedule_name_is_not_compiled_as_a_vue_template(): void
    {
        [$owner, $role, $event] = $this->advertiser();
        $role->update(['name' => "{{constructor.constructor('window.__pwned=1')()}}"]);

        $response = $this->actingAs($owner)->get(route('promotions.create', [
            'event_id' => UrlUtils::encodeId($event->id),
            'role_id' => UrlUtils::encodeId($role->id),
        ]));

        $response->assertOk();

        // The payload still contains the name, but inside a v-pre subtree Vue will not compile.
        $html = $response->getContent();
        $this->assertMatchesRegularExpression(
            '/<span[^>]*\bv-pre\b[^>]*>\s*\{\{constructor/',
            $html,
            'A user-controlled schedule name inside the Vue mount must carry v-pre.'
        );
    }

    /**
     * The hosted purchase path must actually exist in the form.
     *
     * This is the regression that mattered most: the view shipped as a plain POST form with no
     * Stripe.js, no Payment Element and no payment_intent_id field, so promotions.payment_intent
     * was unreachable and every card purchase on hosted fell straight to "payment failed". Every
     * purchase test ran with app.hosted => false, which settles for free, so nothing caught it.
     */
    /**
     * In hosted mode the admin portal lives on the app.<domain> host and anything else is
     * redirected there. is_testing normally short-circuits that, but these tests need
     * is_testing off to exercise the real card path, so they address the app host directly.
     */
    private function appUrl(string $url): string
    {
        return preg_replace('~^(https?://)~', '$1app.', $url, 1);
    }

    public function test_the_buy_form_renders_the_card_payment_path_on_hosted(): void
    {
        // The card block is gated on a configured publisher key as well as the mode, so pin one:
        // STRIPE_PLATFORM_KEY is set in a real hosted .env but empty in CI's .env.example.
        config([
            'app.hosted' => true,
            'app.is_testing' => false,
            'services.stripe_platform.key' => 'pk_test_x',
        ]);

        [$owner, $role, $event] = $this->advertiser();
        $owner->forceFill(['phone_verified_at' => now()])->save();

        $response = $this->actingAs($owner)->get($this->appUrl(route('promotions.create', [
            'event_id' => UrlUtils::encodeId($event->id),
            'role_id' => UrlUtils::encodeId($role->id),
        ])));

        $response->assertOk()
            ->assertSee('js.stripe.com/v3', false)
            ->assertSee('promo-payment-element', false)
            ->assertSee('name="payment_intent_id"', false)
            // The route is emitted through @json, which escapes the slashes, so match the
            // distinctive path segment rather than the full URL.
            ->assertSee('payment-intent', false);
    }

    public function test_the_buy_form_offers_country_targeting(): void
    {
        // The backend has always validated, stored and enforced visitor_countries; for a while
        // the form never rendered a control for it, so the only way to target a country was to
        // hand-craft the POST.
        [$owner, $role, $event] = $this->advertiser();

        $this->actingAs($owner)->get(route('promotions.create', [
            'event_id' => UrlUtils::encodeId($event->id),
            'role_id' => UrlUtils::encodeId($role->id),
        ]))->assertOk()
            ->assertSee('name="visitor_countries[]"', false)
            ->assertSee('South Africa', false);
    }

    public function test_the_buy_form_discloses_how_much_inventory_exists(): void
    {
        // The estimate is exact arithmetic on the budget, which on its own would quote
        // "500,000 views" to a buyer on an instance that serves a handful a day. The
        // inventory line is the part that makes the quote honest.
        [$owner, $role, $event] = $this->advertiser();

        $this->actingAs($owner)->get(route('promotions.create', [
            'event_id' => UrlUtils::encodeId($event->id),
            'role_id' => UrlUtils::encodeId($role->id),
        ]))->assertOk()
            ->assertSee('inventoryNote', false)
            // No analytics rows exist in this test, so the zero-inventory warning is what
            // should be reachable - the advertiser must not be told nothing at all.
            ->assertSee(__('messages.promotion_inventory_none'), false);
    }

    public function test_country_targeting_is_stored_from_the_form(): void
    {
        [$owner, $role, $event] = $this->advertiser();

        $this->actingAs($owner)->post(route('promotions.store'),
            $this->payload($role, $event, ['visitor_countries' => ['ZA', 'NA']]));

        $this->assertSame(['ZA', 'NA'], BoostCampaign::first()->network_targeting['visitor_countries']);
    }

    public function test_an_unrecognised_country_code_is_rejected(): void
    {
        // Not a harmless no-op: matchesTargeting() excludes anything it cannot match, so a
        // typo would silently make the campaign undeliverable after the advertiser had paid.
        [$owner, $role, $event] = $this->advertiser();

        $this->actingAs($owner)
            // XX is absent from countries.json (ZZ is not - it is a real entry, "Unknown Region").
            ->post(route('promotions.store'), $this->payload($role, $event, ['visitor_countries' => ['XX']]))
            ->assertSessionHasErrors('visitor_countries.0');

        $this->assertSame(0, BoostCampaign::network()->count());
    }

    public function test_a_schedule_that_has_not_accepted_the_event_cannot_buy(): void
    {
        // candidates() requires event_role.is_accepted = true for the ADVERTISER's own role.
        // Nothing on the way in checked it - Event::roles() does not filter the pivot and
        // canEditEvent() ignores it - so a schedule added to someone else's event but not yet
        // accepted could pay, pass review, go active, and never appear in a single slot.
        [$owner, $role, $event] = $this->advertiser();

        $role->events()->updateExistingPivot($event->id, ['is_accepted' => null]);

        $this->actingAs($owner)->get(route('promotions.create', [
            'event_id' => UrlUtils::encodeId($event->id),
            'role_id' => UrlUtils::encodeId($role->id),
        ]))->assertForbidden();

        $this->actingAs($owner)->post(route('promotions.store'), $this->payload($role, $event))
            ->assertForbidden();

        $this->assertSame(0, BoostCampaign::network()->count());
    }

    public function test_the_nexus_cannot_sell_inventory_it_can_never_serve(): void
    {
        // Role::showAds() returns false on the nexus, so eventschedule.com can never render a
        // promotion. Selling has to be gated on the same condition, or a schedule prepays, goes
        // active, never serves, and - with no scheduled_end - never completes or refunds either.
        config(['app.is_nexus' => true]);

        [$owner, $role, $event] = $this->advertiser();

        $this->actingAs($owner)->get(route('promotions.create', [
            'event_id' => UrlUtils::encodeId($event->id),
            'role_id' => UrlUtils::encodeId($role->id),
        ]))->assertNotFound();

        $this->actingAs($owner)->post(route('promotions.store'), $this->payload($role, $event))
            ->assertNotFound();

        $this->assertSame(0, BoostCampaign::network()->count());
    }

    public function test_an_unverified_phone_is_stopped_before_the_card_is_charged(): void
    {
        // The form confirms the card BEFORE it submits, so a gate that only exists in store()
        // means the advertiser pays and is then bounced with the money captured and no campaign.
        config(['app.hosted' => true, 'app.is_testing' => false]);

        [$owner, $role, $event] = $this->advertiser();
        $owner->forceFill(['phone_verified_at' => null])->save();

        $this->actingAs($owner)->get($this->appUrl(route('promotions.create', [
            'event_id' => UrlUtils::encodeId($event->id),
            'role_id' => UrlUtils::encodeId($role->id),
        ])))->assertRedirectContains('highlight=phone');
    }

    public function test_a_cpm_rate_too_small_to_charge_is_refused(): void
    {
        // impressionCostMicros() is intdiv($unit_rate_micros, 1000), so any CPM under $0.001
        // truncates to zero cost - the campaign would serve forever, never exhaust and never
        // refund. CPC uses the rate directly, so it is not subject to the same floor.
        [$owner, $role, $event] = $this->advertiser();

        Setting::set('ads_native_cpm', '0.0005');
        Cache::flush();

        $this->actingAs($owner)->post(route('promotions.store'),
            $this->payload($role, $event, ['pricing_model' => 'cpm']));

        $this->assertSame(0, BoostCampaign::network()->count());
    }

    public function test_the_card_payment_path_is_absent_where_no_card_is_needed(): void
    {
        // Selfhost/testing settles for free, so loading Stripe there would be a pointless
        // third-party request on a page that never charges anything.
        [$owner, $role, $event] = $this->advertiser();

        $this->actingAs($owner)->get(route('promotions.create', [
            'event_id' => UrlUtils::encodeId($event->id),
            'role_id' => UrlUtils::encodeId($role->id),
        ]))->assertOk()->assertDontSee('js.stripe.com', false);
    }

    public function test_a_hosted_purchase_without_a_confirmed_intent_is_refused(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => false]);

        [$owner, $role, $event] = $this->advertiser();
        $owner->forceFill(['phone_verified_at' => now()])->save();

        $this->actingAs($owner)->post($this->appUrl(route('promotions.store')), $this->payload($role, $event));

        // The campaign row is marked failed rather than left live and unpaid.
        $this->assertSame(0, BoostCampaign::network()->where('status', '!=', 'failed')->count());
    }

    public function test_a_hosted_purchase_covered_by_credit_needs_no_card(): void
    {
        config(['app.hosted' => true, 'app.is_testing' => false]);

        [$owner, $role, $event] = $this->advertiser();
        $owner->forceFill(['phone_verified_at' => now()])->save();
        $role->forceFill(['boost_credit' => 500])->save();

        $this->actingAs($owner)->post($this->appUrl(route('promotions.store')), $this->payload($role, $event));

        $campaign = BoostCampaign::network()->first();

        $this->assertNotNull($campaign);
        $this->assertSame('charged', $campaign->billing_status);
        $this->assertSame(475.0, (float) $role->fresh()->boost_credit, 'The $25 budget comes out of the wallet.');
    }

    public function test_a_pro_schedule_can_buy_a_promotion(): void
    {
        [$owner, $role, $event] = $this->advertiser();

        $this->actingAs($owner)
            ->post(route('promotions.store'), $this->payload($role, $event))
            ->assertRedirect();

        $campaign = BoostCampaign::network()->first();

        $this->assertNotNull($campaign);
        $this->assertSame('network', $campaign->channel);
        $this->assertSame('cpm', $campaign->pricing_model);
        $this->assertSame(PromotionBillingService::toMicros(25), (int) $campaign->budget_micros);
        // No external ad spend on this channel, so the whole charge is the operator's.
        $this->assertSame(0.0, (float) $campaign->markup_rate);
        $this->assertSame(25.0, (float) $campaign->getTotalCost());
    }

    public function test_a_new_advertiser_goes_to_the_review_queue(): void
    {
        [$owner, $role, $event] = $this->advertiser();

        $this->actingAs($owner)->post(route('promotions.store'), $this->payload($role, $event));

        $campaign = BoostCampaign::network()->first();

        $this->assertSame('pending', $campaign->moderation_status);
        $this->assertSame('pending_review', $campaign->status);
    }

    public function test_the_creative_falls_back_to_the_event_when_left_blank(): void
    {
        [$owner, $role, $event] = $this->advertiser();

        $this->actingAs($owner)->post(route('promotions.store'), $this->payload($role, $event, ['headline' => null]));

        $ad = BoostCampaign::network()->first()->ads->first();

        $this->assertSame('My Show', $ad->headline);
        // utm_source=boost is what makes conversion attribution work with no changes to
        // TicketController.
        $this->assertStringContainsString($event->getGuestUrl(false, null, true), $ad->destination_url);
    }

    public function test_a_free_schedule_cannot_buy_promotions(): void
    {
        config(['app.hosted' => true]);

        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', [
            'plan_type' => 'free',
            'plan_expires' => now()->subDay()->format('Y-m-d'),
        ]);
        $event = $this->createEvent($role, ['starts_at' => now()->addDays(5)]);

        $this->actingAs($owner)
            ->post(route('promotions.store'), $this->payload($role, $event))
            ->assertForbidden();
    }

    public function test_a_draft_event_cannot_be_promoted(): void
    {
        [$owner, $role, $event] = $this->advertiser();
        $event->update(['is_draft' => true]);

        $this->actingAs($owner)->post(route('promotions.store'), $this->payload($role, $event));

        $this->assertSame(0, BoostCampaign::network()->count(), 'Promoting a page nobody can open would just burn the budget.');
    }

    public function test_the_endpoint_is_hidden_when_the_network_is_off(): void
    {
        Setting::set('ads_native_enabled', '0');
        [$owner, $role, $event] = $this->advertiser();

        $this->actingAs($owner)
            ->post(route('promotions.store'), $this->payload($role, $event))
            ->assertNotFound();
    }

    public function test_the_concurrency_cap_is_separate_from_the_meta_cap(): void
    {
        config(['ads.native_max_concurrent' => 1]);
        [$owner, $role, $event] = $this->advertiser();

        $this->actingAs($owner)->post(route('promotions.store'), $this->payload($role, $event));
        $this->actingAs($owner)->post(route('promotions.store'), $this->payload($role, $event));

        $this->assertSame(1, BoostCampaign::network()->count());
    }

    public function test_approving_a_promotion_activates_it_and_emails_the_advertiser(): void
    {
        Mail::fake();
        [$owner, $role, $event] = $this->advertiser();

        $this->actingAs($owner)->post(route('promotions.store'), $this->payload($role, $event));
        $campaign = BoostCampaign::network()->first();

        $admin = $this->createOwner(true);
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin)
            ->post(route('admin.promotions.approve', ['campaign' => $campaign->id]));

        $campaign->refresh();
        $this->assertSame('approved', $campaign->moderation_status);
        $this->assertSame('active', $campaign->status);
        $this->assertSame($admin->id, $campaign->moderated_by);

        Mail::assertSent(PromotionDecision::class);
    }

    public function test_rejecting_a_promotion_refunds_and_does_not_pollute_the_meta_alert(): void
    {
        Mail::fake();
        [$owner, $role, $event] = $this->advertiser();

        $this->actingAs($owner)->post(route('promotions.store'), $this->payload($role, $event));
        $campaign = BoostCampaign::network()->first();

        $admin = $this->createOwner(true);
        $this->withSession(['admin_password_confirmed_at' => now()->timestamp])->actingAs($admin)
            ->post(route('admin.promotions.reject', ['campaign' => $campaign->id]), [
                'moderation_notes' => 'Misleading headline',
            ]);

        $campaign->refresh();
        $this->assertSame('rejected', $campaign->moderation_status);
        $this->assertSame('rejected', $campaign->status);
        $this->assertSame('Misleading headline', $campaign->moderation_notes);

        // The ad's own status is set, never meta_status - 'DISAPPROVED' there is Meta's
        // verdict and drives the boosts_disapproved alert, which must stay clean.
        $ad = $campaign->ads->first();
        $this->assertSame('rejected', $ad->status);
        $this->assertNull($ad->meta_status);

        Mail::assertSent(PromotionDecision::class);
    }

    public function test_a_non_admin_cannot_moderate(): void
    {
        [$owner, $role, $event] = $this->advertiser();
        $this->actingAs($owner)->post(route('promotions.store'), $this->payload($role, $event));
        $campaign = BoostCampaign::network()->first();

        $this->actingAs($this->createOwner())
            ->post(route('admin.promotions.approve', ['campaign' => $campaign->id]));

        $this->assertSame('pending', $campaign->fresh()->moderation_status);
    }

    public function test_a_trusted_advertiser_skips_the_queue(): void
    {
        config(['ads.native_auto_approve_after' => 1, 'ads.native_max_concurrent' => 10]);
        [$owner, $role, $event] = $this->advertiser();

        // One previously approved campaign that actually ran, no rejections. `impressions` is
        // required now: trust is earned by delivery, not by approval alone.
        BoostCampaign::create([
            'event_id' => $event->id, 'role_id' => $role->id, 'user_id' => $owner->id,
            'channel' => 'network', 'name' => 'Past', 'status' => 'completed',
            'moderation_status' => 'approved', 'billing_status' => 'charged', 'user_budget' => 5,
            'impressions' => 2500,
        ]);

        $this->actingAs($owner)->post(route('promotions.store'), $this->payload($role, $event));

        $campaign = BoostCampaign::network()->where('name', 'My Show')->first();

        $this->assertSame('approved', $campaign->moderation_status);
        $this->assertSame('active', $campaign->status);
    }

    public function test_approved_then_cancelled_campaigns_do_not_buy_trusted_status(): void
    {
        // The gaming vector: cancel writes only `status`, never `moderation_status`, and a
        // never-served campaign refunds in full. Counting approvals alone therefore let an
        // advertiser buy three minimum-budget campaigns, wait for approval, cancel each for a
        // full refund, and skip review from then on at a net cost of nothing.
        config(['ads.native_auto_approve_after' => 1, 'ads.native_max_concurrent' => 10]);
        [$owner, $role, $event] = $this->advertiser();

        BoostCampaign::create([
            'event_id' => $event->id, 'role_id' => $role->id, 'user_id' => $owner->id,
            'channel' => 'network', 'name' => 'Approved then cancelled', 'status' => 'cancelled',
            'moderation_status' => 'approved', 'billing_status' => 'refunded', 'user_budget' => 5,
        ]);

        $this->actingAs($owner)->post(route('promotions.store'), $this->payload($role, $event));

        $this->assertSame('pending', BoostCampaign::network()->where('name', 'My Show')->first()->moderation_status);
    }

    public function test_a_single_past_rejection_permanently_restores_review(): void
    {
        config(['ads.native_auto_approve_after' => 1, 'ads.native_max_concurrent' => 10]);
        [$owner, $role, $event] = $this->advertiser();

        foreach ([['approved', 'completed'], ['rejected', 'rejected']] as [$moderation, $status]) {
            BoostCampaign::create([
                'event_id' => $event->id, 'role_id' => $role->id, 'user_id' => $owner->id,
                'channel' => 'network', 'name' => 'Past', 'status' => $status,
                'moderation_status' => $moderation, 'billing_status' => 'charged', 'user_budget' => 5,
            ]);
        }

        $this->actingAs($owner)->post(route('promotions.store'), $this->payload($role, $event));

        $campaign = BoostCampaign::network()->where('name', 'My Show')->first();

        $this->assertSame('pending', $campaign->moderation_status);
    }
}
