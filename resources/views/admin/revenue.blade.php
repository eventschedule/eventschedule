<x-app-admin-layout>

    <div class="space-y-6">
        @include('admin.partials._navigation', ['active' => 'revenue'])

        @include('admin.partials._date-range-filter', ['range' => $range])

        {{-- Revenue & Sales Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
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
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">@lang('messages.boost_markup_revenue')</p>
                <p class="mt-2 text-2xl font-bold text-green-600 dark:text-green-400">${{ number_format($boostMarkupTotal, 2) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">+${{ number_format($boostMarkupInPeriod, 2) }} @lang('messages.in_period')</p>
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

        {{-- Amount Mismatch Sales --}}
        @if ($mismatchSales->count() > 0 || $mismatchBoosts->count() > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 border-l-4 border-amber-500">
            <h3 class="text-lg font-medium text-amber-600 dark:text-amber-400 mb-4">@lang('messages.amount_mismatch_sales')</h3>
            @if ($mismatchSales->count() > 0)
            <div class="overflow-x-auto mb-6">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.date')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.event')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.customer')</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.expected_amount')</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.paid_amount')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.reference')</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.actions')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($mismatchSales as $sale)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" title="{{ $sale->created_at->format('Y-m-d H:i:s') }}">
                                {{ $sale->created_at->diffForHumans() }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                {{ \Illuminate\Support\Str::limit($sale->event?->name ?? '-', 30) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $sale->name }} ({{ $sale->email }})</td>
                            <td class="px-4 py-3 text-sm text-end font-medium text-gray-900 dark:text-white">${{ number_format($sale->calculateTotal(), 2) }}</td>
                            <td class="px-4 py-3 text-sm text-end font-medium text-amber-600 dark:text-amber-400">${{ number_format($sale->payment_amount, 2) }}</td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-500 dark:text-gray-400" title="{{ $sale->transaction_reference }}">
                                {{ $sale->transaction_reference ? \Illuminate\Support\Str::limit($sale->transaction_reference, 15) : '-' }}
                            </td>
                            <td class="px-4 py-3 text-sm text-end whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.sale.approve', $sale->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200 text-sm font-medium" onclick="return confirm('Approve this sale as paid?')">@lang('messages.approve_sale')</button>
                                </form>
                                <form method="POST" action="{{ route('admin.sale.refund', $sale->id) }}" class="inline ms-3">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200 text-sm font-medium" onclick="return confirm('Refund this sale via Stripe?')">@lang('messages.refund_sale')</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            @if ($mismatchBoosts->count() > 0)
            <h4 class="text-md font-medium text-amber-600 dark:text-amber-400 mb-3">@lang('messages.amount_mismatch_boosts')</h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.date')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.event')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.user')</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.paid_amount')</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.actions')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($mismatchBoosts as $campaign)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" title="{{ $campaign->created_at->format('Y-m-d H:i:s') }}">
                                {{ $campaign->created_at->diffForHumans() }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                {{ \Illuminate\Support\Str::limit($campaign->event?->name ?? '-', 30) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $campaign->user?->name }} ({{ $campaign->user?->email }})</td>
                            <td class="px-4 py-3 text-sm text-end font-medium text-amber-600 dark:text-amber-400">${{ number_format($campaign->total_charged, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-end whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.boost.approve', $campaign->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="text-green-600 hover:text-green-800 dark:text-green-400 dark:hover:text-green-200 text-sm font-medium" onclick="return confirm('Approve this boost campaign?')">@lang('messages.approve_sale')</button>
                                </form>
                                <form method="POST" action="{{ route('admin.boost.refund', $campaign->id) }}" class="inline ms-3">
                                    @csrf
                                    <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-200 text-sm font-medium" onclick="return confirm('Refund this boost campaign via Stripe?')">@lang('messages.refund_sale')</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @endif

        {{-- Recent Sales Table --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">@lang('messages.recent_sales')</h3>
            @if ($recentSales->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.date')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.schedule')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.event')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.customer')</th>
                            <th class="px-4 py-3 text-end text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.amount')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.method')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.status')</th>
                            <th class="px-4 py-3 text-start text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">@lang('messages.reference')</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($recentSales as $sale)
                        <tr>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" title="{{ $sale->created_at->format('Y-m-d H:i:s') }}">
                                {{ $sale->created_at->diffForHumans() }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('role.view_guest', ['subdomain' => $sale->subdomain]) }}" class="text-[#4E81FA] hover:underline" target="_blank">{{ $sale->subdomain }}</a>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white" title="{{ $sale->event?->name }}">
                                {{ \Illuminate\Support\Str::limit($sale->event?->name ?? '-', 30) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $sale->name }}</td>
                            <td class="px-4 py-3 text-sm text-end font-medium text-gray-900 dark:text-white">${{ number_format($sale->payment_amount, 2) }}</td>
                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">{{ $sale->payment_method ?? '-' }}</td>
                            <td class="px-4 py-3 text-sm">
                                @php
                                    $statusClasses = match($sale->status) {
                                        'completed', 'paid' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                                        'refunded' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                                        'amount_mismatch' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
                                        'cancelled', 'expired' => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300',
                                    };
                                @endphp
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusClasses }}">
                                    {{ ucfirst($sale->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-gray-500 dark:text-gray-400" title="{{ $sale->transaction_reference }}">
                                {{ $sale->transaction_reference ? \Illuminate\Support\Str::limit($sale->transaction_reference, 15) : '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.no_sales_yet')</p>
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

            // Revenue Trend Chart
            const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
            new Chart(revenueTrendCtx, {
                type: 'line',
                data: {
                    labels: @json($trendData['labels']),
                    datasets: [{
                        label: @json(__('messages.revenue')),
                        data: @json($trendData['revenue']),
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
