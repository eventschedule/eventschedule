<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.pass_booking_confirmation') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ __('messages.pass_booking_confirmation') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $sale->name }},</p>

        <p>{{ __('messages.pass_booking_confirmation_intro') }}</p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <h2 style="margin-top: 0; color: #4E81FA;">{{ $bookedEvent->name }}</h2>
            <p style="margin: 10px 0;"><strong>{{ __('messages.date') }}:</strong> {{ $dateLabel }}</p>
            <p style="margin: 10px 0;"><strong>{{ __('messages.attendee') }}:</strong> {{ $sale->name }}</p>
        </div>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center;">
            <div style="display: inline-block; padding: 15px; background-color: #f9f9f9; border-radius: 8px;">
                <img src="{{ $message->embedData($qrCodeData, 'pass-qr-code.png', 'image/png') }}" alt="QR Code" style="max-width: 200px; height: auto;" />
            </div>
            <p style="margin-top: 15px; font-size: 14px; color: #666;">{{ __('messages.scan_qr_code_to_view_ticket') }}</p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $manageUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ __('messages.manage_my_pass') }}
            </a>
        </div>

        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            {{ __('messages.event_support_contact') }}: <a href="mailto:{{ $bookedEvent->user->email }}" style="color: #4E81FA;">{{ $bookedEvent->user->email }}</a>
        </p>
    </div>
</body>
</html>
