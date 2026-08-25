<?php

namespace App\Mail;

use App\Models\RoleTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Tells the owner their handover offer was turned down. Nothing changed. */
class ScheduleTransferDeclined extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public RoleTransfer $transfer) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.schedule_transfer_declined_subject', ['name' => $this->transfer->role?->name]),
        );
    }

    public function content(): Content
    {
        $role = $this->transfer->role;

        return new Content(
            view: 'emails.schedule_transfer_declined',
            text: 'emails.schedule_transfer_declined_text',
            with: [
                'transfer' => $this->transfer,
                'role' => $role,
                'teamUrl' => app_url(route('role.view_admin', ['subdomain' => $role?->subdomain, 'tab' => 'team'], false)),
            ],
        );
    }
}
