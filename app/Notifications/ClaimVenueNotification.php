<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Utils\UrlUtils;

class ClaimVenueNotification extends Notification
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
        $user = $this->event->user;

        return (new MailMessage)
                    ->cc($user->email)
                    ->replyTo($user->email, $user->name)
                    ->subject(str_replace(
                        [':role', ':venue'], 
                        [$role->name, $venue->name],
                        __('messages.claim_your_venue')))
                    ->line(str_replace(
                        [':venue', ':role'], 
                        [$venue->name, $role->name],
                        __('messages.claim_your_venue')))
                    ->action(__('messages.view_event'), route('role.view_guest', ['subdomain' => $role->subdomain, 'hash' => UrlUtils::encodeId($this->event->id)]))
                    ->line(__('messages.claim_email_line2'));
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
