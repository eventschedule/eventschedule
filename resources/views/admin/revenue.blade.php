<x-app-admin-layout>

    <div class="space-y-6">
        @include('admin.partials._navigation', ['active' => 'revenue'])

        @include('admin.partials._date-range-filter', ['range' => $range])

        {{-- Revenue & Sales Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.total_revenue')</p>
                <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">${{ number_format($totalRevenue, 2) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">+${{ number_format($revenueInPeriod, 2) }} @lang('messages.in_period')</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.total_sales')</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalSales) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">+{{ number_format($salesInPeriod) }} @lang('messages.in_period')</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.refund_rate')</p>
                <p class="mt-2 text-2xl font-bold {{ $refundRate > 5 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ $refundRate }}%</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.pending_revenue')</p>
                <p class="mt-2 text-2xl font-bold text-amber-600 dark:text-amber-400">${{ number_format($pendingRevenue, 2) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ number_format($pendingSales) }} @lang('messages.pending_sales')</p>
            </div>
        </div>

        @if (config('app.hosted'))
        {{-- Subscription Health --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.subscription_health')</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.active_subscriptions')</p>
                    <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">{{ number_format($activeSubscriptions) }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.on_free_trial')</p>
                    <p class="mt-2 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($rolesOnTrial) }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.converted_from_trial')</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($convertedFromTrial) }}</p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.past_due')</p>
                    <p class="mt-2 text-2xl font-bold {{ $pastDueSubscriptions > 0 ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">{{ number_format($pastDueSubscriptions) }}</p>
                </div>
            </div>
        </div>

        {{-- More Subscription Details --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.trialing_subscriptions')</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($trialingSubscriptions) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.canceled_subscriptions')</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($canceledSubscriptions) }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.expired_trials_no_sub')</p>
                <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($expiredTrialsNoSub) }}</p>
            </div>
        </div>
        @endif

        {{-- Revenue Trend Chart --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.revenue_trend')</h3>
            <div class="h-64">
                <canvas id="revenueTrendChart"></canvas>
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

            // Revenue Trend Chart
            const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
            new Chart(revenueTrendCtx, {
                type: 'line',
                data: {
                    labels: {!! json_encode($trendData['labels']) !!},
                    datasets: [{
                        label: @json(__('messages.revenue')),
                        data: {!! json_encode($trendData['revenue']) !!},
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
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
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
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
