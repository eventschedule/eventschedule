{{-- The Revenue tab: the money and when it came in, the window before beside it, then which
     events made it, the promo codes that cut it, and the two funnels that brought buyers (a boost,
     a newsletter). Every figure is in the currency it was taken in: with more than one, each is
     said and nothing is added across them, and the chart is left out. --}}
@if ($conversionStats['total_sales'] > 0 || $boostStats['has_data'] || $newsletterStats['has_data'] || $topEventsByRevenue->isNotEmpty())
@php
    $u = \App\Utils\UrlUtils::class;
    $money = fn ($amount, $currency) => \App\Utils\MoneyUtils::format($amount, $currency ?: 'USD');
    $primary = $conversionStats['primary_currency'] ?? 'USD';
    $periodLink = fn (string $to) => route('analytics', array_filter([
        'role_id' => $u::encodeId($selectedRoleId),
        'event_id' => $u::encodeId($tabEventId),
        'range' => $range,
        'period' => $to !== $defaultPeriod ? $to : null,
        'tab' => 'revenue',
    ]));
    $change = $revenueComparison['percentage_change'] ?? null;
    $topEventsCurrencyCount = $topEventsByRevenue->pluck('currency_code')->unique()->count();
    $cards = array_values(array_filter([
        ($topEventsByRevenue->isNotEmpty() && ! $selectedEventId) ? 'events' : null,
        $conversionStats['promo_sales'] > 0 ? 'promo' : null,
    ]));
    $dash = '–';
@endphp

@if ($conversionStats['total_sales'] > 0)
<section class="ap-card rounded-xl an-hero" aria-labelledby="an-revenue-title">
    <div class="an-hero-head">
        <div class="an-figs">
            <div class="an-fig is-lead">
                <p class="an-fig-label" id="an-revenue-title">
                    <span class="dashboard-icon p-1.5 rounded-lg bg-green-50 dark:bg-green-500/10" style="--icon-glow: rgba(34, 197, 94, 0.15)">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" /></svg>
                    </span>
                    {{ __('messages.total_revenue') }}
                </p>
                @if ($conversionStats['currency_count'] > 1)
                    <div class="an-fig-value is-list">
                        @foreach ($conversionStats['total_revenue_by_currency'] as $entry)
                        <p>{{ $money($entry['amount'], $entry['currency_code']) }}</p>
                        @endforeach
                    </div>
                @else
                    <p class="an-fig-value">{{ $money($conversionStats['total_revenue'], $primary) }}</p>
                @endif
                @if ($revenueComparison)
                <p class="an-fig-sub">
                    @if (! is_null($change))
                    <span class="an-delta {{ $change > 0 ? 'is-up' : ($change < 0 ? 'is-down' : 'is-flat') }}">
                        @if ($change != 0)<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $change > 0 ? 'M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18' : 'M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3' }}" /></svg>@endif
                        <bdi dir="ltr">{{ $change > 0 ? '+' : '' }}{{ $change }}%</bdi>
                    </span>
                    @endif
                    @if ($revenueComparison['previous'] > 0)
                    <span>{{ __('messages.'.$revenueComparison['label']) }} <span class="an-num">({{ $money($revenueComparison['previous'], $primary) }})</span></span>
                    @else
                    <span>{{ __('messages.an_no_earlier') }}</span>
                    @endif
                </p>
                @endif
            </div>
            <div class="an-fig">
                <p class="an-fig-label">{{ __('messages.sales') }}</p>
                <p class="an-fig-value">{{ number_format($conversionStats['total_sales']) }}</p>
            </div>
            <div class="an-fig">
                <p class="an-fig-label" title="{{ __('messages.conversion_rate_tooltip') }}">{{ __('messages.conversion_rate') }} <svg class="an-info" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg><span class="sr-only">{{ __('messages.conversion_rate_tooltip') }}</span></p>
                <p class="an-fig-value">{{ $conversionStats['conversion_rate'] }}%</p>
            </div>
            <div class="an-fig">
                <p class="an-fig-label" title="{{ __('messages.revenue_per_view_tooltip') }}">{{ __('messages.revenue_per_view') }} <svg class="an-info" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg><span class="sr-only">{{ __('messages.revenue_per_view_tooltip') }}</span></p>
                <p class="an-fig-value">{{ ! is_null($conversionStats['revenue_per_view']) ? $money($conversionStats['revenue_per_view'], $primary) : $dash }}</p>
            </div>
        </div>
    </div>
    @if ($revenueChart)
    @php $revenueHasPrevious = array_sum($revenueChart['previous']) > 0; @endphp
    <div class="an-chart-bar">
        @if ($revenueHasPrevious || $revenueChart['events'])
        <ul class="an-legend" role="list">
            <li><span class="an-key is-bar" aria-hidden="true"></span>{{ __('messages.revenue') }}</li>
            @if ($revenueHasPrevious)
            <li><span class="an-key is-previous" aria-hidden="true"></span>{{ __('messages.views_previous_period') }}</li>
            @endif
            @if ($revenueChart['events'])
            <li><span class="an-key is-event" aria-hidden="true"></span>{{ __('messages.events') }}</li>
            @endif
        </ul>
        @endif
        <nav class="an-seg" aria-label="{{ __('messages.revenue') }}">
            @foreach (['daily', 'weekly', 'monthly'] as $p)
            <a href="{{ $periodLink($p) }}" @if ($period === $p) aria-current="page" @endif>{{ __('messages.'.$p) }}</a>
            @endforeach
        </nav>
    </div>
    <div class="an-chart is-short">
        <canvas id="revenueChart" role="img" aria-label="{{ __('messages.revenue') }}"></canvas>
    </div>
    <table class="sr-only">
        <caption>{{ __('messages.revenue') }}</caption>
        <thead><tr><th scope="col">{{ __('messages.date') }}</th><th scope="col">{{ __('messages.revenue') }}</th></tr></thead>
        <tbody>
            @foreach ($revenueChart['keys'] as $i => $key)
            <tr><th scope="row">{{ $key }}</th><td>{{ $money($revenueChart['values'][$i], $revenueChart['currency']) }}</td></tr>
            @endforeach
        </tbody>
    </table>
    @php
        $revenueChartData = [
            'period' => $period,
            'locale' => app()->getLocale(),
            'keys' => $revenueChart['keys'],
            'currency' => $revenueChart['currency'],
            'previousKeys' => $revenueChart['previousKeys'],
            'series' => array_values(array_filter([
                ['id' => 'revenue', 'label' => __('messages.revenue'), 'values' => $revenueChart['values'], 'tone' => 'views'],
                $revenueHasPrevious ? ['id' => 'previous', 'label' => __('messages.views_previous_period'), 'values' => $revenueChart['previous'], 'tone' => 'previous'] : null,
            ])),
            'partial' => $range !== 'last_month',
            'soFar' => __('messages.an_so_far'),
            'events' => $revenueChart['events'],
        ];
    @endphp
    <script {!! nonce_attr() !!}>
        window.anCharts = window.anCharts || [];
        window.anCharts.push({ kind: 'bar', canvas: 'revenueChart', data: @json($revenueChartData) });
    </script>
    @endif
</section>
@endif

@if ($cards)
<div class="an-grid">
    @foreach ($cards as $i => $card)
        @if ($card === 'events')
        <x-page-card :title="__('messages.top_events_by_revenue')" class="an-card {{ count($cards) === 1 ? 'an-w2-full an-w3-full' : 'an-w3-half' }}">
            <x-slot:aside><span class="an-card-unit">{{ __('messages.revenue') }}</span></x-slot:aside>
            {{-- Across currencies a longer bar is not more money, so then there are no bars. --}}
            @include('analytics._list', [
                'listRows' => $topEventsByRevenue->map(fn ($row) => [
                    'label' => $row['event']->translatedName(),
                    'sub' => __('messages.sales').': '.number_format($row['sales_count']),
                    'value' => (float) $row['revenue'],
                    'display' => $money($row['revenue'], $row['currency_code']),
                    'thumb' => $row['event']->getImageUrl() ?: null,
                    'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
                    'href' => $eventLink($row['event'], 'revenue'),
                    'title' => __('messages.an_show_event'),
                ])->values()->all(),
                'listShare' => false,
                'listBars' => $topEventsCurrencyCount === 1,
            ])
        </x-page-card>
        @elseif ($card === 'promo')
        <x-page-card :title="__('messages.promo_codes')" class="an-card {{ count($cards) === 1 ? 'an-w2-full an-w3-full' : 'an-w3-half' }}">
            <div class="an-minifigs">
                <div>
                    <p class="an-fig-label">{{ __('messages.promo_sales') }}</p>
                    <p class="an-mini-value">{{ number_format($conversionStats['promo_sales']) }} <span>/ {{ number_format($conversionStats['total_sales']) }}</span></p>
                </div>
                <div>
                    <p class="an-fig-label">{{ __('messages.total_discounts') }}</p>
                    @if (count($conversionStats['promo_discounts_by_currency']) > 1)
                        @foreach ($conversionStats['promo_discounts_by_currency'] as $entry)
                        <p class="an-mini-value">{{ $money($entry['amount'], $entry['currency_code']) }}</p>
                        @endforeach
                    @else
                        <p class="an-mini-value">{{ $money($conversionStats['promo_discounts'], $conversionStats['promo_discounts_by_currency'][0]['currency_code'] ?? $primary) }}</p>
                    @endif
                </div>
            </div>
            @if ($promoCodeStats->isNotEmpty())
            <div class="page-scroll">
            <table class="page-table is-compact an-table">
                <thead>
                    <tr>
                        <th scope="col">{{ __('messages.promo_code') }}</th>
                        <th scope="col" class="c-num">{{ __('messages.discount') }}</th>
                        <th scope="col" class="c-num">{{ __('messages.sales') }}</th>
                        <th scope="col" class="c-num">{{ __('messages.total_discounts') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($promoCodeStats as $stat)
                    <tr>
                        <td data-label="{{ __('messages.promo_code') }}"><span class="an-code" v-pre>{{ $stat['code'] }}</span></td>
                        <td data-label="{{ __('messages.discount') }}" class="c-num">
                            @if ($stat['type'] === 'percentage')
                                {{ rtrim(rtrim(number_format($stat['value'], 3), '0'), '.') }}%
                            @else
                                {{ $money($stat['value'], $stat['currency_code'] ?? $primary) }}
                            @endif
                        </td>
                        <td data-label="{{ __('messages.sales') }}" class="c-num">{{ number_format($stat['sales_count']) }}</td>
                        <td data-label="{{ __('messages.total_discounts') }}" class="c-num">{{ $money($stat['total_discount'], $stat['currency_code'] ?? $primary) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            @endif
        </x-page-card>
        @endif
    @endforeach
</div>
@endif

{{-- The funnels are the whole schedule's: a boost or a newsletter is not one event's. --}}
@if ($selectedEventId && ($boostStats['has_data'] || $newsletterStats['has_data']))
<p class="an-note">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
    <span>{{ __('messages.an_whole_schedule_cards') }}</span>
</p>
@endif
@foreach (['boost', 'newsletter'] as $funnel)
    @php $stats = $funnel === 'boost' ? $boostStats : $newsletterStats; @endphp
    @if ($stats['has_data'])
        @include('analytics._funnel', ['funnel' => $funnel, 'stats' => $stats])
    @endif
@endforeach

@else
@include('analytics._empty', [
    'emptyTitle' => __('messages.no_revenue_data'),
    'emptyText' => __('messages.revenue_data_will_appear'),
    'emptyFigures' => [__('messages.total_revenue'), __('messages.sales'), __('messages.conversion_rate')],
    'emptyHref' => null,
    'emptyLabel' => null,
])
@endif
