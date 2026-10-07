{{-- One campaign in the list on /boost. The event's name is the way in to the campaign's page.
     Money is the campaign's own: the currency it was bought in, never the installation's. --}}
<tr>
    <td class="c-main c-strong">
        <a href="{{ route('boost.show', ['hash' => $campaign->hashedId()]) }}" class="event-link"><bdi>{{ $campaign->event?->translatedName() ?? __('messages.deleted_event') }}</bdi></a>
        <span class="c-sub">
            <bdi>{{ $campaign->role?->name ?? __('messages.deleted') }}</bdi>
            &middot; {{ $campaign->isNetwork() ? __('messages.promotion_channel_network') : __('messages.promotion_channel_meta') }}
            &middot; {{ $campaign->created_at->translatedFormat('M j, Y') }}
        </span>
    </td>
    <td>@include('boost.partials.status', ['status' => $campaign->status])</td>
    <td class="c-num" data-label="{{ __('messages.impressions') }}">{{ number_format($campaign->impressions) }}</td>
    <td class="c-num" data-label="{{ __('messages.clicks') }}">{{ number_format($campaign->clicks) }}</td>
    <td class="c-num" data-label="{{ __('messages.spend') }}">
        {{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->actual_spend ?? 0, 2) }}
        @if ($campaign->isActive() || $campaign->isPaused())
        <div class="boost-meter" aria-hidden="true"><i style="width: {{ $campaign->getBudgetUtilization() }}%"></i></div>
        @endif
    </td>
    <td class="c-num c-quiet" data-label="{{ __('messages.promotion_budget') }}">{{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->user_budget, 2) }}</td>
</tr>
