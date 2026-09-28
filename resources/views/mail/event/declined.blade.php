<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" @if ($isRtl) dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
</head>
{{-- dir is repeated on the wrapper divs because Gmail's web client strips it from <html> and <body>. --}}
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div @if ($isRtl) dir="rtl" @endif style="background-color: #718096; color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px; font-weight: 600;">{{ $subject }}</h1>
    </div>

    <div @if ($isRtl) dir="rtl" @endif style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px; text-align: {{ $isRtl ? 'right' : 'left' }};">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }}@if (trim((string) $recipient->name) !== '') {{ $recipient->firstName() }}@endif,</p>

        <p style="font-size: 16px; color: #333;">
            {{ str_replace(':venue', $role->name, __('messages.request_declined_body')) }}
        </p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h2 style="margin-top: 0; color: #333; font-size: 20px;">
                {{ $event->name }}
            </h2>
            <p style="margin: 10px 0; color: #666;">
                {{ $eventDate }}
            </p>
            @if($event->getVenueDisplayName())
            <p style="margin: 10px 0; color: #666;">
                {{ $event->getVenueDisplayName() }}
            </p>
            @endif
        </div>

        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            {{ __('messages.request_decision_why', ['schedule' => $role->name]) }}
            <a href="{{ $unsubscribeUrl }}" style="color: #999;">{{ __('messages.unsubscribe') }}</a>
        </p>

        <p style="font-size: 12px; color: #999; margin-top: 10px;">
            {{ __('messages.thanks') }},<br>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
