<x-app-guest-layout :role="$role" :noIndex="true" :page-title="__('messages.appointments')">
    @php
        $manageParams = ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'secret' => $sale->secret];

        // A pale accent is unreadable as text on the card and a pale accent behind white text is
        // unreadable as a button label, so both directions get a contrast-checked value. The readable
        // variant differs per mode because the card surface does.
        $accent = $role->accent_color ?? '#4E81FA';
        $accentText = accent_contrast_color($accent);
        $accentOnLight = \App\Utils\ColorUtils::readableAccentColor($accent, '#ffffff', '#111827');
        $accentOnDark = \App\Utils\ColorUtils::readableAccentColor($accent, '#252526', '#ffffff');

        // $rescheduleBlocked is supplied by the controller from the same helper the reschedule endpoints
        // use, so the button can never offer something the POST would refuse.
        $canReschedule = ($rescheduleBlocked ?? 'blocked') === null;
        $rescheduleUrl = route('appointments.reschedule', $manageParams);

        // The picker showed the guest their OWN zone and we stored it, so the confirmation has to
        // agree - otherwise someone who booked "3:00 PM" in New York lands on a page saying 8:00 PM.
        $use24 = get_use_24_hour_time($role);
        $guestTz = $sale->guestTimezone();
        $scheduleTz = \App\Utils\AppointmentTimeUtils::scheduleTimezone($event);
        $shown = \App\Utils\AppointmentTimeUtils::render($event, $guestTz, $use24);
        $inSchedule = ($guestTz && $guestTz !== $scheduleTz)
            ? \App\Utils\AppointmentTimeUtils::render($event, $scheduleTz, $use24)
            : null;
    @endphp

    <style {!! nonce_attr() !!}>
        #appt-manage { --es-accent: {{ $accent }}; --es-accent-text: {{ $accentText }}; --es-accent-readable: {{ $accentOnLight }}; }
        .dark #appt-manage { --es-accent-readable: {{ $accentOnDark }}; }
        /* x-appointment-add-to-calendar uses these, but they were only defined under #booking-app - so
           the "primary" variant rendered with no background and no text colour on this page. */
        #appt-manage .es-accent-fill { background-color: var(--es-accent); color: var(--es-accent-text); }
        #appt-manage .es-accent-text { color: var(--es-accent-readable); }
    </style>

    <div id="appt-manage" class="max-w-2xl mx-auto px-4 py-10">
        @if (session('message'))
            <div class="mb-4 p-3 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300">{{ session('message') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300">{{ session('error') }}</div>
        @endif

        {{-- First landing after booking. The controller has always appended ?new=1 but nothing read
             it. Presentation only - the param is guest-settable, so nothing is gated on it. --}}
        {{-- Landed here straight after a move. Both times are stated in full rather than "check your
             email", because appointmentCanSend() can silently suppress the mail entirely. --}}
        @if (request()->boolean('moved'))
            @php $movedBand = $state === 'pending' ? 'amber' : 'green'; @endphp
            <div class="mb-4 flex items-start gap-3 rounded-lg border p-4 {{ $movedBand === 'amber' ? 'border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 text-amber-800 dark:text-amber-200' : 'border-green-200 dark:border-green-700 bg-green-50 dark:bg-green-900/20 text-green-800 dark:text-green-200' }}">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="text-sm">
                    @if ($state === 'pending')
                        <p>{{ __('messages.appointments_rescheduled_pending_message', ['time' => $shown['time'], 'schedule' => $role?->name ?? '']) }}</p>
                    @else
                        <p>{{ __('messages.appointments_rescheduled_message') }}</p>
                        <p class="mt-1 text-xs">{{ __('messages.update_your_calendar_note') }}</p>
                    @endif
                </div>
            </div>
        @endif

        @if (request()->boolean('new') && in_array($state, ['confirmed', 'pending']))
            <div class="mb-4 flex items-start gap-3 rounded-lg border border-green-200 dark:border-green-700 bg-green-50 dark:bg-green-900/20 p-4 text-green-800 dark:text-green-200">
                <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm">{{ __('messages.appointments_email_on_the_way', ['email' => $sale->email]) }}</p>
            </div>
        @endif

        {{-- Opaque surface: the schedule background is a photo or gradient by default, so a
             transparent card leaves the whole confirmation unreadable in light mode. --}}
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm p-6">
            @switch($state)
                @case('pending')
                    <h1 tabindex="-1" class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.appointments_request_sent') }}</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('messages.appointments_pending_note', ['schedule' => $role?->name ?? '']) }}</p>
                    @break
                @case('awaiting_payment')
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.appointments_awaiting_payment') }}</h1>
                    <form method="POST" action="{{ route('appointments.pay', $manageParams) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="px-4 py-3 text-base rounded-lg font-semibold transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-800"
                                style="background-color: var(--es-accent); color: var(--es-accent-text); --tw-ring-color: var(--es-accent)">{{ __('messages.appointments_complete_payment') }}</button>
                    </form>
                    @break
                @case('cancelled')
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.appointments_cancelled') }}</h1>
                    @break
                @case('passed')
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.appointments_passed') }}</h1>
                    @break
                @default
                    <h1 tabindex="-1" class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.appointments_youre_booked') }}</h1>
            @endswitch

            {{-- A labelled two-column grid rather than a stack of bare lines: the date and the time
                 pair naturally, and the whole block loses a third of its height. --}}
            <div class="mt-4 text-gray-700 dark:text-gray-300">
                <div class="font-semibold text-lg text-gray-900 dark:text-gray-100">{{ $type?->name ?? $event->name }}</div>
                <dl class="mt-3 grid gap-x-6 gap-y-3 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.date') }}</dt>
                        <dd>{{ $shown['date'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.time') }}</dt>
                        <dd>{{ $shown['time'] }} ({{ $shown['tz'] }})</dd>
                        @if ($inSchedule)
                            <dd class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.appointments_schedule_in') }} {{ $inSchedule['tz'] }} ({{ $inSchedule['time'] }})</dd>
                        @endif
                    </div>
                    {{-- Labelled, and covering the phone case the confirmation email has always had.
                         Full width: a meeting URL is long and wraps with break-all. --}}
                    @if ($event->event_url)
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.online') }}</dt>
                            <dd><a href="{{ $event->event_url }}" class="break-all hover:underline" style="color: var(--es-accent-readable)">{{ $event->event_url }}</a></dd>
                        </div>
                    @elseif ($type && $type->location_type === 'in_person' && $type->location_address)
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.location') }}</dt>
                            <dd>{{ $type->location_address }}</dd>
                        </div>
                    @elseif ($type && $type->location_type === 'phone' && ($type->location_phone || $sale->phone))
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.phone') }}</dt>
                            <dd>{{ $type->location_phone ?: $sale->phone }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- One action row instead of two stacked blocks. Add to calendar is only offered for a
                 confirmed booking: the pending state says outright that nothing is booked yet, so a
                 calendar entry there would be misleading. Reschedule sits beside it; Cancel stays a
                 quiet red link below, destructive-last, so it cannot be mis-tapped. --}}
            @if ($state === 'confirmed' || $canReschedule)
                <div class="mt-5 flex flex-wrap items-center gap-3">
                    @if ($state === 'confirmed')
                        <x-appointment-add-to-calendar :event="$event" :sale="$sale" :role="$role" :primary="request()->boolean('new')" />
                    @endif
                    @if ($canReschedule)
                        <a href="{{ $rescheduleUrl }}"
                           class="inline-flex items-center rounded-lg border border-gray-300 dark:border-gray-600 px-4 py-3 text-base font-semibold text-gray-700 dark:text-gray-300 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                            {{ $state === 'pending' ? __('messages.appointments_change_time') : __('messages.appointments_reschedule') }}
                        </a>
                    @endif
                </div>
            @endif

            @if ($canReschedule)
                {{-- Said BEFORE they commit: a confirmed booking on an approval type is about to be
                     released the moment they pick a new time. --}}
                @if ($state === 'confirmed' && $type?->requires_approval)
                    <div class="mt-3 flex items-start gap-2 rounded-lg border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-3 text-amber-800 dark:text-amber-200">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.5 0L3.16 16.25A2 2 0 005 19z" /></svg>
                        <p class="text-xs">{{ __('messages.appointments_reschedule_approval_warning', ['schedule' => $role?->name ?? '']) }}</p>
                    </div>
                @endif
            @elseif ($state === 'awaiting_payment')
                {{-- An explanation with a real alternative, not a disabled button that reads as broken. --}}
                <div class="mt-4 flex items-start gap-2 rounded-lg border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-3 text-amber-800 dark:text-amber-200">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.5 0L3.16 16.25A2 2 0 005 19z" /></svg>
                    <p class="text-xs">{{ __('messages.appointments_reschedule_blocked_payment') }}</p>
                </div>
            @endif

            @if (in_array($state, ['confirmed', 'pending', 'awaiting_payment']))
                <form method="POST" action="{{ route('appointments.manage_cancel', $manageParams) }}" class="mt-6">
                    @csrf
                    <button type="submit" class="text-red-600 dark:text-red-400 text-sm hover:underline">{{ __('messages.appointments_cancel_booking') }}</button>
                </form>
            @endif

            {{-- 'passed' used to be a dead end: heading, details, nothing to do. A guest returning to an
                 old link almost always wants to book again. --}}
            @if (in_array($state, ['cancelled', 'passed']))
                <a href="{{ route('appointments.book', ['subdomain' => $role->subdomain]) }}" class="inline-block mt-6 text-sm hover:underline" style="color: var(--es-accent-readable)">{{ __('messages.appointments_book_again') }}</a>
            @endif

            <p class="mt-6 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.appointments_manage_link_hint') }}</p>
        </div>
    </div>
</x-app-guest-layout>
