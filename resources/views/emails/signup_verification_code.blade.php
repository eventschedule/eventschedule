<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.signup_verification_code_heading') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px; font-weight: 600;">{{ __('messages.signup_verification_code_heading') }}</h1>
    </div>
    
    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">{{ __('messages.hello') }},</p>
        
        <p>{{ __('messages.signup_verification_code_intro') }}</p>
        
        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4E81FA;">
            <h2 style="margin-top: 0; color: #4E81FA; text-align: center; font-size: 32px; letter-spacing: 4px; font-weight: bold;">
                {{ $code }}
            </h2>
            <p style="margin: 10px 0; text-align: center; color: #666; font-size: 14px;">{{ __('messages.your_verification_code') }}</p>
        </div>
        
        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 10px 0; color: #666;">{{ __('messages.signup_verification_code_expiry') }}</p>
        </div>

        {{-- For whoever opens this somewhere other than the sign-up tab: a phone, or a browser that
             discarded the tab while they were in their inbox. Brings them to the code step with the
             address filled in; the code itself is never in the link. --}}
        @if (! empty($continueUrl))
        <div style="text-align: center; margin: 24px 0;">
            <a href="{{ $continueUrl }}" style="display: inline-block; background-color: #4E81FA; color: #ffffff; text-decoration: none; font-weight: 600; padding: 12px 24px; border-radius: 6px;">{{ __('messages.continue_signup') }}</a>
        </div>
        @endif

        <div style="background-color: #f0f4ff; padding: 15px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 0; color: #555; font-size: 14px;">{{ __('messages.signup_verification_code_security_notice') }}</p>
        </div>

        <p style="font-size: 12px; color: #999; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            {{ __('messages.thank_you_for_using') }}
        </p>
    </div>
</body>
</html>

