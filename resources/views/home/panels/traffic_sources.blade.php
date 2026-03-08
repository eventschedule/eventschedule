<div class="dashboard-panel bg-white dark:bg-transparent rounded-xl shadow-sm border border-gray-200 dark:border-transparent h-full flex flex-col">
    <div class="px-5 py-4 border-b border-gray-200 dark:border-white/[0.06]">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-xl bg-teal-50 dark:bg-teal-500/10">
                <svg class="w-5 h-5 text-teal-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('messages.panel_traffic_sources') }} ({{ $panelSettings['traffic_sources']['period'] ?? 30 }}d)</h3>
        </div>
    </div>
    <div class="divide-y divide-gray-100 dark:divide-white/[0.06] flex-1">
        @forelse($trafficSources as $source)
        <div class="flex items-center justify-between px-5 py-3">
            <span class="text-sm text-gray-700 dark:text-gray-300 truncate mr-3">{{ $source['domain'] }}</span>
            <span class="text-sm font-semibold text-gray-900 dark:text-white flex-shrink-0">{{ number_format($source['view_count']) }} {{ strtolower(__('messages.views')) }}</span>
        </div>
        @empty
        <div class="px-5 py-8 text-center">
            <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_data_available') }}</p>
        </div>
        @endforelse
    </div>
</div>
