<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Utils\UrlUtils;

class RequestApprovedNotification extends Notification
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
        $role = $this->event->role;

        return (new MailMessage)
                    ->subject(str_replace(':venue', $venue->name, __('messages.' . $role->type . '_request_accepted')))
                    ->line(str_replace(':venue', $venue->name, __('messages.' . $role->type . '_request_accepted')))
                    ->action(__('messages.view_event'), route('role.view_guest', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($this->event->id)]))
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
}
