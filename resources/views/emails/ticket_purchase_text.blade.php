{{ $sale->calculateTotal() == 0 ? __('messages.ticket_reservation_confirmation') : __('messages.ticket_purchase_confirmation') }}

{{ __('messages.hello') }} {{ $sale->name }},

{{ $sale->calculateTotal() == 0 ? __('messages.thank_you_for_reserving_tickets') : __('messages.thank_you_for_purchasing_tickets') }}

{{ $event->name }}

{{ __('messages.date') }}: {{ $event->getStartDateTime($sale->event_date, true)->format('F j, Y') }}
{{ __('messages.time') }}: {{ $event->getStartEndTime($sale->event_date) }}
{{ __('messages.attendee') }}: {{ $sale->name }}
{{ __('messages.number_of_attendees') }}: {{ $sale->quantity() }}

{{ __('messages.ticket_details') }}
@foreach ($sale->saleTickets as $saleTicket)
{{ $saleTicket->ticket->type ?: __('messages.ticket') }} x {{ $saleTicket->quantity }}
@endforeach

{{ __('messages.view_your_tickets') }}: {{ $ticketUrl }}

@if ($event->ticket_notes_html)
{{ __('messages.notes') }}:
{{ strip_tags($event->ticket_notes_html) }}
@endif

{{ __('messages.event_support_contact') }}: {{ $event->user->email }}
