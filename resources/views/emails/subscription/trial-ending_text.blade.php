{{ __($hasCard ? 'messages.subscription_trial_ending_subject' : 'messages.subscription_trial_ending_subject_no_card') }}

{{ __('messages.hello') }} {{ $role->user?->name ?? '' }},

{{ __(($windDown ?? false) ? 'messages.subscription_winddown_body' : ($hasCard ? 'messages.subscription_trial_ending_body' : 'messages.subscription_trial_ending_body_no_card'), ['schedule' => $role->name, 'plan' => $planLabel, 'date' => $trialEndDate, 'amount' => $amount]) }}

{{ __(($windDown ?? false) ? 'messages.subscription_winddown_continue' : ($hasCard ? 'messages.subscription_trial_ending_continue' : 'messages.subscription_trial_ending_continue_no_card'), ['plan' => $planLabel]) }}

{{ __(($windDown ?? false) ? 'messages.subscription_winddown_cancel' : ($hasCard ? 'messages.subscription_trial_ending_cancel' : 'messages.subscription_trial_ending_cancel_no_card'), ['plan' => $planLabel]) }}

{{ __('messages.subscription_trial_ending_help') }}

@if ($portalUrl)
{{ __(($windDown ?? false) ? 'messages.subscription_winddown_manage' : ($hasCard ? 'messages.subscription_trial_ending_manage' : 'messages.subscription_trial_ending_manage_no_card')) }}: {{ $portalUrl }}
@endif
