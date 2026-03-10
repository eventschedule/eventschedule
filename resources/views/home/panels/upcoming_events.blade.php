<div class="dashboard-panel bg-white dark:bg-transparent rounded-2xl shadow-sm border border-gray-200 dark:border-transparent h-full flex flex-col overflow-hidden">
    <div class="dashboard-panel-header px-5 py-4">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('messages.upcoming_events') }}</h3>
    </div>
    <div class="divide-y divide-gray-100 dark:divide-white/[0.06] flex-1">
        @forelse($upcomingEvents as $event)
        @php $eventRole = $event->roles->first(); @endphp
        <a href="{{ $eventRole ? route('event.edit', ['subdomain' => $eventRole->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)]) : '#' }}" class="flex items-center gap-4 px-5 py-3 hover:bg-gray-50 dark:hover:bg-black/10 transition-colors">
            @if($event->getImageUrl())
            <img src="{{ $event->getImageUrl() }}" alt="" class="w-12 h-12 rounded-xl object-cover flex-shrink-0">
            @else
            <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-white/[0.06] flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            @endif
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $event->name }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $event->starts_at ? \Carbon\Carbon::parse($event->starts_at)->setTimezone($eventRole?->timezone ?? auth()->user()->timezone ?? 'UTC')->format('M j, g:i A') : '' }}
                    @if($eventRole)
                    <span class="mx-1">&middot;</span> {{ $eventRole->name }}
                    @endif
                </p>
            </div>
            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
        @empty
        <div class="px-5 py-8 text-center">
            <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_upcoming_events') }}</p>
            <a href="{{ route('event.create_default') }}" class="inline-block mt-3 text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">{{ __('messages.create_event') }}</a>
        </div>
        @endforelse
    </div>
</div>
