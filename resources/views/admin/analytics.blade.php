<x-app-admin-layout>
    @include('admin.partials._navigation', ['active' => 'analytics'])

    @php
        $deviceTotal = $desktopViews + $mobileViews + $tabletViews;
        // A share is marked left-to-right, as on the dashboard: in a right-to-left language the
        // percent sign otherwise lands on the wrong side of the number.
        $pct = fn ($part, $whole) => new \Illuminate\Support\HtmlString('<span dir="ltr">'.($whole > 0 ? round(($part / $whole) * 100, 1) : 0).'%</span>');
        $ltr = fn ($text) => new \Illuminate\Support\HtmlString('<span dir="ltr">'.e($text).'</span>');
        $sourceTotal = $directViews + $searchViews + $socialViews + $emailViews + $newsletterViews + $otherViews;
        // The same colours the two charts draw each series in.
        $devices = [
            [__('messages.desktop'), $desktopViews, 'var(--brand-blue)'],
            [__('messages.mobile'), $mobileViews, '#10B981'],
            [__('messages.tablet'), $tabletViews, '#8B5CF6'],
        ];
        $sources = [
            [__('messages.direct'), $directViews],
            [__('messages.search'), $searchViews],
            [__('messages.social'), $socialViews],
            [__('messages.email'), $emailViews],
            [__('messages.newsletter_source'), $newsletterViews],
            [__('messages.other'), $otherViews],
        ];
        // One colour for every bar: they were six colours that meant nothing.
        $features = [
            [__('messages.google_calendar_integration'), $googleCalendarPercent, $googleCalendarEnabled],
            [__('messages.stripe_payments'), $stripeEventsPercent, $stripeEvents],
            [__('messages.custom_domain'), $customDomainPercent, $customDomainEnabled],
            [__('messages.custom_css'), $customCssPercent, $customCssEnabled],
            [__('messages.newsletter'), $newsletterPercent, $newsletterEnabled],
            [__('messages.boost'), $boostPercent, $boostEnabled],
        ];
    @endphp

    <div class="page-head">
        <p class="page-lead">{{ __('messages.admin_analytics_lead') }}</p>
        <div class="page-actions">
            @include('admin.partials._date-range-filter', ['range' => $range])
        </div>
    </div>

    <div class="page-shell page-stack">
        {{-- Traffic to schedule pages in the period. A chart with nothing to draw keeps its
             canvas (the script below draws on it by id) and is covered by a line saying so. --}}
        <div class="page-grid2">
            <x-page-card :title="__('messages.device_breakdown')">
                <x-slot name="aside"><span class="insight-when">@lang('messages.selected_period')</span></x-slot>
                <div class="insight-donut" @if ($deviceTotal === 0) hidden @endif>
                    <div class="insight-donut-chart">
                        <canvas id="deviceChart"></canvas>
                    </div>
                    <dl class="page-kv">
                        @foreach ($devices as [$label, $views, $color])
                        <div>
                            <dt><span class="insight-dot" style="background: {{ $color }}"></span>{{ $label }}</dt>
                            <dd>{{ number_format($views) }}<small>{{ $pct($views, $deviceTotal) }}</small></dd>
                        </div>
                        @endforeach
                    </dl>
                </div>
                @if ($deviceTotal === 0)
                <x-page-empty compact :title="__('messages.no_data_for_period')" />
                @else
                <x-slot name="foot">
                    <p class="mt-4">@lang('messages.total'): {{ number_format($totalPageViews) }} @lang('messages.page_views')</p>
                </x-slot>
                @endif
            </x-page-card>

            <x-page-card flush :title="__('messages.traffic_sources')">
                <x-slot name="aside"><span class="insight-when">@lang('messages.selected_period')</span></x-slot>
                <div class="insight-pad" @if ($sourceTotal === 0) hidden @endif>
                    <div class="h-48">
                        <canvas id="trafficSourcesChart"></canvas>
                    </div>
                </div>
                @if ($sourceTotal === 0)
                <x-page-empty compact :title="__('messages.no_data_for_period')" />
                @else
                <div class="page-stats is-auto insight-strip-top">
                    @foreach ($sources as [$label, $views])
                    <div class="page-stat">
                        <div class="page-stat-value">{{ number_format($views) }}</div>
                        <div class="page-stat-label">{{ $label }}</div>
                    </div>
                    @endforeach
                </div>
                @endif
            </x-page-card>
        </div>

        <div class="page-grid2">
            <x-page-card :title="__('messages.feature_adoption')">
                <div class="insight-bars">
                    @foreach ($features as [$label, $percent, $count])
                    <div>
                        <div class="insight-bar-head">
                            <span class="insight-bar-name">{{ $label }}</span>
                            <span class="insight-bar-figure"><b>{{ $ltr($percent.'%') }}</b> ({{ number_format($count) }} @lang('messages.schedules'))</span>
                        </div>
                        <div class="insight-bar"><i style="width: {{ min($percent, 100) }}%"></i></div>
                    </div>
                    @endforeach
                </div>
                <x-slot name="foot">
                    <p class="mt-4">@lang('messages.based_on_total_schedules', ['count' => number_format($totalSchedules)])</p>
                </x-slot>
            </x-page-card>

            {{-- From a connected account to a finished one to an event that takes payment. The
                 share under a step is of the step before it; it was a line of bare numbers and
                 arrows under the chart. --}}
            <x-page-card flush :title="__('messages.stripe_funnel')">
                <div class="insight-pad">
                    <div class="h-48">
                        <canvas id="stripeFunnelChart"></canvas>
                    </div>
                </div>
                <div class="page-stats insight-strip-top">
                    <div class="page-stat">
                        <div class="page-stat-value">{{ number_format($stripeConnected) }}</div>
                        <div class="page-stat-label">@lang('messages.stripe_connected')</div>
                    </div>
                    <div class="page-stat">
                        <div class="page-stat-value">{{ number_format($stripeOnboarded) }}</div>
                        <div class="page-stat-label">@lang('messages.stripe_onboarded')</div>
                        <div class="page-stat-sub">@lang('messages.conversion_rate') {{ $ltr(($stripeConnected > 0 ? round(($stripeOnboarded / $stripeConnected) * 100) : 0).'%') }}</div>
                    </div>
                    <div class="page-stat">
                        <div class="page-stat-value">{{ number_format($stripeEvents) }}</div>
                        <div class="page-stat-label">@lang('messages.stripe_events')</div>
                        <div class="page-stat-sub">@lang('messages.conversion_rate') {{ $ltr(($stripeOnboarded > 0 ? round(($stripeEvents / $stripeOnboarded) * 100) : 0).'%') }}</div>
                    </div>
                </div>
            </x-page-card>
        </div>

        <x-page-card :title="__('messages.top_schedules_by_events')">
            <div class="h-64" @if ($topSchedulesByEvents->isEmpty()) hidden @endif>
                <canvas id="topSchedulesChart"></canvas>
            </div>
            @if ($topSchedulesByEvents->isEmpty())
            <x-page-empty compact :title="__('messages.no_data_available')" />
            @endif
        </x-page-card>
    </div>

    <x-slot name="head">
        @include('admin.partials._insight-styles')
    </x-slot>

    {{-- Chart.js --}}
    <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>

    <script {!! nonce_attr() !!}>
        function initCharts() {
            if (typeof Chart === 'undefined') {
                setTimeout(initCharts, 50);
                return;
            }

            // The portal's own palette, read from its tokens. This used to ask the operating
            // system whether it was dark, so a light portal on a dark machine drew its charts
            // with black gridlines; and a hex could not follow the six palettes.
            const apStyle = getComputedStyle(document.documentElement);
            const apColor = (token, fallback) => {
                const value = apStyle.getPropertyValue(token).trim();
                return value ? 'rgb(' + value.split(/\s+/).join(', ') + ')' : fallback;
            };
            const isDarkMode = document.documentElement.classList.contains('dark');

            const textColor = apColor('--ap-ink-3', isDarkMode ? '#9CA3AF' : '#6B7280');
            const gridColor = apColor('--ap-border', isDarkMode ? '#2d2d30' : '#E5E7EB');
            const brandBlue = getComputedStyle(document.documentElement).getPropertyValue('--brand-blue').trim();

            // Device Breakdown Chart
            const deviceCtx = document.getElementById('deviceChart').getContext('2d');
            new Chart(deviceCtx, {
                type: 'doughnut',
                data: {
                    labels: [@json(__('messages.desktop')), @json(__('messages.mobile')), @json(__('messages.tablet'))],
                    datasets: [{
                        data: [{{ $desktopViews }}, {{ $mobileViews }}, {{ $tabletViews }}],
                        backgroundColor: [brandBlue, '#10B981', '#8B5CF6'],
                        borderColor: apColor('--ap-surface', isDarkMode ? '#252526' : '#FFFFFF'),
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

            // Traffic Sources Chart
            const trafficSourcesCtx = document.getElementById('trafficSourcesChart').getContext('2d');
            new Chart(trafficSourcesCtx, {
                type: 'bar',
                data: {
                    labels: [@json(__('messages.direct')), @json(__('messages.search')), @json(__('messages.social')), @json(__('messages.email')), @json(__('messages.newsletter_source')), @json(__('messages.other'))],
                    datasets: [{
                        label: @json(__('messages.views')),
                        data: [{{ $directViews }}, {{ $searchViews }}, {{ $socialViews }}, {{ $emailViews }}, {{ $newsletterViews }}, {{ $otherViews }}],
                        backgroundColor: ['#6366F1', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#6B7280']
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

            // Stripe Funnel Chart
            const stripeFunnelCtx = document.getElementById('stripeFunnelChart').getContext('2d');
            new Chart(stripeFunnelCtx, {
                type: 'bar',
                data: {
                    labels: [@json(__('messages.stripe_connected')), @json(__('messages.stripe_onboarded')), @json(__('messages.stripe_events'))],
                    datasets: [{
                        label: @json(__('messages.schedules')),
                        data: [{{ $stripeConnected }}, {{ $stripeOnboarded }}, {{ $stripeEvents }}],
                        backgroundColor: ['#16A34A', '#22C55E', '#86EFAC']
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

            // Top Schedules Chart
            @if ($topSchedulesByEvents->isNotEmpty())
            const topSchedulesCtx = document.getElementById('topSchedulesChart').getContext('2d');
            new Chart(topSchedulesCtx, {
                type: 'bar',
                data: {
                    labels: @json($topSchedulesByEvents->pluck('name')->toArray()),
                    datasets: [{
                        label: @json(__('messages.events')),
                        data: @json($topSchedulesByEvents->pluck('events_count')->toArray()),
                        backgroundColor: brandBlue
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
        }

        // Initialize charts when DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initCharts);
        } else {
            initCharts();
        }
    </script>

</x-app-admin-layout>
