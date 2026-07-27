<?php

namespace App\Mail;

use App\Models\AppointmentType;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

/**
 * Owner/editor notification for appointment activity. $kind: 'booked' (confirmed), 'pending'
 * (awaiting approval), or 'cancelled' (guest cancelled - includes refund guidance when paid).
 */
class AppointmentBookedNotification extends Mailable
{
    use Queueable, SerializesModels;

    protected $sale;

    protected $event;

    protected $role;

    protected $type;

    protected $kind;

    protected $wasPaid;

    protected $oldStartsAt;

    protected $wasShortNotice;

    /**
     * $wasPaid must be captured at DISPATCH time for cancelled-kind mails: SerializesModels
     * re-fetches the Sale at send time, after its status has already flipped to cancelled, so
     * deriving "was this paid?" from the live status would always say no. Scalars survive
     * queue serialization; null falls back to the live status (booked/pending kinds).
     */
    public function __construct(Sale $sale, Event $event, ?Role $role, ?AppointmentType $type, string $kind = 'booked', ?bool $wasPaid = null, ?string $oldStartsAt = null)
    {
        $this->sale = $sale;
        $this->event = $event;
        $this->role = $role;
        $this->type = $type;
        $this->kind = in_array($kind, ['booked', 'pending', 'cancelled', 'rescheduled', 'rescheduled_pending'], true)
            ? $kind
            : 'booked';
        $this->wasPaid = $wasPaid;
        // Same scalar-not-model reasoning as $wasPaid: the event already holds the NEW time.
        $this->oldStartsAt = $oldStartsAt;
        // Decided HERE, at dispatch, not at render. now() in the worker is not now() at dispatch: a
        // backed-up queue turned "moved less than a day before the original time" into a false statement
        // in one direction, and dropped a genuine warning in the other once the original time had passed.
        $this->wasShortNotice = $this->isMove() && $oldStartsAt
            ? self::withinShortNotice($oldStartsAt)
            : false;
    }

    /** Both move kinds want the moved-from block and the short-notice band. */
    protected function isMove(): bool
    {
        return in_array($this->kind, ['rescheduled', 'rescheduled_pending'], true);
    }

    /** Whether $oldStartsAt (UTC 'Y-m-d H:i:s') is still ahead but inside 24 hours. */
    protected static function withinShortNotice(string $oldStartsAt): bool
    {
        try {
            $old = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $oldStartsAt, 'UTC');

            return $old->isFuture() && now()->diffInHours($old, false) <= 24;
        } catch (\Throwable $e) {
            return false; // a malformed stored value must not stop the notification going out
        }
    }

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        if ($this->role && $this->role->hasEmailSettings()) {
            $settings = $this->role->getEmailSettings();
            if (! empty($settings['from_address'])) {
                $fromAddress = $settings['from_address'];
            }
            if (! empty($settings['from_name'])) {
                $fromName = $settings['from_name'];
            }
        }

        $subjectKey = match ($this->kind) {
            'pending' => 'appointment_owner_pending_subject',
            'cancelled' => 'appointment_owner_cancelled_subject',
            // A move that needs re-approval is still a move first: the owner has to see that before they
            // see that it needs a decision, or it reads as an ordinary new request.
            'rescheduled', 'rescheduled_pending' => 'appointment_owner_rescheduled_subject',
            default => 'appointment_owner_booked_subject',
        };

        return new Envelope(
            subject: __('messages.'.$subjectKey, ['name' => $this->type?->name ?? $this->event->name]),
            from: new Address($fromAddress, $fromName),
        );
    }

    public function content(): Content
    {
        // A re-pending move needs a decision, so it lands on Requests like any other pending booking.
        $tab = in_array($this->kind, ['pending', 'rescheduled_pending'], true) ? 'requests' : 'appointments';
        $bookingsUrl = app_url(route('role.view_admin', array_filter([
            'subdomain' => $this->role?->subdomain,
            'tab' => $tab,
            // Without this the owner lands on the appointment TYPES list, because that is the tab's
            // default sub-view - so a "View" click from a booking email showed settings, not the booking.
            'view' => $tab === 'appointments' ? 'bookings' : null,
        ]), false));

        $paid = $this->wasPaid ?? ($this->sale->status === 'paid' && (float) $this->sale->payment_amount > 0);

        // A move made inside the last day before the ORIGINAL time is the one an owner most needs to
        // notice, and it is invisible from the new time alone.
        $shortNotice = null;
        if ($this->wasShortNotice && $this->oldStartsAt) {
            try {
                $old = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $this->oldStartsAt, 'UTC');
                $shownTz = \App\Utils\AppointmentTimeUtils::scheduleTimezone($this->event);
                $shortNotice = __('messages.appointment_owner_moved_short_notice', [
                    'time' => $old->setTimezone($shownTz)->translatedFormat('l, F j, Y').' '
                        .$old->copy()->setTimezone($shownTz)->format(($this->role?->use_24_hour_time ?? false) ? 'H:i' : 'g:i A'),
                ]);
            } catch (\Throwable $e) {
                // A malformed stored value must not stop the notification going out.
            }
        }

        return new Content(
            view: 'emails.appointment_owner_notification',
            text: 'emails.appointment_owner_notification_text',
            with: [
                'sale' => $this->sale,
                'event' => $this->event,
                'role' => $this->role,
                'type' => $this->type,
                'kind' => $this->kind,
                'bookingsUrl' => $bookingsUrl,
                'showRefund' => $this->kind === 'cancelled' && $paid,
                'paidLabel' => $paid,
                'shortNotice' => $shortNotice,
            ],
        );
    }

    public function headers(): Headers
    {
        if ($this->role) {
            return new Headers(
                text: [
                    'List-Unsubscribe' => '<'.route('role.unsubscribe', ['subdomain' => $this->role->subdomain]).'>',
                    'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
                ],
            );
        }

        return new Headers;
    }
}
