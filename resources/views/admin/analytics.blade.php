<x-app-admin-layout>

    <div class="space-y-6">
        @include('admin.partials._navigation', ['active' => 'analytics'])

        @include('admin.partials._date-range-filter', ['range' => $range])

        {{-- Traffic Overview --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Device Breakdown --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.device_breakdown') (@lang('messages.selected_period'))</h3>
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
                                <span class="w-3 h-3 rounded-full bg-blue-500 me-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">@lang('messages.desktop')</span>
                            </div>
                            <div class="text-end">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($desktopViews) }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ms-1">({{ $desktopPercent }}%)</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-full bg-green-500 me-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">@lang('messages.mobile')</span>
                            </div>
                            <div class="text-end">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($mobileViews) }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ms-1">({{ $mobilePercent }}%)</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-full bg-purple-500 me-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">@lang('messages.tablet')</span>
                            </div>
                            <div class="text-end">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($tabletViews) }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ms-1">({{ $tabletPercent }}%)</span>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('messages.total'): {{ number_format($totalPageViews) }} @lang('messages.page_views')
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Traffic Sources --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.traffic_sources') (@lang('messages.selected_period'))</h3>
                <div class="h-48">
                    <canvas id="trafficSourcesChart"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-5 gap-2 text-center">
                    <div>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($directViews) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">@lang('messages.direct')</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($searchViews) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">@lang('messages.search')</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($socialViews) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">@lang('messages.social')</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($emailViews) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">@lang('messages.email')</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-gray-900 dark:text-white">{{ number_format($otherViews) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">@lang('messages.other')</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Feature Adoption --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.feature_adoption')</h3>
            <div class="space-y-4">
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.google_calendar_integration')</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $googleCalendarPercent }}% ({{ number_format($googleCalendarEnabled) }} @lang('messages.schedules'))</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ min($googleCalendarPercent, 100) }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.stripe_payments')</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $stripeEventsPercent }}% ({{ number_format($stripeEvents) }} @lang('messages.schedules'))</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-green-600 h-2.5 rounded-full" style="width: {{ min($stripeEventsPercent, 100) }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.custom_domain')</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $customDomainPercent }}% ({{ number_format($customDomainEnabled) }} @lang('messages.schedules'))</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-purple-600 h-2.5 rounded-full" style="width: {{ min($customDomainPercent, 100) }}%"></div>
                    </div>
                </div>
                <div>
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.custom_css')</span>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ $customCssPercent }}% ({{ number_format($customCssEnabled) }} @lang('messages.schedules'))</span>
                    </div>
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                        <div class="bg-amber-600 h-2.5 rounded-full" style="width: {{ min($customCssPercent, 100) }}%"></div>
                    </div>
                </div>
            </div>
            <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                @lang('messages.based_on_total_schedules', ['count' => number_format($totalSchedules)])
            </p>
        </div>

        {{-- Stripe Funnel --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.stripe_funnel')</h3>
            <div class="h-48">
                <canvas id="stripeFunnelChart"></canvas>
            </div>
            <div class="mt-4 flex items-center justify-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                <span>{{ number_format($stripeConnected) }}</span>
                <span>&rarr; {{ $stripeConnected > 0 ? round(($stripeOnboarded / $stripeConnected) * 100) : 0 }}%</span>
                <span>{{ number_format($stripeOnboarded) }}</span>
                <span>&rarr; {{ $stripeOnboarded > 0 ? round(($stripeEvents / $stripeOnboarded) * 100) : 0 }}%</span>
                <span>{{ number_format($stripeEvents) }}</span>
            </div>
        </div>

        {{-- Top Schedules by Events --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.top_schedules_by_events')</h3>
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
                    labels: [@json(__('messages.desktop')), @json(__('messages.mobile')), @json(__('messages.tablet'))],
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
                    labels: [@json(__('messages.direct')), @json(__('messages.search')), @json(__('messages.social')), @json(__('messages.email')), @json(__('messages.other'))],
                    datasets: [{
                        label: @json(__('messages.views')),
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

            // Stripe Funnel Chart
            const stripeFunnelCtx = document.getElementById('stripeFunnelChart').getContext('2d');
            new Chart(stripeFunnelCtx, {
                type: 'bar',
                data: {
                    labels: [@json(__('messages.stripe_connected')), @json(__('messages.stripe_onboarded')), @json(__('messages.stripe_events'))],
                    datasets: [{
                        label: @json(__('messages.schedules')),
                        data: [{{ $stripeConnected }}, {{ $stripeOnboarded }}, {{ $stripeEvents }}],
                        backgroundColor: ['#16A34A', '#22C55E', '#86EFAC']
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

            // Top Schedules Chart
            @if ($topSchedulesByEvents->isNotEmpty())
            const topSchedulesCtx = document.getElementById('topSchedulesChart').getContext('2d');
            new Chart(topSchedulesCtx, {
                type: 'bar',
                data: {
                    labels: @json($topSchedulesByEvents->pluck('name')->toArray()),
                    datasets: [{
                        label: @json(__('messages.events')),
                        data: @json($topSchedulesByEvents->pluck('events_count')->toArray()),
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
