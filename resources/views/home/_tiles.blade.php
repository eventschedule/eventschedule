{{-- The four numbers. What you have first, what is happening now last: for most schedules "now"
     is quiet most of the day, and the place the eye lands first should not be the tile that
     usually says so.

     Each tile is a link to the page that explains its number, and each has the same anatomy: a
     figure with its caption, a strip of bars, and one label with its value under a hairline.
     Counts sit beside their labels, never inside a sentence, so no tile needs a plural form.

     Realtime shows two figures because they answer two questions. Page views count everybody; a
     visitor can only be told from another once they have accepted cookies, so "visitors now" is
     those people only (App\Services\ScheduleRealtime). With nobody about it says so in words.
     Where the install has no live view, Upcoming events takes the fourth place. --}}
@php
    $views = $dashboard['views'];
    $followers = $dashboard['followers'];
    $revenue = $dashboard['revenue'];
    $live = $dashboard['live'];
    $periodLabel = __('messages.last_'.$period.'_days');
    $figure = 'dashboard-stat-value text-3xl font-bold tabular-nums';
    $caption = 'mt-0.5 px-2 text-[11px] sm:text-xs leading-tight text-gray-500 dark:text-gray-400 text-center';
    // The Realtime tile's two figures: a row each on a phone, a column each from a tablet up.
    $liveFigure = 'dashboard-stat-value shrink-0 min-w-[2.25rem] text-end sm:min-w-0 sm:text-center text-2xl sm:text-3xl font-bold tabular-nums';
    $liveCaption = 'min-w-0 text-[11px] sm:text-xs leading-tight text-gray-500 dark:text-gray-400 text-start sm:text-center sm:mt-0.5 sm:px-2';
    $chevronIcon = 'M8.25 4.5l7.5 7.5-7.5 7.5';
@endphp

<div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
    @if ($views !== null)
    <x-dashboard-tile :href="route('analytics')" :label="__('messages.views')"
        icon="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z M15 12a3 3 0 11-6 0 3 3 0 016 0z"
        tint="bg-sky-50 dark:bg-sky-500/10" ink="text-sky-500" glow="rgba(14, 165, 233, 0.15)"
        :foot-label="__('messages.vs_previous_'.$period.'_days')">
        <div class="flex flex-col items-center min-w-0">
            <span class="{{ $figure }} text-gray-900 dark:text-white">{{ number_format($views['total']) }}</span>
            <span class="{{ $caption }}">{{ $periodLabel }}</span>
        </div>
        <x-slot name="bars">@include('home._bars', ['values' => $views['days'], 'tone' => 'bg-sky-500'])</x-slot>
        <x-slot name="footValue">
            {{-- A signed figure flips to "12+" in Hebrew and Arabic unless it is held LTR. No
                 earlier views means no percentage: "+100%" of nothing is not a trend. --}}
            @if ($views['change'] === null)
                <span class="text-gray-400" aria-hidden="true">&ndash;</span>
            @else
                <span dir="ltr" class="{{ $views['change'] >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">{{ ($views['change'] >= 0 ? '+' : '').rtrim(rtrim(number_format($views['change'], 1), '0'), '.') }}%</span>
            @endif
        </x-slot>
    </x-dashboard-tile>
    @endif

    @if ($followers !== null)
    <x-dashboard-tile :href="$dashboard['followersUrl'] ?? '#dashboard-schedules'" :label="__('messages.followers')"
        icon="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"
        tint="bg-violet-50 dark:bg-violet-500/10" ink="text-violet-500" glow="rgba(139, 92, 246, 0.15)"
        :foot-label="$periodLabel">
        <div class="flex flex-col items-center min-w-0">
            <span class="{{ $figure }} text-gray-900 dark:text-white">{{ number_format($followers['total']) }}</span>
            <span class="{{ $caption }}">{{ __('messages.dash_in_total') }}</span>
        </div>
        <x-slot name="bars">@include('home._bars', ['values' => $followers['days'], 'tone' => 'bg-violet-500'])</x-slot>
        <x-slot name="footValue"><span dir="ltr">{{ $followers['new'] > 0 ? '+' : '' }}{{ number_format($followers['new']) }}</span></x-slot>
    </x-dashboard-tile>
    @endif

    @if ($revenue !== null)
    @php
        // Money that belongs to rows is shown in the currency it was taken in. With several, the
        // largest is the figure and the others follow on one line under it.
        $main = $revenue['currencies'][0] ?? null;
        $others = array_slice($revenue['currencies'], 1);
        $revenueCaption = $others
            ? collect($others)->map(fn ($row) => \App\Utils\MoneyUtils::format($row['amount'], $row['currency_code']))->implode(' · ')
            : $periodLabel;
    @endphp
    <x-dashboard-tile :href="route('sales')" :label="__('messages.revenue')"
        icon="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"
        tint="bg-emerald-50 dark:bg-emerald-500/10" ink="text-emerald-500" glow="rgba(16, 185, 129, 0.15)"
        :foot-label="__('messages.sales')" :foot-value="number_format($revenue['sales'])">
        <div class="flex flex-col items-center min-w-0">
            <span class="{{ $figure }} {{ $main ? 'text-gray-900 dark:text-white' : 'text-gray-500 dark:text-gray-400' }}" dir="ltr">{{ $main ? \App\Utils\MoneyUtils::format($main['amount'], $main['currency_code']) : \App\Utils\MoneyUtils::format(0, $defaultCurrency) }}</span>
            <span class="{{ $caption }}" @if ($others) dir="ltr" title="{{ $periodLabel }}" @endif>{{ $revenueCaption }}</span>
        </div>
        <x-slot name="bars">@include('home._bars', ['values' => $revenue['days'], 'tone' => 'bg-emerald-500'])</x-slot>
    </x-dashboard-tile>
    @endif

    @if ($live !== null)
        @php $quiet = ! $live['views_5m'] && ! $live['visitors_now']; @endphp
        <x-dashboard-tile :href="route('analytics', ['tab' => 'realtime'])" :label="__('messages.realtime')" :icon="\App\Utils\RealtimeIcons::PATHS['signal']"
            tint="bg-green-50 dark:bg-green-500/10" ink="text-green-500" glow="rgba(34, 197, 94, 0.15)"
            :dot="$quiet ? 'bg-gray-400' : 'bg-green-500'" :foot-label="__('messages.dash_page_views_30m')" data-live-tile>
            <div data-live-quiet class="{{ $quiet ? 'flex' : 'hidden' }} items-center justify-center min-h-[3.4rem]">
                <span class="text-base font-medium text-gray-500 dark:text-gray-400 text-center">{{ __('messages.dash_quiet_now') }}</span>
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
            <x-slot name="footValue"><span data-live-total>{{ number_format($live['views_30m']) }}</span></x-slot>
        </x-dashboard-tile>
    @elseif ($dashboard['upcoming'] !== null)
        @php $next = $dashboard['coming']['rows'][0] ?? null; @endphp
        {{-- Opens the calendar below, where there is one: Customize can switch it off. --}}
        <x-dashboard-tile :href="$showCalendar ? '#dashboard-calendar' : null" :label="__('messages.upcoming_events')" :icon="\App\Utils\RealtimeIcons::PATHS['schedule']"
            tint="bg-blue-50 dark:bg-blue-500/10" ink="text-blue-500" glow="rgba(59, 130, 246, 0.15)"
            :foot-label="__('messages.dash_next_event')"
            :foot-value="$next ? ($next['when'] === 'today' ? __('messages.today') : ($next['when'] === 'tomorrow' ? __('messages.dash_tomorrow') : $next['date'])) : ''">
            <div class="flex flex-col items-center min-w-0">
                <span class="{{ $figure }} text-gray-900 dark:text-white">{{ number_format($dashboard['upcoming']['count']) }}</span>
                <span class="{{ $caption }}">{{ __('messages.dash_from_today') }}</span>
            </div>
            <x-slot name="bars"><span class="block h-7" aria-hidden="true"></span></x-slot>
        </x-dashboard-tile>
    @endif
</div>
