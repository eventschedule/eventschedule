<x-app-guest-layout :role="$role" :noIndex="true" :page-title="__('messages.appointments')">
    @php
        $manageParams = ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'secret' => $sale->secret];

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

    {{-- Laid on the stage (partials/guest-stage-styles): what state the booking is in, large, the
         booking's facts in one row, then what can be done with it. Every colour is a stage token or
         the accent the guest layout checked against the stage, light and dark. --}}
    <style {!! nonce_attr() !!}>
        .gk-p-manage { max-width: 52rem; display: flex; flex-direction: column; gap: 1.75rem; }
        .gk-p-manage .gk-title { font-size: clamp(2.25rem, 1.5rem + 2.6vw, 3.5rem); }
        .gk-p-manage-head { display: flex; flex-direction: column; gap: .875rem; }
        .gk-p-manage-say { margin: 0; max-width: 62ch; font-size: 1.125rem; line-height: 1.6; color: var(--gk-ink-2); }
        .gk-p-manage .gk-fact-main { overflow-wrap: anywhere; }
        {{-- The label is a real dt here (the list is a dl), so the cell draws none of its own. --}}
        .gk-p-manage .gk-fact-cell::before { content: none; }
        .gk-p-dt { font-size: .8125rem; font-weight: 500; color: var(--gk-ink-3); }
        .gk-p-manage .gk-fact-main a { color: var(--es-accent-readable); text-decoration: underline; text-underline-offset: 3px; }
        .gk-p-manage-acts { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem; }
        .gk-p-manage .gk-cta-main { background: var(--es-accent); color: var(--es-accent-text); border: 1px solid var(--es-accent-edge, transparent); cursor: pointer; }
        .gk-p-manage .gk-cta-alt { text-decoration: none; cursor: pointer; }
        .gk-p-manage-foot { display: flex; flex-direction: column; gap: 1rem; padding-top: 1.5rem; border-top: 1px solid var(--gk-line); }
        .gk-p-manage-cancel { padding: 0; border: 0; background: none; color: var(--gk-bad); font: inherit; font-size: .9375rem; font-weight: 600; cursor: pointer; }
        .gk-p-manage-cancel:hover { text-decoration: underline; text-underline-offset: 3px; }
        .gk-p-manage-again { align-self: flex-start; font-size: 1rem; font-weight: 650; color: var(--es-accent-readable); text-decoration: none; }
        .gk-p-manage-again:hover { text-decoration: underline; text-underline-offset: 3px; }
        .gk-p-manage-hint { margin: 0; font-size: .875rem; color: var(--gk-ink-3); }
        {{-- The Add to calendar menu (x-appointment-add-to-calendar). --}}
        .gk-p-cal { position: relative; display: inline-block; }
        .gk-p-cal > summary { list-style: none; }
        .gk-p-cal > summary::-webkit-details-marker { display: none; }
        .gk-p-cal-chev { width: 1.125rem; height: 1.125rem; transition: transform var(--gk-swap); }
        .gk-p-cal[open] .gk-p-cal-chev { transform: rotate(180deg); }
        .gk-p-cal-menu { position: absolute; inset-inline-start: 0; z-index: 20; width: 15rem; margin-top: .5rem; overflow: hidden; border: 1px solid var(--gk-line); border-radius: 1rem; background: var(--gk-stage-raised); box-shadow: 0 1.25rem 2.5rem -1rem rgb(0 0 0 / .4); }
        .gk-p-cal-item { display: flex; align-items: center; gap: .75rem; min-height: 3rem; padding: 0 1rem; color: var(--gk-ink); font-size: .9375rem; text-decoration: none; transition: background-color var(--gk-swap); }
        .gk-p-cal-item:hover { background: var(--gk-stage-well); }
        .gk-p-cal-item svg { flex: none; width: 1.25rem; height: 1.25rem; color: var(--gk-ink-3); }
        #appt-manage a:focus-visible, #appt-manage button:focus-visible, #appt-manage summary:focus-visible { outline: 2px solid var(--es-accent-readable); outline-offset: 2px; }
    </style>

    <div id="appt-manage" class="gk-stage gk-p-manage">
        @if (session('message'))
            <div class="gk-note gk-note-ok" role="status"><div class="gk-note-body">{{ session('message') }}</div></div>
        @endif
        @if (session('error'))
            <div class="gk-note gk-note-bad" role="alert"><div class="gk-note-body">{{ session('error') }}</div></div>
        @endif

        {{-- Landed here straight after a move. Both times are stated in full rather than "check your
             email", because appointmentCanSend() can silently suppress the mail entirely. --}}
        @if (request()->boolean('moved'))
            <div class="gk-note {{ $state === 'pending' ? 'gk-note-warn' : 'gk-note-ok' }}">
                <svg class="gk-note-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="gk-note-body">
                    @if ($state === 'pending')
                        <p>{{ __('messages.appointments_rescheduled_pending_message', ['time' => $shown['time'], 'schedule' => $role?->name ?? '']) }}</p>
                    @else
                        <p>{{ __('messages.appointments_rescheduled_message') }}</p>
                        <p style="margin-top: .25rem; font-size: .8125rem">{{ __('messages.update_your_calendar_note') }}</p>
                    @endif
                </div>
            </div>
        @endif

        {{-- Only when the mail really leaves: on hosted that needs the schedule's own mail settings
             (EmailService::appointmentCanSend()), and the Appointments tab warns the owner when not. --}}
        @if (request()->boolean('new') && in_array($state, ['confirmed', 'pending']) && app(\App\Services\EmailService::class)->appointmentGuestMailGoesOut($sale))
            <div class="gk-note gk-note-ok">
                <svg class="gk-note-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="gk-note-body">{{ __('messages.appointments_email_on_the_way', ['email' => $sale->email]) }}</p>
            </div>
        @endif

        <header class="gk-p-manage-head">
            <p class="gk-dateline" dir="auto">{{ $role?->name }}</p>
            @switch($state)
                @case('pending')
                    <h1 tabindex="-1" class="gk-title">{{ __('messages.appointments_request_sent') }}</h1>
                    <p class="gk-p-manage-say">{{ __('messages.appointments_pending_note', ['schedule' => $role?->name ?? '']) }}</p>
                    @break
                @case('awaiting_payment')
                    {{-- Back from the payment page without paying (PaymentGatewayDriver::handleCancel(),
                         and Stripe's cancel_url). Presentation only, like ?new=1, and only in this
                         state, so reopening the address after paying says nothing. Same words as the
                         ticket page's (event/partials/payment-cancelled). --}}
                    @if (request()->query('payment') === 'cancelled')
                        <div role="status" class="gk-note">
                            <svg class="gk-note-icon" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                            </svg>
                            <span class="gk-note-body">{{ __('messages.payment_cancelled_not_charged') }}</span>
                        </div>
                    @endif
                    <h1 class="gk-title">{{ __('messages.appointments_awaiting_payment') }}</h1>
                    @if ($heldUntil)
                        <p class="gk-p-manage-say">{{ __('messages.appointments_held_until', ['time' => $heldUntil]) }}</p>
                    @endif
                    <form method="POST" action="{{ route('appointments.pay', $manageParams) }}" class="gk-p-manage-acts">
                        @csrf
                        <button type="submit" class="gk-cta-main">{{ __('messages.appointments_complete_payment') }}</button>
                    </form>
                    @break
                @case('in_review')
                    {{-- The provider has the payment and has not confirmed it: PayPal is reviewing it,
                         or the guest came back from paying and the confirmation is later than the
                         confirming window (an Instant EFT can clear hours later). No pay button, which
                         would start a second payment, and no Cancel, which would leave the money with
                         no booking. No polling either, so nothing here says the page updates itself. --}}
                    <h1 class="gk-title">{{ __('messages.ticket_confirming_payment') }}</h1>
                    <p class="gk-p-manage-say">{{ __('messages.appointments_payment_in_review', ['schedule' => $role?->name ?? '']) }}</p>
                    @break
                @case('confirming')
                    {{-- No pay button while the provider's confirmation is on its way: the guest has
                         just paid. ticket/partials/confirming-script asks ticket.status (any sale, by
                         event and secret) and reloads once the answer changes or the window closes. --}}
                    <div data-confirming="{{ \App\Services\Payments\PaymentGatewayDriver::landedSecondsLeft($sale) }}"
                         data-status-url="{{ route('ticket.status', $manageParams, false) }}" class="gk-p-manage-head">
                        <h1 class="gk-title">{{ __('messages.ticket_confirming_payment') }}</h1>
                        <p class="gk-p-manage-say">{{ __('messages.ticket_confirming_payment_hint') }}</p>
                    </div>
                    @break
                @case('cancelled')
                    {{-- A hold that lapsed unpaid is not a booking somebody cancelled: read as one, the
                         guest says "it was cancelled" and the owner thinks they did it. --}}
                    <h1 class="gk-title">{{ $sale->status === 'expired' ? __('messages.this_reservation_has_expired') : __('messages.appointments_cancelled') }}</h1>
                    @break
                @case('passed')
                    <h1 class="gk-title">{{ __('messages.appointments_passed') }}</h1>
                    @break
                @default
                    <h1 tabindex="-1" class="gk-title">{{ __('messages.appointments_youre_booked') }}</h1>
            @endswitch
        </header>

        {{-- The booking's facts in one row: what, when, where. --}}
        <dl class="gk-factrow" style="margin: 0">
            <div class="gk-fact-cell">
                <dt class="gk-p-dt">{{ __('messages.type') }}</dt>
                <dd class="gk-fact-main" style="margin: 0" dir="auto">{{ $type?->name ?? $event->name }}</dd>
                {{-- The team member the booking was made with, by name only. --}}
                @if ($hostName = $event->appointmentHostName())
                    <dd class="gk-fact-sub" style="margin: 0" dir="auto" v-pre>{{ __('messages.appointments_with_host', ['name' => $hostName]) }}</dd>
                @endif
            </div>
            <div class="gk-fact-cell">
                <dt class="gk-p-dt">{{ __('messages.date') }}</dt>
                <dd class="gk-fact-main" style="margin: 0">{{ $shown['date'] }}</dd>
            </div>
            <div class="gk-fact-cell">
                <dt class="gk-p-dt">{{ __('messages.time') }}</dt>
                {{-- Left to right inside a right-to-left page: "9:00 AM - 9:30 AM" drew as "AM - 9:30 AM 9:00". --}}
                <dd class="gk-fact-main" style="margin: 0"><bdi dir="ltr">{{ $shown['time'] }}</bdi></dd>
                <dd class="gk-fact-sub" style="margin: 0"><bdi dir="ltr">{{ $shown['tz'] }}</bdi></dd>
                @if ($inSchedule)
                    <dd class="gk-fact-sub" style="margin: 0">{{ __('messages.appointments_schedule_in') }} <bdi dir="ltr">{{ $inSchedule['tz'] }} ({{ $inSchedule['time'] }})</bdi></dd>
                @endif
            </div>
            {{-- Labelled, and covering the phone case the confirmation email has always had. A
                 meeting URL is long, so the cell takes the row's full width. --}}
            @if ($event->event_url)
                <div class="gk-fact-cell" style="grid-column: 1 / -1; border-top: 1px solid var(--gk-line)">
                    <dt class="gk-p-dt">{{ __('messages.online') }}</dt>
                    {{-- Linked only when it is a web link (eventUrlHref()); free-text join
                         instructions are shown to the guest as they were written. --}}
                    @if ($joinHref = $event->eventUrlHref())
                        <dd class="gk-fact-main break-all" style="margin: 0"><a href="{{ $joinHref }}">{{ $event->event_url }}</a></dd>
                    @else
                        <dd class="gk-fact-main break-all" style="margin: 0">{{ $event->event_url }}</dd>
                    @endif
                </div>
            @elseif ($type && $type->location_type === 'in_person' && $type->location_address)
                <div class="gk-fact-cell">
                    <dt class="gk-p-dt">{{ __('messages.location') }}</dt>
                    <dd class="gk-fact-main" style="margin: 0" dir="auto">{{ $type->location_address }}</dd>
                </div>
            @elseif ($type && $type->location_type === 'phone' && ($type->location_phone || $sale->phone))
                <div class="gk-fact-cell">
                    <dt class="gk-p-dt">{{ __('messages.phone') }}</dt>
                    <dd class="gk-fact-main" style="margin: 0"><bdi dir="ltr">{{ $type->location_phone ?: $sale->phone }}</bdi></dd>
                </div>
            @endif
        </dl>

        {{-- One action row. Add to calendar is only offered for a confirmed booking: the pending
             state says outright that nothing is booked yet, so a calendar entry there would be
             misleading. Reschedule sits beside it; Cancel stays a quiet red link at the foot,
             destructive-last, so it cannot be mis-tapped. --}}
        @if ($state === 'confirmed' || $canReschedule)
            <div class="gk-p-manage-acts">
                @if ($state === 'confirmed')
                    <x-appointment-add-to-calendar :event="$event" :sale="$sale" :role="$role" :primary="request()->boolean('new')" />
                @endif
                @if ($canReschedule)
                    <a href="{{ $rescheduleUrl }}" class="gk-cta-alt">
                        {{ $state === 'pending' ? __('messages.appointments_change_time') : __('messages.appointments_reschedule') }}
                    </a>
                @endif
            </div>
        @endif

        @if ($canReschedule)
            {{-- Said BEFORE they commit: a confirmed booking on an approval type is about to be
                 released the moment they pick a new time. --}}
            @if ($state === 'confirmed' && $type?->requires_approval)
                <div class="gk-note gk-note-warn">
                    <svg class="gk-note-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.5 0L3.16 16.25A2 2 0 005 19z" /></svg>
                    <p class="gk-note-body">{{ __('messages.appointments_reschedule_approval_warning', ['schedule' => $role?->name ?? '']) }}</p>
                </div>
            @endif
        @elseif ($state === 'awaiting_payment')
            {{-- An explanation with a real alternative, not a disabled button that reads as broken. --}}
            <div class="gk-note gk-note-warn">
                <svg class="gk-note-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.5 0L3.16 16.25A2 2 0 005 19z" /></svg>
                <p class="gk-note-body">{{ __('messages.appointments_reschedule_blocked_payment') }}</p>
            </div>
        @endif

        <div class="gk-p-manage-foot">
            {{-- 'passed' used to be a dead end: heading, details, nothing to do. A guest returning to
                 an old link almost always wants to book again. --}}
            @if (in_array($state, ['cancelled', 'passed']))
                <a href="{{ route('appointments.book', ['subdomain' => $role->subdomain]) }}" class="gk-p-manage-again">{{ __('messages.appointments_book_again') }}</a>
            @endif

            <p class="gk-p-manage-hint">{{ __('messages.appointments_manage_link_hint') }}</p>

            @if (in_array($state, ['confirmed', 'pending', 'awaiting_payment']))
                <form method="POST" action="{{ route('appointments.manage_cancel', $manageParams) }}">
                    @csrf
                    <button type="submit" class="gk-p-manage-cancel">{{ __('messages.appointments_cancel_booking') }}</button>
                </form>
            @endif
        </div>
    </div>
    @if ($state === 'confirming')
        @include('ticket.partials.confirming-script')
    @endif
</x-app-guest-layout>
