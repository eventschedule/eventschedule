{{ __('messages.onboarding_nudge_heading_'.$stage) }}

{{ $greeting }}

{{ __($typeKey ?? 'messages.onboarding_nudge_body_'.$stage) }}

{{ __('messages.onboarding_nudge_cta') }}: {!! $startUrl !!}
@if ($examplesUrl)
{{ __('messages.onboarding_nudge_examples_cta') }}: {!! $examplesUrl !!}
@endif
@if ($replyKey)

{{ __($replyKey) }}
@endif
@if ($signoff)

{{ $signoff }}
@endif

{{ __('messages.onboarding_nudge_free_note') }}

{{ __('messages.unsubscribe') }}: {!! $unsubscribeUrl !!}{{-- Raw: plain text has no entity decoding, so an escaped &amp; breaks the signed link. --}}
