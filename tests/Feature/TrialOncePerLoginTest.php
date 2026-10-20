<?php

namespace Tests\Feature;

use App\Http\Controllers\SubscriptionController;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * One free trial per login, and saying what a plan covers where the money is asked for.
 *
 * An owner wrote in that they paid when their trial ended and were then given "another trial
 * week", and that a second schedule for an event at a new address asked for payment again. Both
 * were true: the plan's trial was once per SCHEDULE and stacked on the card-free selling trial,
 * and nothing on the checkout page, the Plan tab or the event editor said that a plan covers one
 * schedule or when the card would be charged. RoleBillable::checkoutTrialEnd() is now the one
 * answer the checkout page and the checkout itself read.
 */
class TrialOncePerLoginTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // The editor offers checkout and goes on to it only with a platform Stripe key, which a
        // developer's .env has and CI's does not.
        config(['app.hosted' => true, 'app.trial_days' => 7, 'cashier.key' => 'pk_test_fixture']);
    }

    /** A free schedule as a real one is: no legacy plan_expires (createFreeRole() leaves an expired one). */
    private function freeSchedule(User $owner, string $type = 'venue', array $attrs = []): Role
    {
        $role = $this->createFreeRole($owner, $type, $attrs);
        $role->forceFill(['plan_expires' => null])->save();

        return $role->fresh();
    }

    private function subscribe(Role $role, string $status = 'trialing'): void
    {
        $role->forceFill(['stripe_id' => 'cus_'.Str::random(14), 'plan_type' => 'pro'])->save();
        $role->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_'.Str::random(14),
            'stripe_status' => $status,
            'stripe_price' => config('services.stripe_platform.price_monthly') ?: 'price_test_monthly',
            'quantity' => 1,
            'trial_ends_at' => $status === 'trialing' ? now()->addDays(5) : null,
        ]);
    }

    public function test_a_login_that_has_had_a_trial_is_charged_today_on_its_next_schedule(): void
    {
        $owner = $this->createOwner();
        $first = $this->freeSchedule($owner);

        $this->assertTrue($first->isEligibleForTrial());
        $this->assertSame(7, $first->checkoutTrialDays());

        $owner->forceFill(['trial_used_at' => now()])->save();

        $second = $this->freeSchedule($owner);
        $this->assertFalse($second->isEligibleForTrial(), 'once per login, not per schedule');
        $this->assertNull($second->checkoutTrialEnd(), 'null is "charged today"');
    }

    /** The stamp is on the login, so deleting the schedule that had the trial gives nothing back. */
    public function test_deleting_the_schedule_that_had_the_trial_does_not_reset_it(): void
    {
        $owner = $this->createOwner();
        $first = $this->freeSchedule($owner);
        $owner->forceFill(['trial_used_at' => now()])->save();
        $first->delete();

        $this->assertFalse($this->freeSchedule($owner)->isEligibleForTrial());
    }

    /**
     * Other schedules are not asked: one transferred in keeps its old subscription rows, and must
     * not spend the trial of the person it was handed to.
     */
    public function test_a_schedule_transferred_in_does_not_spend_the_new_owners_trial(): void
    {
        $previous = $this->createOwner();
        $transferred = $this->freeSchedule($previous);
        $this->subscribe($transferred, 'canceled');

        $owner = $this->createOwner();
        $transferred->forceFill(['user_id' => $owner->id])->save();

        $this->assertTrue($this->freeSchedule($owner)->isEligibleForTrial());
        $this->assertFalse($transferred->fresh()->isEligibleForTrial(), 'that schedule has had a plan of its own');
    }

    public function test_the_demo_schedule_and_a_selfhost_install_offer_no_trial(): void
    {
        $owner = $this->createOwner();
        $demo = $this->freeSchedule($owner, 'venue', ['subdomain' => \App\Services\DemoService::DEMO_ROLE_SUBDOMAIN]);
        $this->assertFalse($demo->isEligibleForTrial());

        $role = $this->freeSchedule($owner);
        config(['app.hosted' => false]);
        $this->assertFalse($role->fresh()->isEligibleForTrial());
    }

    /**
     * Stamped only once the subscription exists: a declined card or an abandoned 3DS step leaves
     * an incomplete one, and that must not spend the trial.
     */
    public function test_an_incomplete_checkout_does_not_spend_the_trial(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeSchedule($owner);
        $this->subscribe($role, 'incomplete');

        $markIfStarted = new \ReflectionMethod(SubscriptionController::class, 'markTrialUsedIfStarted');
        $markIfStarted->invoke(new SubscriptionController, $role->fresh());
        $this->assertNull($owner->fresh()->trial_used_at);

        $role->subscriptions()->update(['stripe_status' => 'trialing']);
        $markIfStarted->invoke(new SubscriptionController, $role->fresh());
        $this->assertNotNull($owner->fresh()->trial_used_at);
    }

    /** The backfill: an owner who has had a subscription that started has had their trial. */
    public function test_the_migration_backfills_owners_who_had_a_plan(): void
    {
        $paid = $this->createOwner();
        $this->subscribe($this->freeSchedule($paid), 'active');
        $declined = $this->createOwner();
        $this->subscribe($this->freeSchedule($declined), 'incomplete_expired');

        // The backfill statement alone: the column is already there.
        DB::table('users')
            ->whereNull('trial_used_at')
            ->whereIn('id', DB::table('roles')->select('roles.user_id')->whereNotNull('roles.user_id')
                ->whereExists(fn ($q) => $q->select(DB::raw(1))->from('subscriptions')
                    ->whereColumn('subscriptions.role_id', 'roles.id')
                    ->whereNotIn('subscriptions.stripe_status', ['incomplete', 'incomplete_expired'])))
            ->update(['trial_used_at' => now()]);

        $this->assertNotNull($paid->fresh()->trial_used_at);
        $this->assertNull($declined->fresh()->trial_used_at);

        $migration = file_get_contents(database_path('migrations/2026_10_20_000000_add_trial_used_at_to_users_table.php'));
        $this->assertStringContainsString("->whereNotIn('subscriptions.stripe_status', ['incomplete', 'incomplete_expired'])", $migration,
            'the statement above is the migration\'s own');
    }

    /** The checkout page says what pressing the button does, and the button says it too. */
    public function test_the_checkout_page_says_when_the_card_is_charged(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeSchedule($owner, 'venue', ['name' => 'Harbour Hall']);

        $html = $this->checkoutPage($owner, $role);
        $this->assertStringContainsString(e(__('messages.subscribe_covers_one_schedule', ['name' => 'Harbour Hall'])), $html);
        $this->assertStringContainsString(__('messages.start_free_trial'), $html);
        $price = plan_price(\App\Utils\PlatformPricing::amount('pro', 'monthly'));
        $this->assertStringContainsString(e(__('messages.subscribe_trial_charge_line', ['date' => now()->addDays(7)->translatedFormat('F j, Y'), 'amount' => $price.'/'.__('messages.month')])), $html);

        // The login has had its trial, on a schedule it can name.
        $other = $this->freeSchedule($owner, 'talent', ['name' => 'Night Owls']);
        $this->subscribe($other, 'active');
        $owner->forceFill(['trial_used_at' => now()])->save();

        $this->assertNull($role->fresh()->checkoutTrialEnd());
        $html = $this->checkoutPage($owner, $role->fresh());
        $this->assertStringContainsString(e(__('messages.subscribe_trial_used_on', ['name' => 'Night Owls', 'amount' => $price])), $html);
        $this->assertStringNotContainsString(__('messages.start_free_trial'), $html);
        $this->assertStringContainsString(e(__('messages.subscribe_pay_amount', ['amount' => plan_price(\App\Utils\PlatformPricing::amount('pro', 'monthly'))])), $html);

        // A schedule whose own plan is why: it is named, not another one.
        $this->subscribe($role->fresh(), 'canceled');
        $html = $this->checkoutPage($owner, $role->fresh());
        $this->assertStringContainsString(e(__('messages.subscribe_trial_used_here', ['name' => 'Harbour Hall', 'amount' => $price])), $html);
        $this->assertStringNotContainsString('Night Owls', $html);
    }

    /** A name holding the placeholder cannot pull the price into itself. */
    public function test_a_schedule_name_holding_the_placeholder_keeps_the_price_where_it_belongs(): void
    {
        $owner = $this->createOwner();
        $owner->forceFill(['trial_used_at' => now()])->save();
        $other = $this->freeSchedule($owner, 'talent', ['name' => 'Pay :amount club']);
        $this->subscribe($other, 'active');
        $role = $this->freeSchedule($owner);

        $html = $this->checkoutPage($owner, $role);
        $price = plan_price(\App\Utils\PlatformPricing::amount('pro', 'monthly'));
        $this->assertStringContainsString(e('on the "Pay :amount club" schedule, so you pay '.$price.' today.'), $html);
    }

    /** The view as SubscriptionController::show() hands it over; rendering the route needs Stripe. */
    private function checkoutPage(User $owner, Role $role): string
    {
        $this->actingAs($owner);
        $controller = new SubscriptionController;
        $usedOn = new \ReflectionMethod($controller, 'scheduleThatHadTheTrial');
        // The layout is a component, which sees what the factory shares, not the page's data.
        view()->share('errors', new \Illuminate\Support\ViewErrorBag);

        return view('subscription.show', [
            'role' => $role,
            'intent' => (object) ['client_secret' => 'seti_test_secret'],
            'monthlyPrice' => null,
            'yearlyPrice' => null,
            'selectedTier' => 'pro',
            'enterpriseConfigured' => false,
            'checkoutSource' => null,
            'trialEnd' => $role->checkoutTrialEnd(),
            'trialUsedHere' => ! $role->checkoutTrialEnd() && (new \ReflectionMethod($controller, 'hadAPlanHere'))->invoke($controller, $role),
            'trialUsedOn' => $role->checkoutTrialEnd() ? null : $usedOn->invoke($controller, $role),
            'returnEvent' => null,
        ])->render();
    }

    /** "Trial" alone read as "another trial": the Plan tab says nothing has been charged, and what will be. */
    public function test_the_plan_tab_says_nothing_has_been_charged_during_the_trial(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeSchedule($owner);
        $this->subscribe($role);
        $role->forceFill(['pm_type' => 'visa', 'pm_last_four' => '4242'])->save();

        $html = $this->actingAs($owner)
            ->get(route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'plan']))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-trial-charge', $html);
        $this->assertStringContainsString(e(__('messages.plan_trial_charge_line', [
            'last4' => '4242',
            'amount' => plan_price(\App\Utils\PlatformPricing::amount('pro', 'monthly')),
            'date' => now()->addDays(5)->translatedFormat('F j, Y'),
        ])), $html);
    }

    /**
     * The second payment request turns up in the event editor's paywall: an owner with a paid
     * schedule is told the plan covers that one, and to make the event there instead, but only
     * where that can work (a venue schedule pins an event's location to itself).
     */
    public function test_the_paywall_names_the_paid_schedule_the_event_could_be_made_from(): void
    {
        $owner = $this->createOwner();
        $paid = $this->createRole($owner, 'talent', ['name' => 'Night Owls']);
        $venue = $this->freeSchedule($owner, 'venue', ['name' => 'Harbour Hall']);
        $event = $this->pricedEvent($venue);

        $line = e(__('messages.tickets_need_pro_other_schedule', ['this' => 'Harbour Hall', 'pro' => 'Night Owls']));
        $this->assertStringContainsString($line, $this->editor($owner, $venue, $event));

        $paid->forceFill(['type' => 'venue'])->save();
        $this->assertStringNotContainsString(e(__('messages.tickets_need_pro_other_schedule', ['this' => 'Harbour Hall', 'pro' => 'Night Owls'])), $this->editor($owner, $venue, $event));
    }

    private function pricedEvent(Role $role)
    {
        $event = $this->createEvent($role, ['tickets_enabled' => true, 'payment_method' => 'cash', 'creator_role_id' => $role->id, 'ticket_currency_code' => 'USD']);
        $this->createTicket($event, ['type' => 'Entry', 'price' => 20, 'quantity' => 100]);

        return $event;
    }

    private function editor(User $owner, Role $role, $event): string
    {
        return $this->actingAs($owner)
            ->get(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]))
            ->assertOk()
            ->getContent();
    }

    /** "Save and start free trial" saves, then goes on to checkout carrying the event back. */
    public function test_save_and_start_free_trial_saves_and_goes_to_checkout(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeSchedule($owner);
        $event = $this->pricedEvent($role);
        $hash = UrlUtils::encodeId($event->id);

        $this->actingAs($owner)
            ->put(route('event.update', ['subdomain' => $role->subdomain, 'hash' => $hash]), $this->eventPayload($event, ['then_subscribe' => 1]))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('role.subscribe', ['subdomain' => $role->subdomain, 'tier' => 'pro', 'source' => 'tickets', 'event' => $hash]));

        // An ordinary Save is untouched.
        $this->actingAs($owner)
            ->put(route('event.update', ['subdomain' => $role->subdomain, 'hash' => $hash]), $this->eventPayload($event))
            ->assertRedirectContains('/schedule');
    }

    /** Only the owner can check out, so a team admin's save goes where it always went. */
    public function test_a_team_admins_save_does_not_go_to_checkout(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeSchedule($owner);
        $event = $this->pricedEvent($role);
        $admin = $this->createOwner();
        $role->users()->attach($admin->id, ['level' => 'admin']);

        $response = $this->actingAs($admin)
            ->put(route('event.update', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]), $this->eventPayload($event, ['then_subscribe' => 1]));

        $this->assertStringNotContainsString('/subscribe', $response->headers->get('Location'));
    }

    private function eventPayload($event, array $extra = []): array
    {
        $ticket = $event->tickets()->first();

        return array_merge(['name' => $event->name, 'starts_at' => $event->starts_at, 'duration' => 2, 'ticket_panel' => 'full',
            'tickets_enabled' => 1, 'ticket_currency_code' => 'USD', 'payment_method' => 'cash',
            'tickets' => [['id' => $ticket->id, 'type' => 'Entry', 'price' => 20]]], $extra);
    }

    /** Checkout goes back only to an event of the schedule it is for, that its owner edits. */
    public function test_checkout_returns_only_to_an_event_of_its_own_schedule(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeSchedule($owner);
        $mine = $this->pricedEvent($role);
        $theirs = $this->pricedEvent($this->freeSchedule($this->createOwner()));

        $this->actingAs($owner);
        $returnEvent = new \ReflectionMethod(SubscriptionController::class, 'returnEvent');

        $this->assertSame($mine->id, $returnEvent->invoke(new SubscriptionController, UrlUtils::encodeId($mine->id), $role)?->id);
        $this->assertNull($returnEvent->invoke(new SubscriptionController, UrlUtils::encodeId($theirs->id), $role));
        $this->assertNull($returnEvent->invoke(new SubscriptionController, 'not-a-hash', $role));
    }

    /** The create-schedule form says a new schedule starts on Free, to an owner who has a plan. */
    public function test_the_new_schedule_form_says_a_plan_covers_one_schedule(): void
    {
        $owner = $this->createOwner();
        $this->createRole($owner, 'talent', ['name' => 'Night Owls']);

        $this->actingAs($owner)->get(route('new', ['type' => 'venue']))->assertOk()
            ->assertSee(__('messages.new_schedule_plan_note', ['name' => 'Night Owls']));

        $newcomer = $this->createOwner();
        $this->freeSchedule($newcomer);
        $this->actingAs($newcomer)->get(route('new', ['type' => 'venue']))->assertOk()
            ->assertDontSee('data-plan-note', false);
    }

    /**
     * "No information on when payouts will happen": a connected Stripe account says where the
     * money goes and links to its payouts. Not on selfhost, where the account is the install's.
     */
    public function test_a_connected_stripe_account_says_when_money_reaches_the_bank(): void
    {
        $owner = $this->createOwner();
        $owner->forceFill(['stripe_account_id' => 'acct_test', 'stripe_completed_at' => now()])->save();

        $this->actingAs($owner)->get(route('profile.edit'))->assertOk()
            ->assertSee('data-payout-note="stripe"', false)
            ->assertSee(__('messages.payout_note_stripe'))
            ->assertSee('https://dashboard.stripe.com/payouts', false);

        config(['app.hosted' => false]);
        $this->actingAs($owner)->get(route('profile.edit'))->assertOk()
            ->assertDontSee('data-payout-note="stripe"', false);
    }

    /** A charge-today checkout that needed 3DS starts later, through the webhook, which stamps it then. */
    public function test_a_subscription_that_starts_through_the_webhook_spends_the_trial(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeSchedule($owner);
        $this->subscribe($role, 'incomplete');
        $subscription = $role->subscriptions()->first();

        $handle = new \ReflectionMethod(\App\Http\Controllers\SubscriptionWebhookController::class, 'handleCustomerSubscriptionUpdated');
        $payload = fn (string $status, array $previous) => ['data' => ['object' => [
            'id' => $subscription->stripe_id, 'customer' => $role->fresh()->stripe_id, 'status' => $status,
            'items' => ['data' => [['id' => 'si_test', 'price' => ['id' => $subscription->stripe_price, 'product' => 'prod_test'], 'quantity' => 1]]],
            'quantity' => 1, 'cancel_at_period_end' => false,
        ], 'previous_attributes' => $previous]];

        $handle->invoke(new \App\Http\Controllers\SubscriptionWebhookController, $payload('incomplete', []));
        $this->assertNull($owner->fresh()->trial_used_at);

        // Any other update of a live subscription, such as the cancel a schedule transfer sends,
        // arrives after the schedule has a new owner, whose trial it must not spend.
        $handle->invoke(new \App\Http\Controllers\SubscriptionWebhookController, $payload('active', ['cancel_at_period_end' => false]));
        $this->assertNull($owner->fresh()->trial_used_at);

        $handle->invoke(new \App\Http\Controllers\SubscriptionWebhookController, $payload('active', ['status' => 'incomplete']));
        $this->assertNotNull($owner->fresh()->trial_used_at);
    }

    /** The flag rides in a hidden field: with the price taken off, a save is just a save. */
    public function test_a_save_with_no_price_left_does_not_go_to_checkout(): void
    {
        $owner = $this->createOwner();
        $role = $this->freeSchedule($owner);
        $event = $this->pricedEvent($role);
        $payload = $this->eventPayload($event, ['then_subscribe' => 1]);
        $payload['tickets'][0]['price'] = 0;

        $response = $this->actingAs($owner)
            ->put(route('event.update', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]), $payload)
            ->assertSessionHasNoErrors();

        $this->assertStringNotContainsString('/subscribe', $response->headers->get('Location'));
    }
}
