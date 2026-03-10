<div class="ap-card rounded-xl p-6 h-full flex flex-col items-center">
    <div class="flex items-center gap-3 mb-3 self-stretch">
        <div class="dashboard-icon p-2 rounded-xl bg-sky-50 dark:bg-sky-500/10" style="--icon-glow: rgba(14, 165, 233, 0.15)">
            <svg class="w-5 h-5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        </div>
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.panel_views') }}</span>
        <div class="ml-auto w-20 h-10 flex-shrink-0">
            <canvas id="sparkline-chart" width="80" height="40"></canvas>
        </div>
    </div>
    <div class="flex-1 flex items-center justify-center gap-2">
        <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($views30d) }}</p>
        @if($viewsChange != 0)
        <span class="text-xs font-medium px-1.5 py-0.5 rounded-full {{ $viewsChange > 0 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
            {{ $viewsChange > 0 ? '+' : '' }}{{ $viewsChange }}%
        </span>
        @endif
    </div>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-auto">{{ __('messages.total_views') }} ({{ $panelSettings['views']['period'] ?? 30 }}d)</p>
</div>
