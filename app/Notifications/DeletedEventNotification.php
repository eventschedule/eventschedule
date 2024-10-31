<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeletedEventNotification extends Notification
{
    use Queueable;

    protected $event;
    protected $user;

    /**
     * Create a new notification instance.
     */
    public function __construct($event, $user)
    {
        $this->event = $event;
        $this->user = $user;
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
        $role = $this->event->role();
        $user = $this->user;

        return (new MailMessage)
                    ->replyTo($user->email, $user->name)
                    ->subject(__('messages.event_has_been_deleted'))
                    ->line(str_replace(
                        [':name', ':venue', ':user'],
                        [$role->name, $event->getVenueDisplayName(), $user->name],
                        __('messages.event_has_been_deleted_details'))
                    );
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
