<x-app-admin-layout>

    <x-slot name="head">
        @include('boost.partials.styles')
    </x-slot>

    @php
        $boostSymbol = $campaign->getCurrencySymbol();
        $hasDailySeries = $campaign->daily_analytics && count($campaign->daily_analytics) > 1;
        // Meta's own word for an ad's state, in the reader's language where the portal has one.
        $adStates = ['ACTIVE' => 'active', 'PAUSED' => 'paused', 'DISAPPROVED' => 'rejected', 'COMPLETED' => 'completed'];
    @endphp

    {{-- A campaign bought on Facebook and Instagram: what it is doing, what it has cost, the ad
         itself. What can be done to it (pause, cancel) is in the title row, where every page of
         the portal keeps its actions; they used to be a card at the foot of the page. --}}
    <div class="page-shell">
        <x-page-header
            :title="$campaign->event?->translatedName() ?? __('messages.deleted_event')"
            :lead="($campaign->role?->name ?? __('messages.deleted')).' · '.__('messages.promotion_channel_meta')"
            :back="route('boost.index')" :back-label="__('messages.boost')">
            <x-slot name="actions">
                @include('boost.partials.status', ['status' => $campaign->status])

                @if ($campaign->canBeCancelled())
                <form method="POST" action="{{ route('boost.cancel', ['hash' => $campaign->hashedId()]) }}"
                      data-confirm="{{ __('messages.boost_cancel_confirm') }}">
                    @csrf
                    <x-danger-button class="boost-danger">{{ __('messages.cancel_campaign') }}</x-danger-button>
                </form>
                @endif

                @if ($campaign->canBePaused() || $campaign->canBeResumed())
                <form method="POST" action="{{ route('boost.toggle_pause', ['hash' => $campaign->hashedId()]) }}">
                    @csrf
                    @if ($campaign->isActive())
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

            @if ($campaign->status === 'rejected' && $campaign->meta_rejection_reason)
            <x-page-notice tone="error" :title="__('messages.ad_rejected')">
                <bdi>{{ $campaign->meta_rejection_reason }}</bdi>
            </x-page-notice>
            @endif

            <div class="ap-card rounded-xl page-stats is-auto">
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($campaign->impressions) }}</div>
                    <div class="page-stat-label">{{ __('messages.impressions') }}</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($campaign->reach) }}</div>
                    <div class="page-stat-label">{{ __('messages.reach') }}</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($campaign->clicks) }}</div>
                    <div class="page-stat-label">{{ __('messages.clicks') }}</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($campaign->conversions ?? 0) }}</div>
                    <div class="page-stat-label">{{ __('messages.conversions') }}</div>
                </div>
            </div>

            <div class="page-grid2">
                <x-page-card :title="__('messages.budget_utilization')">
                    <div class="boost-meter-ends">
                        <span>{{ $boostSymbol }}{{ number_format($campaign->actual_spend ?? 0, 2) }} {{ __('messages.spent') }}</span>
                        <span>{{ $boostSymbol }}{{ number_format($campaign->user_budget, 2) }} {{ __('messages.budget') }}</span>
                    </div>
                    <div class="boost-meter" role="img" aria-label="{{ $campaign->getBudgetUtilization() }}%"><i style="width: {{ $campaign->getBudgetUtilization() }}%"></i></div>

                    <dl class="page-kv">
                        <div>
                            <dt>{{ __('messages.ctr') }}</dt>
                            <dd>{{ number_format($campaign->ctr, 2) }}%</dd>
                        </div>
                        <div>
                            <dt>{{ __('messages.cpc') }}</dt>
                            <dd>{{ $boostSymbol }}{{ number_format($campaign->cpc, 2) }}</dd>
                        </div>
                        <div>
                            <dt>{{ __('messages.cpm') }}</dt>
                            <dd>{{ $boostSymbol }}{{ number_format($campaign->cpm, 2) }}</dd>
                        </div>
                    </dl>
                </x-page-card>

                <x-page-card :title="__('messages.campaign_details')">
                    <dl class="page-kv">
                        @if (config('app.hosted'))
                        <div>
                            <dt>{{ __('messages.total_charged') }}</dt>
                            <dd>{{ $boostSymbol }}{{ number_format($campaign->total_charged ?? $campaign->getTotalCost(), 2) }}</dd>
                        </div>
                        @endif
                        @if ($campaign->scheduled_start)
                        <div>
                            <dt>{{ __('messages.start_date') }}</dt>
                            <dd>{{ $campaign->scheduled_start->translatedFormat('M j, Y') }}</dd>
                        </div>
                        @endif
                        @if ($campaign->scheduled_end)
                        <div>
                            <dt>{{ __('messages.end_date') }}</dt>
                            <dd>{{ $campaign->scheduled_end->translatedFormat('M j, Y') }}</dd>
                        </div>
                        @endif
                        @if ($campaign->analytics_synced_at)
                        <div>
                            <dt>{{ __('messages.last_updated') }}</dt>
                            <dd>{{ $campaign->analytics_synced_at->diffForHumans() }}</dd>
                        </div>
                        @endif
                    </dl>
                </x-page-card>
            </div>

            @if ($hasDailySeries)
            <x-page-card :title="__('messages.daily_performance')">
                <div class="boost-chart"><canvas id="performance-chart" role="img" aria-label="{{ __('messages.daily_performance') }}"></canvas></div>
            </x-page-card>
            @endif

            @if ($campaign->ads->isNotEmpty())
            <x-page-card :title="__('messages.ad_creative')">
                <div class="boost-ads">
                    @foreach ($campaign->ads as $ad)
                    <div>
                        @if ($campaign->ads->count() > 1)
                        <div class="boost-ad-head">
                            <span>{{ __('messages.variant') }} {{ $ad->variant }}</span>
                            @if ($ad->is_winner)
                            <span class="event-status is-on">{{ __('messages.winner') }}</span>
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
                        <p class="boost-ad-foot">
                            @if (isset($adStates[$ad->meta_status]))
                            @include('boost.partials.status', ['status' => $adStates[$ad->meta_status]])
                            @else
                            <span class="event-status">{{ \Illuminate\Support\Str::headline(strtolower($ad->meta_status)) }}</span>
                            @endif
                        </p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </x-page-card>
            @endif
        </div>
    </div>

    @if ($hasDailySeries)
    <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>
    <script {!! nonce_attr() !!}>
        (function () {
            const dailyData = @json($campaign->daily_analytics);
            const labels = Object.keys(dailyData);
            const impressions = labels.map(d => dailyData[d].impressions || 0);
            const clicks = labels.map(d => dailyData[d].clicks || 0);

            @include('boost.partials.chart-palette')

            let chart = null;
            function draw() {
                const palette = boostChartPalette();
                if (chart) {
                    chart.destroy();
                }
                chart = new Chart(document.getElementById('performance-chart'), {
                    type: 'line',
                    data: {
                        labels: labels.map(boostDayLabel),
                        datasets: [
                            {
                                label: @json(__("messages.impressions")),
                                data: impressions,
                                borderColor: palette.blue,
                                backgroundColor: palette.blueSoft,
                                fill: true,
                                tension: 0.3,
                                yAxisID: 'y',
                            },
                            {
                                label: @json(__("messages.clicks")),
                                data: clicks,
                                borderColor: palette.green,
                                backgroundColor: 'transparent',
                                tension: 0.3,
                                yAxisID: 'y1',
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: palette.ink, maxRotation: 0, autoSkipPadding: 12 } },
                            y: { position: 'left', beginAtZero: true, grid: { color: palette.grid }, ticks: { color: palette.ink, precision: 0 } },
                            y1: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false }, ticks: { color: palette.ink, precision: 0 } },
                        },
                        plugins: {
                            legend: { position: 'bottom', labels: { color: palette.ink, usePointStyle: true, boxHeight: 6 } },
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
