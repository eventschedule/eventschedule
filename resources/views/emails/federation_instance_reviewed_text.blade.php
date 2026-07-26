{{ $approved ? __('messages.federation_approved_subject') : __('messages.federation_suspended_subject') }}

{{ $approved ? __('messages.federation_approved_intro') : __('messages.federation_suspended_intro') }}

{{ $instance->name ?: $instance->site_url }}
{{ $instance->site_url }}

{{ $approved ? __('messages.federation_approved_next') : __('messages.federation_suspended_next') }}
