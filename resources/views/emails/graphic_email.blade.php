<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.upcoming_events') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ __('messages.upcoming_events') }}</h1>
        <p style="margin: 10px 0 0 0; font-size: 14px; opacity: 0.9;">{{ $role->getDisplayName() }}</p>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <pre style="margin: 0 0 20px 0; white-space: pre-wrap; word-wrap: break-word; font-family: 'Courier New', Courier, monospace; font-size: 13px; line-height: 1.6; color: #333; background-color: #f5f5f5; padding: 16px; border: 1px solid #ddd; border-radius: 4px;">{{ $eventText }}</pre>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $scheduleUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ __('messages.view_full_schedule') }}
            </a>
        </div>

        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px; text-align: center;">
            {{ __('messages.graphic_attached_note') }}
        </p>
    </div>
</body>
</html>
