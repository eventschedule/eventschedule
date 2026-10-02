@php
    $theme = \App\Utils\EmailTheme::owner($role);
    $windDown = $windDown ?? false;
    $subject = __($hasCard ? 'messages.subscription_trial_ending_subject' : 'messages.subscription_trial_ending_subject_no_card');
    $body = __($windDown ? 'messages.subscription_winddown_body' : ($hasCard ? 'messages.subscription_trial_ending_body' : 'messages.subscription_trial_ending_body_no_card'), ['schedule' => $role->name, 'plan' => $planLabel, 'date' => $trialEndDate, 'amount' => $amount]);
@endphp
<x-email.layout :theme="$theme" :title="$subject" :preheader="$body">
<x-email.heading>{{ $subject }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $role->user?->name ?? '' }},</x-email.text>
<x-email.text>{{ $body }}</x-email.text>
<x-email.text>{{ __($windDown ? 'messages.subscription_winddown_continue' : ($hasCard ? 'messages.subscription_trial_ending_continue' : 'messages.subscription_trial_ending_continue_no_card'), ['plan' => $planLabel]) }}</x-email.text>

<x-email.callout tone="warning">{{ __($windDown ? 'messages.subscription_winddown_cancel' : ($hasCard ? 'messages.subscription_trial_ending_cancel' : 'messages.subscription_trial_ending_cancel_no_card'), ['plan' => $planLabel]) }}</x-email.callout>

<x-email.text variant="small">{{ __('messages.subscription_trial_ending_help') }}</x-email.text>

@if ($portalUrl)
<x-email.button :href="$portalUrl">{{ __($windDown ? 'messages.subscription_winddown_manage' : ($hasCard ? 'messages.subscription_trial_ending_manage' : 'messages.subscription_trial_ending_manage_no_card')) }}</x-email.button>
@endif
</x-email.layout>
