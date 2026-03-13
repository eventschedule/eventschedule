<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: {{ $role->accent_color ?? '#4E81FA' }}; color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px; font-weight: 600;">
            @if ($type === 'carpool_ride_requested')
                {{ __('messages.carpool_email_ride_requested_heading') }}
            @elseif ($type === 'carpool_request_approved')
                {{ __('messages.carpool_email_request_approved_heading') }}
            @elseif ($type === 'carpool_request_declined')
                {{ __('messages.carpool_email_request_declined_heading') }}
            @elseif ($type === 'carpool_offer_cancelled')
                {{ __('messages.carpool_email_offer_cancelled_heading') }}
            @elseif ($type === 'carpool_request_cancelled')
                {{ __('messages.carpool_email_request_cancelled_heading') }}
            @elseif ($type === 'carpool_reminder')
                {{ __('messages.carpool_email_reminder_heading') }}
            @endif
        </h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $recipient?->name }},</p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid {{ $role->accent_color ?? '#4E81FA' }};">
            <p style="margin: 0 0 10px 0; font-size: 18px; color: #333;">
                <strong>{{ $event->name }}</strong>
            </p>

            @if ($offer->event_date)
            <p style="margin: 0 0 10px 0; font-size: 14px; color: #666;">
                {{ $event->getStartDateTime($offer->event_date, true)?->translatedFormat('F j, Y') }}
            </p>
            @endif

            <p style="margin: 0 0 5px 0; font-size: 14px; color: #666;">
                <strong>{{ __('messages.carpool_direction') }}:</strong> {{ $offer->directionLabel() }}
            </p>

            <p style="margin: 0 0 5px 0; font-size: 14px; color: #666;">
                <strong>{{ __('messages.carpool_city') }}:</strong> {{ $offer->city }}
            </p>

            @if ($offer->departure_time)
            <p style="margin: 0 0 5px 0; font-size: 14px; color: #666;">
                <strong>{{ __('messages.carpool_departure_time') }}:</strong> {{ \Carbon\Carbon::parse($offer->departure_time)->format('H:i') }}
            </p>
            @endif

            @if ($offer->meeting_point)
            <p style="margin: 0 0 5px 0; font-size: 14px; color: #666;">
                <strong>{{ __('messages.carpool_meeting_point') }}:</strong> {{ $offer->meeting_point }}
            </p>
            @endif
        </div>

        @if ($type === 'carpool_ride_requested' && $carpoolRequest)
        <p style="font-size: 14px; color: #333;">
            {{ __('messages.carpool_email_ride_requested_body', ['name' => $carpoolRequest->user->name]) }}
        </p>
        @if ($carpoolRequest->message)
        <div style="background-color: white; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #eee;">
            <p style="margin: 0; font-size: 14px; color: #666; font-style: italic;">{{ $carpoolRequest->message }}</p>
        </div>
        @endif
        @endif

        @if ($type === 'carpool_request_approved')
        <p style="font-size: 14px; color: #333;">
            {{ __('messages.carpool_email_request_approved_body') }}
        </p>
        <div style="background-color: white; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #10b981;">
            <p style="margin: 0 0 5px 0; font-size: 14px;">
                <strong>{{ __('messages.carpool_driver') }}:</strong> {{ $offer->user->name }}
            </p>
            <p style="margin: 0 0 5px 0; font-size: 14px;">
                <strong>{{ __('messages.email') }}:</strong> {{ $offer->user->email }}
            </p>
            @if ($offer->user->phone)
            <p style="margin: 0; font-size: 14px;">
                <strong>{{ __('messages.phone') }}:</strong> {{ $offer->user->phone }}
            </p>
            @endif
        </div>
        @endif

        @if ($type === 'carpool_request_declined')
        <p style="font-size: 14px; color: #333;">
            {{ __('messages.carpool_email_request_declined_body') }}
        </p>
        @endif

        @if ($type === 'carpool_offer_cancelled')
        <p style="font-size: 14px; color: #333;">
            {{ __('messages.carpool_email_offer_cancelled_body', ['name' => $offer->user->name]) }}
        </p>
        @endif

        @if ($type === 'carpool_request_cancelled')
        <p style="font-size: 14px; color: #333;">
            {{ __('messages.carpool_email_request_cancelled_body', ['name' => $carpoolRequest->user->name]) }}
        </p>
        @endif

        @if ($type === 'carpool_reminder')
        <p style="font-size: 14px; color: #333;">
            {{ __('messages.carpool_email_reminder_body') }}
        </p>
        @if ($carpoolRequest && $carpoolRequest->status === 'approved' && $recipient?->id !== $offer->user_id)
        <div style="background-color: white; padding: 15px; border-radius: 8px; margin: 10px 0; border-left: 4px solid #10b981;">
            <p style="margin: 0 0 5px 0; font-size: 14px;">
                <strong>{{ __('messages.carpool_driver') }}:</strong> {{ $offer->user->name }}
            </p>
            <p style="margin: 0 0 5px 0; font-size: 14px;">
                <strong>{{ __('messages.email') }}:</strong> {{ $offer->user->email }}
            </p>
            @if ($offer->user->phone)
            <p style="margin: 0; font-size: 14px;">
                <strong>{{ __('messages.phone') }}:</strong> {{ $offer->user->phone }}
            </p>
            @endif
        </div>
        @endif
        @endif

        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            {{ __('messages.thank_you_for_using') }}
            <br><br>
            <a href="{{ $unsubscribeUrl }}" style="color: {{ $role->accent_color ?? '#4E81FA' }};">{{ __('messages.unsubscribe') }}</a>
        </p>
    </div>
</body>
</html>
