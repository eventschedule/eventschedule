@php
    $theme = \App\Utils\EmailTheme::owner($role);
    $subject = __($hasCard ? 'messages.subscription_renewal_subject' : 'messages.subscription_renewal_subject_no_card');
    $body = __($hasCard ? 'messages.subscription_renewal_body' : 'messages.subscription_renewal_body_no_card', ['schedule' => $role->name, 'plan' => $planLabel, 'date' => $renewalDate, 'amount' => $amount]);
@endphp
<x-email.layout :theme="$theme" :title="$subject" :preheader="$body">
<x-email.heading>{{ $subject }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $role->user?->name ?? '' }},</x-email.text>
<x-email.text>{{ $body }}</x-email.text>
<x-email.text>{{ __($hasCard ? 'messages.subscription_renewal_continue' : 'messages.subscription_renewal_continue_no_card') }}</x-email.text>

<x-email.callout tone="warning">{{ __($hasCard ? 'messages.subscription_renewal_cancel' : 'messages.subscription_renewal_cancel_no_card', ['date' => $renewalDate, 'plan' => $planLabel]) }}</x-email.callout>

<x-email.text variant="small">{{ __('messages.subscription_renewal_help') }}</x-email.text>

@if ($portalUrl)
<x-email.button :href="$portalUrl">{{ __($hasCard ? 'messages.subscription_renewal_manage' : 'messages.subscription_renewal_manage_no_card') }}</x-email.button>
@endif
</x-email.layout>
