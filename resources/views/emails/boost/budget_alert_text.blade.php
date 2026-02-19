{{ __('messages.boost_email_budget_alert_subject') }}

{{ __('messages.hello') }} {{ $campaign->user?->name ?? '' }},

{{ __('messages.boost_budget_75_percent', ['event' => $event?->name ?? __('messages.deleted_event')]) }}

{{ __('messages.budget') }}: {{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->user_budget, 2) }}
{{ __('messages.amount_spent') }}: {{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->actual_spend, 2) }}
{{ __('messages.impressions') }}: {{ number_format($campaign->impressions) }}
{{ __('messages.clicks') }}: {{ number_format($campaign->clicks) }}

{{ __('messages.view_campaign') }}: {{ $boostUrl }}
