<?php

namespace App\Mail;

use App\Mail\Concerns\RepliesToEventSupport;
use App\Models\Event;
use App\Models\Role;
use App\Models\Sale;
use App\Services\Wallet\GoogleWalletService;
use App\Utils\UrlUtils;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * "{Event} is tomorrow at 8:00 PM": the reminder app:send-ticket-reminders sends to a ticket holder
 * or registered guest before their occurrence. The guest voice, like the confirmation it follows, and
 * the ticket's QR code in the body, so the newest mail in the inbox is the ticket at the door.
 *
 * Times are always absolute ("Tomorrow at 8:00 PM", never "in 3 hours"): mail is read late, and the
 * hourly send already blurs any relative figure.
 */
class TicketReminder extends Mailable
{
    use Queueable, RepliesToEventSupport, SerializesModels;

    /**
     * @param  bool  $preview  The owner's preview (Tickets > Reminders): the same message, nothing for a real ticket.
     */
    public function __construct(
        protected Sale $sale,
        protected Event $event,
        protected ?Role $role = null,
        protected bool $preview = false,
    ) {}

    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = config('mail.from.name');

        if ($this->role && $this->role->hasEmailSettings()) {
            $settings = $this->role->getEmailSettings();
            $fromAddress = ! empty($settings['from_address']) ? $settings['from_address'] : $fromAddress;
            $fromName = ! empty($settings['from_name']) ? $settings['from_name'] : $fromName;
        }

        return new Envelope(
            subject: $this->timing()['subject'],
            from: new Address($fromAddress, $fromName),
            replyTo: $this->eventSupportReplyTo($this->event->user?->email),
        );
    }

    public function content(): Content
    {
        $when = $this->timing();

        $googleWalletUrl = ! $this->preview && GoogleWalletService::canOffer($this->sale, $this->event)
            ? canonical_url(route('ticket.wallet.google', [
                'event_id' => UrlUtils::encodeId($this->event->id),
                'secret' => $this->sale->secret,
            ], false))
            : null;

        $appleWalletUrl = ! $this->preview && \App\Services\Wallet\AppleWalletService::canOffer($this->sale, $this->event)
            ? canonical_url(route('ticket.wallet.apple', [
                'event_id' => UrlUtils::encodeId($this->event->id),
                'secret' => $this->sale->secret,
            ], false))
            : null;
        $appleWalletBadge = $appleWalletUrl && ($badge = \App\Services\Wallet\AppleWalletService::badge('png'))
            ? public_path($badge)
            : null;

        return new Content(
            view: 'emails.ticket_reminder',
            text: 'emails.ticket_reminder_text',
            with: [
                'sale' => $this->sale,
                // In the language the guest bought in, as their confirmation was.
                'event' => $this->sale->eventAsRead($this->event),
                'lang' => $this->sale->linkLanguage($this->role, $this->event),
                'role' => $this->role,
                'ticketUrl' => $this->ticketUrl(),
                'eyebrow' => $when['eyebrow'],
                'headline' => $when['line'],
                'canCancel' => $this->canCancel(),
                'googleWalletUrl' => $googleWalletUrl,
                'googleWalletBadge' => $googleWalletUrl
                    ? public_path('images/wallet/google/'.GoogleWalletService::badgeLocale().'.png')
                    : null,
                'appleWalletUrl' => $appleWalletUrl,
                'appleWalletBadge' => $appleWalletBadge,
            ],
        );
    }

    private function ticketUrl(): string
    {
        return Sale::withLanguage(canonical_url(route('ticket.view', [
            'event_id' => UrlUtils::encodeId($this->event->id),
            'secret' => $this->sale->secret ?: 'preview',
        ], false)), $this->sale->linkLanguage($this->role, $this->event));
    }

    /**
     * A free place the guest can give up themselves, on the ticket page (ticket/view.blade.php decides
     * the same way). The reminder points there: "can't make it?" frees the seat for somebody else.
     */
    private function canCancel(): bool
    {
        $sale = $this->sale;

        return ! $this->preview && $sale->status === 'paid' && ! $this->event->is_cancelled
            && ($sale->isRsvp() || ($sale->payment_amount == 0 && $sale->groupTotalGiftCard() == 0))
            && (! $sale->group_id || $sale->isPrimarySale());
    }

    /**
     * The subject, the eyebrow and the sentence that say when, relative to today in the schedule's
     * own zone: "today", "tomorrow", or the weekday and date.
     *
     * @return array{subject: string, eyebrow: string, line: string}
     */
    public function timing(): array
    {
        $tz = $this->event->scheduleTimezone();
        $start = $this->event->starts_at
            ? $this->event->occurrenceStartUtc((string) $this->sale->event_date)->setTimezone($tz)
            : Carbon::now($tz)->addDay();

        $today = Carbon::now($tz)->startOfDay();
        $days = (int) $today->diffInDays($start->copy()->startOfDay(), false);
        // The schedule's clock, as the event block in the body prints it (components/email/event).
        $time = $start->format(($this->role ?? $this->event->creatorRole)?->use_24_hour_time ? 'H:i' : 'g:i A');
        // In the reader's own word order: "quinta-feira, 19 de novembro", not "quinta, novembro 19".
        $date = $start->localDate(false);
        $name = $this->sale->eventAsRead($this->event)->name;

        [$key, $eyebrow] = match (true) {
            $days <= 0 => ['ticket_reminder_today', __('messages.today')],
            $days === 1 => ['ticket_reminder_tomorrow', __('messages.tomorrow')],
            default => ['ticket_reminder_on', $start->translatedFormat('l')],
        };

        $line = __('messages.'.$key, ['event' => $name, 'time' => $time, 'date' => $date]);

        // The subject is plain text with no markup to carry direction, so in a right-to-left
        // language its name, date and clock are isolated by hand (first-strong and left-to-right
        // isolates), or a Latin name beside "8:00 PM" turns around in a Hebrew or Arabic inbox. Only
        // there: invisible characters in a subject draw as boxes in some older clients and catch a
        // spam filter's eye. The body's components isolate on their own.
        $subject = ! is_rtl() ? $line : __('messages.'.$key, [
            'event' => "\u{2068}".$name."\u{2069}",
            'time' => "\u{2066}".$time."\u{2069}",
            'date' => "\u{2068}".$date."\u{2069}",
        ]);

        return ['subject' => $subject, 'eyebrow' => $eyebrow, 'line' => $line];
    }
}
