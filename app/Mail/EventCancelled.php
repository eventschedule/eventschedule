<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Role;
use App\Utils\TicketInvite;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
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

    /**
     * The buyer this copy of the mail goes to, for the calendar update it carries (TicketInvite).
     * Set by EventChangeNotifier through forAttendee(); a copy without it (the owner's preview of
     * a change) attaches nothing. A property with a default rather than a constructor argument: a
     * mail queued before it existed unserializes without it, and an uninitialised typed property
     * would throw.
     */
    protected ?string $attendeeEmail = null;

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

    public function forAttendee(string $email): static
    {
        $this->attendeeEmail = $email;

        return $this;
    }

    /**
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        if (! $this->attendeeEmail) {
            return [];
        }

        return TicketInvite::attachmentsFor($this->event, $this->role, $this->attendeeEmail, 'CANCEL');
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
