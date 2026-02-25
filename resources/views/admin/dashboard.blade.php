<x-app-admin-layout>

    <div class="space-y-6">
        @include('admin.partials._navigation', ['active' => 'dashboard'])

        @include('admin.partials._date-range-filter', ['range' => $range])

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
                    <div class="ms-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.total_schedules')</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalSchedules) }}</p>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="{{ $schedulesChangePercent >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $schedulesChangePercent >= 0 ? '+' : '' }}{{ $schedulesChangePercent }}%
                    </span>
                    <span class="text-gray-500 dark:text-gray-400 ms-2">
                        +{{ number_format($schedulesInPeriod) }} @lang('messages.in_period')
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
                    <div class="ms-4 flex-1">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.total_events')</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalEvents) }}</p>
                    </div>
                </div>
                <div class="mt-4 flex items-center text-sm">
                    <span class="{{ $eventsChangePercent >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ $eventsChangePercent >= 0 ? '+' : '' }}{{ $eventsChangePercent }}%
                    </span>
                    <span class="text-gray-500 dark:text-gray-400 ms-2">
                        +{{ number_format($eventsInPeriod) }} @lang('messages.in_period')
                    </span>
                </div>
            </div>
        </div>

        {{-- Activity Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.active_users_7_days')</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($activeUsers7Days) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.active_users_30_days')</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($activeUsers30Days) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.avg_events_per_schedule')</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ $avgEventsPerSchedule }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <p class="ms-2 text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.upcoming_online_events')</p>
                </div>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($upcomingOnlineEvents) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-5 h-5 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <p class="ms-2 text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.private_events')</p>
                </div>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($privateEvents) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($passwordProtectedEvents) }} @lang('messages.with_password')</p>
            </div>
        </div>

        {{-- Boost & Newsletter Stats --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.active_boost_campaigns')</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($activeBoostCampaigns) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.boost_markup_revenue')</p>
                <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">${{ number_format($boostMarkupRevenue, 2) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.in_period')</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.admin_newsletters_sent')</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($adminNewslettersSent) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.newsletter_subscribers')</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($newsletterSubscribers) }}</p>
            </div>
        </div>

        {{-- Events by Country --}}
        @if($eventsByCountry->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.upcoming_events_by_country')</h3>
            <div class="space-y-3">
                @foreach($eventsByCountry as $country)
                    <div class="flex items-center">
                        <div class="w-24 flex-shrink-0">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ strtoupper($country->country_code) }}
                            </span>
                        </div>
                        <div class="flex-1 mx-4">
                            <div class="bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                                <div class="bg-indigo-500 h-4 rounded-full" style="width: {{ min(100, ($country->count / $eventsByCountry->max('count')) * 100) }}%"></div>
                            </div>
                        </div>
                        <div class="w-16 text-end">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">{{ number_format($country->count) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Growth Trends Chart --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.growth_trends')</h3>
            <div class="h-64">
                <canvas id="trendsChart"></canvas>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
            {{-- Recent Schedules --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">@lang('messages.recent_schedules')</h3>
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
                                <div class="text-end text-sm text-gray-500 dark:text-gray-400">
                                    <p>{{ $schedule->created_at->diffForHumans() }}</p>
                                    @if ($schedule->owner())
                                        <p class="text-xs">@lang('messages.by') {{ $schedule->owner()->name }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            @lang('messages.no_schedules_yet')
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Events --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">@lang('messages.recent_events')</h3>
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
                                            @lang('messages.no_schedule')
                                        @endif
                                    </p>
                                </div>
                                <div class="text-end text-sm text-gray-500 dark:text-gray-400">
                                    <p>{{ $event->created_at->diffForHumans() }}</p>
                                    @if ($event->starts_at)
                                        <p class="text-xs">{{ \Carbon\Carbon::parse($event->starts_at)->format('M d, Y') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            @lang('messages.no_events_yet')
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Recent Newsletters --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white">@lang('messages.recent_newsletters')</h3>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($recentNewsletters as $newsletter)
                        <div class="px-6 py-4">
                            <div class="flex items-center justify-between">
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $newsletter->subject }}
                                    </p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ number_format($newsletter->sent_count) }} @lang('messages.recipients')
                                        &bull; {{ number_format($newsletter->open_count) }} @lang('messages.opens')
                                    </p>
                                </div>
                                <div class="text-end text-sm text-gray-500 dark:text-gray-400">
                                    <p>{{ $newsletter->sent_at?->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            @lang('messages.no_newsletters_sent')
                        </div>
                    @endforelse
                </div>
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

            // Growth Trends Chart
            const trendsCtx = document.getElementById('trendsChart').getContext('2d');
            new Chart(trendsCtx, {
                type: 'line',
                data: {
                    labels: @json($trendData['labels']),
                    datasets: [
                        {
                            label: @json(__('messages.users')),
                            data: @json($trendData['users']),
                            borderColor: '#4E81FA',
                            backgroundColor: 'rgba(78, 129, 250, 0.1)',
                            fill: false,
                            tension: 0.3
                        },
                        {
                            label: @json(__('messages.schedules')),
                            data: @json($trendData['schedules']),
                            borderColor: '#10B981',
                            backgroundColor: 'rgba(16, 185, 129, 0.1)',
                            fill: false,
                            tension: 0.3
                        },
                        {
                            label: @json(__('messages.events')),
                            data: @json($trendData['events']),
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
        }

        // Initialize charts when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCharts);
        } else {
            initCharts();
        }
    </script>

</x-app-admin-layout>
