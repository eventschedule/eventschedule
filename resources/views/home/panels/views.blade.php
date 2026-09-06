<div class="ap-card rounded-xl p-6 h-full flex flex-col items-center">
    {{-- min-h-[2.5rem] matches the other three stat cards' header rows, which is what keeps the
         four big numbers on one baseline. It used to come for free from the h-10 sparkline;
         once that is hidden below xl the row would otherwise collapse and drop this card's
         number below its neighbours'. self-stretch (not self-start) so ms-auto has a full-width
         row to push the sparkline to the end of. --}}
    <div class="flex items-center gap-3 mb-3 self-stretch min-h-[2.5rem]">
        <div class="dashboard-icon p-2 rounded-xl bg-sky-50 dark:bg-sky-500/10" style="--icon-glow: rgba(14, 165, 233, 0.15)">
            <svg class="w-5 h-5 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
        </div>
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.panel_views') }}</span>
        {{-- ms-auto, not ml-auto: in RTL the sparkline otherwise hugs the label instead of
             sitting at the far edge. Hidden below xl because at lg:grid-cols-4 the card is
             ~156px wide (108px inner) while this header needs 36px icon + gap + an 80px canvas
             that cannot shrink - and .ap-card sets no overflow, so it spills past the rounded
             edge from roughly 1024 to 1100px. --}}
        <div class="ms-auto hidden xl:block w-20 h-10 flex-shrink-0">
            <canvas id="sparkline-chart" width="80" height="40"></canvas>
        </div>
    </div>
    <div class="flex-1 flex items-center justify-center gap-2">
        <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($viewsInPeriod) }}</p>
        @if($viewsChange != 0)
        {{-- The comparison label comes from AnalyticsService::getPeriodComparison(), so it always
             names the window this number was actually measured against. The arrow is there because
             colour and a sign alone do not convey direction. --}}
        <span title="{{ $viewsChangeLabel ? __('messages.'.$viewsChangeLabel) : '' }}"
            class="text-xs font-medium px-1.5 py-0.5 rounded-full {{ $viewsChange > 0 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}">
            <span aria-hidden="true">{{ $viewsChange > 0 ? '▲' : '▼' }}</span>{{ $viewsChange > 0 ? '+' : '' }}{{ $viewsChange }}%
            @if ($viewsChangeLabel)
                <span class="sr-only">{{ __('messages.'.$viewsChangeLabel) }}</span>
            @endif
        </span>
        @endif
    </div>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-auto min-h-[3.75rem] flex items-center justify-center text-center">{{ __('messages.last_'.$viewsPeriod.'_days') }}</p>
</div>
