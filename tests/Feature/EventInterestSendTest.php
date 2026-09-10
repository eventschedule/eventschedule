<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\EventInterestNotification;
use App\Models\Event;
use App\Models\EventInterest;
use App\Models\Role;
use App\Models\User;
use App\Services\EventChangeNotifier;
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

    /** capture() against a schedule and event other than the ones setUp() built. */
    private function captureFor(Role $role, Event $event): void
    {
        $this->postJson(
            route('event.interest.join', ['subdomain' => $role->subdomain]),
            [
                'email' => 'fan@fans.test',
                'event_id' => UrlUtils::encodeId($event->id),
                'event_date' => $event->getStartDateTime(null, true, $event->scheduleTimezone())->format('Y-m-d'),
            ]
        )->assertOk()->assertJson(['success' => true]);
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

    /**
     * Actually SEND the mailable, so both bodies are rendered.
     *
     * Mailable::render() returns the HTML view only - Mailer::render() resolves the view half of
     * the pair and never touches $plain - so every existing assertion about this mail exercised
     * one of its two templates. The plain-text half shipped with adjacent Blade directives
     * (`@endif@if`), which compile to PHP with an unmatched `endif`, and threw a ParseError on
     * every real send while ->render() stayed green.
     *
     * The array transport still builds the whole Symfony message, so this renders both bodies
     * without anything leaving the machine. Keep it a real send, not a render.
     */
    public function test_sending_the_interest_mail_renders_both_bodies(): void
    {
        $interest = $this->capture();

        foreach ([
            EventInterestNotification::KIND_TICKETS,
            EventInterestNotification::KIND_REMINDER,
            EventInterestNotification::KIND_CHANGE,
            EventInterestNotification::KIND_CANCELLED,
        ] as $kind) {
            \Illuminate\Support\Facades\Mail::to('fan@fans.test')->send(new EventInterestNotification(
                $this->role,
                $this->event->fresh(),
                $interest,
                $kind,
                'https://example.test/event',
                'https://example.test/int/u/'.$interest->token,
            ));
        }

        $messages = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(4, $messages, 'every kind must render and send');

        foreach ($messages as $sent) {
            $body = $sent->getOriginalMessage();

            $text = (string) $body->getTextBody();
            $this->assertNotSame('', $text, 'the plain-text part must not be empty');
            $this->assertStringContainsString($this->event->name, $text);
            $this->assertStringContainsString('https://example.test/int/u/', $text,
                'every message carries its own unsubscribe link');

            // Blade source surviving into a rendered body is the signature of a block closed early
            // - an @endphp followed by an echo, or a comment containing its own terminator. Both
            // render as valid PHP, so only the output shows them.
            foreach (['@if', '@endif', '@php', '@__raw_block_', '{{'] as $marker) {
                $this->assertStringNotContainsString($marker, $text,
                    "unrendered Blade [$marker] leaked into the plain-text body");
            }

            $this->assertStringContainsString($this->event->name, (string) $body->getHtmlBody());
        }
    }

    public function test_a_selfhost_install_with_no_mail_transport_sends_nothing_and_claims_nothing(): void
    {
        // The claim is a one-shot conditional UPDATE taken BEFORE the dispatch and handed back only
        // when the dispatch THROWS - and the log/array mailer never throws. Without the transport
        // guard this run stamps tickets_notified_at on every candidate, and candidates() filters on
        // whereNull($column), so the row is unreachable for ever: the person who asked to hear about
        // this event never can be told, even after SMTP is configured.
        //
        // Reachable by default rather than exotic: .env.example ships MAIL_MAILER=log, capture is
        // single opt-in so rows are live at once, and canSendAudienceMail() - the command's only
        // other gate - returns true unconditionally off-platform.
        //
        // Both config keys are set by hand because the test env disagrees with a real selfhost
        // install on both: phpunit.xml forces MAIL_MAILER=array, and app.hosted is true here.
        $interest = $this->capture();
        $this->enableTickets();

        config(['app.hosted' => false, 'mail.default' => 'array']);

        $this->artisan('app:send-event-interest-mail', ['--kind' => 'tickets', '--apply' => true])
            ->expectsOutput('Skipping: no mail transport configured.')
            ->assertSuccessful();

        $this->assertSame(0, $this->queuedCount(), 'nothing may be queued into a log mailer');
        $this->assertNull(
            $interest->fresh()->tickets_notified_at,
            'the claim must NOT be stamped - a stamped row can never be sent again'
        );

        // And the row is still live once a real transport arrives, which is the whole point.
        config(['mail.default' => 'smtp']);
        $this->artisan('app:send-event-interest-mail', ['--kind' => 'tickets', '--apply' => true])->assertSuccessful();

        $this->assertSame(1, $this->queuedCount());
        $this->assertNotNull($interest->fresh()->tickets_notified_at);
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

    public function test_the_interest_mail_speaks_for_the_creator_schedule_not_the_venue(): void
    {
        // The jobs resolve their role with getRoleWithEmailSettings(), which prefers a VENUE and
        // falls back to `$venue ?: $firstRole`. That is right for a buyer, who got their receipt
        // from whichever schedule holds the SMTP, and wrong for this list: somebody who left an
        // address on the talent's page must hear from the talent. Otherwise the mail carries the
        // venue's sender and Reply-To, links to the venue's storefront, is metered against the
        // venue, and is gated by the venue's verification status.
        // The creator must be a TALENT. createRole() defaults to type 'venue', and
        // Event::getVenueAttribute() returns the FIRST venue among the event's roles - so a
        // venue-typed creator IS the venue, both branches resolve to the same role, and the test
        // passes whichever way the code goes. That is how the first version of this test pinned
        // nothing.
        $talent = $this->createRole($this->createOwner(), 'talent');
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);

        // No events.venue_id column: the venue is the event_role pivot row whose role is a venue.
        $venue = $this->createRole($this->createOwner(), 'venue');
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $this->captureFor($talent, $event);
        $this->assertSame($venue->id, $event->fresh()->getRoleWithEmailSettings()->id,
            'the fixture must actually resolve the venue as the sales-side role');

        (new \App\Jobs\NotifyEventCancelled($event->id))->handle();

        $job = Queue::pushed(SendQueuedEmail::class)->first();
        $this->assertNotNull($job);

        // The role id the mail is attributed to, read off the queued job.
        $roleId = (new \ReflectionProperty(SendQueuedEmail::class, 'roleId'));
        $roleId->setAccessible(true);

        $this->assertSame(
            $talent->id,
            $roleId->getValue($job),
            'the interest mail must be attributed to the creator schedule, not the venue'
        );
    }

    public function test_an_ownerless_venue_does_not_silently_swallow_the_whole_send(): void
    {
        // canSendAudienceMail() fails CLOSED for an ownerless schedule, and auto-created venues are
        // ownerless by design. With the venue's role driving the interest half, a talent event at
        // an imported venue dropped the entire change/cancellation send and still reported it as
        // delivered.
        //
        // app.is_testing OFF is load-bearing: canSendAudienceMail() short-circuits to true on it
        // before any of its real rules run, so without this the test passes for the wrong reason.
        config(['app.hosted' => true, 'app.is_testing' => false]);

        // Talent-created, for the same reason as the test above.
        $talent = $this->createRole($this->createOwner(), 'talent');
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id]);

        $venue = $this->createRole($this->createOwner(), 'venue');
        $venue->forceFill(['user_id' => null])->save();
        $event->roles()->attach($venue->id, ['is_accepted' => true]);

        $this->captureFor($talent, $event);
        $this->assertSame($venue->id, $event->fresh()->getRoleWithEmailSettings()->id);

        (new \App\Jobs\NotifyEventCancelled($event->id))->handle();

        $this->assertSame(1, $this->queuedCount(), 'an ownerless venue must not swallow the send');
    }

    public function test_a_refused_send_is_not_reported_as_delivered(): void
    {
        // The count used to model one of the four gates notifyInterested() applies, so the job
        // stamped attendees_notified_at and the flash reported "N people notified" for a send that
        // never happened. The most reachable case is the unverified ceiling: over it, the gate
        // refuses outright.
        config(['app.hosted' => true, 'app.is_testing' => false]);
        config(['usage.audience_mail_unverified_max_recipients' => 1]);

        $this->capture();
        $this->capture(['email' => 'second@fans.test']);

        $this->assertSame(0, EventChangeNotifier::interestedNotifiableCount($this->event->fresh()),
            'a refused send must count as nobody');
        $this->assertFalse(EventChangeNotifier::hasAnyoneToTell($this->event->fresh()),
            'and must not dispatch a job that sends nothing');

        (new \App\Jobs\NotifyEventCancelled($this->event->id))->handle();

        $this->assertSame(0, $this->queuedCount());
        $this->assertNull($this->event->fresh()->attendees_notified_at,
            'nothing was sent, so nothing may be stamped');
    }
}
