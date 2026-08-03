<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.onboarding_nudge_subject_'.$stage) }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ __('messages.onboarding_nudge_heading_'.$stage) }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $user->firstName() }},</p>

        <p>{{ __('messages.onboarding_nudge_body_'.$stage) }}</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $startUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ __('messages.onboarding_nudge_cta') }}
            </a>
        </div>

        <p style="font-size: 14px; color: #555;">{{ __('messages.onboarding_nudge_free_note') }}</p>

        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            <a href="{{ $unsubscribeUrl }}" style="color: #999;">{{ __('messages.unsubscribe') }}</a>
        </p>
    </div>
</body>
</html>
