{{ $approved ? __('messages.promotion_email_approved_subject') : __('messages.promotion_email_rejected_subject') }}

{{ __('messages.hello') }} {{ $campaign->user?->name ?? '' }},

{{ $approved ? __('messages.promotion_email_approved_body') : __('messages.promotion_email_rejected_body') }}

{{ $event?->name ?? __('messages.deleted_event') }}
@if (! $approved && $notes)

{{ __('messages.reason') }}: {{ $notes }}
@endif
@if (! $approved)

{{ __('messages.boost_full_refund_issued') }}
@endif

{{ __('messages.view_campaign') }}: {{ $url }}
