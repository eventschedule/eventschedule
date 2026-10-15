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

        // How long an unpaid online booking keeps its time, in the zone the booking is shown in.
        // The sale's age against the hold stamped on the event at booking, which is what
        // app:release-tickets compares; it runs hourly, so this is the earliest the time can go, not
        // the moment it does. Left out once that moment has passed and the sweep has not yet run.
        $heldUntil = null;
        if ($state === 'awaiting_payment' && (int) $event->expire_unpaid_tickets > 0 && $sale->created_at) {
            $heldAt = $sale->created_at->copy()->addHours((int) $event->expire_unpaid_tickets)->setTimezone($shown['tz']);
            if ($heldAt->isFuture()) {
                // Isolated left to right: inside a Hebrew or Arabic sentence "5:23 PM" drew as "PM 5:23".
                $clock = "\u{2066}".$heldAt->format($use24 ? 'H:i' : 'g:i A')."\u{2069}";
                $heldUntil = $heldAt->isSameDay(now($shown['tz'])) ? $clock : $heldAt->translatedFormat('D, M j').', '.$clock;
            }
        }
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
                    {{-- Back from the payment page without paying (PaymentGatewayDriver::handleCancel(),
                         and Stripe's cancel_url). Presentation only, like ?new=1, and only in this
                         state, so reopening the address after paying says nothing. Same words as the
                         ticket page's (event/partials/payment-cancelled). --}}
                    @if (request()->query('payment') === 'cancelled')
                        <div role="status" class="mb-4 flex items-center gap-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50 p-3">
                            <svg class="w-5 h-5 flex-shrink-0 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                            </svg>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('messages.payment_cancelled_not_charged') }}</span>
                        </div>
                    @endif
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.appointments_awaiting_payment') }}</h1>
                    @if ($heldUntil)
                        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('messages.appointments_held_until', ['time' => $heldUntil]) }}</p>
                    @endif
                    <form method="POST" action="{{ route('appointments.pay', $manageParams) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="px-4 py-3 text-base rounded-lg font-semibold transition-all duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-offset-white dark:focus-visible:ring-offset-gray-800"
                                style="background-color: var(--es-accent); color: var(--es-accent-text); --tw-ring-color: var(--es-accent)">{{ __('messages.appointments_complete_payment') }}</button>
                    </form>
                    @break
                @case('in_review')
                    {{-- The provider has the payment and has not confirmed it: PayPal is reviewing it,
                         or the guest came back from paying and the confirmation is later than the
                         confirming window (an Instant EFT can clear hours later). No pay button, which
                         would start a second payment, and no Cancel, which would leave the money with
                         no booking. No polling either, so nothing here says the page updates itself. --}}
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.ticket_confirming_payment') }}</h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('messages.appointments_payment_in_review', ['schedule' => $role?->name ?? '']) }}</p>
                    @break
                @case('confirming')
                    {{-- No pay button while the provider's confirmation is on its way: the guest has
                         just paid. ticket/partials/confirming-script asks ticket.status (any sale, by
                         event and secret) and reloads once the answer changes or the window closes. --}}
                    <div data-confirming="{{ \App\Services\Payments\PaymentGatewayDriver::landedSecondsLeft($sale) }}"
                         data-status-url="{{ route('ticket.status', $manageParams, false) }}">
                        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.ticket_confirming_payment') }}</h1>
                        <p class="text-gray-600 dark:text-gray-400 mt-1">{{ __('messages.ticket_confirming_payment_hint') }}</p>
                    </div>
                    @break
                @case('cancelled')
                    {{-- A hold that lapsed unpaid is not a booking somebody cancelled: read as one, the
                         guest says "it was cancelled" and the owner thinks they did it. --}}
                    <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">{{ $sale->status === 'expired' ? __('messages.this_reservation_has_expired') : __('messages.appointments_cancelled') }}</h1>
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
                {{-- The team member the booking was made with, by name only. --}}
                @if ($hostName = $event->appointmentHostName())
                    <div class="text-sm text-gray-600 dark:text-gray-400" v-pre>{{ __('messages.appointments_with_host', ['name' => $hostName]) }}</div>
                @endif
                <dl class="mt-3 grid gap-x-6 gap-y-3 sm:grid-cols-2">
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.date') }}</dt>
                        <dd>{{ $shown['date'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.time') }}</dt>
                        {{-- Left to right inside a right-to-left page: "9:00 AM - 9:30 AM" drew as "AM - 9:30 AM 9:00". --}}
                        <dd><bdi dir="ltr">{{ $shown['time'] }} ({{ $shown['tz'] }})</bdi></dd>
                        @if ($inSchedule)
                            <dd class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.appointments_schedule_in') }} <bdi dir="ltr">{{ $inSchedule['tz'] }} ({{ $inSchedule['time'] }})</bdi></dd>
                        @endif
                    </div>
                    {{-- Labelled, and covering the phone case the confirmation email has always had.
                         Full width: a meeting URL is long and wraps with break-all. --}}
                    @if ($event->event_url)
                        <div class="sm:col-span-2">
                            <dt class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.online') }}</dt>
                            {{-- Linked only when it is a web link (eventUrlHref()); free-text join
                                 instructions are shown to the guest as they were written. --}}
                            @if ($joinHref = $event->eventUrlHref())
                                <dd><a href="{{ $joinHref }}" class="break-all hover:underline" style="color: var(--es-accent-readable)">{{ $event->event_url }}</a></dd>
                            @else
                                <dd class="break-all">{{ $event->event_url }}</dd>
                            @endif
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
    @if ($state === 'confirming')
        @include('ticket.partials.confirming-script')
    @endif
</x-app-guest-layout>
