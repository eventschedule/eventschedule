<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $heading }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ $heading }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $sale->name }},</p>

        <p>{{ $intro }}</p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <h2 style="margin-top: 0; color: #4E81FA;">{{ $type?->name ?? $event->name }}</h2>
            <p style="margin: 10px 0;"><strong>{{ __('messages.date') }}:</strong> {{ $event->getStartDateTime($sale->event_date, true, $event->timezone)->format('l, F j, Y') }}</p>
            <p style="margin: 10px 0;"><strong>{{ __('messages.time') }}:</strong> {{ $event->getStartEndTime($sale->event_date) }} ({{ $event->timezone }})</p>
        </div>

        @if ($rebookUrl)
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $rebookUrl }}" style="background-color: #4E81FA; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; display: inline-block; font-weight: bold;">{{ __('messages.appointments_book_again') }}</a>
            </div>
        @else
            <p style="font-size: 13px; color: #888;">{{ __('messages.appointments_manage_link_hint') }}<br>
                <a href="{{ $manageUrl }}" style="color: #4E81FA; word-break: break-all;">{{ $manageUrl }}</a>
            </p>
        @endif
    </div>
</body>
</html>
