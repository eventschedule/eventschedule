{{ __($hasCard ? 'messages.subscription_trial_ending_subject' : 'messages.subscription_trial_ending_subject_no_card') }}

{{ __('messages.hello') }} {{ $role->user?->name ?? '' }},

{{ __($hasCard ? 'messages.subscription_trial_ending_body' : 'messages.subscription_trial_ending_body_no_card', ['schedule' => $role->name, 'plan' => $planLabel, 'date' => $trialEndDate, 'amount' => $amount]) }}

{{ __($hasCard ? 'messages.subscription_trial_ending_continue' : 'messages.subscription_trial_ending_continue_no_card', ['plan' => $planLabel]) }}

{{ __($hasCard ? 'messages.subscription_trial_ending_cancel' : 'messages.subscription_trial_ending_cancel_no_card', ['plan' => $planLabel]) }}

{{ __('messages.subscription_trial_ending_help') }}

@if ($portalUrl)
{{ __($hasCard ? 'messages.subscription_trial_ending_manage' : 'messages.subscription_trial_ending_manage_no_card') }}: {{ $portalUrl }}
@endif
