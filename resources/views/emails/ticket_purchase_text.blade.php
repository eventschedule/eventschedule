@php
    // A gift-card-covered order is a purchase, not a free reservation
    $isFreeReservation = $sale->payment_amount == 0 && ($giftCardAmount ?? 0) == 0;
@endphp
{{ $isFreeReservation ? __('messages.ticket_reservation_confirmation') : __('messages.ticket_purchase_confirmation') }}

{{ __('messages.hello') }} {{ $sale->name }},

{{ $isFreeReservation ? __('messages.thank_you_for_reserving_tickets') : __('messages.thank_you_for_purchasing_tickets') }}

{{ $event->name }}

{{ __('messages.date') }}: {{ $event->is_multi_day ? $event->getDateRangeDisplay($sale->event_date) : $event->getStartDateTime($sale->event_date, true)->format('F j, Y') }}
{{ __('messages.time') }}: {{ $event->getStartEndTime($sale->event_date) }}
{{ __('messages.attendee') }}: {{ $sale->name }}
{{ __('messages.number_of_attendees') }}: {{ $sale->quantity() }}

@php
$regularTickets = $sale->saleTickets->filter(fn($st) => $st->ticket && !$st->ticket->is_addon);
$addonTickets = $sale->saleTickets->filter(fn($st) => $st->ticket && $st->ticket->is_addon);
@endphp
{{ __('messages.ticket_details') }}
@foreach ($regularTickets as $saleTicket)
{{ $saleTicket->ticket->type ?: __('messages.ticket') }} x {{ $saleTicket->quantity }}
@endforeach
@if ($addonTickets->count() > 0)

{{ __('messages.add_ons') }}
@foreach ($addonTickets as $saleTicket)
{{ $saleTicket->ticket->type ?: __('messages.add_on') }} x {{ $saleTicket->quantity }}
@if ($saleTicket->ticket->url)
{{ $saleTicket->ticket->url }}
@endif
@endforeach
@endif

@if (($giftCardAmount ?? 0) > 0 && $giftCard)
{{ __('messages.gift_card_applied_summary') }}: -{{ \App\Utils\MoneyUtils::format($giftCardAmount, $giftCard->currency_code) }}
{{ __('messages.gift_card_remaining_balance') }}: {{ \App\Utils\MoneyUtils::format($giftCard->remaining_amount, $giftCard->currency_code) }}

@endif
{{ __('messages.view_your_tickets') }}: {{ $ticketUrl }}

@php $ticketNotes = $event->parsedTicketNotesText($sale->event_date, $role); @endphp
@if ($ticketNotes && trim($ticketNotes) !== '')
{{ __('messages.important_information') }}:
{{ $ticketNotes }}
@endif

{{ __('messages.event_support_contact') }}: {{ $event->user->email }}
