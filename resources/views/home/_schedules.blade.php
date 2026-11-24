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
    // 2026-11 design: the name takes what is left and each number a narrow column of its own
    // (72px), with the column heads on the title's line. No chevron: the row is the link.
    $cols = 'grid grid-cols-[minmax(0,1fr)_auto] gap-x-3 items-center px-4 sm:px-5';
    if (! $viewer) {
        $cols .= $hasLive
            ? ' md:grid-cols-[minmax(0,1fr)_repeat(4,4.5rem)]'
            : ' md:grid-cols-[minmax(0,1fr)_repeat(3,4.5rem)]';
    }
    // Past this many the rest fold under one line: fifteen schedules are a page of their own.
    $shownRows = 8;
    $num = 'hidden md:block text-end text-[15px] tabular-nums text-gray-900 dark:text-gray-100';
    $head = 'text-[11px] font-semibold uppercase tracking-[0.06em] text-gray-500 dark:text-gray-400';
@endphp

<section id="dashboard-schedules" class="ap-card rounded-xl flex flex-col overflow-hidden scroll-mt-6" aria-labelledby="dashboard-schedules-title">
    {{-- The title and the column heads share one line (the heads only from a tablet up, where the
         numbers have columns under them). --}}
    <div class="{{ $cols }} pt-4 sm:pt-5 pb-3 !items-baseline">
        <h2 id="dashboard-schedules-title" class="text-xs font-semibold uppercase tracking-[0.06em] text-gray-500 dark:text-gray-400">{{ __('messages.your_schedules') }} <span class="tabular-nums">&middot; {{ count($rows) }}</span></h2>
        @unless ($viewer)
            <span class="{{ $head }} text-end hidden md:block" aria-hidden="true">{{ __('messages.panel_upcoming_count') }}</span>
            <span class="{{ $head }} text-end hidden md:block" aria-hidden="true">{{ __('messages.views') }}</span>
            <span class="{{ $head }} text-end hidden md:block" aria-hidden="true">{{ __('messages.followers') }}</span>
            @if ($hasLive)<span class="{{ $head }} text-end hidden md:block" aria-hidden="true">{{ __('messages.dash_visitors_now') }}</span>@endif
        @endunless
    </div>

    @php $rowSets = [array_slice($rows, 0, $shownRows), array_slice($rows, $shownRows)]; @endphp
    @foreach ($rowSets as $setIndex => $rowSet)
    @continue(! $rowSet)
    @if ($setIndex === 1)
    <details class="group" style="border-top: 1px solid rgb(var(--ap-border))">
        <summary class="flex items-center justify-center min-h-[2.75rem] px-5 cursor-pointer select-none list-none text-[13px] font-semibold text-[var(--brand-blue)] hover:underline transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--brand-blue)] [&::-webkit-details-marker]:hidden group-open:hidden">
            <span>{{ __('messages.pending_action_show_more', ['count' => count($rowSet)]) }}</span>
        </summary>
    @endif
    <ul class="divide-y divide-[rgb(var(--ap-border))]" @if ($setIndex === 0) style="border-top: 1px solid rgb(var(--ap-border))" @endif>
        @foreach ($rowSet as $row)
            @php
                $closed = ! empty($row['blocked']);
                $tag = $closed ? 'div' : 'a';
            @endphp
            <li>
                <{{ $tag }} @unless ($closed) href="{{ $row['url'] }}" @endunless class="{{ $cols }} min-h-[3.25rem] py-2 {{ $closed ? '' : 'hover:bg-gray-50 dark:hover:bg-black/10 transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--brand-blue)]' }}">
                    {{-- 2026-11 redesign: no picture, and on a wide screen the kind beside the name. --}}
                    <span class="flex items-center gap-3 min-w-0">
                        <span class="min-w-0 md:flex md:flex-wrap md:items-baseline md:gap-x-2.5">
                            <span class="block md:inline truncate text-[15px] font-semibold text-gray-900 dark:text-white" v-pre>{{ $row['name'] }}</span>
                            {{-- From a tablet up the numbers have columns and this line says what
                                 kind of schedule it is. A phone has no columns, so this line carries
                                 the two numbers INSTEAD of the kind: with both, a longer language
                                 ("Место проведения") filled the line and the numbers were cut off. --}}
                            <span class="block md:inline truncate text-[13px] text-gray-500 dark:text-gray-400">
                                @if ($closed)
                                    @if (\App\Utils\ScheduleTypeLabel::describe($row['type']) !== ''){{ \App\Utils\ScheduleTypeLabel::describe($row['type']) }} &middot; @endif{{ __('messages.dash_plan_closed') }}
                                @elseif ($viewer)
                                    {{ \App\Utils\ScheduleTypeLabel::for($row['type']) }}
                                @else
                                    <span class="hidden md:inline">{{ \App\Utils\ScheduleTypeLabel::for($row['type']) }}</span>
                                    <span class="md:hidden">{{ __('messages.panel_upcoming_count') }} {{ number_format($row['upcoming']) }} &middot; {{ __('messages.views') }} {{ number_format($row['views']) }}</span>
                                @endif
                            </span>
                        </span>
                    </span>

                    @unless ($viewer)
                        <span class="{{ $num }}">{{ number_format($row['upcoming']) }}</span>
                        <span class="{{ $num }}">{{ number_format($row['views']) }}</span>
                        <span class="{{ $num }} !text-gray-500 dark:!text-gray-400">{{ number_format($row['followers']) }}</span>
                        @if ($hasLive)
                        <span class="hidden md:flex justify-end items-center gap-1.5 text-sm tabular-nums" data-live-schedule="{{ $row['id'] }}">
                            <span data-live-schedule-dot class="w-1.5 h-1.5 rounded-full bg-green-500 {{ $row['now'] ? '' : 'hidden' }}" aria-hidden="true"></span>
                            <span data-live-schedule-count class="{{ $row['now'] ? 'font-medium text-green-700 dark:text-green-400' : 'text-gray-500 dark:text-gray-400' }}">{{ number_format($row['now']) }}</span>
                        </span>
                        @endif
                    @endunless

                    <span class="flex md:hidden items-center gap-2 justify-self-end">
                        @if ($hasLive)
                            {{-- A phone has no room for the columns; it keeps the one figure that
                                 changes while you look. --}}
                            <span class="md:hidden items-center gap-1.5 text-sm font-medium tabular-nums text-green-700 dark:text-green-400 {{ $row['now'] ? 'inline-flex' : 'hidden' }}" data-live-schedule-phone="{{ $row['id'] }}">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500" aria-hidden="true"></span><span data-live-schedule-count>{{ number_format($row['now']) }}</span>
                            </span>
                        @endif
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
