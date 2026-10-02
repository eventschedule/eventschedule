@php($theme = \App\Utils\EmailTheme::account())
<x-email.layout :theme="$theme" :title="__('messages.onboarding_nudge_subject_'.$stage)" :preheader="__('messages.onboarding_nudge_body_'.$stage)">
<x-email.heading>{{ __('messages.onboarding_nudge_heading_'.$stage) }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }} {{ $user->firstName() }},</x-email.text>
<x-email.text>{{ __('messages.onboarding_nudge_body_'.$stage) }}</x-email.text>

<x-email.button :href="$startUrl">{{ __('messages.onboarding_nudge_cta') }}</x-email.button>

<x-email.text variant="small">{{ __('messages.onboarding_nudge_free_note') }}</x-email.text>

<x-slot:footer>
<x-email.footer :links="[[__('messages.unsubscribe'), $unsubscribeUrl]]" />
</x-slot:footer>
</x-email.layout>
