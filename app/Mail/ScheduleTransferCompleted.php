<?php

namespace App\Mail;

use App\Models\RoleTransfer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Confirms a completed handover. One class, two audiences: the departing owner needs to
 * know what stopped (access, and on hosted the subscription), the new owner needs to know
 * what starts (billing is now theirs).
 */
class ScheduleTransferCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public RoleTransfer $transfer,
        public bool $forPreviousOwner,
        // What ScheduleTransferService::cancelSubscription() actually did. Derived from
        // config('app.hosted') alone, this used to promise every departing owner that
        // their subscription had been cancelled - including the ones who never had one.
        public bool $subscriptionCancelled = false,
    ) {}

    public function envelope(): Envelope
    {
        $name = $this->transfer->role?->name;

        return new Envelope(
            subject: $this->forPreviousOwner
                ? __('messages.schedule_transfer_sent_subject', ['name' => $name])
                : __('messages.schedule_transfer_received_subject', ['name' => $name]),
        );
    }

    public function content(): Content
    {
        $role = $this->transfer->role;

        return new Content(
            view: 'emails.schedule_transfer_completed',
            text: 'emails.schedule_transfer_completed_text',
            with: [
                'transfer' => $this->transfer,
                'role' => $role,
                'forPreviousOwner' => $this->forPreviousOwner,
                // Only the new owner gets a link into the admin portal; the previous
                // owner no longer has access to it.
                'adminUrl' => $this->forPreviousOwner
                    ? null
                    : app_url(route('role.view_admin', ['subdomain' => $role?->subdomain, 'tab' => 'schedule'], false)),
                'billingEnded' => $this->forPreviousOwner && $this->subscriptionCancelled,
                // Hosted, but there was nothing to cancel - say so rather than staying
                // silent about money on a page that is otherwise all about it.
                'billingNoop' => $this->forPreviousOwner && ! $this->subscriptionCancelled && config('app.hosted'),
            ],
        );
    }
}
