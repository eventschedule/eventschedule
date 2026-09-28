<!DOCTYPE html>
<html @if ($isRtl ?? false) dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.ticket_trial_ending_subject', ['schedule' => $role->name, 'date' => $endDate]) }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ __('messages.ticket_trial_ending_subject', ['schedule' => $role->name, 'date' => $endDate]) }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $role->user?->name ?? '' }},</p>

        <p>{{ __('messages.ticket_trial_ending_body', ['schedule' => $role->name, 'date' => $endDate]) }}</p>

        <div style="background-color: white; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <p style="margin: 0; color: #333;">{{ __('messages.ticket_trial_ending_buyers') }}</p>
        </div>

        <div style="text-align: center; margin: 25px 0;">
            <a href="{{ $planUrl }}" style="display: inline-block; background-color: #4E81FA; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">{{ __('messages.upgrade_to_pro_plan') }}</a>
        </div>

        <p style="font-size: 12px; color: #999;">{{ config('app.name') }}</p>
    </div>
</body>
</html>
