<!DOCTYPE html>
<html @if ($isRtl ?? false) dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.event_cancelled_heading') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; @if ($isRtl ?? false) text-align: right; @endif">
    <div style="background-color: #dc2626; color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px; font-weight: 600;">{{ __('messages.event_cancelled_heading') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }}@if (! empty($recipientName)), {{ $recipientName }}@endif,</p>

        <p style="font-size: 16px;">{{ __('messages.event_cancelled_body', ['event' => $event->name]) }}</p>

        @if (! empty($note))
        <div style="background-color: #fff; padding: 15px 20px; border-radius: 8px; margin: 20px 0; border-{{ ($isRtl ?? false) ? 'right' : 'left' }}: 4px solid #9ca3af;">
            <p style="margin: 0 0 6px 0; font-size: 13px; color: #666; font-weight: bold;">{{ __('messages.organizer_note') }}</p>
            <p style="margin: 0; font-size: 15px; color: #333;">{!! nl2br(e($note)) !!}</p>
        </div>
        @endif

        <p style="font-size: 14px; color: #666;">{{ __('messages.event_cancelled_refund_guidance') }}</p>

        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            {{ __('messages.thank_you_for_using') }}
        </p>
    </div>
</body>
</html>
