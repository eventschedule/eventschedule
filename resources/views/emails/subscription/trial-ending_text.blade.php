{{ __('messages.subscription_trial_ending_subject') }}

{{ __('messages.hello') }} {{ $role->user?->name ?? '' }},

{{ __('messages.subscription_trial_ending_body', ['schedule' => $role->name, 'plan' => $planLabel, 'date' => $trialEndDate, 'amount' => $amount]) }}

{{ __('messages.subscription_trial_ending_continue', ['plan' => $planLabel]) }}

{{ __('messages.subscription_trial_ending_cancel') }}

{{ __('messages.subscription_trial_ending_help') }}

@if ($portalUrl)
{{ __('messages.subscription_trial_ending_manage') }}: {{ $portalUrl }}
@endif
