<?php

namespace App\Mail;

use App\Models\BoostCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BoostRejected extends Mailable
{
    use Queueable, SerializesModels;

    protected BoostCampaign $campaign;

    protected bool $refunded;

    public function __construct(BoostCampaign $campaign, bool $refunded = true)
    {
        $this->campaign = $campaign;
        $this->refunded = $refunded;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.boost_email_rejected_subject').' - '.($this->campaign->event?->name ?? __('messages.deleted_event')),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.boost.rejected',
            text: 'emails.boost.rejected_text',
            with: [
                'campaign' => $this->campaign,
                'event' => $this->campaign->event,
                'rejectionReason' => $this->campaign->meta_rejection_reason,
                'refunded' => $this->refunded,
            ]
        );
    }
}
