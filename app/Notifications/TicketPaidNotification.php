<?php

namespace App\Notifications;

use App\Models\Role;
use App\Models\Sale;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Utils\NotificationUtils;

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

        $mail = (new MailMessage)
            ->subject(__('messages.ticket_paid_subject', ['event' => $eventName]));

        if ($this->recipientType === 'purchaser') {
            $mail->line(__('messages.ticket_paid_line_purchaser', [
                'event' => $eventName,
                'date' => $date ?: __('messages.date_to_be_announced'),
                'amount' => $formattedAmount,
            ]));
        } else {
            $mail->line(__('messages.ticket_paid_line_organizer', [
                'buyer' => $this->sale->name ?: $this->sale->email,
                'event' => $eventName,
                'date' => $date ?: __('messages.date_to_be_announced'),
                'amount' => $formattedAmount,
            ]));
        }

        $mail->action(__('messages.view_event'), $event->getGuestUrl($this->sale->subdomain, $this->sale->event_date));

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

