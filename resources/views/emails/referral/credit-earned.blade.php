<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.referral_credit_earned_subject') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ __('messages.referral_credit_earned_subject') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }} {{ $referrer->firstName() }},</p>

        <p>{{ __('messages.referral_credit_earned_body', ['value' => $creditValue, 'plan' => ucfirst($planType)]) }}</p>

        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <p style="margin: 5px 0;"><strong>{{ __('messages.credit_value') }}:</strong> {{ $creditValue }}</p>
            <p style="margin: 5px 0;"><strong>{{ __('messages.plan_tier') }}:</strong> {{ ucfirst($planType) }}</p>
        </div>

        <p>{{ __('messages.referral_credit_earned_cta') }}</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ $dashboardUrl }}"
               style="display: inline-block; background-color: #4E81FA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px;">
                {{ __('messages.view_referral_dashboard') }}
            </a>
        </div>

        <p style="font-size: 12px; color: #999;">{{ config('app.name') }}</p>
    </div>
</body>
</html>
