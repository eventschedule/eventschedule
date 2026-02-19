<?php

namespace App\Mail;

use App\Models\BoostCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BoostCompleted extends Mailable
{
    use Queueable, SerializesModels;

    protected BoostCampaign $campaign;

    public function __construct(BoostCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.boost_email_completed_subject').' - '.($this->campaign->event?->name ?? __('messages.deleted_event')),
        );
    }

    public function content(): Content
    {
        $refundAmount = 0;
        if (in_array($this->campaign->billing_status, ['refunded', 'partially_refunded'])) {
            $unspent = $this->campaign->user_budget - ($this->campaign->actual_spend ?? 0);
            if ($unspent > 0) {
                $refundAmount = round($unspent * (1 + $this->campaign->markup_rate), 2);
            }
        }

        return new Content(
            view: 'emails.boost.completed',
            text: 'emails.boost.completed_text',
            with: [
                'campaign' => $this->campaign,
                'event' => $this->campaign->event,
                'refundAmount' => $refundAmount,
                'boostUrl' => route('boost.show', ['hash' => $this->campaign->hashedId()]),
            ]
        );
    }
}
