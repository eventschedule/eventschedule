<x-app-admin-layout>

    <div class="space-y-6">
        {{-- Schedule Selector and Period Toggle --}}
        <div class="flex flex-col sm:flex-row sm:justify-between gap-4">
            <div class="flex-1 max-w-xs">
                <select id="role-filter" onchange="filterByRole(this.value)"
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base">
                    <option value="">{{ __('messages.all_schedules') }}</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" {{ $selectedRoleId == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 items-center">
                <a href="{{ route('analytics', ['role_id' => $selectedRoleId, 'period' => 'daily']) }}"
                    class="px-4 py-2 rounded-md text-sm font-medium leading-none flex items-center {{ $period === 'daily' ? 'bg-[#4E81FA] text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                    {{ __('messages.daily') }}
                </a>
                <a href="{{ route('analytics', ['role_id' => $selectedRoleId, 'period' => 'weekly']) }}"
                    class="px-4 py-2 rounded-md text-sm font-medium leading-none flex items-center {{ $period === 'weekly' ? 'bg-[#4E81FA] text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                    {{ __('messages.weekly') }}
                </a>
                <a href="{{ route('analytics', ['role_id' => $selectedRoleId, 'period' => 'monthly']) }}"
                    class="px-4 py-2 rounded-md text-sm font-medium leading-none flex items-center {{ $period === 'monthly' ? 'bg-[#4E81FA] text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                    {{ __('messages.monthly') }}
                </a>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.total_views') }}</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($totalViews) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.views_this_month') }}</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($momComparison['this_month']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.views_last_month') }}</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($momComparison['last_month']) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.month_over_month') }}</div>
                <div class="mt-2 text-3xl font-bold {{ $momComparison['percentage_change'] >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ $momComparison['percentage_change'] >= 0 ? '+' : '' }}{{ $momComparison['percentage_change'] }}%
                </div>
            </div>
        </div>

        @if ($totalViews > 0)
            {{-- Charts Row --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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

            {{-- Second Charts Row --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Top Events Chart --}}
                @if ($topEvents->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.top_events') }}</h3>
                    <div class="h-64">
                        <canvas id="topEventsChart"></canvas>
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
            </div>

            {{-- Recent Views Table --}}
            @if ($recentViews->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">{{ __('messages.recent_views') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('messages.date') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('messages.schedule') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('messages.event') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                                    {{ __('messages.device') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @foreach ($recentViews as $view)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $view->viewed_at->format('M j, Y g:i A') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $view->role?->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                    {{ $view->event?->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $view->device_type === 'desktop' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                        {{ $view->device_type === 'mobile' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                                        {{ $view->device_type === 'tablet' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : '' }}
                                        {{ $view->device_type === 'unknown' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' : '' }}">
                                        {{ ucfirst($view->device_type) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
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
        function filterByRole(roleId) {
            const url = new URL(window.location.href);
            if (roleId) {
                url.searchParams.set('role_id', roleId);
            } else {
                url.searchParams.delete('role_id');
            }
            window.location.href = url.toString();
        }

        @if ($totalViews > 0)
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

            // Views Over Time Chart
            const viewsCtx = document.getElementById('viewsChart').getContext('2d');
            new Chart(viewsCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($viewsByPeriod->pluck('period')->toArray()) !!},
                datasets: [{
                    label: '{{ __("messages.views") }}',
                    data: {!! json_encode($viewsByPeriod->pluck('view_count')->toArray()) !!},
                    borderColor: '#4E81FA',
                    backgroundColor: 'rgba(78, 129, 250, 0.1)',
                    fill: true,
                    tension: 0.3
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
                            color: textColor
                        }
                    }
                }
            }
        });

        // Device Breakdown Chart
        const deviceCtx = document.getElementById('deviceChart').getContext('2d');
        const deviceData = {!! json_encode($deviceBreakdown->toArray()) !!};
        new Chart(deviceCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(deviceData).map(k => k.charAt(0).toUpperCase() + k.slice(1)),
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
                labels: {!! json_encode($topEvents->pluck('event.name')->toArray()) !!},
                datasets: [{
                    label: '{{ __("messages.views") }}',
                    data: {!! json_encode($topEvents->pluck('view_count')->toArray()) !!},
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

        @if ($viewsBySchedule->isNotEmpty() && $viewsBySchedule->count() > 1)
        // Schedule Views Chart
        const scheduleCtx = document.getElementById('scheduleChart').getContext('2d');
        new Chart(scheduleCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($viewsBySchedule->pluck('role.name')->toArray()) !!},
                datasets: [{
                    label: '{{ __("messages.views") }}',
                    data: {!! json_encode($viewsBySchedule->pluck('view_count')->toArray()) !!},
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
