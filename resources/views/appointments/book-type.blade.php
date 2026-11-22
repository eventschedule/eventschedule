@php
    // Reschedule mode reuses this whole view. Its URL carries the booking secret, so unlike the public
    // /book page it must never be indexed.
    $mode = $mode ?? 'book';
    $isReschedule = $mode === 'reschedule';
    $sale = $sale ?? null;
    $event = $event ?? null;
    $ownerMode = $ownerMode ?? false;
    // Set by AppointmentController::showBookType() for a real, published slug that is simply not
    // taking bookings right now - a priced type on a schedule that may not charge, or one whose
    // payment method has gone. Rendered instead of the picker, never indexed.
    $unavailable = $unavailable ?? false;
@endphp
<x-app-guest-layout :role="$role" :noIndex="$isReschedule || $unavailable" :page-title="__('messages.appointments')">
@if ($unavailable)
    {{-- Says nothing about plans or upgrades: the guest is the schedule owner's customer, not ours.
         A "not taking bookings" register, not an error one. --}}
    <div class="gk-stage gk-p-closed">
        <style {!! nonce_attr() !!}>
            .gk-p-closed { max-width: 44rem; display: flex; flex-direction: column; gap: 1rem; }
            .gk-p-closed .gk-title { font-size: clamp(2rem, 1.5rem + 2vw, 3rem); }
            .gk-p-closed-say { margin: .5rem 0 0; font-size: 1.125rem; line-height: 1.6; color: var(--gk-ink); }
            .gk-p-closed .gk-cta-alt { align-self: flex-start; margin-top: .75rem; text-decoration: none; }
        </style>
        <h1 class="gk-title" dir="auto">{{ $type->name }}</h1>
        @if ($type->description)
            <p class="gk-story" dir="auto">{{ $type->description }}</p>
        @endif
        <p class="gk-p-closed-say">{{ __('messages.appointments_not_available') }}</p>
        <a href="{{ $role->getGuestUrl() }}" class="gk-cta-alt" dir="auto">{{ $role->name }}</a>
    </div>
@else
    @php
        // The accent and its label come from the guest layout (partials/guest-theme), which checks
        // them against the stage this page is laid on, light and dark. Still passed to the app.
        $accent = $role->accent_color ?? '#4E81FA';

        // What the guest is told about WHERE the appointment happens. Deliberately not the join link
        // or the phone number: /book/{typeSlug} is public and indexable, so putting a meeting URL or
        // the owner's number here hands both to every crawler. AppointmentService attaches the real
        // location to the event AFTER booking, which is when it should be disclosed.
        $locationLabel = match ($type->location_type) {
            'online' => __('messages.online'),
            'phone' => __('messages.appointments_phone_call'),
            default => __('messages.appointments_in_person'),
        };
        $locationDetail = match ($type->location_type) {
            'in_person' => (string) $type->location_address,
            'online' => $type->location_url ? (string) parse_url($type->location_url, PHP_URL_HOST) : '',
            default => '',
        };
        $locationNote = match (true) {
            $type->location_type === 'online' => __('messages.appointments_link_after_booking'),
            $type->location_type === 'phone' && ! $type->location_phone => __('messages.appointments_we_will_call'),
            default => '',
        };

        // Calendar bounds, mirroring availableSlots()'s own $lastDay math so client and server agree.
        $typeTz = $type->timezone();
        $minDate = \Carbon\Carbon::now($typeTz)->format('Y-m-d');
        // The owner path relaxes the booking window (see AppointmentService::availableSlots), so the
        // calendar must not cap them either.
        // Mirrors availableSlots()'s own $lastDay, including the owner-mode bound - the calendar must not
        // let an owner page into months the engine will never return slots for.
        $maxDate = $ownerMode
            ? \Carbon\Carbon::now($typeTz)->startOfDay()->addDays(max(365, (int) $type->max_advance_days))->format('Y-m-d')
            : \Carbon\Carbon::now($typeTz)->startOfDay()->addDays((int) $type->max_advance_days)->format('Y-m-d');

        // The time the booking currently holds, so the guest can see what they are moving from and the
        // POST can prove the page was not stale.
        // parseUtcInstant tolerates a legacy date-only starts_at; a bare createFromFormat 500s the
        // reschedule page for a row the manage page renders happily.
        $currentSlotUtc = $isReschedule
            ? \App\Utils\AppointmentTimeUtils::parseUtcInstant($event?->starts_at)?->format('Y-m-d\TH:i:s\Z')
            : null;
        // NOT server-rendered from $sale->guestTimezone(): the slot grid renders live in whatever zone
        // the visible timezone control holds (browser-detected on mount), so a server-rendered "moving
        // from" line puts two different labels for the same instant on one screen the moment the two
        // disagree - a guest who has travelled, or simply used the timezone picker. The rail and the
        // review step derive it from currentSlotUtc + tz instead, so they always agree with the grid.
        // A guest holding a CONFIRMED booking on an approval type is about to release it. That has to be
        // said before they commit, not after.
        $approvalWarning = $isReschedule && ! $ownerMode && $type->requires_approval && $event && ! $event->isAwaitingCreatorApproval();

        // Path-relative URLs: absolute subdomain URLs inside json_encode'd props escape their
        // slashes, so the ResolveCustomDomain HTML rewrite misses them and custom-domain visitors
        // would fetch/POST cross-origin (CORS-blocked). Relative paths stay same-origin everywhere.
        $props = [
            'mode' => $mode,
            'ownerMode' => $ownerMode,
            // In reschedule mode these point at the secret-link endpoints, never at the public booking
            // ones - the public slots route is domain-scoped on hosted and would 404 from the apex.
            'slotsUrl' => $isReschedule
                ? $rescheduleSlotsUrl
                : route('appointments.slots', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug], false),
            'bookUrl' => $isReschedule
                ? $rescheduleUrl
                : route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug], false),
            'backUrl' => $isReschedule
                ? $backUrl
                : route('appointments.book', ['subdomain' => $role->subdomain], false),
            'currentSlotUtc' => $currentSlotUtc,
            'approvalWarning' => $approvalWarning,
            // Owner mode only: whose booking is being moved. Guest-supplied text, so it travels as a
            // prop and renders through Vue's own interpolation - never Blade {{ }} inside the mount.
            'guestName' => $ownerMode ? (string) ($sale->name ?? '') : '',
            'guestEmail' => $ownerMode ? (string) ($sale->email ?? '') : '',
            'alreadyPaid' => $isReschedule && $sale && $sale->status === 'paid' && (float) $sale->payment_amount > 0,
            // No challenge when the requester already proved possession of the secret - it is pure
            // friction, and it keeps the cross-origin Turnstile script off a secret-bearing page.
            'turnstileEnabled' => ! $isReschedule && \App\Utils\TurnstileUtils::isActiveForRequest(),
            'turnstileSiteKey' => ! $isReschedule && \App\Utils\TurnstileUtils::isActiveForRequest() ? \App\Utils\TurnstileUtils::getSiteKey() : null,
            'csrf' => csrf_token(),
            // The language this page is read in, for the guest's booking mails (Sale::languageFromRequest()).
            'pageLang' => $role->displayLanguageCode(),
            'initial' => $initialSlots,
            'scheduleTz' => $initialSlots['schedule_timezone'] ?? config('app.timezone'),
            'scheduleName' => $role->name,
            // "with Sam": the team member the booking is made with, by name only. A booking being
            // moved keeps the host it was made with, so its own stamp wins over the type's.
            'hostLine' => ($hostName = ($isReschedule && $event) ? $event->appointmentHostName() : $type->hostName())
                ? __('messages.appointments_with_host', ['name' => $hostName])
                : '',
            'typeName' => $type->name,
            'typeDescription' => $type->description,
            'duration' => $type->duration_minutes,
            // MoneyUtils, not raw concatenation: "JPY 5000.00" should read "¥5,000".
            'priceLabel' => $type->isFree() ? __('messages.free') : \App\Utils\MoneyUtils::format((float) $type->price, $type->currency_code),
            'isFree' => $type->isFree(),
            // Whether Confirm leaves this page for a payment page, which the button then says while it
            // waits (a PayPal order is created on the server first). Cash confirms here.
            'paysOnline' => ! $type->isFree() && \App\Models\AppointmentType::paysOnline($type->payment_method),
            'requiresApproval' => (bool) $type->requires_approval,
            'askPhone' => (bool) $type->ask_phone,
            'requirePhone' => (bool) $type->require_phone,
            'use24' => get_use_24_hour_time($role) ? true : false,
            'firstDay' => (int) ($role->first_day_of_week ?? 0),
            'accent' => $accent,
            'authName' => auth()->user()->name ?? '',
            'authEmail' => auth()->user()->email ?? '',
            'locationType' => $type->location_type,
            'locationLabel' => $locationLabel,
            'locationDetail' => $locationDetail,
            'locationNote' => $locationNote,
            'minNoticeHours' => (int) $type->min_notice_hours,
            'minDate' => $minDate,
            'maxDate' => $maxDate,
            // The `t` strings are server-rendered in this locale; Intl must use it too, or a Hebrew
            // page ends up with English weekday and month names.
            'locale' => app()->getLocale(),
            't' => [
                'pickDate' => __('messages.appointments_pick_date'),
                'yourDetails' => __('messages.appointments_your_details'),
                'confirmBooking' => __('messages.appointments_confirm_booking'),
                'confirmAndPay' => __('messages.appointments_confirm_and_pay', ['price' => $type->isFree() ? '' : \App\Utils\MoneyUtils::format((float) $type->price, $type->currency_code)]),
                'requestThisTime' => __('messages.appointments_request_this_time'),
                'name' => __('messages.name'),
                'email' => __('messages.email'),
                'phone' => __('messages.phone'),
                'notes' => __('messages.appointments_notes_placeholder'),
                'timesShownIn' => __('messages.appointments_times_shown_in'),
                'scheduleIn' => __('messages.appointments_schedule_in'),
                'noTimes' => __('messages.appointments_no_times'),
                'sessionExpired' => __('messages.appointments_session_expired'),
                'nextAvailable' => __('messages.appointments_next_available'),
                'morning' => __('messages.appointments_morning'),
                'afternoon' => __('messages.appointments_afternoon'),
                'evening' => __('messages.appointments_evening'),
                'slotTaken' => __('messages.appointments_slot_taken'),
                'back' => __('messages.back'),
                'requiresConfirmation' => __('messages.appointments_requires_confirmation'),
                'minutes' => __('messages.minutes'),
                'duration' => __('messages.appointments_duration'),
                'price' => __('messages.price'),
                'location' => __('messages.location'),
                'next' => __('messages.appointments_next_step'),
                // Real labels for the details form: the fields used to carry only placeholders,
                // which vanish on input and are not a reliable accessible name.
                'notesLabel' => __('messages.notes'),
                'optional' => __('messages.optional'),
                'pickTime' => __('messages.appointments_pick_time'),
                'change' => __('messages.appointments_change'),
                'noTimesThisMonth' => __('messages.appointments_no_times_this_month'),
                'loadError' => __('messages.appointments_load_error'),
                'rescheduleFailed' => __('messages.appointments_reschedule_failed'),
                'currentSlotTag' => __('messages.appointments_current_slot_tag'),
                'retry' => __('messages.retry'),
                'loading' => __('messages.loading'),
                'redirectingToPayment' => __('messages.redirecting_to_payment'),
                'prevMonth' => __('messages.previous_month'),
                'nextMonth' => __('messages.next_month'),
                'timezone' => __('messages.timezone'),
                'search' => __('messages.search'),
                'showAllTimezones' => __('messages.appointments_show_all_timezones'),
                'stepOf' => __('messages.appointments_step_of'),
                'reschedule' => __('messages.appointments_reschedule'),
                'notifyMessageLabel' => __('messages.notify_message_label'),
                'notifyMessagePlaceholder' => __('messages.notify_message_placeholder'),
                'dontNotify' => __('messages.dont_notify'),
                'notifyAndMove' => __('messages.notify_attendees_button'),
                'rescheduleConfirm' => __('messages.appointments_reschedule_confirm'),
                'rescheduleKeep' => __('messages.appointments_reschedule_keep'),
                'rescheduleCurrent' => __('messages.appointments_reschedule_current'),
                'rescheduleNoCharge' => __('messages.appointments_reschedule_no_charge'),
                'rescheduleNoneLeft' => __('messages.appointments_reschedule_none_left'),
                'approvalWarning' => __('messages.appointments_reschedule_approval_warning', ['schedule' => $role->name]),
                'ownerNote' => __('messages.appointments_reschedule_owner_note'),
                'whatsChanged' => __('messages.event_changed_whats_changed'),
                'previously' => __('messages.event_changed_previously'),
                'nowLabel' => __('messages.event_changed_now'),
                'minNoticeHint' => $type->min_notice_hours > 0
                    ? __('messages.appointments_min_notice_hint', ['hours' => (int) $type->min_notice_hours])
                    : '',
            ],
        ];
    @endphp

    {{-- Kept outside #booking-app: anything inside the mount is compiled as a Vue template.
         The page is laid on the stage (partials/guest-stage-styles): the type's name large at the
         start, its facts in one row, and the picker as the one card on the sheet, a form to fill
         in. Every colour is a stage token or the accent GuestTheme checked against the stage, the
         raised card and the well, light and dark, so nothing here mixes one of its own. --}}
    <style {!! nonce_attr() !!}>
        .gk-p-book { display: grid; gap: 2rem; align-items: start; }
        @media (min-width: 64rem) {
            .gk-p-book { grid-template-columns: minmax(0, 22rem) minmax(0, 1fr); column-gap: clamp(2rem, 4vw, 3.5rem); }
            .gk-p-book-head { position: sticky; top: 1.5rem; }
        }
        .gk-p-book-head { display: flex; flex-direction: column; gap: 1rem; min-width: 0; }
        .gk-p-book .gk-title { font-size: clamp(2.25rem, 1.6rem + 2vw, 3.25rem); }
        .gk-p-back { display: inline-flex; align-items: center; gap: .375rem; align-self: flex-start; font-size: .9375rem; font-weight: 500; color: var(--gk-ink-3); text-decoration: none; }
        .gk-p-back:hover { color: var(--gk-ink); text-decoration: underline; text-underline-offset: 3px; }
        [dir="rtl"] .gk-p-back svg { transform: scaleX(-1); }
        .gk-p-book .gk-factrow { grid-template-columns: repeat(auto-fit, minmax(min(100%, 9.5rem), 1fr)); }
        .gk-p-book .gk-fact-main { font-size: 1.0625rem; }
        .gk-p-small { margin: 0; font-size: .875rem; line-height: 1.5; color: var(--gk-ink-3); }
        .gk-p-desc { margin: 0; font-size: 1.0625rem; line-height: 1.65; color: var(--gk-ink-2); white-space: pre-line; overflow-wrap: anywhere; }
        .gk-p-now { padding: 1rem 1.125rem; border-radius: 1rem; background: var(--gk-stage-well); }
        .gk-p-now-label { margin: 0 0 .25rem; font-size: .8125rem; font-weight: 500; color: var(--gk-ink-3); }
        .gk-p-now-main { margin: 0; font-size: 1.0625rem; font-weight: 650; color: var(--gk-ink); }
        .gk-p-now-sub { margin: 0; font-size: .9375rem; color: var(--gk-ink-2); }
        .gk-p-now-who { margin-bottom: .625rem; padding-bottom: .625rem; border-bottom: 1px solid var(--gk-line); }

        {{-- The picker: one card raised on the sheet. --}}
        .gk-p-picker { min-width: 0; }
        .gk-p-picker-top { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: .75rem; margin-bottom: 1.25rem; }
        .gk-p-step { margin: 0; font-size: .875rem; font-weight: 500; color: var(--gk-ink-3); }
        .gk-p-tz { position: relative; }
        .gk-p-tz-btn { display: inline-flex; align-items: center; gap: .5rem; min-height: 2.5rem; padding: 0 .875rem; border: 1px solid var(--gk-line); border-radius: .75rem; background: transparent; color: var(--gk-ink-2); font: inherit; font-size: .875rem; cursor: pointer; transition: background-color var(--gk-swap); }
        .gk-p-tz-btn:hover { background: var(--gk-stage-well); }
        .gk-p-tz-btn svg { flex: none; width: 1rem; height: 1rem; }
        .gk-p-tz-panel { position: absolute; inset-inline-end: 0; z-index: 10; width: 18rem; max-width: 80vw; margin-top: .5rem; padding: .75rem; border: 1px solid var(--gk-line); border-radius: 1rem; background: var(--gk-stage-raised); box-shadow: 0 1.25rem 2.5rem -1rem rgb(0 0 0 / .35); }
        .gk-p-cols { display: grid; gap: 2rem; }
        @media (min-width: 40rem) { .gk-p-cols { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); column-gap: 2rem; } }
        .gk-p-month { display: flex; align-items: center; justify-content: space-between; margin-bottom: .75rem; }
        .gk-p-month-name { font-size: 1.1875rem; font-weight: 700; color: var(--gk-ink); }
        .gk-p-month-nav { display: flex; gap: .375rem; }
        .gk-p-nav { display: grid; place-items: center; width: 2.5rem; height: 2.5rem; border: 1px solid var(--gk-line); border-radius: 999px; background: transparent; color: var(--gk-ink); font-size: 1.25rem; line-height: 1; cursor: pointer; transition: background-color var(--gk-swap); }
        .gk-p-nav:hover:not(:disabled) { background: var(--gk-stage-well); }
        .gk-p-nav:disabled { opacity: .35; cursor: not-allowed; }
        [dir="rtl"] .gk-p-nav svg { transform: scaleX(-1); }
        .gk-p-wk { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: .25rem; margin-bottom: .25rem; font-size: .75rem; font-weight: 600; text-align: center; color: var(--gk-ink-3); }
        .gk-p-days { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); gap: .25rem; }
        .gk-p-day { position: relative; display: flex; align-items: center; justify-content: center; aspect-ratio: 1; border: 0; border-radius: .75rem; background: none; color: var(--gk-ink-3); font: inherit; font-size: .9375rem; }
        .gk-p-day-on { color: var(--gk-ink); font-weight: 650; cursor: pointer; transition: background-color var(--gk-swap); }
        .gk-p-day-on:hover { background: var(--gk-stage-well); }
        .gk-p-day-off { opacity: .55; cursor: default; }
        #booking-app .es-day-selected { background: var(--es-accent); color: var(--es-accent-text); }
        {{-- A ring rather than a fill, so "today" cannot be confused with "selected". --}}
        #booking-app .es-today { box-shadow: inset 0 0 0 2px var(--es-accent-readable); }
        #booking-app .es-dot { position: absolute; bottom: .3125rem; width: .3125rem; height: .3125rem; border-radius: 999px; background: var(--es-accent-readable); }
        .gk-p-dayname { margin: 0 0 .875rem; font-size: 1.1875rem; font-weight: 700; color: var(--gk-ink); }
        .gk-p-part { margin: 0 0 .5rem; font-size: .8125rem; font-weight: 500; color: var(--gk-ink-3); }
        .gk-p-slots { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; margin-bottom: 1.25rem; }
        .gk-p-slot-row { display: flex; gap: .5rem; min-width: 0; }
        .gk-p-slot-row-armed { grid-column: 1 / -1; }
        #booking-app .es-slot, #booking-app .es-slot-armed { flex: 1 1 auto; min-width: 0; min-height: 3rem; padding: 0 .5rem; border: 1px solid; border-radius: .75rem; font: inherit; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background-color var(--gk-swap), color var(--gk-swap); }
        #booking-app .es-slot { border-color: color-mix(in srgb, var(--es-accent-readable) 45%, transparent); background: transparent; color: var(--es-accent-readable); }
        #booking-app .es-slot:hover { background: var(--gk-stage-well); }
        #booking-app .es-slot-armed { border-color: var(--es-accent-edge, transparent); background: var(--es-accent); color: var(--es-accent-text); }
        .gk-p-slot-tag { margin-inline-start: .25rem; font-size: .75rem; font-weight: 500; opacity: .8; }
        .gk-p-next { flex: none; min-width: 6.5rem; min-height: 3rem; }
        .gk-p-skel { border-radius: .5rem; background: var(--gk-stage-well); }
        .gk-p-link { padding: 0; border: 0; background: none; font: inherit; font-weight: 600; text-decoration: underline; text-underline-offset: 3px; cursor: pointer; }
        #booking-app .es-accent-text { color: var(--es-accent-readable); }
        #booking-app .es-accent-fill { background: var(--es-accent); color: var(--es-accent-text); border-color: var(--es-accent-edge, transparent); }

        {{-- The details step and the review step. --}}
        .gk-p-back-btn { display: inline-flex; align-items: center; gap: .375rem; margin-bottom: 1rem; padding: 0; border: 0; background: none; color: var(--gk-ink-3); font: inherit; font-size: .9375rem; cursor: pointer; }
        .gk-p-back-btn:hover { color: var(--gk-ink); text-decoration: underline; text-underline-offset: 3px; }
        [dir="rtl"] .gk-p-back-btn svg { transform: scaleX(-1); }
        .gk-p-h2 { margin: 0 0 1rem; font-size: 1.5rem; font-weight: 750; letter-spacing: -.01em; color: var(--gk-ink); }
        .gk-p-picked { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; max-width: 42rem; margin-bottom: 1.5rem; padding: 1rem 1.125rem; border-radius: 1rem; background: var(--gk-stage-well); }
        .gk-p-picked-main { font-size: 1.0625rem; font-weight: 650; color: var(--gk-ink); }
        .gk-p-picked-sub { font-size: .9375rem; color: var(--gk-ink-2); }
        .gk-p-was { font-size: .9375rem; color: var(--gk-ink-3); }
        .gk-p-form { display: grid; gap: 1rem; max-width: 42rem; }
        .gk-p-pair { display: grid; gap: 1rem; }
        @media (min-width: 40rem) { .gk-p-pair { grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); } }
        .gk-p-label { display: block; margin-bottom: .375rem; font-size: .875rem; font-weight: 500; color: var(--gk-ink-2); }
        .gk-p-opt { font-weight: 400; color: var(--gk-ink-3); }
        #booking-app .gk-p-input { display: block; box-sizing: border-box; width: 100%; min-height: 3.125rem; padding: .75rem .875rem; border: 1px solid var(--gk-line); border-radius: .75rem; background: var(--gk-stage); color: var(--gk-ink); font: inherit; font-size: 1rem; box-shadow: none; }
        #booking-app input.gk-p-input, #booking-app select.gk-p-input { height: 3.25rem; padding-block: 0; }
        #booking-app .gk-p-tz-panel input.gk-p-input, #booking-app .gk-p-tz-panel select.gk-p-input { height: 2.75rem; }
        #booking-app .gk-p-input::placeholder { color: var(--gk-ink-3); }
        #booking-app .gk-p-input:focus { border-color: var(--es-accent-readable); outline: none; box-shadow: 0 0 0 1px var(--es-accent-readable); }
        #booking-app .gk-p-input[aria-invalid="true"] { border-color: var(--gk-bad); }
        #booking-app .gk-p-tz-panel .gk-p-input { min-height: 2.75rem; padding: .5rem .75rem; font-size: .9375rem; }
        .gk-p-count { margin-top: .25rem; font-size: .8125rem; text-align: end; color: var(--gk-ink-3); }
        .gk-p-actions { display: flex; flex-wrap: wrap; align-items: center; gap: .75rem; max-width: 42rem; }
        .gk-p-actions .gk-btn-lg { flex: 1 1 14rem; }
        .gk-p-quiet { font-size: .9375rem; color: var(--gk-ink-2); }
        .gk-p-quiet:hover { color: var(--gk-ink); text-decoration: underline; text-underline-offset: 3px; }

        {{-- No global outline reset here, but the UA ring is unreliable against an accent fill. --}}
        #booking-app button:focus-visible,
        #booking-app a:focus-visible,
        #booking-app select:focus-visible,
        #booking-app input:focus-visible,
        #booking-app textarea:focus-visible {
            outline: 2px solid var(--es-accent-readable);
            outline-offset: 2px;
        }
    </style>

    <noscript>
        <div class="gk-stage">
            <div class="gk-note gk-note-warn">{{ __('messages.appointments_enable_js') }}</div>
        </div>
    </noscript>

    <div id="booking-app" data-vue-root data-props="{{ json_encode($props) }}" class="gk-stage gk-p-book">
        {{-- The type, large, and its facts. Below a laptop it sits above the picker; from one up,
             beside it, where it stays in view while the guest picks. --}}
        <div class="gk-p-book-head">
            <a :href="backUrl" class="gk-p-back">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                @{{ mode === 'reschedule' ? t.rescheduleKeep : t.back }}
            </a>
            <p class="gk-dateline"><span dir="auto">@{{ scheduleName }}</span><template v-if="hostLine"><span class="gk-dateline-dot" aria-hidden="true">&middot;</span><span dir="auto">@{{ hostLine }}</span></template></p>
            <h1 class="gk-title" dir="auto">@{{ typeName }}</h1>
            <p v-if="typeDescription" class="gk-p-desc" dir="auto">@{{ typeDescription }}</p>

            {{-- One row of facts. Where the appointment happens is part of it: the guest could not
                 see that at all before, so "is this a call or do I travel?" was only answered by
                 the email. --}}
            <div class="gk-factrow">
                <div class="gk-fact-cell" :data-label="t.duration">
                    <span class="gk-fact-main">@{{ duration }} @{{ t.minutes }}</span>
                </div>
                <div class="gk-fact-cell" :data-label="t.price">
                    <span class="gk-fact-main">@{{ priceLabel }}</span>
                    <span v-if="requiresApproval" class="gk-fact-sub">@{{ t.requiresConfirmation }}</span>
                </div>
                <div class="gk-fact-cell" :data-label="t.location">
                    <span class="gk-fact-main">@{{ locationLabel }}</span>
                    <span v-if="locationDetail" class="gk-fact-sub" dir="auto">@{{ locationDetail }}</span>
                    <span v-if="locationNote" class="gk-fact-sub">@{{ locationNote }}</span>
                </div>
            </div>
            <p v-if="t.minNoticeHint && mode !== 'reschedule'" class="gk-p-small">@{{ t.minNoticeHint }}</p>

            {{-- What they are moving, and from when. Without this the picker gives no clue which
                 booking is in play or what time it currently holds. --}}
            <div v-if="mode === 'reschedule' && currentSlotUtc" class="gk-p-now">
                <div v-if="guestName" class="gk-p-now-who">
                    <p class="gk-p-now-main" dir="auto">@{{ guestName }}</p>
                    <p class="gk-p-now-sub">@{{ guestEmail }}</p>
                </div>
                <p class="gk-p-now-label">@{{ t.rescheduleCurrent }}</p>
                <p class="gk-p-now-main">@{{ currentShownDate }}</p>
                <p class="gk-p-now-sub">@{{ currentShownTime }} (@{{ tz }})</p>
            </div>

            {{-- The highest-risk moment in the flow: a confirmed booking is about to be released. --}}
            <div v-if="approvalWarning" class="gk-note gk-note-warn">
                <svg class="gk-note-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.5 0L3.16 16.25A2 2 0 005 19z" /></svg>
                <p class="gk-note-body">@{{ t.approvalWarning }}</p>
            </div>

            <p v-if="mode === 'reschedule' && ownerMode" class="gk-p-small">@{{ t.ownerNote }}</p>
        </div>

        {{-- The picker, a form to fill in: the one card on the sheet. --}}
        <div class="gk-card gk-band gk-p-picker">
            {{-- Step: date + time --}}
            <div v-if="step === 'pick'">
                <div class="gk-p-picker-top">
                    <p class="gk-p-step" aria-current="step">@{{ stepLabel(1) }}</p>

                    {{-- Timezone. Next to the times it relabels: on a phone the type's facts stack
                         above the picker, so a control down there pushed the calendar most of a
                         screen further down. The full IANA list is ~430 unlabelled entries, so the
                         common zones come first and the rest sit behind a filter. --}}
                    <div class="gk-p-tz">
                        <button type="button" @click="toggleTz" aria-controls="tz-panel" :aria-expanded="tzOpen ? 'true' : 'false'" class="gk-p-tz-btn">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" /></svg>
                            <span aria-live="polite">@{{ t.timesShownIn }} @{{ tz }}</span>
                        </button>
                        <p v-if="tz !== scheduleTz" class="gk-p-small" style="margin-top: .25rem; text-align: end">@{{ t.scheduleIn }} @{{ scheduleTz }}</p>
                        <div v-if="tzOpen" id="tz-panel" class="gk-p-tz-panel">
                            <label class="sr-only" for="tz-select">@{{ t.timezone }}</label>
                            <select id="tz-select" v-model="tz" class="gk-p-input">
                                <option v-for="z in tzChoices" :key="z" :value="z">@{{ tzLabel(z) }}</option>
                            </select>
                            <div v-if="!tzShowAll" style="margin-top: .5rem">
                                <button type="button" @click="tzShowAll = true" class="gk-p-link es-accent-text">@{{ t.showAllTimezones }}</button>
                            </div>
                            <div v-else style="margin-top: .5rem">
                                <label class="sr-only" for="tz-filter">@{{ t.search }}</label>
                                <input id="tz-filter" v-model="tzFilter" type="search" :placeholder="t.search" class="gk-p-input">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="gk-p-cols">
                    {{-- Calendar --}}
                    <div>
                        <div class="gk-p-month">
                            <div class="gk-p-month-name" aria-live="polite">@{{ monthLabel }}</div>
                            <div class="gk-p-month-nav">
                                {{-- Bounded: paging into the past, or past the booking window, only ever
                                     produced an empty grid with no explanation. --}}
                                <button type="button" @click="changeMonth(-1)" :disabled="!canGoPrev" class="gk-p-nav">
                                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" /></svg><span class="sr-only">@{{ t.prevMonth }}</span>
                                </button>
                                <button type="button" @click="changeMonth(1)" :disabled="!canGoNext" class="gk-p-nav">
                                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg><span class="sr-only">@{{ t.nextMonth }}</span>
                                </button>
                            </div>
                        </div>
                        {{-- Redundant once every cell carries its full date as an accessible name. --}}
                        <div class="gk-p-wk" aria-hidden="true">
                            <div v-for="d in weekdayLabels" :key="d">@{{ d }}</div>
                        </div>
                        <div class="gk-p-days">
                            <template v-for="(cell, i) in flatCells" :key="i">
                                <button v-if="cell" type="button"
                                    :disabled="!hasSlots(cell.date)"
                                    @click="selectDate(cell.date)"
                                    :aria-label="dayLabel(cell.date)"
                                    :aria-pressed="selectedDate === cell.date ? 'true' : 'false'"
                                    :aria-current="cell.date === todayLocal ? 'date' : null"
                                    :class="['gk-p-day',
                                        selectedDate === cell.date ? 'es-day-selected' : '',
                                        cell.date === todayLocal && selectedDate !== cell.date ? 'es-today' : '',
                                        hasSlots(cell.date) ? 'gk-p-day-on' : 'gk-p-day-off']">
                                    @{{ cell.day }}
                                    <span v-if="hasSlots(cell.date) && selectedDate !== cell.date" class="es-dot" aria-hidden="true"></span>
                                </button>
                                <div v-else></div>
                            </template>
                        </div>

                        <div v-if="loading" style="margin-top: .875rem; display: grid; gap: .5rem" aria-busy="true">
                            <span class="sr-only" aria-live="polite">@{{ t.loading }}</span>
                            <div v-for="n in 3" :key="n" class="gk-p-skel animate-pulse" style="height: .75rem"></div>
                        </div>
                        <p v-if="!loading && !monthHasSlots && !nextAvailable" class="gk-p-small" style="margin-top: .875rem">@{{ anySlots ? t.noTimesThisMonth : t.noTimes }}</p>
                        {{-- In reschedule mode an empty calendar reads like the booking itself broke. --}}
                        <div v-if="!loading && !anySlots && !nextAvailable && mode === 'reschedule'" class="gk-note gk-note-warn" style="margin-top: .875rem">
                            <svg class="gk-note-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.5 0L3.16 16.25A2 2 0 005 19z" /></svg>
                            <div class="gk-note-body">
                                <p>@{{ t.rescheduleNoneLeft }}</p>
                                <a :href="backUrl" class="gk-link">@{{ t.rescheduleKeep }}</a>
                            </div>
                        </div>
                        <p v-if="!loading && !monthHasSlots && nextAvailable" style="margin-top: .875rem">
                            <button type="button" @click="jumpToNext" class="gk-p-link es-accent-text">@{{ t.nextAvailable }}: @{{ nextAvailable }}</button>
                        </p>
                    </div>

                    {{-- Times --}}
                    <div ref="times">
                        <div v-if="error" class="gk-note gk-note-warn" style="margin-bottom: .875rem" role="alert" aria-live="assertive">
                            <div class="gk-note-body">
                                @{{ error }}
                                <button v-if="canRetry" type="button" @click="retryMonth" class="gk-p-link" style="margin-inline-start: .25rem">@{{ t.retry }}</button>
                            </div>
                        </div>
                        <div v-if="selectedDate" role="group" :aria-label="selectedDateLabel">
                            <h2 class="sr-only">@{{ t.pickTime }}</h2>
                            <p class="gk-p-dayname">@{{ selectedDateLabel }}</p>
                            <div v-if="loading" style="display: grid; gap: .5rem" aria-busy="true">
                                <div v-for="n in 5" :key="n" class="gk-p-skel animate-pulse" style="height: 3rem"></div>
                            </div>
                            <template v-else v-for="group in ['morning','afternoon','evening']" :key="group">
                                <div v-if="slotGroups[group].length">
                                    <h3 v-if="showGroups" class="gk-p-part">@{{ t[group] }}</h3>
                                    {{-- Two columns. Single-file, a 16-slot day ran about 700px tall next
                                         to the calendar. The armed slot spans the row, keeping room for
                                         the inline Next button. --}}
                                    <div class="gk-p-slots">
                                        <div v-for="u in slotGroups[group]" :key="u" :class="['gk-p-slot-row', armed === u ? 'gk-p-slot-row-armed' : '']">
                                            {{-- The booking's own slot is offered back (the grid excludes its event so it
                                                 does not block itself), so it has to be LABELLED. Unmarked, it looked like
                                                 any other option, and picking it produced a review step reading
                                                 "Previously: 2:00 PM / Now: 2:00 PM" and then a no-op. --}}
                                            <button type="button" @click="armSlot(u)"
                                                :aria-pressed="armed === u ? 'true' : 'false'"
                                                :aria-expanded="armed === u ? 'true' : 'false'"
                                                :aria-current="u === currentSlotUtc ? 'true' : null"
                                                :class="armed === u ? 'es-slot-armed' : 'es-slot'">
                                                <bdi dir="ltr">@{{ localTime(u) }}</bdi>
                                                <span v-if="u === currentSlotUtc" class="gk-p-slot-tag">@{{ t.currentSlotTag }}</span>
                                            </button>
                                            {{-- Fixed width so arming a slot does not reflow the whole column. --}}
                                            <button v-if="armed === u" ref="nextButton" type="button" @click="confirmSlot(u)" class="gk-btn gk-btn-primary gk-p-next">@{{ t.next }}</button>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        <p v-else class="gk-p-small" style="font-size: 1rem">@{{ t.pickDate }}</p>
                    </div>
                </div>
            </div>

            {{-- Step: review (reschedule only). A move must never be one tap on a button labelled
                 "Next" - the guest gets to see both times and confirm. --}}
            <div v-else-if="step === 'review'">
                <p class="gk-p-step" style="margin-bottom: .75rem" aria-current="step">@{{ stepLabel(2) }}</p>
                <button type="button" @click="backToPick" class="gk-p-back-btn">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                    @{{ t.back }}
                </button>
                <h2 class="gk-p-h2">@{{ t.whatsChanged }}</h2>

                <div class="gk-p-picked">
                    <div>
                        {{-- Deliberately the short form, matching the "Now" line below it rather than
                             the rail's fuller one: the single tz label under them covers both. --}}
                        <div v-if="currentSlotUtc" class="gk-p-was">@{{ t.previously }}: <s>@{{ currentShownShort }}</s></div>
                        <div class="gk-p-picked-main">@{{ t.nowLabel }}: @{{ selectedSlotLabel }}, <bdi dir="ltr">@{{ localTime(selectedSlot) }}</bdi></div>
                        <div class="gk-p-picked-sub">@{{ tz }}</div>
                    </div>
                    <button type="button" @click="backToPick" class="gk-p-link es-accent-text" style="flex: none">@{{ t.change }}</button>
                </div>

                {{-- A paid guest must not be told they are about to pay again. --}}
                <p v-if="alreadyPaid" class="gk-p-small" style="margin-bottom: 1rem">@{{ t.rescheduleNoCharge }}</p>

                <div v-if="approvalWarning" class="gk-note gk-note-warn" style="max-width: 42rem; margin-bottom: 1rem">
                    <svg class="gk-note-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.5 0L3.16 16.25A2 2 0 005 19z" /></svg>
                    <p class="gk-note-body">@{{ t.approvalWarning }}</p>
                </div>

                <div v-if="error" class="gk-note gk-note-warn" style="max-width: 42rem; margin-bottom: 1rem" role="alert" aria-live="assertive"><div class="gk-note-body">@{{ error }}</div></div>

                {{-- Owner moves get the same choice the event editor already offers when a time
                     changes: an optional note to the guest, or move without emailing at all. Inline
                     on the review step rather than as a modal - this step IS the confirmation. --}}
                <div v-if="ownerMode" style="max-width: 42rem; margin-bottom: 1.25rem">
                    <label for="reschedule-note" class="gk-p-label">@{{ t.notifyMessageLabel }}</label>
                    <textarea id="reschedule-note" v-model="note" maxlength="280" rows="3" :placeholder="t.notifyMessagePlaceholder" class="gk-p-input"></textarea>
                    <div class="gk-p-count">@{{ note.length }}/280</div>
                </div>

                <div class="gk-p-actions">
                    <a :href="backUrl" class="gk-p-quiet">@{{ t.rescheduleKeep }}</a>
                    {{-- Forward action last. Its label never says "pay". --}}
                    <button v-if="ownerMode" type="button" @click="submit(false)" :disabled="submitting" class="gk-btn gk-btn-secondary gk-btn-lg">@{{ t.dontNotify }}</button>
                    <button type="button" @click="submit(true)" :disabled="submitting" :aria-busy="submitting ? 'true' : 'false'"
                        class="gk-btn gk-btn-primary gk-btn-lg es-accent-fill disabled:opacity-60">@{{ ownerMode ? t.notifyAndMove : rescheduleCtaLabel }}</button>
                </div>
                <span v-if="submitting" class="sr-only" aria-live="polite">@{{ t.loading }}</span>
            </div>

            {{-- Step: details --}}
            <div v-else-if="step === 'details'">
                <p class="gk-p-step" style="margin-bottom: .75rem" aria-current="step">@{{ stepLabel(2) }}</p>
                <button type="button" @click="backToPick" class="gk-p-back-btn">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
                    @{{ t.back }}
                </button>
                <h2 class="gk-p-h2">@{{ t.yourDetails }}</h2>
                <div class="gk-p-picked">
                    <div>
                        <div class="gk-p-picked-main">@{{ selectedSlotLabel }}, <bdi dir="ltr">@{{ localTime(selectedSlot) }}</bdi></div>
                        <div class="gk-p-picked-sub">@{{ tz }}</div>
                    </div>
                    {{-- The only way back to the times used to be the generic Back link. --}}
                    <button type="button" @click="backToPick" class="gk-p-link es-accent-text" style="flex: none">@{{ t.change }}</button>
                </div>
                <div v-if="error" class="gk-note gk-note-warn" style="max-width: 42rem; margin-bottom: 1rem" role="alert" aria-live="assertive"><div class="gk-note-body">@{{ error }}</div></div>
                <form @submit.prevent="submit" class="gk-p-form">
                    <fieldset style="display: grid; gap: 1rem; min-width: 0; margin: 0; padding: 0; border: 0">
                        <legend class="sr-only">@{{ t.yourDetails }}</legend>
                        <x-honeypot vmodel="website" />

                        <div class="gk-p-pair">
                            <div>
                                <label for="booking-name" class="gk-p-label">@{{ t.name }}</label>
                                <input id="booking-name" v-model="form.name" autocomplete="name" required
                                    :aria-invalid="fieldErrors.name ? 'true' : 'false'"
                                    :aria-describedby="fieldErrors.name ? 'booking-name-error' : null"
                                    class="gk-p-input">
                                <span v-if="fieldErrors.name" id="booking-name-error" role="alert" class="gk-err" style="display: block">@{{ fieldErrors.name }}</span>
                            </div>

                            <div>
                                <label for="booking-email" class="gk-p-label">@{{ t.email }}</label>
                                <input id="booking-email" v-model="form.email" type="email" inputmode="email" autocomplete="email" required
                                    :aria-invalid="fieldErrors.email ? 'true' : 'false'"
                                    :aria-describedby="fieldErrors.email ? 'booking-email-error' : null"
                                    class="gk-p-input">
                                <span v-if="fieldErrors.email" id="booking-email-error" role="alert" class="gk-err" style="display: block">@{{ fieldErrors.email }}</span>
                            </div>
                        </div>

                        <div v-if="askPhone">
                            <label for="booking-phone" class="gk-p-label">
                                @{{ t.phone }}<span v-if="!requirePhone" class="gk-p-opt"> (@{{ t.optional }})</span>
                            </label>
                            <input id="booking-phone" v-model="form.phone" type="tel" autocomplete="tel" :required="requirePhone" :aria-required="requirePhone ? 'true' : 'false'"
                                :aria-invalid="fieldErrors.phone ? 'true' : 'false'"
                                :aria-describedby="fieldErrors.phone ? 'booking-phone-error' : null"
                                class="gk-p-input">
                            <span v-if="fieldErrors.phone" id="booking-phone-error" role="alert" class="gk-err" style="display: block">@{{ fieldErrors.phone }}</span>
                        </div>

                        <div>
                            <label for="booking-notes" class="gk-p-label">
                                @{{ t.notesLabel }}<span class="gk-p-opt"> (@{{ t.optional }})</span>
                            </label>
                            <textarea id="booking-notes" v-model="form.notes" :placeholder="t.notes" rows="3" class="gk-p-input"></textarea>
                        </div>
                    </fieldset>
                    <div v-if="turnstileEnabled" id="turnstile-booking-widget"></div>
                    <button type="submit" :disabled="submitting" :aria-busy="submitting ? 'true' : 'false'" class="gk-btn gk-btn-primary gk-btn-lg gk-btn-block es-accent-fill disabled:opacity-60">@{{ ctaLabel }}</button>
                    <span v-if="submitting" class="sr-only" aria-live="polite">@{{ t.loading }}</span>
                </form>
            </div>
        </div>
    </div>

    @if (! $isReschedule && \App\Utils\TurnstileUtils::isActiveForRequest())
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer {!! nonce_attr() !!}></script>
    @endif
    <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
    <script {!! nonce_attr() !!}>
        (function () {
            if (typeof Vue === 'undefined') return;
            var el = document.getElementById('booking-app');
            var props = JSON.parse(el.dataset.props);
            var { createApp } = Vue;

            createApp({
                data() {
                    return Object.assign({}, props, {
                        tz: props.scheduleTz,
                        tzOpen: false,
                        tzShowAll: false,
                        tzFilter: '',
                        tzList: [],
                        month: '',
                        selectedDate: null,
                        armed: null,
                        selectedSlot: null,
                        step: 'pick',
                        allUtc: [],
                        fetched: {},
                        loading: false,
                        error: '',
                        canRetry: false,
                        nextAvailable: null,
                        fieldErrors: {},
                        submitting: false,
                        // The booking's own page, once one exists (book() answers it beside the
                        // redirect). See the pageshow listener in mounted().
                        bookedManageUrl: null,
                        website: '',
                        turnstileToken: '',
                        turnstileWidgetId: null,
                        note: '',
                        form: { name: props.authName || '', email: props.authEmail || '', phone: '', notes: '' },
                    });
                },
                computed: {
                    visitorDays() {
                        var map = {};
                        for (var i = 0; i < this.allUtc.length; i++) {
                            var d = this.localDate(this.allUtc[i]);
                            (map[d] = map[d] || []).push(this.allUtc[i]);
                        }
                        Object.keys(map).forEach(function (k) { map[k].sort(); });
                        return map;
                    },
                    anySlots() { return Object.keys(this.visitorDays).length > 0; },
                    /**
                     * Whether the DISPLAYED month has anything. anySlots is global across every month
                     * fetched so far, so using it meant an empty month following a full one rendered a
                     * completely disabled grid with no message at all.
                     */
                    monthHasSlots() {
                        var prefix = this.month + '-';
                        return Object.keys(this.visitorDays).some(function (d) { return d.indexOf(prefix) === 0; });
                    },
                    todayLocal() {
                        // Cell keys are guest-local dates, so "today" has to be resolved in this.tz -
                        // not from new Date().getDate(), which is the browser's own zone.
                        return this.localDate(new Date().toISOString());
                    },
                    canGoPrev() { return this.month > this.minDate.slice(0, 7); },
                    canGoNext() {
                        // One month of slop: maxDate is schedule-local, and a late slot on that day can
                        // land on the following guest-local day.
                        var parts = this.maxDate.split('-');
                        var limit = new Date(Date.UTC(+parts[0], +parts[1], 1));
                        return this.month < (limit.getUTCFullYear() + '-' + String(limit.getUTCMonth() + 1).padStart(2, '0'));
                    },
                    tzChoices() {
                        var common = ['UTC', this.scheduleTz, this.tz,
                            'America/New_York', 'America/Chicago', 'America/Denver', 'America/Los_Angeles',
                            'Europe/London', 'Europe/Paris', 'Europe/Berlin',
                            'Asia/Jerusalem', 'Asia/Dubai', 'Asia/Kolkata', 'Asia/Singapore', 'Asia/Tokyo',
                            'Australia/Sydney'];
                        var seen = {};
                        var shortlist = common.filter((z) => z && this.tzList.indexOf(z) !== -1 && !seen[z] && (seen[z] = true));
                        if (!this.tzShowAll) return shortlist;
                        var needle = this.tzFilter.trim().toLowerCase();
                        var all = needle
                            ? this.tzList.filter(function (z) { return z.toLowerCase().indexOf(needle) !== -1; })
                            : this.tzList;
                        // Keep the current value selectable even when it is filtered out.
                        return all.indexOf(this.tz) === -1 ? [this.tz].concat(all) : all;
                    },
                    flatCells() {
                        var parts = this.month.split('-'); var y = +parts[0]; var m = +parts[1];
                        var firstDow = new Date(Date.UTC(y, m - 1, 1)).getUTCDay();
                        var offset = (firstDow - this.firstDay + 7) % 7;
                        var dim = new Date(Date.UTC(y, m, 0)).getUTCDate();
                        var cells = [];
                        for (var i = 0; i < offset; i++) cells.push(null);
                        for (var d = 1; d <= dim; d++) {
                            cells.push({ date: y + '-' + String(m).padStart(2, '0') + '-' + String(d).padStart(2, '0'), day: d });
                        }
                        while (cells.length % 7) cells.push(null);
                        return cells;
                    },
                    weekdayLabels() {
                        var labels = [];
                        for (var i = 0; i < 7; i++) {
                            var dow = (this.firstDay + i) % 7;
                            var dt = new Date(Date.UTC(2024, 0, 7 + dow)); // 2024-01-07 is a Sunday
                            labels.push(new Intl.DateTimeFormat(this.locale, { weekday: 'short', timeZone: 'UTC' }).format(dt));
                        }
                        return labels;
                    },
                    monthLabel() {
                        if (!this.month) return '';
                        var parts = this.month.split('-');
                        return new Intl.DateTimeFormat(this.locale, { month: 'long', year: 'numeric', timeZone: 'UTC' }).format(new Date(Date.UTC(+parts[0], +parts[1] - 1, 1)));
                    },
                    selectedDateLabel() {
                        return this.dayLabel(this.selectedDate);
                    },
                    selectedSlotLabel() {
                        return this.selectedSlot ? this.dayLabel(this.localDate(this.selectedSlot)) : '';
                    },
                    // The slot the booking currently holds, rendered in the SAME zone as the grid so the
                    // two can never disagree, and re-rendered when the guest changes the timezone.
                    currentShownDate() {
                        return this.currentSlotUtc ? this.dayLabel(this.localDate(this.currentSlotUtc), true) : '';
                    },
                    currentShownTime() {
                        if (!this.currentSlotUtc) return '';
                        var start = new Date(this.currentSlotUtc);
                        if (!this.duration) return this.localTime(start.toISOString());
                        var end = new Date(start.getTime() + this.duration * 60000);
                        return this.localTime(start.toISOString()) + ' - ' + this.localTime(end.toISOString());
                    },
                    currentShownShort() {
                        return this.currentSlotUtc
                            ? this.dayLabel(this.localDate(this.currentSlotUtc)) + ', ' + this.localTime(this.currentSlotUtc)
                            : '';
                    },
                    slotGroups() {
                        var slots = this.visitorDays[this.selectedDate] || [];
                        var g = { morning: [], afternoon: [], evening: [] };
                        for (var i = 0; i < slots.length; i++) {
                            var h = this.localHour(slots[i]);
                            if (h < 12) g.morning.push(slots[i]); else if (h < 17) g.afternoon.push(slots[i]); else g.evening.push(slots[i]);
                        }
                        return g;
                    },
                    showGroups() {
                        // The old test was "> 16", but the default configuration - a 30-minute type over
                        // the editor's default 09:00-17:00 - produces exactly 16, so headers never
                        // appeared for the common case. Group once the list is long enough to scan AND
                        // it actually spans more than one part of the day.
                        var n = (this.visitorDays[this.selectedDate] || []).length;
                        var groups = this.slotGroups;
                        var filled = ['morning', 'afternoon', 'evening'].filter(function (g) { return groups[g].length; }).length;
                        return n > 8 && filled >= 2;
                    },
                    rescheduleCtaLabel() {
                        // ctaLabel() returns "Confirm and pay X" for any non-free type, which would tell
                        // an already-paid guest they are about to be charged again.
                        return this.approvalWarning || this.requiresApproval
                            ? this.t.requestThisTime
                            : this.t.rescheduleConfirm;
                    },
                    ctaLabel() {
                        // Online payment happens immediately (fanOut routes to checkout before the
                        // approval check), so payment wording wins for every priced type.
                        if (this.submitting && this.paysOnline) return this.t.redirectingToPayment;
                        if (!this.isFree) return this.t.confirmAndPay;
                        return this.requiresApproval ? this.t.requestThisTime : this.t.confirmBooking;
                    },
                },
                methods: {
                    hasSlots(date) { return (this.visitorDays[date] || []).length > 0; },
                    dayLabel(ymd, withYear) {
                        if (!ymd) return '';
                        var p = ymd.split('-');
                        var opts = { weekday: 'long', month: 'long', day: 'numeric', timeZone: 'UTC' };
                        if (withYear) opts.year = 'numeric';
                        return new Intl.DateTimeFormat(this.locale, opts).format(new Date(Date.UTC(+p[0], +p[1] - 1, +p[2])));
                    },
                    scheduleLocalDate(utc) {
                        var parts = new Intl.DateTimeFormat('en-CA', { timeZone: this.scheduleTz, year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(new Date(utc));
                        var o = {}; parts.forEach(function (pp) { o[pp.type] = pp.value; });
                        return o.year + '-' + o.month + '-' + o.day;
                    },
                    localDate(utc) {
                        var parts = new Intl.DateTimeFormat('en-CA', { timeZone: this.tz, year: 'numeric', month: '2-digit', day: '2-digit' }).formatToParts(new Date(utc));
                        var o = {}; parts.forEach(function (p) { o[p.type] = p.value; });
                        return o.year + '-' + o.month + '-' + o.day;
                    },
                    localTime(utc) {
                        return new Intl.DateTimeFormat(this.locale, { timeZone: this.tz, hour: 'numeric', minute: '2-digit', hour12: !this.use24 }).format(new Date(utc));
                    },
                    localHour(utc) {
                        return parseInt(new Intl.DateTimeFormat('en-GB', { timeZone: this.tz, hour: '2-digit', hour12: false }).format(new Date(utc)), 10);
                    },
                    mergeDays(data) {
                        var self = this;
                        var days = (data && data.days) || {};
                        Object.keys(days).forEach(function (d) {
                            days[d].forEach(function (s) {
                                if (self.allUtc.indexOf(s.utc) === -1) self.allUtc.push(s.utc);
                            });
                        });
                        if (data && data.next_available_date) self.nextAvailable = data.next_available_date;
                    },
                    stepLabel(n) {
                        return (this.t.stepOf || '').replace(':current', n).replace(':total', 2);
                    },
                    tzLabel(z) {
                        // "Asia/Jerusalem · GMT+3" reads far better than a bare identifier.
                        try {
                            var name = new Intl.DateTimeFormat(this.locale, { timeZone: z, timeZoneName: 'shortOffset' })
                                .formatToParts(new Date())
                                .find(function (p) { return p.type === 'timeZoneName'; });
                            return name ? z + ' · ' + name.value : z;
                        } catch (e) { return z; }
                    },
                    toggleTz() {
                        this.tzOpen = !this.tzOpen;
                        if (!this.tzOpen) { this.tzShowAll = false; this.tzFilter = ''; }
                    },
                    async fetchMonth(monthStr) {
                        if (this.fetched[monthStr]) return;
                        this.loading = true;
                        try {
                            var res = await fetch(this.slotsUrl + '?from=' + monthStr + '-01&days=31', { headers: { 'Accept': 'application/json' } });
                            if (!res.ok) {
                                // Read the body before giving up. A blocked booking answers 422 with the
                                // real reason ("this appointment can no longer be changed"), and throwing
                                // that away showed "Could not load times. Retry" forever, with a Retry
                                // button that just re-fired the same 422.
                                var failure = null;
                                try { failure = await res.json(); } catch (pe) { failure = null; }
                                if (failure && failure.error) {
                                    this.error = failure.error;
                                    this.canRetry = false;
                                    this.loading = false;
                                    return;
                                }
                                throw new Error('http ' + res.status);
                            }
                            var data = await res.json();
                            this.mergeDays(data);
                            this.fetched[monthStr] = true;
                            if (this.canRetry) { this.error = ''; this.canRetry = false; }
                        } catch (e) {
                            // Swallowing this left a blank calendar with no message and no way back.
                            this.error = this.t.loadError;
                            this.canRetry = true;
                        }
                        this.loading = false;
                    },
                    retryMonth() {
                        this.error = '';
                        this.canRetry = false;
                        this.fetchMonth(this.month);
                    },
                    changeMonth(delta) {
                        if (delta < 0 && !this.canGoPrev) return;
                        if (delta > 0 && !this.canGoNext) return;
                        var parts = this.month.split('-'); var y = +parts[0]; var m = +parts[1] - 1 + delta;
                        var nd = new Date(Date.UTC(y, m, 1));
                        this.month = nd.getUTCFullYear() + '-' + String(nd.getUTCMonth() + 1).padStart(2, '0');
                        // Leaving the selection behind kept the previous month's times on screen.
                        if (this.selectedDate && this.selectedDate.indexOf(this.month + '-') !== 0) {
                            this.selectedDate = null;
                            this.armed = null;
                        }
                        this.fetchMonth(this.month);
                    },
                    selectDate(date) {
                        this.selectedDate = date;
                        this.armed = null;
                        this.error = '';
                        // Below sm the panels stack, so the slot list is off-screen after a tap.
                        if (window.matchMedia && window.matchMedia('(max-width: 639px)').matches) {
                            this.$nextTick(() => {
                                var el = this.$refs.times;
                                if (el && el.scrollIntoView) el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                            });
                        }
                    },
                    armSlot(u) {
                        this.armed = (this.armed === u) ? null : u;
                        // Move focus onto the confirm button that just appeared.
                        if (this.armed) {
                            this.$nextTick(() => {
                                var btn = this.$refs.nextButton;
                                btn = Array.isArray(btn) ? btn[0] : btn;
                                if (btn && btn.focus) btn.focus();
                            });
                        }
                    },
                    confirmSlot(u) {
                        this.selectedSlot = u;
                        // Reschedule gets a review step instead of a details form: there are no guest
                        // details to collect, but a move still needs an explicit confirmation.
                        this.step = this.mode === 'reschedule' ? 'review' : 'details';
                        this.error = '';
                    },
                    backToPick() { this.step = 'pick'; this.error = ''; },
                    async jumpToNext() {
                        if (!this.nextAvailable) return;
                        this.month = this.nextAvailable.slice(0, 7);
                        // Await, then resolve the day from the slots themselves: next_available_date is
                        // SCHEDULE-local while selectedDate is keyed by GUEST-local date, so assigning it
                        // straight across could select a day that has nothing on it.
                        await this.fetchMonth(this.month);
                        var target = this.nextAvailable;
                        var firstOnOrAfter = this.allUtc
                            .filter((u) => this.scheduleLocalDate(u) >= target)
                            .sort()[0];
                        this.selectedDate = firstOnOrAfter ? this.localDate(firstOnOrAfter) : null;
                        if (this.selectedDate) this.month = this.selectedDate.slice(0, 7);
                    },
                    buildTzList() {
                        try { this.tzList = Intl.supportedValuesOf('timeZone'); }
                        catch (e) { this.tzList = [this.scheduleTz, this.tz]; }
                    },
                    async submit(notify) {
                        this.submitting = true; this.error = ''; this.fieldErrors = {};
                        var fd = new FormData();

                        if (this.mode === 'reschedule') {
                            fd.append('slot', this.selectedSlot);
                            // Proves the page was not stale, so a replay cannot silently re-fire the
                            // calendar sync, the webhook and the mail.
                            if (this.currentSlotUtc) fd.append('from_slot', this.currentSlotUtc);
                            if (this.ownerMode) {
                                fd.append('notify', notify === false ? '0' : '1');
                                if (this.note) fd.append('note', this.note);
                            } else {
                                // The owner is not the guest, so their browser zone must not overwrite
                                // the zone the guest is shown their booking in.
                                fd.append('guest_timezone', this.tz);
                            }
                            return await this.post(fd);
                        }

                        fd.append('name', this.form.name); fd.append('email', this.form.email);
                        if (this.form.phone) fd.append('phone', this.form.phone);
                        if (this.form.notes) fd.append('notes', this.form.notes);
                        fd.append('slot', this.selectedSlot); fd.append('guest_timezone', this.tz);
                        fd.append('website', this.website);
                        if (this.pageLang) fd.append('lang', this.pageLang);
                        if (this.turnstileEnabled) fd.append('cf-turnstile-response', this.turnstileToken);

                        return await this.post(fd);
                    },
                    postTo(url) {
                        var f = document.createElement('form');
                        f.method = 'POST'; f.action = url;
                        var t = document.createElement('input');
                        t.type = 'hidden'; t.name = '_token'; t.value = this.csrf;
                        f.appendChild(t); document.body.appendChild(f); f.submit();
                    },
                    async post(fd) {
                        try {
                            var res = await fetch(this.bookUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' }, body: fd });
                            if (res.status === 419) { this.error = this.t.sessionExpired; this.submitting = false; return; }
                            var j;
                            try { j = await res.json(); } catch (pe) { this.error = this.t.sessionExpired; this.submitting = false; return; }
                            if (res.ok && j.manage_url) { this.bookedManageUrl = j.manage_url; }
                            if (res.ok && j.redirect_url) { window.location = j.redirect_url; return; }
                            // A gateway other than Stripe or the payment URL is started by the pay
                            // step as a real form post, so its driver may answer with a page of its
                            // own rather than a redirect a fetch could hand back.
                            if (res.ok && j.pay_url) { this.postTo(j.pay_url); return; }
                            if (j.error === undefined && j.errors) {
                                this.fieldErrors = {};
                                var leftovers = [];
                                Object.keys(j.errors).forEach((k) => {
                                    var msg = Array.isArray(j.errors[k]) ? j.errors[k][0] : j.errors[k];
                                    this.fieldErrors[k] = msg;
                                    if (['name', 'email', 'phone'].indexOf(k) === -1) leftovers.push(msg);
                                });
                                // Errors without a dedicated field slot (turnstile, notes, slot) surface here.
                                if (leftovers.length) this.error = leftovers.join(' ');
                            }
                            else if (j.slots) {
                                // Reconcile the whole refreshed schedule-local day, not just the slot we
                                // tried: siblings booked by others since our fetch must disappear too.
                                var dayKeys = Object.keys((j.slots && j.slots.days) || {});
                                if (!dayKeys.length && this.selectedSlot) dayKeys = [this.scheduleLocalDate(this.selectedSlot)];
                                this.allUtc = this.allUtc.filter((u) => dayKeys.indexOf(this.scheduleLocalDate(u)) === -1);
                                this.mergeDays(j.slots); this.armed = null; this.selectedSlot = null;
                                this.error = j.error || this.t.slotTaken; this.step = 'pick';
                            }
                            // Branch on the status. A single slotTaken fallback meant 401, 403, 404, 429
                            // and 500 all told the guest "That time was just booked" - which for a lapsed
                            // plan, a deleted booking or a database timeout is simply false, and sends
                            // them off to pick a different slot for a problem that is not about slots.
                            else if (res.status === 429) { this.error = j.error || this.t.rescheduleFailed; }
                            else if (res.status >= 500) { this.error = j.error || this.t.rescheduleFailed; }
                            else if (res.status === 401 || res.status === 403 || res.status === 404) {
                                this.error = j.error || this.t.sessionExpired;
                            }
                            else { this.error = j.error || this.t.slotTaken; }
                            if (this.turnstileEnabled && this.turnstileWidgetId !== null && typeof turnstile !== 'undefined') {
                                this.turnstileToken = ''; turnstile.reset(this.turnstileWidgetId);
                            }
                        } catch (e) { this.error = this.t.sessionExpired; }
                        this.submitting = false;
                    },
                },
                mounted() {
                    // Back from the payment page by the browser's Back button: the browser restores
                    // this page as it was left, with Confirm still busy, and the slot it offers is
                    // now held by this very booking. So a guest who has booked goes to the booking's
                    // own page, where they can pay or cancel; anything else gets its button back.
                    // The same listener the ticket form has (event/tickets).
                    window.addEventListener('pageshow', (event) => {
                        if (!event.persisted) return;
                        if (this.bookedManageUrl) { window.location.replace(this.bookedManageUrl); return; }
                        this.submitting = false;
                    });
                    if (this.turnstileEnabled && this.turnstileSiteKey) {
                        var self = this;
                        var renderWidget = function () {
                            if (typeof turnstile === 'undefined') { setTimeout(renderWidget, 100); return; }
                            var elw = document.getElementById('turnstile-booking-widget');
                            if (!elw) { setTimeout(renderWidget, 200); return; }
                            self.turnstileWidgetId = turnstile.render('#turnstile-booking-widget', {
                                sitekey: self.turnstileSiteKey,
                                size: 'flexible',
                                'retry': 'auto',
                                'refresh-expired': 'auto',
                                callback: function (token) { self.turnstileToken = token; },
                                'error-callback': function () {
                                    self.turnstileToken = '';
                                    if (self.turnstileWidgetId !== null && typeof turnstile !== 'undefined') turnstile.reset(self.turnstileWidgetId);
                                    return true;
                                },
                            });
                        };
                        // The widget container only exists on the details step; watch for it.
                        this.$watch('step', function (v) {
                            if (v !== 'details') return;
                            if (self.turnstileWidgetId !== null && typeof turnstile !== 'undefined') {
                                // Release the previous instance rather than stacking a new one on top.
                                try { turnstile.remove(self.turnstileWidgetId); } catch (e) {}
                                self.turnstileWidgetId = null;
                                self.turnstileToken = '';
                            }
                            setTimeout(renderWidget, 50);
                        });
                    }
                    this.tz = Intl.DateTimeFormat().resolvedOptions().timeZone || this.scheduleTz;
                    this.mergeDays(this.initial);
                    this.buildTzList();
                    var dates = Object.keys(this.visitorDays).sort();
                    // In reschedule mode, open on the day the booking currently sits on rather than the
                    // soonest available one: the guest is orienting from "where I am now", and nearby
                    // times are the most likely choice.
                    var preferred = null;
                    if (this.mode === 'reschedule' && this.currentSlotUtc) {
                        var currentDay = this.localDate(this.currentSlotUtc);
                        if (dates.indexOf(currentDay) !== -1) preferred = currentDay;
                    }
                    if (preferred) { this.selectedDate = preferred; this.month = preferred.slice(0, 7); }
                    else if (dates.length) { this.selectedDate = dates[0]; this.month = dates[0].slice(0, 7); }
                    else {
                        // Start on the first bookable month, not the browser's UTC month - that is also
                        // the lower bound the prev-month button honours.
                        this.month = this.minDate.slice(0, 7);
                    }
                },
            }).mount('#booking-app[data-vue-root]');
        })();
    </script>
@endif
</x-app-guest-layout>
