{{ __('messages.boost_email_created_subject') }}

{{ __('messages.hello') }} {{ $campaign->user?->name ?? '' }},

{{ __('messages.boost_email_created_body') }}

{{ $event?->name ?? __('messages.deleted_event') }}
{{ __('messages.budget') }}: {{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->user_budget, 2) }}
{{ __('messages.total_charged') }}: {{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->getTotalCost(), 2) }}
@if ($campaign->scheduled_end)
{{ __('messages.ends') }}: {{ $campaign->scheduled_end->format('F j, Y') }}
@endif

{{ __('messages.view_campaign') }}: {{ $boostUrl }}
