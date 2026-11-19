@php
    $theme = \App\Utils\EmailTheme::owner($role);
    $windDown = $windDown ?? false;
    $cancelled = $cancelled ?? false;
    $subject = __($hasCard ? 'messages.subscription_trial_ending_subject' : 'messages.subscription_trial_ending_subject_no_card', ['schedule' => $role->name]);
    $params = ['schedule' => $role->name, 'plan' => $planLabel, 'date' => $trialEndDate, 'amount' => $amount];
    $body = __($cancelled ? 'messages.subscription_trial_cancelled_ending_body' : ($windDown ? 'messages.subscription_winddown_body' : ($hasCard ? 'messages.subscription_trial_ending_body' : 'messages.subscription_trial_ending_body_no_card')), $params);
@endphp
<x-email.layout :theme="$theme" :title="$subject" :preheader="$body">
<x-email.heading>{{ $subject }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $role->user?->name ?? '' }},</x-email.text>
<x-email.text>{{ $body }}</x-email.text>
<x-email.text>{{ __($cancelled ? 'messages.subscription_trial_cancelled_ending_continue' : ($windDown ? 'messages.subscription_winddown_continue' : ($hasCard ? 'messages.subscription_trial_ending_continue' : 'messages.subscription_trial_ending_continue_no_card')), $params) }}</x-email.text>

@if (! empty($sellingTip))
<x-email.text>{{ __($sellingTip['key'], $sellingTip['params']) }}</x-email.text>
<x-email.url :href="$sellingTip['url']" />
@endif

@unless ($cancelled)
<x-email.callout tone="warning">{{ __($windDown ? 'messages.subscription_winddown_cancel' : ($hasCard ? 'messages.subscription_trial_ending_cancel' : 'messages.subscription_trial_ending_cancel_no_card'), ['plan' => $planLabel]) }}</x-email.callout>
@endunless

<x-email.text variant="small">{{ __('messages.subscription_trial_ending_help') }}</x-email.text>

@if ($portalUrl)
<x-email.button :href="$portalUrl">{{ __($cancelled ? 'messages.subscription_trial_cancelled_ending_manage' : ($windDown ? 'messages.subscription_winddown_manage' : ($hasCard ? 'messages.subscription_trial_ending_manage' : 'messages.subscription_trial_ending_manage_no_card')), ['plan' => $planLabel]) }}</x-email.button>
@endif
</x-email.layout>
