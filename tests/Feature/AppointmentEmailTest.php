<?php

namespace Tests\Feature;

use App\Mail\AppointmentBookedNotification;
use App\Mail\AppointmentCancelled;
use App\Mail\AppointmentConfirmed;
use App\Mail\AppointmentDeclined;
use App\Mail\AppointmentPending;
use App\Mail\AppointmentReminder;
use App\Mail\AppointmentRescheduled;
use App\Models\Event;
use App\Models\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

class AppointmentEmailTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    public function test_all_appointment_mailables_render(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['name' => 'Consult', 'price' => 25, 'currency_code' => 'USD', 'payment_method' => 'cash']);

        $event = new Event;
        $event->name = 'Consult - Jane';
        $event->starts_at = now()->addDay()->format('Y-m-d H:i:s');
        $event->duration = 0.5;
        $event->timezone = 'America/New_York';
        $event->ticket_currency_code = 'USD';
        $event->is_private = true;
        $event->creator_role_id = $role->id;
        $event->user_id = $role->user_id;
        $event->appointment_type_id = $type->id;
        $event->description = 'Notes from Jane: please call the front desk';
        $event->slug = 'x-'.strtolower(Str::random(8));
        $event->save();
        $event->roles()->attach($role->id, ['is_accepted' => true]);

        $sale = new Sale;
        $sale->event_id = $event->id;
        $sale->subdomain = $role->subdomain;
        $sale->name = 'Jane';
        $sale->email = 'jane@gmail.com';
        $sale->phone = '+15551234567';
        $sale->event_date = now()->addDay()->setTimezone('America/New_York')->format('Y-m-d');
        $sale->status = 'paid';
        $sale->payment_method = 'cash';
        $sale->payment_amount = 25;
        $sale->transaction_reference = 'ch_test123';
        $sale->secret = strtolower(Str::random(32));
        $sale->save();

        // Guest-facing mailables: body renders AND the subject is translated (the :name is
        // interpolated - a bare/unprefixed i18n key would leave the raw key and no interpolation).
        foreach ([AppointmentConfirmed::class, AppointmentReminder::class, AppointmentPending::class, AppointmentDeclined::class, AppointmentCancelled::class] as $class) {
            $mail = new $class($sale, $event, $role, $type);
            $rendered = $mail->render();
            $this->assertNotEmpty($rendered);
            $this->assertStringContainsString('Consult', $rendered);
            $this->assertStringContainsString('Consult', $mail->envelope()->subject, "{$class} subject did not resolve/interpolate (raw i18n key?)");
            // 'messages.' catches BOTH prefixes: the old guard tested 'appointment_', which does not match
            // 'appointments_' - the prefix of nearly every key these views use - so most of them were
            // never actually guarded.
            $this->assertStringNotContainsString('messages.appointment', $rendered, "{$class} body leaked a raw i18n key");
        }

        // Owner notification, every kind (cancelled shows the refund note, rescheduled the old time).
        foreach (['booked', 'pending', 'cancelled', 'rescheduled'] as $kind) {
            $mail = new AppointmentBookedNotification($sale, $event, $role, $type, $kind);
            $rendered = $mail->render();
            $this->assertNotEmpty($rendered);
            $this->assertStringContainsString('jane@gmail.com', $rendered);
            $this->assertStringContainsString('Consult', $mail->envelope()->subject, "owner {$kind} subject did not resolve (raw i18n key?)");
            $this->assertStringNotContainsString('appointment_owner_', $rendered, "owner {$kind} body leaked a raw i18n key");
        }
    }

    public function test_rescheduled_mail_shows_both_times_and_carries_an_itip_invite(): void
    {
        [$role, $type, $event, $sale] = $this->rescheduleFixture();
        $oldStartsAt = now()->addDay()->startOfHour()->format('Y-m-d H:i:s');

        $mail = new AppointmentRescheduled($sale, $event, $role, $type, $oldStartsAt);
        $rendered = $mail->render();

        $this->assertStringContainsString('Consult', $mail->envelope()->subject);
        $this->assertStringNotContainsString('messages.appointment', $rendered, 'body leaked a raw i18n key');
        // Both sides of the change, and the honest caveat about duplicate entries.
        $this->assertStringContainsString(__('messages.event_changed_previously'), $rendered);
        $this->assertStringContainsString(__('messages.event_changed_now'), $rendered);
        $this->assertStringContainsString(__('messages.update_your_calendar_note'), $rendered);

        // The invite must be iTIP, or the guest gets a second calendar entry instead of a moved one.
        // Attachment keeps its resolver private, so pull the bytes out the way attachTo() would.
        $attachment = $mail->attachments()[0];
        $this->assertSame('appointment.ics', $attachment->as);
        $ics = $this->attachmentBody($attachment);
        $this->assertStringContainsString('METHOD:REQUEST', $ics);
        $this->assertStringContainsString('ORGANIZER;CN=', $ics);
        // Unfolded first: buildInvite() folds to the 75-octet limit RFC 5545 requires, and the ATTENDEE
        // line is over it, so mailto: legitimately spans a fold boundary.
        $this->assertStringContainsString('mailto:jane@gmail.com', str_replace("\r\n ", '', $ics));
    }

    public function test_rescheduled_mail_carries_the_organizer_note_and_a_pending_variant(): void
    {
        [$role, $type, $event, $sale] = $this->rescheduleFixture();
        $oldStartsAt = now()->addDay()->startOfHour()->format('Y-m-d H:i:s');

        $withNote = (new AppointmentRescheduled($sale, $event, $role, $type, $oldStartsAt, false, 'Sorry, clinic emergency.'))->render();
        $this->assertStringContainsString('Sorry, clinic emergency.', $withNote);
        $this->assertStringContainsString(__('messages.organizer_note'), $withNote);

        $confirmed = (new AppointmentRescheduled($sale, $event, $role, $type, $oldStartsAt, false))->render();
        $pending = (new AppointmentRescheduled($sale, $event, $role, $type, $oldStartsAt, true))->render();
        $this->assertNotSame($confirmed, $pending);
        // The pending copy has to say nothing is booked yet.
        $this->assertStringContainsString(
            __('messages.appointment_rescheduled_pending_intro', ['schedule' => $role->name]),
            $pending
        );
    }

    /** The raw bytes behind a data attachment, without going through a real mail transport. */
    /**
     * A guest move on an approval-required type sends the booking back to pending. Reporting that as an
     * ordinary 'pending' gave the owner an email identical to a first-time booking: no old time, no
     * short-notice band, and no hint that something they had already approved had changed.
     */
    public function test_a_move_that_needs_reapproval_still_reads_as_a_move(): void
    {
        [$role, $type, $event, $sale] = $this->rescheduleFixture();
        $old = now()->addDays(3)->format('Y-m-d H:i:s');

        $mail = new AppointmentBookedNotification($sale, $event, $role, $type, 'rescheduled_pending', null, $old);
        $rendered = $mail->render();

        // Leads with the move, not with "new request".
        $this->assertStringContainsString(__('messages.appointment_owner_rescheduled_heading'), $rendered);
        $this->assertStringNotContainsString(__('messages.appointment_owner_pending_heading'), $rendered);
        $this->assertStringContainsString(
            __('messages.appointment_owner_rescheduled_subject', ['name' => $type->name]),
            $mail->envelope()->subject
        );

        // But it still routes to Requests, because it needs a decision, and offers Review not View.
        $this->assertStringContainsString('requests', $rendered);
        $this->assertStringNotContainsString('view=bookings', $rendered);
        $this->assertStringContainsString(__('messages.appointment_owner_review'), $rendered);
    }

    /**
     * $shortNotice is decided at DISPATCH, not at render: now() in a backed-up worker is not now() when
     * the move happened, which made the "moved less than a day before" claim false in one direction and
     * dropped a genuine warning in the other once the original time had passed.
     */
    public function test_short_notice_is_captured_at_dispatch_not_at_render(): void
    {
        [$role, $type, $event, $sale] = $this->rescheduleFixture();

        // Original start 2 hours out: a genuine short-notice move.
        $old = now()->addHours(2)->format('Y-m-d H:i:s');
        $mail = new AppointmentBookedNotification($sale, $event, $role, $type, 'rescheduled', null, $old);

        // The worker runs much later, by which time the original time has passed.
        $this->travel(5)->hours();

        $rendered = $mail->render();
        // Compare the fixed part: :time is interpolated with a real formatted time.
        $shortNoticePrefix = \Illuminate\Support\Str::before(
            __('messages.appointment_owner_moved_short_notice', ['time' => '@@']), '@@'
        );
        $this->assertStringContainsString($shortNoticePrefix, $rendered, 'the warning must survive a delayed send');

        // And the inverse: a move made well ahead must not claim short notice, however late it renders.
        $this->travelBack();
        $farOut = new AppointmentBookedNotification(
            $sale, $event, $role, $type, 'rescheduled', null, now()->addDays(3)->format('Y-m-d H:i:s')
        );
        $this->travel(40)->hours();
        $this->assertStringNotContainsString($shortNoticePrefix, $farOut->render());
        $this->travelBack();
    }

    /**
     * The most-repeated invariant in this feature - four separate docblocks say "must be a scalar, not a
     * model read, because SerializesModels re-fetches" - had no coverage at all. QUEUE_CONNECTION=sync and
     * every other test calls ->render() directly, so nothing ever serialized a mailable.
     *
     * This is the test that catches someone "simplifying" $oldStartsAt to $this->event->starts_at, which
     * would ship "moved from 3:00 PM to 3:00 PM" to every guest while the whole suite stayed green.
     */
    public function test_a_queued_reschedule_mail_still_names_the_old_time_after_serialization(): void
    {
        [$role, $type, $event, $sale] = $this->rescheduleFixture();

        $oldStartsAt = $event->starts_at;
        $mail = new AppointmentRescheduled($sale, $event, $role, $type, $oldStartsAt, false, null);

        // Move the booking, exactly as reschedule() does. A model-derived old time reads the NEW value
        // from here on; a scalar does not.
        Event::whereKey($event->id)->update([
            'starts_at' => \Carbon\Carbon::parse($oldStartsAt)->addDays(4)->format('Y-m-d H:i:s'),
        ]);

        // Serialize BEFORE any render, which is the real order: SendQueuedEmail::dispatch() hands the
        // un-rendered mailable to the queue. (Rendering first leaves a closure on the object and
        // serialization refuses it - so this order is not just realistic, it is the only one available.)
        $revived = unserialize(serialize($mail));
        $this->assertInstanceOf(AppointmentRescheduled::class, $revived);

        $rendered = $revived->render();

        // The NEW time is present (the event was re-fetched, as designed)...
        $this->assertStringContainsString(
            \App\Utils\AppointmentTimeUtils::render($event->fresh(), 'Europe/Paris')['date'],
            $rendered
        );
        // ...and the OLD time survived as a scalar, so "moved from X to Y" still has an X.
        $oldLabel = \Carbon\Carbon::parse($oldStartsAt, 'UTC')->setTimezone('Europe/Paris')->translatedFormat('l, F j, Y');
        $this->assertStringContainsString($oldLabel, $rendered, 'the old time must survive queue serialization');
        $this->assertNotSame(
            \App\Utils\AppointmentTimeUtils::render($event->fresh(), 'Europe/Paris')['date'],
            $oldLabel,
            'fixture premise: the two dates differ, so the assertion above can fail'
        );

        // And a fresh (un-serialized) mailable built from the same scalar agrees, so the assertion above
        // is about serialization and not about the fixture.
        $this->assertStringContainsString(
            $oldLabel,
            (new AppointmentRescheduled($sale->fresh(), $event->fresh(), $role, $type, $oldStartsAt, false, null))->render()
        );
    }

    private function attachmentBody(\Illuminate\Mail\Attachment $attachment): string
    {
        $resolver = (new \ReflectionClass($attachment))->getProperty('resolver');
        $resolver->setAccessible(true);

        $captured = '';
        $resolver->getValue($attachment)(
            $attachment,
            fn () => null,                                    // path strategy, unused for fromData
            function (\Closure $data) use (&$captured) {        // data strategy
                $captured = $data();
            }
        );

        return $captured;
    }

    /** @return array{0:\App\Models\Role, 1:\App\Models\AppointmentType, 2:Event, 3:Sale} */
    private function rescheduleFixture(): array
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['name' => 'Consult']);

        $event = new Event;
        $event->name = 'Consult - Jane';
        $event->starts_at = now()->addDays(3)->format('Y-m-d H:i:s');
        $event->duration = 0.5;
        $event->timezone = 'America/New_York';
        $event->is_private = true;
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
        $sale->event_date = now()->addDays(3)->setTimezone('America/New_York')->format('Y-m-d');
        $sale->status = 'paid';
        $sale->payment_method = 'cash';
        $sale->guest_timezone = 'Europe/Paris';
        $sale->secret = strtolower(Str::random(32));
        $sale->save();

        return [$role, $type, $event->fresh(), $sale->fresh()];
    }
}
