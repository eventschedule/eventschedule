{{-- What is coming up across the person's schedules, soonest first (HomeDashboard::coming()). A
     series is one row at its next date. Today and tomorrow are said in words, and an event on
     today that takes tickets or sign-ups offers the one thing done at the door, under its date
     (beside the numbers it squeezed the event's own name out of the row).

     $numbers: sold and views beside each row. False for a brand-new organizer (there are none
     yet) and for someone who may only view the schedule.
     $stretch: fill the grid row, so this card and the one beside it end on one line.

     One anatomy with the card beside it: the header says what the numbers cover, the footer holds
     the link.

     The row is one link. The Check in control sits beside it, not inside it: an <a> inside an <a>
     is dropped by the browser, and the second link would take the first one's place. --}}
@php
    $rows = $coming['rows'] ?? [];
    $frequency = ['daily' => 'daily', 'weekly' => 'weekly', 'monthly_date' => 'monthly', 'monthly_weekday' => 'monthly', 'yearly' => 'yearly'];
    $pill = 'inline-flex items-center px-1.5 py-0.5 rounded-md text-[11px] font-medium leading-4 whitespace-nowrap bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-300';
    // A row's own small action. Compact: on an event day several rows carry one.
    $rowAction = 'inline-flex items-center gap-1.5 px-3 min-h-[2.5rem] border border-[rgb(var(--ap-border-strong))] dark:border-gray-600 rounded-[0.625rem] text-[13px] font-semibold text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]';
    $calendarIcon = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5';
@endphp

{{-- 2026-11 redesign: the header is a small capitals label with the count and the link to every
     event; a row is its time (or its day) first, then the event, then the one action at the door.
     When every row is on today the label says so ("Today · 5 of 56 upcoming"); otherwise it is
     "Coming up · 56".

     A brand-new organizer ($tiles true) gets the design's first-event card instead: each row a
     calendar tile (the month on a blue band over the day), the name, "Thu, Dec 3 · 20:00", and
     under the first one a two-cell strip of what it has sold and how often it was viewed. The
     numbers are computed for every organizer (HomeDashboard::coming()), so the strip reads them. --}}
@php
    $total = (int) ($coming['total'] ?? 0);
    $allToday = $rows && collect($rows)->every(fn ($row) => $row['when'] === 'today');
    $title = $allToday
        ? __('messages.dash_today_of_upcoming', ['count' => number_format(count($rows)), 'total' => number_format($total)])
        : __('messages.dash_coming_up').' · '.number_format($total);
    // Named apart from the page's own $fresh: an include is handed every variable its parent holds.
    $tiles = ($tiles ?? false) === true;
    $label = 'text-[11px] font-semibold uppercase tracking-[0.06em] text-gray-500 dark:text-gray-400';
@endphp
<section class="ap-card rounded-xl flex flex-col overflow-hidden w-full {{ $stretch ? '' : 'self-start' }}" aria-labelledby="dashboard-coming-up">
    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-2 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
        <h2 id="dashboard-coming-up" class="text-xs font-semibold uppercase tracking-[0.06em] text-gray-500 dark:text-gray-400 tabular-nums">{{ $title }}</h2>
        @if ($showCalendar)
            <a href="#dashboard-calendar" class="inline-flex items-center gap-1 text-[13px] font-semibold whitespace-nowrap text-[var(--brand-blue)] hover:underline">
                {{ __('messages.all_events') }}
                <svg class="w-3.5 h-3.5 {{ is_rtl() ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
            </a>
        @endif
    </div>

    @if ($rows && $tiles)
    <ul class="px-4 sm:px-5 pb-4 sm:pb-5 pt-2 flex flex-col gap-4">
        @foreach ($rows as $row)
            @php
                $tickets = $row['tickets'];
            @endphp
            <li class="flex flex-col gap-4">
                <a href="{{ $row['url'] }}" class="group flex items-center gap-3.5 min-w-0 rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
                    {{-- The calendar tile. The month is the app's own word, so it is not v-pre;
                         the day is a number. --}}
                    <span class="w-12 shrink-0 overflow-hidden rounded-[0.625rem] text-center" style="border: 1px solid rgb(var(--ap-border))" aria-hidden="true">
                        <span class="block py-0.5 text-[10px] font-bold uppercase tracking-[0.06em] text-white bg-[var(--brand-button-bg)]">{{ $row['month'] }}</span>
                        <span class="block pt-[3px] pb-1 text-lg font-bold leading-tight text-gray-900 dark:text-white tabular-nums">{{ $row['day'] }}</span>
                    </span>
                    <span class="flex flex-col gap-[3px] min-w-0">
                        <span class="flex items-center gap-2 min-w-0">
                            <span class="break-words text-base font-semibold tracking-[-0.01em] text-gray-900 dark:text-white group-hover:text-[var(--brand-blue)]" v-pre>{{ $row['name'] }}</span>
                            @if ($row['visibility'] !== 'public')
                                <span class="{{ $pill }}">{{ __('messages.'.$row['visibility']) }}</span>
                            @endif
                        </span>
                        <span class="text-[13px] text-gray-600 dark:text-gray-400">{{ $row['date'] }} &middot; <span dir="ltr">{{ $row['time'] }}</span>@if ($row['series']) &middot; {{ __('messages.'.($frequency[$row['frequency'] ?? ''] ?? 'recurring')) }}@endif</span>
                    </span>
                </a>
                @if ($loop->first)
                    {{-- Sold (or registered) and views, two cells. An event with neither tickets
                         nor sign-ups has nothing to count there, and shows a dash. --}}
                    <div class="grid grid-cols-2 rounded-[0.625rem]" style="border: 1px solid rgb(var(--ap-border))">
                        <div class="px-3 py-2.5 flex flex-col gap-0.5" style="border-inline-end: 1px solid rgb(var(--ap-border))">
                            <span class="{{ $label }}">{{ __('messages.'.($tickets && ! $tickets['paid'] ? 'registered' : 'sold')) }}</span>
                            <span class="text-sm font-semibold tabular-nums text-gray-900 dark:text-white">
                                @if ($tickets === null)
                                    <span class="text-gray-500 dark:text-gray-400" aria-hidden="true">&ndash;</span>
                                @elseif ($tickets['capacity'])
                                    {{ __('messages.dash_count_of_total', ['count' => number_format($tickets['sold']), 'total' => number_format($tickets['capacity'])]) }}
                                @else
                                    {{ number_format($tickets['sold']) }}
                                @endif
                            </span>
                        </div>
                        <div class="px-3 py-2.5 flex flex-col gap-0.5">
                            <span class="{{ $label }}">{{ __('messages.views') }}</span>
                            <span class="text-sm font-semibold tabular-nums text-gray-900 dark:text-white">{{ number_format($row['views'] ?? 0) }}</span>
                        </div>
                    </div>
                @endif
            </li>
        @endforeach
    </ul>
    @elseif ($rows)
    <ul class="px-4 sm:px-5 pb-2 divide-y divide-[rgb(var(--ap-border))]">
        @foreach ($rows as $row)
            @php
                $tickets = $row['tickets'];
                $ticketLine = $tickets === null ? null : (
                    $tickets['capacity']
                        ? __('messages.dash_count_of_total', ['count' => number_format($tickets['sold']), 'total' => number_format($tickets['capacity'])])
                        : number_format($tickets['sold'])
                ).' '.mb_strtolower(__('messages.'.($tickets['paid'] ? 'sold' : 'registered')));
                $whenLine = $row['when'] === 'today'
                    ? null
                    : ($row['when'] === 'tomorrow' ? __('messages.dash_tomorrow') : $row['date']);
            @endphp
            <li class="relative flex items-center gap-3 py-3">
                {{-- Today's rows lead with the time; any other day leads with the day, and the time
                     follows it. Either way in the brand colour: a scheduled thing leads with when,
                     in blue (the design review of 2026-11). --}}
                <span class="w-[4.25rem] shrink-0 text-[13px] font-semibold leading-tight tabular-nums text-[var(--brand-blue)]" dir="auto">
                    @if ($whenLine)
                        <span class="block">{{ $whenLine }}</span>
                    @endif
                    <span class="block whitespace-nowrap {{ $whenLine ? 'font-normal text-gray-500 dark:text-gray-400' : '' }}" dir="ltr">{{ $row['time'] }}</span>
                </span>

                <div class="min-w-0 flex-1 flex flex-col gap-0.5">
                    <p class="flex items-center gap-2 min-w-0">
                        {{-- The whole row is the link; Check-in stays a control of its own above it. --}}
                        <a href="{{ $row['url'] }}" class="break-words text-[15px] font-semibold tracking-[-0.01em] text-gray-900 dark:text-white after:absolute after:inset-0 focus:outline-none focus-visible:after:ring-2 focus-visible:after:ring-inset focus-visible:after:ring-[var(--brand-blue)]" v-pre>{{ $row['name'] }}</a>
                        @if ($row['visibility'] !== 'public')
                            <span class="{{ $pill }}">{{ __('messages.'.$row['visibility']) }}</span>
                        @endif
                    </p>
                    {{-- Wraps rather than cuts: "Recurring" at the end of a long venue name used
                         to be cut to "R…" on the first row. --}}
                    <p class="text-[13px] leading-snug text-gray-600 dark:text-gray-400">
                        {{ collect([$row['schedule'] ?? null, $ticketLine])->filter()->implode(' · ') }}
                        @if ($row['series'])
                            @if ($row['schedule'] || $ticketLine) &middot; @endif{{ __('messages.'.($frequency[$row['frequency'] ?? ''] ?? 'recurring')) }}
                        @endif
                    </p>
                </div>

                @if ($row['check_in'])
                    <a href="{{ $row['check_in'] }}" class="relative z-10 shrink-0 {{ $rowAction }}">{{ __('messages.checkin_dashboard') }}</a>
                @endif
            </li>
        @endforeach
    </ul>
    @else
        <div class="px-5 py-8 flex flex-col items-center text-center">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIcon }}" /></svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_upcoming_events') }}</p>
            {{-- A way to add one, where there is exactly one place to add it to. With several, "Add
                 event" in the header lists them, and that list is not repeated. Not for a brand-new
                 organizer ($numbers is false there): the setup guide is already asking for it. --}}
            @if ($numbers && count($dashboard['addTargets'] ?? []) === 1)
                <x-secondary-link :href="$dashboard['addTargets'][0]['add']" class="mt-4">{{ __('messages.add_event') }}</x-secondary-link>
            @endif
        </div>
    @endif
</section>
