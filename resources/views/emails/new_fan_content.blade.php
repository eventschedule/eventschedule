<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.new_fan_content_notification_subject', ['name' => $event->name, 'count' => $fanContentCount]) }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px; font-weight: 600;">{{ __('messages.pending_fan_content') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }},</p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <p style="margin: 0; font-size: 18px; color: #333;">
                <strong>{{ $fanContentCount }}</strong> {{ __('messages.pending_fan_content') }}
            </p>
            <p style="margin: 10px 0 0 0; color: #666;">
                for <strong>{{ $event->name }}</strong>
            </p>
        </div>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $actionUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ __('messages.view_details') }}
            </a>
        </div>

        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            {{ __('messages.thank_you_for_using') }}
            <br><br>
            <a href="{{ $unsubscribeUrl }}" style="color: #4E81FA;">{{ __('messages.unsubscribe') }}</a>
        </p>
    </div>
</body>
</html>
