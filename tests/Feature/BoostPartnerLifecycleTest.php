<?php

namespace Tests\Feature;

use App\Exceptions\BoostPartnerException;
use App\Exceptions\MetaApiException;
use App\Jobs\LaunchPartnerBoost;
use App\Models\BoostCampaign;
use App\Models\BoostPartner;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Services\BoostBillingService;
use App\Services\BoostPartnerService;
use App\Services\BoostPartnerSync;
use App\Services\MetaAdsServiceFake;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * A boost on a partner's schedule is a request: it launches only when the CURRENT version is
 * approved and the CURRENT budget is paid, in either order, and only under the partner's own
 * ad account and Page. Nothing about it is refunded, credited or prepaid to the platform.
 */
class BoostPartnerLifecycleTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private User $partnerUser;

    private User $business;

    private BoostPartner $partner;

    private Role $role;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();
        MetaAdsServiceFake::resetRecorder();
        config(['app.hosted' => true, 'services.meta.access_token' => '', 'services.meta.page_id' => 'PLATFORM_PAGE', 'services.meta.ad_account_id' => 'PLATFORM_ACCOUNT']);

        $this->partnerUser = $this->createOwner();
        $this->role = $this->createRole($this->partnerUser, 'venue');
        $this->business = User::factory()->create();
        $this->role->users()->attach($this->business->id, ['level' => 'admin']);
        $this->event = $this->createEvent($this->role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->partner = $this->makePartner($this->partnerUser);
    }

    private function makePartner(User $user, bool $usable = true): BoostPartner
    {
        $partner = new BoostPartner;
        $partner->forceFill([
            'user_id' => $user->id,
            'enabled_at' => now(),
            'connection_method' => BoostPartner::METHOD_PARTNER_SHARE,
            'page_id' => 'PARTNER_PAGE',
            'page_name' => 'Partner Page',
            'ad_account_id' => 'PARTNER_ACCOUNT',
            'ad_account_currency' => 'USD',
            'ad_account_timezone' => 'America/New_York',
            'confirmed_at' => $usable ? now() : null,
        ])->save();

        return $partner;
    }

    private function submit(array $data = []): BoostCampaign
    {
        return app(BoostPartnerService::class)->submit($this->partner, $this->event, $this->role, $this->business, $data + [
            'budget' => 50,
            'headline' => 'Friday Jazz',
            'primary_text' => 'Live trio',
            'description' => 'Free entry',
            'call_to_action' => 'LEARN_MORE',
            'scheduled_end' => now()->addDays(7)->toDateTimeString(),
        ], [
            'scheduled_start' => now(),
            'scheduled_end' => now()->addDays(7),
            'targeting' => ['geo_locations' => ['countries' => ['US']]],
            'headline' => 'x', 'primary_text' => 'x', 'description' => 'x', 'call_to_action' => 'LEARN_MORE',
        ]);
    }

    private function pay(BoostCampaign $c, string $ref = 'pi_1'): BoostCampaign
    {
        return app(BoostPartnerService::class)->confirmPayment($c, ['reference' => $ref, 'amount' => '60.00', 'currency' => 'USD'], $this->partnerUser);
    }

    private function approve(BoostCampaign $c, ?int $version = null): BoostCampaign
    {
        return app(BoostPartnerService::class)->approve($c, $version ?? (int) $c->fresh()->version, null, $this->partnerUser);
    }

    private function launch(BoostCampaign $c): BoostCampaign
    {
        (new LaunchPartnerBoost($c->fresh()))->handle();

        return $c->fresh();
    }

    public function test_a_request_waits_for_approval_and_nothing_goes_to_meta(): void
    {
        $c = $this->submit();

        $this->assertSame('pending_approval', $c->status);
        $this->assertSame(0.0, (float) $c->markup_rate);
        $this->assertSame('partner', $c->billing_status);
        $this->assertSame('PARTNER_ACCOUNT', $c->meta_ad_account_id);
        $this->assertCount(1, $c->versions);
        Queue::assertNotPushed(LaunchPartnerBoost::class);
    }

    public function test_approval_then_payment_launches_and_one_alone_does_not(): void
    {
        $c = $this->approve($this->submit());
        $this->assertSame('awaiting_payment', $c->status);
        Queue::assertNotPushed(LaunchPartnerBoost::class);

        $c = $this->pay($c);
        $this->assertSame('in_review', $c->status);
        Queue::assertPushed(LaunchPartnerBoost::class, 1);
    }

    public function test_payment_then_approval_launches_too(): void
    {
        $c = $this->pay($this->submit());
        $this->assertSame('pending_approval', $c->status);
        Queue::assertNotPushed(LaunchPartnerBoost::class);

        $c = $this->approve($c);
        $this->assertSame('in_review', $c->status);
        Queue::assertPushed(LaunchPartnerBoost::class, 1);
    }

    public function test_repeating_a_step_does_not_launch_twice(): void
    {
        $c = $this->pay($this->approve($this->submit()));
        $this->approve($c, 1);
        $this->pay($c);

        Queue::assertPushed(LaunchPartnerBoost::class, 1);
    }

    public function test_an_approval_of_an_old_version_is_refused(): void
    {
        $c = $this->submit();
        app(BoostPartnerService::class)->edit($c, $this->business, ['headline' => 'Saturday Jazz']);

        $this->expectException(BoostPartnerException::class);
        $this->approve($c, 1);
    }

    public function test_the_launch_uses_the_partners_account_and_page_never_the_platforms(): void
    {
        $c = $this->launch($this->pay($this->approve($this->submit())));

        $this->assertNotNull($c->meta_campaign_id);
        $this->assertNotNull($c->launched_at);
        $create = collect(MetaAdsServiceFake::$calls)->firstWhere('method', 'createPartnerCampaign');
        $this->assertSame('PARTNER_ACCOUNT', $create['ad_account']);
        $this->assertSame('PARTNER_PAGE', $create['page']);
        $this->assertNotContains('PLATFORM_PAGE', collect(MetaAdsServiceFake::$calls)->pluck('page')->all());
    }

    public function test_an_unconfirmed_partner_holds_a_ready_request_until_confirmed(): void
    {
        $this->partner->forceFill(['confirmed_at' => null])->save();

        $c = $this->pay($this->approve($this->submit()));
        $this->assertSame('ready', $c->status);
        Queue::assertNotPushed(LaunchPartnerBoost::class);

        $this->partner->forceFill(['confirmed_at' => now()])->save();
        app(BoostPartnerService::class)->launchReady($this->partner->fresh());

        $this->assertSame('in_review', $c->fresh()->status);
        Queue::assertPushed(LaunchPartnerBoost::class, 1);
    }

    public function test_a_text_edit_after_launch_pauses_and_needs_approval_but_not_payment(): void
    {
        $c = $this->launch($this->pay($this->approve($this->submit())));
        MetaAdsServiceFake::$calls = [];

        $result = app(BoostPartnerService::class)->edit($c, $this->business, ['headline' => 'Saturday Jazz']);
        $c = $c->fresh();

        $this->assertTrue($result['needs_approval']);
        $this->assertFalse($result['needs_payment']);
        $this->assertSame('pending_approval', $c->status);
        $this->assertSame(2, (int) $c->version);
        $this->assertTrue($c->isPaidCurrent());
        $pause = collect(MetaAdsServiceFake::$calls)->firstWhere('method', 'setStatus');
        $this->assertSame('PAUSED', $pause['args']['status']);

        // Re-approval resumes with the new creative rather than creating a second campaign.
        $this->approve($c, 2);
        $this->launch($c);
        $methods = collect(MetaAdsServiceFake::$calls)->pluck('method')->all();
        $this->assertContains('updateCreative', $methods);
        $this->assertNotContains('createPartnerCampaign', $methods);
    }

    public function test_a_budget_edit_clears_the_payment(): void
    {
        $c = $this->launch($this->pay($this->approve($this->submit())));

        $result = app(BoostPartnerService::class)->edit($c, $this->business, ['budget' => 80]);
        $c = $c->fresh();

        $this->assertTrue($result['needs_payment']);
        $this->assertFalse($c->isPaidCurrent());
        $this->assertSame('awaiting_payment', $c->status);
    }

    public function test_an_end_date_change_applies_at_meta_directly(): void
    {
        $c = $this->launch($this->pay($this->approve($this->submit())));
        $c->forceFill(['status' => 'active'])->save();
        MetaAdsServiceFake::$calls = [];

        $result = app(BoostPartnerService::class)->edit($c, $this->business, ['scheduled_end' => now()->addDays(9)->toDateTimeString()]);

        $this->assertFalse($result['needs_approval']);
        $this->assertSame('active', $c->fresh()->status);
        $this->assertSame('updateAdSet', MetaAdsServiceFake::$calls[0]['method']);
    }

    public function test_stop_pauses_at_meta_and_never_restarts(): void
    {
        $c = $this->launch($this->pay($this->approve($this->submit())));
        MetaAdsServiceFake::$calls = [];

        $result = app(BoostPartnerService::class)->stop($c, $this->partnerUser, BoostPartnerService::STOP_BY_PARTNER, 'Event cancelled');

        $this->assertTrue($result['paused_at_meta']);
        $this->assertSame('stopped', $result['campaign']->status);
        $this->assertSame('PAUSED', MetaAdsServiceFake::$calls[0]['args']['status']);

        // A repeated approval answers the boost as it stands; it never brings it back.
        $this->assertSame('stopped', $this->approve($c, 1)->status);
        Queue::assertPushed(LaunchPartnerBoost::class, 1);

        $this->expectException(BoostPartnerException::class);
        app(BoostPartnerService::class)->edit($c, $this->business, ['headline' => 'Again']);
    }

    public function test_a_stop_that_cannot_reach_meta_is_retried_by_the_sync(): void
    {
        $c = $this->launch($this->pay($this->approve($this->submit())));
        MetaAdsServiceFake::$failWith = new MetaApiException('down', 2, null, 500);

        $result = app(BoostPartnerService::class)->stop($c, $this->partnerUser, BoostPartnerService::STOP_BY_PARTNER);
        $this->assertFalse($result['paused_at_meta']);
        $this->assertNotNull($c->fresh()->pause_pending_at);

        app(BoostPartnerSync::class)->run();
        $this->assertNull($c->fresh()->pause_pending_at);
    }

    public function test_the_sync_moves_a_reviewed_ad_to_active_and_records_daily_spend(): void
    {
        $c = $this->launch($this->pay($this->approve($this->submit())));
        MetaAdsServiceFake::$dailySpend = [now('America/New_York')->format('Y-m-d') => 12.5];

        app(BoostPartnerSync::class)->run();
        $c = $c->fresh();

        $this->assertSame('active', $c->status);
        $this->assertSame(12.5, (float) $c->actual_spend);
        $this->assertSame(1, $c->spendDays()->count());
    }

    public function test_a_meta_refusal_marks_the_boost_rejected_and_it_can_be_resent(): void
    {
        $c = $this->launch($this->pay($this->approve($this->submit())));
        MetaAdsServiceFake::$adStates['*'] = ['status' => 'DISAPPROVED', 'rejection_reason' => 'Text in image'];

        app(BoostPartnerSync::class)->run();
        $this->assertSame('rejected', $c->fresh()->status);

        app(BoostPartnerService::class)->edit($c, $this->business, ['primary_text' => 'Live trio tonight']);
        $this->assertSame('pending_approval', $c->fresh()->status);
    }

    public function test_lost_access_needs_attention_first_and_stops_only_after_the_grace(): void
    {
        $c = $this->launch($this->pay($this->approve($this->submit())));
        MetaAdsServiceFake::$failWith = new MetaApiException('token', 190, null, 400);

        app(BoostPartnerSync::class)->run();
        $this->assertNotNull($this->partner->fresh()->needs_attention_at);
        $this->assertSame('in_review', $c->fresh()->status);

        $this->partner->forceFill(['needs_attention_at' => now()->subHours(73)])->save();
        MetaAdsServiceFake::$failWith = new MetaApiException('token', 190, null, 400);
        app(BoostPartnerSync::class)->run();

        $c = $c->fresh();
        $this->assertSame('stopped', $c->status);
        $this->assertSame(BoostPartnerService::STOP_ACCESS_WITHDRAWN, $c->stop_code);
    }

    public function test_no_refund_path_touches_a_partner_boost(): void
    {
        $c = $this->launch($this->pay($this->approve($this->submit())));
        $billing = new BoostBillingService;

        $this->assertFalse($billing->refundOnCancellation($c));
        $this->assertFalse($billing->refundFull($c));
        $this->assertFalse($billing->refundUnspent($c));
        $this->assertFalse($billing->refundCreditRemainder($c));
        $this->assertSame('partner', $c->fresh()->billing_status);
    }

    public function test_deleting_the_event_stops_the_boost_and_never_deletes_it_at_meta(): void
    {
        $c = $this->launch($this->pay($this->approve($this->submit())));
        MetaAdsServiceFake::$calls = [];

        $this->event->delete();

        $c = BoostCampaign::find($c->id);
        $this->assertSame('stopped', $c->status);
        $this->assertSame(BoostPartnerService::STOP_EVENT_REMOVED, $c->stop_code);
        $this->assertSame(['setStatus'], collect(MetaAdsServiceFake::$calls)->pluck('method')->all());
    }

    public function test_cancel_and_pause_routes_are_refused_for_a_partner_boost(): void
    {
        $c = $this->launch($this->pay($this->approve($this->submit())));

        $this->actingAs($this->business)->post(route('boost.cancel', ['hash' => $c->hashedId()]))->assertSessionHas('error');
        $this->actingAs($this->business)->post(route('boost.toggle_pause', ['hash' => $c->hashedId()]))->assertSessionHas('error');
        $this->assertSame('in_review', $c->fresh()->status);
    }

    public function test_the_covering_partner_is_decided_by_the_events_owning_schedule(): void
    {
        $venueOwner = $this->createOwner();
        $otherVenue = $this->createRole($venueOwner, 'venue');
        $this->event->roles()->attach($otherVenue->id, ['is_accepted' => true]);

        $this->assertSame($this->partner->id, BoostPartner::forEvent($this->event)?->id);
        $this->assertNull(BoostPartner::forRole($otherVenue));
    }

    public function test_a_request_the_partner_sends_itself_is_already_approved(): void
    {
        $c = app(BoostPartnerService::class)->submit($this->partner, $this->event, $this->role, $this->partnerUser, ['budget' => 50], [
            'scheduled_start' => now(), 'scheduled_end' => now()->addDays(7), 'targeting' => [],
            'headline' => 'x', 'primary_text' => 'x', 'description' => 'x', 'call_to_action' => 'LEARN_MORE',
        ]);

        $this->assertSame('awaiting_payment', $c->status);
        $this->assertTrue($c->isApprovedCurrent());
    }

    public function test_a_request_made_before_confirmation_launches_on_the_confirmed_account_only(): void
    {
        // The partner first saved a wrong account, then the right one was confirmed.
        $this->partner->forceFill(['ad_account_id' => 'WRONG_ACCOUNT', 'page_id' => 'WRONG_PAGE', 'confirmed_at' => null])->save();
        $c = $this->pay($this->approve($this->submit()));
        $this->assertSame('ready', $c->status);

        $this->partner->forceFill(['ad_account_id' => 'PARTNER_ACCOUNT', 'page_id' => 'PARTNER_PAGE', 'confirmed_at' => now()])->save();
        app(BoostPartnerService::class)->launchReady($this->partner->fresh());
        $this->launch($c);

        $create = collect(MetaAdsServiceFake::$calls)->firstWhere('method', 'createPartnerCampaign');
        $this->assertSame('PARTNER_ACCOUNT', $create['ad_account']);
        $this->assertSame('PARTNER_PAGE', $create['page']);
        $this->assertSame('PARTNER_ACCOUNT', $c->fresh()->meta_ad_account_id);
    }

    public function test_a_launch_on_an_account_in_another_currency_goes_back_for_approval(): void
    {
        $c = $this->pay($this->approve($this->submit()));
        $this->partner->forceFill(['ad_account_currency' => 'EUR'])->save();

        $c = $this->launch($c);

        $this->assertSame('pending_approval', $c->status);
        $this->assertFalse($c->isPaidCurrent());
        $this->assertNull($c->meta_campaign_id);
    }

    public function test_a_failed_connection_check_holds_launches(): void
    {
        $this->partner->forceFill(['connection_error' => 'Ad account disabled'])->save();

        $c = $this->pay($this->approve($this->submit()));

        $this->assertSame('ready', $c->status);
        Queue::assertNotPushed(LaunchPartnerBoost::class);
    }

    public function test_lost_access_at_launch_waits_as_ready_and_is_not_a_rejection(): void
    {
        $c = $this->pay($this->approve($this->submit()));
        MetaAdsServiceFake::$failWith = new MetaApiException('token', 190, null, 400);

        $c = $this->launch($c);

        $this->assertSame('ready', $c->status);
        $this->assertNotNull($this->partner->fresh()->needs_attention_at);
    }

    public function test_access_coming_back_in_the_sync_launches_what_waited(): void
    {
        $live = $this->launch($this->pay($this->approve($this->submit())));
        $this->partner->forceFill(['needs_attention_at' => now()])->save();

        $otherEvent = $this->createEvent($this->role, ['starts_at' => now()->addDays(12)->format('Y-m-d H:i:s')]);
        $this->event = $otherEvent;
        $waiting = $this->pay($this->approve($this->submit()));
        $this->assertSame('ready', $waiting->status);

        app(BoostPartnerSync::class)->run();

        $this->assertNull($this->partner->fresh()->needs_attention_at);
        $this->assertSame('in_review', $waiting->fresh()->status);
        $this->assertNotNull($live->fresh()->meta_campaign_id);
    }

    public function test_one_campaigns_success_does_not_clear_anothers_access_error(): void
    {
        $this->launch($this->pay($this->approve($this->submit())));
        $this->event = $this->createEvent($this->role, ['starts_at' => now()->addDays(12)->format('Y-m-d H:i:s')]);
        $this->launch($this->pay($this->approve($this->submit())));

        // The first campaign's first call fails; the second's calls all answer.
        MetaAdsServiceFake::$failWith = new MetaApiException('gone', 190, null, 400);
        app(BoostPartnerSync::class)->run();

        $this->assertNotNull($this->partner->fresh()->needs_attention_at);
    }

    public function test_an_ad_account_the_partner_left_is_abandoned_and_raises_no_flag(): void
    {
        $old = $this->launch($this->pay($this->approve($this->submit())));
        $old->forceFill(['meta_ad_account_id' => 'OLD_ACCOUNT', 'status' => 'completed'])->save();

        MetaAdsServiceFake::$failWith = new MetaApiException('gone', 100, 33, 400);
        MetaAdsServiceFake::$failForAccount = 'OLD_ACCOUNT';
        app(BoostPartnerSync::class)->run();

        $this->assertNull($this->partner->fresh()->needs_attention_at);
        $this->assertNotNull($old->fresh()->spend_final_at);
    }

    public function test_a_request_made_before_connecting_takes_the_accounts_currency_and_launches(): void
    {
        $this->partner->forceFill(['ad_account_id' => null, 'page_id' => null, 'ad_account_currency' => null, 'confirmed_at' => null])->save();
        $c = $this->pay($this->approve($this->submit()));
        $this->assertSame('ready', $c->status);

        $this->partner->forceFill(['ad_account_id' => 'PARTNER_ACCOUNT', 'page_id' => 'PARTNER_PAGE', 'ad_account_currency' => 'EUR', 'confirmed_at' => now()])->save();
        app(BoostPartnerService::class)->launchReady($this->partner->fresh());
        $c = $this->launch($c);

        $this->assertSame('EUR', $c->currency_code);
        $this->assertNotNull($c->meta_campaign_id);
        $this->assertSame('in_review', $c->status);
    }

    public function test_a_stop_that_lands_while_the_sync_reads_meta_is_not_overwritten(): void
    {
        $c = $this->launch($this->pay($this->approve($this->submit())));
        MetaAdsServiceFake::$onAdState = function () use ($c) {
            MetaAdsServiceFake::$onAdState = null;
            app(BoostPartnerService::class)->stop($c, $this->partnerUser, BoostPartnerService::STOP_BY_PARTNER);
        };

        app(BoostPartnerSync::class)->run();

        $this->assertSame('stopped', $c->fresh()->status);
    }

    public function test_a_payment_cannot_be_withdrawn_after_launch(): void
    {
        $c = $this->launch($this->pay($this->approve($this->submit())));
        app(BoostPartnerService::class)->edit($c, $this->business, ['headline' => 'Saturday Jazz']);

        $this->expectException(BoostPartnerException::class);
        app(BoostPartnerService::class)->withdrawPayment($c->fresh(), $this->partnerUser);
    }

    public function test_a_request_on_a_schedule_that_left_the_partner_cannot_be_resent(): void
    {
        $c = $this->submit();
        $newOwner = $this->createOwner();
        $this->role->forceFill(['user_id' => $newOwner->id])->save();
        app(BoostPartnerService::class)->declineOpenRequestsForRole($this->role);

        $this->expectException(BoostPartnerException::class);
        app(BoostPartnerService::class)->edit($c->fresh(), $this->business, ['headline' => 'Again']);
    }

    public function test_a_removed_team_member_cannot_stop_or_open_the_boost(): void
    {
        $c = $this->launch($this->pay($this->approve($this->submit())));
        $this->role->users()->detach($this->business->id);

        $this->actingAs($this->business)->get(route('boost.show', ['hash' => $c->hashedId()]))->assertForbidden();
        $this->actingAs($this->business)->post(route('boost.partner.stop', ['hash' => $c->hashedId()]))->assertForbidden();
        $this->assertSame('in_review', $c->fresh()->status);
    }

    public function test_the_partner_still_opens_a_campaign_whose_event_was_deleted(): void
    {
        $c = $this->launch($this->pay($this->approve($this->submit())));
        $this->event->delete();

        $this->actingAs($this->partnerUser)->get(route('boost.show', ['hash' => $c->hashedId()]))->assertOk();
    }

    public function test_a_login_partner_without_a_token_never_falls_back_to_the_fake_or_the_platform(): void
    {
        $this->partner->forceFill(['connection_method' => BoostPartner::METHOD_LOGIN, 'meta_token' => null])->save();
        $c = $this->submit();

        $this->expectException(MetaApiException::class);
        \App\Services\MetaAdsService::forCampaign($c->fresh());
    }

    public function test_a_budget_changed_and_changed_back_moves_on_again(): void
    {
        $c = $this->launch($this->pay($this->approve($this->submit())));
        app(BoostPartnerService::class)->edit($c, $this->business, ['budget' => 80]);
        $this->assertSame('awaiting_payment', $c->fresh()->status);

        app(BoostPartnerService::class)->edit($c->fresh(), $this->business, ['budget' => 50]);

        $this->assertSame('in_review', $c->fresh()->status);
        Queue::assertPushed(LaunchPartnerBoost::class, 2);
    }

    public function test_the_venue_an_event_is_at_cannot_stop_its_boost(): void
    {
        $c = $this->launch($this->pay($this->approve($this->submit())));
        $venueOwner = $this->createOwner();
        $venue = $this->createRole($venueOwner, 'venue');
        $this->event->roles()->attach($venue->id, ['is_accepted' => true]);

        $this->actingAs($venueOwner)->post(route('boost.partner.stop', ['hash' => $c->hashedId()]))->assertForbidden();
        $this->assertSame('in_review', $c->fresh()->status);
    }

    public function test_a_cleared_description_stays_cleared(): void
    {
        $c = $this->submit(['description' => 'Free entry']);
        app(BoostPartnerService::class)->edit($c, $this->business, ['description' => '']);

        $this->assertNull($c->fresh()->currentVersion()->description);
    }

    public function test_meta_budgets_are_whole_units_for_currencies_without_cents(): void
    {
        $this->assertSame(5000, \App\Services\MetaAdsService::minorUnits(50, 'USD'));
        $this->assertSame(5000, \App\Services\MetaAdsService::minorUnits(5000, 'JPY'));
    }

    public function test_disconnect_stops_running_boosts_and_keeps_waiting_requests(): void
    {
        $live = $this->launch($this->pay($this->approve($this->submit())));
        $this->event = $this->createEvent($this->role, ['starts_at' => now()->addDays(12)->format('Y-m-d H:i:s')]);
        $waiting = $this->submit();

        $this->actingAs($this->partnerUser)->post(route('boost.partner.settings.disconnect'))->assertRedirect();

        $this->assertSame('stopped', $live->fresh()->status);
        $this->assertSame('pending_approval', $waiting->fresh()->status);
        $this->assertNull($this->partner->fresh()->ad_account_id);
    }
}
