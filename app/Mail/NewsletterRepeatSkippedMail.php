<?php

namespace App\Mail;

use App\Models\Newsletter;
use App\Models\User;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * To a schedule's owner (and the newsletter's author while they still run the schedule): one run
 * of a repeating newsletter did not go, why, and when the next one is. Sent by
 * NewsletterService::runRepeat(), once per skipped run.
 */
class NewsletterRepeatSkippedMail extends Mailable
{
    use Queueable, SerializesModels;

    /** Why a run can be skipped. Each has a messages.newsletter_repeat_reason_* line. */
    public const REASONS = ['no_events', 'picked_passed', 'no_recipients', 'limit_exceeded', 'over_plan', 'requires_verification', 'failed'];

    public function __construct(
        public Newsletter $newsletter,
        public User $recipient,
        public string $reason,
        public Carbon $due,
        public ?Carbon $next,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('messages.newsletter_repeat_skipped_subject', ['schedule' => $this->newsletter->role->name]),
        );
    }

    public function content(): Content
    {
        $role = $this->newsletter->role;
        $zone = Newsletter::zoneOf($role);
        $locale = $this->locale ?: app()->getLocale();
        $reason = in_array($this->reason, self::REASONS, true) ? $this->reason : 'failed';
        // The schedule's own clock and its 24-hour choice, in the reader's language.
        $when = fn (Carbon $at) => $at->copy()->setTimezone($zone)->locale($locale)->isoFormat('LL')
            .' '.$at->copy()->setTimezone($zone)->format(get_use_24_hour_time($role) ? 'H:i' : 'g:i A');

        return new Content(
            view: 'emails.newsletter_repeat_skipped',
            text: 'emails.newsletter_repeat_skipped_text',
            with: [
                'role' => $role,
                'recipient' => $this->recipient,
                'subjectLine' => Newsletter::fillPlaceholders($this->newsletter->subject, $role, app(\App\Services\NewsletterService::class)->monthFor($this->newsletter, $this->due)),
                'dueText' => $when($this->due),
                'nextText' => $this->next ? $when($this->next) : null,
                'reasonText' => __('messages.newsletter_repeat_reason_'.$reason, [
                    'limit' => (int) config('usage.audience_mail_unverified_max_recipients', 50),
                ]),
                'editUrl' => route('newsletter.edit', [
                    'hash' => UrlUtils::encodeId($this->newsletter->id),
                    'role_id' => UrlUtils::encodeId($role->id),
                ]),
            ],
        );
    }
}
