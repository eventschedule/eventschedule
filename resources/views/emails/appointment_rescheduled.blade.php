@php
    // The previous time, rendered in whichever zone the guest is being shown the new one in, so the
    // two lines are actually comparable. Derived from the scalar old start, never from the event -
    // the event already holds the NEW time by the time this renders.
    $apptUse24 = (bool) ($role->use_24_hour_time ?? false);
    $oldTz = $sale->guestTimezone() ?: \App\Utils\AppointmentTimeUtils::scheduleTimezone($event);
    // parseUtcInstant, not a bare createFromFormat: a legacy/restored date-only value would throw
    // here, killing the job so the guest is never told their appointment moved.
    $oldStart = \App\Utils\AppointmentTimeUtils::parseUtcInstant($oldStartsAt)?->setTimezone($oldTz);
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.appointment_rescheduled_heading') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ __('messages.appointment_rescheduled_heading') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $sale->name }},</p>

        <p>{{ $intro }}</p>

        @if (! empty($note))
        <div style="background-color: #fff; padding: 15px 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #9ca3af;">
            <p style="margin: 0 0 6px 0; font-size: 13px; color: #666; font-weight: bold;">{{ __('messages.organizer_note') }}</p>
            <p style="margin: 0; font-size: 15px; color: #333;">{!! nl2br(e($note)) !!}</p>
        </div>
        @endif

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <h2 style="margin-top: 0; color: #4E81FA;">{{ $type?->name ?? $event->name }}</h2>

            <p style="margin: 0 0 15px 0; font-size: 13px; color: #666; text-transform: uppercase; letter-spacing: 0.05em;">{{ __('messages.event_changed_whats_changed') }}</p>
            @if ($oldStart)
            <p style="margin: 0; font-size: 14px; color: #999;">{{ __('messages.event_changed_previously') }}: <s>{{ $oldStart->translatedFormat('l, F j, Y') }} {{ $oldStart->format($apptUse24 ? 'H:i' : 'g:i A') }} ({{ $oldTz }})</s></p>
            @endif
            <p style="margin: 6px 0 12px 0; font-size: 15px; color: #333;"><strong>{{ __('messages.event_changed_now') }}:</strong></p>

            @include('emails.partials.appointment_datetime')

            @if ($event->event_url)
                <p style="margin: 10px 0;"><strong>{{ __('messages.online') }}:</strong> <a href="{{ $event->event_url }}" style="color: #4E81FA;">{{ $event->event_url }}</a></p>
            @elseif ($type && $type->location_type === 'in_person' && $type->location_address)
                <p style="margin: 10px 0;"><strong>{{ __('messages.location') }}:</strong> {{ $type->location_address }}</p>
            @elseif ($type && $type->location_type === 'phone')
                <p style="margin: 10px 0;"><strong>{{ __('messages.phone') }}:</strong> {{ $type->location_phone ?: $sale->phone }}</p>
            @endif
        </div>

        {{-- iTIP handling still varies by client, so say the honest thing rather than assume the
             attached invite always replaces the old entry cleanly. --}}
        <p style="font-size: 13px; color: #888;">{{ __('messages.update_your_calendar_note') }}</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $manageUrl }}" style="background-color: #4E81FA; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;">{{ __('messages.appointments_manage_booking') }}</a>
        </div>

        <p style="font-size: 13px; color: #888;">{{ __('messages.appointments_manage_link_hint') }}<br>
            <a href="{{ $manageUrl }}" style="color: #4E81FA; word-break: break-all;">{{ $manageUrl }}</a>
        </p>
    </div>
</body>
</html>
