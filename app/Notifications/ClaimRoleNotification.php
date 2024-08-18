<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Utils\UrlUtils;

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
                    ->replyTo($user->email, $user->name)
                    ->subject(str_replace(
                        [':venue', ':role'], 
                        [$venue->name, $role->name],
                        __('messages.claim_your_role')))
                    ->line(__('messages.claim_your_role_details'))
                    ->action(__('messages.view_event'), route('role.view_guest', ['hash' => UrlUtils::encodeId($this->event->id)]))
                    ->line(__('messages.claim_email_line2'))
                    ->action(__('messages.sign_up'), route('sign_up', ['email' => $role->email]))
                    ->line(__('messages.claim_email_line3'));
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
