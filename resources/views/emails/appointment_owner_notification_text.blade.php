@php
    $heading = match ($kind) {
        'pending' => __('messages.appointment_owner_pending_heading'),
        'cancelled' => __('messages.appointment_owner_cancelled_heading'),
        'rescheduled', 'rescheduled_pending' => __('messages.appointment_owner_rescheduled_heading'),
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
@php
    // Schedule zone primary (the owner's clock), guest zone appended. See the HTML twin.
    $ownerUse24 = (bool) ($role->use_24_hour_time ?? false);
    $ownerScheduleTz = \App\Utils\AppointmentTimeUtils::scheduleTimezone($event);
    $ownerShown = \App\Utils\AppointmentTimeUtils::render($event, $ownerScheduleTz, $ownerUse24);
    $ownerGuestTz = $sale->guestTimezone();
    $ownerGuestShown = ($ownerGuestTz && $ownerGuestTz !== $ownerScheduleTz)
        ? \App\Utils\AppointmentTimeUtils::render($event, $ownerGuestTz, $ownerUse24)
        : null;
@endphp
{{ __('messages.date') }}: {{ $ownerShown['date'] }} {{ $ownerShown['time'] }} ({{ $ownerShown['tz'] }})
@if ($ownerGuestShown)
{{ __('messages.appointments_times_shown_in') }} {{ $ownerGuestShown['tz'] }}: {{ $ownerGuestShown['time'] }}
@endif
@if ((float) $sale->payment_amount > 0)
{{ __('messages.price') }}: {{ strtoupper($event->ticket_currency_code) }} {{ number_format((float) $sale->payment_amount, 2) }} - {{ ($paidLabel ?? ($sale->status === 'paid')) ? __('messages.paid') : __('messages.unpaid') }}
@endif
@if (! empty($shortNotice))

{{ $shortNotice }}
@endif
@if ($showRefund)

{{ __('messages.appointment_owner_refund_note', ['amount' => strtoupper($event->ticket_currency_code).' '.number_format((float) $sale->payment_amount, 2), 'reference' => $sale->transaction_reference ?: '-']) }}
@endif

{{ $bookingsUrl }}
