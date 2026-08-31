{{ __('messages.subscription_confirm_heading') }}

{{ __('messages.subscription_confirm_body', ['schedule' => $role->name]) }}

{{ __('messages.subscription_confirm_cadence') }}

{{ __('messages.subscription_confirm_button') }}: {{ $confirmUrl }}

{{ __('messages.subscription_confirm_ignore') }}

{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
