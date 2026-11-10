<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmed;
use App\Mail\EventCancelled;
use App\Mail\EventChanged;
use App\Mail\TicketPurchase;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Services\EventChangeNotifier;
use App\Utils\TicketInvite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\Attachment;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * The calendar invitation a guest is mailed (IcsUtils, TicketInvite).
 *
 * What makes a mail app show an invitation, with its own add button, rather than a file to
 * download is the `method=` parameter on the MIME part, so that is asserted on the sent message
 * itself. Past that: a ticket's entry is one per person per occurrence, the change mail updates it
 * (same UID, higher SEQUENCE), the cancellation mail removes it, and the UID of a one-off event
 * survives the event being moved, which rewrites sales.event_date.
 */
class CalendarInviteMailTest extends TestCase
{
    use CreatesScheduleData, RefreshDatabase;

    private function schedule(): Role
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbour Hall']);
        // Hosted schedule mail to buyers goes out only on the schedule's own SMTP.
        $role->email_settings = [
            'host' => 'smtp.test.dev', 'port' => 587, 'encryption' => 'tls',
            'username' => 'mailer', 'password' => 'secret',
            'from_address' => 'events@harbour.dev', 'from_name' => 'Harbour',
        ];
        $role->save();

        return $role->fresh();
    }

    private function data(Attachment $attachment): string
    {
        return $attachment->attachWith(fn () => '', fn ($data) => $data());
    }

    private function line(string $ics, string $name): ?string
    {
        foreach (explode("\r\n", str_replace("\r\n ", '', $ics)) as $line) {
            if (str_starts_with($line, $name.':') || str_starts_with($line, $name.';')) {
                return substr($line, strpos($line, ':') + 1);
            }
        }

        return null;
    }

    /** @return array<int, Attachment> */
    private function invites(Mailable $mail): array
    {
        return array_values(array_filter($mail->attachments(), fn (Attachment $a) => str_starts_with((string) $a->mime, 'text/calendar')));
    }

    private function queuedMail(string $class): Mailable
    {
        $job = Queue::pushed(SendQueuedEmail::class)->first(fn ($job) => (fn () => $this->mailable)->call($job) instanceof $class);
        $this->assertNotNull($job, $class.' was not queued.');

        return (fn () => $this->mailable)->call($job);
    }

    public function test_the_sent_message_carries_the_invitation_method_on_its_mime_part(): void
    {
        config(['mail.default' => 'array']);
        $role = $this->schedule();
        $event = $this->createEvent($role);
        $sale = $this->createSale($event, $role, ['email' => 'buyer@gmail.com'], $this->createTicket($event));

        Mail::to('buyer@gmail.com')->send(new TicketPurchase($sale, $event, $role));

        $sent = app('mail.manager')->mailer('array')->getSymfonyTransport()->messages()->last();
        $this->assertNotNull($sent);
        $this->assertStringContainsString('Content-Type: text/calendar; method=REQUEST; charset=UTF-8', $sent->toString());
    }

    public function test_a_ticket_mail_invites_the_buyer_from_the_schedule(): void
    {
        $role = $this->schedule();
        $event = $this->createEvent($role, ['name' => 'Night Market']);
        $sale = $this->createSale($event, $role, ['email' => 'buyer@gmail.com', 'name' => 'Dana, Lee'], $this->createTicket($event));

        $invites = $this->invites(new TicketPurchase($sale, $event, $role));
        $this->assertCount(1, $invites);
        $this->assertSame('text/calendar; method=REQUEST; charset=UTF-8', $invites[0]->mime);

        $ics = $this->data($invites[0]);
        $this->assertSame('REQUEST', $this->line($ics, 'METHOD'));
        $this->assertSame('Night Market', $this->line($ics, 'SUMMARY'));
        $this->assertSame('CONFIRMED', $this->line($ics, 'STATUS'));
        $this->assertSame(TicketInvite::uid($event, 'buyer@gmail.com', null), $this->line($ics, 'UID'));
        $this->assertStringContainsString($sale->secret, $this->line($ics, 'DESCRIPTION'));
        // The organizer's address is the From address; a name with a comma stays one value.
        $this->assertStringContainsString('ORGANIZER;CN=Harbour:mailto:events@harbour.dev', str_replace("\r\n ", '', $ics));
        $this->assertStringContainsString('RSVP=FALSE;CN="Dana, Lee":mailto:buyer@gmail.com', str_replace("\r\n ", '', $ics));
        $this->assertStringNotContainsString('buyer@gmail.com', $this->line($ics, 'UID'));
    }

    public function test_the_organizer_is_the_schedule_and_never_the_platform(): void
    {
        $role = $this->createRole($this->createOwner(), 'venue', ['name' => 'Harbour Hall']);
        $event = $this->createEvent($role);
        $sale = $this->createSale($event, $role, ['email' => 'buyer@gmail.com'], $this->createTicket($event));

        $ics = str_replace("\r\n ", '', (string) TicketInvite::forSale($sale, $event, $role));

        // No sender of its own: the platform's address (it must match the From header), but the
        // schedule's name, since a calendar shows the organizer and guest mail never names us.
        $this->assertStringContainsString('ORGANIZER;CN=Harbour Hall:mailto:'.config('mail.from.address'), $ics);
    }

    public function test_nothing_is_put_on_a_calendar_for_a_pass_an_unpaid_sale_or_a_cancelled_event(): void
    {
        $role = $this->schedule();
        $event = $this->createEvent($role);

        $pass = $this->createSale($event, $role, ['email' => 'pass@gmail.com'], $this->createTicket($event, ['is_pass' => true]));
        $this->assertNull(TicketInvite::forSale($pass, $event, $role));

        $unpaid = $this->createSale($event, $role, ['email' => 'later@gmail.com', 'status' => 'unpaid'], $this->createTicket($event));
        $this->assertNull(TicketInvite::forSale($unpaid, $event, $role));

        $paid = $this->createSale($event, $role, ['email' => 'buyer@gmail.com'], $this->createTicket($event));
        $event->forceFill(['is_cancelled' => true])->saveQuietly();
        $this->assertNull(TicketInvite::forSale($paid, $event->fresh(), $role));
        $this->assertNotNull(TicketInvite::forSale($paid, $event->fresh(), $role, 'CANCEL'));
    }

    public function test_moving_a_one_off_event_keeps_the_uid_and_the_change_mail_updates_the_entry(): void
    {
        Queue::fake();
        $role = $this->schedule();
        $event = $this->createEvent($role);
        $sale = $this->createSale($event, $role, ['email' => 'buyer@gmail.com'], $this->createTicket($event));

        $before = TicketInvite::forSale($sale, $event, $role);

        // A move rewrites sales.event_date through Event's saving cascade.
        $event->starts_at = now()->addDays(20)->setTime(18, 0)->format('Y-m-d H:i:s');
        $event->ical_sequence = (int) $event->ical_sequence + 1;
        $event->save();
        $event = $event->fresh();

        EventChangeNotifier::notifyChange($event, $role, ['date' => ['old_starts_at' => null, 'old_duration' => 2, 'old_timezone' => null]]);
        $invites = $this->invites($this->queuedMail(EventChanged::class));

        $this->assertCount(1, $invites);
        $after = $this->data($invites[0]);
        $this->assertSame($this->line($before, 'UID'), $this->line($after, 'UID'));
        $this->assertGreaterThan((int) $this->line($before, 'SEQUENCE'), (int) $this->line($after, 'SEQUENCE'));
        $this->assertSame(now()->addDays(20)->setTime(18, 0)->utc()->format('Ymd\THis\Z'), $this->line($after, 'DTSTART'));
        $this->assertSame('text/calendar; method=REQUEST; charset=UTF-8', $invites[0]->mime);
    }

    public function test_the_cancellation_mail_takes_every_date_the_buyer_holds_off_their_calendar(): void
    {
        Queue::fake();
        $role = $this->schedule();
        $event = $this->createRecurringEvent($role, ['starts_at' => now()->subWeek()->setTime(16, 0)->format('Y-m-d H:i:s')]);
        $ticket = $this->createTicket($event);
        $first = now()->addDays(3)->toDateString();
        $second = now()->addDays(10)->toDateString();
        $a = $this->createSale($event, $role, ['email' => 'buyer@gmail.com', 'event_date' => $first], $ticket);
        $this->createSale($event, $role, ['email' => 'buyer@gmail.com', 'event_date' => $second], $ticket);

        $purchased = TicketInvite::forSale($a, $event, $role);

        $event->forceFill(['is_cancelled' => true, 'ical_sequence' => (int) $event->ical_sequence + 1])->saveQuietly();
        EventChangeNotifier::notifyCancellation($event->fresh(), $role);

        $invites = $this->invites($this->queuedMail(EventCancelled::class));
        $this->assertCount(2, $invites, 'One mail per address, so it carries one cancellation per date held.');

        $uids = array_map(fn ($i) => $this->line($this->data($i), 'UID'), $invites);
        $this->assertSame([TicketInvite::uid($event, 'buyer@gmail.com', $first), TicketInvite::uid($event, 'buyer@gmail.com', $second)], $uids);
        $this->assertSame($this->line($purchased, 'UID'), $uids[0]);

        $ics = $this->data($invites[0]);
        $this->assertSame('CANCEL', $this->line($ics, 'METHOD'));
        $this->assertSame('CANCELLED', $this->line($ics, 'STATUS'));
        $this->assertSame('text/calendar; method=CANCEL; charset=UTF-8', $invites[0]->mime);
    }

    public function test_the_owners_preview_of_a_change_attaches_nothing(): void
    {
        $role = $this->schedule();
        $event = $this->createEvent($role);
        $this->createSale($event, $role, ['email' => 'buyer@gmail.com'], $this->createTicket($event));

        $this->assertSame([], (new EventChanged($event, $role, [], 'https://example.test'))->attachments());
    }

    private function booking(Role $role, array $saleAttrs = []): array
    {
        $type = $this->createAppointmentType($role);

        $event = new Event;
        $event->name = 'Consult - Jane';
        $event->starts_at = now()->addDays(2)->setTime(15, 0)->format('Y-m-d H:i:s');
        $event->duration = 0.5;
        $event->creator_role_id = $role->id;
        $event->user_id = $role->user_id;
        $event->appointment_type_id = $type->id;
        $event->slug = 'x-'.strtolower(Str::random(8));
        $event->save();
        $event->roles()->attach($role->id, ['is_accepted' => true]);

        $sale = new Sale;
        $sale->event_id = $event->id;
        $sale->subdomain = $role->subdomain;
        $sale->name = 'Jane';
        $sale->email = 'jane@gmail.com';
        $sale->event_date = now()->addDays(2)->toDateString();
        $sale->status = 'paid';
        $sale->payment_method = 'cash';
        $sale->payment_amount = 0;
        $sale->secret = Str::random(32);
        $sale->forceFill($saleAttrs);
        $sale->save();

        return [$event->fresh(), $sale->fresh(), $type];
    }

    public function test_an_appointment_confirmation_is_an_invitation(): void
    {
        $role = $this->schedule();
        [$event, $sale, $type] = $this->booking($role, ['confirmed_at' => now()]);

        $invites = $this->invites(new AppointmentConfirmed($sale, $event, $role, $type));

        $this->assertCount(1, $invites);
        $this->assertSame('text/calendar; method=REQUEST; charset=UTF-8', $invites[0]->mime);
        $this->assertSame('REQUEST', $this->line($this->data($invites[0]), 'METHOD'));
    }

    public function test_an_appointment_cancellation_removes_the_entry_only_when_one_was_sent(): void
    {
        $role = $this->schedule();

        [$event, $sale, $type] = $this->booking($role, ['confirmed_at' => now()]);
        $invites = $this->invites(new AppointmentCancelled($sale, $event, $role, $type));
        $this->assertCount(1, $invites);
        $this->assertSame('text/calendar; method=CANCEL; charset=UTF-8', $invites[0]->mime);
        $ics = $this->data($invites[0]);
        $this->assertSame('CANCEL', $this->line($ics, 'METHOD'));
        $this->assertSame('CANCELLED', $this->line($ics, 'STATUS'));

        // A request that was never confirmed or moved: the guest has no entry to remove.
        [$pending, $pendingSale, $type] = $this->booking($role);
        $this->assertSame([], $this->invites(new AppointmentCancelled($pendingSale, $pending, $role, $type)));
    }
}
