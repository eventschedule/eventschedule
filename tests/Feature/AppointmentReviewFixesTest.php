<?php

namespace Tests\Feature;

use App\Jobs\SendQueuedEmail;
use App\Mail\AppointmentBookedNotification;
use App\Mail\AppointmentConfirmed;
use App\Models\AppointmentType;
use App\Models\Event;
use App\Services\AppointmentService;
use App\Services\EmailService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Feature\Concerns\CreatesScheduleData;
use Tests\TestCase;

/**
 * Regression tests for the final-review fixes: acceptAll confirmation, payment-deferred accept,
 * refund-note serialization, free-booking owner-mail suppression, resendEmail branch, requests-tab
 * cleanup on cancel, handleAction guest email, and custom-domain-safe picker URLs.
 */
class AppointmentReviewFixesTest extends TestCase
{
    use CreatesScheduleData;
    use RefreshDatabase;

    private function allDays(): array
    {
        return array_fill_keys(['0', '1', '2', '3', '4', '5', '6'], [['start' => '09:00', 'end' => '17:00']]);
    }

    private function book($role, AppointmentType $type): array
    {
        $from = Carbon::now('America/New_York')->addDay()->format('Y-m-d');
        $slots = app(AppointmentService::class)->availableSlots($type, $from, 1);
        $slot = $slots['days'][array_key_first($slots['days'])][0]['utc'];
        $sale = app(AppointmentService::class)->book($type, $role, ['name' => 'Jane', 'email' => 'jane@gmail.com', 'slot' => $slot]);

        return [$sale->event, $sale];
    }

    /** Mailables queued via SendQueuedEmail whose class matches (empty when nothing queued). */
    private function queuedMailables(string $class): array
    {
        $found = [];
        foreach (Queue::pushed(SendQueuedEmail::class) as $job) {
            $ref = new \ReflectionProperty($job, 'mailable');
            $ref->setAccessible(true);
            $mailable = $ref->getValue($job);
            if ($mailable instanceof $class) {
                $found[] = $mailable;
            }
        }

        return $found;
    }

    public function test_accept_all_confirms_appointment_bookings(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays(), 'requires_approval' => true]);
        [$event, $sale] = $this->book($role, $type);

        $this->assertNull($sale->confirmed_at);

        $this->actingAs($owner)->post(route('event.accept_all', ['subdomain' => $role->subdomain]));

        $this->assertNotNull($sale->fresh()->confirmed_at, 'Accept All must confirm appointment bookings');
        $this->assertTrue((bool) $event->roles()->where('roles.id', $role->id)->first()->pivot->is_accepted);
    }

    public function test_accepting_unpaid_stripe_request_defers_confirmation(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, [
            'weekly_windows' => $this->allDays(), 'requires_approval' => true,
            'price' => 50, 'currency_code' => 'USD', 'payment_method' => 'stripe',
        ]);
        [$event, $sale] = $this->book($role, $type);
        $this->assertSame('unpaid', $sale->status);

        $this->actingAs($owner)->post(route('event.accept', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]));

        // Pivot accepted, but no "Confirmed" email while the guest has not paid.
        $this->assertTrue((bool) $event->roles()->where('roles.id', $role->id)->first()->pivot->is_accepted);
        $this->assertNull($sale->fresh()->confirmed_at, 'Unpaid stripe bookings must not confirm on accept');

        // Payment arriving (webhook path) now confirms.
        $sale->refresh();
        $sale->status = 'paid';
        $sale->save();
        (new EmailService)->sendSaleConfirmationEmails($sale->fresh());
        $this->assertNotNull($sale->fresh()->confirmed_at);
    }

    public function test_refund_note_renders_with_dispatch_time_paid_state(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays(), 'price' => 50, 'currency_code' => 'USD', 'payment_method' => 'cash']);
        [$event, $sale] = $this->book($role, $type);

        // Simulate the real sequence: paid booking gets cancelled BEFORE the mail renders.
        $sale->forceFill(['payment_amount' => 50, 'transaction_reference' => 'ch_test'])->saveQuietly();
        $sale->status = 'paid';
        $sale->saveQuietly();
        $sale->status = 'cancelled';
        $sale->saveQuietly();

        $rendered = (new AppointmentBookedNotification($sale->fresh(), $event->fresh(), $role, $type, 'cancelled', true))->render();
        $this->assertStringContainsString('ch_test', $rendered, 'refund note (with transaction reference) must render');
        $this->assertStringContainsString(__('messages.paid'), $rendered);

        // Without the scalar, the live cancelled status would hide it - proving the fix matters.
        $renderedWithout = (new AppointmentBookedNotification($sale->fresh(), $event->fresh(), $role, $type, 'cancelled'))->render();
        $this->assertStringNotContainsString('ch_test', $renderedWithout);
    }

    public function test_declining_free_booking_sends_no_owner_cancellation(): void
    {
        config(['app.hosted' => false, 'mail.default' => 'smtp']); // pass the transport gates so sends queue
        Queue::fake();
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays(), 'requires_approval' => true]);
        [$event, $sale] = $this->book($role, $type);

        $this->actingAs($owner)->post(route('event.decline', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($event->id)]));

        $this->assertSame('cancelled', $sale->fresh()->status);
        $ownerMails = array_filter(
            $this->queuedMailables(AppointmentBookedNotification::class),
            fn ($m) => true
        );
        $this->assertCount(0, $ownerMails, 'declining a FREE booking must not email the owner a cancellation notice');
    }

    public function test_resend_email_skips_unconfirmed_and_never_sends_ticket_email(): void
    {
        config(['app.hosted' => false, 'mail.default' => 'smtp']);
        Queue::fake();
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays(), 'requires_approval' => true]);
        [, $sale] = $this->book($role, $type);

        // Pending (unconfirmed) booking: resend is skipped entirely.
        $this->assertSame(EmailService::ERROR_SKIPPED, (new EmailService)->sendTicketEmail($sale->fresh(), $role));

        // Confirmed booking: resend re-sends AppointmentConfirmed, never TicketPurchase.
        $sale->forceFill(['confirmed_at' => now()])->saveQuietly();
        $result = (new EmailService)->sendTicketEmail($sale->fresh(), $role);
        $this->assertTrue($result === true);
        $this->assertNotEmpty($this->queuedMailables(AppointmentConfirmed::class));
        $this->assertEmpty($this->queuedMailables(\App\Mail\TicketPurchase::class), 'appointment resend must never send the QR ticket email');
    }

    public function test_cancelled_pending_booking_leaves_requests_tab(): void
    {
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays(), 'requires_approval' => true]);
        [$event, $sale] = $this->book($role, $type);

        $pendingCount = fn () => Event::whereHas('roles', function ($q) use ($role) {
            $q->where('role_id', $role->id)->whereNull('is_accepted');
        })->count();

        $this->assertSame(1, $pendingCount());

        // Guest cancels via the manage page.
        $this->post(route('appointments.manage_cancel', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]));

        $this->assertSame('cancelled', $sale->fresh()->status);
        $this->assertSame(0, $pendingCount(), 'a cancelled pending booking must leave the requests query');
    }

    public function test_sales_page_cancel_emails_the_guest(): void
    {
        config(['app.hosted' => false, 'mail.default' => 'smtp']);
        Queue::fake();
        $owner = $this->createOwner();
        $role = $this->createRole($owner, 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);
        [, $sale] = $this->book($role, $type);

        $this->actingAs($owner)->post(route('sales.action', ['sale_id' => UrlUtils::encodeId($sale->id)]), [
            'action' => 'cancel',
        ]);

        $this->assertSame('cancelled', $sale->fresh()->status);
        $this->assertNotEmpty($this->queuedMailables(\App\Mail\AppointmentCancelled::class), 'Sales-page cancel must email the guest');
    }

    public function test_picker_props_use_relative_urls(): void
    {
        $role = $this->createRole($this->createOwner(), 'talent', ['timezone' => 'America/New_York']);
        $type = $this->createAppointmentType($role, ['weekly_windows' => $this->allDays()]);

        $html = $this->get(route('appointments.book_type', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]))->getContent();
        preg_match('/data-props="([^"]+)"/', $html, $m);
        $props = json_decode(html_entity_decode($m[1], ENT_QUOTES), true);

        $this->assertStringStartsWith('/', $props['slotsUrl'], 'slots URL must be path-relative (custom-domain safe)');
        $this->assertStringStartsWith('/', $props['bookUrl']);
        $this->assertStringNotContainsString('http', $props['slotsUrl']);
    }
}
