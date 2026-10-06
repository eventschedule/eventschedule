{{-- One row to a schedule, directly under the tiles: the same measures, split. A row opens its
     schedule. The name takes two shares of the width and each number one, so the numbers start
     under the middle of the card at any width instead of drifting to its far edge.

     $viewer: someone who was only given a look at these schedules. Then the rows carry no numbers
     (they were not given any).

     A schedule whose plan no longer includes a team is named with the reason and has no link, for
     a viewer and for an admin alike: its own pages would send either of them back here.

     "Visitors now" is the Realtime count (people who accepted cookies and are on a page of that
     schedule), shown only where this person has a live view. home/_live-script keeps it current. --}}
@php
    $hasLive = ! $viewer && collect($rows)->contains(fn ($row) => ($row['now'] ?? null) !== null);
    // Literal class strings, so Tailwind's scanner sees each. Four number columns with a live
    // view, three without: an empty column would only push the numbers apart.
    $cols = 'grid grid-cols-[minmax(0,1fr)_auto] gap-x-4 items-center px-4 sm:px-5';
    if (! $viewer) {
        $cols .= $hasLive
            ? ' md:grid-cols-[minmax(0,2fr)_repeat(4,minmax(0,1fr))_1.25rem]'
            : ' md:grid-cols-[minmax(0,2fr)_repeat(3,minmax(0,1fr))_1.25rem]';
    }
    // Past this many the rest fold under one line: fifteen schedules are a page of their own.
    $shownRows = 8;
    $num = 'hidden md:block text-end text-sm tabular-nums text-gray-700 dark:text-gray-300';
    $chevron = '<svg class="w-4 h-4 text-gray-400 '.(is_rtl() ? 'rotate-180' : '').'" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>';
@endphp

<section id="dashboard-schedules" class="ap-card rounded-xl flex flex-col overflow-hidden scroll-mt-6" aria-labelledby="dashboard-schedules-title">
    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
        <h2 id="dashboard-schedules-title" class="text-base font-semibold text-gray-900 dark:text-white">{{ __('messages.your_schedules') }}</h2>
        @unless ($viewer)
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.views') }}: {{ __('messages.last_'.$period.'_days') }}</span>
        @endunless
    </div>

    @unless ($viewer)
    <div class="{{ $cols }} py-2 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400 !hidden md:!grid" style="background: var(--ap-tint-sunken)" aria-hidden="true">
        <span>{{ __('messages.schedule') }}</span>
        <span class="text-end">{{ __('messages.panel_upcoming_count') }}</span>
        <span class="text-end">{{ __('messages.views') }}</span>
        <span class="text-end">{{ __('messages.followers') }}</span>
        @if ($hasLive)<span class="text-end">{{ __('messages.dash_visitors_now') }}</span>@endif
        <span></span>
    </div>
    @endunless

    @php $rowSets = [array_slice($rows, 0, $shownRows), array_slice($rows, $shownRows)]; @endphp
    @foreach ($rowSets as $setIndex => $rowSet)
    @continue(! $rowSet)
    @if ($setIndex === 1)
    <details class="group" style="border-top: 1px solid var(--ap-hairline)">
        <summary class="flex items-center justify-center gap-1.5 px-5 py-3 cursor-pointer select-none list-none text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-black/10 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--brand-blue)] [&::-webkit-details-marker]:hidden">
            <span>{{ __('messages.pending_action_show_more', ['count' => count($rowSet)]) }}</span>
            <svg class="w-4 h-4 transition-transform duration-200 group-open:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
        </summary>
    @endif
    <ul class="divide-y divide-gray-100 dark:divide-white/[0.06]" @if ($viewer || $setIndex === 1) style="border-top: 1px solid var(--ap-hairline)" @endif>
        @foreach ($rowSet as $row)
            @php
                $closed = ! empty($row['blocked']);
                $tag = $closed ? 'div' : 'a';
            @endphp
            <li>
                <{{ $tag }} @unless ($closed) href="{{ $row['url'] }}" @endunless class="{{ $cols }} py-2.5 {{ $closed ? '' : 'hover:bg-gray-50 dark:hover:bg-black/10 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--brand-blue)]' }}">
                    <span class="flex items-center gap-3 min-w-0">
                        @include('home._thumb', ['image' => $row['image'], 'name' => $row['name'], 'thumbSmall' => true])
                        <span class="min-w-0">
                            <span class="block truncate text-sm font-medium text-gray-900 dark:text-white">{{ $row['name'] }}</span>
                            {{-- From a tablet up the numbers have columns and this line says what
                                 kind of schedule it is. A phone has no columns, so this line carries
                                 the two numbers INSTEAD of the kind: with both, a longer language
                                 ("Место проведения") filled the line and the numbers were cut off. --}}
                            <span class="block truncate text-xs text-gray-500 dark:text-gray-400">
                                @if ($closed)
                                    {{ __('messages.'.$row['type']) }} &middot; {{ __('messages.dash_plan_closed') }}
                                @elseif ($viewer)
                                    {{ __('messages.'.$row['type']) }}
                                @else
                                    <span class="hidden md:inline">{{ __('messages.'.$row['type']) }}</span>
                                    <span class="md:hidden">{{ __('messages.panel_upcoming_count') }} {{ number_format($row['upcoming']) }} &middot; {{ __('messages.views') }} {{ number_format($row['views']) }}</span>
                                @endif
                            </span>
                        </span>
                    </span>

                    @unless ($viewer)
                        <span class="{{ $num }}">{{ number_format($row['upcoming']) }}</span>
                        <span class="{{ $num }}">{{ number_format($row['views']) }}</span>
                        <span class="{{ $num }}">{{ number_format($row['followers']) }}</span>
                        @if ($hasLive)
                        <span class="hidden md:flex justify-end items-center gap-1.5 text-sm tabular-nums" data-live-schedule="{{ $row['id'] }}">
                            <span data-live-schedule-dot class="w-1.5 h-1.5 rounded-full bg-green-500 {{ $row['now'] ? '' : 'hidden' }}" aria-hidden="true"></span>
                            <span data-live-schedule-count class="{{ $row['now'] ? 'font-medium text-green-700 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">{{ number_format($row['now']) }}</span>
                        </span>
                        @endif
                    @endunless

                    <span class="flex items-center gap-2 justify-self-end">
                        @if ($hasLive)
                            {{-- A phone has no room for the columns; it keeps the one figure that
                                 changes while you look. --}}
                            <span class="md:hidden items-center gap-1.5 text-sm font-medium tabular-nums text-green-700 dark:text-green-400 {{ $row['now'] ? 'inline-flex' : 'hidden' }}" data-live-schedule-phone="{{ $row['id'] }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500" aria-hidden="true"></span><span data-live-schedule-count>{{ number_format($row['now']) }}</span>
                            </span>
                        @endif
                        @unless ($closed) {!! $chevron !!} @endunless
                    </span>
                </{{ $tag }}>
            </li>
        @endforeach
    </ul>
    @if ($setIndex === 1)
    </details>
    @endif
    @endforeach
</section>
