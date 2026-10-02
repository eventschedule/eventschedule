@php
    $theme = \App\Utils\EmailTheme::guest($role ?? null);

    // A gift-card-covered order is a purchase, not a free reservation
    $isFreeReservation = $sale->payment_amount == 0 && ($giftCardAmount ?? 0) == 0;
    $thanks = $sale->isRsvp() ? __('messages.registration_confirmation') : ($isFreeReservation ? __('messages.thank_you_for_reserving_tickets') : __('messages.thank_you_for_purchasing_tickets'));
    $eyebrow = $sale->isRsvp() ? __('messages.registration_confirmation') : ($isFreeReservation ? __('messages.ticket_reservation_confirmation') : __('messages.ticket_purchase_confirmation'));

    $start = $event->starts_at ? $event->getStartDateTime($sale->event_date, true) : null;
    $when = $start ? ($event->is_multi_day ? $event->getDateRangeDisplay($sale->event_date) : $start->translatedFormat('l, F j')) : null;
    $currency = $event->ticket_currency_code ?: 'USD';

    $passTerms = function ($ticket) {
        if (! $ticket->is_pass) {
            return null;
        }
        if ($ticket->pass_usage_type === 'per_occurrence') {
            return __('messages.season_pass').' · '.__('messages.pass_valid_all_dates');
        }
        $terms = __('messages.subscription');
        if ($ticket->pass_usage_type === 'total' && $ticket->pass_max_uses) {
            $terms .= ' · '.$ticket->pass_max_uses.' '.__('messages.visits');
        } elseif ($ticket->pass_usage_type === 'unlimited') {
            $terms .= ' · '.__('messages.pass_unlimited_visits');
        }

        return $terms;
    };

    $ticketRows = $sale->saleTickets->filter(fn ($st) => $st->ticket && ! $st->ticket->is_addon)->map(fn ($st) => [
        'name' => $st->ticket->type ?: __('messages.ticket'),
        'caption' => implode(' · ', array_filter([implode(' · ', $st->seatLabels()), $passTerms($st->ticket)])),
        'value' => '× '.$st->quantity,
    ])->values()->all();
    $addonRows = $sale->saleTickets->filter(fn ($st) => $st->ticket && $st->ticket->is_addon)->map(fn ($st) => [
        'name' => $st->ticket->type ?: __('messages.add_on'),
        'url' => $st->ticket->url,
        'value' => '× '.$st->quantity,
    ])->values()->all();

    // Payment plan. This is the buyer's FIRST email after paying, so without it the message
    // reads "thank you for your purchase" and says nothing about the further charges coming to
    // their card.
    $plan = $sale->installmentPlan;
    $hasPlan = $plan && $plan->status !== 'cancelled';

    // What the order came to, so the confirmation doubles as a receipt. Only on a plain single-event
    // order: on a group or multi-event primary, payment_amount is deliberately the per-seat figure
    // (see Sale::legTotalPayment()) and the gift-card lines below cover the whole group, so a
    // total there would not add up. Left to the payment plan when there is one, whose table
    // already carries it.
    $showTotal = ! $sale->isRsvp() && ! $isFreeReservation && ! $hasPlan && ! $sale->group_id && ! $sale->order_id;

    // A gift card's deduction sits directly above the total it explains, as at checkout, so
    // "-$30" then "Total $70" reads as what was charged. Printed after the total, it read as a
    // second deduction from it. The card's remaining balance stays in its own section below.
    $hasGiftCard = ($giftCardAmount ?? 0) > 0 && $giftCard;
    $giftRow = $hasGiftCard && $showTotal
        ? ['name' => __('messages.gift_card_applied_summary'), 'value' => '-'.\App\Utils\MoneyUtils::format($giftCardAmount, $giftCard->currency_code)]
        : null;
    if ($giftRow && $addonRows) {
        $addonRows[] = $giftRow;
    } elseif ($giftRow) {
        $ticketRows[] = $giftRow;
    }
@endphp
<x-email.layout :theme="$theme" :title="$eyebrow" :preheader="$event->name.($when ? ' · '.$when : '')">
<x-slot:hero>
<x-email.flyer :event="$event" />
</x-slot:hero>

<x-email.heading :eyebrow="$eyebrow" auto>{{ $event->name }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $sale->name }},</x-email.text>
<x-email.text>{{ $thanks }}</x-email.text>

<x-email.event :event="$event" :date="$sale->event_date" :role="$role ?? null" />

<x-email.details>
<x-email.item :label="__('messages.attendee')">{{ $sale->name }}</x-email.item>
@if (! $sale->isRsvp())
<x-email.item :label="__('messages.number_of_attendees')">{{ $sale->quantity() }}</x-email.item>
@endif
</x-email.details>

<x-email.button :href="$ticketUrl">@if ($sale->isPass()){{ __('messages.manage_my_pass') }}@elseif ($sale->isRsvp()){{ __('messages.view_registration') }}@else{{ __('messages.view_your_tickets') }}@endif</x-email.button>

@if ($googleWalletUrl && $googleWalletBadge && is_file($googleWalletBadge))
    @php($badgeSize = getimagesize($googleWalletBadge) ?: [283, 50])
    {{-- embedData, not a hotlink: this app never asks a recipient's mail client to fetch an
         asset from someone else's server. Google's badge may not be recoloured or rebuilt, so
         it ships as their own artwork under public/images/wallet/google. The <img> stays split
         over several lines: Mailer::render() swaps a one-line cid: image for a data: URI, and
         GoogleWalletPassTest asserts the cid: survives. --}}
    <x-email.text align="center" :gap="24">
        <a href="{{ $googleWalletUrl }}" target="_blank" rel="noopener" style="text-decoration: none;">
            <img src="{{ $message->embedData(file_get_contents($googleWalletBadge), 'add-to-google-wallet.png', 'image/png') }}"
                 alt="{{ __('messages.add_to_google_wallet') }}"
                 width="{{ (int) round($badgeSize[0] * 50 / max(1, $badgeSize[1])) }}" height="50"
                 style="display: inline-block; height: 50px; width: auto; border: 0;" />
        </a>
    </x-email.text>
@endif

@if ($ticketRows)
<x-email.section :label="__('messages.ticket_details')" />
<x-email.items :rows="$ticketRows" :total="$showTotal && ! $addonRows ? \App\Utils\MoneyUtils::format($sale->payment_amount, $currency) : null" :total-label="__('messages.total')" />
@endif

@if ($addonRows)
<x-email.section :label="__('messages.add_ons')" />
<x-email.items :rows="$addonRows" :total="$showTotal ? \App\Utils\MoneyUtils::format($sale->payment_amount, $currency) : null" :total-label="__('messages.total')" />
@endif

@if ($hasPlan)
<x-email.section :label="__('messages.payment_plan')" />
<x-email.table :align="['start', 'end', 'end']" :rows="$plan->installments->map(fn ($row) => [
    $row->due_at?->translatedFormat('j M Y'),
    \App\Utils\MoneyUtils::format($row->amount, $plan->currency),
    $row->status === 'paid' ? [__('messages.paid'), 'success'] : __('messages.scheduled'),
])->all()" />
<x-email.text variant="small"><strong>{{ __('messages.total') }} {{ \App\Utils\MoneyUtils::format($plan->total_amount, $plan->currency) }}.</strong> {{ __('messages.installments_no_interest_short') }}</x-email.text>
@if (! empty($plan->id))
<x-email.text variant="small"><x-email.link :href="route('installment.view', ['plan_id' => \App\Utils\UrlUtils::encodeId($plan->id), 'secret' => $plan->secret])">{{ __('messages.payment_plan') }}</x-email.link></x-email.text>
@endif
@endif

@if ($hasGiftCard)
{{-- The ticket buyer may not be the card recipient, so do NOT link the secret-authed card
     view page here (it exposes the purchaser's name/message). Show amounts only. --}}
<x-email.section :label="__('messages.gift_card')" />
<x-email.items :rows="array_values(array_filter([
    $giftRow ? null : ['name' => __('messages.gift_card_applied_summary'), 'value' => '-'.\App\Utils\MoneyUtils::format($giftCardAmount, $giftCard->currency_code)],
    ['name' => __('messages.gift_card_remaining_balance'), 'value' => \App\Utils\MoneyUtils::format($giftCard->remaining_amount, $giftCard->currency_code)],
]))" />
@endif

@php($ticketNotes = $event->parsedTicketNotesHtml($sale->event_date, $role))
@if ($ticketNotes && trim(strip_tags($ticketNotes)) !== '')
<x-email.section :label="__('messages.important_information')" />
<x-email.prose>{!! \App\Utils\UrlUtils::convertUrlsToLinks($ticketNotes) !!}</x-email.prose>
@endif

@if ($event->user?->email)
<x-slot:footer>
<x-email.footer>{{ __('messages.event_support_contact') }}: <x-email.link :href="'mailto:'.$event->user->email" muted>{{ $event->user->email }}</x-email.link></x-email.footer>
</x-slot:footer>
@endif
</x-email.layout>
