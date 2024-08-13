<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

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
                    ->replyTo($user->email, $user->name)
                    ->subject(str_replace(
                        [':user', ':venue'], 
                        [$user->name, $venue->name],
                        __('messages.claim_your_venue')))
                    ->line(str_replace(
                        [':user', ':role', ':venue'], 
                        [$user->name, $role->name, $venue->name], 
                        __('messages.claim_your_venue_details')))
                    ->action(__('messages.sign_up'), route('sign_up', ['email' => $venue->email]));
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
