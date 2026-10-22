{{-- One panel of the dashboard's cards (AdminDashboardController::panel()), fetched once the page
     is up and put in place by the cards' script (admin/dashboard/_cards-script). Not a page: no
     layout, no script that runs (scripts set with innerHTML never do), and the charts' numbers
     ride in a JSON block that the script reads.

     What each card counts is App\Services\AdminDashboard's to say. The order down the page is
     "what's new first": the newest schedules and events, then the breakdowns that move slowly. --}}
@php
    $hosted = (bool) config('app.hosted');

    // "+16%" and the colour it wears. One decimal only when there is one. Marked left-to-right,
    // as every signed figure on the page is: in a right-to-left language the sign otherwise
    // lands after the number.
    $pct = fn ($value) => new \Illuminate\Support\HtmlString('<span dir="ltr">'.($value >= 0 ? '+' : '').rtrim(rtrim(number_format((float) $value, 1), '0'), '.').'%</span>');
    $tone = fn ($value) => $value >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-600 dark:text-red-400';
    $pair = 'grid grid-cols-1 lg:grid-cols-2 gap-4';
@endphp

{{-- data-now: the server's clock, for the "new since your last visit" marks. A row's time is
     the server's, so the visit it is compared with has to be too. --}}
<div class="space-y-4" data-dash-fragment="{{ $panel }}" data-now="{{ now()->getTimestamp() }}" @if ($dashboard['firstRun'] ?? false) data-first-run @endif>
    @if ($panel === 'recent')
        @if ($dashboard['firstRun'] ?? false)
            @include('admin.dashboard._first-run')
        @else
            <div class="{{ $pair }}">
                @include('admin.dashboard._recent-schedules', ['schedules' => $dashboard['schedules']])
                @include('admin.dashboard._recent-events', ['recentEvents' => $dashboard['recentEvents'], 'events' => $dashboard['eventCounts']])
            </div>
        @endif
    @elseif ($panel === 'growth')
        @php
            $signups = $dashboard['signups'];
            $active = $dashboard['active'];
            $events = $dashboard['events'];
            $series = $active['series'] ?? [];
            $charts = [
                'data' => [
                    'signups' => [
                        'labels' => array_column($signups['days'] ?? [], 'label'),
                        'organizers' => array_column($signups['days'] ?? [], 'organizers'),
                        'others' => array_column($signups['days'] ?? [], 'others'),
                    ],
                    'active' => [
                        'labels' => array_column($series, 'label'),
                        'exact' => array_map(fn ($point) => $point['exact'] ? $point['active'] : null, $series),
                        'estimate' => array_map(fn ($point) => $point['exact'] ? null : $point['active'], $series),
                    ],
                    'rtl' => is_rtl(),
                ],
                'text' => [
                    'organizers' => __('messages.admin_dash_organizers'),
                    'others' => __('messages.admin_dash_other_accounts'),
                    'exact' => __('messages.admin_dash_exact'),
                    'estimate' => __('messages.admin_dash_estimate'),
                    'exactFrom' => ($active['exact_from_label'] ?? null) ? __('messages.admin_dash_exact_from', ['date' => $active['exact_from_label']]) : null,
                ],
            ];
        @endphp

        <div class="{{ $pair }}">
            @include('admin.dashboard._signups')
            @include('admin.dashboard._active-users')
        </div>

        <div class="{{ $pair }}">
            @include('admin.dashboard._sources')
            @include('admin.dashboard._upcoming-events')
        </div>

        <script type="application/json" data-dash-charts>@json($charts)</script>
    @else
        @include('admin.dashboard._federation', ['federation' => $dashboard['federation']])
        @include('admin.dashboard._system', ['system' => $dashboard['system']])
    @endif
</div>
