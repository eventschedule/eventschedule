<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Support\MailConfigManager;
use App\Utils\NotificationUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RequestAcceptedNotification extends Notification
{
    use Queueable;

    protected Event $event;
    protected ?User $actor;
    protected string $recipientType;
    protected ?Role $contextRole;

    public function __construct(Event $event, ?User $actor = null, string $recipientType = 'talent', ?Role $contextRole = null)
    {
        $this->event = $event;
        $this->actor = $actor;
        $this->recipientType = $recipientType;
        $this->contextRole = $contextRole;
    }

    public function via(object $notifiable): array
    {
        MailConfigManager::applyFromDatabase();

        if (config('mail.disable_delivery')) {
            return [];
        }

        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $eventName = NotificationUtils::eventDisplayName($this->event);
        $venueName = $this->event->getVenueDisplayName();
        $talentName = optional($this->event->role())->getDisplayName();
        $date = $this->event->localStartsAt(true);

        $lineKey = $this->recipientType === 'organizer'
            ? 'messages.booking_request_accepted_organizer'
            : 'messages.booking_request_accepted_talent';

        $mail = (new MailMessage)
            ->subject(__('messages.booking_request_accepted_subject'))
            ->line(__($lineKey, [
                'talent' => $talentName ?: $eventName,
                'venue' => $venueName ?: __('messages.event'),
                'date' => $date ?: __('messages.date_to_be_announced'),
            ]))
            ->action(__('messages.view_event'), $this->event->getGuestUrl($this->contextRole?->subdomain ?? $this->event->venue?->subdomain))
            ->line(__('messages.thank_you_for_using'));

        if ($this->actor && $this->actor->email) {
            $mail->replyTo($this->actor->email, $this->actor->name);
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }

    public function toMailHeaders(): array
    {
        $subdomain = $this->contextRole?->subdomain
            ?? $this->event->venue?->subdomain
            ?? $this->event->role()?->subdomain;

        if (! $subdomain) {
            return [];
        }

        return [
            'List-Unsubscribe' => '<' . route('role.unsubscribe', ['subdomain' => $subdomain]) . '>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ];
    }
}

