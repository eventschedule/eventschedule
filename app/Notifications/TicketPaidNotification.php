<?php

namespace App\Notifications;

use App\Models\Role;
use App\Models\Sale;
use App\Support\MailConfigManager;
use App\Support\MailTemplateManager;
use App\Utils\NotificationUtils;
use App\Utils\UrlUtils;
use App\Services\Wallet\AppleWalletService;
use App\Services\Wallet\GoogleWalletService;
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
        MailConfigManager::applyFromDatabase();

        if (config('mail.disable_delivery')) {
            return [];
        }

        $templates = app(MailTemplateManager::class);

        return $templates->enabled($this->templateKey()) ? ['mail'] : [];
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
        $templateKey = $this->templateKey();

        $eventDate = $date ?: __('messages.date_to_be_announced');
        $buyerName = $this->sale->name ?: $this->sale->email;
        $buyerEmail = $this->sale->email ?? '';

        $eventGuestUrl = $event->getGuestUrl($this->sale->subdomain);
        $eventTicketUrl = UrlUtils::appendQueryParameters($eventGuestUrl, ['tickets' => 'true']);

        $data = [
            'event_name' => $eventName,
            'event_date' => $eventDate,
            'amount_total' => $formattedAmount,
            'buyer_name' => $buyerName,
            'buyer_email' => $buyerEmail,
            'event_url' => $eventGuestUrl,
            'ticket_view_url' => $this->recipientType === 'purchaser'
                ? $ticketViewUrl
                : $eventTicketUrl,
            'order_reference' => (string) $this->sale->id,
            'app_name' => config('app.name'),
            'wallet_links_markdown' => $this->walletLinksMarkdown(),
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

    protected function walletLinksMarkdown(): string
    {
        $links = [];
        $sale = $this->sale->loadMissing('event');

        $appleWallet = app(AppleWalletService::class);

        if ($appleWallet->isAvailableForSale($sale)) {
            $links[] = '[' . __('messages.add_to_apple_wallet') . '](' . route('ticket.wallet.apple', [
                'event_id' => UrlUtils::encodeId($sale->event_id),
                'secret' => $sale->secret,
            ]) . ')';
        }

        $googleWallet = app(GoogleWalletService::class);

        if ($googleWallet->isAvailableForSale($sale)) {
            $links[] = '[' . __('messages.save_to_google_wallet') . '](' . route('ticket.wallet.google', [
                'event_id' => UrlUtils::encodeId($sale->event_id),
                'secret' => $sale->secret,
            ]) . ')';
        }

        return implode("\n\n", $links);
    }

    protected function templateKey(): string
    {
        return $this->recipientType === 'purchaser'
            ? 'ticket_paid_purchaser'
            : 'ticket_paid_organizer';
    }
}

