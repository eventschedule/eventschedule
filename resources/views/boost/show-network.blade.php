{{--
    Campaign dashboard for an on-network promotion.

    A sibling of boost/show.blade.php rather than a set of @if branches inside it: the metric
    set genuinely differs (no Meta status, no reach, but budget pacing and a projected end
    date instead), and interleaving them would make both harder to read.
--}}
<x-app-admin-layout>
    <div class="max-w-4xl mx-auto space-y-4">

        <div class="ap-card rounded-xl p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 truncate">
                        {{ $campaign->event?->name ?? __('messages.deleted_event') }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        @lang('messages.promotion_channel_network')
                        &middot; {{ strtoupper($campaign->pricing_model) }}
                        &middot; {{ $campaign->getCurrencySymbol() }}{{ number_format($campaign->user_budget, 2) }}
                    </p>
                </div>

                @php
                    $statusColor = match ($campaign->status) {
                        'active' => 'bg-green-100 text-green-800 dark:bg-green-500/10 dark:text-green-400',
                        'pending_review' => 'bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-400',
                        'rejected', 'failed' => 'bg-red-100 text-red-800 dark:bg-red-500/10 dark:text-red-400',
                        default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                    };
                @endphp
                <span class="rounded-full px-3 py-1 text-sm font-medium {{ $statusColor }}">
                    {{ __('messages.boost_status_'.$campaign->status) }}
                </span>
            </div>

            @if ($campaign->isAwaitingReview())
            {{-- An advertiser who has already paid must not be left guessing. --}}
            <div class="mt-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>@lang('messages.promotion_awaiting_review_help')</span>
                </p>
            </div>
            @endif

            @if ($campaign->moderation_status === 'rejected' && $campaign->moderation_notes)
            <div class="mt-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-3">
                <p class="text-sm text-red-800 dark:text-red-200">
                    <strong>@lang('messages.reason'):</strong> {{ $campaign->moderation_notes }}
                </p>
            </div>
            @endif
        </div>

        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            <div class="ap-card rounded-xl p-6 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.promotion_impressions')</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($summary['impressions']) }}</p>
            </div>
            <div class="ap-card rounded-xl p-6 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.clicks')</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($summary['clicks']) }}</p>
            </div>
            <div class="ap-card rounded-xl p-6 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.ctr')</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ number_format($summary['ctr'], 2) }}%</p>
            </div>
            <div class="ap-card rounded-xl p-6 text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.spent')</p>
                <p class="mt-1 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $campaign->getCurrencySymbol() }}{{ number_format($summary['spend'], 2) }}</p>
            </div>
        </div>

        <div class="ap-card rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">@lang('messages.promotion_delivery')</h2>

            <div class="mb-2 flex justify-between text-sm text-gray-600 dark:text-gray-400">
                <span>{{ $campaign->getCurrencySymbol() }}{{ number_format($summary['spend'], 2) }} @lang('messages.spent')</span>
                <span>{{ $campaign->getCurrencySymbol() }}{{ number_format($summary['remaining'], 2) }} @lang('messages.promotion_remaining')</span>
            </div>
            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                <div class="h-full rounded-full bg-[var(--brand-button-bg)]" style="width: {{ $summary['utilization'] }}%"></div>
            </div>

            <dl class="mt-6 grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">@lang('messages.promotion_effective_cpc')</dt>
                    <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $campaign->getCurrencySymbol() }}{{ number_format($summary['effective_cpc'], 2) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">@lang('messages.promotion_effective_cpm')</dt>
                    <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $campaign->getCurrencySymbol() }}{{ number_format($summary['effective_cpm'], 2) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">@lang('messages.promotion_unique_visitors')</dt>
                    <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($summary['unique_visitors']) }}</dd>
                </div>
                <div>
                    <dt class="text-gray-500 dark:text-gray-400">@lang('messages.promotion_conversions')</dt>
                    <dd class="font-semibold text-gray-900 dark:text-gray-100">
                        {{ number_format($conversions['count']) }}
                        {{-- Attributed ticket revenue is the number that tells an advertiser
                             whether the campaign paid for itself, and it was being computed and
                             thrown away. Shown next to the count rather than as its own stat so
                             it reads as "what those conversions were worth". --}}
                        @if ($conversions['revenue'] > 0)
                        <span class="font-normal text-gray-500 dark:text-gray-400">
                            ({{ $campaign->getCurrencySymbol() }}{{ number_format($conversions['revenue'], 2) }})
                        </span>
                        @endif
                    </dd>
                </div>
            </dl>
        </div>

        @if (count($dailySeries) > 1)
        <div class="ap-card rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">@lang('messages.promotion_daily_delivery')</h2>
            <div class="h-64"><canvas id="promotion-chart"></canvas></div>
        </div>
        @endif

        @if ($countries->isNotEmpty())
        <div class="ap-card rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">@lang('messages.promotion_countries')</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700">
                        <th class="py-2 text-start font-medium text-gray-500 dark:text-gray-400">@lang('messages.country')</th>
                        <th class="py-2 text-end font-medium text-gray-500 dark:text-gray-400">@lang('messages.promotion_impressions')</th>
                        {{-- Per-country clicks were already being recorded and aggregated, then
                             dropped here. Which countries convert is the half of this table an
                             advertiser can actually act on when setting targeting next time. --}}
                        <th class="py-2 text-end font-medium text-gray-500 dark:text-gray-400">@lang('messages.clicks')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($countries as $country)
                    <tr class="border-b border-gray-100 dark:border-gray-700 last:border-0">
                        <td class="py-2 text-gray-700 dark:text-gray-300">{{ $country['name'] }}</td>
                        <td class="py-2 text-end text-gray-500 dark:text-gray-400">{{ number_format($country['impressions']) }}</td>
                        <td class="py-2 text-end text-gray-500 dark:text-gray-400">{{ number_format($country['clicks']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        @if ($placements['schedule_count'] > 0)
        <div class="ap-card rounded-xl p-6">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">@lang('messages.promotion_placements')</h2>
            {{-- Counts and kinds only. The schedules carrying this promotion did not agree to
                 have their traffic disclosed to the advertiser paying for it. --}}
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                {{ trans_choice('messages.promotion_placement_count', $placements['schedule_count'], ['count' => $placements['schedule_count']]) }}
            </p>
            <table class="w-full text-sm">
                <tbody>
                    @foreach ($placements['by_type'] as $row)
                    <tr class="border-b border-gray-100 dark:border-gray-700 last:border-0">
                        <td class="py-2 text-gray-700 dark:text-gray-300">{{ __('messages.'.$row['type']) }}</td>
                        <td class="py-2 text-end text-gray-500 dark:text-gray-400">{{ number_format($row['impressions']) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <div class="ap-card rounded-xl p-6">
            <div class="flex flex-wrap justify-end gap-3">
                <x-secondary-link href="{{ route('boost.index') }}">@lang('messages.back')</x-secondary-link>

                @if ($campaign->canBePaused() || $campaign->canBeResumed())
                <form method="POST" action="{{ route('boost.toggle_pause', ['hash' => $campaign->hashedId()]) }}">
                    @csrf
                    {{-- A plain button carrying the secondary-link classes: the component
                         itself renders an <a> and needs an href, which a submit cannot have. --}}
                    <button type="submit" class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        {{ $campaign->canBePaused() ? __('messages.pause') : __('messages.resume') }}
                    </button>
                </form>
                @endif

                @if ($campaign->canBeCancelled())
                <form method="POST" action="{{ route('boost.cancel', ['hash' => $campaign->hashedId()]) }}" data-confirm="{{ __('messages.are_you_sure') }}">
                    @csrf
                    <x-danger-button type="submit">@lang('messages.cancel')</x-danger-button>
                </form>
                @endif
            </div>
        </div>
    </div>

    @if (count($dailySeries) > 1)
    <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>
    <script {!! nonce_attr() !!}>
        (function () {
            const series = @json($dailySeries);
            const styles = getComputedStyle(document.documentElement);
            const brandBlue = styles.getPropertyValue('--brand-blue').trim();
            const isDark = document.documentElement.classList.contains('dark');
            const textColor = isDark ? '#9ca3af' : '#6b7280';
            const gridColor = isDark ? '#2d2d30' : '#e5e7eb';

            new Chart(document.getElementById('promotion-chart'), {
                type: 'line',
                data: {
                    labels: series.map(r => r.date),
                    datasets: [
                        {
                            label: @json(__('messages.promotion_impressions')),
                            data: series.map(r => r.impressions),
                            borderColor: brandBlue,
                            backgroundColor: brandBlue + '20',
                            tension: 0.3,
                            fill: true,
                        },
                        {
                            label: @json(__('messages.clicks')),
                            data: series.map(r => r.clicks),
                            borderColor: '#10B981',
                            tension: 0.3,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { labels: { color: textColor } } },
                    scales: {
                        x: { grid: { color: gridColor }, ticks: { color: textColor } },
                        y: { grid: { color: gridColor }, ticks: { color: textColor }, beginAtZero: true },
                    },
                },
            });
        })();
    </script>
    @endif
</x-app-admin-layout>
