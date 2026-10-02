<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\ActivationNudge;
use App\Models\DismissedNextStep;
use App\Models\Role;
use App\Models\User;
use App\Services\DemoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * These reach people who are already using the app and have not asked for anything, so the
 * windows and the exclusions matter more than the sends.
 *
 * The dangerous failure is not a missed nudge, it is an unbounded query: 226 schedules have
 * never had an event and 542 are dormant, so a no_event or idle trigger without an upper bound
 * is a mailshot to every account the app has ever had, on the first run.
 */
class ActivationNudgeTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.hosted' => true]);
        Queue::fake();
    }

    private function owner(array $attrs = []): User
    {
        $user = $this->createOwner();
        $user->forceFill(array_merge(['is_subscribed' => true], $attrs))->save();

        return $user->fresh();
    }

    /**
     * --now skips the owner's local-morning window, which every test below except the ones about
     * that window would otherwise depend on the hour the suite happens to run at.
     */
    private function nudge(?string $key = null, bool $now = true): void
    {
        $args = ['--apply' => true];
        if ($now) {
            $args['--now'] = true;
        }
        if ($key) {
            $args['--key'] = $key;
        }

        $this->artisan('app:send-activation-nudges', $args)->assertExitCode(0);
    }

    private function assertSent(string $key, int $times = 1): void
    {
        Queue::assertPushed(SendQueuedEmail::class, $times);
        $this->assertSame($times, DB::table('schedule_nudges')->where('nudge_key', $key)->count());
    }

    /** SendQueuedEmail keeps its recipient protected, so read it the way OnboardingNudgeTest does. */
    private function assertQueuedTo(string $email): void
    {
        Queue::assertPushed(SendQueuedEmail::class, function ($job) use ($email) {
            $recipient = new \ReflectionProperty($job, 'recipient');
            $recipient->setAccessible(true);

            return $recipient->getValue($job) === $email;
        });
    }

    private function assertNothingSent(): void
    {
        Queue::assertNothingPushed();
        $this->assertSame(0, DB::table('schedule_nudges')->count());
    }

    /** A schedule with a date still to sell and no way to buy: the one that matters most. */
    public function test_it_nudges_a_published_schedule_with_no_ticket_type(): void
    {
        $role = $this->createRole($this->owner());
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->nudge('no_ticket_type');

        $this->assertSent('no_ticket_type');
        $this->assertQueuedTo($role->user->email);
    }

    /** Once a ticket type exists there is nothing to ask for. */
    public function test_a_schedule_that_already_has_a_ticket_type_is_not_nudged(): void
    {
        $role = $this->createRole($this->owner());
        $event = $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        $this->createTicket($event, ['price' => 10]);

        $this->nudge('no_ticket_type');

        $this->assertNothingSent();
    }

    /**
     * Scoped to an UPCOMING event. Someone whose season ended has nothing left to sell, and
     * telling them to set up tickets is noise.
     */
    public function test_a_schedule_whose_events_are_all_past_is_not_nudged_about_tickets(): void
    {
        $role = $this->createRole($this->owner());
        $this->createEvent($role, ['starts_at' => now()->subDays(10)->format('Y-m-d H:i:s')]);

        $this->nudge('no_ticket_type');

        $this->assertNothingSent();
    }

    /** A draft or private event is not on anyone's page, so it is not a reason to sell. */
    public function test_a_draft_event_does_not_trigger_the_ticket_nudge(): void
    {
        $role = $this->createRole($this->owner());
        $this->createEvent($role, [
            'starts_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'is_draft' => true,
        ]);

        $this->nudge('no_ticket_type');

        $this->assertNothingSent();
    }

    /**
     * Both selling nudges are Pro-gated now that paid selling is.
     *
     * Every other fixture in this file comes from createRole(), which defaults to enterprise, so
     * without these two cases the gate is never evaluated in the deny direction and the whole file
     * would stay green with the gate removed. Mailing a free schedule "you can sell from this page"
     * is an upsell wearing an activation email's clothes: the thing it asks them to set up will not
     * sell until they pay.
     */
    public function test_it_does_not_nudge_a_free_schedule_to_add_a_ticket_type(): void
    {
        $role = $this->createFreeRole($this->owner());
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->assertFalse($role->fresh()->isPro(), 'sanity check: the fixture is not Pro');

        $this->nudge('no_ticket_type');

        $this->assertNothingSent();
    }

    /**
     * The free half of the same moment, which no_ticket_type cannot reach: the ticket cliff is on
     * free schedules. Its own key and copy, because the ask is registration, not selling.
     */
    public function test_it_nudges_a_free_schedule_to_take_sign_ups(): void
    {
        $role = $this->createFreeRole($this->owner());
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->nudge();

        $this->assertSame(['no_ticket_type_free'], DB::table('schedule_nudges')->pluck('nudge_key')->all());
    }

    /**
     * Most free schedules carry a NULL plan_expires. A negated wherePro() compares it and drops
     * the row, so this is the case the NOT IN exists for.
     */
    public function test_a_free_schedule_with_no_plan_expiry_is_still_reached(): void
    {
        $role = $this->createFreeRole($this->owner(), 'venue', ['plan_expires' => null]);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->nudge('no_ticket_type_free');

        $this->assertSent('no_ticket_type_free');
    }

    public function test_a_pro_schedule_never_gets_the_free_copy(): void
    {
        $role = $this->createRole($this->owner());
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->nudge('no_ticket_type_free');

        $this->assertNothingSent();
    }

    /** Registration is the free way to sign up, so a schedule taking it has done what this asks. */
    public function test_a_free_schedule_taking_registrations_is_left_alone(): void
    {
        $role = $this->createFreeRole($this->owner());
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s'), 'rsvp_enabled' => true]);

        $this->nudge('no_ticket_type_free');

        $this->assertNothingSent();
    }

    /** A schedule on the selling trial can sell, so it gets the selling copy. */
    public function test_a_schedule_on_the_selling_trial_gets_the_selling_copy(): void
    {
        $role = $this->createFreeRole($this->owner());
        $role->forceFill(['ticket_trial_ends_at' => now()->addDays(5)])->save();
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->nudge();

        $this->assertSame(['no_ticket_type'], DB::table('schedule_nudges')->pluck('nudge_key')->all());
    }

    /** A grandfathered event takes money on a free schedule, so it needs somewhere to put it. */
    public function test_a_grandfathered_free_seller_is_nudged_to_connect_a_gateway(): void
    {
        $role = $this->createFreeRole($this->owner(['stripe_account_id' => null]));
        $event = $this->createEvent($role, [
            'starts_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'creator_role_id' => $role->id,
        ]);
        $event->forceFill(['tickets_grandfathered_at' => now()])->save();
        $this->createTicket($event, ['price' => 20, 'quantity' => 50]);

        $this->nudge('no_gateway');

        $this->assertSent('no_gateway');
    }

    public function test_it_does_not_nudge_a_free_schedule_to_connect_a_gateway(): void
    {
        $role = $this->createFreeRole($this->owner());
        $event = $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        $this->createTicket($event, ['price' => 20, 'quantity' => 50]);

        $this->nudge('no_gateway');

        $this->assertNothingSent();
    }

    public function test_it_nudges_a_schedule_with_paid_tickets_and_no_gateway(): void
    {
        $role = $this->createRole($this->owner(['stripe_account_id' => null]));
        $event = $this->createEvent($role);
        $this->createTicket($event, ['price' => 25]);

        $this->nudge('no_gateway');

        $this->assertSent('no_gateway');
    }

    /**
     * Only while the priced tickets are on a date still to come. Unbounded, this reached 59
     * dormant schedules (61 of 67 candidates were admin comps) on 2026-09-28.
     */
    public function test_a_dormant_seller_is_not_nudged_about_payments(): void
    {
        $role = $this->createRole($this->owner(['stripe_account_id' => null]));
        $event = $this->createEvent($role, ['starts_at' => now()->subDays(200)->format('Y-m-d H:i:s')]);
        $this->createTicket($event, ['price' => 25]);

        $this->nudge('no_gateway');

        $this->assertNothingSent();
    }

    /**
     * stripe_completed_at, not stripe_account_id.
     *
     * canAcceptStripePayments() reads the former; StripeController writes it only once Stripe
     * confirms charges_enabled. This test used to set stripe_account_id and passed against a
     * hand-rolled column check that read the same field - i.e. for the wrong reason.
     */
    public function test_a_connected_gateway_disqualifies_the_payment_nudge(): void
    {
        $role = $this->createRole($this->owner([
            'stripe_account_id' => 'acct_123',
            'stripe_completed_at' => now(),
        ]));
        $event = $this->createEvent($role);
        $this->createTicket($event, ['price' => 25]);

        $this->nudge('no_gateway');

        $this->assertNothingSent();
    }

    /**
     * The false negative the column check produced, and the worse half of that bug.
     *
     * stripe_account_id is written the moment Connect onboarding STARTS. Someone who began it and
     * never finished cannot take a payment, and was skipped by the one nudge written for them.
     */
    public function test_a_half_finished_stripe_onboarding_is_still_nudged(): void
    {
        $role = $this->createRole($this->owner([
            'stripe_account_id' => 'acct_123',
            'stripe_completed_at' => null,
        ]));
        $event = $this->createEvent($role);
        $this->createTicket($event, ['price' => 25]);

        $this->nudge('no_gateway');

        $this->assertSent('no_gateway');
    }

    /** payment_url is a gateway too, and the column check did not know about it. */
    public function test_a_payment_link_counts_as_a_connected_gateway(): void
    {
        $role = $this->createRole($this->owner(['payment_url' => 'https://paypal.me/someone']));
        $event = $this->createEvent($role);
        $this->createTicket($event, ['price' => 25]);

        $this->nudge('no_gateway');

        $this->assertNothingSent();
    }

    /** Free tickets are not a reason to connect a payment gateway. */
    public function test_free_ticket_types_do_not_trigger_the_payment_nudge(): void
    {
        $role = $this->createRole($this->owner(['stripe_account_id' => null]));
        $event = $this->createEvent($role);
        $this->createTicket($event, ['price' => 0]);

        $this->nudge('no_gateway');

        $this->assertNothingSent();
    }

    public function test_it_congratulates_a_recent_first_sale(): void
    {
        $role = $this->createRole($this->owner());
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 20]);
        $this->createSale($event, $role, ['payment_amount' => 20, 'paid_at' => now()->subDay()], $ticket);

        $this->nudge('first_sale');

        $this->assertSent('first_sale');
    }

    /** Congratulating someone on a sale from last year reads as a bug, not a nudge. */
    public function test_an_old_first_sale_is_not_congratulated(): void
    {
        $role = $this->createRole($this->owner());
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 20]);
        $this->createSale($event, $role, ['payment_amount' => 20, 'paid_at' => now()->subDays(60)], $ticket);

        $this->nudge('first_sale');

        $this->assertNothingSent();
    }

    public function test_it_nudges_a_new_schedule_with_no_event(): void
    {
        $role = $this->createRole($this->owner());
        $role->forceFill(['created_at' => now()->subDays(2)])->save();

        $this->nudge('no_event');

        $this->assertSent('no_event');
    }

    /** Mid-task. Someone who signed up an hour ago is still doing it. */
    public function test_a_schedule_created_minutes_ago_is_left_alone(): void
    {
        $this->createRole($this->owner());

        $this->nudge('no_event');

        $this->assertNothingSent();
    }

    /**
     * The upper bound, which is the whole safety property.
     *
     * Without it the first run emails every schedule that has ever existed without an event.
     * A test that only proves the lower bound would pass on that code.
     */
    public function test_a_long_dormant_empty_schedule_is_not_mailshot(): void
    {
        $role = $this->createRole($this->owner());
        $role->forceFill(['created_at' => now()->subYear()])->save();

        $this->nudge('no_event');

        $this->assertNothingSent();
    }

    public function test_it_nudges_a_schedule_that_has_gone_quiet(): void
    {
        $role = $this->createRole($this->owner());
        $this->createEvent($role, ['starts_at' => now()->subDays(40)->format('Y-m-d H:i:s')]);

        $this->nudge('idle_30');

        $this->assertSent('idle_30');
    }

    /** Something upcoming means the page is working, whatever the back catalogue looks like. */
    public function test_an_upcoming_event_disqualifies_the_idle_nudge(): void
    {
        $role = $this->createRole($this->owner());
        $this->createEvent($role, ['starts_at' => now()->subDays(40)->format('Y-m-d H:i:s')]);
        $this->createEvent($role, ['starts_at' => now()->addDays(5)->format('Y-m-d H:i:s')]);

        $this->nudge('idle_30');

        $this->assertNothingSent();
    }

    /**
     * A curator's page is the events it lists, not only the ones it created.
     *
     * The upcoming check went through ownedEvents(), which leaves out a curator's listed events so
     * it is never asked to price them. A curator whose calendar was full of events it listed from
     * other schedules was emailed "your page has no upcoming dates".
     *
     * creator_role_id is set on the curator's own event on purpose: createEvent() leaves it null,
     * and without it the trigger half never matches a curator, so this would pass vacuously.
     */
    public function test_a_curator_with_upcoming_listed_events_is_not_idle(): void
    {
        $this->curatorListingAnUpcomingEvent(true);

        $this->nudge('idle_30');

        $this->assertNothingSent();
    }

    /** An uncurated row is off the page, so the curator can still go quiet. */
    public function test_a_curator_whose_upcoming_event_was_uncurated_can_be_idle(): void
    {
        $curator = $this->curatorListingAnUpcomingEvent(false);

        $this->nudge('idle_30');

        $this->assertSent('idle_30');
        $this->assertSame($curator->id, (int) DB::table('schedule_nudges')->value('role_id'));
    }

    /** A curator that created one event 40 days ago, and a venue's event 10 days out on its page. */
    private function curatorListingAnUpcomingEvent(bool $accepted): Role
    {
        $curator = $this->createRole($this->owner(), 'curator');
        $this->createEvent($curator, [
            'starts_at' => now()->subDays(40)->format('Y-m-d H:i:s'),
            'creator_role_id' => $curator->id,
        ]);

        $venue = $this->createRole($this->owner(), 'venue');
        $event = $this->createEvent($venue, [
            'starts_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'creator_role_id' => $venue->id,
        ]);
        $event->roles()->attach($curator->id, ['is_accepted' => $accepted]);

        return $curator;
    }

    /**
     * A series' starts_at is its anchor, weeks in the past while it runs. The bare starts_at test
     * this replaced mailed a schedule in the middle of a weekly run "nothing coming up".
     */
    public function test_a_running_weekly_series_is_not_idle(): void
    {
        $role = $this->createRole($this->owner());
        $this->createRecurringEvent($role, ['starts_at' => now()->subDays(40)->format('Y-m-d H:i:s')]);

        $this->nudge('idle_30');

        $this->assertNothingSent();
    }

    /** A series whose end date has passed has nothing coming up, so it can go quiet like any page. */
    public function test_a_series_that_has_ended_can_be_idle(): void
    {
        $role = $this->createRole($this->owner());
        $this->createRecurringEvent($role, [
            'starts_at' => now()->subDays(40)->format('Y-m-d H:i:s'),
            'recurring_end_type' => 'on_date',
            'recurring_end_value' => now()->subDays(33)->format('Y-m-d'),
        ]);

        $this->nudge('idle_30');

        $this->assertSent('idle_30');
    }

    /** The same fix from the other side: a running series has a date still to sell. */
    public function test_a_running_weekly_series_is_nudged_about_tickets(): void
    {
        $role = $this->createRole($this->owner());
        $this->createRecurringEvent($role, ['starts_at' => now()->subDays(30)->format('Y-m-d H:i:s')]);

        $this->nudge('no_ticket_type');

        $this->assertSent('no_ticket_type');
    }

    /** idle_60 must not re-reach everyone idle_30 already covered, or it is the same email twice. */
    public function test_the_two_idle_windows_do_not_overlap(): void
    {
        $role = $this->createRole($this->owner());
        $this->createEvent($role, ['starts_at' => now()->subDays(40)->format('Y-m-d H:i:s')]);

        $this->nudge();

        $this->assertSame(
            ['idle_30'],
            DB::table('schedule_nudges')->pluck('nudge_key')->all(),
            'a schedule 40 days quiet is in the idle_30 window and nowhere else'
        );
    }

    /** The claim is the unique index, so a second pass sends nothing. */
    public function test_a_second_run_sends_nothing(): void
    {
        $role = $this->createRole($this->owner());
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->nudge('no_ticket_type');
        Queue::fake();
        $this->nudge('no_ticket_type');

        Queue::assertNothingPushed();
        $this->assertSame(1, DB::table('schedule_nudges')->where('nudge_key', 'no_ticket_type')->count());
    }

    public function test_an_unsubscribed_owner_is_never_emailed(): void
    {
        $role = $this->createRole($this->owner(['is_subscribed' => false]));
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->nudge();

        $this->assertNothingSent();
    }

    public function test_the_demo_account_is_never_emailed(): void
    {
        $user = $this->owner();
        $user->forceFill(['email' => DemoService::DEMO_EMAIL])->save();
        $role = $this->createRole($user->fresh());
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->nudge();

        $this->assertNothingSent();
    }

    /**
     * The other half of the demo exclusion, and it needs its own test.
     *
     * Per-visitor demo schedules are handed out on demo-* subdomains under ordinary accounts,
     * so the DEMO_EMAIL check above does not see them - a test that only covers the shared
     * account passes with the subdomain clauses deleted.
     */
    public function test_per_visitor_demo_subdomains_are_never_emailed(): void
    {
        $role = $this->createRole($this->owner(), 'venue', ['subdomain' => 'demo-abc123']);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->nudge();

        $this->assertNothingSent();
    }

    /**
     * The unique index, not the query filter, is what makes a double-fired scheduler safe.
     *
     * Two runners can read the same rows before either writes, and the whereNotExists filter
     * cannot see a claim that has not been committed yet - only the index can reject it. That is
     * true of two concurrent hand-runs today, and of the two scheduler rails if this goes back
     * on a schedule.
     */
    public function test_the_database_rejects_a_duplicate_claim(): void
    {
        $role = $this->createRole($this->owner());

        $first = DB::table('schedule_nudges')->insertOrIgnore([
            'role_id' => $role->id, 'nudge_key' => 'no_ticket_type', 'created_at' => now(),
        ]);
        $second = DB::table('schedule_nudges')->insertOrIgnore([
            'role_id' => $role->id, 'nudge_key' => 'no_ticket_type', 'created_at' => now(),
        ]);

        $this->assertSame(1, $first, 'the first runner claims it');
        $this->assertSame(0, $second, 'the second is rejected by the unique index, not by a read');
        $this->assertSame(1, DB::table('schedule_nudges')->count());
    }

    public function test_a_deleted_schedule_is_never_emailed(): void
    {
        $role = $this->createRole($this->owner(), 'venue', ['is_deleted' => true]);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->nudge();

        $this->assertNothingSent();
    }

    /** Without --apply nothing is sent AND nothing is claimed, or the dry run burns the nudge. */
    public function test_the_dry_run_neither_sends_nor_claims(): void
    {
        $role = $this->createRole($this->owner());
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->artisan('app:send-activation-nudges')->assertExitCode(0);

        $this->assertNothingSent();
    }

    public function test_it_does_nothing_on_a_selfhosted_install(): void
    {
        config(['app.hosted' => false]);
        $role = $this->createRole($this->owner());
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->nudge();

        $this->assertNothingSent();
    }

    /**
     * "First" has to mean first, or the first run congratulates every established seller.
     *
     * A recent sale alone qualified, and the once-per-(role, key) claim only makes that read as
     * first for a schedule that had never sold when this shipped. The four accounts carrying 89%
     * of all ticket volume would each have been told they sold their first ticket.
     */
    public function test_a_long_time_seller_is_not_congratulated_on_a_first_sale(): void
    {
        $role = $this->createRole($this->owner());
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 20]);

        // Sold a year ago AND last week: the recent sale is real, but it is not the first.
        $this->createSale($event, $role, ['payment_amount' => 20, 'paid_at' => now()->subYear()], $ticket);
        $this->createSale($event, $role, ['payment_amount' => 20, 'paid_at' => now()->subDay()], $ticket);

        $this->nudge('first_sale');

        $this->assertNothingSent();
    }

    /** An undated legacy sale counts as old, or `paid_at < cutoff` lets it through as first. */
    public function test_an_undated_older_sale_still_blocks_the_first_sale_nudge(): void
    {
        $role = $this->createRole($this->owner());
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 20]);

        $old = $this->createSale($event, $role, ['payment_amount' => 20, 'paid_at' => now()->subYear()], $ticket);
        $old->forceFill(['paid_at' => null])->saveQuietly();
        $this->createSale($event, $role, ['payment_amount' => 20, 'paid_at' => now()->subDay()], $ticket);

        $this->nudge('first_sale');

        $this->assertNothingSent();
    }

    /** An old RSVP is not a sale, so it must not disqualify a genuine first paid sale. */
    public function test_an_old_rsvp_does_not_block_a_genuine_first_sale(): void
    {
        $role = $this->createRole($this->owner());
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 20]);

        $this->createSale($event, $role, [
            'payment_amount' => 0, 'payment_method' => 'rsvp', 'paid_at' => now()->subYear(),
        ], $ticket);
        $this->createSale($event, $role, ['payment_amount' => 20, 'paid_at' => now()->subDay()], $ticket);

        $this->nudge('first_sale');

        $this->assertSent('first_sale');
    }

    /**
     * Platform mail goes out on the PLATFORM mailer.
     *
     * A non-null roleId routes SendQueuedEmail through RoleMailerService::sendForRole(), which
     * sends via the schedule's own SMTP, meters it against their allowance, and silently drops it
     * while that SMTP is inside its 24h failure window - after the claim is already written.
     * Same rule WindDownReminderTest states for the wind-down notice.
     */
    public function test_the_nudge_goes_out_on_the_platform_mailer(): void
    {
        $role = $this->createRole($this->owner());
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->nudge('no_ticket_type');

        Queue::assertPushed(SendQueuedEmail::class, function ($job) {
            $roleId = new \ReflectionProperty($job, 'roleId');
            $roleId->setAccessible(true);

            return $roleId->getValue($job) === null;
        });
    }

    /**
     * One owner, one nudge per run - the only other ceiling is a global batch.
     *
     * An account on this install owns 37 schedules, 34 of them dormant with history, so without
     * this the first run hands one person a mailshot.
     */
    public function test_one_owner_gets_at_most_one_nudge_per_run(): void
    {
        $owner = $this->owner();

        foreach (range(1, 4) as $ignored) {
            $role = $this->createRole($owner);
            $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        }

        $this->nudge();

        Queue::assertPushed(SendQueuedEmail::class, 1);
        $this->assertSame(1, DB::table('schedule_nudges')->count(),
            'the other three must NOT be claimed, so they are still due next run');
    }

    /**
     * And the ones it skipped are still due, but a week apart: the per-owner cooldown is what
     * keeps an hourly schedule from mailing an owner of 17 stalled schedules every day.
     */
    public function test_the_skipped_schedules_drain_one_a_week(): void
    {
        $owner = $this->owner();

        foreach (range(1, 3) as $ignored) {
            $role = $this->createRole($owner);
            $this->createEvent($role, ['starts_at' => now()->addDays(30)->format('Y-m-d H:i:s')]);
        }

        // Asserted after EACH run, not just at the end: with no cap all three go out on the
        // first run and the closing count is 3 either way.
        $this->nudge();
        $this->assertSame(1, DB::table('schedule_nudges')->count());

        $this->travel(3)->days();
        $this->nudge();
        $this->assertSame(1, DB::table('schedule_nudges')->count(), 'inside the cooldown: nothing');

        $this->travel(5)->days();
        $this->nudge();
        $this->assertSame(2, DB::table('schedule_nudges')->count(), 'a week on: the next one');
    }

    /** The cooldown is per owner: someone else's nudge does not hold this owner back. */
    public function test_the_cooldown_does_not_cross_owners(): void
    {
        $first = $this->createRole($this->owner());
        $this->createEvent($first, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        $this->nudge();

        $second = $this->createRole($this->owner());
        $this->createEvent($second, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        $this->nudge();

        $this->assertSame(2, DB::table('schedule_nudges')->count());
    }

    /** A congratulation is not held back by the cooldown - late, it reads like a bug. */
    public function test_a_first_sale_is_congratulated_inside_the_cooldown(): void
    {
        $owner = $this->owner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        $this->nudge();
        $this->assertSent('no_ticket_type');

        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 20]);
        $this->createSale($event, $role, ['payment_amount' => 20, 'paid_at' => now()->subDay()], $ticket);
        $this->nudge('first_sale');

        $this->assertSame(1, DB::table('schedule_nudges')->where('nudge_key', 'first_sale')->count());
    }

    /** Never two of our emails at once: the weekly digest covers what a nudge would say. */
    public function test_no_nudge_within_two_days_of_the_owners_digest(): void
    {
        $owner = $this->owner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        DB::table('owner_digests')->insert(['user_id' => $owner->id, 'week' => now()->format('o-\WW'), 'schedules' => 1, 'created_at' => now()->subDay()]);

        $this->nudge();
        $this->assertNothingSent();

        $this->travel(2)->days();
        $this->nudge();
        $this->assertSame(1, DB::table('schedule_nudges')->count());
    }

    /** Sent in the owner's own morning, not at one UTC hour that is midnight somewhere. */
    public function test_it_waits_for_the_owners_local_morning(): void
    {
        $owner = $this->owner(['timezone' => 'Asia/Tokyo']);
        $role = $this->createRole($owner);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        // 18:00 UTC Tuesday is 03:00 Wednesday in Tokyo: not sent, and not claimed either.
        // (Weekdays pinned: Monday is the digest's morning, when nudges stand aside.)
        $this->travelTo(now()->utc()->next('Tuesday')->setTime(18, 0));
        $this->nudge(now: false);
        $this->assertNothingSent();

        // 01:00 UTC is 10:00 in Tokyo.
        $this->travelTo(now()->utc()->addDay()->setTime(1, 0));
        $this->nudge(now: false);
        $this->assertSame(1, DB::table('schedule_nudges')->count());
    }

    /**
     * Monday morning is the weekly digest's, and this command runs first in the same tick, so it
     * stands aside that day rather than landing a second email beside the digest.
     */
    public function test_it_does_not_nudge_on_the_owners_digest_morning(): void
    {
        $owner = $this->owner(['timezone' => 'UTC']);
        $role = $this->createRole($owner, 'venue', ['timezone' => 'UTC']);
        $this->createEvent($role, ['starts_at' => now()->addDays(20)->format('Y-m-d H:i:s')]);

        $this->travelTo(now()->utc()->next('Monday')->setTime(10, 0));
        $this->nudge(now: false);
        $this->assertNothingSent();

        $this->travelTo(now()->utc()->next('Thursday')->setTime(10, 0));
        $this->nudge(now: false);
        $this->assertSame(1, DB::table('schedule_nudges')->count());
    }

    /** A first sale is congratulated on a Monday all the same. */
    public function test_a_first_sale_goes_out_on_a_monday(): void
    {
        $owner = $this->owner(['timezone' => 'UTC']);
        $role = $this->createRole($owner, 'venue', ['timezone' => 'UTC']);
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 20]);

        $this->travelTo(now()->utc()->next('Monday')->setTime(10, 0));
        $this->createSale($event, $role, ['payment_amount' => 20, 'paid_at' => now()->subDay()], $ticket);
        $this->nudge('first_sale', now: false);

        $this->assertSent('first_sale');
    }

    /**
     * The free-plan copy offers the card-free selling trial, which an owner who has already had it
     * cannot start. Promising it only for the paywall to refuse is worse than not mentioning it.
     */
    public function test_the_free_copy_only_offers_the_trial_to_owners_who_can_start_it(): void
    {
        $trialLine = 'free for 7 days';

        $firstTimer = $this->createFreeRole($this->owner());
        $html = (new ActivationNudge($firstTimer, 'no_ticket_type_free'))->render();
        $this->assertStringContainsString($trialLine, $html);

        $owner = $this->owner();
        $this->createFreeRole($owner)->forceFill(['ticket_trial_ends_at' => now()->subMonth()])->save();
        $second = $this->createFreeRole($owner);
        $html = (new ActivationNudge($second->fresh(), 'no_ticket_type_free'))->render();
        $this->assertStringNotContainsString($trialLine, $html);
        $this->assertStringContainsString('no way to sign up', $html);
    }

    /**
     * The two scheduler rails hold different mutexes. When this run loses the claim on one of an
     * owner's schedules, the other rail is emailing that owner right now, so this run must not
     * move on to their next due schedule and send a second nudge.
     */
    public function test_a_lost_claim_still_counts_as_the_owners_nudge_this_run(): void
    {
        $owner = $this->owner();
        $first = $this->createRole($owner);
        $this->createEvent($first, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        $second = $this->createRole($owner);
        $this->createEvent($second, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        // The other rail claims the first schedule between this run's candidate read and its own
        // insert: fire once, right after the candidate SELECT has run.
        $raced = false;
        DB::listen(function ($query) use (&$raced, $first) {
            if (! $raced && str_starts_with(ltrim($query->sql), 'select') && str_contains($query->sql, 'from `roles`')
                && str_contains($query->sql, 'schedule_nudges')) {
                $raced = true;
                DB::table('schedule_nudges')->insert(['role_id' => $first->id, 'nudge_key' => 'no_ticket_type', 'created_at' => now()]);
            }
        });

        $this->nudge('no_ticket_type');

        $this->assertTrue($raced, 'sanity: the simulated race fired');

        Queue::assertNothingPushed();
        $this->assertSame(0, DB::table('schedule_nudges')->where('role_id', $second->id)->count(),
            'the owner was being emailed by the other rail; this run must not add a second');
    }

    /** Sunday's nudge would be followed by Monday's digest; Sundays are left alone too. */
    public function test_it_does_not_nudge_on_the_owners_sunday(): void
    {
        $owner = $this->owner(['timezone' => 'UTC']);
        $role = $this->createRole($owner, 'venue', ['timezone' => 'UTC']);
        $this->createEvent($role, ['starts_at' => now()->addDays(20)->format('Y-m-d H:i:s')]);

        $this->travelTo(now()->utc()->next('Sunday')->setTime(10, 0));
        $this->nudge(now: false);

        $this->assertNothingSent();
    }

    /**
     * Only the event that can take money counts. An old grandfathered event elsewhere does not
     * make a new priced event sellable on a free schedule, so there is nothing to connect for.
     */
    public function test_an_old_grandfathered_event_does_not_trigger_the_gateway_nudge(): void
    {
        $role = $this->createFreeRole($this->owner(['stripe_account_id' => null]));
        $old = $this->createEvent($role, ['starts_at' => now()->subDays(300)->format('Y-m-d H:i:s'), 'creator_role_id' => $role->id]);
        $old->forceFill(['tickets_grandfathered_at' => now()->subYear()])->save();
        $this->createTicket($old, ['price' => 20]);
        $new = $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s'), 'creator_role_id' => $role->id]);
        $this->createTicket($new, ['price' => 20]);

        $this->nudge('no_gateway');

        $this->assertNothingSent();
    }

    /** A timezone PHP does not know falls back rather than stopping the run. */
    public function test_a_bad_timezone_falls_back_to_the_schedules(): void
    {
        $owner = $this->owner(['timezone' => 'Not/AZone']);
        $role = $this->createRole($owner, 'venue', ['timezone' => 'UTC']);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->travelTo(now()->utc()->next('Wednesday')->setTime(10, 0));
        $this->nudge(now: false);

        $this->assertSame(1, DB::table('schedule_nudges')->count());
    }

    /** Two owners are independent - the cap is per user, not per run. */
    public function test_the_cap_is_per_owner_not_per_run(): void
    {
        foreach (range(1, 2) as $ignored) {
            $role = $this->createRole($this->owner());
            $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        }

        $this->nudge();

        Queue::assertPushed(SendQueuedEmail::class, 2);
    }

    /**
     * A curator that only LISTS someone else's event does not own it.
     *
     * The editor's Tickets panel follows canViewEventData(), which has a curator exception, so
     * this nudge would have linked to a page where they cannot act.
     */
    public function test_a_curator_listing_someone_elses_event_is_not_nudged(): void
    {
        $venue = $this->createRole($this->owner(), 'venue');
        $event = $this->createEvent($venue, [
            'starts_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'creator_role_id' => $venue->id,
        ]);

        $curator = $this->createRole($this->owner(), 'curator');
        $event->roles()->attach($curator->id, ['is_accepted' => true]);

        $this->nudge('no_ticket_type');

        // The venue that created it is still nudged; the curator is not.
        $this->assertSame(1, DB::table('schedule_nudges')->count());
        $this->assertSame(
            $venue->id,
            (int) DB::table('schedule_nudges')->value('role_id'),
            'the creating venue is nudged, the listing curator is not'
        );
    }

    /** A decline leaves the pivot in place, so it must be read, not just ignored. */
    public function test_a_declined_event_does_not_nudge_the_schedule_that_declined_it(): void
    {
        $venue = $this->createRole($this->owner(), 'venue');
        $event = $this->createEvent($venue, [
            'starts_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'creator_role_id' => $venue->id,
        ]);

        $other = $this->createRole($this->owner(), 'venue');
        $event->roles()->attach($other->id, ['is_accepted' => false]);

        $this->nudge('no_ticket_type');

        $this->assertSame(1, DB::table('schedule_nudges')->count());
        $this->assertSame($venue->id, (int) DB::table('schedule_nudges')->value('role_id'));
    }

    /**
     * But a venue that ACCEPTED a talent's event IS nudged.
     *
     * canViewEventData() grants it the Tickets panel, and it is the persona this whole thing is
     * for. Scoping on creator_role_id alone - my first attempt at this fix - would have silently
     * stopped nudging them.
     */
    public function test_a_venue_that_accepted_someone_elses_event_is_nudged(): void
    {
        $talent = $this->createRole($this->owner(), 'talent');
        $event = $this->createEvent($talent, [
            'starts_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'creator_role_id' => $talent->id,
        ]);

        $venueOwner = $this->owner();
        $venue = $this->createRole($venueOwner, 'venue');
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $this->nudge('no_ticket_type');

        $claimed = DB::table('schedule_nudges')->pluck('role_id')->map(fn ($id) => (int) $id)->all();
        $this->assertContains($venue->id, $claimed, 'the accepting venue can price it, so it is nudged');
    }

    /**
     * A schedule owns its OWN event whatever the pivot says.
     *
     * The first branch of ownedEvents(), and the one the other ownership tests cannot reach:
     * they all lean on an accepted pivot. A pending appointment sits at is_accepted null and an
     * uncurate leaves it false, so a creator whose pivot is not accepted must still be nudged.
     */
    public function test_a_schedule_owns_its_own_event_even_with_an_unaccepted_pivot(): void
    {
        $role = $this->createRole($this->owner(), 'venue');
        $event = $this->createEvent($role, [
            'starts_at' => now()->addDays(10)->format('Y-m-d H:i:s'),
            'creator_role_id' => $role->id,
            // false, not null: the helper's `?? true` swallows a null, and a declined pivot is
            // the sharper case anyway - uncurate() leaves exactly this state.
            'is_accepted' => false,
        ]);

        $this->assertFalse((bool) $event->roles()->first()->pivot->is_accepted, 'the fixture really is unaccepted');

        $this->nudge('no_ticket_type');

        $this->assertSent('no_ticket_type');
    }

    /**
     * The command must stay off both scheduler rails until someone has read a real pass.
     *
     * Its windows are wide enough that a first run over an install that has never had it reaches
     * a large backlog at once, so the first send should be a deliberate act. Asserted rather than
     * commented, because the repo rule is that the two rails stay in sync and the obvious "fix"
     * for a missing registration is to add one back.
     */
    /**
     * Scheduled on both rails, sending (--apply) and never with --now: the local-morning window
     * is what makes an hourly schedule reach each owner at a sensible time.
     * CronRailSyncTest checks the two rails agree on cadence and gate.
     */
    public function test_the_command_is_scheduled_on_both_rails_without_now(): void
    {
        foreach (['routes/console.php', 'app/Http/Controllers/AppController.php'] as $file) {
            $body = file_get_contents(base_path($file));

            $this->assertStringContainsString("Artisan::call('app:send-activation-nudges', ['--apply' => true])", $body, $file);
            $this->assertDoesNotMatchRegularExpression("/app:send-activation-nudges'[^;]*--now/", $body, $file);
        }
    }

    /** The mail carries the schedule it is about, and a CTA that goes where the ask is. */
    public function test_the_mail_renders_with_a_working_cta(): void
    {
        $role = $this->createRole($this->owner(), 'venue', ['name' => 'The Blue Room']);

        $rendered = (new ActivationNudge($role, 'no_ticket_type'))->render();

        $this->assertStringContainsString('The Blue Room', $rendered);
        $this->assertStringContainsString($role->subdomain, $rendered);
        $this->assertStringNotContainsString('activation_nudge_', $rendered, 'every key must resolve');
    }

    /**
     * Every language actually DEFINES every key, and none of them is the English string.
     *
     * Asserted against the language files, not against a rendered mail. __() silently falls
     * back to the English line when a key is missing, so a render-based check passes with a
     * whole language deleted - it only ever proves the English file is complete.
     */
    public function test_every_language_defines_its_own_copy(): void
    {
        $keys = [];
        foreach (['no_event', 'no_ticket_type', 'no_ticket_type_free', 'no_gateway', 'first_sale', 'idle_30', 'idle_60'] as $nudge) {
            foreach (['subject', 'heading', 'body', 'cta'] as $part) {
                $keys[] = "activation_nudge_{$part}_{$nudge}";
            }
        }
        $keys[] = 'activation_nudge_body_no_ticket_type_free_no_trial';

        $en = require resource_path('lang/en/messages.php');

        foreach (array_keys(config('app.supported_languages')) as $lang) {
            $lines = require resource_path("lang/{$lang}/messages.php");

            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $lines, "{$lang} is missing {$key}");

                if ($lang !== 'en') {
                    $this->assertNotSame(
                        $en[$key], $lines[$key],
                        "{$lang}/{$key} is the English string copied over, not a translation"
                    );
                }

                // The placeholder has to survive translation or the mail names no schedule.
                if (str_contains($en[$key], ':schedule')) {
                    $this->assertStringContainsString(':schedule', $lines[$key], "{$lang}/{$key} lost :schedule");
                }
            }
        }
    }

    /**
     * An RTL locale marks the body direction.
     *
     * The mail interpolates a user-supplied schedule name into prose, so an unmarked RTL body
     * renders the Latin name in the wrong place. <x-email.layout> sets the direction from the locale.
     */
    public function test_rtl_locales_mark_the_body_direction(): void
    {
        $role = $this->createRole($this->owner(), 'venue', ['name' => 'The Blue Room']);

        foreach (array_keys(config('app.supported_languages')) as $lang) {
            app()->setLocale($lang);
            $rendered = (new ActivationNudge($role, 'no_event'))->render();

            $this->assertSame(
                in_array($lang, ['ar', 'he']),
                str_contains($rendered, 'dir="rtl"'),
                "{$lang} direction"
            );
            $this->assertStringContainsString('The Blue Room', $rendered, "{$lang} lost the name");
        }
    }

    //
    // In-app dismissals. The dashboard panel asks the same things, so a step the recipient has
    // already turned down there must not then arrive by email.
    //

    private function dismissInApp(User $user, Role $role, string $stepType): void
    {
        DismissedNextStep::create([
            'user_id' => $user->id,
            'role_id' => $role->id,
            'step_type' => $stepType,
        ]);
    }

    public function test_a_dismissed_tickets_step_silences_the_free_copy_too(): void
    {
        $owner = $this->owner();
        $role = $this->createFreeRole($owner);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        $this->dismissInApp($owner, $role, 'next_step_tickets');

        $this->nudge('no_ticket_type_free');

        $this->assertNothingSent();
    }

    public function test_a_dismissed_tickets_step_silences_the_no_ticket_type_nudge(): void
    {
        $owner = $this->owner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->dismissInApp($owner, $role, 'next_step_tickets');

        $this->nudge('no_ticket_type');

        $this->assertNothingSent();
    }

    public function test_a_dismissed_payments_step_silences_the_no_gateway_nudge(): void
    {
        $owner = $this->owner(['stripe_account_id' => null]);
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);
        $this->createTicket($event, ['price' => 25]);

        $this->dismissInApp($owner, $role, 'next_step_payments');

        $this->nudge('no_gateway');

        $this->assertNothingSent();
    }

    /**
     * The event step fans out to three keys, because the panel uses one type for both "Add your
     * first event" and "Add your next date" - it picks the copy, not the type - and those are
     * no_event and the two idle windows. A one-to-one map would leave the idle mail firing at
     * someone who has already said the schedule is finished.
     */
    public function test_a_dismissed_event_step_silences_no_event_and_both_idle_windows(): void
    {
        foreach ([
            'no_event' => ['next_step_first_event', null],
            'idle_30' => ['next_step_next_event', 40],
            'idle_60' => ['next_step_next_event', 70],
        ] as $key => [$stepType, $daysAgo]) {
            $owner = $this->owner();
            $role = $this->createRole($owner);
            $role->forceFill(['created_at' => now()->subDays(2)])->save();

            if ($daysAgo) {
                $this->createEvent($role, ['starts_at' => now()->subDays($daysAgo)->format('Y-m-d H:i:s')]);
            }

            $this->dismissInApp($owner, $role, $stepType);

            $this->nudge($key);

            // Not assertNothingSent(): inside a loop it cannot say which key regressed.
            Queue::assertNothingPushed();
            $this->assertSame(0, DB::table('schedule_nudges')->count(), "{$key} was not silenced by the dismissal");
        }
    }

    /** Turning down one suggestion is not blanket consent to hear nothing. */
    public function test_a_dismissal_of_a_different_step_does_not_silence_this_nudge(): void
    {
        $owner = $this->owner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->dismissInApp($owner, $role, 'next_step_first_event');

        $this->nudge('no_ticket_type');

        $this->assertSent('no_ticket_type');
    }

    /**
     * The two halves are keyed differently and this is the rule that resolves it.
     *
     * A dismissal is per USER, because several editors share a schedule; a nudge is per schedule
     * and is addressed to roles.user_id. A co-admin clearing their own dashboard must not
     * silence mail to an owner who never saw the panel.
     */
    public function test_a_non_owner_admins_dismissal_does_not_silence_the_owners_email(): void
    {
        $owner = $this->owner();
        $role = $this->createRole($owner);
        $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $admin = $this->createOwner();
        $role->users()->attach($admin->id, ['level' => 'admin']);
        $this->dismissInApp($admin, $role, 'next_step_tickets');

        $this->nudge('no_ticket_type');

        $this->assertSent('no_ticket_type');
        $this->assertQueuedTo($owner->email);
    }

    /**
     * Asserted on the claimed row rather than the send count: $seenUsers caps one send per owner
     * per run, so a bare "one email went out" passes whether the filter is keyed on the schedule
     * or ignores it entirely.
     */
    public function test_a_dismissal_on_one_schedule_does_not_silence_another(): void
    {
        $owner = $this->owner();
        $quiet = $this->createRole($owner);
        $other = $this->createRole($owner);
        $this->createEvent($quiet, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        $this->createEvent($other, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);

        $this->dismissInApp($owner, $quiet, 'next_step_tickets');

        $this->nudge('no_ticket_type');

        $this->assertSame(0, DB::table('schedule_nudges')->where('role_id', $quiet->id)->count());
        $this->assertSame(1, DB::table('schedule_nudges')->where('role_id', $other->id)->count());
    }

    /** One gateway per account, so one answer: schedule B must not be mailed what A declined. */
    public function test_a_payments_dismissal_silences_the_gateway_nudge_on_every_schedule(): void
    {
        $owner = $this->owner(['stripe_account_id' => null]);
        $a = $this->createRole($owner);
        $this->createTicket($this->createEvent($a), ['price' => 25]);
        $b = $this->createRole($owner);
        $this->createTicket($this->createEvent($b), ['price' => 25]);

        // Answered once, on one schedule.
        $this->dismissInApp($owner, $a, 'next_step_payments');

        $this->nudge('no_gateway');

        $this->assertNothingSent();
    }

    /**
     * The split in the other direction: turning down the day-one ask must NOT silence the
     * dormancy mail, which is a different situation on a schedule that has since published.
     */
    public function test_a_dismissed_first_event_ask_does_not_silence_the_idle_nudges(): void
    {
        foreach (['idle_30' => 40, 'idle_60' => 70] as $key => $daysAgo) {
            $owner = $this->owner();
            $role = $this->createRole($owner);
            $this->createEvent($role, ['starts_at' => now()->subDays($daysAgo)->format('Y-m-d H:i:s')]);

            $this->dismissInApp($owner, $role, 'next_step_first_event');

            $this->nudge($key);

            $this->assertSame(
                1,
                DB::table('schedule_nudges')->where('nudge_key', $key)->count(),
                "{$key} was silenced by a dismissal of a different ask"
            );
        }
    }

    /**
     * first_sale congratulates rather than asks. It has no dismiss control on the panel, so
     * nothing can silence it, and mapping it to a step type would silence a thank-you.
     */
    public function test_a_first_sale_congratulation_is_never_silenced(): void
    {
        $owner = $this->owner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role);
        $ticket = $this->createTicket($event, ['price' => 20]);
        $this->createSale($event, $role, ['payment_amount' => 20, 'paid_at' => now()->subDay()], $ticket);

        foreach (DismissedNextStep::STEP_TYPES as $stepType) {
            $this->dismissInApp($owner, $role, $stepType);
        }

        $this->nudge('first_sale');

        $this->assertSent('first_sale');
    }
}
