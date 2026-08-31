<?php

namespace App\Mail;

use App\Mail\Concerns\ResolvesScheduleSender;
use App\Models\Role;
use App\Models\RoleSubscriber;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent once, when somebody gives a schedule their email address on the guest portal.
 *
 * This is the confirm step of a single-opt-in flow: the visitor's experience is one form and one
 * click, but AudienceResolver only ever mails rows with confirmed_at set. The repo has no bounce,
 * complaint or suppression handling anywhere, so an address typed in by somebody else has to cost
 * one stray message rather than a permanent subscription.
 *
 * Transactional, so it deliberately does NOT go through Role::canSendAudienceMail(): gating it
 * would withhold the confirmation step from exactly the unverified schedules that most need it.
 */
class SubscriptionConfirmation extends Mailable
{
    use Queueable, ResolvesScheduleSender, SerializesModels;

    public function __construct(
        public Role $role,
        public RoleSubscriber $subscriber,
        public string $confirmUrl,
        public string $unsubscribeUrl,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.subscription_confirm_subject', ['schedule' => $this->role->name]),
            from: $this->scheduleFrom($this->role),
            replyTo: $this->scheduleReplyTo($this->role),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription_confirmation',
            text: 'emails.subscription_confirmation_text',
            with: [
                'isRtl' => in_array(app()->getLocale(), ['ar', 'he']),
            ],
        );
    }
}
