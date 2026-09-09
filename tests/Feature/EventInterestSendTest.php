<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\EventInterestNotification;
use App\Models\Event;
use App\Models\EventInterest;
use App\Models\Role;
use App\Models\User;
use App\Utils\UrlUtils;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The two sends.
 *
 * The assertions that matter are the ones about what is NOT sent: this mail goes out on the shared
 * platform sending reputation and cannot be recalled, so the pre-claim, the cancellation guard and
 * the double-run guard are the feature.
 */
class EventInterestSendTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    private Event $event;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();

        $this->role = $this->createRole($this->createOwner());
        $this->event = $this->createEvent($this->role, ['creator_role_id' => $this->role->id]);
    }

    private function capture(array $overrides = []): EventInterest
    {
        $this->postJson(
            route('event.interest.join', ['subdomain' => $this->role->subdomain]),
            $overrides + [
                'email' => 'fan@fans.test',
                'event_id' => UrlUtils::encodeId($this->event->id),
                'event_date' => $this->event->getStartDateTime(null, true, $this->event->scheduleTimezone())->format('Y-m-d'),
            ]
        )->assertOk()->assertJson(['success' => true]);

        return EventInterest::firstOrFail();
    }

    private function enableTickets(): void
    {
        $this->event->forceFill(['tickets_enabled' => true])->save();
        $this->createTicket($this->event, ['price' => 10]);
        $this->event->refresh()->load('tickets');
    }

    private function queuedCount(): int
    {
        return count(Queue::pushed(SendQueuedEmail::class) ?: []);
    }

    public function test_tickets_going_on_sale_after_the_ask_sends_one_email(): void
    {
        $interest = $this->capture();
        $this->assertNull($interest->tickets_notified_at, 'an event with nothing on sale must not pre-claim');

        $this->enableTickets();
        $this->artisan('app:send-event-interest-mail', ['--kind' => 'tickets', '--apply' => true])->assertSuccessful();

        $this->assertSame(1, $this->queuedCount());
        $this->assertNotNull($interest->fresh()->tickets_notified_at);
    }

    public function test_an_event_already_selling_when_asked_about_never_sends_the_on_sale_email(): void
    {
        // The pre-claim, and the reason this feature needs no watermark column. "Tickets are now on
        // sale" is only news to somebody who asked BEFORE they were; telling everyone else about
        // something already on the page they just used is the mailshot the guard exists to prevent.
        $this->enableTickets();
        $interest = $this->capture();

        $this->assertNotNull($interest->tickets_notified_at, 'capture must pre-claim when tickets already sell');

        $this->artisan('app:send-event-interest-mail', ['--kind' => 'tickets', '--apply' => true])->assertSuccessful();

        $this->assertSame(0, $this->queuedCount());
    }

    public function test_running_twice_sends_once(): void
    {
        $this->capture();
        $this->enableTickets();

        $this->artisan('app:send-event-interest-mail', ['--kind' => 'tickets', '--apply' => true])->assertSuccessful();
        $this->artisan('app:send-event-interest-mail', ['--kind' => 'tickets', '--apply' => true])->assertSuccessful();

        // What this pins is idempotency ACROSS runs: the stamp written by the first pass takes the
        // row out of the second pass's candidates query.
        //
        // It does NOT pin the `if (! $claimed)` guard in the command - deleting that still passes
        // here, because the candidates filter alone is enough when the runs are sequential. That
        // guard exists for the case a sequential test cannot reach: the two scheduler rails hold
        // different mutexes, so both can read the same unstamped row and race to send it. The
        // conditional UPDATE is what makes exactly one of them win.
        $this->assertSame(1, $this->queuedCount());
    }

    public function test_a_dry_run_sends_nothing_and_claims_nothing(): void
    {
        $interest = $this->capture();
        $this->enableTickets();

        $this->artisan('app:send-event-interest-mail', ['--kind' => 'tickets'])->assertSuccessful();

        $this->assertSame(0, $this->queuedCount());
        $this->assertNull($interest->fresh()->tickets_notified_at);
    }

    public function test_a_cancelled_event_sends_no_reminder(): void
    {
        // Deliberately inside the reminder window, and deliberately the REMINDER pass. The tickets
        // pass needs no is_cancelled guard of its own - Event::canSellTickets() already returns
        // false for a cancelled event, so a test built on that branch passes with the guard
        // deleted and pins nothing. The reminder is the branch the guard actually protects.
        $this->event->forceFill([
            'starts_at' => now()->addHours(24)->format('Y-m-d H:i:s'),
        ])->save();
        $this->event->refresh();

        $this->postJson(route('event.interest.join', ['subdomain' => $this->role->subdomain]), [
            'email' => 'fan@fans.test',
            'event_id' => UrlUtils::encodeId($this->event->id),
            'event_date' => $this->event->getStartDateTime(null, true, $this->event->scheduleTimezone())->format('Y-m-d'),
        ])->assertOk()->assertJson(['success' => true]);

        $this->event->forceFill(['is_cancelled' => true])->save();

        $this->artisan('app:send-event-interest-mail', ['--kind' => 'reminder', '--apply' => true])->assertSuccessful();

        // A cancelled event owes its list a cancellation notice, which EventChangeNotifier sends.
        // Cheerfully reminding them to turn up is the worst possible message.
        $this->assertSame(0, $this->queuedCount());
    }

    public function test_the_reminder_fires_inside_the_window(): void
    {
        // Inside the 48h default.
        $this->event->forceFill(['starts_at' => now()->addHours(24)->format('Y-m-d H:i:s')])->save();
        $this->event->refresh();

        $this->postJson(route('event.interest.join', ['subdomain' => $this->role->subdomain]), [
            'email' => 'fan@fans.test',
            'event_id' => UrlUtils::encodeId($this->event->id),
            'event_date' => $this->event->getStartDateTime(null, true, $this->event->scheduleTimezone())->format('Y-m-d'),
        ])->assertOk()->assertJson(['success' => true]);

        $this->artisan('app:send-event-interest-mail', ['--kind' => 'reminder', '--apply' => true])->assertSuccessful();

        $this->assertSame(1, $this->queuedCount());
        $this->assertNotNull(EventInterest::firstOrFail()->reminder_sent_at);
    }

    public function test_the_reminder_does_not_fire_outside_the_window(): void
    {
        // createEvent() defaults to +7 days, well outside the 48h window.
        $this->capture();

        $this->artisan('app:send-event-interest-mail', ['--kind' => 'reminder', '--apply' => true])->assertSuccessful();

        $this->assertSame(0, $this->queuedCount());
        $this->assertNull(EventInterest::firstOrFail()->reminder_sent_at);
    }

    public function test_the_mail_carries_a_working_one_click_unsubscribe_token(): void
    {
        $interest = $this->capture();
        $this->enableTickets();
        $this->artisan('app:send-event-interest-mail', ['--kind' => 'tickets', '--apply' => true])->assertSuccessful();

        $this->assertSame(1, $this->queuedCount());

        // Built directly rather than reached through the queued job: SendQueuedEmail::$mailable is
        // protected, and the contract under test is the mailable's, not the job's.
        $headers = (new EventInterestNotification(
            $this->role,
            $this->event->fresh(),
            $interest->fresh(),
            EventInterestNotification::KIND_TICKETS,
            'https://example.test/event',
            route('event.interest.show_unsubscribe', ['token' => $interest->token]),
        ))->headers();
        $listUnsubscribe = $headers->text['List-Unsubscribe'] ?? '';

        // The row's OWN token. WaitlistNotification points this header at
        // RoleController::unsubscribe(), which matches on Role::where('email') - so a one-click
        // POST, carrying neither an address nor a signature, fails and the header is decorative.
        $this->assertStringContainsString('/int/u/'.$interest->token, $listUnsubscribe);
        $this->assertSame('List-Unsubscribe=One-Click', $headers->text['List-Unsubscribe-Post'] ?? null);

        // And it genuinely works.
        $this->post('/int/u/'.$interest->token)->assertOk();
        $this->assertSame(0, EventInterest::count());
    }

    public function test_a_cancellation_reaches_the_interest_list_without_the_schedules_own_smtp(): void
    {
        // The promise in event_interest_help is "one if the date or venue changes". Buyers keep the
        // hasEmailSettings() gate they have always had - a receipt came from that address, so a
        // platform-branded follow-up would be a surprise - but the interest list cannot sit behind
        // it, or the promise is false for every schedule on the platform mailer, which is most of
        // them. This fixture has no SMTP settings at all.
        $this->capture();
        $this->assertFalse($this->role->hasEmailSettings());

        \App\Services\EventChangeNotifier::notifyCancellation($this->event->fresh(), $this->role);

        $this->assertSame(1, $this->queuedCount());
    }

    public function test_a_change_notice_reaches_the_interest_list(): void
    {
        $this->capture();

        \App\Services\EventChangeNotifier::notifyChange($this->event->fresh(), $this->role, ['starts_at' => 'x']);

        $this->assertSame(1, $this->queuedCount());
    }

    public function test_an_unsubscribed_person_gets_no_change_notice(): void
    {
        $interest = $this->capture();
        $this->post('/int/u/'.$interest->token)->assertOk();

        \App\Services\EventChangeNotifier::notifyCancellation($this->event->fresh(), $this->role);

        $this->assertSame(0, $this->queuedCount());
    }

    public function test_cancelling_through_the_controller_reaches_the_interest_list(): void
    {
        // THE test the original suite was missing. The three tests above call EventChangeNotifier
        // directly and pass whether or not anything ever reaches it - and nothing did: both
        // dispatch sites gated on hasRecipients(), which counts SALES, and the cancel route also
        // required the schedule's own SMTP. An event with an interest list and no sales, on a
        // schedule using the platform mailer, failed every one of those gates.
        //
        // Driven through the HTTP route so the gate, the job dispatch and the notifier are all in
        // the path.
        $this->capture();
        $this->assertFalse($this->role->hasEmailSettings(), 'the fixture must use the platform mailer');
        $this->assertSame(0, $this->event->sales()->count(), 'and must have no sales');

        $this->actingAs(User::find($this->role->user_id))
            ->post(route('event.cancel', [
                'subdomain' => $this->role->subdomain,
                'hash' => UrlUtils::encodeId($this->event->id),
            ]), ['notify_attendees' => 1])
            ->assertRedirect();

        // Assert the JOB, not a SendQueuedEmail: Queue::fake() intercepts NotifyEventCancelled
        // itself, so the notifier never runs inside this test. The gate is what is under test, and
        // the notifier's own behaviour is covered by the direct-call tests above.
        Queue::assertPushed(\App\Jobs\NotifyEventCancelled::class);
    }

    public function test_the_editor_is_told_someone_is_waiting_even_with_no_sales(): void
    {
        // The other half: the dispatch gate is useless if the UI never offers the option.
        // cancelWillNotify() and shouldPromptNotify() both read registrantCount, which is
        // sales-only, so the organizer was never asked in the first place.
        $this->capture();

        $html = $this->actingAs(User::find($this->role->user_id))
            ->get(route('event.edit', [
                'subdomain' => $this->role->subdomain,
                'hash' => UrlUtils::encodeId($this->event->id),
            ]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('interestedCount: 1', $html);
    }

    public function test_a_dateless_event_does_not_poison_the_mail(): void
    {
        // Dateless events are a supported capture target - resolveDate() returns '' for them and
        // the migration documents the sentinel - and canSellTickets() skips all its date checks for
        // one, so the tickets pass could reach it. Both mail views then called getStartDateTime(),
        // which has NO null guard: it reaches Carbon::createFromFormat('Y-m-d H:i:s', null) and
        // throws, so the ?-> never runs. The job died with the claim column already stamped.
        $this->event->forceFill(['starts_at' => null, 'tickets_enabled' => true])->save();
        $this->createTicket($this->event, ['price' => 10]);
        $this->event->refresh();

        \App\Models\EventInterest::create([
            'event_id' => $this->event->id,
            'event_date' => '',
            'email' => 'fan@fans.test',
            'confirmed_at' => now(),
            'token' => \App\Models\EventInterest::newToken(),
        ]);

        // The change path has no starts_at guard of its own, so this is the one that must not throw.
        \App\Services\EventChangeNotifier::notifyCancellation($this->event->fresh(), $this->role);

        $this->assertSame(1, $this->queuedCount());

        // And the rendered body is the real assertion - a throw here is the defect.
        $interest = \App\Models\EventInterest::firstOrFail();
        $html = (new EventInterestNotification(
            $this->role,
            $this->event->fresh(),
            $interest,
            EventInterestNotification::KIND_CANCELLED,
            'https://example.test/event',
            'https://example.test/int/u/'.$interest->token,
        ))->render();

        $this->assertStringContainsString($this->event->name, $html);
    }

    public function test_a_past_occurrence_leaves_the_tickets_window(): void
    {
        // The starvation guard. isDue() skips without stamping, which is right for "not yet" but
        // wrong for "never": a passed occurrence can never become due again and nothing removes it,
        // so those rows keep the lowest ids and ORDER BY id LIMIT n eventually returns nothing but
        // corpses. They are excluded in SQL instead.
        $this->capture();
        $this->event->forceFill([
            'starts_at' => now()->subMonths(2)->format('Y-m-d H:i:s'),
        ])->save();
        \App\Models\EventInterest::query()->update([
            'event_date' => now()->subMonths(2)->format('Y-m-d'),
        ]);

        $reflection = new \ReflectionMethod(\App\Console\Commands\SendEventInterestMail::class, 'candidates');
        $reflection->setAccessible(true);
        $rows = iterator_to_array($reflection->invoke(
            app(\App\Console\Commands\SendEventInterestMail::class),
            EventInterestNotification::KIND_TICKETS,
            10
        ));

        $this->assertCount(0, $rows, 'a passed occurrence must not occupy the candidate window');
    }

    public function test_running_the_cancellation_job_actually_reaches_the_interest_list(): void
    {
        // Queue::assertPushed() proves a job was QUEUED. It does not prove that running it does
        // anything - and for three commits it did not: NotifyEventCancelled::handle() bailed on
        // hasEmailSettings() before ever calling the notifier, so the interest list heard nothing
        // while event_interest_help promised "one if the date or venue changes". The dispatch-level
        // test above passed throughout.
        //
        // This runs the job BODY, which is the only thing that could have caught it.
        $this->capture();
        $this->assertFalse($this->role->hasEmailSettings(), 'the fixture must use the platform mailer');

        (new \App\Jobs\NotifyEventCancelled($this->event->id))->handle();

        $this->assertSame(1, $this->queuedCount(), 'the interest list must actually be mailed');
    }

    public function test_running_the_change_job_actually_reaches_the_interest_list(): void
    {
        $this->capture();

        (new \App\Jobs\NotifyEventChange($this->event->id, ['starts_at' => 'x']))->handle();

        $this->assertSame(1, $this->queuedCount());
    }

    public function test_the_change_job_still_mails_buyers_on_a_schedule_with_its_own_smtp(): void
    {
        // The other side of removing that bail: the sales half must be untouched. It keeps the SMTP
        // gate, applied inside the notifier where it belongs.
        $this->role->email_settings = [
            'host' => 'smtp.test', 'username' => 'u', 'password' => 'p',
            'port' => 587, 'from_address' => 'sched@gmail.com', 'from_name' => 'Sched',
        ];
        $this->role->save();
        $this->createSale($this->event, $this->role, ['email' => 'buyer@gmail.com', 'status' => 'paid']);

        (new \App\Jobs\NotifyEventChange($this->event->fresh()->id, ['starts_at' => 'x']))->handle();

        $this->assertSame(1, $this->queuedCount(), 'the buyer must still be mailed');
    }
}
