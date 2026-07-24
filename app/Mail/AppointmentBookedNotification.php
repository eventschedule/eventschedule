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

    public function __construct(Sale $sale, Event $event, ?Role $role, ?AppointmentType $type, string $kind = 'booked')
    {
        $this->sale = $sale;
        $this->event = $event;
        $this->role = $role;
        $this->type = $type;
        $this->kind = in_array($kind, ['booked', 'pending', 'cancelled'], true) ? $kind : 'booked';
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
            default => 'appointment_owner_booked_subject',
        };

        return new Envelope(
            subject: __('messages.'.$subjectKey, ['name' => $this->type?->name ?? $this->event->name]),
            from: new Address($fromAddress, $fromName),
        );
    }

    public function content(): Content
    {
        $tab = $this->kind === 'pending' ? 'requests' : 'appointments';
        $bookingsUrl = app_url(route('role.view_admin', [
            'subdomain' => $this->role?->subdomain,
            'tab' => $tab,
        ], false));

        $paid = $this->sale->status === 'paid' && (float) $this->sale->payment_amount > 0;

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
