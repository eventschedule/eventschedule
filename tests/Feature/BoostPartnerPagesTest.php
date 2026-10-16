<?php

namespace Tests\Feature;

use App\Models\BoostCampaign;
use App\Models\BoostPartner;
use App\Models\BoostStatement;
use App\Models\BoostStatementLine;
use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Services\BoostPartnerService;
use App\Services\MetaAdsServiceFake;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The pages of a boost that runs through a boost partner's own Page, for the three people who
 * open them: the Business that asks (the request form, its page, the event form's button), the
 * partner that answers (Requests, Campaigns, Statements, the Boosts settings tab) and a platform
 * admin (the Boost partners card on /admin/boost).
 *
 * Each test is something a person could see. The Business never reads the word "version" or
 * "Total charged": it pays the partner, not this site, and an edit is "sent again", not a version.
 * Assertions count matches and never hand a whole page to a pattern assertion.
 */
class BoostPartnerPagesTest extends TestCase
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

        // No page here may reach the network.
        Http::preventStrayRequests();
        Queue::fake();
        MetaAdsServiceFake::resetRecorder();
        config([
            'app.hosted' => true,
            'services.meta.partners_enabled' => true,
            'services.meta.access_token' => '',
            'services.meta.page_id' => 'PLATFORM_PAGE',
            'services.meta.ad_account_id' => 'PLATFORM_ACCOUNT',
            'services.meta.business_id' => '4455667788',
            'auth.admin_requires_two_factor' => false,
        ]);

        $this->partnerUser = $this->createOwner();
        $this->role = $this->createRole($this->partnerUser, 'venue', ['name' => 'Harbor Cafe']);
        $this->business = $this->createOwner();
        $this->business->forceFill(['name' => 'Jane Business'])->save();
        $this->role->users()->attach($this->business->id, ['level' => 'admin']);
        $this->event = $this->createEvent($this->role, [
            'name' => 'Friday Jazz Night',
            'starts_at' => now()->addDays(10)->setTime(12, 0)->format('Y-m-d H:i:s'),
            'creator_role_id' => $this->role->id,
            'flyer_image_url' => 'demo_flyer_dj.jpg',
        ]);

        $this->partner = $this->makePartner($this->partnerUser);
    }

    private function makePartner(User $user, array $attrs = []): BoostPartner
    {
        $partner = new BoostPartner;
        $partner->forceFill($attrs + [
            'user_id' => $user->id,
            'enabled_at' => now(),
            'connection_method' => BoostPartner::METHOD_PARTNER_SHARE,
            'page_id' => '111222333',
            'page_name' => 'Harbor Events Page',
            'ad_account_id' => '999888777',
            'ad_account_name' => 'Network Ads',
            'ad_account_currency' => 'USD',
            'ad_account_timezone' => 'America/New_York',
            'ad_account_business_name' => 'Network Holdings LLC',
            'confirmed_at' => now(),
        ])->save();

        return $partner->fresh();
    }

    private function submit(?Event $event = null, array $data = []): BoostCampaign
    {
        $event ??= $this->event;

        return app(BoostPartnerService::class)->submit($this->partner, $event, $this->role, $this->business, $data + [
            'budget' => 50,
            'headline' => 'Friday Jazz',
            'primary_text' => 'Live trio from 7 PM',
            'description' => 'Free entry',
            'call_to_action' => 'LEARN_MORE',
            'scheduled_end' => now()->addDays(7)->toDateTimeString(),
        ], [
            'scheduled_start' => now(),
            'scheduled_end' => now()->addDays(7),
            'targeting' => ['geo_locations' => ['countries' => ['US']], 'age_min' => 18, 'age_max' => 65],
            'headline' => 'x', 'primary_text' => 'x', 'description' => 'x', 'call_to_action' => 'LEARN_MORE',
        ]);
    }

    /** What a person reads on the page: the main column, without its scripts and styles. */
    private function readable(string $html): string
    {
        $main = preg_match('/<main id="main-content"[^>]*>(.*)<\/main>/s', $html, $found) ? $found[1] : $html;
        $main = preg_replace('/<(script|style|dialog)\b.*?<\/\1>/s', '', $main);

        return html_entity_decode(strip_tags($main));
    }

    private function assertBusinessWording(string $html, string $page): void
    {
        $text = $this->readable($html);
        $this->assertSame(0, preg_match('/\bversions?\b/i', $text), "$page: the Business never reads \"version\"");
        $this->assertStringNotContainsString(__('messages.total_charged'), $text, "$page: nothing is charged here");
        // No Pause or Resume, and no Cancel that deletes the campaign at Facebook: an edit
        // pauses the ad for approval, and Withdraw or Stop ends it.
        $this->assertStringNotContainsString('/toggle-pause', $html, "$page: no Pause or Resume");
        $this->assertStringNotContainsString(__('messages.cancel_campaign'), $text, "$page: no Cancel campaign");
    }

    public function test_the_business_sends_a_request_with_no_fee_and_no_card(): void
    {
        $url = route('boost.create', ['event_id' => UrlUtils::encodeId($this->event->id), 'role_id' => UrlUtils::encodeId($this->role->id)]);
        $html = $this->actingAs($this->business)->get($url)->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'id="bp-form"'));
        $this->assertStringContainsString(__('messages.boost_partner_send_for_approval'), $html);
        $this->assertStringContainsString(e(__('messages.boost_partner_step_approve', ['page' => 'Harbor Events Page'])), $html, 'the three steps name the partner');
        $this->assertStringContainsString('<bdi>Harbor Events Page</bdi>', $html, 'the preview runs under the partner\'s Page');
        $this->assertStringNotContainsString('payment-element', $html, 'no card');
        $this->assertStringNotContainsString(__('messages.service_fee'), $html, 'no fee rows');
        $this->assertStringNotContainsString(__('messages.daily_budget'), $html, 'lifetime budget only');
        $this->assertStringNotContainsString('type="date"', $html, 'the dates use the portal\'s picker');
        $this->assertSame(1, substr_count($html, 'name="scheduled_start"'));
        $this->assertBusinessWording($html, 'the request form');

        // What the form posts is what the controller reads.
        $this->actingAs($this->business)->post(route('boost.store'), [
            'event_id' => UrlUtils::encodeId($this->event->id),
            'role_id' => UrlUtils::encodeId($this->role->id),
            'headline' => 'Jazz on the harbor',
            'primary_text' => 'Live trio, free entry',
            'description' => 'Fridays at 7',
            'call_to_action' => 'GET_TICKETS',
            'budget' => '75',
            'scheduled_start' => now()->format('Y-m-d'),
            'scheduled_end' => now()->addDays(6)->format('Y-m-d'),
            'age_min' => '21',
            'age_max' => '60',
        ])->assertRedirect();

        $campaign = BoostCampaign::where('event_id', $this->event->id)->firstOrFail();
        $this->assertSame('pending_approval', $campaign->status);
        $this->assertSame(75.0, (float) $campaign->user_budget);
        $this->assertSame(21, (int) $campaign->targeting['age_min']);
        $this->assertSame('Jazz on the harbor', $campaign->currentVersion()->headline);
        $this->assertSame('GET_TICKETS', $campaign->currentVersion()->call_to_action);
    }

    public function test_the_business_reads_its_request_in_its_own_words(): void
    {
        $campaign = $this->submit();
        $show = route('boost.show', ['hash' => $campaign->hashedId()]);

        $html = $this->actingAs($this->business)->get($show)->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, 'class="event-status is-warn">'.__('messages.boost_partner_status_pending_approval')));
        $this->assertStringContainsString(__('messages.boost_partner_withdraw_request'), $html, 'before launch it is withdrawn');
        $this->assertStringContainsString('data-confirm="'.e(__('messages.boost_partner_withdraw_confirm', ['page' => 'Harbor Events Page'])).'"', $html);
        $this->assertStringContainsString('href="'.route('boost.edit', ['hash' => $campaign->hashedId()]).'"', $html);
        $this->assertStringContainsString('Live trio from 7 PM', $html, 'the full text beside the preview');
        $this->assertStringContainsString(__('messages.boost_partner_history_sent'), $html);
        $this->assertStringNotContainsString('id="bp-dialog-approve"', $html, 'the partner\'s answers are not the Business\'s');
        $this->assertBusinessWording($html, 'a waiting request');

        // Sent back with a note: amber, the note, and the way to edit it.
        app(BoostPartnerService::class)->decline($campaign, 'Please use a photo of the venue', $this->partnerUser);
        $html = $this->actingAs($this->business)->get($show)->assertOk()->getContent();
        $this->assertStringContainsString(e(__('messages.boost_partner_asked_for_changes', ['page' => 'Harbor Events Page'])), $html);
        $this->assertStringContainsString('Please use a photo of the venue', $html);
        $this->assertStringContainsString(__('messages.boost_partner_edit_and_resend'), $html);
        $this->assertStringContainsString('bg-amber-50', $html);
        $this->assertBusinessWording($html, 'a declined request');

        // Edited and sent again, then approved and waiting for payment: how to pay the partner.
        app(BoostPartnerService::class)->edit($campaign, $this->business, ['headline' => 'Jazz by the water']);
        $this->partner->forceFill(['payment_instructions' => 'Pay <b>here</b>: https://pay.example.org/boost?a=1&b=2'])->save();
        app(BoostPartnerService::class)->approve($campaign, 2, null, $this->partnerUser);
        $html = $this->actingAs($this->business)->get($show)->assertOk()->getContent();
        $this->assertSame('awaiting_payment', $campaign->fresh()->status);
        $this->assertStringContainsString(e(__('messages.boost_partner_how_to_pay', ['page' => 'Harbor Events Page'])), $html);
        $this->assertStringContainsString('href="https://pay.example.org/boost?a=1&amp;b=2"', $html, 'the instructions\' link can be followed');
        $this->assertStringNotContainsString('&amp;amp;', $html, 'the address is escaped once, not twice');
        $this->assertStringContainsString('Pay &lt;b&gt;here&lt;/b&gt;:', $html, 'the partner\'s words are text, never markup');
        $this->assertStringContainsString(__('messages.boost_partner_history_resent'), $html);
        $this->assertBusinessWording($html, 'an approved request');

        // Running: Stop, with the confirm that says it is for good.
        $campaign->fresh()->forceFill(['status' => 'active', 'launched_at' => now(), 'meta_campaign_id' => 'm1', 'actual_spend' => 12.5])->save();
        $html = $this->actingAs($this->business)->get($show)->assertOk()->getContent();
        $this->assertStringContainsString(__('messages.boost_partner_stop_boost'), $html);
        $this->assertStringContainsString('data-confirm="'.e(__('messages.boost_partner_stop_confirm')).'"', $html);
        $this->assertStringContainsString(__('messages.boost_partner_status_active'), $html);
        $this->assertStringContainsString('$12.50', $html, 'the spend, once it runs');
        $this->assertBusinessWording($html, 'a running boost');
    }

    public function test_the_edit_form_says_what_each_change_does_to_a_running_ad(): void
    {
        $campaign = $this->submit();
        $campaign->forceFill(['status' => 'active', 'launched_at' => now(), 'meta_campaign_id' => 'm1', 'approved_version' => 1, 'paid_budget' => 50, 'payment_confirmed_at' => now(), 'payment_amount' => 60, 'payment_reference' => 'pi_1'])->save();

        $html = $this->actingAs($this->business)->get(route('boost.edit', ['hash' => $campaign->hashedId()]))->assertOk()->getContent();

        $this->assertStringContainsString('data-live="1"', $html);
        $this->assertSame(5, substr_count($html, '>'.__('messages.boost_partner_hint_pauses').'<'), 'under each of the ad\'s fields and the picture');
        $this->assertSame(1, substr_count($html, __('messages.boost_partner_hint_pauses_and_pay')), 'under the budget');
        $this->assertStringContainsString('action="'.route('boost.update', ['hash' => $campaign->hashedId()]).'"', $html);
        $this->assertStringContainsString('enctype="multipart/form-data"', $html);
        $this->assertStringContainsString('<span id="bp-submit-label">'.__('messages.save').'</span>', $html, 'nothing changed yet');
        $this->assertStringContainsString('data-label-pause-send="'.e(__('messages.boost_partner_pause_and_send')).'"', $html);
        $this->assertStringNotContainsString('name="scheduled_start"', $html, 'the start cannot move after launch');
        $this->assertBusinessWording($html, 'the edit form');

        // After a refused save the fields hold what was typed, and the label is still measured
        // against what is stored: a change that pauses the running ad asks first.
        $html = $this->actingAs($this->business)
            ->withSession(['_old_input' => ['headline' => 'Typed and refused', 'budget' => '80']])
            ->get(route('boost.edit', ['hash' => $campaign->hashedId()]))->assertOk()->getContent();
        $this->assertSame(1, preg_match('/<input type="text" name="headline"[^>]*value="Typed and refused"[^>]*data-bp-initial="Friday Jazz"/s', $html));
        $this->assertSame(1, preg_match('/<input type="number" name="budget"[^>]*value="80"[^>]*data-bp-initial="50"/s', $html));
        $this->assertStringContainsString("el.getAttribute('data-bp-initial')", $html);
        // Help opens the part of the guide on requests.
        $this->assertStringContainsString(e(marketing_url('/docs/boost#partners-requests')), $html);
    }

    public function test_the_partner_answers_requests_oldest_first_and_sees_what_changed(): void
    {
        $first = $this->submit();
        $later = $this->createEvent($this->role, ['name' => 'Saturday Brunch', 'creator_role_id' => $this->role->id, 'starts_at' => now()->addDays(12)->setTime(12, 0)->format('Y-m-d H:i:s'), 'flyer_image_url' => 'demo_flyer_dj.jpg']);
        $this->travel(5)->minutes();
        $second = $this->submit($later, ['headline' => 'Brunch by the sea']);

        // The first is declined, edited and sent again.
        app(BoostPartnerService::class)->decline($first, 'Show the stage', $this->partnerUser);
        app(BoostPartnerService::class)->edit($first, $this->business, ['headline' => 'Jazz on the stage']);

        $html = $this->actingAs($this->partnerUser)->get(route('boost.partner.requests'))->assertOk()->getContent();

        $this->assertLessThan(strpos($html, 'id="request-'.$second->hashedId().'"'), strpos($html, 'id="request-'.$first->hashedId().'"'), 'oldest first');
        $this->assertStringContainsString('<bdi>Jane Business</bdi>', $html, 'who asked');
        $this->assertStringContainsString('&quot;<bdi>Harbor Cafe</bdi>&quot;', $html, 'for which schedule');
        $this->assertStringContainsString(e(__('messages.boost_partner_version_changed', ['version' => 2, 'fields' => __('messages.headline')])), $html);
        $this->assertStringContainsString('Show the stage', $html, 'the note that asked for it');
        $this->assertStringContainsString(__('messages.boost_partner_before'), $html);
        $this->assertStringContainsString(__('messages.boost_partner_approve_version', ['version' => 2]), $html);
        $this->assertStringContainsString(__('messages.boost_partner_sent_today'), $html);
        $this->assertStringContainsString(e(marketing_url('/docs/boost#partners-requests')), $html, 'Help opens the guide on requests');
        $this->assertSame(2, substr_count($html, 'data-bp-open="decline"'), 'Decline on each card');
        $this->assertSame(2, substr_count($html, 'data-bp-open="payment"'));
        // Decline comes before Approve on a card's approval row.
        $card = substr($html, strpos($html, 'id="request-'.$first->hashedId().'"'));
        $this->assertLessThan(strpos($card, 'data-bp-open="approve"'), strpos($card, 'data-bp-open="decline"'));
        // One copy of each dialog, with the optional payment part of the approval.
        foreach (['approve', 'decline', 'payment'] as $dialog) {
            $this->assertSame(1, substr_count($html, 'id="bp-dialog-'.$dialog.'"'));
        }
        $this->assertStringContainsString('name="with_payment"', $html);
        $this->assertSame(1, substr_count($html, 'id="boost-partner-tabs"'), 'the section\'s tabs');
        $this->assertSame(1, preg_match('/aria-current="page"\s*>\s*'.preg_quote(__('messages.requests'), '/').'\s*<span class="ap-tab-count is-waiting">2<\/span>/', $html), 'Requests, with how many wait');

        // A request that changed while it was being answered: back here, said as an alert, and
        // that card is the one in focus.
        $html = $this->actingAs($this->partnerUser)
            ->withSession(['error' => __('messages.boost_partner_changed_while_reviewing'), 'focus_request' => $second->hashedId()])
            ->get(route('boost.partner.requests'))->assertOk()->getContent();
        $this->assertSame(1, preg_match('/role="alert"[^>]*>.*?'.preg_quote(e(__('messages.boost_partner_changed_while_reviewing')), '/').'/s', $html));
        $this->assertSame(1, preg_match('/id="request-'.preg_quote($second->hashedId(), '/').'"[^>]*data-bp-focus/', $html));

        // The schedule filter narrows the list.
        $other = $this->createRole($this->partnerUser, 'venue', ['name' => 'Dune Bar']);
        $html = $this->actingAs($this->partnerUser)->get(route('boost.partner.requests', ['schedule' => UrlUtils::encodeId($other->id)]))->assertOk()->getContent();
        $this->assertStringContainsString(__('messages.boost_partner_no_requests_here'), $html);
        $this->assertStringContainsString('data-searchable', $html);
    }

    public function test_an_empty_requests_tab_is_a_setup_checklist(): void
    {
        $fresh = $this->createOwner();
        $this->createRole($fresh, 'venue');
        $this->makePartner($fresh, ['page_id' => null, 'ad_account_id' => null, 'page_name' => null, 'confirmed_at' => null, 'connection_method' => null]);

        $html = $this->actingAs($fresh)->get(route('boost.partner.requests'))->assertOk()->getContent();

        foreach (['boost_partner_setup_connect', 'boost_partner_setup_confirm', 'boost_partner_setup_card', 'boost_partner_setup_tell'] as $step) {
            $this->assertSame(1, substr_count($html, __('messages.'.$step).'<'), $step);
        }
        $this->assertStringContainsString(__('messages.boost_partner_connect_notice_not_connected'), $html, 'every partner page says it is not connected');
        $this->assertStringContainsString('href="'.route('profile.edit').'#section-boost-account"', $html);
        $this->assertStringContainsString('href="'.route('profile.edit').'#section-boost-card"', $html);
    }

    public function test_campaigns_set_what_was_paid_beside_what_was_spent(): void
    {
        $campaign = $this->submit();
        $campaign->forceFill([
            'status' => 'active', 'launched_at' => now(), 'meta_campaign_id' => 'm1',
            'approved_version' => 1, 'paid_budget' => 50, 'payment_confirmed_at' => now(),
            'payment_amount' => 60, 'payment_currency' => 'USD', 'payment_reference' => 'pi_3QxReference', 'actual_spend' => 46,
        ])->save();

        $html = $this->actingAs($this->partnerUser)->get(route('boost.partner.campaigns'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<table class="page-table'), 'one list');
        $this->assertStringContainsString('href="'.route('boost.show', ['hash' => $campaign->hashedId()]).'"', $html);
        $this->assertStringContainsString('pi_3QxReference', $html);
        $this->assertStringContainsString(__('messages.boost_partner_paid_minus_spend'), $html);
        $this->assertSame(2, substr_count($html, '>$14<'), 'paid minus spend, in the row and in the totals');
        $this->assertStringContainsString(__('messages.boost_partner_not_final'), $html);
        $this->assertSame(1, substr_count($html, 'page-stats is-auto'), 'the totals are one strip for one currency');
        $this->assertStringContainsString('href="'.e(route('boost.partner.export')).'"', $html);
        // A running boost can be stopped from its row, with a reason, after a confirm.
        $this->assertSame(1, substr_count($html, 'data-bp-open="stop"'));
        $this->assertStringContainsString('data-action="'.route('boost.partner.stop', ['hash' => $campaign->hashedId()]).'"', $html);
        $this->assertSame(1, preg_match('/<dialog id="bp-dialog-stop".*?data-confirm="'.preg_quote(e(__('messages.boost_partner_stop_confirm_partner')), '/').'".*?name="reason"/s', $html));
        $this->assertStringContainsString(e(marketing_url('/docs/boost#partners-campaigns')), $html);
        $campaign->forceFill(['status' => 'completed'])->save();
        $html = $this->actingAs($this->partnerUser)->get(route('boost.partner.campaigns'))->assertOk()->getContent();
        $this->assertSame(0, substr_count($html, 'data-bp-open="stop"'), 'nothing to stop once it has ended');
        $campaign->forceFill(['status' => 'active'])->save();

        // The filters narrow the list and the export follows them.
        $html = $this->actingAs($this->partnerUser)->get(route('boost.partner.campaigns', ['status' => 'completed', 'unpaid' => 1]))->assertOk()->getContent();
        $this->assertStringContainsString(__('messages.boost_partner_no_campaigns_match'), $html);
        $this->assertStringContainsString(e(route('boost.partner.export', ['status' => 'completed', 'unpaid' => 1])), $html);
    }

    public function test_statements_say_the_charge_and_offer_pay_now_when_it_failed(): void
    {
        // Pay now needs a card on file (without one the page points at Update card only).
        $this->partner->forceFill(['stripe_payment_method_id' => 'pm_test', 'stripe_customer_id' => 'cus_test'])->save();
        $campaign = $this->submit();
        $statement = BoostStatement::create([
            'boost_partner_id' => $this->partner->id,
            'period' => '2026-09',
            'currency' => 'USD',
            'total_spend' => 46,
            'total_fee' => 9.2,
            'status' => 'failed',
        ]);
        BoostStatementLine::create([
            'boost_statement_id' => $statement->id,
            'boost_campaign_id' => $campaign->id,
            'period' => '2026-09',
            'spend' => 46,
            'fee' => 9.2,
        ]);
        // Facebook corrected August after it was billed: a line for the earlier month, no words.
        BoostStatementLine::create([
            'boost_statement_id' => $statement->id,
            'boost_campaign_id' => $campaign->id,
            'period' => '2026-08',
            'spend' => 0,
            'fee' => 0,
            'adjustment' => -1.5,
        ]);

        $html = $this->actingAs($this->partnerUser)->get(route('boost.partner.statements'))->assertOk()->getContent();
        $this->assertStringContainsString(__('messages.boost_statement_this_month'), $html);
        $this->assertStringContainsString('href="'.e(route('boost.partner.statement', ['period' => '2026-09', 'currency' => 'USD'])).'"', $html);
        $this->assertStringContainsString(__('messages.boost_statement_state_failed'), $html);
        $this->assertStringContainsString('$9.20', $html);

        $html = $this->actingAs($this->partnerUser)->get(route('boost.partner.statement', ['period' => '2026-09', 'currency' => 'USD']))->assertOk()->getContent();
        $this->assertStringContainsString('action="'.route('boost.partner.statement_pay', ['period' => '2026-09']).'"', $html);
        $this->assertStringContainsString('<input type="hidden" name="currency" value="USD">', $html, 'Pay now names the statement\'s currency');
        $this->assertStringContainsString(e(__('messages.boost_statement_correction', ['month' => \Carbon\Carbon::createFromFormat('Y-m-d', '2026-08-01')->translatedFormat('F Y')])), $html);
        $this->assertSame(1, substr_count($html, e(__('messages.boost_statement_correction', ['month' => '']))), 'only the line for an earlier month is a correction');
        $this->assertStringContainsString(__('messages.pay_now'), $html);
        $this->assertStringContainsString('href="'.route('profile.edit').'#section-boost-card"', $html);
        $this->assertStringContainsString('America/New_York', $html, 'the account\'s timezone');
        $this->assertStringContainsString('Friday Jazz Night', $html, 'a line per campaign');
    }

    public function test_the_partner_answers_from_the_boosts_own_page(): void
    {
        $campaign = $this->submit();
        $html = $this->actingAs($this->partnerUser)->get(route('boost.show', ['hash' => $campaign->hashedId()]))->assertOk()->getContent();

        $this->assertStringContainsString('data-bp-open="approve"', $html);
        $this->assertStringContainsString('data-bp-open="decline"', $html);
        $this->assertStringContainsString('data-bp-open="payment"', $html);
        $this->assertStringContainsString('name="reason"', $html, 'Stop, with a reason');
        $this->assertStringContainsString('href="'.route('boost.partner.requests').'#request-'.$campaign->hashedId().'" class="page-back"', $html);
        $this->assertStringNotContainsString('href="'.route('boost.edit', ['hash' => $campaign->hashedId()]).'"', $html, 'the partner does not edit a Business\'s ad');
    }

    public function test_the_boosts_settings_tab_is_a_partners_alone(): void
    {
        $html = $this->actingAs($this->partnerUser)
            ->withSession(['boost_partner_checks' => ['ad_account_shared' => true, 'page_shared' => false, 'ad_account_active' => true]])
            ->get(route('profile.edit'))->assertOk()->getContent();

        $this->assertSame(1, preg_match('/id="section-boost" class="section-content/', $html));
        $this->assertSame(1, preg_match('/id="section-boost-account" class="settings-block"/', $html));
        $this->assertSame(1, preg_match('/id="section-boost-card" class="settings-block"/', $html));
        $this->assertStringContainsString('4455667788', $html, 'our Business ID, to copy');
        $this->assertStringContainsString('data-bp-copy="bp-business-id"', $html);
        $this->assertStringContainsString('href="https://business.facebook.com/settings/ad-accounts"', $html);
        $this->assertStringContainsString('href="https://business.facebook.com/settings/pages"', $html);
        $this->assertStringContainsString('action="'.route('boost.partner.settings.share').'"', $html);
        $this->assertStringContainsString('action="'.route('boost.partner.settings.check').'"', $html);
        $this->assertStringContainsString('action="'.route('boost.partner.settings.instructions').'"', $html);
        $this->assertStringContainsString(e(__('messages.boost_partner_check_page_shared_fail')), $html, 'each check, with what fixes it');
        $this->assertStringContainsString(__('messages.boost_partner_check_ad_account_shared_ok'), $html);
        $this->assertStringContainsString('Network Ads', $html);
        $this->assertStringNotContainsString(route('boost.partner.settings.login'), $html, 'Login waits on its configuration');

        // Every place on the tab has a Help page.
        $anchors = (new \ReflectionClass(\App\Utils\HelpUtils::class))->getStaticPropertyValue('mappings')['settings']['anchors'];
        foreach (['section-boost', 'section-boost-account', 'section-boost-card'] as $id) {
            $this->assertArrayHasKey($id, $anchors);
        }

        config(['services.meta.login_config_id' => 'cfg_1', 'services.meta.app_id' => 'app_1']);
        $html = $this->actingAs($this->partnerUser)->get(route('profile.edit'))->assertOk()->getContent();
        $this->assertStringContainsString('href="'.route('boost.partner.settings.login').'"', $html);

        // Somebody who is not a partner has no such tab.
        $html = $this->actingAs($this->business)->get(route('profile.edit'))->assertOk()->getContent();
        $this->assertStringNotContainsString('id="section-boost', $html);
    }

    public function test_an_admin_confirms_a_shared_account_and_enables_partners(): void
    {
        $waiting = $this->makePartner($this->createOwner(), ['confirmed_at' => null, 'ad_account_business_name' => 'Coastal Media Group']);
        $admin = $this->createOwner(true);

        $html = $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($admin)->get(route('admin.boost'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'id="boost-partners"'));
        $this->assertStringContainsString('Coastal Media Group', $html, 'the owning Business beside the account');
        $this->assertSame(1, substr_count($html, 'action="'.route('admin.boost.partners.confirm', ['partner' => $waiting->id]).'"'), 'Confirm for the one waiting');
        $this->assertSame(0, substr_count($html, 'action="'.route('admin.boost.partners.confirm', ['partner' => $this->partner->id]).'"'), 'not for one confirmed');
        $this->assertSame(2, substr_count($html, 'action="'.route('admin.boost.partners.disable', ['partner' => $waiting->id]).'"') + substr_count($html, 'action="'.route('admin.boost.partners.disable', ['partner' => $this->partner->id]).'"'));
        $this->assertStringContainsString('action="'.route('admin.boost.partners.enable').'"', $html);

        // Enabling waits on its switch.
        config(['services.meta.partners_enabled' => false]);
        $html = $this->withSession(['admin_password_confirmed_at' => now()->timestamp])
            ->actingAs($admin)->get(route('admin.boost'))->assertOk()->getContent();
        $this->assertStringNotContainsString('action="'.route('admin.boost.partners.enable').'"', $html);
        $this->assertSame(1, substr_count($html, 'id="boost-partners"'), 'the partners already enabled are still listed');
    }

    public function test_the_event_form_and_the_sidebar_lead_to_the_request(): void
    {
        $campaign = $this->submit();
        $editUrl = route('event.edit', ['subdomain' => $this->role->subdomain, 'hash' => UrlUtils::encodeId($this->event->id)]);

        $html = $this->actingAs($this->business)->get($editUrl)->assertOk()->getContent();
        $label = e(__('messages.boost_partner_event_button', ['status' => __('messages.boost_partner_status_pending_approval')]));
        $this->assertSame(2, substr_count($html, $label), 'on the toolbar and in the phone\'s Actions menu');
        $this->assertSame(2, substr_count($html, 'href="'.route('boost.show', ['hash' => $campaign->hashedId()]).'"'));

        // The partner's sidebar leads to Requests and says how many wait, in words too.
        $html = $this->actingAs($this->partnerUser)->get(route('boost.partner.campaigns'))->assertOk()->getContent();
        $this->assertSame(2, substr_count($html, '<span class="sr-only">'.trans_choice('messages.boost_partner_nav_waiting', 1, ['count' => 1]).'</span>'), 'in the drawer and the rail');
        $this->assertGreaterThanOrEqual(3, substr_count($html, 'href="'.route('boost.partner.requests').'"'), 'the sidebar twice, and the Requests tab');
        $this->assertStringNotContainsString('href="'.route('boost.index').'"', $html, 'one Boost entry');

        // The Business's /boost dialog knows the event goes to the partner.
        $html = $this->actingAs($this->business)->get(route('boost.index'))->assertOk()->getContent();
        $this->assertStringContainsString('"partner":true', $html);
        // This site's own Meta account is not set up here, and the partner's Page still is.
        $this->assertStringContainsString('v-if="selectedEvent && selectedEvent.partner"', $html);
    }

    // ------------------------------------------------------- what the week's review found

    public function test_a_boost_declined_after_it_ran_stays_in_campaigns(): void
    {
        $ran = $this->submit();
        $ran->forceFill(['status' => 'declined', 'launched_at' => now()->subDays(3), 'meta_campaign_id' => 'm1', 'actual_spend' => 46])->save();

        $later = $this->createEvent($this->role, ['name' => 'Saturday Brunch', 'creator_role_id' => $this->role->id, 'starts_at' => now()->addDays(12)->setTime(12, 0)->format('Y-m-d H:i:s'), 'flyer_image_url' => 'demo_flyer_dj.jpg']);
        $neverRan = $this->submit($later, ['headline' => 'Brunch by the sea']);
        $neverRan->forceFill(['status' => 'declined'])->save();

        $html = $this->actingAs($this->partnerUser)->get(route('boost.partner.campaigns'))->assertOk()->getContent();

        // It spent the partner's money and is on its statement.
        $this->assertStringContainsString('href="'.route('boost.show', ['hash' => $ran->hashedId()]).'"', $html);
        // A request that was never at Meta is not a campaign.
        $this->assertStringNotContainsString('href="'.route('boost.show', ['hash' => $neverRan->hashedId()]).'"', $html);
    }

    public function test_check_again_without_a_token_is_a_failed_check_not_an_error_page(): void
    {
        // Meta's deauthorize webhook clears a login partner's token.
        $this->partner->forceFill(['connection_method' => BoostPartner::METHOD_LOGIN, 'meta_token' => null])->save();

        $this->actingAs($this->partnerUser)->post(route('boost.partner.settings.check'))
            ->assertRedirect()
            ->assertSessionHas('error', __('messages.boost_partner_check_failed'));

        $this->assertNotNull($this->partner->fresh()->connection_error);
    }

    /** What Meta answers a login: these Pages and ad accounts, and the two the partner uses. */
    private function fakeMetaLogin(array $pageIds, array $accountIds, ?string $meId = '424242'): void
    {
        config(['services.meta.app_id' => '1234', 'services.meta.app_secret' => 'app-secret-xyz']);

        Http::fake(function ($request) use ($pageIds, $accountIds, $meId) {
            $url = $request->url();

            return match (true) {
                str_contains($url, '/oauth/access_token') => Http::response(['access_token' => 'FRESH_TOKEN', 'expires_in' => 5184000]),
                str_contains($url, '/me/accounts') => Http::response(['data' => array_map(fn ($id) => ['id' => $id, 'name' => 'Page '.$id], $pageIds)]),
                str_contains($url, '/me/adaccounts') => Http::response(['data' => array_map(fn ($id) => ['id' => 'act_'.$id, 'name' => 'Ads '.$id, 'currency' => 'USD'], $accountIds)]),
                (bool) preg_match('#/me(\?|$)#', $url) => $meId !== null ? Http::response(['id' => $meId]) : Http::response(['error' => ['message' => 'Service temporarily unavailable']], 503),
                str_contains($url, '/act_999888777') => Http::response(['name' => 'Network Ads', 'currency' => 'USD', 'timezone_name' => 'America/New_York', 'account_status' => 1]),
                str_contains($url, '/111222333') => Http::response(['name' => 'Harbor Events Page']),
                default => Http::response(['error' => ['message' => 'unexpected '.$url]], 400),
            };
        });
    }

    private function loginAgain()
    {
        return $this->actingAs($this->partnerUser)
            ->withSession(['boost_partner_oauth_state' => 'st8'])
            ->get(route('boost.partner.settings.callback', ['state' => 'st8', 'code' => 'c0de']));
    }

    private function runningLoginBoost(): BoostCampaign
    {
        $this->partner->forceFill(['connection_method' => BoostPartner::METHOD_LOGIN, 'meta_token' => 'OLD_TOKEN'])->save();
        $live = $this->submit();
        $live->forceFill(['status' => 'active', 'launched_at' => now(), 'meta_campaign_id' => 'm1'])->save();

        return $live;
    }

    public function test_logging_in_again_keeps_the_account_and_the_boosts_running_on_it(): void
    {
        $live = $this->runningLoginBoost();
        $this->fakeMetaLogin(['111222333', '555000555'], ['999888777', '666000666']);

        $this->loginAgain()->assertRedirect()
            ->assertSessionHas('message', __('messages.boost_partner_checks_passed'))
            ->assertSessionHas('boost_partner_assets');

        $partner = $this->partner->fresh();
        $this->assertSame('999888777', (string) $partner->ad_account_id);
        $this->assertSame('111222333', (string) $partner->page_id);
        $this->assertSame('FRESH_TOKEN', $partner->accessToken());
        $this->assertNotNull($partner->confirmed_at);
        $this->assertSame('active', $live->fresh()->status);

        // Another pair is still offered, and taken only once nothing runs on the current one.
        $this->actingAs($this->partnerUser)
            ->post(route('boost.partner.settings.select'), ['page_id' => '555000555', 'ad_account_id' => '666000666'])
            ->assertRedirect()->assertSessionHas('error', __('messages.boost_partner_disconnect_blocked'));
        $this->assertSame('999888777', (string) $this->partner->fresh()->ad_account_id);
    }

    public function test_logging_in_again_to_other_assets_waits_while_a_boost_runs(): void
    {
        $this->runningLoginBoost();
        $this->fakeMetaLogin(['555000555'], ['666000666']);

        $this->loginAgain()->assertRedirect()->assertSessionHas('error', __('messages.boost_partner_disconnect_blocked'));

        // Cleared, the running boost would be read and paused with a token that cannot reach it.
        $partner = $this->partner->fresh();
        $this->assertSame('999888777', (string) $partner->ad_account_id);
        $this->assertSame('OLD_TOKEN', $partner->accessToken());
    }

    public function test_a_login_that_times_out_never_reports_the_app_secret(): void
    {
        Exceptions::fake();
        config(['services.meta.app_id' => '1234', 'services.meta.app_secret' => 'app-secret-xyz']);
        Http::fake(['*' => Http::failedConnection('cURL error 28: Operation timed out for https://graph.facebook.com/v21.0/oauth/access_token?client_id=1234&client_secret=app-secret-xyz&code=c0de')]);

        $this->loginAgain()->assertRedirect()->assertSessionHas('error', __('messages.boost_partner_login_failed'));

        // One report, and it is the cleaned one. (A closure typed \Throwable in assertNotReported()
        // matches no class at all, so it would pass whatever was reported.)
        Exceptions::assertReportedCount(1);
        Exceptions::assertReported(fn (\RuntimeException $e) => str_contains($e->getMessage(), 'oauth/access_token?[query removed]')
            && ! str_contains($e->getMessage(), 'app-secret-xyz'));
    }

    /** Laravel turns five cURL errors into a ConnectionException; the rest arrive as Guzzle's own. */
    public function test_a_login_broken_off_some_other_way_never_reports_the_app_secret(): void
    {
        Exceptions::fake();
        config(['services.meta.app_id' => '1234', 'services.meta.app_secret' => 'app-secret-xyz']);
        Http::fake(fn () => throw new \GuzzleHttp\Exception\TransferException('cURL error 56: Connection reset by peer for https://graph.facebook.com/v21.0/oauth/access_token?client_id=1234&client_secret=app-secret-xyz&code=c0de'));

        $this->loginAgain()->assertRedirect()->assertSessionHas('error', __('messages.boost_partner_login_failed'));

        Exceptions::assertReportedCount(1);
        Exceptions::assertReported(fn (\RuntimeException $e) => str_contains($e->getMessage(), 'Connection reset by peer')
            && ! str_contains($e->getMessage(), 'app-secret-xyz') && ! str_contains($e->getMessage(), 'c0de'));
    }

    public function test_a_login_whose_facebook_user_cannot_be_read_is_refused(): void
    {
        $this->runningLoginBoost();
        $this->partner->forceFill(['meta_user_id' => '111'])->save();
        // Meta answers the token exchange and the asset lists, and not /me.
        $this->fakeMetaLogin(['111222333'], ['999888777'], null);

        $this->loginAgain()->assertRedirect()->assertSessionHas('error', __('messages.boost_partner_login_failed'));

        // Stored under the previous person's id, the token would outlive its own owner's
        // withdrawal (forgetMetaUser()) and be wiped by somebody else's.
        $partner = $this->partner->fresh();
        $this->assertSame('OLD_TOKEN', $partner->accessToken());
        $this->assertSame('111', (string) $partner->meta_user_id);
    }

    public function test_a_share_cannot_name_another_partners_or_the_platforms_account_or_page(): void
    {
        config(['services.meta.ad_account_id' => 'act_70000001', 'services.meta.page_id' => '80000001']);
        $other = $this->makePartner($this->createOwner(), ['page_id' => '555000555', 'ad_account_id' => '666000666']);
        $save = fn (string $page, string $account) => $this->actingAs($this->partnerUser)
            ->post(route('boost.partner.settings.share'), ['page_id' => $page, 'ad_account_id' => $account]);

        // Another partner's ad account, or its Page.
        $save('121212121', '666000666')->assertRedirect()->assertSessionHasErrors(['ad_account_id' => __('messages.boost_partner_ids_taken')]);
        $save('555000555', '343434343')->assertRedirect()->assertSessionHasErrors(['ad_account_id' => __('messages.boost_partner_ids_taken')]);
        // The platform's own.
        $save('121212121', '70000001')->assertRedirect()->assertSessionHasErrors(['ad_account_id' => __('messages.boost_partner_ids_taken')]);
        $save('80000001', '343434343')->assertRedirect()->assertSessionHasErrors(['ad_account_id' => __('messages.boost_partner_ids_taken')]);

        $partner = $this->partner->fresh();
        $this->assertSame('999888777', (string) $partner->ad_account_id, 'nothing was saved');
        $this->assertSame('111222333', (string) $partner->page_id);
        $this->assertSame([], MetaAdsServiceFake::$calls, 'nothing was read from Meta with the platform\'s token');
        $this->assertSame('666000666', (string) $other->fresh()->ad_account_id);

        // Its own new pair is taken.
        $save('121212121', '343434343')->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('343434343', (string) $this->partner->fresh()->ad_account_id);
    }

    public function test_the_boost_forms_card_step_charges_nothing_for_a_partners_event(): void
    {
        $this->actingAs($this->business)
            ->postJson(route('boost.payment_intent'), ['event_id' => UrlUtils::encodeId($this->event->id), 'budget' => 50])
            ->assertStatus(422)
            ->assertJson(['error' => __('messages.boost_partner_promotion_refused', ['page' => $this->partner->displayName()])]);
    }

    public function test_a_boost_sent_after_the_partner_was_switched_on_gives_its_card_payment_back(): void
    {
        // The form was opened before the partner was, and its card was confirmed for this site.
        $this->mock(\App\Services\BoostBillingService::class)
            ->shouldReceive('abandonPaymentIntent')->once()
            ->with('pi_3QxCharged', 'boost_partner_request', 'boost_abandon_', $this->event->id, 'boost', $this->business->id);

        $this->actingAs($this->business)->post(route('boost.store'), [
            'event_id' => UrlUtils::encodeId($this->event->id),
            'role_id' => UrlUtils::encodeId($this->role->id),
            'budget' => 50,
            'budget_type' => 'lifetime',
            'payment_intent_id' => 'pi_3QxCharged',
            'headline' => 'Friday Jazz',
            'primary_text' => 'Live trio from 7 PM',
        ])->assertRedirect();

        $this->assertSame(1, BoostCampaign::where('event_id', $this->event->id)->whereNotNull('boost_partner_id')->count(), 'a request to the partner');
    }
}
