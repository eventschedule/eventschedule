<div class="ap-card rounded-xl h-full flex flex-col overflow-hidden">
    <div class="dashboard-panel-header px-5 py-4">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('messages.panel_boosts') }}</h3>
    </div>
    <div class="divide-y divide-gray-100 dark:divide-white/[0.06] flex-1">
        @forelse($boostCampaigns as $campaign)
        <div class="px-5 py-3">
            <div class="flex items-center gap-2 mb-1">
                <span class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $campaign->name }}</span>
                <span class="inline-flex items-center px-1.5 py-0.5 text-xs font-medium rounded-full flex-shrink-0 {{ $campaign->status === 'active' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                    {{ ucfirst($campaign->status) }}
                </span>
            </div>
            <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400">
                <span>{{ number_format($campaign->impressions ?? 0) }} {{ strtolower(__('messages.impressions')) }}</span>
                <span>{{ number_format($campaign->clicks ?? 0) }} {{ strtolower(__('messages.clicks')) }}</span>
                <span>${{ number_format($campaign->actual_spend ?? 0, 2) }}</span>
            </div>
        </div>
        @empty
        <div class="px-5 py-8 text-center">
            <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_active_boosts') }}</p>
        </div>
        @endforelse
    </div>
</div>
