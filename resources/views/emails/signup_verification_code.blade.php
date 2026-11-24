@php($theme = \App\Utils\EmailTheme::account())
{{-- Preview text leads with the code: it is what the reader came for, and a clear, early code is
     what mail clients' "Copy code" detection keys on. --}}
<x-email.layout :theme="$theme" :title="__('messages.signup_verification_code_heading')" :preheader="$code.' - '.__('messages.signup_verification_code_heading')">
{{-- The sign-up page's own mail says where the code goes; the guest forms and the API share this
     notification and have no sign-up page, so theirs does not name one. --}}
<x-email.heading :subtitle="! empty($continueUrl) ? __('messages.signup_verification_code_intro') : __('messages.signup_verification_code_intro_any')">{{ __('messages.signup_verification_code_heading') }}</x-email.heading>

{{-- Drawn as 123 456 and copied as 123456: see the highlight's group prop. --}}
<x-email.highlight :value="$code" :caption="__('messages.signup_code_expires_short')" large :group="3" ltr />

{{-- For whoever opens this somewhere other than the sign-up tab: a phone, or a browser that
     discarded the tab while they were in their inbox. Brings them to the code step with the
     address filled in; the code itself is never in the link. --}}
@if (! empty($continueUrl))
<x-email.button :href="$continueUrl" arrow>{{ __('messages.continue_signup') }}</x-email.button>
@endif

<x-email.section />
<x-email.text variant="small"><strong class="es-ink" style="color: #0f172a;">{{ __('messages.signup_code_never_share') }}</strong> {{ __('messages.signup_code_never_asked') }}</x-email.text>

<x-slot:footer>
<x-email.footer>{{ __('messages.signup_code_not_you', ['app' => config('app.name')]) }}</x-email.footer>
</x-slot:footer>
</x-email.layout>
