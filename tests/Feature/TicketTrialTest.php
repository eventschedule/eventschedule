<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\TicketTrialEnding;
use App\Models\Role;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The card-free selling trial (roles.ticket_trial_ends_at), started from the event editor's
 * paid-ticket paywall until it was retired on 2026-10-20 for one trial per login, at checkout
 * (RoleBillable::checkoutTrialEnd(), TrialOncePerLoginTest). Nothing starts one now; what is
 * pinned here is that the trials already running keep working until they end, and do not add
 * a second trial on top when their owner subscribes.
 *
 * It is deliberately NOT a Pro trial. roles.trial_ends_at would make isPro() true and unlock every
 * Pro extra, and some of those outlive a trial in a buyer's hands: a pass stops booking and an
 * installment plan stops collecting the day the schedule stops being Pro. So the half of this file
 * that matters most is the negative one - the trial opens paid selling and nothing else.
 */
class TicketTrialTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true, 'app.trial_days' => 7]);
    }

    /** A selling trial as one started before the retirement left it. */
    private function start(Role $role): void
    {
        $role->forceFill(['ticket_trial_ends_at' => now()->addDays(7)])->save();
        DB::table('users')->where('id', $role->user_id)->update(['ticket_trial_used_at' => now()]);
    }

    /** A free schedule with an event carrying one priced row. */
    private function pricedEvent(Role $role): array
    {
        $event = $this->createEvent($role, [
            'tickets_enabled' => true,
            'payment_method' => 'stripe',
            'creator_role_id' => $role->id,
        ]);
        $ticket = $this->createTicket($event, ['price' => 20, 'quantity' => 100]);

        return [$event, $ticket];
    }

    public function test_the_trial_opens_paid_selling(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        [$event, $ticket] = $this->pricedEvent($role);

        $this->assertFalse($ticket->fresh()->isSellable(), 'sanity: a free schedule cannot sell it');

        $this->start($role);

        $role->refresh();
        $this->assertTrue($role->onTicketTrial());
        $this->assertSame(7, $role->ticketTrialDaysRemaining());
        $this->assertTrue($role->canSellPaidTickets());
        $this->assertTrue($event->fresh()->canSellPaidTickets());
        // isSellable() is what both the web checkout and POST /api/sales ask per row.
        $this->assertTrue($ticket->fresh()->isSellable());
    }

    /** The negative half: nothing but paid selling. */
    public function test_the_trial_is_not_a_pro_trial(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        [$event] = $this->pricedEvent($role);

        $this->start($role);

        $role->refresh();
        $this->assertFalse($role->isPro(), 'passes, installments, add-ons and the rest stay on isPro()');
        $this->assertFalse($role->onGenericTrial(), 'roles.trial_ends_at is Cashier\'s Pro trial and must stay untouched');
        $this->assertFalse($event->fresh()->hasProTicketingPlan());
        $this->assertSame('free', $role->actualPlanTier());
    }

    public function test_the_trial_ends_and_selling_stops(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        [, $ticket] = $this->pricedEvent($role);

        $this->start($role);

        $this->travel(8)->days();

        $this->assertFalse($role->fresh()->onTicketTrial());
        $this->assertFalse($ticket->fresh()->isSellable(),
            'no grandfathering: a seven-day trial must not become permanent selling on the event');
    }

    /** Nothing starts one any more: the route is gone, and with it every way in. */
    public function test_nothing_starts_a_selling_trial_any_more(): void
    {
        $this->assertFalse(\Illuminate\Support\Facades\Route::has('subscription.ticket_trial'));
        $this->assertFalse(method_exists(Role::class, 'isEligibleForTicketTrial'));
    }

    /**
     * Subscribing while a selling trial runs keeps its end date: the plan's first charge is the
     * day the selling trial ends, never a fresh trial week on top of it (the "I paid and got
     * another trial" an owner wrote in about).
     */
    public function test_checkout_during_a_running_selling_trial_ends_with_it(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $this->start($role);
        $role->refresh();

        $this->assertFalse($role->isEligibleForTrial(), 'the login has had its trial');
        $this->assertSame($role->ticket_trial_ends_at->toDateTimeString(), $role->checkoutTrialEnd()?->toDateTimeString());
    }

    /** Once it has ended, checkout charges today, on this schedule and on any other. */
    public function test_an_ended_selling_trial_means_checkout_charges_today(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $this->start($role);
        $other = $this->createFreeRole($owner);

        $this->travel(8)->days();

        $this->assertNull($role->fresh()->checkoutTrialEnd());
        $this->assertNull($other->fresh()->checkoutTrialEnd());
    }

    /**
     * Subscribing mid-trial leaves ticket_trial_ends_at set. The countdown must go, or a paying
     * schedule is told its trial ends in 5 days beside an Upgrade link that bounces.
     */
    public function test_a_schedule_that_subscribes_mid_trial_no_longer_shows_the_trial(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner); // Pro
        $role->forceFill(['ticket_trial_ends_at' => now()->addDays(5)])->save();

        $this->assertFalse($role->fresh()->onTicketTrial());
        $this->assertTrue($role->fresh()->canSellPaidTickets());

        $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan']))
            ->assertOk()
            ->assertDontSee(__('messages.ticket_trial_title'));
    }

    /** The paywall saves the event and goes on to checkout, which comes back to it. */
    public function test_the_editor_offers_save_and_start_free_trial(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $role->forceFill(['plan_expires' => null])->save();
        [$event] = $this->pricedEvent($role);

        $html = $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('messages.save_and_start_free_trial'), $html);
        $this->assertStringContainsString('saveThenSubscribe', $html);
        $this->assertStringNotContainsString('startTicketTrial', $html);
    }

    /** A login that has had its trial is not offered one it would be charged for. */
    public function test_the_editor_does_not_offer_a_trial_twice(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        [$event] = $this->pricedEvent($role);
        $role->forceFill(['ticket_trial_ends_at' => now()->subDay()])->save();
        $owner->forceFill(['ticket_trial_used_at' => now()->subDays(8)])->save();

        $html = $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('messages.tickets_need_pro_title'), $html);
        $this->assertStringContainsString(__('messages.save_and_upgrade'), $html);
        $this->assertStringNotContainsString(__('messages.save_and_start_free_trial'), $html);
    }

    /** While the trial runs the editor has nothing to block, and says how long is left. */
    public function test_a_running_trial_removes_the_paywall_from_the_editor(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        [$event] = $this->pricedEvent($role);
        $role->forceFill(['ticket_trial_ends_at' => now()->addDays(3)->addHour()])->save();

        $html = $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString(__('messages.tickets_need_pro_title'), $html);
        $this->assertStringContainsString(trans_choice('messages.ticket_trial_days_left', 4, ['count' => 4]), $html);
    }

    /** The Plan tab offers the plan and its trial, and no second, card-free one. */
    public function test_the_plan_tab_no_longer_offers_the_card_free_trial(): void
    {
        $owner = $this->createOwner();
        // createFreeRole() leaves an expired legacy plan_expires, which rules a trial out.
        $role = $this->createFreeRole($owner);
        $role->forceFill(['plan_expires' => null])->save();

        $html = $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan']))
            ->assertOk()
            ->getContent();

        $this->assertStringNotContainsString('/ticket-trial', $html);
        $this->assertStringContainsString(e(__('messages.plan_trial_note', ['days' => 7])), $html);
    }

    /** The dashboard to-do "these paid tickets cannot be sold" is false while the trial runs. */
    public function test_the_dashboard_does_not_say_trialing_tickets_cannot_sell(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $this->pricedEvent($role);

        // The dashboard reads the 'userRoles' container singleton, which a real request builds
        // afresh but which outlives a request inside one test, carrying the first call's Role
        // models into the second.
        $types = function () use ($owner) {
            $this->app->forgetInstance('userRoles');

            return collect($this->actingAs($owner->fresh())->get(route('home'))->assertOk()->viewData('pendingActionItems') ?? [])
                ->pluck('type')->all();
        };

        $this->assertContains('ticket_quota', $types(), 'sanity: a free schedule is told');

        $role->forceFill(['ticket_trial_ends_at' => now()->addDays(3)])->save();

        $this->assertNotContains('ticket_quota', $types());
    }

    public function test_the_ending_reminders_go_out_once_per_window(): void
    {
        Queue::fake();

        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $role->forceFill(['ticket_trial_ends_at' => now()->addDays(3)->setTime(15, 0)])->save();

        $this->artisan('app:send-subscription-reminders')->assertExitCode(0);
        $this->artisan('app:send-subscription-reminders')->assertExitCode(0);

        Queue::assertPushed(SendQueuedEmail::class, 1);
        Queue::assertPushed(SendQueuedEmail::class, function ($job) {
            $mailable = new \ReflectionProperty($job, 'mailable');
            $roleId = new \ReflectionProperty($job, 'roleId');

            // Platform mailer: a schedule's own SMTP must not carry, meter or drop a billing notice.
            return $mailable->getValue($job) instanceof TicketTrialEnding && $roleId->getValue($job) === null;
        });

        // The last day is its own window.
        $this->travel(2)->days();
        $this->artisan('app:send-subscription-reminders')->assertExitCode(0);

        Queue::assertPushed(SendQueuedEmail::class, 2);
    }

    /** The end date is the owner's own calendar day, not UTC's. */
    public function test_the_reminder_names_the_owners_local_end_date(): void
    {
        Queue::fake();

        $owner = $this->createOwner();
        $owner->forceFill(['timezone' => 'America/Los_Angeles'])->save();
        $role = $this->createFreeRole($owner->fresh());
        $ends = now()->utc()->addDays(3)->setTime(2, 0);
        $role->forceFill(['ticket_trial_ends_at' => $ends])->save();

        $this->artisan('app:send-subscription-reminders')->assertExitCode(0);

        Queue::assertPushed(SendQueuedEmail::class, function ($job) use ($ends) {
            $mailable = (new \ReflectionProperty($job, 'mailable'))->getValue($job);
            $endDate = (new \ReflectionProperty($mailable, 'endDate'))->getValue($mailable);

            return $endDate === $ends->copy()->subDay()->format('F j, Y');
        });
    }

    /** Someone who subscribed during the trial is not told their selling is about to stop. */
    public function test_a_schedule_that_subscribed_is_not_reminded(): void
    {
        Queue::fake();

        $role = $this->createRole($this->createOwner());
        $role->forceFill(['ticket_trial_ends_at' => now()->addDays(3)->setTime(15, 0)])->save();

        $this->artisan('app:send-subscription-reminders')->assertExitCode(0);

        Queue::assertNothingPushed();
        $this->assertNull(DB::table('roles')->where('id', $role->id)->value('ticket_trial_reminder_sent_at'));
    }

    public function test_the_reminder_renders_in_every_language(): void
    {
        $role = $this->createFreeRole();
        $role->forceFill(['ticket_trial_ends_at' => now()->addDays(3)])->save();

        foreach (array_keys(config('app.supported_languages')) as $locale) {
            app()->setLocale($locale);
            $html = (new TicketTrialEnding($role->fresh(), 'October 1, 2026'))->render();

            $this->assertStringNotContainsString('messages.', $html, "{$locale} is missing a key");
            $this->assertStringContainsString(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan']), $html);
        }
    }

    /** Every language has its own copy, not the English fallback __() would silently render. */
    public function test_every_language_translates_the_trial_copy(): void
    {
        $keys = ['ticket_trial_days_left',
            'ticket_trial_title', 'ticket_trial_ending_subject', 'ticket_trial_ending_body', 'ticket_trial_ending_buyers',
            'plan_gate_ask_owner', 'audit_ticket_trial_started', 'funnel_stage_hit_ticket_paywall'];
        $english = require base_path('resources/lang/en/messages.php');

        foreach (array_keys(config('app.supported_languages')) as $locale) {
            if ($locale === 'en') {
                continue;
            }
            $messages = require base_path("resources/lang/{$locale}/messages.php");

            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $messages, "{$locale} is missing {$key}");
                $this->assertNotSame($english[$key], $messages[$key], "{$locale}.{$key} is still English");
            }
        }
    }
}
