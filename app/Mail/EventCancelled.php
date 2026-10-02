<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Role;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EventCancelled extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The cancelled occurrence (the sale's event_date), so a recurring event's mail shows the date
     * the buyer holds rather than the series' first one. A class property with a default rather
     * than a promoted one: a mail queued before it existed unserializes without it, and an
     * uninitialised typed property would throw.
     */
    protected ?string $date = null;

    public function __construct(
        protected Event $event,
        protected ?Role $role,
        protected string $eventUrl,
        protected ?string $note = null,
        protected ?string $recipientName = null,
        protected bool $partOfOrder = false,
        ?string $date = null,
    ) {
        $this->date = $date;
    }

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        if ($this->role && $this->role->hasEmailSettings()) {
            $emailSettings = $this->role->getEmailSettings();
            if (! empty($emailSettings['from_address'])) {
                $fromAddress = $emailSettings['from_address'];
            }
            if (! empty($emailSettings['from_name'])) {
                $fromName = $emailSettings['from_name'];
            }
        }

        return new Envelope(
            subject: __('messages.event_cancelled_subject', ['event' => $this->event->name]),
            from: new Address($fromAddress, $fromName),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.event_cancelled',
            text: 'emails.event_cancelled_text',
            with: [
                'event' => $this->event,
                'role' => $this->role,
                'note' => $this->note,
                'recipientName' => $this->recipientName,
                'eventUrl' => $this->eventUrl,
                'partOfOrder' => $this->partOfOrder,
                'date' => $this->date,
            ],
        );
    }
}
