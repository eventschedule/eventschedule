<x-app-admin-layout>

    <div class="space-y-4">
        {{-- Schedule Selector, Date Range, and Period Toggle --}}
        <div class="flex flex-col sm:flex-row sm:justify-between gap-4">
            <div class="flex gap-2 flex-wrap items-center">
                <div class="min-w-[200px]">
                    <select id="role-filter"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base">
                        <option value="">{{ __('messages.all_schedules') }}</option>
                        @foreach ($roles as $role)
                            <option value="{{ \App\Utils\UrlUtils::encodeId($role->id) }}" {{ $selectedRoleId == $role->id ? 'selected' : '' }}>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="min-w-[180px]">
                    <select id="date-range"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base">
                        <option value="last_7_days" {{ $range === 'last_7_days' ? 'selected' : '' }}>{{ __('messages.last_7_days') }}</option>
                        <option value="last_30_days" {{ $range === 'last_30_days' ? 'selected' : '' }}>{{ __('messages.last_30_days') }}</option>
                        <option value="last_90_days" {{ $range === 'last_90_days' ? 'selected' : '' }}>{{ __('messages.last_90_days') }}</option>
                        <option value="this_month" {{ $range === 'this_month' ? 'selected' : '' }}>{{ __('messages.this_month') }}</option>
                        <option value="last_month" {{ $range === 'last_month' ? 'selected' : '' }}>{{ __('messages.last_month') }}</option>
                        <option value="this_year" {{ $range === 'this_year' ? 'selected' : '' }}>{{ __('messages.this_year') }}</option>
                        <option value="all_time" {{ $range === 'all_time' ? 'selected' : '' }}>{{ __('messages.all_time') }}</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2 items-center">
                <a href="{{ route('analytics', ['role_id' => \App\Utils\UrlUtils::encodeId($selectedRoleId), 'period' => 'daily', 'range' => $range]) }}"
                    class="px-5 py-3 rounded-md text-base font-semibold leading-none flex items-center {{ $period === 'daily' ? 'bg-[#4E81FA] text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                    {{ __('messages.daily') }}
                </a>
                <a href="{{ route('analytics', ['role_id' => \App\Utils\UrlUtils::encodeId($selectedRoleId), 'period' => 'weekly', 'range' => $range]) }}"
                    class="px-5 py-3 rounded-md text-base font-semibold leading-none flex items-center {{ $period === 'weekly' ? 'bg-[#4E81FA] text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                    {{ __('messages.weekly') }}
                </a>
                <a href="{{ route('analytics', ['role_id' => \App\Utils\UrlUtils::encodeId($selectedRoleId), 'period' => 'monthly', 'range' => $range]) }}"
                    class="px-5 py-3 rounded-md text-base font-semibold leading-none flex items-center {{ $period === 'monthly' ? 'bg-[#4E81FA] text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                    {{ __('messages.monthly') }}
                </a>
            </div>
        </div>

        {{-- Stats Cards --}}
        @php
            $statsColumns = 2; // Total Views + Current Period
            if ($periodComparison) $statsColumns++; // Previous Period
            if ($periodComparison) $statsColumns++; // Comparison %
            if ($appearanceViews > 0) $statsColumns++; // Appearance Views
        @endphp
        <div class="grid grid-cols-2 lg:grid-cols-{{ $statsColumns }} gap-4">
            {{-- Total Views --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.total_views') }}</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalViews) }}</p>
                    </div>
                </div>
            </div>
            {{-- Views in Period --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.views_in_period') }}</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($periodComparison ? $periodComparison['current_period'] : $momComparison['this_month']) }}</p>
                    </div>
                </div>
            </div>
            @if ($periodComparison)
            {{-- Previous Period --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-full">
                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.views_previous_period') }}</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($periodComparison['previous_period']) }}</p>
                    </div>
                </div>
            </div>
            {{-- Change Percentage --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 {{ $periodComparison['percentage_change'] >= 0 ? 'bg-green-100 dark:bg-green-900' : 'bg-red-100 dark:bg-red-900' }} rounded-full">
                            @if ($periodComparison['percentage_change'] >= 0)
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                            </svg>
                            @else
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6" />
                            </svg>
                            @endif
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.' . $periodComparison['comparison_label']) }}</p>
                        <p class="text-2xl font-bold {{ $periodComparison['percentage_change'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $periodComparison['percentage_change'] >= 0 ? '+' : '' }}{{ $periodComparison['percentage_change'] }}%
                        </p>
                    </div>
                </div>
            </div>
            @endif
            @if ($appearanceViews > 0)
            {{-- Appearance Views --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1">
                            {{ __('messages.appearance_views') }}
                            <span class="relative group">
                                <svg class="w-4 h-4 text-gray-400 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                    {{ __('messages.views_from_other_schedules') }}
                                </span>
                            </span>
                        </p>
                        <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($appearanceViews) }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>

        {{-- Conversion Stats Cards --}}
        @if ($conversionStats['total_sales'] > 0)
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- Total Revenue --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.total_revenue') }}</p>
                        <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($conversionStats['total_revenue'], 2) }}</p>
                    </div>
                </div>
            </div>
            {{-- Conversion Rate --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1">
                            {{ __('messages.conversion_rate') }}
                            <span class="relative group">
                                <svg class="w-4 h-4 text-gray-400 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                    {{ __('messages.conversion_rate_tooltip') }}
                                </span>
                            </span>
                        </p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $conversionStats['conversion_rate'] }}%</p>
                    </div>
                </div>
            </div>
            {{-- Revenue per View --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-full">
                            <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1">
                            {{ __('messages.revenue_per_view') }}
                            <span class="relative group">
                                <svg class="w-4 h-4 text-gray-400 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                    {{ __('messages.revenue_per_view_tooltip') }}
                                </span>
                            </span>
                        </p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($conversionStats['revenue_per_view'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- Promo Code Stats --}}
        @if ($conversionStats['promo_sales'] > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.promo_codes') }}</h3>

            {{-- Summary cards --}}
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.promo_sales') }}</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white">
                        {{ number_format($conversionStats['promo_sales']) }}
                        <span class="text-sm font-normal text-gray-500 dark:text-gray-400">/ {{ number_format($conversionStats['total_sales']) }}</span>
                    </p>
                </div>
                <div class="bg-orange-50 dark:bg-orange-900/20 rounded-lg p-4">
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.total_discounts') }}</p>
                    <p class="text-xl font-bold text-orange-600 dark:text-orange-400">{{ number_format($conversionStats['promo_discounts'], 2) }}</p>
                </div>
            </div>

            {{-- Per-code table --}}
            @if ($promoCodeStats->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-2 font-medium">{{ __('messages.promo_code') }}</th>
                            <th class="pb-2 font-medium text-end">{{ __('messages.discount') }}</th>
                            <th class="pb-2 font-medium text-end">{{ __('messages.sales') }}</th>
                            <th class="pb-2 font-medium text-end">{{ __('messages.total_discounts') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($promoCodeStats as $stat)
                        <tr>
                            <td class="py-2 text-gray-900 dark:text-white font-mono">{{ $stat['code'] }}</td>
                            <td class="py-2 text-gray-500 dark:text-gray-400 text-end">
                                @if ($stat['type'] === 'percentage')
                                    {{ rtrim(rtrim(number_format($stat['value'], 3), '0'), '.') }}%
                                @else
                                    {{ rtrim(rtrim(number_format($stat['value'], 3), '0'), '.') }}
                                @endif
                            </td>
                            <td class="py-2 text-gray-900 dark:text-white text-end">{{ number_format($stat['sales_count']) }}</td>
                            <td class="py-2 text-orange-600 dark:text-orange-400 text-end">{{ number_format($stat['total_discount'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endif

        {{-- Boost Performance --}}
        @if ($boostStats['has_data'])
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.boost_funnel') }}</h3>

            {{-- Funnel visualization --}}
            <div class="flex flex-col md:flex-row items-stretch gap-0 mb-6">
                {{-- Impressions --}}
                <div class="flex-1 bg-gray-50 dark:bg-gray-700/50 rounded-lg md:rounded-r-none p-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('messages.impressions') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($boostStats['total_impressions']) }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">${{ number_format($boostStats['total_spend'], 2) }} {{ __('messages.total_spend') }}</p>
                </div>
                {{-- Arrow --}}
                <div class="hidden md:flex items-center text-gray-300 dark:text-gray-600 px-1">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </div>
                {{-- Clicks --}}
                <div class="flex-1 bg-gray-50 dark:bg-gray-700/50 rounded-lg md:rounded-none p-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('messages.clicks') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($boostStats['total_clicks']) }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        {{ number_format($boostStats['avg_ctr'], 1) }}% CTR
                        @if ($boostStats['avg_cpc'] > 0)
                            &middot; ${{ number_format($boostStats['avg_cpc'], 2) }}/{{ __('messages.click') }}
                        @endif
                    </p>
                </div>
                {{-- Arrow --}}
                <div class="hidden md:flex items-center text-gray-300 dark:text-gray-600 px-1">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </div>
                {{-- Page Views --}}
                <div class="flex-1 bg-gray-50 dark:bg-gray-700/50 rounded-lg md:rounded-none p-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('messages.page_views') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($boostStats['boost_views']) }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        @if ($boostStats['total_clicks'] > 0)
                            {{ number_format(($boostStats['boost_views'] / $boostStats['total_clicks']) * 100, 0) }}% {{ __('messages.landed') }}
                        @endif
                        @if ($boostStats['cost_per_view'] > 0)
                            &middot; ${{ number_format($boostStats['cost_per_view'], 2) }}/{{ __('messages.view') }}
                        @endif
                    </p>
                </div>
                {{-- Arrow --}}
                <div class="hidden md:flex items-center text-gray-300 dark:text-gray-600 px-1">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </div>
                {{-- Sales --}}
                <div class="flex-1 bg-gray-50 dark:bg-gray-700/50 rounded-lg md:rounded-l-none p-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('messages.sales') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($boostStats['boost_sales']) }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        @if ($boostStats['boost_views'] > 0 && $boostStats['boost_sales'] > 0)
                            {{ number_format(($boostStats['boost_sales'] / $boostStats['boost_views']) * 100, 1) }}% {{ __('messages.converted') }}
                        @endif
                        @if ($boostStats['cost_per_sale'] > 0)
                            &middot; ${{ number_format($boostStats['cost_per_sale'], 2) }}/{{ __('messages.sale') }}
                        @endif
                    </p>
                </div>
            </div>

            {{-- ROAS highlight --}}
            @if ($boostStats['boost_revenue'] > 0)
            <div class="flex items-center gap-4 mb-6 p-3 rounded-lg {{ $boostStats['roas'] >= 1 ? 'bg-green-50 dark:bg-green-900/20' : 'bg-amber-50 dark:bg-amber-900/20' }}">
                <div>
                    <p class="text-sm font-medium {{ $boostStats['roas'] >= 1 ? 'text-green-800 dark:text-green-300' : 'text-amber-800 dark:text-amber-300' }}">
                        {{ __('messages.roas') }}: {{ number_format($boostStats['roas'], 1) }}x
                    </p>
                    <p class="text-xs {{ $boostStats['roas'] >= 1 ? 'text-green-600 dark:text-green-400' : 'text-amber-600 dark:text-amber-400' }}">
                        ${{ number_format($boostStats['boost_revenue'], 2) }} {{ __('messages.revenue') }} / ${{ number_format($boostStats['total_spend'], 2) }} {{ __('messages.spend') }}
                    </p>
                </div>
            </div>
            @endif

            {{-- Campaigns table --}}
            @if (count($boostStats['campaigns']) > 0)
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.boost_campaigns_in_period') }}</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-2 font-medium">{{ __('messages.event') }}</th>
                            <th class="pb-2 font-medium">{{ __('messages.status') }}</th>
                            <th class="pb-2 font-medium text-end">{{ __('messages.spend') }}</th>
                            <th class="pb-2 font-medium text-end">{{ __('messages.impressions') }}</th>
                            <th class="pb-2 font-medium text-end">{{ __('messages.clicks') }}</th>
                            <th class="pb-2 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($boostStats['campaigns'] as $campaign)
                        <tr>
                            <td class="py-2 text-gray-900 dark:text-white max-w-[200px] truncate">{{ $campaign['event_name'] }}</td>
                            <td class="py-2">
                                @php
                                $badgeColors = match($campaign['status']) {
                                    'active' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                                    'paused' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300',
                                    'completed' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                    default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $badgeColors }}">
                                    {{ ucfirst($campaign['status']) }}
                                </span>
                            </td>
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">${{ number_format($campaign['spend'], 2) }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">{{ number_format($campaign['impressions']) }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">{{ number_format($campaign['clicks']) }}</td>
                            <td class="py-2">
                                <a href="{{ route('boost.show', ['hash' => $campaign['hash']]) }}" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">{{ __('messages.view_details') }}</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endif

        {{-- Newsletter Performance --}}
        @if ($newsletterStats['has_data'])
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.newsletter_funnel') }}</h3>

            {{-- Funnel visualization --}}
            <div class="flex flex-col md:flex-row items-stretch gap-0 mb-6">
                {{-- Sent --}}
                <div class="flex-1 bg-gray-50 dark:bg-gray-700/50 rounded-lg md:rounded-r-none p-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('messages.total_sent') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($newsletterStats['total_sent']) }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        {{ count($newsletterStats['campaigns']) }} {{ Str::lower(__('messages.newsletters')) }}
                    </p>
                </div>
                {{-- Arrow --}}
                <div class="hidden md:flex items-center text-gray-300 dark:text-gray-600 px-1">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </div>
                {{-- Opens --}}
                <div class="flex-1 bg-gray-50 dark:bg-gray-700/50 rounded-lg md:rounded-none p-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('messages.opens') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($newsletterStats['total_opens']) }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $newsletterStats['open_rate'] }}% {{ __('messages.open_rate') }}</p>
                </div>
                {{-- Arrow --}}
                <div class="hidden md:flex items-center text-gray-300 dark:text-gray-600 px-1">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </div>
                {{-- Clicks --}}
                <div class="flex-1 bg-gray-50 dark:bg-gray-700/50 rounded-lg md:rounded-none p-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('messages.clicks') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($newsletterStats['total_clicks']) }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $newsletterStats['click_rate'] }}% {{ __('messages.click_rate') }}</p>
                </div>
                {{-- Arrow --}}
                <div class="hidden md:flex items-center text-gray-300 dark:text-gray-600 px-1">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </div>
                {{-- Page Views --}}
                <div class="flex-1 bg-gray-50 dark:bg-gray-700/50 rounded-lg md:rounded-none p-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('messages.page_views') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($newsletterStats['newsletter_views']) }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        @if ($newsletterStats['total_clicks'] > 0)
                            {{ number_format(($newsletterStats['newsletter_views'] / $newsletterStats['total_clicks']) * 100, 0) }}% {{ __('messages.landed') }}
                        @endif
                    </p>
                </div>
                {{-- Arrow --}}
                <div class="hidden md:flex items-center text-gray-300 dark:text-gray-600 px-1">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/></svg>
                </div>
                {{-- Sales --}}
                <div class="flex-1 bg-gray-50 dark:bg-gray-700/50 rounded-lg md:rounded-l-none p-4 text-center">
                    <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">{{ __('messages.sales') }}</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($newsletterStats['newsletter_sales']) }}</p>
                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
                        @if ($newsletterStats['newsletter_revenue'] > 0)
                            ${{ number_format($newsletterStats['newsletter_revenue'], 2) }} {{ __('messages.revenue') }}
                        @endif
                    </p>
                </div>
            </div>

            {{-- Campaigns table --}}
            @if (count($newsletterStats['campaigns']) > 0)
            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">{{ __('messages.newsletters_in_period') }}</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="text-left text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                            <th class="pb-2 font-medium">{{ __('messages.subject') }}</th>
                            <th class="pb-2 font-medium">{{ __('messages.sent_at') }}</th>
                            <th class="pb-2 font-medium text-end">{{ __('messages.sent') }}</th>
                            <th class="pb-2 font-medium text-end">{{ __('messages.opens') }}</th>
                            <th class="pb-2 font-medium text-end">{{ __('messages.clicks') }}</th>
                            <th class="pb-2 font-medium"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach ($newsletterStats['campaigns'] as $campaign)
                        <tr>
                            <td class="py-2 text-gray-900 dark:text-white max-w-[200px] truncate">{{ $campaign['subject'] }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-300">{{ $campaign['sent_at']->format('M j, Y') }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">{{ number_format($campaign['sent_count']) }}</td>
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">
                                {{ number_format($campaign['open_count']) }}
                                @if ($campaign['sent_count'] > 0)
                                    <span class="text-xs text-gray-400">({{ number_format(($campaign['open_count'] / $campaign['sent_count']) * 100, 0) }}%)</span>
                                @endif
                            </td>
                            <td class="py-2 text-gray-700 dark:text-gray-300 text-end">
                                {{ number_format($campaign['click_count']) }}
                                @if ($campaign['sent_count'] > 0)
                                    <span class="text-xs text-gray-400">({{ number_format(($campaign['click_count'] / $campaign['sent_count']) * 100, 0) }}%)</span>
                                @endif
                            </td>
                            <td class="py-2">
                                <a href="{{ route('newsletter.stats', ['hash' => $campaign['hash']]) }}" class="text-blue-600 dark:text-blue-400 hover:underline text-xs">{{ __('messages.view_details') }}</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endif

        @if ($totalViews > 0 || $appearanceViews > 0)
            {{-- Charts Row --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- Views Over Time Chart --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.views_over_time') }}</h3>
                    <div class="h-64">
                        <canvas id="viewsChart"></canvas>
                    </div>
                </div>

                {{-- Device Breakdown Chart --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.device_breakdown') }}</h3>
                    <div class="h-64 flex items-center justify-center">
                        <canvas id="deviceChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- Bar Charts Grid --}}
            @if ($topEvents->isNotEmpty() || ($viewsBySchedule->isNotEmpty() && $viewsBySchedule->count() > 1) || $topAppearances->isNotEmpty() || $topSchedulesAppearedOn->isNotEmpty() || $topEventsByRevenue->isNotEmpty() || $trafficSources->isNotEmpty())
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                {{-- Top Events Chart --}}
                @if ($topEvents->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.top_events') }}</h3>
                    <div class="h-64">
                        <canvas id="topEventsChart"></canvas>
                    </div>
                </div>
                @endif

                {{-- Traffic Sources Chart --}}
                @if ($trafficSources->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.traffic_sources') }}</h3>
                    <div class="h-64 flex items-center justify-center">
                        <canvas id="trafficSourcesChart"></canvas>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2 text-xs text-gray-500 dark:text-gray-400">
                        <div class="flex items-start gap-1">
                            <span class="inline-block w-2 h-2 rounded-full mt-1 flex-shrink-0" style="background-color: #4E81FA;"></span>
                            <span><strong>{{ __('messages.direct') }}:</strong> {{ __('messages.direct_description') }}</span>
                        </div>
                        <div class="flex items-start gap-1">
                            <span class="inline-block w-2 h-2 rounded-full mt-1 flex-shrink-0" style="background-color: #10B981;"></span>
                            <span><strong>{{ __('messages.search') }}:</strong> {{ __('messages.search_description') }}</span>
                        </div>
                        <div class="flex items-start gap-1">
                            <span class="inline-block w-2 h-2 rounded-full mt-1 flex-shrink-0" style="background-color: #F59E0B;"></span>
                            <span><strong>{{ __('messages.social') }}:</strong> {{ __('messages.social_description') }}</span>
                        </div>
                        <div class="flex items-start gap-1">
                            <span class="inline-block w-2 h-2 rounded-full mt-1 flex-shrink-0" style="background-color: #EF4444;"></span>
                            <span><strong>{{ __('messages.email_source') }}:</strong> {{ __('messages.email_description') }}</span>
                        </div>
                        <div class="flex items-start gap-1">
                            <span class="inline-block w-2 h-2 rounded-full mt-1 flex-shrink-0" style="background-color: #8B5CF6;"></span>
                            <span><strong>{{ __('messages.newsletter_source') }}:</strong> {{ __('messages.newsletter_source_description') }}</span>
                        </div>
                        <div class="flex items-start gap-1">
                            <span class="inline-block w-2 h-2 rounded-full mt-1 flex-shrink-0" style="background-color: #F97316;"></span>
                            <span><strong>{{ __('messages.boost') }}:</strong> {{ __('messages.boost_traffic_desc') }}</span>
                        </div>
                        <div class="flex items-start gap-1">
                            <span class="inline-block w-2 h-2 rounded-full mt-1 flex-shrink-0" style="background-color: #F472B6;"></span>
                            <span><strong>{{ __('messages.promo_code') }}:</strong> {{ __('messages.promo_traffic_desc') }}</span>
                        </div>
                        <div class="flex items-start gap-1">
                            <span class="inline-block w-2 h-2 rounded-full mt-1 flex-shrink-0" style="background-color: #6B7280;"></span>
                            <span><strong>{{ __('messages.other') }}:</strong> {{ __('messages.other_description') }}</span>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Views by Schedule Chart --}}
                @if ($viewsBySchedule->isNotEmpty() && $viewsBySchedule->count() > 1)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.schedule_views') }}</h3>
                    <div class="h-64">
                        <canvas id="scheduleChart"></canvas>
                    </div>
                </div>
                @endif

                {{-- Top Referrers Chart --}}
                @if ($topReferrers->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.top_referrers') }}</h3>
                    <div class="h-64">
                        <canvas id="topReferrersChart"></canvas>
                    </div>
                </div>
                @endif

                {{-- Top Associated Talents/Venues Chart --}}
                @if ($topAppearances->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.top_associated_roles') }}</h3>
                    <div class="h-64">
                        <canvas id="appearancesChart"></canvas>
                    </div>
                </div>
                @endif

                {{-- Top Schedules You Appeared On Chart (for talents/venues) --}}
                @if ($topSchedulesAppearedOn->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.top_schedules_appeared_on') }}</h3>
                    <div class="h-64">
                        <canvas id="schedulesAppearedOnChart"></canvas>
                    </div>
                </div>
                @endif

                {{-- Top Events by Revenue Chart --}}
                @if ($topEventsByRevenue->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.top_events_by_revenue') }}</h3>
                    <div class="h-64">
                        <canvas id="topEventsByRevenueChart"></canvas>
                    </div>
                </div>
                @endif
            </div>
            @endif

        @else
            {{-- No Data State --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900 dark:text-white">{{ __('messages.no_analytics_data') }}</h3>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('messages.analytics_data_will_appear') }}
                </p>
            </div>
        @endif
    </div>

    {{-- Chart.js --}}
    <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>

    <script {!! nonce_attr() !!}>
        document.getElementById('role-filter').addEventListener('change', function() {
            var url = new URL(window.location.href);
            if (this.value) {
                url.searchParams.set('role_id', this.value);
            } else {
                url.searchParams.delete('role_id');
            }
            window.location.href = url.toString();
        });

        document.getElementById('date-range').addEventListener('change', function() {
            var url = new URL(window.location.href);
            url.searchParams.set('range', this.value);
            window.location.href = url.toString();
        });

        @if ($totalViews > 0 || $appearanceViews > 0)
        function initCharts() {
            if (typeof Chart === 'undefined') {
                setTimeout(initCharts, 50);
                return;
            }

            // Dark mode detection
            const isDarkMode = document.documentElement.classList.contains('dark') ||
                (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches && !document.documentElement.classList.contains('light'));

            const textColor = isDarkMode ? '#9CA3AF' : '#6B7280';
            const gridColor = isDarkMode ? '#2d2d30' : '#E5E7EB';

            // Views Over Time Chart
            const viewsCtx = document.getElementById('viewsChart').getContext('2d');
            const viewsLabels = @json($viewsByPeriod->pluck('period')->toArray());
            const viewsDatasets = [{
                label: @json(__('messages.views')),
                data: @json($viewsByPeriod->pluck('view_count')->toArray()),
                borderColor: '#4E81FA',
                backgroundColor: 'rgba(78, 129, 250, 0.1)',
                fill: true,
                tension: 0.3
            }];

            @if ($boostViewsByPeriod->isNotEmpty())
            // Build boost views data aligned to the same labels
            const boostViewsMap = @json($boostViewsByPeriod->pluck('view_count', 'period')->toArray());
            const boostViewsData = viewsLabels.map(label => boostViewsMap[label] || 0);
            if (boostViewsData.some(v => v > 0)) {
                viewsDatasets.push({
                    label: @json(__('messages.boost') . ' ' . __('messages.views')),
                    data: boostViewsData,
                    borderColor: '#F97316',
                    backgroundColor: 'rgba(249, 115, 22, 0.1)',
                    fill: true,
                    tension: 0.3,
                    borderDash: [5, 5]
                });
            }
            @endif

            @if ($newsletterViewsByPeriod->isNotEmpty())
            // Build newsletter views data aligned to the same labels
            const newsletterViewsMap = @json($newsletterViewsByPeriod->pluck('view_count', 'period')->toArray());
            const newsletterViewsData = viewsLabels.map(label => newsletterViewsMap[label] || 0);
            if (newsletterViewsData.some(v => v > 0)) {
                viewsDatasets.push({
                    label: @json(__('messages.newsletter_source') . ' ' . __('messages.views')),
                    data: newsletterViewsData,
                    borderColor: '#8B5CF6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    fill: true,
                    tension: 0.3,
                    borderDash: [5, 5]
                });
            }
            @endif

            new Chart(viewsCtx, {
            type: 'line',
            data: {
                labels: viewsLabels,
                datasets: viewsDatasets
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: viewsDatasets.length > 1,
                        labels: {
                            color: textColor
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor,
                            precision: 0
                        }
                    }
                }
            }
        });

        // Device Breakdown Chart
        const deviceCtx = document.getElementById('deviceChart').getContext('2d');
        const deviceData = @json($deviceBreakdown->toArray());
        const deviceLabels = {
            'desktop': @json(__('messages.desktop') ?? 'Desktop'),
            'mobile': @json(__('messages.mobile') ?? 'Mobile'),
            'tablet': @json(__('messages.tablet') ?? 'Tablet'),
            'unknown': @json(__('messages.other'))
        };
        new Chart(deviceCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(deviceData).map(k => deviceLabels[k] || k.charAt(0).toUpperCase() + k.slice(1)),
                datasets: [{
                    data: Object.values(deviceData),
                    backgroundColor: ['#4E81FA', '#10B981', '#8B5CF6', '#6B7280']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor
                        }
                    }
                }
            }
        });

        @if ($topEvents->isNotEmpty())
        // Top Events Chart
        const topEventsCtx = document.getElementById('topEventsChart').getContext('2d');
        new Chart(topEventsCtx, {
            type: 'bar',
            data: {
                labels: @json($topEvents->pluck('event.name')->toArray()),
                datasets: [{
                    label: @json(__('messages.views')),
                    data: @json($topEvents->pluck('view_count')->toArray()),
                    backgroundColor: '#4E81FA'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor,
                            precision: 0
                        }
                    },
                    y: {
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor
                        }
                    }
                }
            }
        });
        @endif

        @if ($viewsBySchedule->isNotEmpty() && $viewsBySchedule->count() > 1)
        // Schedule Views Chart
        const scheduleCtx = document.getElementById('scheduleChart').getContext('2d');
        new Chart(scheduleCtx, {
            type: 'bar',
            data: {
                labels: @json($viewsBySchedule->pluck('role.name')->toArray()),
                datasets: [{
                    label: @json(__('messages.views')),
                    data: @json($viewsBySchedule->pluck('view_count')->toArray()),
                    backgroundColor: '#10B981'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor,
                            precision: 0
                        }
                    }
                }
            }
        });
        @endif

        @if ($topAppearances->isNotEmpty())
        // Top Associated Roles Chart
        const appearancesCtx = document.getElementById('appearancesChart').getContext('2d');
        new Chart(appearancesCtx, {
            type: 'bar',
            data: {
                labels: @json($topAppearances->map(fn($item) => $item['role']->translatedName())->toArray()),
                datasets: [{
                    label: @json(__('messages.views')),
                    data: @json($topAppearances->pluck('view_count')->toArray()),
                    backgroundColor: '#8B5CF6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor,
                            precision: 0
                        }
                    },
                    y: {
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor
                        }
                    }
                }
            }
        });
        @endif

        @if ($topSchedulesAppearedOn->isNotEmpty())
        // Schedules Appeared On Chart
        const schedulesAppearedOnCtx = document.getElementById('schedulesAppearedOnChart').getContext('2d');
        new Chart(schedulesAppearedOnCtx, {
            type: 'bar',
            data: {
                labels: @json($topSchedulesAppearedOn->map(fn($item) => $item['role']->name)->toArray()),
                datasets: [{
                    label: @json(__('messages.views')),
                    data: @json($topSchedulesAppearedOn->pluck('view_count')->toArray()),
                    backgroundColor: '#EC4899'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor,
                            precision: 0
                        }
                    },
                    y: {
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor
                        }
                    }
                }
            }
        });
        @endif

        @if ($trafficSources->isNotEmpty())
        // Traffic Sources Chart
        const trafficSourcesCtx = document.getElementById('trafficSourcesChart').getContext('2d');
        const sourceLabels = {
            'direct': @json(__('messages.direct')),
            'search': @json(__('messages.search')),
            'social': @json(__('messages.social')),
            'email': @json(__('messages.email')),
            'newsletter': @json(__('messages.newsletter_source')),
            'boost': @json(__('messages.boost')),
            'promo': @json(__('messages.promo_code')),
            'other': @json(__('messages.other'))
        };
        const sourceColors = {
            'direct': '#4E81FA',
            'search': '#10B981',
            'social': '#F59E0B',
            'email': '#EF4444',
            'newsletter': '#8B5CF6',
            'boost': '#F97316',
            'promo': '#F472B6',
            'other': '#6B7280'
        };
        const trafficData = @json($trafficSources->toArray());
        new Chart(trafficSourcesCtx, {
            type: 'doughnut',
            data: {
                labels: trafficData.map(item => sourceLabels[item.source] || item.source),
                datasets: [{
                    data: trafficData.map(item => item.view_count),
                    backgroundColor: trafficData.map(item => sourceColors[item.source] || '#6B7280')
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            color: textColor
                        }
                    }
                }
            }
        });
        @endif

        @if ($topReferrers->isNotEmpty())
        // Top Referrers Chart
        const topReferrersCtx = document.getElementById('topReferrersChart').getContext('2d');
        new Chart(topReferrersCtx, {
            type: 'bar',
            data: {
                labels: @json($topReferrers->pluck('domain')->toArray()),
                datasets: [{
                    label: @json(__('messages.views')),
                    data: @json($topReferrers->pluck('view_count')->toArray()),
                    backgroundColor: '#F59E0B'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor,
                            precision: 0
                        }
                    },
                    y: {
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor
                        }
                    }
                }
            }
        });
        @endif

        @if ($topEventsByRevenue->isNotEmpty())
        // Top Events by Revenue Chart
        const topEventsByRevenueCtx = document.getElementById('topEventsByRevenueChart').getContext('2d');
        new Chart(topEventsByRevenueCtx, {
            type: 'bar',
            data: {
                labels: @json($topEventsByRevenue->pluck('event.name')->toArray()),
                datasets: [{
                    label: @json(__('messages.revenue')),
                    data: @json($topEventsByRevenue->pluck('revenue')->toArray()),
                    backgroundColor: '#10B981'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor
                        }
                    },
                    y: {
                        grid: {
                            color: gridColor
                        },
                        ticks: {
                            color: textColor
                        }
                    }
                }
            }
        });
        @endif
        }

        // Initialize charts when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCharts);
        } else {
            initCharts();
        }
        @endif
    </script>

</x-app-admin-layout>
