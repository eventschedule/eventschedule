<?php

namespace App\Notifications;

use App\Models\Role;
use App\Models\Sale;
use App\Support\MailTemplateManager;
use App\Utils\NotificationUtils;
use App\Utils\UrlUtils;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketPaidNotification extends Notification
{
    use Queueable;

    protected Sale $sale;
    protected string $recipientType;
    protected ?Role $contextRole;

    public function __construct(Sale $sale, string $recipientType = 'organizer', ?Role $contextRole = null)
    {
        $this->sale = $sale;
        $this->recipientType = $recipientType;
        $this->contextRole = $contextRole;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $event = $this->sale->event;
        $eventName = NotificationUtils::eventDisplayName($event);
        $date = $event->localStartsAt(true, $this->sale->event_date);
        $amount = $this->sale->payment_amount ?? $this->sale->calculateTotal();
        $currency = $event->ticket_currency_code ?? 'USD';
        $formattedAmount = number_format((float) $amount, 2) . ' ' . $currency;
        $ticketViewUrl = route('ticket.view', [
            'event_id' => UrlUtils::encodeId($event->id),
            'secret' => $this->sale->secret,
        ]);

        $templates = app(MailTemplateManager::class);
        $templateKey = $this->recipientType === 'purchaser'
            ? 'ticket_paid_purchaser'
            : 'ticket_paid_organizer';

        $eventDate = $date ?: __('messages.date_to_be_announced');
        $buyerName = $this->sale->name ?: $this->sale->email;
        $buyerEmail = $this->sale->email ?? '';

        $data = [
            'event_name' => $eventName,
            'event_date' => $eventDate,
            'amount_total' => $formattedAmount,
            'buyer_name' => $buyerName,
            'buyer_email' => $buyerEmail,
            'event_url' => $event->getGuestUrl($this->sale->subdomain, $this->sale->event_date),
            'ticket_view_url' => $this->recipientType === 'purchaser'
                ? $ticketViewUrl
                : $event->getGuestUrl($this->sale->subdomain, $this->sale->event_date),
            'order_reference' => (string) $this->sale->id,
            'app_name' => config('app.name'),
        ];

        $subject = $templates->renderSubject($templateKey, $data);
        $body = $templates->renderBody($templateKey, $data);

        $mail = (new MailMessage())
            ->subject($subject)
            ->markdown('mail.templates.generic', [
                'body' => $body,
            ]);

        if ($event->user && $event->user->email) {
            $mail->replyTo($event->user->email, $event->user->name);
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }

    public function toMailHeaders(): array
    {
        $subdomain = $this->contextRole?->subdomain
            ?? $this->sale->subdomain
            ?? $this->sale->event->venue?->subdomain;

        if (! $subdomain) {
            return [];
        }

        return [
            'List-Unsubscribe' => '<' . route('role.unsubscribe', ['subdomain' => $subdomain]) . '>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ];
    }
}

