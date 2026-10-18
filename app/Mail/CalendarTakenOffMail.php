<?php

namespace App\Mail;

use App\Models\Role;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * To a schedule's owner, once: a calendar sync took events that were on the public page off it,
 * because the calendar now marks them private or their text carries meeting details or an email
 * thread (CalendarArrival). Nothing was deleted; this says so, and where to look.
 */
class CalendarTakenOffMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  list<string>  $names  The first few events taken off, by name.
     */
    public function __construct(public Role $role, public User $recipient, public int $count, public array $names, public string $provider) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: trans_choice('messages.calendar_taken_off_mail_subject', $this->count, ['count' => number_format($this->count), 'name' => $this->role->name]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.calendar_taken_off',
            text: 'emails.calendar_taken_off_text',
            with: [
                'role' => $this->role,
                'recipient' => $this->recipient,
                'count' => $this->count,
                'names' => $this->names,
                'provider' => $this->provider,
                'reviewUrl' => route('role.calendar_review', ['subdomain' => $this->role->subdomain, 'show' => 'taken']),
            ]
        );
    }
}
