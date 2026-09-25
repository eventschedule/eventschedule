<?php

namespace App\Notifications;

use App\Services\NotificationEmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRequestsNotification extends Notification
{
    use Queueable;

    protected $role;

    protected $requestCount;

    /**
     * Create a new notification instance.
     */
    public function __construct($role, $requestCount)
    {
        $this->role = $role;
        $this->requestCount = $requestCount;
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
        $subject = __('messages.new_requests_notification_subject', ['name' => $this->role->name, 'count' => $this->requestCount]);
        $actionUrl = route('role.view_admin', ['subdomain' => $this->role->subdomain, 'tab' => 'requests']);
        $unsubscribeUrl = route('role.unsubscribe', ['subdomain' => $this->role->subdomain]);

        // The copy for the schedule's shared notification address (routed on demand, so the
        // notifiable is anonymous) unsubscribes by removing that address; nobody there has an
        // account for the generic route to act on.
        $notificationEmailUnsubscribeUrl = null;
        if ($notifiable instanceof AnonymousNotifiable) {
            $notificationEmailUnsubscribeUrl = NotificationEmailService::unsubscribeUrl($this->role);
            $unsubscribeUrl = $notificationEmailUnsubscribeUrl;
        }

        return (new MailMessage)
            ->subject($subject)
            ->view('emails.new_requests', [
                'role' => $this->role,
                'requestCount' => $this->requestCount,
                'actionUrl' => $actionUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
                'notificationEmailUnsubscribeUrl' => $notificationEmailUnsubscribeUrl,
            ])
            ->text('emails.new_requests_text', [
                'role' => $this->role,
                'requestCount' => $this->requestCount,
                'actionUrl' => $actionUrl,
                'unsubscribeUrl' => $unsubscribeUrl,
                'notificationEmailUnsubscribeUrl' => $notificationEmailUnsubscribeUrl,
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
