@php
    $windDown = $windDown ?? false;
    $cancelled = $cancelled ?? false;
    $params = ['schedule' => $role->name, 'plan' => $planLabel, 'date' => $trialEndDate, 'amount' => $amount];
@endphp
{{ __($hasCard ? 'messages.subscription_trial_ending_subject' : 'messages.subscription_trial_ending_subject_no_card', ['schedule' => $role->name]) }}

{{ __('messages.hello') }} {{ $role->user?->name ?? '' }},

{{ __($cancelled ? 'messages.subscription_trial_cancelled_ending_body' : ($windDown ? 'messages.subscription_winddown_body' : ($hasCard ? 'messages.subscription_trial_ending_body' : 'messages.subscription_trial_ending_body_no_card')), $params) }}

{{ __($cancelled ? 'messages.subscription_trial_cancelled_ending_continue' : ($windDown ? 'messages.subscription_winddown_continue' : ($hasCard ? 'messages.subscription_trial_ending_continue' : 'messages.subscription_trial_ending_continue_no_card')), $params) }}

@if (! empty($sellingTip))
{{ __($sellingTip['key'], $sellingTip['params']) }} {!! $sellingTip['url'] !!}

@endif
@unless ($cancelled)
{{ __($windDown ? 'messages.subscription_winddown_cancel' : ($hasCard ? 'messages.subscription_trial_ending_cancel' : 'messages.subscription_trial_ending_cancel_no_card'), ['plan' => $planLabel]) }}

@endunless
{{ __('messages.subscription_trial_ending_help') }}

@if ($portalUrl)
{{ __($cancelled ? 'messages.subscription_trial_cancelled_ending_manage' : ($windDown ? 'messages.subscription_winddown_manage' : ($hasCard ? 'messages.subscription_trial_ending_manage' : 'messages.subscription_trial_ending_manage_no_card')), ['plan' => $planLabel]) }}: {{ $portalUrl }}
@endif
