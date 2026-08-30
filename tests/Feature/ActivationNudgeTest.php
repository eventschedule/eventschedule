<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\ActivationNudge;
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

    private function nudge(?string $key = null): void
    {
        $args = ['--apply' => true];
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

    public function test_it_nudges_a_schedule_with_paid_tickets_and_no_gateway(): void
    {
        $role = $this->createRole($this->owner(['stripe_account_id' => null]));
        $event = $this->createEvent($role);
        $this->createTicket($event, ['price' => 25]);

        $this->nudge('no_gateway');

        $this->assertSent('no_gateway');
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

    /** And the ones it skipped are still due, so they drain one run at a time. */
    public function test_the_skipped_schedules_are_still_due_on_the_next_run(): void
    {
        $owner = $this->owner();

        foreach (range(1, 3) as $ignored) {
            $role = $this->createRole($owner);
            $this->createEvent($role, ['starts_at' => now()->addDays(10)->format('Y-m-d H:i:s')]);
        }

        // Asserted after EACH run, not just at the end: with no cap all three go out on the
        // first run and the closing count is 3 either way, so a single final assertion passes
        // against the unfixed code.
        foreach ([1, 2, 3] as $expected) {
            $this->nudge();
            $this->assertSame($expected, DB::table('schedule_nudges')->count());
        }
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
    public function test_the_command_is_not_scheduled_on_either_rail(): void
    {
        foreach (['routes/console.php', 'app/Http/Controllers/AppController.php'] as $file) {
            $body = file_get_contents(base_path($file));

            $this->assertStringNotContainsString(
                "Artisan::call('app:send-activation-nudges'", $body,
                "{$file} schedules the nudges; it is meant to be hand-run for now"
            );
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
        foreach (['no_event', 'no_ticket_type', 'no_gateway', 'first_sale', 'idle_30', 'idle_60'] as $nudge) {
            foreach (['subject', 'heading', 'body', 'cta'] as $part) {
                $keys[] = "activation_nudge_{$part}_{$nudge}";
            }
        }

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
     * renders the Latin name in the wrong place - the same reason EventChanged passes isRtl.
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
}
