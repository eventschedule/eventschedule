{{-- The four numbers, in the 2026-11 design's order and short form: Views, Revenue, Upcoming
     events, Followers. Where the owner has a live view, Realtime takes the last place and Upcoming
     gives up its own: what is happening now last, because for most schedules "now" is quiet most
     of the day, and the place the eye lands first should not be the tile that usually says so.

     Each tile is a link to the page that explains its number (components/dashboard-tile): a label
     that names the number and its span ("Views · 30 days"), the figure, and one line. Views keeps
     its strip of bars and its change, inline beside the figure, with the line saying what the
     change is measured against ("vs previous 30 days").

     Realtime shows two figures because they answer two questions. Page views count everybody; a
     visitor can only be told from another once they have accepted cookies, so "visitors now" is
     those people only (App\Services\ScheduleRealtime). With nobody about it says so in words.
     Where the install has no live view, Upcoming events takes the fourth place. --}}
@php
    $views = $dashboard['views'];
    $followers = $dashboard['followers'];
    $revenue = $dashboard['revenue'];
    $live = $dashboard['live'];
    $span = __('messages.dash_days_'.$period);
    $figure = 'dashboard-stat-value text-[1.875rem] leading-tight font-bold tracking-[-0.025em] tabular-nums';
    // The Realtime tile's two figures: a row each on a phone, a column each from a tablet up.
    $liveFigure = 'dashboard-stat-value shrink-0 min-w-[2.25rem] text-end sm:min-w-0 sm:text-center text-2xl sm:text-3xl font-bold tabular-nums';
    $liveCaption = 'min-w-0 text-[11px] sm:text-xs leading-tight text-gray-500 dark:text-gray-400 text-start sm:text-center sm:mt-0.5 sm:px-2';
    // The design's pale strip: the brand blue at under half strength, so it follows dark mode.
    $paleBars = 'background-color: color-mix(in srgb, var(--brand-blue) 45%, transparent);';
@endphp

<div class="grid grid-cols-2 xl:grid-cols-4 gap-3 sm:gap-4">
    @if ($views !== null)
    <x-dashboard-tile :href="route('analytics')" :label="__('messages.views').' · '.$span">
        <div class="flex items-baseline flex-wrap gap-x-2 min-w-0">
            <span class="{{ $figure }} text-gray-900 dark:text-white">{{ number_format($views['total']) }}</span>
            {{-- A signed figure flips to "12+" in Hebrew and Arabic unless it is held LTR. No
                 earlier views means no percentage: "+100%" of nothing is not a trend. --}}
            @if ($views['change'] !== null)
                <span dir="ltr" class="text-[13px] font-semibold tabular-nums {{ $views['change'] >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}" title="{{ __('messages.vs_previous_'.$period.'_days') }}">{{ ($views['change'] >= 0 ? '+' : '').rtrim(rtrim(number_format($views['change'], 1), '0'), '.') }}%</span>
            @endif
        </div>
        <x-slot name="bars">@include('home._bars', ['values' => $views['days'], 'tone' => 'bg-[var(--brand-blue)]', 'barStyle' => $paleBars])</x-slot>
        <x-slot name="line">{{ __('messages.vs_previous_'.$period.'_days') }}</x-slot>
    </x-dashboard-tile>
    @endif

    @if ($revenue !== null)
    @php
        // Money that belongs to rows is shown in the currency it was taken in. With several, the
        // largest is the figure and the others follow the count of sales on its line.
        $main = $revenue['currencies'][0] ?? null;
        $others = array_slice($revenue['currencies'], 1);
        $salesLine = trans_choice('messages.dash_sales_count', $revenue['sales'], ['count' => number_format($revenue['sales'])]);
    @endphp
    <x-dashboard-tile :href="route('sales')" :label="__('messages.revenue').' · '.$span">
        <span class="block {{ $figure }} {{ $main ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}" dir="ltr">{{ $main ? \App\Utils\MoneyUtils::format($main['amount'], $main['currency_code']) : \App\Utils\MoneyUtils::format(0, $defaultCurrency) }}</span>
        <x-slot name="line">{{ $salesLine }}@foreach ($others as $other) &middot; <span dir="ltr">{{ \App\Utils\MoneyUtils::format($other['amount'], $other['currency_code']) }}</span>@endforeach</x-slot>
    </x-dashboard-tile>
    @endif

    @if ($live === null && $dashboard['upcoming'] !== null)
        @php
            $next = $dashboard['coming']['rows'][0] ?? null;
            $nextLine = ! $next ? null : ($next['when'] === 'today'
                ? __('messages.dash_next_today')
                : ($next['when'] === 'tomorrow' ? __('messages.dash_next_tomorrow') : __('messages.dash_next_on', ['date' => $next['date']])));
        @endphp
        {{-- Opens the calendar below, where there is one: Customize can switch it off. --}}
        <x-dashboard-tile :href="$showCalendar ? '#dashboard-calendar' : null" :label="__('messages.upcoming_events')">
            <span class="block {{ $figure }} text-gray-900 dark:text-white">{{ number_format($dashboard['upcoming']['count']) }}</span>
            @if ($nextLine)
                <x-slot name="line">{{ $nextLine }}</x-slot>
            @endif
        </x-dashboard-tile>
    @endif

    @if ($followers !== null)
    <x-dashboard-tile :href="$dashboard['followersUrl'] ?? '#dashboard-schedules'" :label="__('messages.followers')">
        <span class="block {{ $figure }} text-gray-900 dark:text-white">{{ number_format($followers['total']) }}</span>
        <x-slot name="line">{{ __('messages.dash_new_last_'.$period.'_days', ['count' => number_format($followers['new'])]) }}</x-slot>
    </x-dashboard-tile>
    @endif

    @if ($live !== null)
        @php $quiet = ! $live['views_5m'] && ! $live['visitors_now']; @endphp
        <x-dashboard-tile :href="route('analytics', ['tab' => 'realtime'])" :label="__('messages.realtime')"
            :dot="$quiet ? 'bg-gray-400' : 'bg-green-500'" data-live-tile>
            <div data-live-quiet class="{{ $quiet ? 'flex' : 'hidden' }} items-center min-h-[2.35rem]">
                <span class="text-base font-medium text-gray-500 dark:text-gray-400">{{ __('messages.dash_quiet_now') }}</span>
            </div>
            {{-- Two figures side by side from a tablet up. On a phone each half is about 68px
                 wide, and "visualizzazioni," or "посетители" is wider than that: the caption
                 ran into the line between them. So a phone stacks them, each figure with its
                 caption beside it, where the caption has the width of the tile to wrap in. --}}
            <div data-live-figures class="{{ $quiet ? 'hidden' : 'grid' }} grid-cols-1 gap-y-1.5 sm:grid-cols-2 sm:gap-y-0">
                <div class="flex items-baseline gap-2 min-w-0 sm:flex-col sm:items-center sm:gap-0">
                    <span data-live-views class="{{ $liveFigure }} text-gray-900 dark:text-white">{{ number_format($live['views_5m']) }}</span>
                    <span class="{{ $liveCaption }}">{{ __('messages.dash_views_5m') }}</span>
                </div>
                <div class="flex items-baseline gap-2 min-w-0 sm:flex-col sm:items-center sm:gap-0 sm:border-s sm:[border-inline-start-color:var(--ap-hairline)]">
                    <span data-live-visitors class="{{ $liveFigure }} text-gray-900 dark:text-white">{{ number_format($live['visitors_now']) }}</span>
                    <span class="{{ $liveCaption }}">{{ __('messages.dash_visitors_now') }}</span>
                </div>
            </div>
            {{-- The live series is one colour on both pages; green is kept for "live" itself. --}}
            <x-slot name="bars">@include('home._bars', ['values' => $live['minutes'], 'tone' => 'bg-[var(--brand-blue)]', 'liveStrip' => true])</x-slot>
            <x-slot name="line">{{ __('messages.dash_page_views_30m') }} &middot; <span data-live-total class="tabular-nums">{{ number_format($live['views_30m']) }}</span></x-slot>
        </x-dashboard-tile>
    @endif
</div>
