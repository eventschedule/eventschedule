<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700/50 h-full flex flex-col items-center">
    <div class="flex items-center gap-3 mb-3 self-start min-h-[2.5rem]">
        @if($followersCount > 0)
        <div class="p-2 rounded-lg bg-green-50 dark:bg-green-900/30">
            <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.panel_followers') }}</span>
        @else
        <div class="p-2 rounded-lg bg-green-50 dark:bg-green-900/30">
            <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
        </div>
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.panel_followers') }}</span>
        @endif
    </div>
    @if($followersCount > 0)
    <div class="flex-1 flex items-center justify-center">
        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($followersCount) }}</p>
    </div>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-auto">{{ __('messages.followers') }}</p>
    @else
    <div class="flex-1 flex items-center justify-center">
        <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalEventsCount) }}</p>
    </div>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-auto">{{ __('messages.events') }}</p>
    @endif
</div>
