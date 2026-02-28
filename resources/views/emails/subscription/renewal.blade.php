<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __($hasCard ? 'messages.subscription_renewal_subject' : 'messages.subscription_renewal_subject_no_card') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ __($hasCard ? 'messages.subscription_renewal_subject' : 'messages.subscription_renewal_subject_no_card') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $role->user?->name ?? '' }},</p>

        <p>{{ __($hasCard ? 'messages.subscription_renewal_body' : 'messages.subscription_renewal_body_no_card', ['schedule' => $role->name, 'plan' => $planLabel, 'date' => $renewalDate, 'amount' => $amount]) }}</p>

        <div style="background-color: white; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <p style="margin: 0; color: #333;">{{ __($hasCard ? 'messages.subscription_renewal_continue' : 'messages.subscription_renewal_continue_no_card') }}</p>
        </div>

        <div style="background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">
            <p style="margin: 0; color: #856404;">{{ __($hasCard ? 'messages.subscription_renewal_cancel' : 'messages.subscription_renewal_cancel_no_card', ['date' => $renewalDate, 'plan' => $planLabel]) }}</p>
        </div>

        <p>{{ __('messages.subscription_renewal_help') }}</p>

        @if ($portalUrl)
        <div style="text-align: center; margin: 25px 0;">
            <a href="{{ $portalUrl }}" style="display: inline-block; background-color: #4E81FA; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; font-weight: bold;">{{ __($hasCard ? 'messages.subscription_renewal_manage' : 'messages.subscription_renewal_manage_no_card') }}</a>
        </div>
        @endif

        <p style="font-size: 12px; color: #999;">{{ config('app.name') }}</p>
    </div>
</body>
</html>
