@php
    $heading = match ($kind) {
        'pending' => __('messages.appointment_owner_pending_heading'),
        'cancelled' => __('messages.appointment_owner_cancelled_heading'),
        default => __('messages.appointment_owner_booked_heading'),
    };
@endphp
{{ $heading }}

{{ $type?->name ?? $event->name }}
{{ __('messages.name') }}: {{ $sale->name }}
{{ __('messages.email') }}: {{ $sale->email }}
@if ($sale->phone)
{{ __('messages.phone') }}: {{ $sale->phone }}
@endif
{{ __('messages.date') }}: {{ $event->getStartDateTime($sale->event_date, true, $event->timezone)->format('l, F j, Y') }} {{ $event->getStartEndTime($sale->event_date) }} ({{ $event->timezone }})
@if ((float) $sale->payment_amount > 0)
{{ __('messages.price') }}: {{ strtoupper($event->ticket_currency_code) }} {{ number_format((float) $sale->payment_amount, 2) }} - {{ $sale->status === 'paid' ? __('messages.paid') : __('messages.unpaid') }}
@endif
@if ($showRefund)

{{ __('messages.appointment_owner_refund_note', ['amount' => strtoupper($event->ticket_currency_code).' '.number_format((float) $sale->payment_amount, 2), 'reference' => $sale->transaction_reference ?: '-']) }}
@endif

{{ $bookingsUrl }}
