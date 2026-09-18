{{ __('messages.set_password_heading') }}

{{ __('messages.set_password_body', ['email' => $email]) }}

{{ __('messages.set_password_button') }}: {{ $resetUrl }}

{{ __('messages.set_password_expires', ['minutes' => $expiresInMinutes]) }} {{ __('messages.set_password_ignore') }}
