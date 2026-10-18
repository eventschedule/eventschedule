<?php

namespace App\Mail;

use App\Models\Role;
use App\Models\User;
use App\Services\CalendarArrival;
use App\Utils\CalendarPrivacy;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Route;

/**
 * The operator telling a schedule's owner that its page looks like a private calendar
 * (/admin/private-calendars, step 3). Owner voice: the platform writing about the schedule.
 *
 * It names what was found by kind (dial-in codes, links with passwords, an email thread) and never
 * prints a code or a link from the events: the mail is about a leak and must not be a second one.
 * It says what was done for the owner (hidden, paused) only when it was, and every link goes to a
 * page that survives signing in: a fragment is lost through the login form, a query is not.
 */
class CalendarPrivacyMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  list<string>  $kinds  CalendarPrivacy::FLAGS found
     */
    public function __construct(
        public Role $role,
        public User $owner,
        public array $kinds,
        public int $hidden,
        public bool $paused,
        public ?string $provider,
    ) {}

    public function envelope(): Envelope
    {
        $support = config('app.support_email');

        return new Envelope(
            subject: __('messages.calendar_privacy_mail_subject', ['name' => $this->role->name]),
            replyTo: $support ? [new Address($support)] : [],
        );
    }

    public function content(): Content
    {
        $tab = match ($this->provider) {
            CalendarArrival::MICROSOFT => 'outlook',
            CalendarArrival::CALDAV => 'caldav',
            default => 'google',
        };

        $chooseUrl = route('role.edit', ['subdomain' => $this->role->subdomain]).'?integration_tab='.$tab;
        $reviewUrl = Route::has('role.calendar_review')
            ? route('role.calendar_review', ['subdomain' => $this->role->subdomain])
            : route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'schedule']);

        return new Content(
            view: 'emails.calendar_privacy',
            text: 'emails.calendar_privacy_text',
            with: [
                'role' => $this->role,
                'owner' => $this->owner,
                'found' => array_values(array_map(fn (string $kind) => __('messages.calendar_privacy_mail_kind_'.$kind), array_intersect(CalendarPrivacy::FLAGS, $this->kinds))),
                'hidden' => $this->hidden,
                'paused' => $this->paused,
                'pageUrl' => $this->role->getGuestUrl(true),
                'chooseUrl' => $chooseUrl,
                'reviewUrl' => $reviewUrl,
            ],
        );
    }
}
