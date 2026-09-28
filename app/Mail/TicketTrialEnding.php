<?php

namespace App\Mail;

use App\Models\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The card-free selling trial (roles.ticket_trial_ends_at) is about to end.
 *
 * Not SubscriptionTrialEnding: that one is about a plan and a card, and neither is true here.
 * Nothing is charged and nothing renews. What changes is that priced ticket rows stop selling,
 * and the owner needs to hear the other half too - buyers keep their tickets and refunds keep
 * working - or "your selling stops" reads as "your buyers lose out".
 */
class TicketTrialEnding extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(protected Role $role, protected string $endDate) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.ticket_trial_ending_subject', ['schedule' => $this->role->name, 'date' => $this->endDate]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription.ticket-trial-ending',
            text: 'emails.subscription.ticket-trial-ending_text',
            with: [
                'role' => $this->role,
                'endDate' => $this->endDate,
                'planUrl' => route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'plan']),
                // The schedule name is interpolated into prose, so ar/he need the direction set.
                'isRtl' => in_array(app()->getLocale(), ['ar', 'he']),
            ]
        );
    }
}
