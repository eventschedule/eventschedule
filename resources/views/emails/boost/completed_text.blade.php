{{ __('messages.boost_email_completed_subject') }}

{{ __('messages.hello') }} {{ $campaign->user?->name ?? '' }},

{{ __('messages.boost_email_completed_body') }}

{{ $event?->name ?? __('messages.deleted_event') }}
{{ __('messages.impressions') }}: {{ number_format($campaign->impressions) }}
{{ __('messages.reach') }}: {{ number_format($campaign->reach) }}
{{ __('messages.clicks') }}: {{ number_format($campaign->clicks) }}
@if ($campaign->conversions > 0)
{{ __('messages.conversions') }}: {{ $campaign->conversions }}
@endif
{{ __('messages.amount_spent') }}: {{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->actual_spend, 2) }}
@if ($refundAmount > 0)

{{ __('messages.boost_unspent_refund') }}
{{ __('messages.boost_refund_amount') }}: {{ $campaign->getCurrencySymbol() }}{{ number_format($refundAmount, 2) }}
@endif

{{ __('messages.view_results') }}: {{ $boostUrl }}
