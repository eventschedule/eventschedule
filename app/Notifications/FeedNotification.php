<?php

namespace App\Notifications;

use App\Models\Role;
use App\Services\NotificationEmailService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * What a feed needs somebody for: drafts waiting to be looked over, an event people signed up for
 * that the source moved, called off or no longer lists, and a feed that stopped being read.
 *
 * One class for the four, because they are one preference ("Feeds") and one shape: what happened,
 * in a sentence, and the button that opens the place where it is dealt with. Sent by
 * FeedNotifier, which decides when and how often.
 */
class FeedNotification extends Notification
{
    use Queueable;

    public const REVIEW = 'review';

    public const DECIDE = 'decide';

    public const FAILING = 'failing';

    public const PAUSED = 'paused';

    /**
     * @param  array{feed: string, url: string, count?: int, event?: string, says?: string, people?: int, more?: int, since?: string}  $facts
     *                                                                                                                                         `url` is a path, made
     *                                                                                                                                         absolute when the mail
     *                                                                                                                                         is built.
     */
    public function __construct(protected Role $role, protected string $kind, protected array $facts) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function kind(): string
    {
        return $this->kind;
    }

    public function facts(): array
    {
        return $this->facts;
    }

    /** The subject, which is also the sentence the mail opens with. */
    public function subject(): string
    {
        $facts = $this->facts;

        return match ($this->kind) {
            self::REVIEW => trans_choice('messages.feeds_mail_review', $facts['count'], ['count' => number_format($facts['count']), 'feed' => $facts['feed']]),
            self::DECIDE => __('messages.feeds_mail_decide_'.$facts['says'], ['event' => $facts['event']]),
            self::PAUSED => __('messages.feeds_mail_paused', ['feed' => $facts['feed']]),
            // The day, as the schedule counts days and in the reader's language: this runs with
            // the notification's own locale set.
            default => __('messages.feeds_mail_failing', [
                'feed' => $facts['feed'],
                'date' => Carbon::parse($facts['since'])->setTimezone($this->role->captureTimezone())->translatedFormat('j F'),
            ]),
        };
    }

    public function toMail(object $notifiable): MailMessage
    {
        // app_url(), never a bare route(): from the scheduler the host is whatever is running.
        $unsubscribeUrl = app_url(route('role.unsubscribe', ['subdomain' => $this->role->subdomain], false));
        $notificationEmailUnsubscribeUrl = null;
        if ($notifiable instanceof AnonymousNotifiable) {
            $notificationEmailUnsubscribeUrl = NotificationEmailService::unsubscribeUrl($this->role);
            $unsubscribeUrl = $notificationEmailUnsubscribeUrl;
        }

        $data = [
            'role' => $this->role,
            'kind' => $this->kind,
            'facts' => $this->facts,
            'subject' => $this->subject(),
            'actionUrl' => app_url($this->facts['url']),
            'unsubscribeUrl' => $unsubscribeUrl,
            'notificationEmailUnsubscribeUrl' => $notificationEmailUnsubscribeUrl,
        ];

        return (new MailMessage)
            ->subject($data['subject'])
            ->view('emails.feed_notice', $data)
            ->text('emails.feed_notice_text', $data)
            ->withSymfonyMessage(function ($message) use ($unsubscribeUrl) {
                $message->getHeaders()->addTextHeader('List-Unsubscribe', '<'.$unsubscribeUrl.'>');
                $message->getHeaders()->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
            });
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
