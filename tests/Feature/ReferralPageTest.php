<?php

namespace Tests\Feature;

use App\Models\Referral;
use App\Models\User;
use App\Utils\PlatformPricing;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * /referrals as the person using it reads it, rebuilt in October 2026: four figures that left out
 * a credit waiting to be spent, a credit applied without a word on what it would do, and a status
 * column that never said what came next.
 *
 * Assertions count matches with substr_count()/preg_match() and never hand the whole page to a
 * pattern assertion: a failure would print the whole page.
 */
class ReferralPageTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
    }

    private function page(User $user): string
    {
        return $this->actingAs($user)->get(route('referrals'))->assertOk()->getContent();
    }

    private function refer(User $referrer, string $status, array $attrs = []): Referral
    {
        $id = DB::table('referrals')->insertGetId(array_merge([
            'referrer_user_id' => $referrer->id,
            'referred_user_id' => $this->createOwner()->id,
            'status' => $status,
            'created_at' => now(),
            'updated_at' => now(),
        ], $attrs));

        return Referral::find($id);
    }

    private function day(Carbon $date): string
    {
        return $date->translatedFormat('M j, Y');
    }

    private function subscribe(int $roleId): void
    {
        DB::table('subscriptions')->insert([
            'role_id' => $roleId,
            'type' => 'default',
            'stripe_id' => 'sub_'.Str::random(14),
            'stripe_status' => 'active',
            'stripe_price' => 'price_test',
            'quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_a_new_referrer_is_told_how_it_works_and_shown_no_zeros(): void
    {
        $owner = $this->createOwner();

        $html = $this->page($owner);

        // The reward comes first, with both amounts in the installation's currency.
        $this->assertSame(1, substr_count($html, e(__('messages.referral_share_headline'))));
        $this->assertStringContainsString(plan_price(PlatformPricing::proMonthly()), $html);
        $this->assertStringContainsString(plan_price(PlatformPricing::enterpriseMonthly()), $html);

        // The track is How it works, with nothing counted yet.
        $this->assertSame(1, substr_count($html, 'id="referral-dashboard"'));
        $this->assertSame(1, substr_count($html, 'v-pre>'.e(__('messages.how_it_works')).'</h2>'));
        $this->assertSame(4, substr_count($html, '<li class="referral-step'));
        $this->assertSame(0, substr_count($html, 'class="referral-step-count'));

        $this->assertSame(0, substr_count($html, 'id="referral-credits"'));
        $this->assertSame(0, substr_count($html, 'id="referral-history"'));
    }

    public function test_each_referral_is_counted_once_at_the_step_it_is_on(): void
    {
        $owner = $this->createOwner();
        $hall = $this->createRole($owner, 'venue', ['name' => 'Blue Note']);

        $soonest = now()->subDays(20);
        $this->refer($owner, 'pending');
        $this->refer($owner, 'pending');
        $this->refer($owner, 'subscribed', ['plan_type' => 'pro', 'subscribed_at' => $soonest]);
        $this->refer($owner, 'subscribed', ['plan_type' => 'pro', 'subscribed_at' => now()->subDays(5)]);
        $this->refer($owner, 'qualified', ['plan_type' => 'pro', 'subscribed_at' => now()->subDays(40), 'qualified_at' => now()->subDays(9)]);
        $this->refer($owner, 'credited', ['plan_type' => 'pro', 'credited_role_id' => $hall->id, 'credited_at' => now()]);
        $this->refer($owner, 'expired');

        $html = $this->page($owner);

        $this->assertSame(1, substr_count($html, 'v-pre>'.e(__('messages.your_referrals')).'</h2>'));
        // In the order of the steps: waiting to subscribe, in their 30 days, earned, applied.
        preg_match_all('/<span class="referral-step-count[^"]*">(\d+)<\/span>/', $html, $counts);
        $this->assertSame(['2', '2', '1', '1'], $counts[1]);
        $this->assertSame(1, substr_count($html, e(__('messages.referral_track_total', ['count' => 7]))));
        $this->assertSame(1, substr_count($html, e(__('messages.referral_track_expired', ['count' => 1]))));
        // The credit that comes first is the one that started paying first.
        $this->assertSame(1, substr_count($html, e(__('messages.referral_next_credit', ['date' => $this->day($soonest->copy()->addDays(30))]))));
    }

    public function test_the_history_says_where_each_referral_stands_and_what_comes_next(): void
    {
        $owner = $this->createOwner();
        $hall = $this->createRole($owner, 'venue', ['name' => 'Credited Hall']);

        $signedUp = now()->subDays(10);
        $subscribedAt = now()->subDays(12);
        $creditedAt = now()->subDays(3);
        $this->refer($owner, 'pending', ['created_at' => $signedUp]);
        $this->refer($owner, 'subscribed', ['plan_type' => 'enterprise', 'subscribed_at' => $subscribedAt, 'created_at' => now()->subDays(14)]);
        $this->refer($owner, 'qualified', ['plan_type' => 'pro', 'subscribed_at' => now()->subDays(40), 'qualified_at' => now()->subDays(9)]);
        $this->refer($owner, 'credited', ['plan_type' => 'pro', 'credited_role_id' => $hall->id, 'credited_at' => $creditedAt]);
        $this->refer($owner, 'expired', ['created_at' => now()->subDays(100)]);
        $this->refer($owner, 'expired', ['plan_type' => 'pro', 'subscribed_at' => now()->subDays(50), 'created_at' => now()->subDays(60)]);

        $html = $this->page($owner);

        $this->assertSame(1, substr_count($html, e(__('messages.referral_detail_pending', ['date' => $this->day($signedUp->copy()->addDays(90))]))));
        $this->assertSame(1, substr_count($html, e(__('messages.referral_detail_subscribed', ['date' => $this->day($subscribedAt->copy()->addDays(30))]))));
        $this->assertSame(1, substr_count($html, 'href="#referral-credits"'));
        // The schedule's name is its owner's text, set apart for a right-to-left page.
        $applied = e(__('messages.referral_detail_credited', ['schedule' => '%SCHEDULE%', 'date' => $this->day($creditedAt)]));
        $this->assertSame(1, substr_count($html, str_replace('%SCHEDULE%', '<bdi>Credited Hall</bdi>', $applied)));
        // Two ways to expire, each said in its own words.
        $this->assertSame(1, substr_count($html, e(__('messages.referral_detail_never_subscribed'))));
        $this->assertSame(1, substr_count($html, e(__('messages.referral_detail_ended_early'))));

        // The words of the steps, never "Qualified" or "Credited".
        $this->assertSame(1, substr_count($html, '<span class="event-status is-on">'.e(__('messages.referral_status_credited')).'</span>'));
        $this->assertSame(1, substr_count($html, '<span class="event-status is-warn">'.e(__('messages.referral_status_qualified')).'</span>'));
        $this->assertSame(0, substr_count($html, '>'.e(__('messages.qualified')).'<'));
        $this->assertSame(0, substr_count($html, '>'.e(__('messages.credited')).'<'));
    }

    public function test_the_picker_says_what_the_credit_will_do_on_each_schedule(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue', ['name' => 'Free Hall', 'plan_type' => 'free', 'plan_expires' => null]);
        $this->createRole($owner, 'venue', ['name' => 'Granted Hall', 'plan_type' => 'enterprise', 'plan_expires' => now()->addDays(10)->format('Y-m-d')]);
        $paying = $this->createRole($owner, 'venue', ['name' => 'Paying Hall', 'plan_type' => 'pro', 'plan_expires' => null]);
        $this->subscribe($paying->id);
        $this->refer($owner, 'qualified', ['plan_type' => 'pro', 'subscribed_at' => now()->subDays(40), 'qualified_at' => now()->subDays(9)]);

        $html = $this->page($owner);

        $outcome = fn (string $key, array $replace) => 'data-outcome="'.e(__('messages.'.$key, $replace)).'"';
        $this->assertSame(1, substr_count($html, $outcome('referral_outcome_moves', [
            'schedule' => 'Free Hall', 'plan' => __('messages.pro'), 'date' => $this->day(now()->addDays(30)),
        ])));
        // A Pro credit does not take a schedule off Enterprise: it adds the month to it.
        $this->assertSame(1, substr_count($html, $outcome('referral_outcome_stays', [
            'schedule' => 'Granted Hall', 'plan' => __('messages.enterprise'), 'date' => $this->day(now()->addDays(40)),
        ])));
        $this->assertSame(1, substr_count($html, $outcome('referral_outcome_invoice', [
            'amount' => plan_price(PlatformPricing::proMonthly()), 'schedule' => 'Paying Hall',
        ])));
        // Three to choose from, so none is chosen for them.
        $this->assertSame(0, preg_match('/<option[^>]*data-outcome[^>]*\bselected\b/', $html));
    }

    public function test_one_schedule_is_chosen_for_them_and_none_offers_to_make_one(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'venue', ['name' => 'Only Hall', 'plan_type' => 'free', 'plan_expires' => null]);
        $this->refer($owner, 'qualified', ['plan_type' => 'pro', 'subscribed_at' => now()->subDays(40), 'qualified_at' => now()->subDays(9)]);

        $html = $this->page($owner);

        $this->assertSame(1, preg_match('/<option[^>]*\bselected\b[^>]*>Only Hall<\/option>/', $html));
        // What it will do is said before anything is pressed.
        $sentence = e(__('messages.referral_outcome_moves', ['schedule' => 'Only Hall', 'plan' => __('messages.pro'), 'date' => $this->day(now()->addDays(30))]));
        $this->assertSame(1, preg_match('/<p class="referral-outcome"[^>]*>'.preg_quote($sentence, '/').'<\/p>/', $html));

        $nobody = $this->createOwner();
        $this->refer($nobody, 'qualified', ['plan_type' => 'pro', 'subscribed_at' => now()->subDays(40), 'qualified_at' => now()->subDays(9)]);

        $html = $this->page($nobody);

        $this->assertSame(1, substr_count($html, 'id="referral-credits"'));
        $this->assertSame(0, substr_count($html, 'name="referral_id"'));
        foreach (['talent', 'venue', 'curator'] as $type) {
            $this->assertSame(1, preg_match_all('/href="'.preg_quote(e(route('new', ['type' => $type])), '/').'"\s+class="[^"]*\breferral-make\b/', $html), $type);
        }
    }

    public function test_a_pro_credit_never_takes_a_schedule_off_enterprise(): void
    {
        $owner = $this->createOwner();
        $granted = $this->createRole($owner, 'venue', [
            'name' => 'Granted Hall',
            'plan_type' => 'enterprise',
            'plan_expires' => now()->addDays(10)->format('Y-m-d'),
            'plan_source' => 'admin',
        ]);
        $free = $this->createRole($owner, 'venue', ['name' => 'Free Hall', 'plan_type' => 'free', 'plan_expires' => null, 'plan_source' => null]);
        $first = $this->refer($owner, 'qualified', ['plan_type' => 'pro', 'subscribed_at' => now()->subDays(40), 'qualified_at' => now()->subDays(9)]);
        $second = $this->refer($owner, 'qualified', ['plan_type' => 'pro', 'subscribed_at' => now()->subDays(40), 'qualified_at' => now()->subDays(9)]);

        $response = $this->actingAs($owner)
            ->post(route('referrals.apply_credit'), [
                'referral_id' => UrlUtils::encodeId($first->id),
                'role_id' => UrlUtils::encodeId($granted->id),
            ]);

        $granted->refresh();
        $this->assertSame('enterprise', $granted->plan_type);
        $this->assertSame(now()->addDays(40)->format('Y-m-d'), $granted->plan_expires);
        $this->assertSame('admin', $granted->plan_source);
        $this->assertSame('credited', $first->fresh()->status);
        $response->assertRedirect(route('referrals'))
            ->assertSessionHas('message', __('messages.referral_credit_applied').' '.__('messages.referral_outcome_stays', [
                'schedule' => 'Granted Hall', 'plan' => __('messages.enterprise'), 'date' => $this->day(now()->addDays(40)),
            ]));

        // A schedule on no plan moves onto the credit's, from today.
        $this->actingAs($owner)
            ->post(route('referrals.apply_credit'), [
                'referral_id' => UrlUtils::encodeId($second->id),
                'role_id' => UrlUtils::encodeId($free->id),
            ])
            ->assertSessionHas('message', __('messages.referral_credit_applied').' '.__('messages.referral_outcome_moves', [
                'schedule' => 'Free Hall', 'plan' => __('messages.pro'), 'date' => $this->day(now()->addDays(30)),
            ]));

        $free->refresh();
        $this->assertSame('pro', $free->plan_type);
        $this->assertSame(now()->addDays(30)->format('Y-m-d'), $free->plan_expires);
        $this->assertSame('referral', $free->plan_source);
    }
}
