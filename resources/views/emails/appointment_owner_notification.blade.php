<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $heading = match ($kind) {
            'pending' => __('messages.appointment_owner_pending_heading'),
            'cancelled' => __('messages.appointment_owner_cancelled_heading'),
            default => __('messages.appointment_owner_booked_heading'),
        };
    @endphp
    <title>{{ $heading }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ $heading }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 0 0 20px; border-left: 4px solid #4E81FA;">
            <h2 style="margin-top: 0; color: #4E81FA;">{{ $type?->name ?? $event->name }}</h2>
            <p style="margin: 8px 0;"><strong>{{ __('messages.name') }}:</strong> {{ $sale->name }}</p>
            <p style="margin: 8px 0;"><strong>{{ __('messages.email') }}:</strong> {{ $sale->email }}</p>
            @if ($sale->phone)
                <p style="margin: 8px 0;"><strong>{{ __('messages.phone') }}:</strong> {{ $sale->phone }}</p>
            @endif
            <p style="margin: 8px 0;"><strong>{{ __('messages.date') }}:</strong> {{ $event->getStartDateTime($sale->event_date, true, $event->timezone)->format('l, F j, Y') }} {{ $event->getStartEndTime($sale->event_date) }} ({{ $event->timezone }})</p>
            @if ((float) $sale->payment_amount > 0)
                <p style="margin: 8px 0;"><strong>{{ __('messages.price') }}:</strong> {{ strtoupper($event->ticket_currency_code) }} {{ number_format((float) $sale->payment_amount, 2) }} &middot; {{ $sale->status === 'paid' ? __('messages.paid') : __('messages.unpaid') }}</p>
            @endif
            @if ($event->description)
                <p style="margin: 8px 0;">{{ $event->description }}</p>
            @endif
        </div>

        @if ($showRefund)
            <div style="background-color: #fef3c7; border: 1px solid #fcd34d; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-size: 14px;">
                {{ __('messages.appointment_owner_refund_note', ['amount' => strtoupper($event->ticket_currency_code).' '.number_format((float) $sale->payment_amount, 2), 'reference' => $sale->transaction_reference ?: '-']) }}
            </div>
        @endif

        <div style="text-align: center; margin: 20px 0;">
            <a href="{{ $bookingsUrl }}" style="background-color: #4E81FA; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;">{{ $kind === 'pending' ? __('messages.appointment_owner_review') : __('messages.view') }}</a>
        </div>
    </div>
</body>
</html>
