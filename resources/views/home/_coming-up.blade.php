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
    $rowAction = 'inline-flex items-center gap-1.5 px-2.5 py-1 border border-gray-300 dark:border-gray-600 rounded-md text-xs font-semibold text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]';
    $calendarIcon = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5';
@endphp

<section class="ap-card rounded-xl flex flex-col overflow-hidden w-full {{ $stretch ? '' : 'self-start' }}" aria-labelledby="dashboard-coming-up">
    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3 flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1">
        <h2 id="dashboard-coming-up" class="text-base font-semibold text-gray-900 dark:text-white">{{ __('messages.dash_coming_up') }}</h2>
        @if ($numbers && $rows)
            <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.views') }}: {{ __('messages.last_'.$period.'_days') }}</span>
        @endif
    </div>

    @if ($rows)
    <ul class="divide-y divide-gray-100 dark:divide-white/[0.06]" style="border-top: 1px solid var(--ap-hairline)">
        @foreach ($rows as $row)
            @php
                $tickets = $row['tickets'];
                $ticketLine = $tickets === null ? null : __('messages.'.($tickets['paid'] ? 'sold' : 'registered')).' '.(
                    $tickets['capacity']
                        ? __('messages.dash_count_of_total', ['count' => number_format($tickets['sold']), 'total' => number_format($tickets['capacity'])])
                        : number_format($tickets['sold'])
                );
                // A draft nobody has seen has no views to report; a public event with none says so.
                $viewsLine = $row['views'] !== null && ($row['views'] > 0 || $row['visibility'] === 'public')
                    ? __('messages.views').' '.number_format($row['views'])
                    : null;
                $fill = $tickets !== null && $tickets['capacity'] ? min(100, (int) round($tickets['sold'] / $tickets['capacity'] * 100)) : null;
            @endphp
            <li class="relative flex items-center gap-3 px-4 sm:px-5 py-2.5 hover:bg-gray-50 dark:hover:bg-black/10 transition-all duration-200">
                @include('home._thumb', ['image' => $row['image'], 'icon' => $calendarIcon])

                <div class="min-w-0 flex-1">
                    <p class="flex items-center gap-2 min-w-0">
                        {{-- The stretched link: the whole row opens the event, and Check in below
                             stays a control of its own above it (relative z-10). --}}
                        <a href="{{ $row['url'] }}" class="truncate text-sm font-medium text-gray-900 dark:text-white after:absolute after:inset-0 focus:outline-none focus-visible:after:ring-2 focus-visible:after:ring-inset focus-visible:after:ring-[var(--brand-blue)]">{{ $row['name'] }}</a>
                        @if ($row['series'])
                            <span class="{{ $pill }}">{{ __('messages.'.($frequency[$row['frequency'] ?? ''] ?? 'recurring')) }}</span>
                        @endif
                        @if ($row['visibility'] !== 'public')
                            <span class="{{ $pill }}">{{ __('messages.'.$row['visibility']) }}</span>
                        @endif
                    </p>
                    {{-- When first, then whose: a long schedule name cuts itself short at the end
                         of the line, and the time never does. --}}
                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                        @if ($row['when'] === 'today')
                            <span class="font-medium text-[var(--brand-blue)]">{{ __('messages.today') }}</span>
                        @elseif ($row['when'] === 'tomorrow')
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ __('messages.dash_tomorrow') }}</span>
                        @else
                            {{ $row['date'] }}
                        @endif
                        &middot; <span dir="ltr">{{ $row['time'] }}</span>@if ($row['schedule']) &middot; {{ $row['schedule'] }}@endif
                    </p>
                    @if ($ticketLine || $viewsLine)
                        <p class="sm:hidden mt-0.5 text-xs text-gray-700 dark:text-gray-300">{{ collect([$ticketLine, $viewsLine])->filter()->implode(' · ') }}</p>
                    @endif
                    @if ($row['check_in'])
                        <a href="{{ $row['check_in'] }}" class="relative z-10 mt-1.5 {{ $rowAction }}">{{ __('messages.checkin_dashboard') }}</a>
                    @endif
                </div>

                @if ($ticketLine || $viewsLine)
                <div class="hidden sm:block shrink-0 text-end">
                    <p class="text-xs whitespace-nowrap text-gray-700 dark:text-gray-300">{!! $ticketLine ? e($ticketLine) : '&nbsp;' !!}</p>
                    @if ($fill !== null)
                        {{-- How full, drawn: a count against a limit is easier to weigh as a bar. --}}
                        <span class="block my-1 h-1 w-full min-w-[5.5rem] rounded-full bg-gray-200 dark:bg-white/[0.15]" role="img" aria-label="{{ $fill }}%">
                            <span class="block h-1 rounded-full bg-[var(--brand-button-bg)]" style="width: {{ $fill }}%"></span>
                        </span>
                    @endif
                    <p class="text-xs whitespace-nowrap text-gray-500 dark:text-gray-400">{!! $viewsLine ? e($viewsLine) : '&nbsp;' !!}</p>
                </div>
                @endif

                <svg class="w-4 h-4 shrink-0 text-gray-400 {{ is_rtl() ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            </li>
        @endforeach
    </ul>
    @else
        <div class="px-5 py-8 flex flex-col items-center text-center" style="border-top: 1px solid var(--ap-hairline)">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $calendarIcon }}" /></svg>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_upcoming_events') }}</p>
            {{-- The way to add one, where there is exactly one schedule it could go to. With
                 several, "Add event" in the header lists them, and that list is not repeated.
                 Not for a brand-new organizer ($numbers is false there): the setup guide
                 directly above this card is already asking for exactly that. --}}
            @if ($numbers && count($dashboard['addTargets'] ?? []) === 1)
                <x-secondary-link :href="$dashboard['addTargets'][0]['add']" class="mt-4">{{ __('messages.add_event') }}</x-secondary-link>
            @endif
        </div>
    @endif

    <div class="mt-auto px-4 sm:px-5 py-3 flex items-center justify-between gap-3" style="border-top: 1px solid var(--ap-hairline)">
        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.panel_upcoming_count') }} <span class="tabular-nums">{{ number_format($coming['total'] ?? 0) }}</span></span>
        @if ($showCalendar)
            <x-link href="#dashboard-calendar" class="inline-flex items-center gap-1 text-sm font-medium whitespace-nowrap">
                {{ __('messages.all_events') }}
                <svg class="w-3.5 h-3.5 {{ is_rtl() ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            </x-link>
        @endif
    </div>
</section>
