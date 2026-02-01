<x-app-admin-layout>

    <div class="space-y-6">
        @include('admin.partials._navigation', ['active' => 'users'])

        @include('admin.partials._date-range-filter', ['range' => $range])

        {{-- User Count with Period Comparison --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                            <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ms-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.total_users')</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalUsers) }}</p>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="{{ $usersChangePercent >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $usersChangePercent >= 0 ? '+' : '' }}{{ $usersChangePercent }}%
                    </span>
                    <span class="text-gray-500 dark:text-gray-400 ms-2">
                        +{{ number_format($usersInPeriod) }} @lang('messages.in_period')
                    </span>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.active_users_7_days')</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($activeUsers7Days) }}</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.active_users_30_days')</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($activeUsers30Days) }}</p>
            </div>
        </div>

        {{-- User Signup Method Breakdown --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Signup Method Donut Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.signup_method_breakdown') (@lang('messages.all_time'))</h3>
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
                                <span class="w-3 h-3 rounded-full bg-blue-500 me-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">@lang('messages.email')</span>
                            </div>
                            <div class="text-end">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($emailUsers) }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ms-1">({{ $emailPercent }}%)</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-full bg-red-500 me-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">@lang('messages.google')</span>
                            </div>
                            <div class="text-end">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($googleUsers) }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ms-1">({{ $googlePercent }}%)</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <span class="w-3 h-3 rounded-full bg-amber-500 me-2"></span>
                                <span class="text-sm text-gray-600 dark:text-gray-400">@lang('messages.hybrid')</span>
                            </div>
                            <div class="text-end">
                                <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($hybridUsers) }}</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 ms-1">({{ $hybridPercent }}%)</span>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-gray-200 dark:border-gray-700">
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                @lang('messages.hybrid') = @lang('messages.hybrid_description')
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Signup Method in Period --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.signups_by_method') (@lang('messages.selected_period'))</h3>
                <div class="h-48">
                    <canvas id="signupMethodTrendChart"></canvas>
                </div>
                <div class="mt-4 grid grid-cols-3 gap-4 text-center">
                    <div>
                        <p class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($emailUsersInPeriod) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">@lang('messages.email')</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ number_format($googleUsersInPeriod) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">@lang('messages.google')</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ number_format($hybridUsersInPeriod) }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">@lang('messages.hybrid')</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- UTM Attribution Section --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- UTM Summary Card + Bar Chart --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.utm_attribution') (@lang('messages.selected_period'))</h3>

                @if($usersWithUtmInPeriod + $usersWithoutUtmInPeriod > 0)
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                        {{ number_format($usersWithUtmInPeriod) }} @lang('messages.from_campaigns')
                        ({{ $usersWithUtmInPeriod + $usersWithoutUtmInPeriod > 0 ? round(($usersWithUtmInPeriod / ($usersWithUtmInPeriod + $usersWithoutUtmInPeriod)) * 100, 1) : 0 }}%
                        @lang('messages.of') {{ number_format($usersWithUtmInPeriod + $usersWithoutUtmInPeriod) }} @lang('messages.signups_total'))
                    </p>

                    @if($utmSourcesInPeriod->count() > 0)
                        <div class="h-48">
                            <canvas id="utmSourcesChart"></canvas>
                        </div>
                    @else
                        <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_utm_data')</p>
                    @endif
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_utm_data')</p>
                @endif
            </div>

            {{-- Top Campaigns Table --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.top_campaigns') (@lang('messages.all_time'))</h3>

                @if($topUtmCampaigns->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 dark:border-gray-700">
                                    <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.source')</th>
                                    <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.medium')</th>
                                    <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.campaign')</th>
                                    <th class="text-end py-2 font-medium text-gray-500 dark:text-gray-400">@lang('messages.users')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($topUtmCampaigns as $campaign)
                                    <tr class="border-b border-gray-100 dark:border-gray-700/50">
                                        <td class="py-2 pe-4 text-gray-900 dark:text-white">{{ $campaign->utm_source ?? '-' }}</td>
                                        <td class="py-2 pe-4 text-gray-600 dark:text-gray-400">{{ $campaign->utm_medium ?? '-' }}</td>
                                        <td class="py-2 pe-4 text-gray-600 dark:text-gray-400">{{ $campaign->utm_campaign }}</td>
                                        <td class="py-2 text-end font-medium text-gray-900 dark:text-white">{{ number_format($campaign->count) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_utm_data')</p>
                @endif
            </div>
        </div>

        {{-- Top UTM Sources & Top Referrers (All Time) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            @if($topUtmSources->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.top_sources') (@lang('messages.all_time'))</h3>
                    <div class="h-64">
                        <canvas id="utmTopSourcesChart"></canvas>
                    </div>
                </div>
            @endif

            @if($topReferrerDomains->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.top_referrers') (@lang('messages.all_time'))</h3>
                    <div class="h-64">
                        <canvas id="topReferrersChart"></canvas>
                    </div>
                </div>
            @endif
        </div>

        {{-- Recent Signups --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.recent_signups')</h3>

            @if($recentSignups->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-gray-700">
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.name')</th>
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.email')</th>
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.date')</th>
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.source')</th>
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.medium')</th>
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.campaign')</th>
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.content')</th>
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.term')</th>
                                <th class="text-start py-2 pe-4 font-medium text-gray-500 dark:text-gray-400">@lang('messages.referrer')</th>
                                <th class="text-start py-2 font-medium text-gray-500 dark:text-gray-400">@lang('messages.landing_page')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentSignups as $signup)
                                <tr class="border-b border-gray-100 dark:border-gray-700/50">
                                    <td class="py-2 pe-4 text-gray-900 dark:text-white">{{ $signup->name }}</td>
                                    <td class="py-2 pe-4 text-gray-600 dark:text-gray-400">{{ $signup->email }}</td>
                                    <td class="py-2 pe-4 text-gray-600 dark:text-gray-400 whitespace-nowrap">{{ $signup->created_at->format('M j, Y') }}</td>
                                    <td class="py-2 pe-4 text-gray-600 dark:text-gray-400">{{ $signup->utm_source ?? '-' }}</td>
                                    <td class="py-2 pe-4 text-gray-600 dark:text-gray-400">{{ $signup->utm_medium ?? '-' }}</td>
                                    <td class="py-2 pe-4 text-gray-600 dark:text-gray-400">{{ $signup->utm_campaign ?? '-' }}</td>
                                    <td class="py-2 pe-4 text-gray-600 dark:text-gray-400">{{ $signup->utm_content ?? '-' }}</td>
                                    <td class="py-2 pe-4 text-gray-600 dark:text-gray-400">{{ $signup->utm_term ?? '-' }}</td>
                                    <td class="py-2 pe-4 text-gray-600 dark:text-gray-400" title="{{ $signup->referrer_url }}">
                                        @if($signup->referrer_url)
                                            {{ parse_url($signup->referrer_url, PHP_URL_HOST) ?? '-' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="py-2 text-gray-600 dark:text-gray-400" title="{{ $signup->landing_page }}">{{ Str::limit($signup->landing_page ?? '-', 30) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    {{ $recentSignups->links() }}
                </div>
            @else
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_data')</p>
            @endif
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

            // Signup Method Donut Chart
            const signupMethodCtx = document.getElementById('signupMethodChart').getContext('2d');
            new Chart(signupMethodCtx, {
                type: 'doughnut',
                data: {
                    labels: [@json(__('messages.email')), @json(__('messages.google')), @json(__('messages.hybrid'))],
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
                            label: @json(__('messages.email')),
                            data: {!! json_encode($trendData['emailUsers']) !!},
                            backgroundColor: '#3B82F6',
                            stack: 'signups'
                        },
                        {
                            label: @json(__('messages.google')),
                            data: {!! json_encode($trendData['googleUsers']) !!},
                            backgroundColor: '#EF4444',
                            stack: 'signups'
                        },
                        {
                            label: @json(__('messages.hybrid')),
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

            // UTM Sources Bar Chart (selected period)
            @if($utmSourcesInPeriod->count() > 0)
                const utmSourcesCtx = document.getElementById('utmSourcesChart').getContext('2d');
                new Chart(utmSourcesCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($utmSourcesInPeriod->pluck('utm_source')->toArray()) !!},
                        datasets: [{
                            label: @json(__('messages.users')),
                            data: {!! json_encode($utmSourcesInPeriod->pluck('count')->toArray()) !!},
                            backgroundColor: '#8B5CF6'
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: textColor, precision: 0 }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { color: textColor }
                            }
                        }
                    }
                });
            @endif

            // UTM Top Sources Bar Chart (all time)
            @if($topUtmSources->count() > 0)
                const utmTopSourcesCtx = document.getElementById('utmTopSourcesChart').getContext('2d');
                new Chart(utmTopSourcesCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($topUtmSources->pluck('utm_source')->toArray()) !!},
                        datasets: [{
                            label: @json(__('messages.users')),
                            data: {!! json_encode($topUtmSources->pluck('count')->toArray()) !!},
                            backgroundColor: '#8B5CF6'
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: textColor, precision: 0 }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { color: textColor }
                            }
                        }
                    }
                });
            @endif

            // Top Referrer Domains Bar Chart (all time)
            @if($topReferrerDomains->count() > 0)
                const topReferrersCtx = document.getElementById('topReferrersChart').getContext('2d');
                new Chart(topReferrersCtx, {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode($topReferrerDomains->pluck('domain')->toArray()) !!},
                        datasets: [{
                            label: @json(__('messages.users')),
                            data: {!! json_encode($topReferrerDomains->pluck('count')->toArray()) !!},
                            backgroundColor: '#10B981'
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: textColor, precision: 0 }
                            },
                            y: {
                                grid: { display: false },
                                ticks: { color: textColor }
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
