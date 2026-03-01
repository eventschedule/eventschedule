<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.feedback_notification_subject', ['event' => $event->name]) }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px; font-weight: 600;">{{ __('messages.new_feedback_received') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $recipient?->name }},</p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <p style="margin: 0 0 10px 0; font-size: 18px; color: #333;">
                <strong>{{ $event->name }}</strong>
            </p>
            @if ($feedback->event_date)
            <p style="margin: 0 0 15px 0; font-size: 14px; color: #666;">
                {{ $event->getStartDateTime($feedback->event_date, true)->translatedFormat('F j, Y') }}
            </p>
            @endif

            <hr style="border: none; border-top: 1px solid #eee; margin: 15px 0;">

            <p style="margin: 0 0 5px 0; font-size: 14px; color: #666;">
                <strong>{{ __('messages.attendee') }}:</strong> {{ $sale->name }} ({{ $sale->email }})
            </p>

            <hr style="border: none; border-top: 1px solid #eee; margin: 15px 0;">

            <p style="margin: 0 0 10px 0; font-size: 14px; color: #333;">
                <strong>{{ __('messages.rating') }}:</strong>
                @for ($i = 1; $i <= 5; $i++)
                    @if ($i <= $feedback->rating)
                        &#9733;
                    @else
                        &#9734;
                    @endif
                @endfor
                ({{ $feedback->rating }}/5)
            </p>

            @if ($feedback->comment)
            <p style="margin: 0; font-size: 14px; color: #333;">
                <strong>{{ __('messages.comment') }}:</strong><br>
                {{ $feedback->comment }}
            </p>
            @endif
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $salesUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ __('messages.view_feedback') }}
            </a>
        </div>

        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            {{ __('messages.thank_you_for_using') }}
        </p>
    </div>
</body>
</html>
