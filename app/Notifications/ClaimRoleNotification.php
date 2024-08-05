<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ClaimRoleNotification extends Notification
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
        $role = $this->event->role;
        $venue = $this->event->venue;
        $user = $this->event->user;

        return (new MailMessage)
                    ->subject(str_replace(
                        [':user', ':role'], 
                        [$user->name, $role->name],
                        __('messages.claim_your_role')))
                    ->line(str_replace(
                        [':user', ':role', ':venue'], 
                        [$user->name, $role->name, $venue->name], 
                        __('messages.claim_your_role_details')))
                    ->action(__('messages.sign_up'), url('/'));
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
