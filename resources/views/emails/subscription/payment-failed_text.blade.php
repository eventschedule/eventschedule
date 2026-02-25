{{ __('messages.subscription_payment_failed_subject') }}

{{ __('messages.hello') }} {{ $role->user?->name ?? '' }},

{{ __('messages.subscription_payment_failed_body', ['schedule' => $role->name]) }}

{{ __('messages.subscription_payment_failed_warning') }}

@if ($portalUrl)
{{ __('messages.subscription_payment_failed_update') }}: {{ $portalUrl }}
@endif
