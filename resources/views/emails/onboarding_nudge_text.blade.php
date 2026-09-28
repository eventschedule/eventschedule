{{ __('messages.onboarding_nudge_heading_'.$stage) }}

{{ __('messages.hello') }} {{ $user->firstName() }},

{{ __('messages.onboarding_nudge_body_'.$stage) }}

{{ __('messages.onboarding_nudge_cta') }}: {{ $startUrl }}

{{ __('messages.onboarding_nudge_free_note') }}

{{ __('messages.unsubscribe') }}: {!! $unsubscribeUrl !!}{{-- Raw: plain text has no entity decoding, so an escaped &amp; breaks the signed link. --}}
