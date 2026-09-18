<!DOCTYPE html>
<html @if ($isRtl ?? false) dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.set_password_subject') }}</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #4E81FA; color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;">
        <h1 style="margin: 0; font-size: 24px; font-weight: 600;">{{ __('messages.set_password_heading') }}</h1>
    </div>

    <div style="background-color: #f9f9f9; padding: 20px; border-radius: 0 0 8px 8px;">
        {{-- bdi: the address is LTR inside RTL prose in ar/he, and without isolation it reorders
             around the surrounding punctuation. --}}
        <p style="font-size: 16px; margin-top: 0;">
            {!! __('messages.set_password_body', ['email' => '<bdi dir="ltr">'.e($email).'</bdi>']) !!}
        </p>

        <div style="text-align: center; margin: 28px 0;">
            <a href="{{ $resetUrl }}"
               style="background-color: #4E81FA; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 6px; font-size: 16px; font-weight: 600; display: inline-block;">
                {{ __('messages.set_password_button') }}
            </a>
        </div>

        {{-- The plain URL as well as the button, as subscription_confirmation.blade.php does: a
             client that strips or fails to render the anchor otherwise leaves a dead email. --}}
        <p style="font-size: 12px; color: #888; word-break: break-all; margin-bottom: 16px;">
            {{ $resetUrl }}
        </p>

        <p style="font-size: 13px; color: #888; margin-bottom: 0;">
            {{ __('messages.set_password_expires', ['minutes' => $expiresInMinutes]) }}
            {{ __('messages.set_password_ignore') }}
        </p>
    </div>
</body>
</html>
