<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->assertSame(['next_step_event'], $this->types($user), 'a draft leaves the page empty');
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

    public function test_a_connected_gateway_clears_the_payment_step(): void
    {
        $user = $this->createOwner();
        $user->forceFill(['stripe_account_id' => 'acct_123'])->save();
        $role = $this->createRole($user->fresh());
        $event = $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        $this->createTicket($event, ['price' => 25]);

        $this->assertSame([], $this->types($user->fresh()));
    }

    public function test_an_empty_schedule_is_asked_for_its_first_event(): void
    {
        $user = $this->createOwner();
        $this->createRole($user);

        $steps = $this->nextSteps($user);

        $this->assertSame(['next_step_event'], array_column($steps, 'type'));
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

        $this->assertSame(['next_step_event'], array_column($steps, 'type'));
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
}
