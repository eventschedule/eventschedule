{{-- What the schedule's calendar syncs are keeping off its public page (CalendarArrival::notice()),
     on the Schedule tab, for the people who can publish it: the owner and the admins. Calm by
     default: a strip like the one a new event gets. Amber where something was held for its text or
     taken off the page, which the owner should look at. Event names are the owner's calendar's own
     text, so they are v-pre and in <bdi>. None of these is a live region: they are part of the page
     as it opens, not an answer to something just done. --}}
@if (auth()->user()?->isEditor($role->subdomain))
@php
    $calendarNotice = \App\Services\CalendarArrival::notice($role);
    $readingStarted = session('calendar_reading_started');
    $paneOf = fn (?string $provider) => in_array($provider, \App\Services\CalendarArrival::PROVIDERS, true) ? $provider : 'google';
    $rowUrl = fn (?string $provider) => route('role.edit', ['subdomain' => $role->subdomain]).'?integration_tab='.$paneOf($provider).'#integration-tab-'.$paneOf($provider);
    $reviewUrl = route('role.calendar_review', ['subdomain' => $role->subdomain]);
    $isOwner = (int) auth()->id() === (int) $role->user_id;
    $amber = 'flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-900/20';
    $triangle = '<svg class="w-5 h-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>';
@endphp

{{-- First: a sync that publishes a main calendar right now is the one that matters most. --}}
@if ($calendarNotice && $calendarNotice['confirm'] && $isOwner)
<div class="pb-4">
    <div class="{{ $amber }}">
        {!! $triangle !!}
        <div class="min-w-0 flex-1 text-sm text-amber-900 dark:text-amber-100">
            <p>{{ __('messages.calendar_confirm_main', ['provider' => \App\Services\CalendarArrival::providerName($calendarNotice['confirm'])]) }}</p>
            <div class="mt-3 flex flex-wrap items-center gap-3">
                <form method="post" action="{{ route('role.calendar_review.keep_publishing', ['subdomain' => $role->subdomain, 'provider' => $calendarNotice['confirm']]) }}">
                    @csrf
                    <button type="submit" class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">{{ __('messages.calendar_keep_publishing') }}</button>
                </form>
                <x-brand-link :href="$rowUrl($calendarNotice['confirm'])">{{ __('messages.choose_a_calendar') }}</x-brand-link>
            </div>
        </div>
    </div>
</div>
@endif

@if (is_string($readingStarted) && ! ($calendarNotice['waiting'] ?? 0))
<div class="pb-4">
    <div class="ap-card rounded-xl px-4 py-3 flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300" role="status">
        <svg class="w-5 h-5 shrink-0 text-[var(--brand-blue)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
        </svg>
        <p>{{ __(\App\Services\CalendarArrival::publishes($role, $readingStarted) ? 'messages.calendar_reading_started_publishing' : 'messages.calendar_reading_started', ['provider' => \App\Services\CalendarArrival::providerName($readingStarted)]) }}</p>
    </div>
</div>
@endif

@if ($calendarNotice && $calendarNotice['taken'] > 0)
<div class="pb-4">
    <div class="{{ $amber }}">
        {!! $triangle !!}
        <div class="min-w-0 flex-1 text-sm text-amber-900 dark:text-amber-100">
            <p class="font-semibold">{{ trans_choice('messages.calendar_taken_off_title', $calendarNotice['taken'], ['count' => number_format($calendarNotice['taken'])]) }}</p>
            {{-- "Comes back by itself" only while the sync reads that calendar. --}}
            <p class="mt-1">{{ __($calendarNotice['taken_reads'] ? 'messages.calendar_taken_off_body' : 'messages.calendar_taken_off_body_paused', ['provider' => \App\Services\CalendarArrival::providerName($calendarNotice['taken_provider'])]) }}</p>
            @if ($calendarNotice['taken_events']->isNotEmpty())
            <ul class="mt-2 space-y-0.5" v-pre>
                @foreach ($calendarNotice['taken_events'] as $takenEvent)
                <li><bdi>{{ $takenEvent->name }}</bdi>@if ($takenEvent->starts_at) <span class="text-amber-800 dark:text-amber-200">&middot; {{ $takenEvent->getStartDateTime(null, true)->translatedFormat('D j M') }}</span>@endif</li>
                @endforeach
            </ul>
            @endif
            <p class="mt-3">
                <x-link :href="route('role.calendar_review', ['subdomain' => $role->subdomain, 'show' => 'taken'])">{{ trans_choice('messages.calendar_see_events', $calendarNotice['taken'], ['count' => number_format($calendarNotice['taken'])]) }}</x-link>
            </p>
        </div>
    </div>
</div>
@endif

@if ($calendarNotice && $calendarNotice['hidden_by_admin'] > 0)
<div class="pb-4">
    <div class="{{ $amber }}">
        {!! $triangle !!}
        <div class="min-w-0 flex-1 text-sm text-amber-900 dark:text-amber-100">
            <p class="font-semibold">{{ trans_choice('messages.calendar_hidden_by_admin_title', $calendarNotice['hidden_by_admin'], ['count' => number_format($calendarNotice['hidden_by_admin'])]) }}</p>
            <p class="mt-1">{{ __('messages.calendar_hidden_by_admin_body') }}</p>
            <p class="mt-3">
                <x-link :href="route('role.calendar_review', ['subdomain' => $role->subdomain, 'show' => 'taken'])">{{ trans_choice('messages.calendar_see_events', $calendarNotice['hidden_by_admin'], ['count' => number_format($calendarNotice['hidden_by_admin'])]) }}</x-link>
            </p>
        </div>
    </div>
</div>
@endif

@if ($calendarNotice && $calendarNotice['waiting'] > 0)
@php
    $firstPull = $calendarNotice['first_pull'];
    $leftOut = $firstPull ? (int) ($firstPull['private'] ?? 0) + (int) ($firstPull['declined'] ?? 0) + (int) ($firstPull['not_an_event'] ?? 0) + (int) ($firstPull['cancelled'] ?? 0) : 0;
    $held = $calendarNotice['held'] > 0;
@endphp
<div class="pb-4">
    <div class="{{ $held ? 'rounded-xl border border-amber-200 bg-amber-50 dark:border-amber-700 dark:bg-amber-900/20' : 'ap-card rounded-xl' }} px-4 py-4 sm:px-5" id="calendar-waiting">
        <div class="flex flex-col gap-x-6 gap-y-3 sm:flex-row sm:items-start">
            <div class="min-w-0 flex-1 flex items-start gap-3">
                @if ($held)
                {!! $triangle !!}
                @else
                <svg class="w-5 h-5 mt-0.5 shrink-0 text-[var(--brand-blue)]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                @endif
                <div class="min-w-0 text-sm {{ $held ? 'text-amber-900 dark:text-amber-100' : 'text-gray-700 dark:text-gray-300' }}">
                    <p class="font-semibold {{ $held ? '' : 'text-gray-900 dark:text-gray-100' }}">{{ trans_choice('messages.calendar_waiting_title', $calendarNotice['waiting'], ['count' => number_format($calendarNotice['waiting']), 'provider' => \App\Services\CalendarArrival::providerName($calendarNotice['provider'])]) }}</p>
                    <p class="mt-0.5">{{ __('messages.calendar_waiting_body') }}</p>
                    @if ($held)
                    <p class="mt-0.5">{{ trans_choice('messages.calendar_waiting_held', $calendarNotice['held'], ['count' => number_format($calendarNotice['held'])]) }}</p>
                    @endif
                    @if ($firstPull && (int) ($firstPull['created'] ?? 0) > 0)
                    <p class="mt-0.5 {{ $held ? '' : 'text-gray-500 dark:text-gray-400' }}">
                        {{ __('messages.calendar_first_read_added', ['added' => number_format((int) $firstPull['created'])]) }}
                        @if ($leftOut > 0)
                        {{ __('messages.calendar_first_read_left_out', ['left' => number_format($leftOut)]) }}
                        @endif
                    </p>
                    @endif
                    @if ($calendarNotice['main'] && ! $calendarNotice['confirm'])
                    <p class="mt-0.5 {{ $held ? '' : 'text-gray-500 dark:text-gray-400' }}">{{ __('messages.calendar_main_calendar_note') }}</p>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                @if (! $calendarNotice['publishes'] && $calendarNotice['reads'] && $isOwner)
                <x-link :href="$rowUrl($calendarNotice['provider'])">{{ __('messages.publish_on_arrival') }}</x-link>
                @endif
                <x-brand-link :href="$reviewUrl">{{ trans_choice('messages.calendar_review_button', $calendarNotice['waiting'], ['count' => number_format($calendarNotice['waiting'])]) }}</x-brand-link>
            </div>
        </div>
    </div>
</div>
@endif
@endif
