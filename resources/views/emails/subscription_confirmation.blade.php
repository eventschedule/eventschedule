<!DOCTYPE html>
<html @if ($isRtl ?? false) dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.subscription_confirm_subject', ['schedule' => $role->name]) }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px; font-weight: 600;">{{ __('messages.subscription_confirm_heading') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.subscription_confirm_body', ['schedule' => $role->name]) }}</p>

        <p style="font-size: 15px; color: #555;">{{ __('messages.subscription_confirm_cadence') }}</p>

        <div style="text-align: center; margin: 28px 0;">
            <a href="{{ $confirmUrl }}"
               style="background-color: #4E81FA; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: 600; display: inline-block;">
                {{ __('messages.subscription_confirm_button') }}
            </a>
        </div>

        <p style="font-size: 13px; color: #888; margin-bottom: 0;">
            {{ __('messages.subscription_confirm_ignore') }}
        </p>
    </div>

    <div style="text-align: center; padding: 16px 0; font-size: 12px; color: #999;">
        <a href="{{ $unsubscribeUrl }}" style="color: #999;">{{ __('messages.unsubscribe') }}</a>
    </div>
</body>
</html>
