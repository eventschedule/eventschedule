<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.email_settings_failed_email_subject', ['schedule' => $role->name]) }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px; font-weight: 600;">{{ __('messages.email_settings_failed_email_heading') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.email_settings_failed_email_greeting', ['name' => $recipient->name ?? $recipient->email]) }},</p>

        <p style="font-size: 15px;">
            {!! __('messages.email_settings_failed_email_intro', [
                'schedule' => '<strong>'.e($role->name).'</strong>',
                'date' => $failedAt ? $failedAt->isoFormat('LLL') : '',
            ]) !!}
        </p>

        <p style="font-size: 15px;">
            {{ __('messages.email_settings_failed_email_consequence') }}
        </p>

        @if (!empty($errorMessage))
        <div style="background-color: #fff; border-left: 4px solid #f59e0b; padding: 12px 16px; margin: 20px 0; font-family: monospace; font-size: 13px; color: #555; word-break: break-word;">
            {{ $errorMessage }}
        </div>
        @endif

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $editUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ __('messages.email_settings_failed_email_action') }}
            </a>
        </div>

        <p style="font-size: 14px; color: #555; margin-top: 25px;">
            <strong>{{ __('messages.email_settings_failed_email_common_causes_title') }}</strong>
        </p>
        <ul style="font-size: 14px; color: #555; padding-left: 20px;">
            <li>{{ __('messages.email_settings_failed_email_cause_password') }}</li>
            <li>{{ __('messages.email_settings_failed_email_cause_2fa') }}</li>
            <li>{{ __('messages.email_settings_failed_email_cause_disabled') }}</li>
        </ul>

        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            {{ __('messages.thank_you_for_using') }}
        </p>
    </div>
</body>
</html>
