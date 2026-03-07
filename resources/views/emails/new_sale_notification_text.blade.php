{{ __('messages.new_sale') }}

{{ __('messages.new_sale_notification_greeting', ['name' => $recipient?->name ?? __('messages.hello')]) }},

{{ $event->name }}
@if ($sale->event_date){{ $event->getStartDateTime($sale->event_date, true)->format('F j, Y') }} {{ $event->getStartEndTime($sale->event_date) }}

@endif
{{ __('messages.buyer') }}: {{ $sale->name }} ({{ $sale->email }})
@if ($sale->phone)
{{ __('messages.phone_number') }}: {{ $sale->phone }}
@endif
@if (!empty($groupedSales) && $groupedSales->count() > 0)

{{ __('messages.guests') }}:
@foreach ($groupedSales as $guestSale)
{{ $guestSale->name }} ({{ $guestSale->email }})@if ($guestSale->phone) - {{ $guestSale->phone }}@endif

@endforeach
@endif

@php
$allSaleTickets = $sale->saleTickets->toBase();
if (!empty($groupedSales) && $groupedSales->count() > 0) {
    foreach ($groupedSales as $gs) {
        $allSaleTickets = $allSaleTickets->merge($gs->saleTickets);
    }
}
$ticketSummary = $allSaleTickets->groupBy(fn($st) => $st->ticket->name)
    ->map(fn($group) => $group->sum('quantity'));
@endphp
@foreach ($ticketSummary as $ticketName => $qty)
{{ $ticketName }} x {{ $qty }}
@endforeach

{{ __('messages.total') }}: {{ $total }}
{{ __('messages.status') }}: {{ $paymentStatus }}

{{ __('messages.view_sales') }}: {{ $salesUrl }}

{{ __('messages.thank_you_for_using') }}
@if ($unsubscribeUrl)

{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
@endif
