{{-- The dashboard of someone who runs no schedule: the tickets they hold for what is still to
     come, and what the schedules they follow have on (HomeDashboard::attendee()). No organizer
     numbers, and no "create your first schedule" as the page's opening line: they came for their
     ticket.

     The invitation to start a schedule is a small card under their tickets, where the page would
     otherwise have a hole beside the longer list.

     Someone whose tickets are all behind them still gets the tickets card, saying so, with the
     way to all of them: they are an attendee, and the page must not greet them as a stranger. --}}
@php
    $tickets = $dashboard['tickets'];
    $follows = $dashboard['follows'];
    $row = 'relative flex items-center gap-3 px-4 sm:px-5 py-2.5 hover:bg-gray-50 dark:hover:bg-black/10 transition-all duration-200';
    $rowLink = 'truncate text-sm font-medium text-gray-900 dark:text-white after:absolute after:inset-0 focus:outline-none focus-visible:after:ring-2 focus-visible:after:ring-inset focus-visible:after:ring-[var(--brand-blue)]';
    $chevron = '<svg class="w-4 h-4 shrink-0 text-gray-400 '.(is_rtl() ? 'rotate-180' : '').'" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>';
    $footLink = 'inline-flex items-center gap-1 text-sm font-medium whitespace-nowrap';
    $ticketIcon = 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z';
    $calendarIcon = 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5';
    $when = fn (array $item) => $item['when'] === 'today' ? __('messages.today') : ($item['when'] === 'tomorrow' ? __('messages.dash_tomorrow') : $item['date']);
@endphp

<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
    <div class="space-y-4">
        @if ($tickets || ! empty($dashboard['holdsTickets']))
        <section class="ap-card rounded-xl flex flex-col overflow-hidden" aria-labelledby="dashboard-tickets">
            <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3">
                <h2 id="dashboard-tickets" class="text-base font-semibold text-gray-900 dark:text-white">{{ __('messages.your_tickets') }}</h2>
            </div>
            @if (! $tickets)
                <p class="px-5 py-8 text-sm text-center text-gray-500 dark:text-gray-400" style="border-top: 1px solid var(--ap-hairline)">{{ __('messages.no_upcoming_events') }}</p>
            @else
            <ul class="divide-y divide-gray-100 dark:divide-white/[0.06]" style="border-top: 1px solid var(--ap-hairline)">
                @foreach ($tickets as $ticket)
                    @php $count = __('messages.tickets').' '.number_format($ticket['quantity']); @endphp
                    <li class="{{ $row }}">
                        @include('home._thumb', ['image' => $ticket['image'], 'icon' => $ticketIcon])
                        <div class="min-w-0 flex-1">
                            <p class="flex min-w-0"><a href="{{ $ticket['url'] }}" class="{{ $rowLink }}">{{ $ticket['name'] }}</a></p>
                            <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                <span class="{{ $ticket['when'] === 'today' ? 'font-medium text-[var(--brand-blue)]' : ($ticket['when'] ? 'font-medium text-gray-700 dark:text-gray-300' : '') }}">{{ $when($ticket) }}</span>
                                &middot; <span dir="ltr">{{ $ticket['time'] }}</span>
                                @if ($ticket['place'])<span class="hidden sm:inline"> &middot; {{ $ticket['place'] }}</span>@endif
                            </p>
                            <p class="sm:hidden truncate text-xs text-gray-500 dark:text-gray-400">{{ collect([$ticket['place'], $count])->filter()->implode(' · ') }}</p>
                        </div>
                        <span class="shrink-0 hidden sm:inline-flex items-center gap-1.5 text-xs font-medium text-gray-700 dark:text-gray-300">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $ticketIcon }}" /></svg>
                            {{ $count }}
                        </span>
                        {!! $chevron !!}
                    </li>
                @endforeach
            </ul>
            @endif
            <div class="mt-auto px-4 sm:px-5 py-3 flex items-center justify-end" style="border-top: 1px solid var(--ap-hairline)">
                {{-- Past ones too when nothing is ahead: that is where this person's tickets are. --}}
                <x-link :href="route('tickets', $tickets ? [] : ['past' => 1])" class="{{ $footLink }}">{{ __('messages.all_tickets') }}</x-link>
            </div>
        </section>
        @endif

        @if (! is_demo_mode() && $canCreateSchedule)
        <section class="ap-card rounded-xl p-4 sm:p-5 flex flex-wrap items-center gap-3">
            <div class="min-w-0 flex-1">
                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ __('messages.dash_own_events_title') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.dash_own_events_body') }}</p>
            </div>
            {{-- Its own menu of the three kinds, not a link to /getting-started: that page sends
                 anyone who holds a ticket straight back here. --}}
            <div class="relative inline-block text-start">
                {{-- A button with <x-secondary-link>'s classes: it opens a menu, so it cannot be the link. --}}
                <button type="button" class="popup-toggle ap-secondary-btn inline-flex items-center justify-center gap-1.5 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800" data-popup-target="dashboard-invite-menu" aria-expanded="false">
                    {{ __('messages.create_schedule') }}
                    <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                </button>
                <div id="dashboard-invite-menu" class="ap-dropdown pop-up-menu hidden absolute end-0 z-10 mt-2 w-56 max-w-[calc(100vw-2rem)] rounded-md ring-1 ring-black/5 dark:ring-white/[0.06] focus:outline-none">
                    <div class="py-1" data-popup-target="dashboard-invite-menu">
                        @foreach (['talent', 'venue', 'curator'] as $type)
                            <a href="{{ route('new', ['type' => $type]) }}" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-black/10">{{ __('messages.'.$type) }}</a>
                        @endforeach
                    </div>
                </div>
            </div>
        </section>
        @endif
    </div>

    @if ($dashboard['following'] > 0)
    <section class="ap-card rounded-xl flex flex-col overflow-hidden" aria-labelledby="dashboard-following">
        <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3">
            <h2 id="dashboard-following" class="text-base font-semibold text-gray-900 dark:text-white">{{ __('messages.dash_from_following') }}</h2>
        </div>
        @if ($follows)
        <ul class="divide-y divide-gray-100 dark:divide-white/[0.06]" style="border-top: 1px solid var(--ap-hairline)">
            @foreach ($follows as $event)
                <li class="{{ $row }}">
                    @include('home._thumb', ['image' => $event['image'], 'icon' => $calendarIcon])
                    <div class="min-w-0 flex-1">
                        <p class="flex min-w-0"><a href="{{ $event['url'] }}" class="{{ $rowLink }}">{{ $event['name'] }}</a></p>
                        <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                            <span class="{{ $event['when'] === 'today' ? 'font-medium text-[var(--brand-blue)]' : ($event['when'] ? 'font-medium text-gray-700 dark:text-gray-300' : '') }}">{{ $when($event) }}</span>
                            &middot; <span dir="ltr">{{ $event['time'] }}</span>
                            @if ($event['host'])<span class="hidden sm:inline"> &middot; {{ $event['host'] }}</span>@endif
                        </p>
                        @if ($event['host'])<p class="sm:hidden truncate text-xs text-gray-500 dark:text-gray-400">{{ $event['host'] }}</p>@endif
                    </div>
                    {!! $chevron !!}
                </li>
            @endforeach
        </ul>
        @else
            <p class="px-5 py-8 text-sm text-center text-gray-500 dark:text-gray-400" style="border-top: 1px solid var(--ap-hairline)">{{ __('messages.no_upcoming_events') }}</p>
        @endif
        <div class="mt-auto px-4 sm:px-5 py-3 flex items-center justify-end" style="border-top: 1px solid var(--ap-hairline)">
            <x-link :href="route('following')" class="{{ $footLink }}">{{ __('messages.dash_schedules_you_follow') }}</x-link>
        </div>
    </section>
    @endif
</div>
