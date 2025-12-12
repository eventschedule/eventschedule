<?php

namespace App\Notifications;

use App\Models\Event;
use App\Models\Role;
use App\Models\User;
use App\Support\MailConfigManager;
use App\Support\MailTemplateManager;
use App\Utils\NotificationUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DeletedEventNotification extends Notification
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

        $templates = app(MailTemplateManager::class);

        return $templates->enabled($this->templateKey()) ? ['mail'] : [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $eventName = NotificationUtils::eventDisplayName($this->event);
        $talentName = optional($this->event->role())->getDisplayName();
        $venueName = $this->event->getVenueDisplayName();
        $date = $this->event->localStartsAt(true);

        $templates = app(MailTemplateManager::class);
        $templateKey = $this->templateKey();

        $data = [
            'event_name' => $eventName,
            'venue_name' => $venueName ?: __('messages.event'),
            'event_date' => $date ?: __('messages.date_to_be_announced'),
            'actor_name' => $this->actor?->name ?? config('app.name'),
            'event_url' => $this->event->getGuestUrl($this->contextRole?->subdomain ?? $this->event->venue?->subdomain),
            'app_name' => config('app.name'),
        ];

        $subject = $templates->renderSubject($templateKey, $data);
        $body = $templates->renderBody($templateKey, $data);

        $mail = (new MailMessage())
            ->subject($subject)
            ->markdown('mail.templates.generic', [
                'body' => $body,
            ]);

        if ($this->actor && $this->actor->email) {
            $mail->replyTo($this->actor->email, $this->actor->name);
        }

        return $mail;
    }

    protected function templateKey(): string
    {
        return match ($this->recipientType) {
            'purchaser' => 'event_deleted_purchaser',
            'organizer' => 'event_deleted_organizer',
            default => 'event_deleted_talent',
        };
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

