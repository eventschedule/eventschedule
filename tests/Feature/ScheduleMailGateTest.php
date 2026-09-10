<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\EventCancelled;
use App\Mail\EventChanged;
use App\Mail\FeedbackRequest;
use App\Mail\PassBookingConfirmation;
use App\Mail\TicketPurchase;
use App\Models\Event;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use App\Services\EmailService;
use App\Services\EventChangeNotifier;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * EmailService::canSendScheduleMail(), the one transport rule, at the sites that used to ask
 * hasEmailSettings() on its own.
 *
 * hasEmailSettings() is ALWAYS false on selfhost: the Email Settings tab that fills it in is
 * hosted-only. A gate written as that check alone is one no selfhosted install can pass, however
 * well its MAIL_MAILER is set up, which is how selfhosted buyers never heard that an event moved or
 * was cancelled. On hosted the rule has two strengths: transactional mail (a ticket, a booking)
 * needs only a schedule and falls back to the platform mailer; the rest needs the schedule's SMTP.
 *
 * Both config keys are pinned by hand, because the test env is neither kind of install exactly:
 * phpunit.xml forces MAIL_MAILER=array, and app.hosted comes from the .env.
 */
class ScheduleMailGateTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private const SMTP = [
        'host' => 'smtp.test', 'username' => 'u', 'password' => 'p',
        'port' => 587, 'from_address' => 'sched@gmail.com', 'from_name' => 'Sched',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        config(['app.hosted' => true]);
    }

    private function selfhost(string $mailer): void
    {
        config(['app.hosted' => false, 'mail.default' => $mailer]);
    }

    /** @return list<array{mailable: \Illuminate\Mail\Mailable, to: string}> */
    private function queuedMail(): array
    {
        $mailable = new \ReflectionProperty(SendQueuedEmail::class, 'mailable');
        $recipient = new \ReflectionProperty(SendQueuedEmail::class, 'recipient');

        return Queue::pushed(SendQueuedEmail::class)
            ->map(fn ($job) => ['mailable' => $mailable->getValue($job), 'to' => $recipient->getValue($job)])
            ->values()
            ->all();
    }

    public function test_the_rule(): void
    {
        $role = $this->createRole($this->createOwner());
        $this->assertFalse($role->hasEmailSettings());

        // Hosted, a schedule on the platform mailer.
        $this->assertFalse(EmailService::canSendScheduleMail($role));
        $this->assertTrue(EmailService::canSendScheduleMail($role, true));
        $this->assertFalse(EmailService::canSendScheduleMail(null, true));

        // Hosted, a schedule with SMTP of its own.
        $role->email_settings = self::SMTP;
        $role->save();
        $this->assertTrue(EmailService::canSendScheduleMail($role->fresh()));

        // Selfhost: the install's mailer is the whole question, whatever the schedule holds.
        $this->selfhost('smtp');
        $this->assertTrue(EmailService::canSendScheduleMail($this->createRole($this->createOwner())));

        foreach (['log', 'array'] as $mailer) {
            $this->selfhost($mailer);
            $this->assertFalse(EmailService::canSendScheduleMail($role), $mailer);
            $this->assertFalse(EmailService::canSendScheduleMail($role, true), $mailer);
        }
    }

    // ---------------------------------------------------------------- change and cancel notices

    /** A schedule with no email settings of its own, and one paid buyer. */
    private function eventWithOneBuyer(): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id]);
        // Not example.com: Sale::scopeExcludeTestEmails() drops the reserved test domains.
        $this->createSale($event, $role, ['email' => 'buyer@gmail.com', 'status' => 'paid']);

        $this->assertFalse($role->hasEmailSettings(), 'the fixture must have no SMTP of its own');

        return [$event->fresh(), $role, $owner];
    }

    public function test_a_selfhosted_install_tells_buyers_about_a_change_and_a_cancellation(): void
    {
        [$event, $role] = $this->eventWithOneBuyer();
        $this->selfhost('smtp');

        $this->assertTrue(EventChangeNotifier::hasNotifiableBuyers($event), 'the editor must be offered the notice');
        $this->assertSame(1, EventChangeNotifier::notifyChange($event, $role, ['starts_at' => 'x']));
        $this->assertSame(1, EventChangeNotifier::notifyCancellation($event, $role));

        $mail = $this->queuedMail();
        $this->assertCount(2, $mail);
        $this->assertInstanceOf(EventChanged::class, $mail[0]['mailable']);
        $this->assertInstanceOf(EventCancelled::class, $mail[1]['mailable']);
        $this->assertSame(['buyer@gmail.com', 'buyer@gmail.com'], array_column($mail, 'to'));
    }

    public function test_a_selfhosted_install_without_a_real_mailer_tells_no_buyer(): void
    {
        [$event, $role] = $this->eventWithOneBuyer();
        $this->selfhost('log');

        $this->assertFalse(EventChangeNotifier::hasNotifiableBuyers($event));
        $this->assertSame(0, EventChangeNotifier::notifyChange($event, $role, ['starts_at' => 'x']));
        $this->assertSame(0, EventChangeNotifier::notifyCancellation($event, $role));
        Queue::assertNotPushed(SendQueuedEmail::class);
    }

    public function test_hosted_buyers_still_need_the_schedules_own_smtp(): void
    {
        [$event, $role] = $this->eventWithOneBuyer();

        $this->assertFalse(EventChangeNotifier::hasNotifiableBuyers($event));
        $this->assertSame(0, EventChangeNotifier::notifyChange($event, $role, ['starts_at' => 'x']));
        Queue::assertNotPushed(SendQueuedEmail::class);

        $role->email_settings = self::SMTP;
        $role->save();

        $this->assertTrue(EventChangeNotifier::hasNotifiableBuyers($event->fresh()));
        $this->assertSame(1, EventChangeNotifier::notifyChange($event->fresh(), $role->fresh(), ['starts_at' => 'x']));
    }

    public function test_the_event_editor_offers_the_buyer_notice_on_the_same_rule(): void
    {
        [$event, $role, $owner] = $this->eventWithOneBuyer();
        $url = route('event.edit', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]);
        $setUpOwnEmail = __('messages.notify_requires_email_settings');

        // Hosted, unchanged: no SMTP of its own, so buyers are not offered the notice, and the hint
        // links to the Email Settings tab that would change that.
        $response = $this->actingAs($owner)->get($url)->assertOk();
        $this->assertFalse($response->viewData('scheduleHasEmailSettings'));
        $response->assertSee($setUpOwnEmail);

        // Selfhost with a real mailer: buyers are offered it, and there is no such tab to point at.
        $this->selfhost('smtp');
        $response = $this->actingAs($owner)->get($url)->assertOk();
        $this->assertTrue($response->viewData('scheduleHasEmailSettings'));
        $response->assertDontSee($setUpOwnEmail);
    }

    // ---------------------------------------------------------------- pass booking confirmation

    public function test_a_pass_booking_confirmation_falls_back_to_the_platform_mailer_on_hosted(): void
    {
        $role = $this->createRole($this->createOwner());
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'tickets_enabled' => true]);
        $pass = $this->createTicket($event, [
            'type' => 'Season Pass', 'quantity' => 100, 'price' => 50,
            'is_pass' => true, 'pass_usage_type' => 'unlimited', 'pass_scope' => 'this_event',
            'pass_allow_booking' => true,
        ]);
        $holder = $this->createSale($event, $role, ['email' => 'holder@gmail.com'], $pass);
        $date = Carbon::parse($event->starts_at)->format('Y-m-d');
        $this->assertFalse($role->hasEmailSettings());

        $this->assertTrue((new EmailService)->sendPassBookingConfirmation($holder->fresh(), $event, $date));

        $mail = $this->queuedMail();
        $this->assertCount(1, $mail);
        $this->assertInstanceOf(PassBookingConfirmation::class, $mail[0]['mailable']);
        $this->assertSame('holder@gmail.com', $mail[0]['to']);

        // Still refused where there is no transport at all.
        $this->selfhost('log');
        $this->assertSame(
            EmailService::ERROR_NOT_CONFIGURED,
            (new EmailService)->sendPassBookingConfirmation($holder->fresh(), $event, $date)
        );
    }

    // ---------------------------------------------------------------- attendee import

    private function importFixture(): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner);
        $event = $this->createEvent($role, ['creator_role_id' => $role->id, 'tickets_enabled' => true]);
        $ticket = $this->createTicket($event, ['price' => 0, 'quantity' => 100]);
        $this->assertFalse($role->hasEmailSettings());

        return [$owner, $role, $event, $ticket];
    }

    /** What the import form is told: false keeps Save disabled while Send Email is on. */
    private function importFormCanEmail(User $owner, Role $role, Event $event): bool
    {
        return $this->actingAs($owner)
            ->get(route('sales.import', [
                'role_id' => UrlUtils::encodeId($role->id),
                'event_id' => UrlUtils::encodeId($event->id),
            ]))
            ->assertOk()
            ->viewData('hasEmailSettings');
    }

    private function import(User $owner, Event $event, Ticket $ticket, string $email): void
    {
        $this->actingAs($owner)->post(route('sales.import_store'), [
            'event_id' => UrlUtils::encodeId($event->id),
            'event_date' => $event->saleEventDateFromStartsAt(),
            'ticket_id' => UrlUtils::encodeId($ticket->id),
            'default_status' => 'paid',
            'send_emails' => true,
            'entries' => [['name' => 'Imported Guest', 'email' => $email, 'quantity' => 1, 'status' => 'paid']],
        ])->assertRedirect(route('sales'));

        $this->assertDatabaseHas('sales', ['event_id' => $event->id, 'email' => $email]);
    }

    /** @return list<string> */
    private function ticketRecipients(): array
    {
        return collect($this->queuedMail())
            ->filter(fn ($mail) => $mail['mailable'] instanceof TicketPurchase)
            ->pluck('to')
            ->values()
            ->all();
    }

    public function test_an_import_on_hosted_emails_tickets_without_the_schedules_own_smtp(): void
    {
        [$owner, $role, $event, $ticket] = $this->importFixture();

        $this->assertTrue($this->importFormCanEmail($owner, $role, $event));
        $this->import($owner, $event, $ticket, 'imported@gmail.com');

        $this->assertSame(['imported@gmail.com'], $this->ticketRecipients());
    }

    public function test_an_import_on_selfhost_emails_through_a_real_mailer_only(): void
    {
        [$owner, $role, $event, $ticket] = $this->importFixture();

        $this->selfhost('log');
        $this->assertFalse($this->importFormCanEmail($owner, $role, $event));
        $this->import($owner, $event, $ticket, 'first@gmail.com');
        $this->assertSame([], $this->ticketRecipients());

        $this->selfhost('smtp');
        $this->assertTrue($this->importFormCanEmail($owner, $role, $event));
        $this->import($owner, $event, $ticket, 'second@gmail.com');
        $this->assertSame(['second@gmail.com'], $this->ticketRecipients());
    }

    // ---------------------------------------------------------------- test feedback email

    private function feedbackSchedule(): array
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'venue', ['feedback_enabled' => true]);
        $this->createEvent($role, ['creator_role_id' => $role->id]);

        return [$owner, route('role.test_feedback_email', ['subdomain' => $role->subdomain])];
    }

    public function test_the_feedback_test_email_is_refused_on_hosted_without_the_schedules_own_smtp(): void
    {
        Mail::fake();
        [$owner, $url] = $this->feedbackSchedule();

        $this->actingAs($owner)->postJson($url)
            ->assertStatus(422)
            ->assertJson(['error' => __('messages.email_not_configured')]);

        Mail::assertNothingSent();
    }

    public function test_the_feedback_test_email_follows_the_selfhosted_mailer(): void
    {
        Mail::fake();
        [$owner, $url] = $this->feedbackSchedule();

        $this->selfhost('log');
        $this->actingAs($owner)->postJson($url)->assertStatus(422);
        Mail::assertNothingSent();

        $this->selfhost('smtp');
        $this->actingAs($owner)->postJson($url)->assertOk()->assertJson(['success' => true]);
        Mail::assertSent(FeedbackRequest::class);
    }
}
