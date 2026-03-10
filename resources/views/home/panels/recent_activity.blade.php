<div class="ap-card rounded-xl h-full flex flex-col overflow-hidden">
    <div class="dashboard-panel-header px-5 py-4">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ __('messages.recent_activity') }}</h3>
    </div>
    <div class="divide-y divide-gray-100 dark:divide-white/[0.06] flex-1">
        @forelse($recentActivity as $activity)
        <div class="flex items-start gap-3 px-5 py-3">
            @if($activity['type'] === 'sale')
            <div class="activity-dot-green mt-1 w-2 h-2 rounded-full bg-green-500 flex-shrink-0"></div>
            <div class="min-w-0 flex-1">
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    {{ __('messages.new_ticket_sale') }}
                    @if($activity['description'])
                    <span class="font-medium text-gray-900 dark:text-white">{{ $activity['description'] }}</span>
                    @endif
                    @if(!empty($activity['amount']))
                    <span class="text-green-600 dark:text-green-400 font-medium">${{ number_format($activity['amount'], 2) }}</span>
                    @endif
                </p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $activity['date']->diffForHumans() }}</p>
            </div>
            @elseif($activity['type'] === 'follower')
            <div class="activity-dot-blue mt-1 w-2 h-2 rounded-full bg-blue-500 flex-shrink-0"></div>
            <div class="min-w-0 flex-1">
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    @if($activity['description'])
                    <span class="font-medium text-gray-900 dark:text-white">{{ $activity['description'] }}</span>
                    @endif
                    {{ __('messages.started_following') }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $activity['date']->diffForHumans() }}</p>
            </div>
            @elseif($activity['type'] === 'newsletter')
            <div class="activity-dot-amber mt-1 w-2 h-2 rounded-full bg-amber-500 flex-shrink-0"></div>
            <div class="min-w-0 flex-1">
                <p class="text-sm text-gray-700 dark:text-gray-300">
                    <span class="font-medium text-gray-900 dark:text-white">{{ $activity['description'] }}</span>
                    {{ __('messages.newsletter_sent_to') }} {{ $activity['sent_count'] ?? 0 }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">{{ $activity['date']->diffForHumans() }}</p>
            </div>
            @endif
        </div>
        @empty
        <div class="px-5 py-8 text-center">
            <svg class="w-8 h-8 text-gray-300 dark:text-gray-600 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_recent_activity') }}</p>
        </div>
        @endforelse
    </div>
</div>
