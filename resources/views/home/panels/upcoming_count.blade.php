<div class="dashboard-panel bg-white dark:bg-transparent rounded-2xl shadow-sm p-5 border border-gray-200 dark:border-transparent h-full flex flex-col items-center">
    <div class="flex items-center gap-3 mb-3 self-start min-h-[2.5rem]">
        <div class="dashboard-icon p-2 rounded-xl bg-blue-50 dark:bg-blue-500/10" style="--icon-glow: rgba(59, 130, 246, 0.15)">
            <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.panel_upcoming_count') }}</span>
    </div>
    <div class="flex-1 flex items-center justify-center">
        <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($upcomingCount) }}</p>
    </div>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-auto">{{ __('messages.upcoming_events') }}</p>
</div>
