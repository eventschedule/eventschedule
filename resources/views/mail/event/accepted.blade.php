<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #48bb78; color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px; font-weight: 600;">{{ $subject }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }},</p>

        <p style="font-size: 16px; color: #333;">
            {{ str_replace(':venue', $role->name, __('messages.request_accepted')) }}
        </p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #48bb78;">
            <h2 style="margin-top: 0; color: #333; font-size: 20px;">
                {{ $event->name }}
            </h2>
            <p style="margin: 10px 0; color: #666;">
                {{ $event->localStartsAt(true) }}
            </p>
            @if($event->getVenueDisplayName())
            <p style="margin: 10px 0; color: #666;">
                {{ $event->getVenueDisplayName() }}
            </p>
            @endif
        </div>

        <div style="text-align: center; margin: 25px 0;">
            <a href="{{ $event->getGuestUrl() }}" style="display: inline-block; background-color: #4E81FA; color: white; padding: 12px 30px; text-decoration: none; border-radius: 6px; font-weight: 600;">
                {{ __('messages.view_event') }}
            </a>
        </div>

        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            {!! __('messages.claim_email_line2', ['click_here' => '<a href="' . route('role.show_unsubscribe', ['email' => base64_encode($creatorRole->email)]) . '" style="color: #4E81FA;">' . __('messages.click_here') . '</a>']) !!}
        </p>

        <p style="font-size: 12px; color: #999; margin-top: 10px;">
            {{ __('messages.thanks') }},<br>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
