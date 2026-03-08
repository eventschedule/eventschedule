<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.backup_export_email_subject') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ __('messages.backup_export_email_subject') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.backup_export_email_intro') }}</p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <h3 style="margin-top: 0; color: #4E81FA;">{{ __('messages.backup_included_schedules') }}</h3>
            <ul style="margin: 0; padding-left: 20px;">
                @foreach ($scheduleNames as $name)
                <li>{{ $name }}</li>
                @endforeach
            </ul>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $downloadUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ __('messages.backup_download_button') }}
            </a>
        </div>

        <p style="color: #666; font-size: 14px; text-align: center;">
            {{ __('messages.backup_download_expires', ['date' => $expiresAt->format('F j, Y')]) }}
        </p>

        <div style="margin-top: 20px; padding: 15px; background-color: #fff3cd; border-radius: 8px; font-size: 13px; color: #856404;">
            {{ __('messages.backup_pii_warning') }}
        </div>
    </div>
</body>
</html>
