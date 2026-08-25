<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.schedule_transfer_invite_subject', ['name' => $role?->name]) }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ __('messages.schedule_transfer_invite_heading') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">
            {{ __('messages.schedule_transfer_invite_intro', ['user' => $fromUser?->name, 'name' => $role?->name]) }}
        </p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <p style="margin: 0;"><strong>{{ $role?->name }}</strong></p>
            @if ($role)
            <p style="margin: 6px 0 0; color: #666;">{{ $role->getGuestUrl(true) }}</p>
            @endif
        </div>

        <p style="font-size: 16px;">
            {{ __('messages.schedule_transfer_invite_what_moves') }}
        </p>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $acceptUrl }}" style="background-color: #4E81FA; color: white; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;">
                {{ __('messages.schedule_transfer_review') }}
            </a>
        </p>

        <p style="font-size: 14px; color: #666;">
            {{ __('messages.schedule_transfer_invite_sign_in', ['email' => $transfer->to_email]) }}
        </p>

        <p style="font-size: 14px; color: #666;">
            {{ __('messages.schedule_transfer_invite_expires', ['date' => $transfer->expires_at?->format('M j, Y')]) }}
        </p>

        <p style="font-size: 14px; color: #666;">
            {{ __('messages.schedule_transfer_invite_ignore') }}
        </p>
    </div>
</body>
</html>
