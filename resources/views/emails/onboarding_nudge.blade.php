@php($theme = \App\Utils\EmailTheme::account())
<x-email.layout :theme="$theme" :title="$title" :preheader="__('messages.onboarding_nudge_preheader_'.$stage)">
<x-email.heading>{{ __('messages.onboarding_nudge_heading_'.$stage) }}</x-email.heading>

<x-email.text>{{ $greeting }}</x-email.text>
<x-email.text>{{ __($typeKey ?? 'messages.onboarding_nudge_body_'.$stage) }}</x-email.text>

<x-email.button :href="$startUrl">{{ __('messages.onboarding_nudge_cta') }}</x-email.button>
@if ($examplesUrl)
<x-email.button :href="$examplesUrl" variant="secondary">{{ __('messages.onboarding_nudge_examples_cta') }}</x-email.button>
@endif

@if ($replyKey)
<x-email.text>{{ __($replyKey) }}</x-email.text>
@endif
@if ($signoff)
<x-email.text>{{ $signoff }}</x-email.text>
@endif

<x-email.text variant="small">{{ __('messages.onboarding_nudge_free_note') }}</x-email.text>

<x-slot:footer>
<x-email.footer :links="[[__('messages.unsubscribe'), $unsubscribeUrl]]" />
</x-slot:footer>
</x-email.layout>
