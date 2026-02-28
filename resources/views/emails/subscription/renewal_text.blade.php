{{ __($hasCard ? 'messages.subscription_renewal_subject' : 'messages.subscription_renewal_subject_no_card') }}

{{ __('messages.hello') }} {{ $role->user?->name ?? '' }},

{{ __($hasCard ? 'messages.subscription_renewal_body' : 'messages.subscription_renewal_body_no_card', ['schedule' => $role->name, 'plan' => $planLabel, 'date' => $renewalDate, 'amount' => $amount]) }}

{{ __($hasCard ? 'messages.subscription_renewal_continue' : 'messages.subscription_renewal_continue_no_card') }}

{{ __($hasCard ? 'messages.subscription_renewal_cancel' : 'messages.subscription_renewal_cancel_no_card', ['date' => $renewalDate, 'plan' => $planLabel]) }}

{{ __('messages.subscription_renewal_help') }}

@if ($portalUrl)
{{ __($hasCard ? 'messages.subscription_renewal_manage' : 'messages.subscription_renewal_manage_no_card') }}: {{ $portalUrl }}
@endif
