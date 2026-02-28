{{ __('messages.subscription_renewal_subject') }}

{{ __('messages.hello') }} {{ $role->user?->name ?? '' }},

{{ __('messages.subscription_renewal_body', ['schedule' => $role->name, 'plan' => $planLabel, 'date' => $renewalDate, 'amount' => $amount]) }}

{{ __('messages.subscription_renewal_continue') }}

{{ __('messages.subscription_renewal_cancel', ['date' => $renewalDate, 'plan' => $planLabel]) }}

{{ __('messages.subscription_renewal_help') }}

@if ($portalUrl)
{{ __('messages.subscription_renewal_manage') }}: {{ $portalUrl }}
@endif
