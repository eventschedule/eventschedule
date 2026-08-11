<?php

namespace App\Mail;

use App\Models\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * One digest per schedule per day, not one email per buyer.
 *
 * The VIP asked for the reminder to reach the organizer too. Taken literally that is a mail per
 * payment: forty seats on four-part plans means forty emails landing in one morning, three months
 * running. That floods the mailbox, risks the hosted sender's reputation, and trains the
 * organizer to filter the exact address that will later carry the failure notices. So the routine
 * "these will be charged tomorrow" case is aggregated, the way NotifyRequestChanges aggregates
 * pending requests. Failures stay immediate and individual, because those are actionable.
 *
 * $kind is 'due' (routine, tomorrow's charges) or 'overdue' (the pre-event backstop).
 */
class InstallmentOrganizerDigest extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected Role $role,
        protected array $rows,
        protected string $kind = 'due',
        protected ?string $currency = null,
        protected float $total = 0.0,
    ) {}

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        if ($this->role->hasEmailSettings()) {
            $emailSettings = $this->role->getEmailSettings();
            if (! empty($emailSettings['from_address'])) {
                $fromAddress = $emailSettings['from_address'];
            }
            if (! empty($emailSettings['from_name'])) {
                $fromName = $emailSettings['from_name'];
            }
        }

        $key = $this->kind === 'overdue'
            ? 'messages.installment_digest_overdue_subject'
            : 'messages.installment_digest_due_subject';

        return new Envelope(
            subject: trans_choice($key, count($this->rows), ['count' => count($this->rows)]),
            from: new Address($fromAddress, $fromName),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.installment_digest',
            text: 'emails.installment_digest_text',
            with: [
                'role' => $this->role,
                'rows' => $this->rows,
                'kind' => $this->kind,
                'currency' => $this->currency,
                'total' => $this->total,
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
