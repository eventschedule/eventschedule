<div class="ap-card rounded-xl p-6 h-full flex flex-col items-center">
    <div class="flex items-center gap-3 mb-3 self-start min-h-[2.5rem]">
        <div class="dashboard-icon p-2 rounded-xl bg-green-50 dark:bg-green-500/10" style="--icon-glow: rgba(34, 197, 94, 0.15)">
            <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.panel_revenue') }}</span>
    </div>
    <div class="flex-1 flex items-center justify-center">
        @php
            $revenueByCurrency = $revenueStats['total_revenue_by_currency'] ?? [];
        @endphp
        @if (count($revenueByCurrency) > 1)
            <div class="text-center space-y-1">
                @foreach ($revenueByCurrency as $entry)
                    <p class="dashboard-stat-value text-xl font-bold text-gray-900 dark:text-white">{{ \App\Utils\MoneyUtils::format($entry['amount'], $entry['currency_code']) }}</p>
                @endforeach
            </div>
        @else
            <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white">{{ \App\Utils\MoneyUtils::format($revenueByCurrency[0]['amount'] ?? 0, $revenueByCurrency[0]['currency_code'] ?? ($revenueStats['primary_currency'] ?? ($defaultCurrency ?? 'USD'))) }}</p>
        @endif
    </div>
    {{-- All four stat-card footers reserve the same height, because the value above sits in a
         flex-1 box: a footer that grew an extra line here would shrink this card's box and lift
         its number off the baseline the row shares. This is the longest of the four footers, and
         at lg:grid-cols-4 a card is only ~108px of inner width, so it is what sets the number.
         3.75rem is three lines at text-sm - Russian needs all three there
         ("1 234 продажи ·" / "Последние 30" / "дней"). That is a bound sized against the longest
         shipped translation, not a proof; a longer string in a future locale would need more.
         mb_strtolower, not strtolower: the latter is byte-wise and mangles Turkish and Greek. --}}
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-auto min-h-[3.75rem] flex items-center justify-center text-center">{{ number_format($revenueStats['total_sales'] ?? 0) }} {{ mb_strtolower(__('messages.sales')) }} · {{ __('messages.last_'.$revenuePeriod.'_days') }}</p>
</div>
