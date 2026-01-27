<x-app-admin-layout>

    <div class="space-y-6">
        @include('admin.partials._navigation', ['active' => 'analytics'])

        @include('admin.partials._date-range-filter', ['range' => $range])

        {{-- Traffic Overview --}}
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
            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                Based on {{ number_format($totalSchedules) }} total schedules
            </p>
        </div>

        {{-- Top Schedules by Events --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Top Schedules by Events</h3>
            <div class="h-64">
                <canvas id="topSchedulesChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Chart.js --}}
    <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>

    <script {!! nonce_attr() !!}>
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
