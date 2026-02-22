<x-app-admin-layout>
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <a href="{{ route('boost.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300">&larr; {{ __('messages.back_to_boost') }}</a>
        </div>

        @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-md text-green-700 dark:text-green-300">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md text-red-700 dark:text-red-300">
            {{ session('error') }}
        </div>
        @endif

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $campaign->event?->translatedName() ?? __('messages.deleted_event') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $campaign->role?->name ?? __('messages.deleted') }}</p>
            </div>
            @php
                $statusColors = [
                    'draft' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                    'pending_payment' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
                    'active' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
                    'paused' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
                    'completed' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                    'failed' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                    'rejected' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
                    'cancelled' => 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400',
                ];
            @endphp
            <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $statusColors[$campaign->status] ?? $statusColors['draft'] }}">
                {{ __('messages.boost_status_' . $campaign->status) }}
            </span>
        </div>

        {{-- Rejection banner --}}
        @if ($campaign->status === 'rejected' && $campaign->meta_rejection_reason)
        <div class="mb-6 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg">
            <h3 class="font-semibold text-red-700 dark:text-red-300 mb-1">{{ __('messages.ad_rejected') }}</h3>
            <p class="text-sm text-red-600 dark:text-red-400">{{ $campaign->meta_rejection_reason }}</p>
        </div>
        @endif

        {{-- Key metrics --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($campaign->impressions) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.impressions') }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($campaign->reach) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.reach') }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($campaign->clicks) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.clicks') }}</p>
            </div>
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4 text-center">
                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($campaign->conversions ?? 0) }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.conversions') }}</p>
            </div>
        </div>

        {{-- Detailed metrics --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
            {{-- Budget utilization --}}
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('messages.budget_utilization') }}</h3>
                <div class="mb-2">
                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                        <div class="bg-blue-500 h-3 rounded-full transition-all" style="width: {{ $campaign->getBudgetUtilization() }}%"></div>
                    </div>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 dark:text-gray-400">{{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->actual_spend ?? 0, 2) }} {{ __('messages.spent') }}</span>
                    <span class="text-gray-500 dark:text-gray-400">{{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->user_budget, 2) }} {{ __('messages.budget') }}</span>
                </div>

                <div class="mt-4 space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('messages.ctr') }}</span>
                        <span class="text-gray-900 dark:text-white">{{ number_format($campaign->ctr, 2) }}%</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('messages.cpc') }}</span>
                        <span class="text-gray-900 dark:text-white">{{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->cpc, 2) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('messages.cpm') }}</span>
                        <span class="text-gray-900 dark:text-white">{{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->cpm, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Campaign info --}}
            <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('messages.campaign_details') }}</h3>
                <div class="space-y-2 text-sm">
                    @if (config('app.hosted'))
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('messages.total_charged') }}</span>
                        <span class="text-gray-900 dark:text-white">{{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->total_charged ?? $campaign->getTotalCost(), 2) }}</span>
                    </div>
                    @endif
                    @if ($campaign->scheduled_start)
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('messages.start_date') }}</span>
                        <span class="text-gray-900 dark:text-white">{{ $campaign->scheduled_start->format('M j, Y') }}</span>
                    </div>
                    @endif
                    @if ($campaign->scheduled_end)
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('messages.end_date') }}</span>
                        <span class="text-gray-900 dark:text-white">{{ $campaign->scheduled_end->format('M j, Y') }}</span>
                    </div>
                    @endif
                    @if ($campaign->analytics_synced_at)
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('messages.last_updated') }}</span>
                        <span class="text-gray-900 dark:text-white">{{ $campaign->analytics_synced_at->diffForHumans() }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Daily performance chart --}}
        @if ($campaign->daily_analytics && count($campaign->daily_analytics) > 1)
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-6">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('messages.daily_performance') }}</h3>
            <canvas id="performance-chart" height="200"></canvas>
        </div>
        @endif

        {{-- Ad creative(s) --}}
        @if ($campaign->ads->isNotEmpty())
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6 mb-6">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('messages.ad_creative') }}</h3>
            @foreach ($campaign->ads as $ad)
            <div class="@if (!$loop->first) mt-4 pt-4 border-t border-gray-200 dark:border-gray-700 @endif">
                @if ($campaign->ads->count() > 1)
                <div class="flex items-center gap-2 mb-2">
                    <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('messages.variant') }} {{ $ad->variant }}</span>
                    @if ($ad->is_winner)
                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300">{{ __('messages.winner') }}</span>
                    @endif
                </div>
                @endif
                @include('boost.partials.ad-preview-mockup', [
                    'headline' => $ad->headline,
                    'primaryText' => $ad->primary_text,
                    'imageUrl' => $ad->image_url,
                    'cta' => $ad->call_to_action,
                ])
                @if ($ad->meta_status)
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 text-center">{{ $ad->meta_status }}</p>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- Campaign controls --}}
        @if ($campaign->canBePaused() || $campaign->canBeResumed() || $campaign->canBeCancelled())
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-6">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{ __('messages.campaign_controls') }}</h3>
            <div class="flex gap-3">
                @if ($campaign->canBeCancelled())
                <form method="POST" action="{{ route('boost.cancel', ['hash' => $campaign->hashedId()]) }}"
                      onsubmit="return confirm({{ json_encode(__('messages.boost_cancel_confirm')) }})">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-md">
                        {{ __('messages.cancel_campaign') }}
                    </button>
                </form>
                @endif

                @if ($campaign->canBePaused() || $campaign->canBeResumed())
                <form method="POST" action="{{ route('boost.toggle_pause', ['hash' => $campaign->hashedId()]) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-md">
                        {{ $campaign->isActive() ? __('messages.pause') : __('messages.resume') }}
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endif
    </div>

    @if ($campaign->daily_analytics && count($campaign->daily_analytics) > 1)
    <script src="https://cdn.jsdelivr.net/npm/chart.js" {!! nonce_attr() !!}></script>
    <script {!! nonce_attr() !!}>
        const dailyData = @json($campaign->daily_analytics);
        const labels = Object.keys(dailyData);
        const impressions = labels.map(d => dailyData[d].impressions || 0);
        const clicks = labels.map(d => dailyData[d].clicks || 0);

        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)';
        const textColor = isDark ? '#9ca3af' : '#6b7280';

        new Chart(document.getElementById('performance-chart'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: @json(__("messages.impressions")),
                        data: impressions,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y',
                    },
                    {
                        label: @json(__("messages.clicks")),
                        data: clicks,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16,185,129,0.1)',
                        fill: true,
                        tension: 0.3,
                        yAxisID: 'y1',
                    },
                ],
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: { grid: { color: gridColor }, ticks: { color: textColor } },
                    y: { position: 'left', grid: { color: gridColor }, ticks: { color: textColor } },
                    y1: { position: 'right', grid: { drawOnChartArea: false }, ticks: { color: textColor } },
                },
                plugins: {
                    legend: { labels: { color: textColor } },
                },
            },
        });
    </script>
    @endif
</x-app-admin-layout>
