{{ __('messages.boost_email_rejected_subject') }}

{{ __('messages.hello') }} {{ $campaign->user?->name ?? '' }},

{{ __('messages.boost_email_rejected_body') }}

{{ $event?->name ?? __('messages.deleted_event') }}
@if ($rejectionReason)
{{ __('messages.reason') }}: {{ $rejectionReason }}
@endif

@if ($refunded)
{{ __('messages.boost_full_refund_issued') }}
{{ __('messages.boost_refund_amount') }}: {{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->total_charged, 2) }}
@else
{{ __('messages.boost_refund_pending') }}
@endif

{{ __('messages.boost_try_again_suggestion') }}
