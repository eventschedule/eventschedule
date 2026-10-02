@php($theme = \App\Utils\EmailTheme::owner($role))
<x-email.layout :theme="$theme" :title="__('messages.subscription_payment_failed_subject')" :preheader="__('messages.subscription_payment_failed_body', ['schedule' => $role->name])">
<x-email.heading>{{ __('messages.subscription_payment_failed_subject') }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $role->user?->name ?? '' }},</x-email.text>
<x-email.text>{{ __('messages.subscription_payment_failed_body', ['schedule' => $role->name]) }}</x-email.text>

<x-email.callout tone="danger"><strong>{{ __('messages.subscription_payment_failed_warning') }}</strong></x-email.callout>

@if ($portalUrl)
<x-email.button :href="$portalUrl">{{ __('messages.subscription_payment_failed_update') }}</x-email.button>
@endif
</x-email.layout>
