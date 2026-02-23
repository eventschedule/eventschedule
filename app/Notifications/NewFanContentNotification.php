<?php

namespace App\Notifications;

use App\Utils\UrlUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewFanContentNotification extends Notification
{
    use Queueable;

    protected $event;

    protected $fanContentCount;

    protected $subdomain;

    /**
     * Create a new notification instance.
     */
    public function __construct($event, $fanContentCount, $subdomain)
    {
        $this->event = $event;
        $this->fanContentCount = $fanContentCount;
        $this->subdomain = $subdomain;
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
        $subject = __('messages.new_fan_content_notification_subject', ['name' => $this->event->name, 'count' => $this->fanContentCount]);
        $actionUrl = route('event.edit', ['subdomain' => $this->subdomain, 'hash' => UrlUtils::encodeId($this->event->id)]).'#section-fan-content';
        $encodedEmail = base64_encode($notifiable->email);
        $unsubscribeUrl = route('user.unsubscribe', ['email' => $encodedEmail, 'sig' => \App\Utils\UrlUtils::signEmail($encodedEmail)]);

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.new_fan_content', [
                'event' => $this->event,
                'fanContentCount' => $this->fanContentCount,
                'actionUrl' => $actionUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
            ])
            ->text('emails.new_fan_content_text', [
                'event' => $this->event,
                'fanContentCount' => $this->fanContentCount,
                'actionUrl' => $actionUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
            ])
            ->withSymfonyMessage(function ($message) use ($unsubscribeUrl) {
                $message->getHeaders()->addTextHeader('List-Unsubscribe', '<'.$unsubscribeUrl.'>');
                $message->getHeaders()->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
            });
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
