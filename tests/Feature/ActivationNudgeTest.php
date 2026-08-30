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

    public function test_a_connected_gateway_disqualifies_the_payment_nudge(): void
    {
        $role = $this->createRole($this->owner(['stripe_account_id' => 'acct_123']));
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
     * routes/console.php and AppController::translateData hold different mutexes, so both can
     * read the same rows before either writes. The whereNotExists filter cannot see a claim
     * that has not been committed yet; only the index can reject it.
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
