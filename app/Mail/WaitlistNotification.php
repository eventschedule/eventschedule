<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Role;
use App\Models\TicketWaitlist;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WaitlistNotification extends Mailable
{
    use Queueable, SerializesModels;

    protected $entry;

    protected $event;

    protected $role;

    public function __construct(TicketWaitlist $entry, Event $event, ?Role $role = null)
    {
        $this->entry = $entry;
        $this->event = $event;
        $this->role = $role;
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

        $subject = $this->event->rsvp_enabled
            ? __('messages.waitlist_spots_available')
            : __('messages.waitlist_tickets_available');

        return new Envelope(
            subject: $subject.' - '.$this->readEvent()->name,
            from: new Address($fromAddress, $fromName),
        );
    }

    public function content(): Content
    {
        $isRsvp = (bool) $this->event->rsvp_enabled;
        $eventUrl = $this->event->getGuestUrl($this->entry->subdomain, $this->entry->event_date, true);
        $eventUrl .= (str_contains($eventUrl, '?') ? '&' : '?').($isRsvp ? 'rsvp=true' : 'tickets=true');
        // Opened in the translation the person joined from, when that is what they read.
        $locale = $this->entry->locale;
        if (is_valid_language_code($locale) && $locale === $this->event->getTranslationLanguageCode() && $locale !== $this->event->getLanguageCode()) {
            $eventUrl = \App\Models\Sale::withLanguage($eventUrl, $locale);
        }

        return new Content(
            view: 'emails.waitlist_notification',
            text: 'emails.waitlist_notification_text',
            with: [
                'entry' => $this->entry,
                'event' => $this->readEvent(),
                'role' => $this->role,
                'eventUrl' => $eventUrl,
                'isRsvp' => $isRsvp,
                'unsubscribeUrl' => $this->role ? route('role.unsubscribe', ['subdomain' => $this->role->subdomain]) : '',
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }

    /** The event as the person read it: its name in the language they joined in (ticket_waitlists.locale), where it has one. */
    private function readEvent(): Event
    {
        return $this->event->readIn(is_valid_language_code($this->entry->locale) ? $this->entry->locale : null);
    }
}
