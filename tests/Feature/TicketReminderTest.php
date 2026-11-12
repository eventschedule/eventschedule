<?php

namespace Tests\Feature;

use App\Console\Commands\SendTicketReminders;
use App\Jobs\SendQueuedEmail;
use App\Mail\EventInterestNotification;
use App\Mail\TicketReminder;
use App\Models\Event;
use App\Models\EventInterest;
use App\Models\Role;
use App\Models\Sale;
use App\Models\TicketReminderSetting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * "See you tomorrow" (app:send-ticket-reminders, Tickets > Reminders).
 *
 * Most of what matters is who is NOT mailed: the mail goes to people who paid or registered, on
 * the schedule's name, and cannot be recalled.
 */
class TicketReminderTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();

        // The command stands down on the log and array mailers off hosted (nothing would be sent,
        // and the claim would be stamped). Queue::fake() keeps anything from leaving.
        config(['mail.default' => 'smtp']);

        Carbon::setTestNow(Carbon::parse('2026-11-10 12:00:00', 'UTC'));
        $this->role = $this->createRole($this->createOwner(), 'venue', ['timezone' => 'UTC']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function eventStartingIn(int $hours, array $attrs = []): Event
    {
        return $this->createEvent($this->role, $attrs + [
            'creator_role_id' => $this->role->id,
            'starts_at' => now()->addHours($hours)->format('Y-m-d H:i:s'),
        ]);
    }

    private function sale(Event $event, array $attrs = []): Sale
    {
        $sale = $this->createSale($event, $this->role, $attrs + [
            'email' => 'guest-'.Str::random(6).'@guests.org',
            'event_date' => Carbon::parse($event->starts_at)->format('Y-m-d'),
        ]);
        Sale::whereKey($sale->id)->update(['created_at' => $attrs['created_at'] ?? now()->subDays(5)]);

        return $sale->fresh();
    }

    private function sendReminders(): void
    {
        $this->artisan('app:send-ticket-reminders', ['--apply' => true])->assertSuccessful();
    }

    /** @return array<int, string> the addresses a reminder was queued for */
    private function remindedEmails(): array
    {
        return collect(Queue::pushed(SendQueuedEmail::class))
            ->filter(fn ($job) => $this->prop($job, 'mailable') instanceof TicketReminder)
            ->map(fn ($job) => $this->prop($job, 'recipient'))
            ->values()->all();
    }

    private function prop(object $object, string $name): mixed
    {
        $property = new \ReflectionProperty($object, $name);

        return $property->getValue($object);
    }

    public function test_a_ticket_holder_is_reminded_once_a_day_before(): void
    {
        $event = $this->eventStartingIn(20);
        $sale = $this->sale($event);

        $this->sendReminders();
        $this->assertSame([$sale->email], $this->remindedEmails());
        $this->assertNotNull($sale->fresh()->reminder_sent_at);

        // The other rail, or the next hour: nothing more.
        $this->sendReminders();
        $this->assertCount(1, $this->remindedEmails());
    }

    public function test_nobody_is_reminded_before_the_reminder_moment(): void
    {
        $this->sale($this->eventStartingIn(30));

        $this->sendReminders();

        $this->assertSame([], $this->remindedEmails());
    }

    public function test_a_ticket_bought_after_the_reminder_moment_is_not_reminded(): void
    {
        $event = $this->eventStartingIn(20);
        // The reminder was due four hours ago; this ticket was bought an hour ago, and its receipt
        // has just arrived.
        $late = $this->sale($event, ['created_at' => now()->subHour()]);
        $early = $this->sale($event);

        $this->sendReminders();

        $this->assertSame([$early->email], $this->remindedEmails());
        $this->assertNull($late->fresh()->reminder_sent_at, 'left alone, not stamped');
    }

    public function test_the_owners_switch_and_timing_decide(): void
    {
        $event = $this->eventStartingIn(20);
        $this->sale($event);

        TicketReminderSetting::store($this->role, ['enabled' => false]);
        $this->sendReminders();
        $this->assertSame([], $this->remindedEmails(), 'switched off');

        TicketReminderSetting::store($this->role, ['enabled' => true, 'hours_before' => 3]);
        $this->sendReminders();
        $this->assertSame([], $this->remindedEmails(), 'three hours before, and the event is twenty away');

        Carbon::setTestNow(now()->addHours(18));
        $this->sendReminders();
        $this->assertCount(1, $this->remindedEmails());
    }

    /**
     * The owning schedule's setting counts, not the venue's whose mail settings carry the message:
     * a talent owner who switched reminders off is not overruled by the venue the event is at.
     */
    public function test_the_owning_schedules_setting_counts(): void
    {
        $talent = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'UTC']);
        $event = $this->createEvent($this->role, [
            'creator_role_id' => $talent->id,
            'starts_at' => now()->addHours(20)->format('Y-m-d H:i:s'),
        ]);
        $event->roles()->attach($talent->id, ['is_accepted' => true]);
        $this->sale($event);

        TicketReminderSetting::store($talent, ['enabled' => false]);
        $this->sendReminders();

        $this->assertSame([], $this->remindedEmails());
    }

    public function test_who_is_never_reminded(): void
    {
        $event = $this->eventStartingIn(20);
        $good = $this->sale($event);
        $this->sale($event, ['payment_method' => 'import']);
        $this->sale($event, ['status' => 'unpaid']);
        $this->sale($event, ['status' => 'refunded']);
        $this->sale($event, ['is_deleted' => true]);
        $this->sale($event, ['email' => 'someone@example.com']);

        $cancelled = $this->eventStartingIn(20, ['is_cancelled' => true]);
        $this->sale($cancelled);

        $pass = $this->eventStartingIn(20);
        $passTicket = $this->createTicket($pass, ['is_pass' => true, 'price' => 10]);
        $this->createSale($pass, $this->role, ['email' => 'pass@guests.org', 'event_date' => Carbon::parse($pass->starts_at)->format('Y-m-d')], $passTicket);
        Sale::where('email', 'pass@guests.org')->update(['created_at' => now()->subDays(5)]);

        $this->sendReminders();

        $this->assertSame([$good->email], $this->remindedEmails());
    }

    public function test_a_deleted_schedule_sends_nothing(): void
    {
        $this->sale($this->eventStartingIn(20));
        $this->role->forceFill(['is_deleted' => true])->saveQuietly();

        $this->sendReminders();

        $this->assertSame([], $this->remindedEmails());
    }

    public function test_a_recurring_event_is_reminded_per_occurrence_and_not_for_a_skipped_date(): void
    {
        $event = $this->createRecurringEvent($this->role, [
            'creator_role_id' => $this->role->id,
            // Started a week ago, every day at 20:00: tonight's is eight hours away and due.
            'starts_at' => now()->subWeek()->setTime(20, 0)->format('Y-m-d H:i:s'),
        ]);
        $tonight = now()->format('Y-m-d');

        $forTonight = $this->sale($event, ['event_date' => $tonight]);
        $this->sale($event, ['event_date' => now()->addWeek()->format('Y-m-d')]);

        // The owner skipped tonight after the ticket was bought: nobody is told to come.
        $event->forceFill(['recurring_exclude_dates' => [$tonight]])->save();
        $this->sendReminders();
        $this->assertSame([], $this->remindedEmails());
        $this->assertNull($forTonight->fresh()->reminder_sent_at, 'left alone, not stamped');

        // Back on: tonight is reminded, next week is not due yet.
        $event->forceFill(['recurring_exclude_dates' => []])->save();
        $this->sendReminders();
        $this->assertSame([$forTonight->email], $this->remindedEmails());
    }

    /** A reminder sent for the old day said the wrong day: moving the event reminds its holders again. */
    public function test_moving_an_event_to_another_day_reminds_again(): void
    {
        $event = $this->eventStartingIn(20);
        $sale = $this->sale($event);
        $this->sendReminders();
        $this->assertNotNull($sale->fresh()->reminder_sent_at);

        // The owner moves it a week later (the Event saving hook re-dates its sales).
        $event->starts_at = now()->addWeek()->addHours(8)->format('Y-m-d H:i:s');
        $event->save();
        $moved = $sale->fresh();
        $this->assertSame(now()->addWeek()->addHours(8)->format('Y-m-d'), $moved->event_date);
        $this->assertNull($moved->reminder_sent_at);

        Carbon::setTestNow(now()->addWeek()->subHours(4));
        $this->sendReminders();
        $this->assertSame([$sale->email, $sale->email], $this->remindedEmails());
    }

    public function test_a_deleted_venue_does_not_silence_its_events(): void
    {
        // A talent's event at a venue: the venue is what getRoleWithEmailSettings() hands back.
        $talent = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'UTC']);
        $venue = $this->createRole($this->createOwner(), 'venue', ['timezone' => 'UTC']);
        $event = $this->createEvent($talent, ['creator_role_id' => $talent->id, 'starts_at' => now()->addHours(20)->format('Y-m-d H:i:s')]);
        $event->roles()->attach($venue->id, ['is_accepted' => true]);
        $this->assertSame($venue->id, $event->fresh()->getRoleWithEmailSettings()?->id, 'sanity: the venue would carry the mail');
        $venue->forceFill(['is_deleted' => true])->saveQuietly();
        $sale = $this->sale($event);

        $this->sendReminders();

        $this->assertSame([$sale->email], $this->remindedEmails());
    }

    public function test_a_reminder_that_would_land_in_the_night_goes_the_evening_before(): void
    {
        // Saturday 00:30 in Berlin, a day before: Friday 00:30, so Thursday 20:00 instead.
        $start = Carbon::parse('2026-11-14 00:30', 'Europe/Berlin')->utc();
        $moment = SendTicketReminders::reminderMoment($start, 24, 'Europe/Berlin');
        $this->assertSame('2026-11-12 20:00', $moment->copy()->setTimezone('Europe/Berlin')->format('Y-m-d H:i'));

        // 23:00: the same evening at 20:00.
        $start = Carbon::parse('2026-11-14 23:00', 'Europe/Berlin')->utc();
        $this->assertSame('2026-11-13 20:00', SendTicketReminders::reminderMoment($start, 24, 'Europe/Berlin')->setTimezone('Europe/Berlin')->format('Y-m-d H:i'));

        // Daytime, and the three-hour option, are left as they are.
        $start = Carbon::parse('2026-11-14 18:00', 'Europe/Berlin')->utc();
        $this->assertSame('2026-11-13 18:00', SendTicketReminders::reminderMoment($start, 24, 'Europe/Berlin')->setTimezone('Europe/Berlin')->format('Y-m-d H:i'));
        $start = Carbon::parse('2026-11-14 02:00', 'Europe/Berlin')->utc();
        $this->assertSame('2026-11-13 23:00', SendTicketReminders::reminderMoment($start, 3, 'Europe/Berlin')->setTimezone('Europe/Berlin')->format('Y-m-d H:i'));
    }

    public function test_nothing_is_claimed_without_a_mail_transport(): void
    {
        // Off hosted, where MAIL_MAILER=log is the shipped default.
        config(['app.hosted' => false, 'mail.default' => 'log']);
        $sale = $this->sale($this->eventStartingIn(20));

        $this->sendReminders();

        $this->assertSame([], $this->remindedEmails());
        $this->assertNull($sale->fresh()->reminder_sent_at);
    }

    public function test_the_reminder_renders_both_bodies_and_says_when(): void
    {
        $event = $this->eventStartingIn(20, ['name' => 'Jazz Night']);
        $sale = $this->sale($event, ['name' => 'Omar']);
        $rsvp = $this->sale($event, ['name' => 'Dana', 'payment_method' => 'rsvp']);

        // A real send through the array transport renders both bodies, as SendQueuedEmail would.
        config(['mail.default' => 'array']);
        Mail::to($sale->email)->send(new TicketReminder($sale, $event->fresh(), $this->role));
        Mail::to($rsvp->email)->send(new TicketReminder($rsvp, $event->fresh(), $this->role));

        $messages = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(2, $messages);

        $ticket = $messages[0]->getOriginalMessage();
        // No invisible characters in a left-to-right subject; a right-to-left one isolates the name.
        $this->assertSame('Jazz Night is tomorrow at 8:00 AM', $ticket->getSubject());
        app()->setLocale('he');
        $this->assertStringContainsString("\u{2068}Jazz Night\u{2069}", (new TicketReminder($sale, $event->fresh(), $this->role))->envelope()->subject);
        app()->setLocale('en');
        $this->assertStringContainsString('Hello Omar', (string) $ticket->getHtmlBody());
        $this->assertStringContainsString('View Your Tickets', (string) $ticket->getHtmlBody());
        $this->assertStringContainsString('/ticket/view/', (string) $ticket->getTextBody());
        $this->assertStringContainsString('because you have a ticket to Jazz Night', (string) $ticket->getTextBody());
        $this->assertStringContainsString('Show this code at the door', (string) $ticket->getHtmlBody(), 'the ticket itself, in the body');
        $this->assertContains('ticket-qr-code.png', array_map(fn ($part) => $part->getFilename(), $ticket->getAttachments()), 'the QR code, embedded');
        $this->assertStringContainsString('cid:', (string) $ticket->getHtmlBody());

        $registration = $messages[1]->getOriginalMessage();
        $this->assertStringContainsString('View Registration', (string) $registration->getHtmlBody());
        $this->assertStringContainsString('Can&#039;t make it?', (string) $registration->getHtmlBody(), 'a free place the guest can give up');
        $this->assertStringContainsString('because you registered for Jazz Night', (string) $registration->getTextBody());
    }

    /** Somebody who asked about the event and then got a ticket is reminded once, by the ticket reminder. */
    public function test_a_ticket_holder_is_not_sent_the_interest_reminder_too(): void
    {
        $event = $this->eventStartingIn(20);
        $date = Carbon::parse($event->starts_at)->format('Y-m-d');
        $sale = $this->sale($event);
        $interest = EventInterest::create([
            'event_id' => $event->id,
            'event_date' => $date,
            'email' => strtoupper($sale->email),
            'token' => Str::random(40),
            'confirmed_at' => now()->subDays(5),
            'tickets_notified_at' => now()->subDays(5),
        ]);

        $this->artisan('app:send-event-interest-mail', ['--apply' => true])->assertSuccessful();

        $this->assertNotNull($interest->fresh()->reminder_sent_at, 'stamped, so it leaves the window');
        $this->assertSame(0, collect(Queue::pushed(SendQueuedEmail::class))
            ->filter(fn ($job) => $this->prop($job, 'mailable') instanceof EventInterestNotification)->count());

        // With the schedule's reminders off, the interest reminder is the only one, so it goes.
        TicketReminderSetting::store($this->role, ['enabled' => false]);
        $interest->forceFill(['reminder_sent_at' => null])->save();
        $this->artisan('app:send-event-interest-mail', ['--apply' => true])->assertSuccessful();
        $this->assertNotNull($interest->fresh()->reminder_sent_at);
        $this->assertSame(1, collect(Queue::pushed(SendQueuedEmail::class))
            ->filter(fn ($job) => $this->prop($job, 'mailable') instanceof EventInterestNotification)->count());
    }

    /** A season pass is never reminded by the ticket reminder, so its holder keeps the interest list's. */
    public function test_a_pass_holder_still_gets_the_interest_reminder(): void
    {
        $event = $this->eventStartingIn(20);
        $date = Carbon::parse($event->starts_at)->format('Y-m-d');
        $passTicket = $this->createTicket($event, ['is_pass' => true, 'price' => 10]);
        $this->createSale($event, $this->role, ['email' => 'pass@guests.org', 'event_date' => $date], $passTicket);
        Sale::where('email', 'pass@guests.org')->update(['created_at' => now()->subDays(5)]);
        $interest = EventInterest::create([
            'event_id' => $event->id,
            'event_date' => $date,
            'email' => 'pass@guests.org',
            'token' => Str::random(40),
            'confirmed_at' => now()->subDays(5),
            'tickets_notified_at' => now()->subDays(5),
        ]);

        $this->artisan('app:send-event-interest-mail', ['--apply' => true])->assertSuccessful();
        $this->sendReminders();

        $this->assertSame(1, collect(Queue::pushed(SendQueuedEmail::class))
            ->filter(fn ($job) => $this->prop($job, 'mailable') instanceof EventInterestNotification)->count());
        $this->assertSame([], $this->remindedEmails());
    }

    public function test_the_install_wide_switch_stops_every_reminder(): void
    {
        $sale = $this->sale($this->eventStartingIn(20));
        config(['usage.ticket_reminders_enabled' => false]);

        $this->sendReminders();

        $this->assertSame([], $this->remindedEmails());
        $this->assertNull($sale->fresh()->reminder_sent_at);
    }

    public function test_the_schedule_form_saves_only_what_it_was_sent(): void
    {
        $owner = $this->role->users()->first();
        $this->actingAs($owner);

        $this->assertNull($this->role->ticketReminderSetting->enabled, 'not chosen with no row');
        $this->assertTrue($this->role->ticketRemindersOn(), 'a paying schedule is on with no row');
        $this->assertSame(24, $this->role->ticketReminderSetting->hours_before);

        $base = ['name' => $this->role->name, 'email' => $this->role->email, 'new_subdomain' => $this->role->subdomain, 'timezone' => 'UTC', 'language_code' => 'en'];

        $this->put(route('role.update', ['subdomain' => $this->role->subdomain]), $base + [
            'ticket_reminders_enabled' => '0',
            'ticket_reminders_hours' => '48',
        ])->assertSessionHasNoErrors();
        $setting = $this->role->fresh()->ticketReminderSetting;
        $this->assertFalse($setting->enabled);
        $this->assertSame(48, $setting->hours_before);

        // A save that did not carry the fields leaves them.
        $this->put(route('role.update', ['subdomain' => $this->role->subdomain]), $base);
        $this->assertFalse($this->role->fresh()->ticketReminderSetting->enabled);

        // A timing the form does not offer is refused.
        $this->put(route('role.update', ['subdomain' => $this->role->subdomain]), $base + ['ticket_reminders_hours' => '5'])
            ->assertSessionHasErrors('ticket_reminders_hours');
        $this->assertSame(48, $this->role->fresh()->ticketReminderSetting->hours_before);
    }

    /** Make $this->role a free schedule on the hosted install, with no email settings of its own. */
    private function makeHostedFree(): void
    {
        config(['app.hosted' => true]);
        $this->role->plan_type = 'free';
        $this->role->plan_expires = now()->subYear()->format('Y-m-d');
        $this->role->trial_ends_at = null;
        $this->role->save();
        $this->role = $this->role->fresh();
        $this->assertFalse($this->role->isPro(), 'the fixture is free');
    }

    public function test_optional_mail_is_off_by_default_only_where_our_server_would_send_it_for_free(): void
    {
        // Selfhost: the operator's own mail server.
        config(['app.hosted' => false]);
        $this->assertTrue($this->role->fresh()->optionalMailOnByDefault());

        // Hosted and paying (the fixture's plan): on.
        config(['app.hosted' => true]);
        $this->assertTrue($this->role->fresh()->optionalMailOnByDefault());

        // Hosted and free: through the platform's mail server, so off.
        $this->makeHostedFree();
        $this->assertFalse($this->role->optionalMailOnByDefault());
        $this->assertFalse($this->role->ticketRemindersOn());

        // A trial is not paying, though it unlocks Pro.
        $this->role->trial_ends_at = now()->addDays(10);
        $this->role->save();
        $this->assertTrue($this->role->fresh()->isPro());
        $this->assertFalse($this->role->fresh()->optionalMailOnByDefault());

        // Free, but sending through its own mail server: on.
        $this->role->trial_ends_at = null;
        $this->role->email_settings = [
            'host' => 'smtp.test.dev', 'port' => 587, 'encryption' => 'tls',
            'username' => 'mailer', 'password' => 'secret',
            'from_address' => 'events@test.dev', 'from_name' => 'Test',
        ];
        $this->role->save();
        $this->assertTrue($this->role->fresh()->optionalMailOnByDefault());
    }

    public function test_a_free_hosted_schedule_reminds_nobody_until_its_owner_turns_reminders_on(): void
    {
        $this->makeHostedFree();
        $sale = $this->sale($this->eventStartingIn(20));

        $this->sendReminders();
        $this->assertSame([], $this->remindedEmails());
        $this->assertNull($sale->fresh()->reminder_sent_at);

        // The owner's own choice wins over the default.
        TicketReminderSetting::store($this->role, ['enabled' => true]);
        $this->sendReminders();
        $this->assertSame([$sale->email], $this->remindedEmails());
    }

    public function test_a_save_that_leaves_the_switch_alone_keeps_following_the_default(): void
    {
        $this->makeHostedFree();
        $this->actingAs($this->role->users()->first());
        $base = ['name' => $this->role->name, 'email' => $this->role->email, 'new_subdomain' => $this->role->subdomain, 'timezone' => 'UTC', 'language_code' => 'en'];

        // The form showed it off and posted it off: not a choice, so nothing is stored.
        $this->put(route('role.update', ['subdomain' => $this->role->subdomain]), $base + [
            'ticket_reminders_shown' => '0',
            'ticket_reminders_enabled' => '0',
            'ticket_reminders_hours' => '24',
        ])->assertSessionHasNoErrors();
        $this->assertNull($this->role->fresh()->ticketReminderSetting->enabled);

        // So when the schedule starts paying, its reminders come on.
        $this->role->plan_type = 'pro';
        $this->role->plan_expires = now()->addYear()->format('Y-m-d');
        $this->role->save();
        $this->assertTrue($this->role->fresh()->ticketRemindersOn());

        // Switched off on purpose while paying: stored, and kept whatever the plan does next.
        $this->put(route('role.update', ['subdomain' => $this->role->subdomain]), $base + [
            'ticket_reminders_shown' => '1',
            'ticket_reminders_enabled' => '0',
        ])->assertSessionHasNoErrors();
        $this->assertFalse($this->role->fresh()->ticketReminderSetting->enabled);
        $this->assertFalse($this->role->fresh()->ticketRemindersOn());
    }

    public function test_the_preview_is_for_the_schedules_editors(): void
    {
        $this->eventStartingIn(40, ['name' => 'Preview Night']);

        $this->actingAs($this->role->users()->first())
            ->get(route('role.ticket_reminder_preview', ['subdomain' => $this->role->subdomain]))
            ->assertOk()
            ->assertSee('Preview Night')
            ->assertSee('/ticket/view/', false);

        $this->actingAs($this->createOwner())
            ->get(route('role.ticket_reminder_preview', ['subdomain' => $this->role->subdomain]))
            ->assertForbidden();

        $this->assertSame(0, Sale::count(), 'the preview stores nothing');
    }

    /** "Pay the organizer" leaves an order unpaid until the door: it holds a seat, and is reminded. */
    public function test_an_order_to_pay_at_the_door_is_reminded(): void
    {
        $event = $this->eventStartingIn(20);
        $cash = $this->sale($event, ['status' => 'unpaid', 'payment_method' => 'cash']);
        $this->sale($event, ['status' => 'expired', 'payment_method' => 'cash']);
        $this->sale($event, ['status' => 'unpaid', 'payment_method' => 'stripe']);

        $this->sendReminders();

        $this->assertSame([$cash->email], $this->remindedEmails());
    }

    /** A sale whose date the event no longer has (a series made a one-off on another day) is not told a wrong day. */
    public function test_a_sale_for_a_date_the_event_no_longer_has_is_not_reminded(): void
    {
        // The event is the day after tomorrow; a row still carries tomorrow, a date it no longer
        // has (a series made a one-off without its start changing). Tomorrow's reminder is due now.
        $event = $this->eventStartingIn(44);
        $stale = $this->sale($event, ['event_date' => now()->addHours(20)->format('Y-m-d')]);

        $this->sendReminders();

        $this->assertSame([], $this->remindedEmails(), 'never "tomorrow" for a night that is not on');
        $this->assertNull($stale->fresh()->reminder_sent_at);
    }

    /**
     * A daily series made a one-off on another day, in one save: its sales keep the nights they were
     * bought for. The saving hook used to move every one of them onto the new day, and the reminder
     * then told every past night's buyer that the event was tomorrow.
     */
    public function test_a_series_made_a_one_off_on_a_new_day_does_not_remind_past_buyers(): void
    {
        $event = $this->createRecurringEvent($this->role, [
            'creator_role_id' => $this->role->id,
            'starts_at' => now()->subWeeks(3)->setTime(20, 0)->format('Y-m-d H:i:s'),
        ]);
        $past = $this->sale($event, ['event_date' => now()->subWeeks(2)->format('Y-m-d')]);
        $lastWeek = $this->sale($event, ['event_date' => now()->subWeek()->format('Y-m-d')]);

        // One save: no longer recurring, and on tomorrow evening.
        $event->days_of_week = null;
        $event->recurring_frequency = null;
        $event->starts_at = now()->addDay()->setTime(8, 0)->format('Y-m-d H:i:s');
        $event->save();

        $this->assertSame(now()->subWeeks(2)->format('Y-m-d'), $past->fresh()->event_date, 'kept the night it was bought for');
        $this->assertSame(now()->subWeek()->format('Y-m-d'), $lastWeek->fresh()->event_date);

        Carbon::setTestNow(now()->addHours(4));
        $this->sendReminders();

        $this->assertSame([], $this->remindedEmails(), 'nobody who went on a past night is told "tomorrow"');
    }

    public function test_moving_an_event_to_another_time_the_same_day_reminds_again(): void
    {
        $event = $this->eventStartingIn(20);
        $sale = $this->sale($event);
        $this->sendReminders();

        $event->starts_at = Carbon::parse($event->starts_at)->addHours(2)->format('Y-m-d H:i:s');
        $event->save();

        $this->assertSame(Carbon::parse($event->starts_at)->format('Y-m-d'), $sale->fresh()->event_date, 'the same day');
        $this->assertNull($sale->fresh()->reminder_sent_at, 'the reminder said the old time');

        $this->sendReminders();
        $this->assertSame([$sale->email, $sale->email], $this->remindedEmails());
    }
}
