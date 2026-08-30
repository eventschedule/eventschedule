{{ __('messages.activation_nudge_heading_'.$nudgeKey) }}

{{ __('messages.hello') }} {{ $user->firstName() }},

{{ __('messages.activation_nudge_body_'.$nudgeKey, ['schedule' => $role->name]) }}

{{ __('messages.activation_nudge_cta_'.$nudgeKey) }}: {{ $ctaUrl }}

{{ __('messages.unsubscribe') }}: {{ $unsubscribeUrl }}
