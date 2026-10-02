@php($theme = \App\Utils\EmailTheme::account())
{{-- Preview text leads with the code. Without it the inbox shows the heading and "Hello," - the
     code is what the reader came for, and a clear, early code is what mail clients' "Copy code"
     detection keys on. --}}
<x-email.layout :theme="$theme" :title="__('messages.signup_verification_code_heading')" :preheader="$code.' - '.__('messages.your_verification_code')">
<x-email.heading>{{ __('messages.signup_verification_code_heading') }}</x-email.heading>

<x-email.text>{{ __('messages.hello') }},</x-email.text>
<x-email.text>{{ __('messages.signup_verification_code_intro') }}</x-email.text>

{{-- One text node, no per-digit markup, so a copy (ours or Gmail's) yields exactly the code. --}}
<x-email.highlight :value="$code" :caption="__('messages.your_verification_code')" mono ltr />

<x-email.text variant="small">{{ __('messages.signup_verification_code_expiry') }}</x-email.text>

{{-- For whoever opens this somewhere other than the sign-up tab: a phone, or a browser that
     discarded the tab while they were in their inbox. Brings them to the code step with the
     address filled in; the code itself is never in the link. --}}
@if (! empty($continueUrl))
<x-email.button :href="$continueUrl">{{ __('messages.continue_signup') }}</x-email.button>
@endif

<x-email.callout tone="info">{{ __('messages.signup_verification_code_security_notice') }}</x-email.callout>
</x-email.layout>
