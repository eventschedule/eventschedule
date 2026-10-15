<?php

namespace App\Mail;

use App\Models\BoostStatement;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * A boost partner's monthly statement: each campaign's spend in the month, the fee, any
 * adjustment, and what was charged (or why not). Account voice: the platform writing to the
 * account about its own bill.
 */
class BoostStatementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public BoostStatement $statement) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: __('messages.boost_statement_mail_subject', ['month' => $this->monthLabel()]));
    }

    public function content(): Content
    {
        $this->statement->loadMissing(['lines.campaign.event', 'partner']);

        return new Content(
            view: 'emails.boost.statement',
            text: 'emails.boost.statement_text',
            with: [
                'statement' => $this->statement,
                'month' => $this->monthLabel(),
                'money' => fn ($amount) => \App\Utils\MoneyUtils::format((float) $amount, $this->statement->currency),
                'url' => route('boost.partner.statement', ['period' => $this->statement->period, 'currency' => $this->statement->currency]),
            ],
        );
    }

    protected function monthLabel(): string
    {
        return \Carbon\Carbon::createFromFormat('Y-m-d', $this->statement->period.'-01')->translatedFormat('F Y');
    }
}
