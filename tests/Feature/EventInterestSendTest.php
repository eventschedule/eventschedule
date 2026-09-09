<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\EventInterestNotification;
use App\Models\Event;
use App\Models\EventInterest;
use App\Models\Role;
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
}
