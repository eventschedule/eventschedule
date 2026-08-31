<?php

namespace Tests\Feature;

use App\Models\DismissedNextStep;
use App\Models\Role;
use App\Services\DemoService;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The in-app half of the activation nudges.
 *
 * The email half is bounded at both ends so a first run cannot mailshot the whole base, which
 * leaves every schedule that stalled before those windows reachable only here - and that is
 * most of them: 12 of the 401 schedules created up to 2026-02 have had any event in the last
 * 90 days.
 *
 * The other property under test is separation. getPendingActionItems() is a reactive queue and
 * its own comment says a to-do list is for things that need doing; a suggestion leaking into it
 * would make the queue impossible to trust.
 */
class DashboardNextStepsTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.hosted' => true]);
    }

    private function nextSteps($user): array
    {
        $response = $this->actingAs($user)->get(route('home'));
        $response->assertOk();

        return $response->viewData('nextStepItems')->all();
    }

    private function types($user): array
    {
        return array_column($this->nextSteps($user), 'type');
    }

    private function dismiss($user, Role $role, string $type)
    {
        return $this->actingAs($user)->post(route('home.next_steps_dismiss'), [
            'schedule' => UrlUtils::encodeId($role->id),
            'type' => $type,
        ]);
    }

    private function dismissAll($user)
    {
        return $this->actingAs($user)->post(route('home.next_steps_dismiss_all'));
    }

    /** The step that matters most: a date on the page and no way to buy. */
    public function test_a_published_schedule_with_no_ticket_type_is_told_to_add_one(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->assertSame(['next_step_tickets'], $this->types($user));
    }

    public function test_a_schedule_with_a_ticket_type_is_not_told_to_add_one(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user);
        $event = $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        $this->createTicket($event, ['price' => 10]);

        $this->assertNotContains('next_step_tickets', $this->types($user));
    }

    /** A draft is not on anyone's page, so it is not a reason to sell. */
    public function test_a_draft_event_does_not_ask_for_tickets(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user);
        $this->createEvent($role, [
            'starts_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'is_draft' => true,
        ]);

        $this->assertSame(['next_step_next_event'], $this->types($user), 'a draft leaves the page empty');
    }

    public function test_paid_tickets_without_a_gateway_ask_for_one(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['stripe_account_id' => null])->save();
        $role = $this->createRole($user->fresh());
        $event = $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        $this->createTicket($event, ['price' => 25]);

        $this->assertSame(['next_step_payments'], $this->types($user->fresh()));
    }

    /** stripe_completed_at, not stripe_account_id - see canAcceptStripePayments(). */
    public function test_a_connected_gateway_clears_the_payment_step(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['stripe_account_id' => 'acct_123', 'stripe_completed_at' => now()])->save();
        $role = $this->createRole($user->fresh());
        $event = $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        $this->createTicket($event, ['price' => 25]);

        $this->assertSame([], $this->types($user->fresh()));
    }

    /** A half-finished Stripe onboarding cannot take money, so the step must stand. */
    public function test_an_unfinished_stripe_onboarding_still_asks_for_payments(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['stripe_account_id' => 'acct_123', 'stripe_completed_at' => null])->save();
        $role = $this->createRole($user->fresh());
        $event = $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        $this->createTicket($event, ['price' => 25]);

        $this->assertSame(['next_step_payments'], $this->types($user->fresh()));
    }

    /** A payment link is a gateway. */
    public function test_a_payment_link_clears_the_payment_step(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['payment_url' => 'https://paypal.me/someone'])->save();
        $role = $this->createRole($user->fresh());
        $event = $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        $this->createTicket($event, ['price' => 25]);

        $this->assertSame([], $this->types($user->fresh()));
    }

    /** A paid ADD-ON is not a ticket type, and the email half never counted it as one. */
    public function test_a_paid_addon_is_not_a_ticket_type(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user);
        $event = $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        $this->createTicket($event, ['price' => 25, 'is_addon' => true]);

        $this->assertSame(['next_step_tickets'], $this->types($user));
    }

    /** A curator that only lists someone else's event is offered nothing for it. */
    public function test_a_curator_listing_someone_elses_event_is_offered_nothing(): void
    {
        $venueOwner = $this->createOwner();
        $venue = $this->createRole($venueOwner, 'venue');
        $event = $this->createEvent($venue, [
            'starts_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'creator_role_id' => $venue->id,
        ]);

        $curatorUser = $this->createOwner();
        $curator = $this->createRole($curatorUser, 'curator');
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        // Not "nothing at all": the curator's own page has no upcoming event of its own, so the
        // step it gets must be about publishing one, never about pricing someone else's.
        $this->assertSame(['next_step_first_event'], $this->types($curatorUser));
    }

    /** But a venue that accepted a talent's event can price it, so it is asked to. */
    public function test_a_venue_that_accepted_someone_elses_event_is_asked_for_tickets(): void
    {
        $talent = $this->createRole($this->createOwner(), 'talent');
        $event = $this->createEvent($talent, [
            'starts_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'creator_role_id' => $talent->id,
        ]);

        $venueOwner = $this->createOwner();
        $venue = $this->createRole($venueOwner, 'venue');
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $this->assertSame(['next_step_tickets'], $this->types($venueOwner));
    }

    public function test_an_empty_schedule_is_asked_for_its_first_event(): void
    {
        $user = $this->createOwner();
        $this->createRole($user);

        $steps = $this->nextSteps($user);

        $this->assertSame(['next_step_first_event'], array_column($steps, 'type'));
        $this->assertSame(__('messages.next_step_add_first_event'), $steps[0]['title']);
    }

    /**
     * A schedule that HAS published before gets different copy: "your next date", not "your
     * first event". Reaching a dormant schedule is the whole reason this panel exists.
     */
    public function test_a_schedule_whose_dates_have_all_passed_is_asked_for_the_next_one(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user);
        $this->createEvent($role, ['starts_at' => now()->subDays(200)->format('Y-m-d H:i:s')]);

        $steps = $this->nextSteps($user);

        $this->assertSame(['next_step_next_event'], array_column($steps, 'type'));
        $this->assertSame(__('messages.next_step_add_next_event'), $steps[0]['title']);
    }

    /**
     * One step per schedule. A list of chores reads as a chore, not a suggestion.
     *
     * Built on the ONE pair of branches that can both match: a schedule with a paid ticket
     * type on a past event and nothing upcoming wants a gateway AND a new date. The other
     * pairs are mutually exclusive by construction, so a test built on them proves nothing.
     */
    public function test_at_most_one_step_per_schedule(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['stripe_account_id' => null])->save();
        $role = $this->createRole($user->fresh());
        $event = $this->createEvent($role, ['starts_at' => now()->subDays(20)->format('Y-m-d H:i:s')]);
        $this->createTicket($event, ['price' => 25]);

        $steps = $this->nextSteps($user->fresh());

        $this->assertCount(1, $steps, 'it wants a gateway and a date; it may only ask for one');
        $this->assertSame('next_step_payments', $steps[0]['type'], 'and it asks for the more valuable one');
    }

    /** Suggestions must never leak into the reactive to-do queue. */
    public function test_next_steps_stay_out_of_the_needs_attention_queue(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $response = $this->actingAs($user)->get(route('home'));

        $this->assertNotEmpty($response->viewData('nextStepItems'));
        $this->assertEmpty(
            $response->viewData('pendingActionItems'),
            'a suggestion in the to-do queue makes the queue impossible to trust'
        );
    }

    /**
     * A brand-new account never reaches this panel at all.
     *
     * HomeController::home() redirects an account with no schedules to /getting-started, and
     * the onboarding nudge emails cover that stretch. This panel starts where they stop.
     */
    public function test_an_account_with_no_schedules_is_sent_to_getting_started(): void
    {
        $user = $this->createOwner();

        $this->actingAs($user)->get(route('home'))
            ->assertRedirectContains('/getting-started');
    }

    /**
     * A viewer cannot act on any of these, so offering them is noise.
     *
     * Guaranteed upstream: the dashboard passes $user->editor(), which is owner and admin
     * only. Asserted here as behaviour, because that is the contract the panel depends on.
     */
    public function test_a_viewer_is_offered_nothing(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $viewer = $this->createOwner();
        $role->users()->attach($viewer->id, ['level' => 'viewer']);

        $this->assertSame([], $this->types($viewer));
    }

    /** And the panel actually renders, under its own heading. */
    public function test_the_panel_renders_with_its_own_heading(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->actingAs($user)->get(route('home'))
            ->assertOk()
            ->assertSee(__('messages.next_steps'))
            ->assertSee(__('messages.next_step_add_ticket_type'));
    }

    //
    // Dismissal. A suggestion nobody wants must be answerable, and the answer is remembered
    // per (user, schedule, step) rather than as a flag on the account - this is the surface
    // that reaches everyone the email windows exclude.
    //

    public function test_dismissing_a_step_removes_it_from_the_panel(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->assertSame(['next_step_tickets'], $this->types($user));

        $this->dismiss($user, $role, 'next_step_tickets')->assertRedirect();

        $this->assertSame([], $this->types($user));
        $this->assertDatabaseHas('dismissed_next_steps', [
            'user_id' => $user->id,
            'role_id' => $role->id,
            'step_type' => 'next_step_tickets',
        ]);
    }

    /** No expiry window: "not interested" does not become interested again in three months. */
    public function test_a_dismissal_is_permanent(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user);

        $this->dismiss($user, $role, 'next_step_first_event');
        $this->assertSame([], $this->types($user));

        $this->travel(400)->days();

        $this->assertSame([], $this->types($user), 'a dismissal must not age out');
    }

    /** Per schedule, not per account. Turning one venue down says nothing about the other. */
    public function test_a_dismissal_is_scoped_to_the_schedule(): void
    {
        $user = $this->createOwner();
        $quiet = $this->createRole($user, 'venue', ['name' => 'Quiet Room']);
        $other = $this->createRole($user, 'venue', ['name' => 'Corner Pub']);
        $this->createEvent($quiet, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        $this->createEvent($other, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->dismiss($user, $quiet, 'next_step_tickets');

        $steps = $this->nextSteps($user);

        $this->assertCount(1, $steps);
        $this->assertSame('Corner Pub', $steps[0]['subtitle']);
    }

    /**
     * Per step, not per schedule. Someone who will never sell tickets here still wants to know
     * when the page has no dates left on it, which is the whole dormant-schedule case.
     */
    public function test_a_dismissal_is_scoped_to_the_step_type(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user);
        $event = $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->dismiss($user, $role, 'next_step_tickets');
        $this->assertSame([], $this->types($user));

        // The date passes and the page is now empty. A different ask, so it is still made.
        $event->forceFill(['starts_at' => now()->subDays(5)->format('Y-m-d H:i:s')])->save();

        $this->assertSame(['next_step_next_event'], $this->types($user));
    }

    /** Several editors share a schedule, and one of them saying no does not decide for another. */
    public function test_one_editors_dismissal_does_not_hide_the_row_for_another(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $admin = $this->createOwner();
        $role->users()->attach($admin->id, ['level' => 'admin']);

        $this->dismiss($admin, $role, 'next_step_tickets');

        $this->assertSame([], $this->types($admin));
        $this->assertSame(['next_step_tickets'], $this->types($owner));
    }

    /**
     * A dismissed schedule keeps its slot for the pass rather than falling through.
     *
     * Built on the one pair of branches that can both match - a paid ticket type on a past
     * event with no gateway wants a gateway AND a new date - so a test built on any other pair
     * proves nothing. Without the continue, dismissing the payment step would swap in "add your
     * next date" on the same row, which reads as the button not working.
     */
    public function test_a_dismissed_schedule_is_not_offered_a_different_step_instead(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['stripe_account_id' => null])->save();
        $role = $this->createRole($user->fresh());
        $event = $this->createEvent($role, ['starts_at' => now()->subDays(20)->format('Y-m-d H:i:s')]);
        $this->createTicket($event, ['price' => 25]);

        $this->assertSame(['next_step_payments'], $this->types($user->fresh()));

        $this->dismiss($user->fresh(), $role, 'next_step_payments');

        $this->assertSame([], $this->types($user->fresh()), 'it must not swap in the next-best ask');
    }

    public function test_dismiss_all_clears_every_listed_step(): void
    {
        $user = $this->createOwner();
        $withDate = $this->createRole($user);
        $this->createEvent($withDate, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        $this->createRole($user);
        $this->createRole($user);

        $this->assertCount(3, $this->nextSteps($user));

        $this->dismissAll($user)->assertRedirect();

        $this->assertSame([], $this->types($user));
        $this->assertSame(3, DB::table('dismissed_next_steps')->where('user_id', $user->id)->count());
    }

    /**
     * The panel renders at most $limit rows and folds the rest into a "show more" details, so a
     * form built from what is on screen would leave everything past the eighth schedule behind.
     * The action recomputes server-side instead.
     */
    public function test_dismiss_all_also_clears_the_rows_behind_show_more(): void
    {
        $user = $this->createOwner();
        for ($i = 0; $i < 10; $i++) {
            $this->createRole($user);
        }

        $this->assertCount(10, $this->nextSteps($user), 'more than the panel renders');

        $this->dismissAll($user);

        $this->assertSame([], $this->types($user));
        $this->assertSame(10, DB::table('dismissed_next_steps')->where('user_id', $user->id)->count());
    }

    /** Per item, so it clears what is on the panel today without silencing tomorrow. */
    public function test_a_schedule_created_after_dismiss_all_still_gets_a_suggestion(): void
    {
        $user = $this->createOwner();
        $this->createRole($user);

        $this->dismissAll($user);
        $this->assertSame([], $this->types($user));

        $this->createRole($user, 'venue', ['name' => 'Brand New']);

        $steps = $this->nextSteps($user);

        $this->assertSame(['next_step_first_event'], array_column($steps, 'type'));
        $this->assertSame('Brand New', $steps[0]['subtitle']);
    }

    public function test_a_user_cannot_dismiss_for_a_schedule_they_do_not_edit(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        // An outsider with a schedule of their own, so the dashboard does not bounce them to
        // /getting-started and the request genuinely reaches the controller.
        $outsider = $this->createOwner();
        $this->createRole($outsider);

        $this->dismiss($outsider, $role, 'next_step_tickets')
            ->assertSessionHas('error');

        $this->assertDatabaseCount('dismissed_next_steps', 0);
        $this->assertSame(['next_step_tickets'], $this->types($owner));
    }

    /** A viewer can act on none of these steps and never sees a row, so it cannot answer one. */
    public function test_a_viewer_cannot_dismiss(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $viewer = $this->createOwner();
        $role->users()->attach($viewer->id, ['level' => 'viewer']);

        $this->dismiss($viewer, $role, 'next_step_tickets')->assertSessionHas('error');

        $this->assertDatabaseCount('dismissed_next_steps', 0);
        $this->assertSame(['next_step_tickets'], $this->types($owner));
    }

    /**
     * The discriminator column is a free-text string, so the only thing keeping a junk value out
     * is the validation rule. One that later collided with a real step would silently suppress
     * both a panel row and an email.
     */
    public function test_an_unknown_step_type_is_rejected(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user);

        $this->dismiss($user, $role, 'requests')->assertSessionHasErrors('type');

        $this->assertDatabaseCount('dismissed_next_steps', 0);
    }

    /** decodeId() returns null on a bad hash, which must not fall through to an unkeyed write. */
    public function test_a_malformed_schedule_hash_dismisses_nothing(): void
    {
        $user = $this->createOwner();
        $this->createRole($user);

        $this->assertNull(UrlUtils::decodeId('...'), 'precondition: this really is undecodable');

        $this->actingAs($user)->post(route('home.next_steps_dismiss'), [
            'schedule' => '...',
            'type' => 'next_step_first_event',
        ])->assertSessionHas('error');

        $this->assertDatabaseCount('dismissed_next_steps', 0);
    }

    /** Two tabs, or a double click. The unique index is what makes the second one harmless. */
    public function test_dismissing_the_same_step_twice_is_a_no_op(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user);

        $this->dismiss($user, $role, 'next_step_first_event')->assertRedirect();
        $this->dismiss($user, $role, 'next_step_first_event')->assertRedirect();
        $this->dismissAll($user)->assertRedirect();

        $this->assertSame(1, DismissedNextStep::where('user_id', $user->id)->count());
    }

    public function test_demo_mode_cannot_dismiss(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        // saveQuietly, because User::boot()'s updating hook nulls email_verified_at on an email
        // change while hosted. An unverified user is bounced by the verified middleware and the
        // test would pass without ever reaching the demo guard.
        $user->email = DemoService::DEMO_EMAIL;
        $user->email_verified_at = now();
        $user->saveQuietly();
        $user = $user->fresh();
        $this->assertTrue($user->hasVerifiedEmail(), 'precondition: the request must reach the controller');

        $this->dismiss($user, $role, 'next_step_tickets')->assertRedirect();
        $this->dismissAll($user)->assertRedirect();

        $this->assertDatabaseCount('dismissed_next_steps', 0);
    }

    public function test_the_panel_renders_a_dismiss_control_and_a_dismiss_all(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        // Two rows: the header action is deliberately hidden beside a single suggestion.
        $this->createRole($user);

        $this->actingAs($user)->get(route('home'))
            ->assertOk()
            ->assertSee('action="'.route('home.next_steps_dismiss').'"', false)
            ->assertSee('action="'.route('home.next_steps_dismiss_all').'"', false)
            ->assertSee(__('messages.next_steps_dismiss_all'));
    }

    /**
     * Dismissal is opt-in per caller. The to-do queue is reactive - those rows are things that
     * need doing, not suggestions - and the admin dashboard shares the same component.
     */
    public function test_the_to_do_queue_has_no_dismiss_control(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user);
        $event = $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        // A pending fan comment puts exactly one row in the reactive queue.
        DB::table('event_comments')->insert([
            'event_id' => $event->id,
            'guest_name' => 'Someone',
            'comment' => 'Nice one',
            'is_approved' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)->get(route('home'))->assertOk();

        $this->assertCount(1, $response->viewData('pendingActionItems'));
        $this->assertCount(1, $response->viewData('nextStepItems'));
        $this->assertSame(
            1,
            substr_count($response->getContent(), 'action="'.route('home.next_steps_dismiss').'"'),
            'only the next-step row may carry a dismiss form'
        );
    }

    /**
     * The X on a row must carry that row's OWN schedule.
     *
     * Every other dismissal test builds the POST body itself, so this is the only one that reads
     * the value the panel actually rendered. Get it wrong - a stale $role, a copy-pasted
     * $roles->first()->id - and the X on one row silences a different schedule, silently.
     */
    public function test_each_row_carries_its_own_schedule_hash(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['stripe_account_id' => null])->save();
        $user = $user->fresh();

        $tickets = $this->createRole($user, 'venue', ['name' => 'Blue Note Jazz']);
        $this->createEvent($tickets, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $payments = $this->createRole($user, 'venue', ['name' => 'Corner Pub']);
        $past = $this->createEvent($payments, ['starts_at' => now()->subDays(20)->format('Y-m-d H:i:s')]);
        $this->createTicket($past, ['price' => 25]);

        $empty = $this->createRole($user, 'talent', ['name' => 'The Wandering Trio']);

        // One schedule per branch, so a hash copied from the wrong row cannot coincide.
        $expected = [
            'next_step_tickets' => $tickets->id,
            'next_step_payments' => $payments->id,
            'next_step_first_event' => $empty->id,
        ];

        $steps = $this->nextSteps($user);
        $this->assertCount(3, $steps);

        foreach ($steps as $step) {
            $this->assertSame(
                $expected[$step['type']],
                UrlUtils::decodeId($step['dismiss_schedule']),
                $step['type'].' carries the wrong schedule, so its X would silence another row'
            );
        }
    }

    /**
     * Rows past the component's $limit are folded into a "show more" details, but they are still
     * rendered - so the route has to reach BOTH include sites, not just the visible loop.
     */
    public function test_the_rows_behind_show_more_also_carry_a_dismiss_control(): void
    {
        $user = $this->createOwner();
        for ($i = 0; $i < 10; $i++) {
            $this->createRole($user);
        }

        $response = $this->actingAs($user)->get(route('home'))->assertOk();

        $this->assertCount(10, $response->viewData('nextStepItems'), 'more than the panel shows inline');
        $this->assertSame(
            10,
            substr_count($response->getContent(), 'action="'.route('home.next_steps_dismiss').'"'),
            'the rows folded into "show more" must carry the control too'
        );
    }

    /**
     * The gateway is one per ACCOUNT, so its ask is too, and so is the answer.
     *
     * payment_gateways()->connectedFor() resolves per user: connecting one fixes every schedule
     * at once, so there is nothing schedule-specific to decline. Keeping this per schedule made
     * an owner with several ticketed schedules answer the identical question once each.
     */
    public function test_dismissing_the_payments_step_clears_it_for_every_schedule(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['stripe_account_id' => null])->save();
        $user = $user->fresh();

        foreach (['Blue Note Jazz', 'Corner Pub'] as $name) {
            $role = $this->createRole($user, 'venue', ['name' => $name]);
            $event = $this->createEvent($role, ['starts_at' => now()->subDays(20)->format('Y-m-d H:i:s')]);
            $this->createTicket($event, ['price' => 25]);
        }

        $roles = $user->fresh()->editor()->get();
        $this->assertSame(['next_step_payments', 'next_step_payments'], $this->types($user->fresh()));

        $this->dismiss($user->fresh(), $roles->first(), 'next_step_payments');

        $this->assertSame([], $this->types($user->fresh()), 'one gateway, one answer');

        // And a schedule added afterwards inherits it, because it is the same question again.
        $later = $this->createRole($user->fresh(), 'venue', ['name' => 'New Room']);
        $this->createTicket(
            $this->createEvent($later, ['starts_at' => now()->subDays(20)->format('Y-m-d H:i:s')]),
            ['price' => 25]
        );

        $this->assertSame([], $this->types($user->fresh()));
    }

    /**
     * "You have never published here" and "this page has gone quiet" are different asks at
     * opposite ends of a schedule's life, so they are separate step types.
     *
     * Dismissals never expire, so folding them into one let a day-one "not ready yet" silence the
     * dormancy nudge forever - on a schedule that had since run for years and stopped. That nudge
     * is the one the whole activation programme exists for.
     */
    public function test_dismissing_the_first_event_ask_does_not_silence_the_dormancy_one(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user);

        $this->assertSame(['next_step_first_event'], $this->types($user));
        $this->dismiss($user, $role, 'next_step_first_event');
        $this->assertSame([], $this->types($user));

        // The schedule then runs, and later goes quiet. A different situation, so it is asked.
        $this->createEvent($role, ['starts_at' => now()->subDays(45)->format('Y-m-d H:i:s')]);

        $this->assertSame(['next_step_next_event'], $this->types($user));
    }

    public function test_the_panel_disappears_once_everything_is_dismissed(): void
    {
        $user = $this->createOwner();
        $role = $this->createRole($user);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->actingAs($user)->get(route('home'))->assertSee(__('messages.next_steps'));

        $this->dismissAll($user);

        $this->actingAs($user)->get(route('home'))
            ->assertOk()
            ->assertDontSee(__('messages.next_steps'));
    }
}
