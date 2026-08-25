<?php

namespace App\Mail;

use App\Models\RoleTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Offers a schedule to the address its owner nominated (discussion #119).
 *
 * The link carries a token, but the token alone cannot complete anything: accepting
 * requires being signed in as this address. So the worst a misaddressed offer can do is
 * show the recipient a schedule name.
 */
class ScheduleTransferInvite extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public RoleTransfer $transfer) {}

    public function envelope(): Envelope
    {
        $role = $this->transfer->role;
        $from = $this->transfer->fromUser;

        return new Envelope(
            subject: __('messages.schedule_transfer_invite_subject', ['name' => $role?->name]),
            replyTo: $from ? [new Address($from->email, $from->name)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.schedule_transfer_invite',
            text: 'emails.schedule_transfer_invite_text',
            with: [
                'transfer' => $this->transfer,
                'role' => $this->transfer->role,
                'fromUser' => $this->transfer->fromUser,
                // app_url() so the recipient lands on the host that holds the auth
                // session; route(..., false) because an absolute path would double the
                // base and 404 silently.
                'acceptUrl' => app_url(route('role.transfer.show', ['token' => $this->transfer->token], false)),
            ],
        );
    }
}
