<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\TicketTrialEnding;
use App\Models\Role;
use App\Services\DemoService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The card-free selling trial (roles.ticket_trial_ends_at), started from the event editor's
 * paid-ticket paywall.
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

    private function start(Role $role)
    {
        return $this->postJson(route('subscription.ticket_trial', ['subdomain' => $role->subdomain]));
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

        $this->actingAs($owner);
        $this->start($role)->assertOk()->assertJson(['ok' => true]);

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

        $this->actingAs($owner);
        $this->start($role)->assertOk();

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

        $this->actingAs($owner);
        $this->start($role)->assertOk();

        $this->travel(8)->days();

        $this->assertFalse($role->fresh()->onTicketTrial());
        $this->assertFalse($ticket->fresh()->isSellable(),
            'no grandfathering: a seven-day trial must not become permanent selling on the event');
    }

    /** Once per OWNER. isEligibleForTrial() is per schedule, so a second schedule was a second trial. */
    public function test_a_second_schedule_does_not_get_a_second_trial(): void
    {
        $owner = $this->createOwner();
        $first = $this->createFreeRole($owner);
        $second = $this->createFreeRole($owner);

        $this->actingAs($owner);
        $this->start($first)->assertOk();
        $this->start($second)->assertStatus(422)->assertJson(['ok' => false]);

        $this->assertNull($second->fresh()->ticket_trial_ends_at);
    }

    /** Nor does the same schedule once its trial has run out. */
    public function test_an_ended_trial_cannot_be_restarted(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);

        $this->actingAs($owner);
        $this->start($role)->assertOk();
        $this->travel(8)->days();

        $this->start($role)->assertStatus(422);
        $this->assertFalse($role->fresh()->onTicketTrial());
    }

    /**
     * Deleting a schedule removes its row outright (Role has no soft deletes), so a stamp kept
     * only on the schedule let trial, delete, recreate grant it again, forever.
     */
    public function test_deleting_the_trialled_schedule_does_not_reset_the_trial(): void
    {
        $owner = $this->createOwner();
        $first = $this->createFreeRole($owner);

        $this->actingAs($owner);
        $this->start($first)->assertOk();
        $this->assertNotNull($owner->fresh()->ticket_trial_used_at);

        $first->delete();
        $second = $this->createFreeRole($owner);

        $this->assertFalse($second->fresh()->isEligibleForTicketTrial());
        $this->start($second)->assertStatus(422);
    }

    /** A declined card or abandoned 3DS leaves an incomplete row; that is not "has paid". */
    public function test_a_failed_checkout_does_not_block_the_trial(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $role->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_'.Str::random(14),
            'stripe_status' => 'incomplete_expired',
            'stripe_price' => 'price_test_monthly',
            'quantity' => 1,
        ]);

        $this->assertTrue($role->fresh()->isEligibleForTicketTrial());
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

    /** Someone who has already had a subscription has nothing left to try. */
    public function test_a_past_subscriber_is_not_offered_the_trial(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $role->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_'.Str::random(14),
            'stripe_status' => 'canceled',
            'stripe_price' => 'price_test_monthly',
            'quantity' => 1,
            'ends_at' => now()->subMonth(),
        ]);

        $this->assertFalse($role->fresh()->isEligibleForTicketTrial());

        $this->actingAs($owner);
        $this->start($role)->assertStatus(422);
    }

    /** Checkout is owner-only, and so is this. */
    public function test_an_editor_who_is_not_the_owner_cannot_start_it(): void
    {
        $role = $this->createFreeRole();
        $editor = $this->createOwner();
        $role->users()->attach($editor->id, ['level' => 'admin']);

        $this->actingAs($editor);
        $this->start($role)->assertForbidden();

        $this->assertNull($role->fresh()->ticket_trial_ends_at);
    }

    /** The demo schedule already sells; it is never offered anything to buy or try. */
    public function test_the_demo_schedule_is_not_eligible(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner, 'venue', ['subdomain' => DemoService::DEMO_ROLE_SUBDOMAIN]);

        $this->assertFalse($role->isEligibleForTicketTrial());
    }

    /** A Pro schedule can already sell. */
    public function test_a_pro_schedule_is_not_eligible(): void
    {
        $this->assertFalse($this->createRole($this->createOwner())->isEligibleForTicketTrial());
    }

    public function test_the_editor_offers_the_trial_to_an_eligible_owner(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        [$event] = $this->pricedEvent($role);

        $html = $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('messages.ticket_trial_start', ['days' => 7]), $html);
        $this->assertStringContainsString('startTicketTrial', $html);
    }

    public function test_the_editor_does_not_offer_it_twice(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        [$event] = $this->pricedEvent($role);
        $role->forceFill(['ticket_trial_ends_at' => now()->subDay()])->save();

        $html = $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString(__('messages.tickets_need_pro_title'), $html);
        $this->assertStringNotContainsString(__('messages.ticket_trial_start', ['days' => 7]), $html);
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

    /**
     * The dashboard's "paid tickets cannot be sold" to-do links to the plan tab, so the trial has
     * to be on offer there too. A plain form: nothing unsaved to lose on that page.
     */
    public function test_the_plan_tab_offers_the_trial_and_starts_it_with_a_redirect(): void
    {
        $owner = $this->createOwner();
        $role = $this->createFreeRole($owner);
        $planUrl = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan']);

        $this->actingAs($owner)->get($planUrl)->assertOk()
            ->assertSee(__('messages.ticket_trial_start', ['days' => 7]))
            ->assertSee(route('subscription.ticket_trial', ['subdomain' => $role->subdomain]), false);

        $this->from($planUrl)
            ->post(route('subscription.ticket_trial', ['subdomain' => $role->subdomain]))
            ->assertRedirect($planUrl)
            ->assertSessionHas('message', __('messages.ticket_trial_started', ['days' => 7]));

        $this->assertTrue($role->fresh()->onTicketTrial());
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
        $keys = ['ticket_trial_start', 'ticket_trial_started', 'ticket_trial_unavailable', 'ticket_trial_days_left',
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
