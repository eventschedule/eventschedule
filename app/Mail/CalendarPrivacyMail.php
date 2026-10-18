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

    /**
     * The steps that apply, in order, each with where it is done; the first one's is the button.
     * Choosing a calendar is for a calendar sync (CalDAV's is made again, it has no chooser).
     * Checking the hidden events is for when some were hidden, and taking them off the page for
     * when none were: they are still public. Turning the sync back on is for a paused one.
     *
     * @return non-empty-list<array{title: string, text: string, url: ?string, link: ?string}>
     */
    public static function steps(?string $sync, int $hidden, bool $paused, string $chooseUrl, string $reviewUrl, string $scheduleUrl): array
    {
        $steps = [];

        if ($sync) {
            $caldav = $sync === CalendarArrival::CALDAV;
            $steps[] = [
                'title' => __('messages.calendar_privacy_mail_step_choose_title'),
                'text' => __($caldav ? 'messages.calendar_privacy_mail_step_choose_caldav' : 'messages.calendar_privacy_mail_step_choose'),
                'url' => $chooseUrl,
                'link' => __($caldav ? 'messages.calendar_privacy_mail_action_caldav' : 'messages.calendar_privacy_mail_action'),
            ];
        }

        $steps[] = $hidden > 0
            ? [
                'title' => __('messages.calendar_privacy_mail_step_check_title'),
                'text' => __('messages.calendar_privacy_mail_step_check'),
                'url' => $reviewUrl,
                'link' => __('messages.calendar_privacy_mail_step_check_link'),
            ]
            : [
                'title' => __('messages.calendar_privacy_mail_step_unpublish_title'),
                'text' => __('messages.calendar_privacy_mail_step_unpublish'),
                'url' => $scheduleUrl,
                'link' => __('messages.calendar_privacy_mail_step_unpublish_link'),
            ];

        if ($paused) {
            $steps[] = [
                'title' => __('messages.calendar_privacy_mail_step_resume_title'),
                'text' => __('messages.calendar_privacy_mail_step_resume'),
                'url' => null,
                'link' => null,
            ];
        }

        return $steps;
    }

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
                // Where the owner takes events off the page themselves, when nothing was hidden.
                'scheduleUrl' => route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'schedule']),
                // The calendar sync behind it, or null for a feed or a one-time import, which has
                // no calendar to choose and no sync to turn back on.
                'sync' => in_array($this->provider, CalendarArrival::PROVIDERS, true) ? $this->provider : null,
                // The codes are worth changing only when links or codes were what was found.
                'codes' => (bool) array_intersect([CalendarPrivacy::LINK_PASSWORD, CalendarPrivacy::ACCESS_CODES], $this->kinds),
            ],
        );
    }
}
