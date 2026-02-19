<?php

namespace App\Mail;

use App\Models\BoostCampaign;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BoostCreated extends Mailable
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
            subject: __('messages.boost_email_created_subject').' - '.($this->campaign->event?->name ?? __('messages.deleted_event')),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.boost.created',
            text: 'emails.boost.created_text',
            with: [
                'campaign' => $this->campaign,
                'event' => $this->campaign->event,
                'boostUrl' => route('boost.show', ['hash' => $this->campaign->hashedId()]),
            ]
        );
    }
}
