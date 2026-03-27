{{ $sale->payment_amount == 0 ? __('messages.ticket_reservation_confirmation') : __('messages.ticket_purchase_confirmation') }}

{{ __('messages.hello') }} {{ $sale->name }},

{{ $sale->payment_amount == 0 ? __('messages.thank_you_for_reserving_tickets') : __('messages.thank_you_for_purchasing_tickets') }}

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

{{ __('messages.view_your_tickets') }}: {{ $ticketUrl }}

@if ($event->ticket_notes_html)
{{ __('messages.notes') }}:
{{ strip_tags($event->ticket_notes_html) }}
@endif

{{ __('messages.event_support_contact') }}: {{ $event->user->email }}
