@if($size === 'half')
{{-- Compact summary card --}}
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700/50">
    <div class="flex items-center gap-3 mb-3">
        <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/30">
            <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.panel_stats') }}</span>
    </div>
    <div class="space-y-2">
        <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.upcoming_events') }}</span>
            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($dashboardStats['upcoming_count']) }}</span>
        </div>
        <div class="flex justify-between items-center">
            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.total_views') }} (30d)</span>
            <div class="flex items-center gap-1">
                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($dashboardStats['views_30d']) }}</span>
                @if($dashboardStats['views_change'] != 0)
                <span class="text-xs font-medium {{ $dashboardStats['views_change'] > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ $dashboardStats['views_change'] > 0 ? '+' : '' }}{{ $dashboardStats['views_change'] }}%
                </span>
                @endif
            </div>
        </div>
        <div class="flex justify-between items-center">
            @if($dashboardStats['followers_count'] > 0)
            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.followers') }}</span>
            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($dashboardStats['followers_count']) }}</span>
            @else
            <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.events') }}</span>
            <span class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($dashboardStats['total_events_count']) }}</span>
            @endif
        </div>
    </div>
</div>
@else
{{-- Full-width stat cards grid --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
    {{-- Upcoming Events --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700/50">
        <div class="flex items-center gap-3 mb-3">
            <div class="p-2 rounded-lg bg-blue-50 dark:bg-blue-900/30">
                <svg class="w-5 h-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            </div>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.upcoming_events') }}</span>
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($dashboardStats['upcoming_count']) }}</p>
    </div>

    {{-- Page Views (30d) with sparkline --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700/50 lg:col-span-2">
        <div class="flex items-start justify-between">
            <div>
                <div class="flex items-center gap-3 mb-3">
                    <div class="p-2 rounded-lg bg-sky-50 dark:bg-sky-900/30">
                        <svg class="w-5 h-5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    </div>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.total_views') }} (30d)</span>
                </div>
                <div class="flex items-baseline gap-2">
                    <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($dashboardStats['views_30d']) }}</p>
                    @if($dashboardStats['views_change'] != 0)
                    <span class="text-sm font-medium {{ $dashboardStats['views_change'] > 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $dashboardStats['views_change'] > 0 ? '+' : '' }}{{ $dashboardStats['views_change'] }}%
                    </span>
                    @endif
                </div>
            </div>
            <div class="w-24 h-12 flex-shrink-0">
                <canvas id="sparkline-chart" width="96" height="48"></canvas>
            </div>
        </div>
    </div>

    {{-- Followers or Total Events --}}
    @if($dashboardStats['followers_count'] > 0)
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700/50">
        <div class="flex items-center gap-3 mb-3">
            <div class="p-2 rounded-lg bg-green-50 dark:bg-green-900/30">
                <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.followers') }}</span>
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($dashboardStats['followers_count']) }}</p>
    </div>
    @else
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-5 border border-gray-200 dark:border-gray-700/50">
        <div class="flex items-center gap-3 mb-3">
            <div class="p-2 rounded-lg bg-green-50 dark:bg-green-900/30">
                <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.events') }}</span>
        </div>
        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($dashboardStats['total_events_count']) }}</p>
    </div>
    @endif
</div>
@endif
