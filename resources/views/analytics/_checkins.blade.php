{{-- The Check-Ins tab: how many of the people who had tickets came, when they arrived, by ticket
     type, and night by night. Only a night that has begun can have a no-show: tickets for nights
     still to come are counted as sold and nowhere else (AnalyticsService::getCheckinStats()). --}}
@if ($checkinStats['has_data'])
@php
    $hasPast = $checkinStats['past_sold'] > 0;
    $arrivals = $checkinStats['arrival_hours'];
    $hasArrivals = array_sum($arrivals) > 0;
    // The hours people arrived in, one either side, so a chart of a night's door is not twenty
    // empty hours around four full ones.
    $first = $last = null;
    foreach ($arrivals as $hour => $n) {
        if ($n > 0) {
            $first ??= $hour;
            $last = $hour;
        }
    }
    $hours = $hasArrivals ? range(max(0, $first - 1), min(23, $last + 1)) : [];
    $types = collect($checkinStats['ticket_types']);
    $locale = app()->getLocale();
@endphp

<section class="ap-card rounded-xl an-hero" aria-labelledby="an-checkins-title">
    <div class="an-hero-head">
        <div class="an-figs">
            @if ($hasPast)
            <div class="an-fig is-lead">
                <p class="an-fig-label" id="an-checkins-title">
                    <span class="dashboard-icon p-1.5 rounded-lg bg-green-50 dark:bg-green-500/10" style="--icon-glow: rgba(34, 197, 94, 0.15)">
                        <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </span>
                    {{ __('messages.attendance_rate') }}
                </p>
                <p class="an-fig-value">{{ $checkinStats['attendance_rate'] }}%</p>
                {{-- The line under it says the same in words. --}}
                <div class="an-meter" aria-hidden="true">
                    <span style="width: {{ min(100, $checkinStats['attendance_rate']) }}%"></span>
                </div>
                <p class="an-fig-sub">{{ __('messages.dash_count_of_total', ['count' => number_format($checkinStats['total_checked_in']), 'total' => number_format($checkinStats['past_sold'])]) }}</p>
            </div>
            <div class="an-fig">
                <p class="an-fig-label">{{ __('messages.checked_in') }}</p>
                <p class="an-fig-value">{{ number_format($checkinStats['total_checked_in']) }}</p>
            </div>
            @endif
            <div class="an-fig {{ $hasPast ? '' : 'is-lead' }}">
                <p class="an-fig-label" @unless ($hasPast) id="an-checkins-title" @endunless>{{ __('messages.tickets_sold') }}</p>
                <p class="an-fig-value">{{ number_format($checkinStats['total_sold']) }}</p>
                @if ($checkinStats['upcoming_sold'] > 0)
                <p class="an-fig-sub"><span>{{ __('messages.an_for_upcoming') }}: <span class="an-num">{{ number_format($checkinStats['upcoming_sold']) }}</span></span></p>
                @endif
            </div>
            @if ($hasPast)
            <div class="an-fig">
                <p class="an-fig-label">{{ __('messages.no_shows') }}</p>
                <p class="an-fig-value">{{ number_format($checkinStats['no_shows']) }}</p>
                <p class="an-fig-sub"><span class="an-num">{{ $checkinStats['no_show_rate'] }}%</span></p>
            </div>
            @endif
        </div>
    </div>

    @if ($hasArrivals || $types->count() > 1)
    <div class="an-hero-split {{ $hasArrivals && $types->count() > 1 ? 'has-side' : '' }}">
        @if ($hasArrivals)
        <div class="min-w-0">
            <p class="an-chart-title">{{ __('messages.arrival_times') }}</p>
            <div class="an-chart is-short">
                <canvas id="arrivalChart" role="img" aria-label="{{ __('messages.arrival_times') }}"></canvas>
            </div>
            <table class="sr-only">
                <caption>{{ __('messages.arrival_times') }}</caption>
                <tbody>
                    @foreach ($hours as $hour)
                    <tr><th scope="row">{{ sprintf('%02d:00', $hour) }}</th><td>{{ $arrivals[$hour] }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            @php
                $arrivalChartData = [
                    'period' => 'hourly',
                    'locale' => $locale,
                    'keys' => $hours,
                    'series' => [['id' => 'arrivals', 'label' => __('messages.check_ins'), 'values' => array_map(fn ($h) => $arrivals[$h], $hours), 'tone' => 'views']],
                ];
            @endphp
            <script {!! nonce_attr() !!}>
                window.anCharts = window.anCharts || [];
                window.anCharts.push({ kind: 'bar', canvas: 'arrivalChart', data: @json($arrivalChartData) });
            </script>
        </div>
        @endif
        {{-- Attendance by ticket type, by name across the schedule's events: a VIP list that
             comes and a free list that does not are the two things a door wants to know. --}}
        @if ($types->count() > 1)
        <div class="an-side min-w-0">
            <div class="an-side-head">
                <p class="an-chart-title">{{ __('messages.an_by_ticket_type') }}</p>
                <span class="an-card-unit">{{ __('messages.attendance_rate') }}</span>
            </div>
            @include('analytics._list', [
                'listRows' => $types->map(fn ($t) => [
                    'label' => $t['name'],
                    'sub' => __('messages.dash_count_of_total', ['count' => number_format($t['checked_in']), 'total' => number_format($t['sold'])]),
                    'value' => (float) $t['attendance_rate'],
                    'display' => number_format($t['attendance_rate'], 1).'%',
                ])->values()->all(),
                'listShare' => false,
                'listBars' => true,
                'listMax' => 100,
            ])
        </div>
        @endif
    </div>
    @endif
</section>

@if (count($checkinStats['events_breakdown']) > 0)
<x-page-card :title="__('messages.events')" class="an-card" flush>
    <div class="page-scroll">
    <table class="page-table is-compact an-table">
        <thead>
            <tr>
                <th scope="col">{{ __('messages.event') }}</th>
                <th scope="col">{{ __('messages.date') }}</th>
                <th scope="col" class="c-num">{{ __('messages.sold') }}</th>
                <th scope="col" class="c-num">{{ __('messages.checked_in') }}</th>
                <th scope="col" class="an-col-rate">{{ __('messages.attendance_rate') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($checkinStats['events_breakdown'] as $row)
            <tr>
                <td class="c-main c-strong" v-pre><bdi>{{ $row['event_name'] }}</bdi></td>
                <td class="c-date">{{ $row['event_date'] ? \Carbon\Carbon::parse($row['event_date'])->locale($locale)->isoFormat('ll') : '' }}</td>
                {{-- The rate beside is over the dates that have begun, so that is what Sold
                     counts; tickets for this event's later dates are said apart. --}}
                <td data-label="{{ __('messages.sold') }}" class="c-num">
                    {{ number_format($row['is_upcoming'] ? $row['sold'] : $row['past_sold']) }}
                    @if (! $row['is_upcoming'] && $row['sold'] > $row['past_sold'])
                    <span class="c-quiet" title="{{ __('messages.an_for_upcoming') }}">+{{ number_format($row['sold'] - $row['past_sold']) }}</span>
                    @endif
                </td>
                <td data-label="{{ __('messages.checked_in') }}" class="c-num">{{ $row['is_upcoming'] ? '' : number_format($row['checked_in']) }}</td>
                <td class="an-col-rate" data-label="{{ __('messages.attendance_rate') }}">
                    @if ($row['is_upcoming'])
                        <span class="event-status is-info">{{ __('messages.an_upcoming') }}</span>
                    @else
                        <span class="an-rate">
                            <span class="an-meter is-small" aria-hidden="true"><span style="width: {{ min(100, $row['attendance_rate']) }}%"></span></span>
                            <span class="an-num">{{ number_format($row['attendance_rate'], 1) }}%</span>
                        </span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    </div>
</x-page-card>
@endif
@else
@include('analytics._empty', [
    'emptyTitle' => __('messages.no_checkin_data'),
    'emptyText' => __('messages.checkin_data_will_appear'),
    'emptyFigures' => [__('messages.attendance_rate'), __('messages.tickets_sold'), __('messages.no_shows')],
    'emptyHref' => null,
    'emptyLabel' => null,
])
@endif
