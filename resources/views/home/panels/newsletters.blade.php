<div class="ap-card rounded-xl h-full flex flex-col overflow-hidden">
    <div class="dashboard-panel-header px-5 py-4">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('messages.panel_newsletters') }}</h3>
    </div>
    <div class="divide-y divide-gray-100 dark:divide-white/[0.06] flex-1">
        @forelse($latestNewsletters as $newsletter)
        <div class="px-5 py-3">
            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $newsletter->subject }}</p>
            <div class="flex items-center gap-3 mt-1">
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.sent') }}: {{ number_format($newsletter->sent_count ?? 0) }}</span>
                <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.opened') }}: {{ number_format($newsletter->open_count ?? 0) }}</span>
            </div>
        </div>
        @empty
        <div class="px-5 py-8 text-center">
            <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_newsletters_sent') }}</p>
        </div>
        @endforelse
    </div>
</div>
