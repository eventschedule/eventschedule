@php
    // Reschedule mode reuses this whole view. Its URL carries the booking secret, so unlike the public
    // /book page it must never be indexed.
    $mode = $mode ?? 'book';
    $isReschedule = $mode === 'reschedule';
    $sale = $sale ?? null;
    $event = $event ?? null;
    $ownerMode = $ownerMode ?? false;
@endphp
<x-app-guest-layout :role="$role" :noIndex="$isReschedule">
    @php
        // A pale accent is unreadable as text on the card, and a pale accent behind hardcoded white
        // text is unreadable as a button label. Both directions get a contrast-checked value, driven
        // through CSS custom properties so the light/dark split resolves in CSS rather than in Vue.
        $accent = $role->accent_color ?? '#4E81FA';
        $accentText = accent_contrast_color($accent);
        $accentOnLight = \App\Utils\ColorUtils::readableAccentColor($accent, '#ffffff', '#111827');
        $accentOnDark = \App\Utils\ColorUtils::readableAccentColor($accent, '#252526', '#ffffff');

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
            'initial' => $initialSlots,
            'scheduleTz' => $initialSlots['schedule_timezone'] ?? config('app.timezone'),
            'scheduleName' => $role->name,
            'typeName' => $type->name,
            'typeDescription' => $type->description,
            'duration' => $type->duration_minutes,
            // MoneyUtils, not raw concatenation: "JPY 5000.00" should read "¥5,000".
            'priceLabel' => $type->isFree() ? __('messages.free') : \App\Utils\MoneyUtils::format((float) $type->price, $type->currency_code),
            'isFree' => $type->isFree(),
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

    {{-- Kept outside #booking-app: anything inside the mount is compiled as a Vue template. --}}
    <style {!! nonce_attr() !!}>
        #booking-app { --es-accent: {{ $accent }}; --es-accent-text: {{ $accentText }}; --es-accent-readable: {{ $accentOnLight }}; }
        .dark #booking-app { --es-accent-readable: {{ $accentOnDark }}; }

        /* Accent as a fill: the label uses the contrast-checked colour, never a hardcoded white. */
        #booking-app .es-accent-fill { background-color: var(--es-accent); color: var(--es-accent-text); }
        /* Accent as text or a border on the card surface: contrast-checked per mode. */
        #booking-app .es-accent-text { color: var(--es-accent-readable); }
        #booking-app .es-dot { background-color: var(--es-accent-readable); }
        #booking-app .es-day-selected { background-color: var(--es-accent); color: var(--es-accent-text); }
        /* A ring rather than a fill, so "today" cannot be confused with "selected". */
        #booking-app .es-today { box-shadow: inset 0 0 0 2px var(--es-accent-readable); }
        #booking-app .es-slot { border-color: var(--es-accent-readable); color: var(--es-accent-readable); }
        #booking-app .es-slot-armed { background-color: var(--es-accent); border-color: var(--es-accent); color: var(--es-accent-text); }

        /* Compact fact chips for the rail. Custom dark palette, per the app's own scale: the guest
           card is #252526 in dark mode, so the chip sits one shade above it. */
        #booking-app .es-fact {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            border-radius: 9999px;
            background-color: #f3f4f6;
            padding: 0.125rem 0.5rem;
            font-size: 0.75rem;
            line-height: 1.25rem;
            color: #2d2d30;
        }
        .dark #booking-app .es-fact { background-color: #2d2d30; color: #d1d5db; }

        /* No global outline reset here, but the UA ring is unreliable against an accent fill. */
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
        <div class="max-w-5xl mx-auto px-4 py-8">
            <div class="rounded-2xl border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-6 text-amber-800 dark:text-amber-200">
                {{ __('messages.appointments_enable_js') }}
            </div>
        </div>
    </noscript>

    {{-- max-w-5xl, and the rail is a fixed 20rem rather than a third: the extra width all goes to the
         picker, which is what turns the slot list into two readable columns. --}}
    <div id="booking-app" data-props="{{ json_encode($props) }}" class="max-w-5xl mx-auto px-4 py-8">
        {{-- The guest page background comes from the schedule's own theme and does not follow dark
             mode - and it defaults to a photo or a random gradient - so the widget needs an opaque
             surface of its own in BOTH modes, and every child carries an explicit text color. --}}
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-sm overflow-hidden md:flex">
            {{-- Left panel --}}
            {{-- A fixed 320px rail only from lg. Pinning it at md took 75px off the picker at a 768px
                 viewport (rail 320 vs the 245 a third used to give it), which is where the two-column
                 slot grid is tightest - the opposite of what widening the card was for. --}}
            <div class="md:w-1/3 lg:w-80 lg:flex-shrink-0 p-6 border-b md:border-b-0 md:border-e border-gray-200 dark:border-gray-700">
                <a :href="backUrl" class="text-xs text-gray-500 dark:text-gray-400 hover:underline"><span class="inline-block rtl:rotate-180">&larr;</span> @{{ mode === 'reschedule' ? t.rescheduleKeep : t.back }}</a>
                <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100 mt-2">@{{ typeName }}</h1>
                <p class="text-sm text-gray-600 dark:text-gray-400">@{{ scheduleName }}</p>
                {{-- One wrapped row of facts rather than four stacked lines. Below md the rail sits
                     above the calendar, so every line here is a line the picker starts further down.
                     Where the appointment happens is part of it: the guest could not see that at all
                     before, so "is this a call or do I travel?" was only answered by the email. --}}
                <div class="mt-2 flex flex-wrap items-center gap-1.5">
                    <span class="es-fact">@{{ duration }} @{{ t.minutes }}</span>
                    <span class="es-fact">@{{ priceLabel }}</span>
                    <span class="es-fact">
                        <svg v-if="locationType === 'online'" class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                        <svg v-else-if="locationType === 'phone'" class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                        <svg v-else class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        @{{ locationLabel }}
                    </span>
                    <span v-if="requiresApproval" class="es-fact">@{{ t.requiresConfirmation }}</span>
                </div>
                <p v-if="locationDetail" class="text-xs text-gray-500 dark:text-gray-400 mt-2">@{{ locationDetail }}</p>
                <p v-if="locationNote" class="text-xs text-gray-500 dark:text-gray-400 mt-1">@{{ locationNote }}</p>
                <p v-if="t.minNoticeHint && mode !== 'reschedule'" class="text-xs text-gray-500 dark:text-gray-400 mt-1">@{{ t.minNoticeHint }}</p>

                {{-- What they are moving, and from when. Without this the picker gives no clue which
                     booking is in play or what time it currently holds. --}}
                <div v-if="mode === 'reschedule' && currentSlotUtc" class="mt-4 rounded-lg bg-gray-50 dark:bg-gray-900 p-3">
                    <div v-if="guestName" class="mb-2 pb-2 border-b border-gray-200 dark:border-gray-700">
                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">@{{ guestName }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">@{{ guestEmail }}</div>
                    </div>
                    <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400">@{{ t.rescheduleCurrent }}</div>
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 mt-1">@{{ currentShownDate }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">@{{ currentShownTime }} (@{{ tz }})</div>
                </div>

                {{-- The highest-risk moment in the flow: a confirmed booking is about to be released. --}}
                <div v-if="approvalWarning" class="mt-4 flex items-start gap-2 rounded-lg border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-3 text-amber-800 dark:text-amber-200">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.5 0L3.16 16.25A2 2 0 005 19z" /></svg>
                    <p class="text-xs">@{{ t.approvalWarning }}</p>
                </div>

                <p v-if="mode === 'reschedule' && ownerMode" class="mt-4 text-xs text-gray-500 dark:text-gray-400">@{{ t.ownerNote }}</p>
                <p v-if="typeDescription" class="text-sm text-gray-600 dark:text-gray-300 mt-3">@{{ typeDescription }}</p>
            </div>

            {{-- Right panel --}}
            <div class="md:flex-1 min-w-0 p-6">
                {{-- Step: date + time --}}
                <div v-if="step === 'pick'">
                    <div class="mb-3 flex flex-wrap items-start justify-between gap-2">
                        <p class="text-xs text-gray-500 dark:text-gray-400" aria-current="step">@{{ stepLabel(1) }}</p>

                        {{-- Timezone. Moved out of the foot of the rail and next to the times it
                             relabels: on a phone the rail stacks above the picker, so a control down
                             there pushed the calendar most of a screen further down. The full IANA
                             list is ~430 unlabelled entries, so the common zones come first and the
                             rest sit behind a filter. --}}
                        <div class="relative">
                            <button type="button" @click="toggleTz" aria-controls="tz-panel" :aria-expanded="tzOpen ? 'true' : 'false'"
                                class="inline-flex items-center gap-1 text-xs px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300">
                                <span aria-hidden="true">&#127760;</span> <span aria-live="polite">@{{ t.timesShownIn }} @{{ tz }}</span>
                            </button>
                            <p v-if="tz !== scheduleTz" class="text-xs text-gray-500 dark:text-gray-400 mt-1 text-end">@{{ t.scheduleIn }} @{{ scheduleTz }}</p>
                            <div v-if="tzOpen" id="tz-panel" class="absolute end-0 z-10 mt-2 w-72 max-w-[80vw] rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800 p-3 shadow-lg">
                                <label class="sr-only" for="tz-select">@{{ t.timezone }}</label>
                                <select id="tz-select" v-model="tz" class="w-full text-sm px-2 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100">
                                    <option v-for="z in tzChoices" :key="z" :value="z">@{{ tzLabel(z) }}</option>
                                </select>
                                <div v-if="!tzShowAll" class="mt-1">
                                    <button type="button" @click="tzShowAll = true" class="text-xs es-accent-text hover:underline">@{{ t.showAllTimezones }}</button>
                                </div>
                                <div v-else class="mt-2">
                                    <label class="sr-only" for="tz-filter">@{{ t.search }}</label>
                                    <input id="tz-filter" v-model="tzFilter" type="search" :placeholder="t.search"
                                        class="w-full text-sm px-2 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="sm:flex sm:gap-6">
                        {{-- Calendar --}}
                        <div class="sm:w-1/2">
                            <div class="flex items-center justify-between mb-3">
                                <div class="font-semibold text-gray-900 dark:text-gray-100" aria-live="polite">@{{ monthLabel }}</div>
                                <div class="flex gap-1">
                                    {{-- Bounded: paging into the past, or past the booking window, only ever
                                         produced an empty grid with no explanation. --}}
                                    <button type="button" @click="changeMonth(-1)" :disabled="!canGoPrev"
                                        class="px-2 py-1 rounded border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rtl:rotate-180 disabled:opacity-40 disabled:cursor-not-allowed">
                                        <span aria-hidden="true">&lsaquo;</span><span class="sr-only">@{{ t.prevMonth }}</span>
                                    </button>
                                    <button type="button" @click="changeMonth(1)" :disabled="!canGoNext"
                                        class="px-2 py-1 rounded border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-300 rtl:rotate-180 disabled:opacity-40 disabled:cursor-not-allowed">
                                        <span aria-hidden="true">&rsaquo;</span><span class="sr-only">@{{ t.nextMonth }}</span>
                                    </button>
                                </div>
                            </div>
                            {{-- Redundant once every cell carries its full date as an accessible name. --}}
                            <div class="grid grid-cols-7 gap-1 text-center text-xs text-gray-500 dark:text-gray-400 mb-1" aria-hidden="true">
                                <div v-for="d in weekdayLabels" :key="d">@{{ d }}</div>
                            </div>
                            <div class="grid grid-cols-7 gap-1">
                                <template v-for="(cell, i) in flatCells" :key="i">
                                    <button v-if="cell" type="button"
                                        :disabled="!hasSlots(cell.date)"
                                        @click="selectDate(cell.date)"
                                        :aria-label="dayLabel(cell.date)"
                                        :aria-pressed="selectedDate === cell.date ? 'true' : 'false'"
                                        :aria-current="cell.date === todayLocal ? 'date' : null"
                                        :class="['aspect-square rounded-lg text-sm flex items-center justify-center relative',
                                            selectedDate === cell.date ? 'es-day-selected font-medium' : '',
                                            cell.date === todayLocal && selectedDate !== cell.date ? 'es-today' : '',
                                            hasSlots(cell.date) ? 'text-gray-900 dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium cursor-pointer' : 'text-gray-400 dark:text-gray-600 cursor-default']">
                                        @{{ cell.day }}
                                        <span v-if="hasSlots(cell.date) && selectedDate !== cell.date" class="absolute bottom-1 w-1 h-1 rounded-full es-dot" aria-hidden="true"></span>
                                    </button>
                                    <div v-else></div>
                                </template>
                            </div>

                            {{-- Loading was a bare ellipsis in a <p>. --}}
                            <div v-if="loading" class="mt-3 space-y-2" aria-busy="true">
                                <span class="sr-only" aria-live="polite">@{{ t.loading }}</span>
                                <div v-for="n in 3" :key="n" class="h-3 rounded bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                            </div>
                            <p v-if="!loading && !monthHasSlots && !nextAvailable" class="text-sm text-gray-500 dark:text-gray-400 mt-3">@{{ anySlots ? t.noTimesThisMonth : t.noTimes }}</p>
                            {{-- In reschedule mode an empty calendar reads like the booking itself broke. --}}
                            <div v-if="!loading && !anySlots && !nextAvailable && mode === 'reschedule'" class="mt-3 flex items-start gap-2 rounded-lg border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-3 text-amber-800 dark:text-amber-200">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.5 0L3.16 16.25A2 2 0 005 19z" /></svg>
                                <div class="text-xs">
                                    <p>@{{ t.rescheduleNoneLeft }}</p>
                                    <a :href="backUrl" class="es-accent-text hover:underline">@{{ t.rescheduleKeep }}</a>
                                </div>
                            </div>
                            <p v-if="!loading && !monthHasSlots && nextAvailable" class="text-xs mt-3">
                                <button type="button" @click="jumpToNext" class="underline es-accent-text">@{{ t.nextAvailable }}: @{{ nextAvailable }}</button>
                            </p>
                        </div>

                        {{-- Times --}}
                        <div class="sm:w-1/2 mt-6 sm:mt-0" ref="times">
                            <div v-if="error" class="mb-3 p-2 rounded bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 text-sm" role="alert" aria-live="assertive">
                                @{{ error }}
                                <button v-if="canRetry" type="button" @click="retryMonth" class="ms-1 underline">@{{ t.retry }}</button>
                            </div>
                            <div v-if="selectedDate" role="group" :aria-label="selectedDateLabel">
                                <h2 class="sr-only">@{{ t.pickTime }}</h2>
                                <div class="font-semibold text-gray-900 dark:text-gray-100 mb-2">@{{ selectedDateLabel }}</div>
                                <div v-if="loading" class="space-y-2" aria-busy="true">
                                    <div v-for="n in 5" :key="n" class="h-9 rounded-lg bg-gray-200 dark:bg-gray-700 animate-pulse"></div>
                                </div>
                                <template v-else v-for="group in ['morning','afternoon','evening']" :key="group">
                                    <div v-if="slotGroups[group].length" class="mb-3">
                                        <h3 v-if="showGroups" class="text-xs text-gray-500 dark:text-gray-400 uppercase mb-1">@{{ t[group] }}</h3>
                                        {{-- Two columns. Single-file, a 16-slot day ran about 700px
                                             tall next to a ~330px calendar, so most of the panel was
                                             empty on one side and a long scroll on the other. The
                                             armed slot spans the row, keeping room for the inline
                                             Next button. --}}
                                        <div class="grid grid-cols-2 gap-2">
                                            <div v-for="u in slotGroups[group]" :key="u" :class="['flex gap-2', armed === u ? 'col-span-2' : '']">
                                                {{-- The booking's own slot is offered back (the grid excludes its event so it
                                                     does not block itself), so it has to be LABELLED. Unmarked, it looked like
                                                     any other option, and picking it produced a review step reading
                                                     "Previously: 2:00 PM / Now: 2:00 PM" and then a no-op. --}}
                                                <button type="button" @click="armSlot(u)"
                                                    :aria-pressed="armed === u ? 'true' : 'false'"
                                                    :aria-expanded="armed === u ? 'true' : 'false'"
                                                    :aria-current="u === currentSlotUtc ? 'true' : null"
                                                    :class="['min-w-0 flex-1 py-2 rounded-lg border text-sm transition', armed === u ? 'es-slot-armed' : 'es-slot']">
                                                    @{{ localTime(u) }}
                                                    <span v-if="u === currentSlotUtc" class="ms-1 text-xs opacity-70">@{{ t.currentSlotTag }}</span>
                                                </button>
                                                {{-- Fixed width so arming a slot does not reflow the whole column. --}}
                                                <button v-if="armed === u" ref="nextButton" type="button" @click="confirmSlot(u)" class="w-24 flex-shrink-0 px-4 py-2 rounded-lg text-sm es-accent-fill">@{{ t.next }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <p v-else class="text-sm text-gray-500 dark:text-gray-400">@{{ t.pickDate }}</p>
                        </div>
                    </div>
                </div>

                {{-- Step: review (reschedule only). A move must never be one tap on a button labelled
                     "Next" - the guest gets to see both times and confirm. --}}
                <div v-else-if="step === 'review'">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2" aria-current="step">@{{ stepLabel(2) }}</p>
                    <button type="button" @click="backToPick" class="text-xs text-gray-500 dark:text-gray-400 hover:underline mb-3"><span class="inline-block rtl:rotate-180">&larr;</span> @{{ t.back }}</button>
                    <h2 class="mb-3 text-base font-semibold text-gray-900 dark:text-gray-100">@{{ t.whatsChanged }}</h2>

                    <div class="mb-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm">
                        {{-- Deliberately the short form, matching the "Now" line below it rather than
                             the rail's fuller one: the single tz label under them covers both. --}}
                        <div v-if="currentSlotUtc" class="text-gray-500 dark:text-gray-400">
                            @{{ t.previously }}: <s>@{{ currentShownShort }}</s>
                        </div>
                        <div class="flex items-start justify-between gap-3 mt-1">
                            <div>
                                <div class="font-semibold text-gray-900 dark:text-gray-100">@{{ t.nowLabel }}: @{{ selectedSlotLabel }}, @{{ localTime(selectedSlot) }}</div>
                                <div class="text-gray-500 dark:text-gray-400">@{{ tz }}</div>
                            </div>
                            <button type="button" @click="backToPick" class="text-xs es-accent-text hover:underline flex-shrink-0">@{{ t.change }}</button>
                        </div>
                    </div>

                    {{-- A paid guest must not be told they are about to pay again. --}}
                    <p v-if="alreadyPaid" class="mb-3 text-xs text-gray-500 dark:text-gray-400">@{{ t.rescheduleNoCharge }}</p>

                    <div v-if="approvalWarning" class="mb-3 flex items-start gap-2 rounded-lg border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-3 text-amber-800 dark:text-amber-200">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.5 0L3.16 16.25A2 2 0 005 19z" /></svg>
                        <p class="text-xs">@{{ t.approvalWarning }}</p>
                    </div>

                    <div v-if="error" class="mb-3 p-2 rounded bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 text-sm" role="alert" aria-live="assertive">@{{ error }}</div>

                    {{-- Owner moves get the same choice the event editor already offers when a time
                         changes: an optional note to the guest, or move without emailing at all. Inline
                         on the review step rather than as a modal - this step IS the confirmation. --}}
                    <div v-if="ownerMode" class="mb-4 max-w-xl">
                        <label for="reschedule-note" class="block text-sm font-medium text-gray-700 dark:text-gray-300">@{{ t.notifyMessageLabel }}</label>
                        <textarea id="reschedule-note" v-model="note" maxlength="280" rows="3" :placeholder="t.notifyMessagePlaceholder"
                            class="mt-1 block w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-sm text-gray-900 dark:text-gray-100 dark:placeholder-gray-500"></textarea>
                        <div class="mt-1 text-xs text-gray-400 text-end">@{{ note.length }}/280</div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 max-w-xl">
                        <a :href="backUrl" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">@{{ t.rescheduleKeep }}</a>
                        {{-- Forward action last. Its label never says "pay". --}}
                        <button v-if="ownerMode" type="button" @click="submit(false)" :disabled="submitting"
                            class="px-4 py-3 rounded-lg border border-gray-300 dark:border-gray-600 text-sm text-gray-700 dark:text-gray-300 disabled:opacity-60">@{{ t.dontNotify }}</button>
                        <button type="button" @click="submit(true)" :disabled="submitting" :aria-busy="submitting ? 'true' : 'false'"
                            class="flex-1 py-3 rounded-lg font-semibold disabled:opacity-60 es-accent-fill">@{{ ownerMode ? t.notifyAndMove : rescheduleCtaLabel }}</button>
                    </div>
                    <span v-if="submitting" class="sr-only" aria-live="polite">@{{ t.loading }}</span>
                </div>

                {{-- Step: details --}}
                <div v-else-if="step === 'details'">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2" aria-current="step">@{{ stepLabel(2) }}</p>
                    <button type="button" @click="backToPick" class="text-xs text-gray-500 dark:text-gray-400 hover:underline mb-3"><span class="inline-block rtl:rotate-180">&larr;</span> @{{ t.back }}</button>
                    <h2 class="mb-3 text-base font-semibold text-gray-900 dark:text-gray-100">@{{ t.yourDetails }}</h2>
                    <div class="mb-4 max-w-2xl p-3 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm flex items-start justify-between gap-3">
                        <div>
                            <div class="font-semibold text-gray-900 dark:text-gray-100">@{{ selectedSlotLabel }}, @{{ localTime(selectedSlot) }}</div>
                            <div class="text-gray-500 dark:text-gray-400">@{{ tz }}</div>
                        </div>
                        {{-- The only way back to the times used to be the generic Back link. --}}
                        <button type="button" @click="backToPick" class="text-xs es-accent-text hover:underline flex-shrink-0">@{{ t.change }}</button>
                    </div>
                    <div v-if="error" class="mb-3 p-2 rounded bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 text-sm" role="alert" aria-live="assertive">@{{ error }}</div>
                    {{-- max-w-md inside a two-thirds panel left most of this step empty; name and email
                         also pair up once there is room for them. --}}
                    <form @submit.prevent="submit" class="space-y-3 max-w-2xl">
                        <fieldset>
                            <legend class="sr-only">@{{ t.yourDetails }}</legend>
                            <x-honeypot vmodel="website" />

                            <div class="space-y-3">
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <div>
                                        <label for="booking-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">@{{ t.name }}</label>
                                        <input id="booking-name" v-model="form.name" autocomplete="name" required
                                            :aria-invalid="fieldErrors.name ? 'true' : 'false'"
                                            :aria-describedby="fieldErrors.name ? 'booking-name-error' : null"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
                                        <span v-if="fieldErrors.name" id="booking-name-error" role="alert" class="block text-xs text-red-600 dark:text-red-400 mt-1">@{{ fieldErrors.name }}</span>
                                    </div>

                                    <div>
                                        <label for="booking-email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">@{{ t.email }}</label>
                                        <input id="booking-email" v-model="form.email" type="email" inputmode="email" autocomplete="email" required
                                            :aria-invalid="fieldErrors.email ? 'true' : 'false'"
                                            :aria-describedby="fieldErrors.email ? 'booking-email-error' : null"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
                                        <span v-if="fieldErrors.email" id="booking-email-error" role="alert" class="block text-xs text-red-600 dark:text-red-400 mt-1">@{{ fieldErrors.email }}</span>
                                    </div>
                                </div>

                                <div v-if="askPhone">
                                    <label for="booking-phone" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        @{{ t.phone }}<span v-if="!requirePhone" class="text-gray-500 dark:text-gray-400 font-normal"> (@{{ t.optional }})</span>
                                    </label>
                                    <input id="booking-phone" v-model="form.phone" type="tel" autocomplete="tel" :required="requirePhone" :aria-required="requirePhone ? 'true' : 'false'"
                                        :aria-invalid="fieldErrors.phone ? 'true' : 'false'"
                                        :aria-describedby="fieldErrors.phone ? 'booking-phone-error' : null"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
                                    <span v-if="fieldErrors.phone" id="booking-phone-error" role="alert" class="block text-xs text-red-600 dark:text-red-400 mt-1">@{{ fieldErrors.phone }}</span>
                                </div>

                                <div>
                                    <label for="booking-notes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        @{{ t.notesLabel }}<span class="text-gray-500 dark:text-gray-400 font-normal"> (@{{ t.optional }})</span>
                                    </label>
                                    <textarea id="booking-notes" v-model="form.notes" :placeholder="t.notes" rows="3"
                                        class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100 dark:placeholder-gray-500"></textarea>
                                </div>
                            </div>
                        </fieldset>
                        <div v-if="turnstileEnabled" id="turnstile-booking-widget"></div>
                        <button type="submit" :disabled="submitting" :aria-busy="submitting ? 'true' : 'false'" class="w-full py-3 rounded-lg font-semibold disabled:opacity-60 es-accent-fill">@{{ ctaLabel }}</button>
                        <span v-if="submitting" class="sr-only" aria-live="polite">@{{ t.loading }}</span>
                    </form>
                </div>
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
                        // approval check), so payment wording wins for stripe/payment_url types.
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
                        if (this.turnstileEnabled) fd.append('cf-turnstile-response', this.turnstileToken);

                        return await this.post(fd);
                    },
                    async post(fd) {
                        try {
                            var res = await fetch(this.bookUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': this.csrf, 'Accept': 'application/json' }, body: fd });
                            if (res.status === 419) { this.error = this.t.sessionExpired; this.submitting = false; return; }
                            var j;
                            try { j = await res.json(); } catch (pe) { this.error = this.t.sessionExpired; this.submitting = false; return; }
                            if (res.ok && j.redirect_url) { window.location = j.redirect_url; return; }
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
            }).mount('#booking-app');
        })();
    </script>
</x-app-guest-layout>
