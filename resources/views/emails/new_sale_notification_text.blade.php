{{ __('messages.new_sale') }}

{{ __('messages.new_sale_notification_greeting', ['name' => $recipient?->name ?? __('messages.hello')]) }},

{{ $event->name }}
@if ($sale->event_date){{ $event->getStartDateTime($sale->event_date, true)->format('F j, Y') }} {{ $event->getStartEndTime($sale->event_date) }}

@endif
{{ __('messages.buyer') }}: {{ $sale->name }} ({{ $sale->email }})

@foreach ($sale->saleTickets as $saleTicket)
{{ $saleTicket->ticket->name }} x {{ $saleTicket->quantity }}
@endforeach

{{ __('messages.total') }}: {{ $total }}
{{ __('messages.status') }}: {{ $paymentStatus }}

{{ __('messages.view_sales') }}: {{ $salesUrl }}

{{ __('messages.thank_you_for_using') }}
@if ($unsubscribeUrl)

{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
@endif
