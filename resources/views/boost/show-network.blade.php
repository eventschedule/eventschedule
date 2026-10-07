{{--
    Campaign dashboard for an on-network promotion.

    A sibling of boost/show.blade.php rather than a set of @if branches inside it: the metric
    set genuinely differs (no Meta status, no reach, but budget pacing and a projected end
    date instead), and interleaving them would make both harder to read. The two open the same
    way and are built from the same parts (boost/partials), so a campaign reads the same
    whichever channel carries it.
--}}
<x-app-admin-layout>

    <x-slot name="head">
        @include('boost.partials.styles')
    </x-slot>

    @php
        $boostSymbol = $campaign->getCurrencySymbol();
        $pricingLabel = in_array($campaign->pricing_model, ['cpm', 'cpc'], true) ? __('messages.'.$campaign->pricing_model) : strtoupper((string) $campaign->pricing_model);
    @endphp

    <div class="page-shell">
        <x-page-header
            :title="$campaign->event?->translatedName() ?? __('messages.deleted_event')"
            :lead="($campaign->role?->name ?? __('messages.deleted')).' · '.__('messages.promotion_channel_network').' · '.$pricingLabel"
            :back="route('boost.index')" :back-label="__('messages.boost')">
            <x-slot name="actions">
                @include('boost.partials.status', ['status' => $campaign->status])

                @if ($campaign->canBeCancelled())
                <form method="POST" action="{{ route('boost.cancel', ['hash' => $campaign->hashedId()]) }}" data-confirm="{{ __('messages.boost_cancel_confirm') }}">
                    @csrf
                    <x-danger-button class="boost-danger">{{ __('messages.cancel_campaign') }}</x-danger-button>
                </form>
                @endif

                @if ($campaign->canBePaused() || $campaign->canBeResumed())
                <form method="POST" action="{{ route('boost.toggle_pause', ['hash' => $campaign->hashedId()]) }}">
                    @csrf
                    @if ($campaign->canBePaused())
                    {{-- A plain button carrying the secondary-link classes: the component
                         itself renders an <a> and needs an href, which a submit cannot have. --}}
                    <button type="submit" class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        {{ __('messages.pause') }}
                    </button>
                    @else
                    <x-brand-button type="submit">{{ __('messages.resume') }}</x-brand-button>
                    @endif
                </form>
                @endif
            </x-slot>
        </x-page-header>

        <div class="page-stack">
            <x-page-flash :keys="['success' => 'success', 'error' => 'error']" />

            @if ($campaign->isAwaitingReview())
            {{-- An advertiser who has already paid must not be left guessing. --}}
            <x-page-notice tone="warn">@lang('messages.promotion_awaiting_review_help')</x-page-notice>
            @endif

            @if ($campaign->moderation_status === 'rejected' && $campaign->moderation_notes)
            <x-page-notice tone="error" :title="__('messages.reason')">
                <bdi>{{ $campaign->moderation_notes }}</bdi>
            </x-page-notice>
            @endif

            <div class="ap-card rounded-xl page-stats is-auto">
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($summary['impressions']) }}</div>
                    <div class="page-stat-label">@lang('messages.promotion_impressions')</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($summary['clicks']) }}</div>
                    <div class="page-stat-label">@lang('messages.clicks')</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($summary['ctr'], 2) }}%</div>
                    <div class="page-stat-label">@lang('messages.ctr')</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ $boostSymbol }}{{ number_format($summary['spend'], 2) }}</div>
                    <div class="page-stat-label">@lang('messages.spend')</div>
                </div>
            </div>

            <x-page-card :title="__('messages.promotion_delivery')">
                <div class="boost-meter-ends">
                    <span>{{ $boostSymbol }}{{ number_format($summary['spend'], 2) }} @lang('messages.spent')</span>
                    <span>{{ $boostSymbol }}{{ number_format($summary['remaining'], 2) }} @lang('messages.promotion_remaining')</span>
                </div>
                <div class="boost-meter" role="img" aria-label="{{ $summary['utilization'] }}%"><i style="width: {{ $summary['utilization'] }}%"></i></div>

                <dl class="boost-facts">
                    <div>
                        <dt>@lang('messages.promotion_budget')</dt>
                        <dd>{{ $boostSymbol }}{{ number_format($campaign->user_budget, 2) }}</dd>
                    </div>
                    <div>
                        <dt>@lang('messages.promotion_effective_cpc')</dt>
                        <dd>{{ $boostSymbol }}{{ number_format($summary['effective_cpc'], 2) }}</dd>
                    </div>
                    <div>
                        <dt>@lang('messages.promotion_effective_cpm')</dt>
                        <dd>{{ $boostSymbol }}{{ number_format($summary['effective_cpm'], 2) }}</dd>
                    </div>
                    <div>
                        <dt>@lang('messages.promotion_unique_visitors')</dt>
                        <dd>{{ number_format($summary['unique_visitors']) }}</dd>
                    </div>
                    <div>
                        <dt>@lang('messages.promotion_conversions')</dt>
                        <dd>
                            {{ number_format($conversions['count']) }}
                            {{-- Attributed ticket revenue is the number that tells an advertiser
                                 whether the campaign paid for itself, and it was being computed and
                                 thrown away. Shown next to the count rather than as its own stat so
                                 it reads as "what those conversions were worth". --}}
                            @if ($conversions['revenue'] > 0)
                            <span>({{ $boostSymbol }}{{ number_format($conversions['revenue'], 2) }})</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </x-page-card>

            @if (count($dailySeries) > 1)
            <x-page-card :title="__('messages.promotion_daily_delivery')">
                <div class="boost-chart"><canvas id="promotion-chart" role="img" aria-label="{{ __('messages.promotion_daily_delivery') }}"></canvas></div>
            </x-page-card>
            @endif

            @if ($countries->isNotEmpty() || $placements['schedule_count'] > 0)
            <div class="page-grid2">
                @if ($countries->isNotEmpty())
                <x-page-card flush :title="__('messages.promotion_countries')">
                    <table class="page-table is-compact">
                        <thead>
                            <tr>
                                <th scope="col">@lang('messages.country')</th>
                                <th scope="col" class="c-num">@lang('messages.promotion_impressions')</th>
                                {{-- Per-country clicks were already being recorded and aggregated, then
                                     dropped here. Which countries convert is the half of this table an
                                     advertiser can actually act on when setting targeting next time. --}}
                                <th scope="col" class="c-num">@lang('messages.clicks')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($countries as $country)
                            <tr>
                                <td class="c-main">{{ $country['name'] }}</td>
                                <td class="c-num" data-label="{{ __('messages.promotion_impressions') }}">{{ number_format($country['impressions']) }}</td>
                                <td class="c-num" data-label="{{ __('messages.clicks') }}">{{ number_format($country['clicks']) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </x-page-card>
                @endif

                @if ($placements['schedule_count'] > 0)
                {{-- Counts and kinds only. The schedules carrying this promotion did not agree to
                     have their traffic disclosed to the advertiser paying for it. --}}
                <x-page-card :title="__('messages.promotion_placements')"
                    :lead="trans_choice('messages.promotion_placement_count', $placements['schedule_count'], ['count' => $placements['schedule_count']])">
                    <dl class="page-kv">
                        @foreach ($placements['by_type'] as $row)
                        <div>
                            <dt>{{ __('messages.'.$row['type']) }}</dt>
                            <dd>{{ number_format($row['impressions']) }}</dd>
                        </div>
                        @endforeach
                    </dl>
                </x-page-card>
                @endif
            </div>
            @endif
        </div>
    </div>

    @if (count($dailySeries) > 1)
    <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>
    <script {!! nonce_attr() !!}>
        (function () {
            const series = @json($dailySeries);

            @include('boost.partials.chart-palette')

            let chart = null;
            function draw() {
                const palette = boostChartPalette();
                if (chart) {
                    chart.destroy();
                }
                chart = new Chart(document.getElementById('promotion-chart'), {
                    type: 'line',
                    data: {
                        labels: series.map(r => boostDayLabel(r.date)),
                        datasets: [
                            {
                                label: @json(__('messages.promotion_impressions')),
                                data: series.map(r => r.impressions),
                                borderColor: palette.blue,
                                backgroundColor: palette.blueSoft,
                                tension: 0.3,
                                fill: true,
                            },
                            {
                                label: @json(__('messages.clicks')),
                                data: series.map(r => r.clicks),
                                borderColor: palette.green,
                                tension: 0.3,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: { legend: { position: 'bottom', labels: { color: palette.ink, usePointStyle: true, boxHeight: 6 } } },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: palette.ink, maxRotation: 0, autoSkipPadding: 12 } },
                            y: { grid: { color: palette.grid }, ticks: { color: palette.ink, precision: 0 }, beginAtZero: true },
                        },
                    },
                });
            }

            draw();
            // The theme picker changes the palette without a reload; the chart re-reads its colours.
            new MutationObserver(draw).observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'data-theme'] });
        })();
    </script>
    @endif
</x-app-admin-layout>
