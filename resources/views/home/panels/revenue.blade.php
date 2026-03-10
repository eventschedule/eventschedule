<div class="ap-card rounded-xl p-6 h-full flex flex-col items-center">
    <div class="flex items-center gap-3 mb-3 self-start min-h-[2.5rem]">
        <div class="dashboard-icon p-2 rounded-xl bg-green-50 dark:bg-green-500/10" style="--icon-glow: rgba(34, 197, 94, 0.15)">
            <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.panel_revenue') }}</span>
    </div>
    <div class="flex-1 flex items-center justify-center">
        <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white">${{ number_format($revenueStats['total_revenue'] ?? 0, 2) }}</p>
    </div>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-auto">{{ number_format($revenueStats['total_sales'] ?? 0) }} {{ strtolower(__('messages.sales')) }} ({{ $panelSettings['revenue']['period'] ?? 30 }}d)</p>
</div>
