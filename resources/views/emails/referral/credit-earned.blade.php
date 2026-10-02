@php($theme = \App\Utils\EmailTheme::account())
<x-email.layout :theme="$theme" :title="__('messages.referral_credit_earned_subject')" :preheader="__('messages.referral_credit_earned_body', ['value' => $creditValue, 'plan' => ucfirst($planType)])">
<x-email.heading>{{ __('messages.referral_credit_earned_subject') }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $referrer->firstName() }},</x-email.text>
<x-email.text>{{ __('messages.referral_credit_earned_body', ['value' => $creditValue, 'plan' => ucfirst($planType)]) }}</x-email.text>

<x-email.details panel>
<x-email.item :label="__('messages.credit_value')">{{ $creditValue }}</x-email.item>
<x-email.item :label="__('messages.plan_tier')">{{ ucfirst($planType) }}</x-email.item>
</x-email.details>

<x-email.text>{{ __('messages.referral_credit_earned_cta') }}</x-email.text>

<x-email.button :href="$dashboardUrl">{{ __('messages.view_referral_dashboard') }}</x-email.button>
</x-email.layout>
