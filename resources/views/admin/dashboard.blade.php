<x-app-admin-layout>

    <div class="space-y-6">
        {{-- Admin Navigation Tabs --}}
        <div class="border-b border-gray-200 dark:border-gray-700">
            <div class="flex justify-between items-center">
                <nav class="-mb-px flex space-x-8">
                    <a href="{{ route('admin.dashboard') }}"
                        class="whitespace-nowrap border-b-2 border-[#4E81FA] px-1 pb-4 text-base font-medium text-[#4E81FA]">
                        Dashboard
                    </a>
                    @if (config('app.hosted') || config('app.is_nexus'))
                    <a href="{{ route('admin.plans') }}"
                        class="whitespace-nowrap border-b-2 border-transparent px-1 pb-4 text-base font-medium text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300">
                        Plans
                    </a>
                    @endif
                    @if (!config('app.hosted') || config('app.is_nexus'))
                    <a href="{{ route('blog.admin.index') }}"
                        class="whitespace-nowrap border-b-2 border-transparent px-1 pb-4 text-base font-medium text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300">
                        Blog
                    </a>
                    @endif
                </nav>
                <button onclick="window.location.reload()" class="mb-4 inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Refresh
                </button>
            </div>
        </div>

        {{-- Date Range and Refresh --}}
        <div class="flex flex-col sm:flex-row sm:justify-between gap-4">
            <div class="flex gap-2 flex-wrap items-center">
                <div class="min-w-[180px]">
                    <select id="date-range" onchange="filterByDateRange(this.value)"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base">
                        <option value="last_7_days" {{ $range === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="last_30_days" {{ $range === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="last_90_days" {{ $range === 'last_90_days' ? 'selected' : '' }}>Last 90 Days</option>
                        <option value="all_time" {{ $range === 'all_time' ? 'selected' : '' }}>All Time</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Key Metrics Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- Total Users --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalUsers) }}</p>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="{{ $usersChangePercent >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $usersChangePercent >= 0 ? '+' : '' }}{{ $usersChangePercent }}%
                    </span>
                    <span class="text-gray-500 dark:text-gray-400 ml-2">
                        +{{ number_format($usersInPeriod) }} in period
                    </span>
                </div>
            </div>

            {{-- Total Schedules --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Schedules</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalSchedules) }}</p>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="{{ $schedulesChangePercent >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $schedulesChangePercent >= 0 ? '+' : '' }}{{ $schedulesChangePercent }}%
                    </span>
                    <span class="text-gray-500 dark:text-gray-400 ml-2">
                        +{{ number_format($schedulesInPeriod) }} in period
                    </span>
                </div>
            </div>

            {{-- Total Events --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                            <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Events</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalEvents) }}</p>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="{{ $eventsChangePercent >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $eventsChangePercent >= 0 ? '+' : '' }}{{ $eventsChangePercent }}%
                    </span>
                    <span class="text-gray-500 dark:text-gray-400 ml-2">
                        +{{ number_format($eventsInPeriod) }} in period
                    </span>
                </div>
            </div>
        </div>

        {{-- Additional Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Users (7 days)</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($activeUsers7Days) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Users (30 days)</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($activeUsers30Days) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Avg Events per Schedule</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $avgEventsPerSchedule }}</p>
            </div>
        </div>

        {{-- User Signup Method Breakdown --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Signup Method Donut Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Signup Method Breakdown (All Time)</h3>
                <div class="flex items-center gap-6">
                    <div class="w-48 h-48">
                        <canvas id="signupMethodChart"></canvas>
                    </div>
                    <div class="flex-1 space-y-3">
                        @php
                            $signupTotal = $emailUsers + $googleUsers + $hybridUsers;
                            $emailPercent = $signupTotal > 0 ? round(($emailUsers / $signupTotal) * 100, 1) : 0;
                            $googlePercent = $signupTotal > 0 ? round(($googleUsers / $signupTotal) * 100, 1) : 0;
                            $hybridPercent = $signupTotal > 0 ? round(($hybridUsers / $signupTotal) * 100, 1) : 0;
                        @endphp
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Email</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($emailUsers) }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ml-1">({{ $emailPercent }}%)</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Google</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($googleUsers) }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ml-1">({{ $googlePercent }}%)</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-full bg-amber-500 mr-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Hybrid</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($hybridUsers) }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ml-1">({{ $hybridPercent }}%)</span>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Hybrid = Email signup + Google connected
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Signup Method in Period --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Signups by Method (Selected Period)</h3>
                <div class="h-48">
                    <canvas id="signupMethodTrendChart"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-4 text-center">
                    @php
                        $periodTotal = $emailUsersInPeriod + $googleUsersInPeriod + $hybridUsersInPeriod;
                    @endphp
                    <div>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($emailUsersInPeriod) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Email</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($googleUsersInPeriod) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Google</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($hybridUsersInPeriod) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Hybrid</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Revenue & Sales --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</p>
                <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">${{ number_format($totalRevenue, 2) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">+${{ number_format($revenueInPeriod, 2) }} in period</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Sales</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalSales) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">+{{ number_format($salesInPeriod) }} in period</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Refund Rate</p>
                <p class="mt-2 text-2xl font-bold {{ $refundRate > 5 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ $refundRate }}%</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pending Revenue</p>
                <p class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">${{ number_format($pendingRevenue, 2) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($pendingSales) }} pending sales</p>
            </div>
        </div>

        {{-- Traffic & Analytics --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Device Breakdown --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Device Breakdown (Selected Period)</h3>
                <div class="flex items-center gap-6">
                    <div class="w-48 h-48">
                        <canvas id="deviceChart"></canvas>
                    </div>
                    <div class="flex-1 space-y-3">
                        @php
                            $deviceTotal = $desktopViews + $mobileViews + $tabletViews;
                            $desktopPercent = $deviceTotal > 0 ? round(($desktopViews / $deviceTotal) * 100, 1) : 0;
                            $mobilePercent = $deviceTotal > 0 ? round(($mobileViews / $deviceTotal) * 100, 1) : 0;
                            $tabletPercent = $deviceTotal > 0 ? round(($tabletViews / $deviceTotal) * 100, 1) : 0;
                        @endphp
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Desktop</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($desktopViews) }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ml-1">({{ $desktopPercent }}%)</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Mobile</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($mobileViews) }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ml-1">({{ $mobilePercent }}%)</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-full bg-purple-500 mr-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">Tablet</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($tabletViews) }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ml-1">({{ $tabletPercent }}%)</span>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Total: {{ number_format($totalPageViews) }} page views
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Traffic Sources --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Traffic Sources (Selected Period)</h3>
                <div class="h-48">
                    <canvas id="trafficSourcesChart"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-5 gap-2 text-center">
                    <div>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($directViews) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Direct</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($searchViews) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Search</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($socialViews) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Social</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($emailViews) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Email</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($otherViews) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Other</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Feature Adoption --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Feature Adoption</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Google Calendar</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $googleCalendarPercent }}% ({{ number_format($googleCalendarEnabled) }} schedules)</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ min($googleCalendarPercent, 100) }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Stripe Payments</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $stripePercent }}% ({{ number_format($stripeEnabled) }} schedules)</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ min($stripePercent, 100) }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Custom Domain</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $customDomainPercent }}% ({{ number_format($customDomainEnabled) }} schedules)</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-purple-600 h-2.5 rounded-full" style="width: {{ min($customDomainPercent, 100) }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Custom CSS</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $customCssPercent }}% ({{ number_format($customCssEnabled) }} schedules)</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-amber-600 h-2.5 rounded-full" style="width: {{ min($customCssPercent, 100) }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        @if (config('app.hosted'))
        {{-- Subscription Health --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Subscriptions</p>
                <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($activeSubscriptions) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">On Free Trial</p>
                <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($rolesOnTrial) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Converted from Trial</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($convertedFromTrial) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Past Due</p>
                <p class="mt-2 text-2xl font-bold {{ $pastDueSubscriptions > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ number_format($pastDueSubscriptions) }}</p>
            </div>
        </div>
        @endif

        {{-- Charts Row --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Growth Trends Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Growth Trends</h3>
                <div class="h-64">
                    <canvas id="trendsChart"></canvas>
                </div>
            </div>

            {{-- Top Schedules by Events --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Top Schedules by Events</h3>
                <div class="h-64">
                    <canvas id="topSchedulesChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Recent Schedules --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recent Schedules</h3>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($recentSchedules as $schedule)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <a href="{{ route('role.view_guest', ['subdomain' => $schedule->subdomain]) }}" target="_blank" class="text-sm font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 truncate block">
                                        {{ $schedule->name }}
                                    </a>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ ucfirst($schedule->type) }} &bull; {{ $schedule->subdomain }}
                                    </p>
                                </div>
                                <div class="text-right text-sm text-gray-500 dark:text-gray-400">
                                    <p>{{ $schedule->created_at->diffForHumans() }}</p>
                                    @if ($schedule->owner())
                                        <p class="text-xs">by {{ $schedule->owner()->name }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No schedules yet
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Events --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">Recent Events</h3>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($recentEvents as $event)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    @php($viewableRole = $event->getViewableRole())
                                    @if ($viewableRole)
                                        <a href="{{ $event->getGuestUrl($viewableRole->subdomain) }}" target="_blank" class="text-sm font-medium text-gray-900 dark:text-white hover:text-indigo-600 dark:hover:text-indigo-400 truncate block">
                                            {{ $event->name }}
                                        </a>
                                    @else
                                        <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                            {{ $event->name }}
                                        </p>
                                    @endif
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        @if ($viewableRole)
                                            {{ $viewableRole->name }}
                                        @else
                                            No schedule
                                        @endif
                                    </p>
                                </div>
                                <div class="text-right text-sm text-gray-500 dark:text-gray-400">
                                    <p>{{ $event->created_at->diffForHumans() }}</p>
                                    @if ($event->starts_at)
                                        <p class="text-xs">{{ \Carbon\Carbon::parse($event->starts_at)->format('M d, Y') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            No events yet
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Chart.js --}}
    <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>

    <script {!! nonce_attr() !!}>
        function filterByDateRange(range) {
            const url = new URL(window.location.href);
            url.searchParams.set('range', range);
            window.location.href = url.toString();
        }

        function initCharts() {
            if (typeof Chart === 'undefined') {
                setTimeout(initCharts, 50);
                return;
            }

            // Dark mode detection
            const isDarkMode = document.documentElement.classList.contains('dark') ||
                (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches && !document.documentElement.classList.contains('light'));

            const textColor = isDarkMode ? '#9CA3AF' : '#6B7280';
            const gridColor = isDarkMode ? '#374151' : '#E5E7EB';

            // Growth Trends Chart
            const trendsCtx = document.getElementById('trendsChart').getContext('2d');
            new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($trendData['labels']) !!},
                    datasets: [
                        {
                            label: 'Users',
                            data: {!! json_encode($trendData['users']) !!},
                            borderColor: '#4E81FA',
                            backgroundColor: 'rgba(78, 129, 250, 0.1)',
                            fill: false,
                            tension: 0.3
                        },
                        {
                            label: 'Schedules',
                            data: {!! json_encode($trendData['schedules']) !!},
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: false,
                            tension: 0.3
                        },
                        {
                            label: 'Events',
                            data: {!! json_encode($trendData['events']) !!},
                            borderColor: '#8B5CF6',
                            backgroundColor: 'rgba(139, 92, 246, 0.1)',
                            fill: false,
                            tension: 0.3
                        }
                    ]
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

            // Signup Method Donut Chart
            const signupMethodCtx = document.getElementById('signupMethodChart').getContext('2d');
            new Chart(signupMethodCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Email', 'Google', 'Hybrid'],
                    datasets: [{
                        data: [{{ $emailUsers }}, {{ $googleUsers }}, {{ $hybridUsers }}],
                        backgroundColor: ['#3B82F6', '#EF4444', '#F59E0B'],
                        borderColor: isDarkMode ? '#1F2937' : '#FFFFFF',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Signup Method Trend Chart (Stacked Bar)
            const signupMethodTrendCtx = document.getElementById('signupMethodTrendChart').getContext('2d');
            new Chart(signupMethodTrendCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($trendData['labels']) !!},
                    datasets: [
                        {
                            label: 'Email',
                            data: {!! json_encode($trendData['emailUsers']) !!},
                            backgroundColor: '#3B82F6',
                            stack: 'signups'
                        },
                        {
                            label: 'Google',
                            data: {!! json_encode($trendData['googleUsers']) !!},
                            backgroundColor: '#EF4444',
                            stack: 'signups'
                        },
                        {
                            label: 'Hybrid',
                            data: {!! json_encode($trendData['hybridUsers']) !!},
                            backgroundColor: '#F59E0B',
                            stack: 'signups'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                color: textColor,
                                boxWidth: 12,
                                padding: 8
                            }
                        }
                    },
                    scales: {
                        x: {
                            stacked: true,
                            grid: {
                                color: gridColor
                            },
                            ticks: {
                                color: textColor
                            }
                        },
                        y: {
                            stacked: true,
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
            new Chart(deviceCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Desktop', 'Mobile', 'Tablet'],
                    datasets: [{
                        data: [{{ $desktopViews }}, {{ $mobileViews }}, {{ $tabletViews }}],
                        backgroundColor: ['#3B82F6', '#10B981', '#8B5CF6'],
                        borderColor: isDarkMode ? '#1F2937' : '#FFFFFF',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            });

            // Traffic Sources Chart
            const trafficSourcesCtx = document.getElementById('trafficSourcesChart').getContext('2d');
            new Chart(trafficSourcesCtx, {
                type: 'bar',
                data: {
                    labels: ['Direct', 'Search', 'Social', 'Email', 'Other'],
                    datasets: [{
                        label: 'Views',
                        data: [{{ $directViews }}, {{ $searchViews }}, {{ $socialViews }}, {{ $emailViews }}, {{ $otherViews }}],
                        backgroundColor: ['#6366F1', '#10B981', '#F59E0B', '#EF4444', '#6B7280']
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

            // Top Schedules Chart
            @if ($topSchedulesByEvents->isNotEmpty())
            const topSchedulesCtx = document.getElementById('topSchedulesChart').getContext('2d');
            new Chart(topSchedulesCtx, {
                type: 'bar',
                data: {
                    labels: {!! json_encode($topSchedulesByEvents->pluck('name')->toArray()) !!},
                    datasets: [{
                        label: 'Events',
                        data: {!! json_encode($topSchedulesByEvents->pluck('events_count')->toArray()) !!},
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
        }

        // Initialize charts when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCharts);
        } else {
            initCharts();
        }
    </script>

</x-app-admin-layout>
