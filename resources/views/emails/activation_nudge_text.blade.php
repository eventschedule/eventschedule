{{ __('messages.activation_nudge_heading_'.$nudgeKey) }}

{{ $greeting }}

{{ __($bodyKey ?? 'messages.activation_nudge_body_'.$nudgeKey, ['schedule' => $role->name]) }}
@if ($importUrl)

{{ __('messages.activation_nudge_import_'.$nudgeKey) }}
@endif

{{ __('messages.activation_nudge_cta_'.$nudgeKey) }}: {!! $ctaUrl !!}
@if ($importUrl)
{{ __('messages.activation_nudge_import_cta_'.$nudgeKey) }}: {!! $importUrl !!}
@endif

{{ __('messages.unsubscribe') }}: {!! $unsubscribeUrl !!}{{-- Raw: plain text has no entity decoding, so an escaped &amp; breaks the signed link. --}}
