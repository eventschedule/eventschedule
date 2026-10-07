<?php

namespace Tests\Feature;

use App\Models\BoostCampaign;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use App\Services\PromotionBillingService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The Boost pages of the admin portal, after they were rebuilt on the portal's page kit
 * (October 2026): the list of campaigns, a campaign's page on either channel, and the three ways
 * to start one (boost/create, boost/create-advanced, and boost/create-network behind
 * /promotions/create).
 *
 * Each test is something a person could see or be stopped by: a list drawn as cards with four
 * looks of status pill, a button that opened a dialog with no way on, a page whose script threw on
 * its first line, a slider with one stop, an ad's button in English whatever the language, a date
 * box that was never given its picker. What the pages look like belongs to the screenshots.
 *
 * The name says Analytics because the two were planned as one area. /analytics was being
 * restructured by another piece of work that night and was left alone, so nothing here reads it.
 *
 * Assertions count matches with substr_count()/preg_match() and never hand the whole page to a
 * pattern assertion: a failure would print the page.
 */
class ApBoostAnalyticsPagesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // No page here may reach the network: the suite loads the developer's .env, where real
        // tokens live, and a page that asks an outside service (the Domains page asked
        // DigitalOcean) would do it for real on every run. A request nothing faked throws.
        \Illuminate\Support\Facades\Http::preventStrayRequests();

        // A selfhosted install with Meta connected: every schedule may boost, nothing is charged.
        config([
            'app.hosted' => false,
            'app.is_testing' => true,
            'services.meta.access_token' => 'test-token',
        ]);
        Cache::flush();
    }

    /** @return array{0: User, 1: Role, 2: \App\Models\Event} */
    private function advertiser(array $roleAttrs = []): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['name' => 'The Vinyl Room']);

        if ($roleAttrs !== []) {
            $role->forceFill($roleAttrs)->save();
            $role = $role->fresh();
        }

        $event = $this->createEvent($role, ['name' => 'Halloween Warehouse Party', 'starts_at' => now()->addDays(20)]);
        $role->events()->updateExistingPivot($event->id, ['is_accepted' => true]);

        return [$owner, $role, $event];
    }

    private function campaign(User $owner, Role $role, $event, array $attrs = []): BoostCampaign
    {
        $campaign = new BoostCampaign;
        $campaign->forceFill($attrs + [
            'event_id' => $event->id,
            'role_id' => $role->id,
            'user_id' => $owner->id,
            'channel' => 'meta',
            'name' => 'Campaign',
            'status' => 'active',
            'currency_code' => 'USD',
            'user_budget' => 150,
            'markup_rate' => 0,
        ])->save();

        return $campaign->fresh();
    }

    private function createUrl(Role $role, $event, array $extra = []): string
    {
        return route('boost.create', ['event_id' => UrlUtils::encodeId($event->id), 'role_id' => UrlUtils::encodeId($role->id)] + $extra);
    }

    /** The hosted service keeps its signed-in pages on the app. host once it is not testing. */
    private function appUrl(string $url): string
    {
        return preg_replace('~^(https?://)~', '$1app.', $url, 1);
    }

    /**
     * The campaigns are one list, a table that stacks on a phone, and a campaign's state is the
     * portal's status mark from one map. They were cards, and the list, the two campaign pages
     * and each state had pill colours of their own.
     */
    public function test_the_campaigns_are_one_list_with_one_status_mark(): void
    {
        [$owner, $role, $event] = $this->advertiser();
        $active = $this->campaign($owner, $role, $event, ['actual_spend' => 96.42, 'impressions' => 15480, 'clicks' => 327]);
        $waiting = $this->campaign($owner, $role, $event, [
            'channel' => 'network', 'status' => 'pending_review', 'moderation_status' => 'pending', 'pricing_model' => 'cpm',
            'unit_rate_micros' => PromotionBillingService::toMicros(2.00), 'budget_micros' => PromotionBillingService::toMicros(150),
        ]);
        $rejected = $this->campaign($owner, $role, $event, ['status' => 'rejected', 'currency_code' => 'EUR', 'user_budget' => 50]);
        $this->campaign($owner, $role, $event, ['status' => 'completed']);

        $html = $this->actingAs($owner)->get(route('boost.index'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, '<table class="page-table'), 'one list');
        $this->assertSame(1, substr_count($html, '<h1 class="page-title"'), 'the page opens with the title row');
        foreach ([$active, $waiting, $rejected] as $campaign) {
            $this->assertSame(1, substr_count($html, 'href="'.route('boost.show', ['hash' => $campaign->hashedId()]).'"'), 'each campaign once');
        }

        $this->assertSame(1, substr_count($html, 'class="event-status is-on">'.__('messages.boost_status_active')));
        $this->assertSame(1, substr_count($html, 'class="event-status is-warn">'.__('messages.boost_status_pending_review')));
        $this->assertSame(1, substr_count($html, 'class="event-status is-bad">'.__('messages.boost_status_rejected')));
        $this->assertSame(1, substr_count($html, 'class="event-status ">'.__('messages.boost_status_completed')), 'a finished campaign is quiet');
        $this->assertStringNotContainsString('rounded-full px-2.5', $html, 'no pills of the page\'s own');

        // Money is the campaign's own: the currency it was bought in.
        $this->assertStringContainsString('$96.42', $html);
        $this->assertStringContainsString('€50.00', $html);

        // And a campaign's own page says the same state with the same mark.
        foreach ([[$active, 'is-on'], [$waiting, 'is-warn'], [$rejected, 'is-bad']] as [$campaign, $tone]) {
            $page = $this->actingAs($owner)->get(route('boost.show', ['hash' => $campaign->hashedId()]))->assertOk()->getContent();
            $this->assertSame(1, substr_count($page, 'class="event-status '.$tone.'">'), $campaign->status);
        }
    }

    /**
     * The way to start a campaign is offered where a channel exists to carry one. With neither
     * Meta nor the network set up, the button opened a dialog that had an event picker and no
     * button to go on with.
     */
    public function test_boost_event_is_offered_only_where_a_channel_exists(): void
    {
        [$owner] = $this->advertiser();

        $with = $this->actingAs($owner)->get(route('boost.index'))->assertOk()->getContent();
        $this->assertSame(1, substr_count($with, 'id="boost-modal-app"'));
        $this->assertStringNotContainsString(__('messages.boost_not_set_up'), $with);

        config(['services.meta.access_token' => null]);

        $without = $this->actingAs($owner)->get(route('boost.index'))->assertOk()->getContent();
        $this->assertStringNotContainsString('id="boost-modal-app"', $without);
        $this->assertSame(1, substr_count($without, __('messages.boost_not_set_up')));
    }

    /**
     * Somebody whose schedules are all on the free plan is told Boost is part of Pro, for a
     * schedule by name. They used to get an empty list and a dialog saying they had no upcoming
     * events, which they did.
     */
    public function test_a_free_schedule_is_told_what_boost_needs(): void
    {
        $owner = $this->createOwner();
        $free = $this->createFreeRole($owner, 'venue');
        $this->createEvent($free, ['starts_at' => now()->addDays(5)]);
        $this->assertFalse($free->fresh()->isPro());

        $html = $this->actingAs($owner)->get(route('boost.index'))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, __('messages.boost_plan_gate')));
        $this->assertStringContainsString(e(route('role.subscribe', ['subdomain' => $free->subdomain, 'tier' => 'pro'])), $html);
        $this->assertStringNotContainsString('id="boost-modal-app"', $html, 'no button that leads to an empty dialog');
        $this->assertStringNotContainsString(__('messages.no_boost_campaigns'), $html, 'the gate is the page');

        // One paid schedule beside it and the page is the list again.
        $paid = $this->createRole($owner, 'talent');
        $html = $this->actingAs($owner)->get(route('boost.index'))->assertOk()->getContent();
        $this->assertStringNotContainsString(__('messages.boost_plan_gate'), $html);
        $this->assertSame(1, substr_count($html, 'id="boost-modal-app"'));

        // Narrowed to the free one, the gate is back, about that schedule.
        $html = $this->actingAs($owner)->get(route('boost.index', ['role_id' => UrlUtils::encodeId($free->id)]))->assertOk()->getContent();
        $this->assertSame(1, substr_count($html, __('messages.boost_plan_gate')));
        $this->assertTrue($paid->fresh()->isPro());
    }

    /**
     * The quick form's scripts read the form by element id. On the page that says the limit of
     * running campaigns is reached there is no form, and they threw on their first line.
     */
    public function test_the_quick_form_sends_its_scripts_only_with_the_form(): void
    {
        config(['services.meta.max_concurrent_boosts' => 1]);
        [$owner, $role, $event] = $this->advertiser();

        $open = $this->actingAs($owner)->get($this->createUrl($role, $event))->assertOk()->getContent();
        $this->assertSame(1, substr_count($open, 'id="boost-form"'));
        $this->assertSame(1, substr_count($open, "getElementById('budget-slider')"));
        // One preview of the ad, placed by the stylesheet at either width. It was drawn twice.
        $this->assertSame(1, substr_count($open, 'class="boost-cols-aside"'));
        $this->assertSame(1, substr_count($open, __('messages.sponsored')));
        // The way back is the page this one hangs from, and Cancel stands before the button that
        // spends the money.
        $this->assertSame(1, substr_count($open, 'class="page-back"'));
        $this->assertLessThan(strpos($open, 'id="submit-btn"'), strpos($open, 'js-cancel-btn"'));

        $this->campaign($owner, $role, $event, ['status' => 'active']);

        $full = $this->actingAs($owner)->get($this->createUrl($role, $event))->assertOk()->getContent();
        $this->assertSame(1, substr_count($full, __('messages.boost_max_concurrent')));
        $this->assertStringNotContainsString('id="boost-form"', $full);
        $this->assertStringNotContainsString('budget-slider', $full, 'no script reaching for a form that is not there');
    }

    /**
     * A new schedule's spending limit on the hosted service is the smallest budget there is, so
     * the slider had one stop. It is not drawn then; the element stays, because the script reads
     * the budget from it.
     */
    public function test_a_slider_with_one_stop_is_not_drawn(): void
    {
        config(['app.hosted' => true, 'services.meta.boost_default_limit' => 10, 'services.meta.min_budget' => 10]);
        [$owner, $role, $event] = $this->advertiser();

        $fixed = $this->actingAs($owner)->get($this->createUrl($role, $event))->assertOk()->getContent();
        $this->assertSame(1, preg_match('/<input type="range" id="budget-slider"[^>]*\shidden\s*>/', $fixed));

        $role->forceFill(['boost_max_budget' => 250])->save();

        $free = $this->actingAs($owner)->get($this->createUrl($role, $event))->assertOk()->getContent();
        $this->assertSame(1, preg_match('/<input type="range" id="budget-slider"[^>]*max="250"[^>]*>/', $free, $slider));
        $this->assertStringNotContainsString('hidden', $slider[0]);
    }

    /**
     * The advanced form asks Stripe for a card only where a card is charged. In testing it called
     * Stripe with no key, which threw and took the interest search and the totals with it. Its
     * dates use the portal's picker, not the browser's own date box.
     */
    public function test_the_advanced_form_asks_for_a_card_only_where_one_is_charged(): void
    {
        config(['app.hosted' => true]);
        [$owner, $role, $event] = $this->advertiser(['boost_max_budget' => 250]);

        $testing = $this->actingAs($owner)->get($this->createUrl($role, $event, ['advanced' => 1]))->assertOk()->getContent();
        $this->assertStringNotContainsString('js.stripe.com', $testing);
        $this->assertStringNotContainsString('Stripe(', $testing);
        $this->assertSame(1, substr_count($testing, __('messages.boost_testing_mode')));
        // The fee is the hosted service's whether or not a card is asked for.
        $this->assertSame(1, substr_count($testing, 'id="review-fee"'));
        $this->assertStringNotContainsString('type="date"', $testing);
        $this->assertSame(2, substr_count($testing, 'class="mt-1 boost-date '));
        $this->assertSame(1, preg_match('/name="scheduled_start" id="scheduled-start" value="\d{4}-\d{2}-\d{2}"/', $testing), 'what is sent is still Y-m-d');

        config(['app.is_testing' => false, 'services.stripe_platform.key' => 'pk_test_x']);
        $owner->forceFill(['phone_verified_at' => now()])->save();

        $live = $this->actingAs($owner)->get($this->appUrl($this->createUrl($role, $event, ['advanced' => 1])))->assertOk()->getContent();
        $this->assertSame(1, substr_count($live, 'js.stripe.com/v3'));
        $this->assertSame(1, substr_count($live, 'id="payment-element"'));
        $this->assertStringNotContainsString(__('messages.boost_testing_mode'), $live);
    }

    /**
     * The button on the ad's preview is in the reader's language. It was the constant Meta is
     * sent, with its underscore taken out: "GET TICKETS" in every language.
     */
    public function test_the_ad_preview_says_its_button_in_the_readers_language(): void
    {
        app()->setLocale('es');

        $html = view('boost.partials.ad-preview-mockup', [
            'headline' => 'Fiesta', 'primaryText' => 'Ven', 'imageUrl' => null, 'cta' => 'GET_TICKETS',
        ])->render();

        $this->assertStringContainsString(__('messages.cta_get_tickets'), $html);
        $this->assertStringNotContainsString('GET TICKETS', $html);
        $this->assertNotSame('Get Tickets', __('messages.cta_get_tickets'), 'the fixture is a language that says it differently');

        // A constant the map does not know still reads as words.
        $html = view('boost.partials.ad-preview-mockup', [
            'headline' => 'x', 'primaryText' => 'y', 'imageUrl' => null, 'cta' => 'WATCH_MORE',
        ])->render();
        $this->assertStringContainsString('Watch More', $html);
    }

    /**
     * What can be done to a campaign is in its title row, on either channel, and cancelling says
     * what it cancels. The network page ended with a red button that read only "Cancel", beside
     * one that read "Back".
     */
    public function test_a_campaigns_actions_are_in_its_title_row(): void
    {
        [$owner, $role, $event] = $this->advertiser();
        $meta = $this->campaign($owner, $role, $event, ['meta_campaign_id' => 'm1']);
        $network = $this->campaign($owner, $role, $event, [
            'channel' => 'network', 'moderation_status' => 'approved', 'pricing_model' => 'cpm',
            'unit_rate_micros' => PromotionBillingService::toMicros(2.00), 'budget_micros' => PromotionBillingService::toMicros(150),
        ]);

        foreach ([$meta, $network] as $campaign) {
            $html = $this->actingAs($owner)->get(route('boost.show', ['hash' => $campaign->hashedId()]))->assertOk()->getContent();

            $this->assertSame(1, preg_match('/<header class="page-top">(.*?)<\/header>/s', $html, $head), $campaign->channel);
            $this->assertSame(1, substr_count($head[1], 'action="'.route('boost.cancel', ['hash' => $campaign->hashedId()]).'"'));
            $this->assertSame(1, substr_count($head[1], 'action="'.route('boost.toggle_pause', ['hash' => $campaign->hashedId()]).'"'));
            $this->assertSame(1, substr_count($head[1], 'data-confirm="'.e(__('messages.boost_cancel_confirm')).'"'));
            $this->assertSame(1, substr_count($head[1], __('messages.cancel_campaign')));
            $this->assertSame(1, substr_count($head[1], 'href="'.route('boost.index').'" class="page-back"'), 'the way back is Boost, by name');
            // Nowhere else on the page.
            $this->assertSame(1, substr_count($html, route('boost.cancel', ['hash' => $campaign->hashedId()])));
            $this->assertSame(1, substr_count($html, 'page-stats is-auto'), 'the figures are one strip');
            $this->assertStringNotContainsString(__('messages.back_to_boost'), $html);
        }
    }

    /**
     * The two dates of the network form carried the date picker's class, and nothing started it:
     * they were bare text boxes, and the rule that keeps the end after the start read a picker
     * that was not there. The page starts both now.
     */
    public function test_the_promotion_form_starts_its_date_pickers(): void
    {
        config(['app.hosted' => true, 'ads.enabled' => true, 'app.is_nexus' => false]);
        Setting::set('ads_native_enabled', '1');
        Cache::flush();
        [$owner, $role, $event] = $this->advertiser(['boost_max_budget' => 1000]);

        $html = $this->actingAs($owner)->get(route('promotions.create', [
            'event_id' => UrlUtils::encodeId($event->id),
            'role_id' => UrlUtils::encodeId($role->id),
        ]))->assertOk()->getContent();

        $this->assertSame(1, substr_count($html, 'flatpickr(end, options)'));
        $this->assertSame(1, substr_count($html, 'flatpickr(start, '));
        $this->assertSame(1, substr_count($html, "endPicker.set('minDate', value"), 'choosing a start moves the earliest end');
        // One preview, and the form's parts each under a name.
        $this->assertSame(1, substr_count($html, 'class="boost-cols-aside"'));
        $this->assertSame(1, substr_count($html, '<h1 class="page-title"'));
        $this->assertGreaterThanOrEqual(4, substr_count($html, 'class="page-card-title" v-pre'), 'headings inside the Vue mount are not compiled');
    }
}
