{{-- The Web Analytics tab: one card that says how many people opened these pages and when, with
     the window before laid under it, then what they opened, where from, where they are and on
     what. Everything the old tab held is still here; what changed is the form. The four "views"
     cards became one figure with its change beside it, the eleven Chart.js boxes became ranked
     lists that print every number, and the five cards about where visitors came from became one
     card with a switch. --}}
@if ($totalViews > 0 || $appearanceViews > 0 || ($missingLinks ?? collect())->isNotEmpty())
@php
    $locale = app()->getLocale();
    $u = \App\Utils\UrlUtils::class;
    $hasPrevious = (bool) $periodComparison;
    $viewsInRange = $periodComparison ? $periodComparison['current_period'] : (int) array_sum($viewsChart['views']);
    $previousViews = $periodComparison['previous_period'] ?? 0;
    $change = $periodComparison && $previousViews > 0 ? $periodComparison['percentage_change'] : null;

    // The busiest day (week, month) of the range, said as a date: the number a person asks next.
    $busiest = null;
    if (count($viewsChart['views']) > 1 && max($viewsChart['views']) > 0) {
        $at = array_search(max($viewsChart['views']), $viewsChart['views'], true);
        $key = $viewsChart['keys'][$at];
        $when = match ($period) {
            'weekly' => \Carbon\Carbon::now()->setISODate((int) substr($key, 0, 4), (int) substr($key, 5, 2))->startOfWeek(\Carbon\Carbon::MONDAY)->locale($locale)->isoFormat('ll'),
            'monthly' => \Carbon\Carbon::parse($key.'-01')->locale($locale)->isoFormat('MMMM YYYY'),
            default => \Carbon\Carbon::parse($key)->locale($locale)->isoFormat('ddd ll'),
        };
        $busiest = ['label' => __('messages.busiest_'.['daily' => 'day', 'weekly' => 'week', 'monthly' => 'month'][$period]), 'when' => $when, 'key' => $key, 'views' => (int) max($viewsChart['views'])];
    }

    $periodLink = fn (string $to) => route('analytics', array_filter([
        'role_id' => $u::encodeId($selectedRoleId),
        'event_id' => $u::encodeId($tabEventId),
        'range' => $range,
        'period' => $to !== $defaultPeriod ? $to : null,
    ]));

    // The chart's series, in the order of the legend. Colours are the chart's own and never the
    // text's: the legend and the tooltip write the names in ink beside a short line of colour.
    $series = [['id' => 'views', 'label' => __('messages.realtime_views'), 'values' => $viewsChart['views'], 'tone' => 'views']];
    if ($hasPrevious && array_sum($viewsChart['previous']) > 0) {
        $series[] = ['id' => 'previous', 'label' => __('messages.views_previous_period'), 'values' => $viewsChart['previous'], 'tone' => 'previous'];
    }
    // Views that came from a boost or a newsletter are counted for the schedule, never for one
    // event, so with an event selected they would stand above its own line.
    if (! $selectedEventId && array_sum($viewsChart['boost'] ?? []) > 0) {
        $series[] = ['id' => 'boost', 'label' => __('messages.boost').' '.__('messages.views'), 'values' => $viewsChart['boost'], 'tone' => 'boost'];
    }
    if (! $selectedEventId && array_sum($viewsChart['newsletter'] ?? []) > 0) {
        $series[] = ['id' => 'newsletter', 'label' => __('messages.newsletter_source').' '.__('messages.views'), 'values' => $viewsChart['newsletter'], 'tone' => 'newsletter'];
    }

    // Where visitors came from: by kind, by site, by campaign. One icon to a kind, so the kinds
    // read apart without a colour key.
    $sourceIcons = [
        'direct' => 'M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zM12 2.25V4.5m5.834.166l-1.591 1.591M20.25 10.5H18M7.757 14.743l-1.59 1.59M6 10.5H3.75m4.007-4.243l-1.59-1.59',
        'search' => 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z',
        'social' => 'M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z',
        'email' => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
        'newsletter' => 'M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h13.5M6 7.5h3v3H6v-3z',
        'boost' => 'M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 110-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 01-1.44-4.282m3.102.069a18.03 18.03 0 01-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 018.835 2.535M10.34 6.66a23.847 23.847 0 008.835-2.535m0 0A23.74 23.74 0 0018.795 3m.38 1.125a23.91 23.91 0 011.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 001.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 010 3.46',
        'promo' => 'M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z M6 6h.008v.008H6V6z',
        'other' => 'M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418',
    ];
    $sourceNames = ['direct' => 'direct', 'search' => 'search', 'social' => 'social', 'email' => 'email_source', 'newsletter' => 'newsletter_source', 'boost' => 'boost', 'promo' => 'promo_code', 'other' => 'other'];
    $sourceHelp = ['direct' => 'direct_description', 'search' => 'search_description', 'social' => 'social_description', 'email' => 'email_description', 'newsletter' => 'newsletter_source_description', 'boost' => 'boost_traffic_desc', 'promo' => 'promo_traffic_desc', 'other' => 'other_description'];
    $channelRows = $trafficSources->map(fn ($s) => [
        'label' => __('messages.'.($sourceNames[$s['source']] ?? 'other')),
        'value' => $s['view_count'],
        'icon' => $sourceIcons[$s['source']] ?? $sourceIcons['other'],
        'title' => isset($sourceHelp[$s['source']]) ? __('messages.'.$sourceHelp[$s['source']]) : null,
    ])->values()->all();
    // A site that is a social network wears its own mark.
    $siteBrand = function (string $domain) {
        $host = preg_replace('/^(www\.|m\.|l\.|lm\.)/', '', strtolower($domain));
        $platform = match (true) {
            str_contains($host, 'instagram') => 'instagram',
            str_contains($host, 'facebook') || $host === 'fb.me' => 'facebook',
            $host === 't.co' || $host === 'x.com' || str_contains($host, 'twitter') => 'twitter',
            str_contains($host, 'youtube') || $host === 'youtu.be' => 'youtube',
            str_contains($host, 'tiktok') => 'tiktok',
            str_contains($host, 'linkedin') || $host === 'lnkd.in' => 'linkedin',
            str_contains($host, 'threads') => 'threads',
            str_contains($host, 'whatsapp') => 'whatsapp',
            str_contains($host, 'telegram') || $host === 't.me' => 'telegram',
            default => null,
        };

        return $platform ? \App\Utils\UrlUtils::getSocialSvgPath($platform) : null;
    };
    $siteRows = $topReferrers->map(fn ($r) => array_filter([
        'label' => $r['domain'],
        'value' => $r['view_count'],
        'brand' => $siteBrand($r['domain']),
        'icon' => $sourceIcons['other'],
    ], fn ($v) => $v !== null))->values()->all();
    $utmRows = fn ($rows) => $rows->map(fn ($r) => ['label' => $r['param_value'], 'value' => $r['view_count'], 'mono' => true])->values()->all();
    $campaignPanels = array_filter([
        'campaign' => $utmRows($topUtmCampaigns),
        'source' => $utmRows($topUtmSources),
        'medium' => $utmRows($topUtmMediums),
    ]);
    $sourcePanels = array_filter([
        'channels' => $channelRows ? ['label' => __('messages.an_channels')] : null,
        'sites' => $siteRows ? ['label' => __('messages.an_sites')] : null,
        'campaigns' => $campaignPanels ? ['label' => __('messages.campaigns')] : null,
    ]);

    $deviceNames = ['mobile' => __('messages.mobile'), 'desktop' => __('messages.desktop'), 'tablet' => __('messages.tablet'), 'unknown' => __('messages.other')];
    $deviceIcons = [
        'mobile' => 'M10.5 1.5H8.25A2.25 2.25 0 006 3.75v16.5a2.25 2.25 0 002.25 2.25h7.5A2.25 2.25 0 0018 20.25V3.75a2.25 2.25 0 00-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3',
        'desktop' => 'M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25',
        'tablet' => 'M10.5 19.5h3m-6.75 2.25h10.5a2.25 2.25 0 002.25-2.25v-15a2.25 2.25 0 00-2.25-2.25H6.75A2.25 2.25 0 004.5 4.5v15a2.25 2.25 0 002.25 2.25z',
        'unknown' => 'M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z',
    ];
    // Largest first, but each device keeps its own colour whatever its place.
    $devices = collect($deviceBreakdown)->sortDesc();
    $deviceTotal = max(1, $devices->sum());

    $scheduleImage = fn ($role) => $role && $role->profile_image_url ? $role->getProfileImageUrl() : null;

    // The cards under the chart, in the order a person reads them. Each is there only when it
    // has something to say; the grid then fills every row (see .an-grid in _styles).
    $cards = array_values(array_filter([
        ($topEvents->isNotEmpty() && ! $selectedEventId) ? 'events' : null,
        $sourcePanels ? 'sources' : null,
        $locationBreakdown->isNotEmpty() ? 'countries' : null,
        $devices->isNotEmpty() ? 'devices' : null,
        ($viewsBySchedule->count() > 1 && ! $selectedEventId) ? 'schedules' : null,
        $socialClickStats->isNotEmpty() ? 'social' : null,
        $topSchedulesAppearedOn->isNotEmpty() ? 'appeared' : null,
        $topAppearances->isNotEmpty() ? 'appearances' : null,
        ($missingLinks ?? collect())->isNotEmpty() ? 'broken' : null,
    ]));
    // With an event selected, the cards that can only count the whole schedule carry the words
    // at their foot (and one line above the cards says what they mean), so their numbers are not
    // read as the event's.
    // (analytics/_scope)
@endphp

{{-- The figures and the chart are one card: the number, what it is against, and the shape of it. --}}
<section class="ap-card rounded-xl an-hero" aria-labelledby="an-views-title">
    <div class="an-hero-head">
        <div class="an-figs">
            <div class="an-fig is-lead">
                <p class="an-fig-label" id="an-views-title">
                    <span class="dashboard-icon p-1.5 rounded-lg bg-blue-50 dark:bg-blue-500/10" style="--icon-glow: rgba(59, 130, 246, 0.15)">
                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </span>
                    {{ __('messages.realtime_views') }}
                </p>
                <p class="an-fig-value">{{ number_format($viewsInRange) }}</p>
                @if ($periodComparison)
                <p class="an-fig-sub">
                    @if (! is_null($change))
                    <span class="an-delta {{ $change > 0 ? 'is-up' : ($change < 0 ? 'is-down' : 'is-flat') }}">
                        @if ($change != 0)<svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $change > 0 ? 'M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18' : 'M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3' }}" /></svg>@endif
                        <bdi dir="ltr">{{ $change > 0 ? '+' : '' }}{{ $change }}%</bdi>
                    </span>
                    @endif
                    @if ($previousViews > 0)
                    <span>{{ __('messages.'.$periodComparison['comparison_label']) }} <span class="an-num">({{ number_format($previousViews) }})</span></span>
                    @else
                    <span>{{ __('messages.an_no_earlier') }}</span>
                    @endif
                </p>
                @endif
            </div>
            @if ($range !== 'all_time')
            <div class="an-fig">
                <p class="an-fig-label">{{ __('messages.total_views') }}</p>
                <p class="an-fig-value">{{ number_format($totalViews) }}</p>
                <p class="an-fig-sub">{{ __('messages.all_time') }}</p>
            </div>
            @endif
            @if ($busiest)
            <div class="an-fig">
                <p class="an-fig-label">{{ $busiest['label'] }}</p>
                {{-- Written again by the page's script in the reader's own date order (Intl);
                     the server's is the fallback. --}}
                <p class="an-fig-value is-text"><span data-an-date="{{ $busiest['key'] }}" data-an-period="{{ $period }}">{{ $busiest['when'] }}</span></p>
                <p class="an-fig-sub"><span><span class="an-num">{{ number_format($busiest['views']) }}</span> {{ __('messages.page_views') }}</span></p>
            </div>
            @endif
            @if ($appearanceViews > 0 && ! $selectedEventId)
            <div class="an-fig">
                <p class="an-fig-label">{{ __('messages.appearance_views') }}</p>
                <p class="an-fig-value">{{ number_format($appearanceViews) }}</p>
                <p class="an-fig-sub">{{ __('messages.views_from_other_schedules') }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- The key on one side, how the chart groups on the other. --}}
    <div class="an-chart-bar">
        @if (count($series) > 1 || $viewsChart['events'])
        <ul class="an-legend" role="list">
            @foreach ($series as $s)
            <li><span class="an-key is-{{ $s['tone'] }}" aria-hidden="true"></span>{{ $s['label'] }}</li>
            @endforeach
            @if ($viewsChart['events'])
            <li><span class="an-key is-event" aria-hidden="true"></span>{{ __('messages.events') }}</li>
            @endif
        </ul>
        @endif
        <nav class="an-seg" aria-label="{{ __('messages.views_over_time') }}">
            @foreach (['daily', 'weekly', 'monthly'] as $p)
            <a href="{{ $periodLink($p) }}" @if ($period === $p) aria-current="page" @endif>{{ __('messages.'.$p) }}</a>
            @endforeach
        </nav>
    </div>

    <div class="an-chart">
        <canvas id="viewsChart" role="img" aria-label="{{ __('messages.views_over_time') }}"></canvas>
    </div>
    {{-- The chart as a table, for a screen reader and for anybody who wants the numbers. --}}
    <table class="sr-only">
        <caption>{{ __('messages.views_over_time') }}</caption>
        <thead><tr><th scope="col">{{ __('messages.date') }}</th>@foreach ($series as $s)<th scope="col">{{ $s['label'] }}</th>@endforeach</tr></thead>
        <tbody>
            @foreach ($viewsChart['keys'] as $i => $key)
            <tr><th scope="row">{{ $key }}</th>@foreach ($series as $s)<td>{{ $s['values'][$i] ?? 0 }}</td>@endforeach</tr>
            @endforeach
        </tbody>
    </table>
</section>

@php
    // Every row of the grid full, whatever cards there are (App\Utils\AnalyticsGrid).
    $spans = \App\Utils\AnalyticsGrid::spans(count($cards));
    $spanOf = fn (int $i) => $spans[$i] ?? '';
@endphp
@if ($cards && $selectedEventId)
<p class="an-note">
    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" /></svg>
    <span>{{ __('messages.an_whole_schedule_cards') }}</span>
</p>
@endif
@if ($cards)
<div class="an-grid">
    @foreach ($cards as $i => $card)
        @if ($card === 'events')
        <x-page-card :title="__('messages.top_events')" class="an-card {{ $spanOf($i) }}">
            <x-slot:aside><span class="an-card-unit">{{ __('messages.realtime_views') }}</span></x-slot:aside>
            @include('analytics._list', [
                'listRows' => $topEvents->map(fn ($row) => [
                    'label' => $row['event']->translatedName(),
                    'value' => $row['view_count'],
                    'thumb' => $row['event']->getImageUrl() ?: null,
                    'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
                    'href' => $eventLink($row['event']),
                    'title' => __('messages.an_show_event'),
                ])->values()->all(),
                'listShare' => false,
                'listBars' => true,
            ])
        </x-page-card>
        @elseif ($card === 'sources')
        <x-page-card :title="__('messages.realtime_sources')" class="an-card {{ $spanOf($i) }}">
            @if (count($sourcePanels) > 1)
            <x-slot:aside>
                @if (count($sourcePanels) > 1)
                <div class="an-seg is-sm" role="tablist" aria-label="{{ __('messages.realtime_sources') }}">
                    @foreach ($sourcePanels as $key => $panel)
                    <button type="button" role="tab" id="an-src-tab-{{ $key }}" aria-controls="an-src-{{ $key }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-an-tab>{{ $panel['label'] }}</button>
                    @endforeach
                </div>
                @endif
            </x-slot:aside>
            @endif
            @foreach ($sourcePanels as $key => $panel)
            <div id="an-src-{{ $key }}" @if (count($sourcePanels) > 1) role="tabpanel" aria-labelledby="an-src-tab-{{ $key }}" @endif @unless ($loop->first) hidden @endunless>
                @if ($key === 'channels')
                    @include('analytics._list', ['listRows' => $channelRows, 'listShare' => true, 'listBars' => true])
                    <details class="an-help an-foot">
                        <summary>{{ __('messages.an_about_sources') }}</summary>
                        <dl>
                            @foreach ($sourceNames as $src => $name)
                            <div><dt>{{ __('messages.'.$name) }}</dt><dd>{{ __('messages.'.$sourceHelp[$src]) }}</dd></div>
                            @endforeach
                        </dl>
                    </details>
                @elseif ($key === 'sites')
                    @include('analytics._list', ['listRows' => $siteRows, 'listShare' => false, 'listBars' => true])
                @else
                    @if (count($campaignPanels) > 1)
                    <div class="an-seg is-xs" role="tablist" aria-label="{{ __('messages.campaigns') }}">
                        @foreach ($campaignPanels as $param => $rows)
                        <button type="button" role="tab" id="an-utm-tab-{{ $param }}" aria-controls="an-utm-{{ $param }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-an-tab>utm_{{ $param }}</button>
                        @endforeach
                    </div>
                    @endif
                    @foreach ($campaignPanels as $param => $rows)
                    <div id="an-utm-{{ $param }}" @if (count($campaignPanels) > 1) role="tabpanel" aria-labelledby="an-utm-tab-{{ $param }}" @endif @unless ($loop->first) hidden @endunless>
                        @include('analytics._list', ['listRows' => $rows, 'listShare' => false, 'listBars' => true])
                    </div>
                    @endforeach
                @endif
            </div>
            @endforeach
            @if ($selectedEventId) @include('analytics._scope') @endif
        </x-page-card>
        @elseif ($card === 'countries')
        <x-page-card :title="__('messages.realtime_countries')" class="an-card {{ $spanOf($i) }}">
            <x-slot:aside><span class="an-card-unit">{{ __('messages.realtime_views') }}</span></x-slot:aside>
            @include('analytics._list', [
                'listRows' => $locationBreakdown->map(fn ($l) => ['label' => $l['country_name'] ?: $l['country_code'], 'value' => $l['view_count'], 'flag' => $l['country_code']])->values()->all(),
                'listShare' => true,
                'listBars' => true,
                // Ten countries are listed; a share is of every country.
                'listTotal' => $locationTotal,
            ])
            @if ($selectedEventId) @include('analytics._scope') @endif
        </x-page-card>
        @elseif ($card === 'devices')
        <x-page-card :title="__('messages.realtime_owner_devices')" class="an-card {{ $spanOf($i) }}">
            <div class="an-split" role="img" aria-label="{{ $devices->map(fn ($n, $d) => ($deviceNames[$d] ?? $d).' '.round($n / $deviceTotal * 100).'%')->implode(', ') }}">
                @foreach ($devices as $device => $n)
                <span class="is-{{ $device }}" style="flex-grow: {{ $n }}"></span>
                @endforeach
            </div>
            <ul class="an-device-list" role="list">
                @foreach ($devices as $device => $n)
                <li>
                    <span class="an-swatch is-{{ $device }}" aria-hidden="true"></span>
                    <span class="an-device-name">{{ $deviceNames[$device] ?? $device }}</span>
                    <span class="an-device-share">{{ round($n / $deviceTotal * 100) }}%</span>
                    <span class="an-device-count">{{ number_format($n) }}</span>
                </li>
                @endforeach
            </ul>
        </x-page-card>
        @elseif ($card === 'schedules')
        <x-page-card :title="__('messages.schedule_views')" class="an-card {{ $spanOf($i) }}">
            <x-slot:aside><span class="an-card-unit">{{ __('messages.realtime_views') }}</span></x-slot:aside>
            @include('analytics._list', [
                'listRows' => $viewsBySchedule->filter(fn ($r) => $r['role'])->sortByDesc('view_count')->map(fn ($r) => [
                    'label' => $r['role']->name,
                    'value' => $r['view_count'],
                    'thumb' => $scheduleImage($r['role']),
                    'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5',
                    'href' => route('analytics', array_filter(['role_id' => $u::encodeId($r['role']->id), 'range' => $range, 'period' => $period !== $defaultPeriod ? $period : null])),
                ])->values()->all(),
                'listShare' => true,
                'listBars' => true,
            ])
        </x-page-card>
        @elseif ($card === 'social')
        <x-page-card :title="__('messages.social_link_clicks')" class="an-card {{ $spanOf($i) }}">
            <x-slot:aside><span class="an-card-unit">{{ __('messages.clicks') }}</span></x-slot:aside>
            @include('analytics._list', [
                'listRows' => $socialClickStats->map(fn ($s) => ['label' => $u::getPlatformBrandName($s['platform']), 'value' => $s['click_count'], 'brand' => $u::getSocialSvgPath($s['platform'])])->values()->all(),
                'listShare' => false,
                'listBars' => true,
            ])
            @if ($selectedEventId) @include('analytics._scope') @endif
        </x-page-card>
        @elseif ($card === 'appeared')
        <x-page-card :title="__('messages.top_schedules_appeared_on')" class="an-card {{ $spanOf($i) }}">
            <x-slot:aside><span class="an-card-unit">{{ __('messages.realtime_views') }}</span></x-slot:aside>
            @include('analytics._list', [
                'listRows' => $topSchedulesAppearedOn->map(fn ($r) => ['label' => $r['role']->name, 'value' => $r['view_count'], 'thumb' => $scheduleImage($r['role']), 'icon' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z'])->values()->all(),
                'listShare' => false,
                'listBars' => true,
            ])
            @if ($selectedEventId) @include('analytics._scope') @endif
        </x-page-card>
        @elseif ($card === 'appearances')
        <x-page-card :title="__('messages.top_associated_roles')" class="an-card {{ $spanOf($i) }}">
            <x-slot:aside><span class="an-card-unit">{{ __('messages.realtime_views') }}</span></x-slot:aside>
            @include('analytics._list', [
                'listRows' => $topAppearances->map(fn ($r) => ['label' => $r['role']->translatedName(), 'value' => $r['view_count'], 'thumb' => $scheduleImage($r['role']), 'icon' => 'M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z'])->values()->all(),
                'listShare' => false,
                'listBars' => true,
            ])
            @if ($selectedEventId) @include('analytics._scope') @endif
        </x-page-card>
        @elseif ($card === 'broken')
        {{-- Addresses on this schedule that visitors reached and that matched nothing. Before this
             existed, a dead event link 302'd to the schedule home: the visit counted for the
             schedule, counted for no event, and looked exactly like an event whose statistics had
             stopped working. --}}
        <x-page-card :title="__('messages.broken_links')" :lead="__('messages.broken_links_description')" class="an-card {{ $spanOf($i) }}">
            @include('analytics._list', [
                'listRows' => $missingLinks->map(fn ($l) => ['label' => '/'.$l['slug'], 'value' => $l['views'], 'mono' => true, 'icon' => 'M13.181 8.68a4.503 4.503 0 011.903 6.405m-9.768-2.782L3.56 14.06a4.5 4.5 0 006.364 6.365l3.129-3.129m5.614-5.615l1.757-1.757a4.5 4.5 0 00-6.364-6.365l-4.5 4.5c-.258.26-.479.541-.661.84m1.903 6.405a4.495 4.495 0 01-1.242-.88 4.483 4.483 0 01-1.062-1.683m6.587 2.345l5.907 5.907m-5.907-5.907L8.898 8.898M2.991 2.99L8.898 8.9'])->values()->all(),
                'listShare' => false,
                'listBars' => false,
            ])
        </x-page-card>
        @endif
    @endforeach
</div>
@endif

@php
    $webChart = [
        'period' => $period,
        'locale' => $locale,
        'keys' => $viewsChart['keys'],
        'previousKeys' => $viewsChart['previousKeys'],
        'series' => $series,
        'events' => $viewsChart['events'],
        // Every range but Last Month ends today, whose day (week, month) is not over: its last
        // stretch of line is drawn dashed and its tooltip says "so far".
        'partial' => $range !== 'last_month',
        'soFar' => __('messages.an_so_far'),
    ];
@endphp
<script {!! nonce_attr() !!}>
    window.anCharts = window.anCharts || [];
    window.anCharts.push({ kind: 'line', canvas: 'viewsChart', data: @json($webChart) });
</script>
@else
@php
    $emptyRole = $selectedRoleId ? $roles->firstWhere('id', $selectedRoleId) : ($roles->count() === 1 ? $roles->first() : null);
@endphp
@include('analytics._empty', [
    'emptyTitle' => __('messages.no_analytics_data'),
    'emptyText' => __('messages.analytics_data_will_appear'),
    'emptyFigures' => [__('messages.realtime_views'), __('messages.total_views'), __('messages.busiest_day')],
    'emptyHref' => $emptyRole?->getGuestUrl(),
    'emptyLabel' => __('messages.view_schedule'),
])
@endif
