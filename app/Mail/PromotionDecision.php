<?php

namespace App\Mail;

use App\Models\BoostCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Tells an advertiser whether their on-network promotion passed review.
 *
 * One mailable for both outcomes because the advertiser is waiting either way, and the
 * difference is a heading, a reason and whether a refund is mentioned.
 *
 * $approved is a plain scalar constructor argument rather than something re-derived from the
 * model: SerializesModels re-fetches the campaign when the mail is queued, so anything read
 * off it at send time reflects the row as it is THEN, not as it was at the decision.
 */
class PromotionDecision extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected BoostCampaign $campaign,
        protected bool $approved,
    ) {}

    public function envelope(): Envelope
    {
        $key = $this->approved
            ? 'messages.promotion_email_approved_subject'
            : 'messages.promotion_email_rejected_subject';

        return new Envelope(
            subject: __($key).' - '.($this->campaign->event?->name ?? __('messages.deleted_event')),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.promotion.decision',
            text: 'emails.promotion.decision_text',
            with: [
                'campaign' => $this->campaign,
                'event' => $this->campaign->event,
                'approved' => $this->approved,
                'notes' => $this->campaign->moderation_notes,
                'url' => route('boost.show', ['hash' => $this->campaign->hashedId()]),
            ]
        );
    }
}
