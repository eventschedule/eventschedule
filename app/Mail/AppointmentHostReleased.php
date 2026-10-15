<?php

namespace App\Mail;

use App\Models\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells a schedule's owner that a team member who hosted appointment types there has left, so
 * those types are off until a new host is chosen (AppointmentHostService::releaseHost()).
 *
 * The host's name, the type names and the booking count are scalars taken at dispatch: by the
 * time a worker renders this the account may be gone and the types renamed.
 */
class AppointmentHostReleased extends Mailable
{
    use Queueable, SerializesModels;

    /** @param  array<int, string>  $typeNames */
    public function __construct(
        public Role $role,
        public string $hostName,
        public array $typeNames,
        public int $futureBookings,
        public string $hostHash,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.appointment_host_released_subject', ['host' => $this->hostName, 'name' => $this->role->name]),
        );
    }

    public function content(): Content
    {
        $typesUrl = app_url(route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'appointments'], false));
        $bookingsUrl = app_url(route('role.view_admin', [
            'subdomain' => $this->role->subdomain,
            'tab' => 'appointments',
            'view' => 'bookings',
            'host' => $this->hostHash,
        ], false));

        return new Content(
            view: 'emails.appointment_host_released',
            text: 'emails.appointment_host_released_text',
            with: [
                'role' => $this->role,
                'hostName' => $this->hostName,
                'typeNames' => $this->typeNames,
                'futureBookings' => $this->futureBookings,
                'typesUrl' => $typesUrl,
                'bookingsUrl' => $bookingsUrl,
            ],
        );
    }
}
