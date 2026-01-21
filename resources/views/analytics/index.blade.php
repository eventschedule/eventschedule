<x-app-admin-layout>

    <div class="space-y-6">
        {{-- Schedule Selector and Period Toggle --}}
        <div class="flex flex-col sm:flex-row sm:justify-between gap-4">
            <div class="flex-1 max-w-xs">
                <select id="role-filter" onchange="filterByRole(this.value)"
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base">
                    <option value="">{{ __('messages.all_schedules') }}</option>
                    @foreach ($roles as $role)
                        <option value="{{ \App\Utils\UrlUtils::encodeId($role->id) }}" {{ $selectedRoleId == $role->id ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2 items-center">
                <a href="{{ route('analytics', ['role_id' => \App\Utils\UrlUtils::encodeId($selectedRoleId), 'period' => 'daily']) }}"
                    class="px-5 py-3 rounded-md text-base font-semibold leading-none flex items-center {{ $period === 'daily' ? 'bg-[#4E81FA] text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                    {{ __('messages.daily') }}
                </a>
                <a href="{{ route('analytics', ['role_id' => \App\Utils\UrlUtils::encodeId($selectedRoleId), 'period' => 'weekly']) }}"
                    class="px-5 py-3 rounded-md text-base font-semibold leading-none flex items-center {{ $period === 'weekly' ? 'bg-[#4E81FA] text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                    {{ __('messages.weekly') }}
                </a>
                <a href="{{ route('analytics', ['role_id' => \App\Utils\UrlUtils::encodeId($selectedRoleId), 'period' => 'monthly']) }}"
                    class="px-5 py-3 rounded-md text-base font-semibold leading-none flex items-center {{ $period === 'monthly' ? 'bg-[#4E81FA] text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600' }}">
                    {{ __('messages.monthly') }}
                </a>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 {{ $appearanceViews > 0 ? 'lg:grid-cols-5' : 'lg:grid-cols-4' }} gap-4">
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
            @if ($appearanceViews > 0)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1">
                    {{ __('messages.appearance_views') }}
                    <span class="relative group">
                        <svg class="w-4 h-4 text-gray-400 cursor-help" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 px-2 py-1 text-xs text-white bg-gray-900 dark:bg-gray-700 rounded whitespace-nowrap opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                            {{ __('messages.views_from_other_schedules') }}
                        </span>
                    </span>
                </div>
                <div class="mt-2 text-3xl font-bold text-purple-600 dark:text-purple-400">{{ number_format($appearanceViews) }}</div>
            </div>
            @endif
        </div>

        {{-- Conversion Stats Cards --}}
        @if ($conversionStats['total_sales'] > 0)
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.total_revenue') }}</div>
                <div class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ number_format($conversionStats['total_revenue'], 2) }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.conversion_rate') }}</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $conversionStats['conversion_rate'] }}%</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.revenue_per_view') }}</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ number_format($conversionStats['revenue_per_view'], 2) }}</div>
            </div>
        </div>
        @endif

        @if ($totalViews > 0 || $appearanceViews > 0)
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

            {{-- Bar Charts Grid --}}
            @if ($topEvents->isNotEmpty() || ($viewsBySchedule->isNotEmpty() && $viewsBySchedule->count() > 1) || $topAppearances->isNotEmpty() || $topSchedulesAppearedOn->isNotEmpty() || $topEventsByRevenue->isNotEmpty())
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

            {{-- Traffic Sources Row --}}
            @if ($trafficSources->isNotEmpty())
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- Traffic Sources Chart --}}
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.traffic_sources') }}</h3>
                    <div class="h-64 flex items-center justify-center">
                        <canvas id="trafficSourcesChart"></canvas>
                    </div>
                </div>

                {{-- Top Referrers Chart --}}
                @if ($topReferrers->isNotEmpty())
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">{{ __('messages.top_referrers') }}</h3>
                    <div class="h-64">
                        <canvas id="topReferrersChart"></canvas>
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
        function filterByRole(roleId) {
            const url = new URL(window.location.href);
            if (roleId) {
                url.searchParams.set('role_id', roleId);
            } else {
                url.searchParams.delete('role_id');
            }
            window.location.href = url.toString();
        }

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
                            color: textColor,
                            precision: 0
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
                labels: {!! json_encode($topAppearances->map(fn($item) => $item['role']->translatedName())->toArray()) !!},
                datasets: [{
                    label: '{{ __("messages.views") }}',
                    data: {!! json_encode($topAppearances->pluck('view_count')->toArray()) !!},
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
                labels: {!! json_encode($topSchedulesAppearedOn->map(fn($item) => $item['role']->name)->toArray()) !!},
                datasets: [{
                    label: '{{ __("messages.views") }}',
                    data: {!! json_encode($topSchedulesAppearedOn->pluck('view_count')->toArray()) !!},
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
            'direct': '{{ __("messages.direct") }}',
            'search': '{{ __("messages.search") }}',
            'social': '{{ __("messages.social") }}',
            'email': '{{ __("messages.email") }}',
            'other': '{{ __("messages.other") }}'
        };
        const trafficData = {!! json_encode($trafficSources->toArray()) !!};
        new Chart(trafficSourcesCtx, {
            type: 'doughnut',
            data: {
                labels: trafficData.map(item => sourceLabels[item.source] || item.source),
                datasets: [{
                    data: trafficData.map(item => item.view_count),
                    backgroundColor: ['#4E81FA', '#10B981', '#F59E0B', '#EF4444', '#6B7280']
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
                labels: {!! json_encode($topReferrers->pluck('domain')->toArray()) !!},
                datasets: [{
                    label: '{{ __("messages.views") }}',
                    data: {!! json_encode($topReferrers->pluck('view_count')->toArray()) !!},
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
                labels: {!! json_encode($topEventsByRevenue->pluck('event.name')->toArray()) !!},
                datasets: [{
                    label: '{{ __("messages.revenue") }}',
                    data: {!! json_encode($topEventsByRevenue->pluck('revenue')->toArray()) !!},
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
