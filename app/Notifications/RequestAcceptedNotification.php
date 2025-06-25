<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Utils\UrlUtils;

class RequestAcceptedNotification extends Notification
{
    use Queueable;

    protected $event;

    /**
     * Create a new notification instance.
     */
    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $venue = $this->event->venue;
        $role = $this->event->role();

        return (new MailMessage)
                    ->replyTo($this->venue->user->email, $this->venue->user->name)
                    ->subject(str_replace(':venue', $venue->name, __('messages.' . $role->type . '_request_accepted')))
                    ->line(str_replace(':venue', $venue->name, __('messages.' . $role->type . '_request_accepted')))
                    ->action(__('messages.view_event'), $this->event->getGuestUrl())
                    ->line(__('messages.thank_you_for_using'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }

    /**
     * Get the notification's mail headers.
     */
    public function toMailHeaders(): array
    {
        $venue = $this->event->venue;
        return [
            'List-Unsubscribe' => '<' . route('role.unsubscribe', ['subdomain' => $venue->subdomain]) . '>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ];
    }
}
