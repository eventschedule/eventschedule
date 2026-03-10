<div class="dashboard-panel bg-white dark:bg-transparent rounded-2xl shadow-sm border border-gray-200 dark:border-transparent h-full flex flex-col overflow-hidden">
    <div class="dashboard-panel-header px-5 py-4">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('messages.panel_top_events') }} ({{ $panelSettings['top_events']['period'] ?? 30 }}d)</h3>
    </div>
    <div class="divide-y divide-gray-100 dark:divide-white/[0.06] flex-1">
        @forelse($topEvents as $item)
        @php $eventRole = $item['event']->roles->first(); @endphp
        <div class="flex items-center justify-between px-5 py-3">
            <span class="text-sm text-gray-700 dark:text-gray-300 truncate mr-3">{{ $item['event']->name ?? '' }}</span>
            <div class="flex items-center gap-2 flex-shrink-0">
                <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ number_format($item['view_count']) }} {{ strtolower(__('messages.views')) }}</span>
                @if($eventRole)
                <a href="{{ route('event.edit', ['subdomain' => $eventRole->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($item['event']->id)]) }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors" title="{{ __('messages.edit') }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                </a>
                <a href="{{ $item['event']->getGuestUrl() }}" target="_blank" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors" title="{{ __('messages.view') }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
                @endif
            </div>
        </div>
        @empty
        <div class="px-5 py-8 text-center">
            <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_data_available') }}</p>
        </div>
        @endforelse
    </div>
</div>
