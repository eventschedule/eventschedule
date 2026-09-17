<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $approved ? __('messages.federation_approved_subject') : __('messages.federation_suspended_subject') }}</title>
</head>
<body dir="{{ $isRtl ? 'rtl' : 'ltr' }}" style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; text-align: {{ $isRtl ? 'right' : 'left' }};">
    <div style="background-color: #4E81FA; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">{{ $approved ? __('messages.federation_approved_subject') : __('messages.federation_suspended_subject') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        <p style="font-size: 16px; margin-top: 0;">
            {{ $approved ? __('messages.federation_reapproved_intro') : __('messages.federation_suspended_intro') }}
        </p>

        {{-- Only the host, and never as a link: the install's name and site_url are whatever its
             registration sent. See FederatedInstance::displayHost(). --}}
        <div style="background-color: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-{{ $isRtl ? 'right' : 'left' }}: 4px solid #4E81FA;">
            <p dir="ltr" style="margin: 0; text-align: {{ $isRtl ? 'right' : 'left' }};"><strong>{{ $host }}</strong></p>
        </div>

        <p style="font-size: 14px; color: #666;">
            {{ $approved ? __('messages.federation_approved_next') : __('messages.federation_suspended_next') }}
        </p>
    </div>
</body>
</html>
