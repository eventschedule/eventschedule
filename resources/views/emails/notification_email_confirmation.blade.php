<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.notification_email_confirm_subject', ['schedule' => $role->name]) }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ __('messages.notification_email_confirm_heading', ['schedule' => $role->name]) }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">
            {{ __('messages.notification_email_confirm_intro', ['name' => $requesterName, 'schedule' => $role->name]) }}
        </p>

        <p style="font-size: 16px;">
            {{ __('messages.notification_email_confirm_body') }}
        </p>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ $confirmUrl }}" style="background-color: #4E81FA; color: white; padding: 14px 28px; text-decoration: none; border-radius: 8px; font-weight: bold; display: inline-block;">
                {{ __('messages.notification_email_confirm_button') }}
            </a>
        </p>

        <p style="font-size: 14px; color: #666;">
            {{ __('messages.notification_email_confirm_expires', ['days' => \App\Services\NotificationEmailService::VERIFY_TTL_DAYS]) }}
        </p>

        <p style="font-size: 14px; color: #666;">
            {{ __('messages.notification_email_confirm_ignore') }}
        </p>
    </div>
</body>
</html>
