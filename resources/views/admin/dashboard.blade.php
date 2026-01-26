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
