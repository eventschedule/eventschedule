{{ __('messages.subscription_confirm_heading') }}

{{ __('messages.subscription_confirm_body', ['schedule' => $role->name]) }}

{{ __('messages.subscription_confirm_cadence') }}
@if ($role->willCreateAccountOnConfirm())

{{ __('messages.subscribe_account_note') }}
@endif

{{ __('messages.subscription_confirm_button') }}: {{ $confirmUrl }}

{{ __('messages.subscription_confirm_ignore') }}

{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
