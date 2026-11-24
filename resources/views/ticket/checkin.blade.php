<x-app-admin-layout>

    <x-slot name="head">
        <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>

        {{-- This page is used standing at a door, on a phone: the picker, three figures, the box
             to look somebody up, the last arrivals. What the page kit does not have is the bar
             that says how full the room is. --}}
        <style {!! nonce_attr() !!}>
            .door-pickers {
              display: flex;
              flex-wrap: wrap;
              gap: 0.625rem;
              max-width: 48rem;
              margin: 0 0 1rem;
            }
            .door-pickers > :first-child {
              flex: 1 1 16rem;
              min-width: 0;
            }
            .door-pickers > select {
              flex: 0 1 14rem;
            }
            .door-bar {
              height: 0.5rem;
              border-radius: 999px;
              background: var(--ap-tint-2);
              overflow: hidden;
            }
            .door-bar > i {
              display: block;
              height: 100%;
              border-radius: 999px;
              background: var(--brand-button-bg);
              transition: width 0.5s;
            }
            /* The door's one card (the 2026-11 redesign). */
            .door-hero {
              padding: 1.25rem 1.5rem 1.5rem;
              box-shadow: 0 1px 2px rgba(20, 22, 26, 0.06), 0 8px 24px rgba(20, 22, 26, 0.08);
            }
            .door-hero-top {
              display: flex;
              flex-wrap: wrap;
              align-items: flex-end;
              justify-content: space-between;
              gap: 0.75rem 2rem;
            }
            .door-hero-side {
              text-align: end;
            }
            .door-kicker {
              font-size: 0.6875rem;
              font-weight: 600;
              letter-spacing: 0.06em;
              text-transform: uppercase;
              color: rgb(var(--ap-ink-3));
            }
            .door-big {
              margin-top: 0.125rem;
              font-size: 3rem;
              font-weight: 700;
              line-height: 1;
              letter-spacing: -0.03em;
              font-variant-numeric: tabular-nums;
              color: rgb(var(--ap-ink));
            }
            .door-big .is-good {
              color: rgb(var(--ap-ink));
            }
            .door-of {
              font-size: 1rem;
              font-weight: 500;
              letter-spacing: 0;
              color: rgb(var(--ap-ink-2));
            }
            .door-mid {
              margin-top: 0.125rem;
              font-size: 1.5rem;
              font-weight: 700;
              font-variant-numeric: tabular-nums;
              color: rgb(var(--ap-ink));
            }
            .door-sub {
              margin-top: 0.25rem;
              font-size: 0.8125rem;
              color: rgb(var(--ap-ink-3));
            }
            .door-types-grid {
              display: grid;
              gap: 1rem 2rem;
              margin-top: 1rem;
              padding-top: 1rem;
              border-top: 1px solid rgb(var(--ap-border));
            }
            @media (min-width: 640px) {
              .door-types-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
              }
            }
            .door-type-line {
              display: flex;
              justify-content: space-between;
              gap: 1rem;
              margin-bottom: 0.375rem;
              font-size: 0.875rem;
            }
            .door-type-name {
              font-weight: 600;
              color: rgb(var(--ap-ink));
            }
            .door-type-num {
              font-variant-numeric: tabular-nums;
              font-weight: 600;
              color: rgb(var(--ap-ink));
            }
            .door-type-num span {
              font-weight: 400;
              color: rgb(var(--ap-ink-3));
            }
            .door-progress {
              display: flex;
              align-items: center;
              gap: 0.75rem;
              margin-top: 1rem;
              font-size: 0.8125rem;
              font-variant-numeric: tabular-nums;
              color: rgb(var(--ap-ink-3));
            }
            .door-progress .door-bar {
              flex: 1;
            }
            .door-types .door-bar {
              min-width: 6rem;
            }
            .door-types .c-bar {
              width: 40%;
            }
            .door-search input {
              display: block;
              width: 100%;
            }
            .door-hits {
              margin: 0.75rem -1.25rem -1.25rem;
              border-top: 1px solid rgb(var(--ap-border));
            }
            .door-note {
              margin: 0.75rem 0 0;
              font-size: 0.875rem;
              color: rgb(var(--ap-ink-3));
            }
            .door-loading {
              padding: 4rem 0;
              text-align: center;
            }
            @media (max-width: 639.98px) {
              .door-pickers > select {
                flex: 1 1 100%;
              }
              .door-progress {
                padding-inline: 0.75rem;
              }
              .door-types .c-bar {
                flex: 1 1 0;
                width: auto;
              }
            }

            /* The 2026-11 design, second pass (Check-in.dc.html): 24px between the parts, a hero
               that stands higher (.ap-hero) with a 64px count and "of 150 sold" at 18px, "Still to
               arrive" as a left-aligned block at 28px, the share in bold ink, a 52px search box,
               and 52px rows of arrivals whose rules stay inside the card's padding. */
            .door-wrap {
              --door-track: rgb(var(--ap-border));
            }
            .dark .door-wrap {
              --door-track: rgb(var(--ap-surface-sunken));
            }
            .door-wrap > .page-stack {
              gap: 1.5rem;
            }
            .door-pickers {
              margin-bottom: 1.5rem;
            }
            .door-date {
              min-height: 3rem;
              font-size: 0.9375rem;
              font-weight: 500;
              border-radius: 0.75rem;
            }
            .door-bar {
              background: var(--door-track);
            }
            .door-hero {
              display: flex;
              flex-direction: column;
              gap: 1.375rem;
              padding: clamp(1.25rem, 3vw, 1.75rem);
            }
            .door-hero-top {
              gap: 1.125rem 2.5rem;
            }
            .door-hero-top > .min-w-0 {
              flex: 1 1 13.75rem;
            }
            .door-hero-side {
              text-align: start;
            }
            .door-kicker {
              margin: 0;
              font-size: 0.75rem;
            }
            .door-big {
              margin-top: 0.375rem;
              font-size: clamp(2.75rem, 8vw, 4rem);
              letter-spacing: -0.035em;
            }
            .door-of {
              font-size: 1.125rem;
              font-weight: 400;
            }
            .door-mid {
              margin-top: 0.25rem;
              font-size: 1.75rem;
              letter-spacing: -0.02em;
            }
            .door-progress {
              margin-top: 0;
              font-size: 0.875rem;
              font-weight: 600;
              color: rgb(var(--ap-ink));
            }
            .door-progress .door-bar {
              height: 0.5rem;
            }
            .door-types-grid {
              grid-template-columns: repeat(auto-fit, minmax(12.5rem, 1fr));
              gap: 1rem 2rem;
              margin: 0;
              padding-top: 1.25rem;
              border-top-color: var(--ap-hairline);
            }
            .door-type-line {
              align-items: baseline;
              margin-bottom: 0.5rem;
            }
            .door-type-name {
              font-size: 0.9375rem;
            }
            .door-type-num {
              font-size: 0.875rem;
            }
            .door-type-num span {
              color: rgb(var(--ap-ink-2));
            }
            .door-types-grid .door-bar {
              height: 0.375rem;
            }
            .door-search {
              padding: 1.25rem;
            }
            .door-search-label {
              display: block;
              margin-bottom: 0.5rem;
              font-size: 0.875rem;
              font-weight: 600;
              color: rgb(var(--ap-ink));
            }
            .door-search input {
              height: 3.25rem;
              padding: 0 0.875rem;
              font-size: 1rem;
              border-radius: 0.75rem;
            }
            .door-recent {
              padding: 1.25rem 1.25rem 0.375rem;
            }
            .door-recent .door-kicker {
              padding-bottom: 0.75rem;
            }
            .door-people {
              margin: 0;
              padding: 0;
              list-style: none;
            }
            .door-person {
              display: flex;
              align-items: center;
              justify-content: space-between;
              gap: 0.75rem;
              min-height: 3.25rem;
              border-top: 1px solid var(--ap-hairline);
            }
            .door-person-main {
              display: flex;
              flex-wrap: wrap;
              align-items: baseline;
              gap: 0 0.625rem;
              min-width: 0;
            }
            .door-person-name {
              font-size: 0.9375rem;
              font-weight: 600;
              color: rgb(var(--ap-ink));
              overflow-wrap: anywhere;
            }
            .door-person-type {
              font-size: 0.8125rem;
              color: rgb(var(--ap-ink-2));
            }
            .door-person-ago {
              flex: none;
              font-size: 0.8125rem;
              font-variant-numeric: tabular-nums;
              white-space: nowrap;
              color: rgb(var(--ap-ink-2));
            }
        </style>
    </x-slot>

    <div class="page-shell">
        <x-page-header :title="__('messages.checkin_dashboard')" :lead="__('messages.checkin_lead')"
                       :back="route('sales')" :back-label="__('messages.sales')">
            <x-slot name="actions">
                <x-brand-link :href="route('ticket.scan')" class="gap-2">
                    {{ __('messages.scan_ticket') }}
                </x-brand-link>
            </x-slot>
        </x-page-header>

        @include('partials.team-access-notice', ['roles' => $planBlockedRoles])

        <div id="app" class="door-wrap">

            {{-- Which event, and which of its dates --}}
            <div class="door-pickers" v-if="events.length">
                <x-event-selector />
                <select v-if="availableDates.length > 0" v-model="selectedDate" @change="fetchStats" aria-label="{{ __('messages.date') }}"
                    class="door-date rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                    <option v-for="date in availableDates" :key="date" :value="date">@{{ prettyDate(date) }}</option>
                </select>
            </div>

            {{-- Nothing to count: no event on a plan that includes check-in --}}
            <div v-if="!events.length" class="ap-card rounded-xl">
                <x-page-empty :title="__('messages.no_events')" :text="__('messages.checkin_no_events_help')"
                    icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </div>

            {{-- No event chosen yet --}}
            <div v-else-if="!selectedEventId" class="ap-card rounded-xl">
                <x-page-empty :title="__('messages.select_event')"
                    icon="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
            </div>

            <div v-if="selectedEventId && loading" class="door-loading" role="status">
                <svg class="animate-spin mx-auto h-8 w-8 text-[var(--brand-blue)]" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span class="sr-only">{{ __('messages.loading') }}</span>
            </div>

            <div v-if="stats && !loading" class="page-stack">

                {{-- Nobody holds a ticket for this date (a pass booked in advance is somebody) --}}
                <div v-if="stats.total_sold === 0 && !(stats.pass_reserved > 0) && !stats.recent_checkins.length" class="ap-card rounded-xl">
                    <x-page-empty :title="__('messages.no_sales_yet')"
                        icon="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                </div>

                <template v-else>
                    {{-- 2026-11 redesign: one card for the door. Who is in, out of how many, and who
                         is still to come, then how full the room is, and each kind of ticket under it. --}}
                    <div class="ap-card rounded-xl ap-hero door-hero">
                        <div class="door-hero-top">
                            <div class="min-w-0">
                                <p class="door-kicker">{{ __('messages.checked_in') }}</p>
                                <p class="door-big"><span :class="{ 'is-good': stats.total_checked_in > 0 }">@{{ stats.total_checked_in }}</span> <span class="door-of" v-text="{{ \Illuminate\Support\Js::from(__('messages.checkin_of_sold')) }}.replace(':total', stats.total_sold)"></span></p>
                                <p v-if="stats.total_admitted > stats.total_checked_in" class="door-sub">@{{ stats.total_admitted }} {{ __('messages.admitted_incl_guests') }}</p>
                                <p v-if="stats.pass_reserved > 0" class="door-sub">{{ __('messages.pass_seats_reserved') }}: @{{ stats.pass_reserved }}</p>
                            </div>
                            <div class="door-hero-side">
                                <p class="door-kicker">{{ __('messages.checkin_still_to_come') }}</p>
                                <p class="door-mid">@{{ Math.max(0, stats.total_sold - stats.total_checked_in) }}</p>
                            </div>
                        </div>
                        <div class="door-progress">
                            <div class="door-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" :aria-valuenow="progressPercent" aria-label="{{ __('messages.checked_in') }}"><i :style="{ width: progressPercent + '%' }"></i></div>
                            <span>@{{ progressPercent }}%</span>
                        </div>
                        {{-- Each kind of ticket: how many of it are in. --}}
                        <ul v-if="stats.tickets.length > 1" class="door-types-grid">
                            <li v-for="ticket in stats.tickets" :key="ticket.type">
                                <p class="door-type-line"><bdi class="door-type-name">@{{ ticket.type }}</bdi><span class="door-type-num">@{{ ticket.checked_in }} <span>{{ __('messages.of') }} @{{ ticket.sold }}</span></span></p>
                                <div class="door-bar"><i :style="{ width: ticketPercent(ticket) + '%' }"></i></div>
                                <p v-if="ticket.admitted > ticket.checked_in" class="door-sub">@{{ ticket.admitted }} {{ __('messages.admitted_incl_guests') }}</p>
                            </li>
                        </ul>
                    </div>

                    {{-- Find somebody at the door.
                         This screen had no search of ANY kind - not by name, not by seat, not by order -
                         only a rear-view feed of the last ten arrivals. So "is C14 here yet", and "they say
                         they booked but the scanner will not read their phone", had no answer here. --}}
                    <section class="ap-card rounded-xl page-card door-search">
                        <label for="checkin-search" class="door-search-label">{{ __('messages.checkin_search') }}</label>
                        {{-- A plain attribute. It was bound to a JSON string, whose own quotes closed
                             the attribute, so the box never showed what can be typed into it. --}}
                        <input id="checkin-search" v-model="searchQuery" @input="onSearch" type="search" autocomplete="off"
                            placeholder="{{ __('messages.checkin_search_placeholder') }}"
                            class="rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" />

                        <p v-if="searchQuery.length >= 2 && !searching && !searchResults.length" class="door-note" role="status">{{ __('messages.checkin_search_none') }}</p>

                        <div v-if="searchResults.length" class="door-hits">
                            <table class="page-table is-compact">
                                <tbody>
                                    <tr v-for="(hit, i) in searchResults" :key="i">
                                        <td class="c-main c-strong">
                                            <bdi>@{{ hit.name }}</bdi>
                                            {{-- A seat, or (an order with no seat to its name) how many
                                                 tickets of which kind: "2 x General". --}}
                                            <span class="c-sub"><template v-if="hit.seat">@{{ hit.seat }}<template v-if="hit.ticket_type"> &middot; </template></template><template v-if="! hit.seat && hit.quantity > 1">@{{ hit.quantity }} &times; </template>@{{ hit.ticket_type }}</span>
                                        </td>
                                        <td class="c-actions">
                                            {{-- An order of several tickets can be partly in: said
                                                 as a count, in amber, never as "checked in". --}}
                                            <span v-if="hit.arrived" class="event-status is-on"><template v-if="hit.quantity > 1">@{{ hit.arrived_count }}/@{{ hit.quantity }} </template>{{ __('messages.checked_in') }}</span>
                                            <span v-else-if="hit.arrived_count" class="event-status is-warn">@{{ hit.arrived_count }}/@{{ hit.quantity }} {{ __('messages.checked_in') }}</span>
                                            <span v-else class="event-status">{{ __('messages.checkin_not_yet') }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    {{-- The last people through the door --}}
                    {{-- A name and its ticket on one line, the time at the end; the rules between
                         rows stay inside the card's padding (the 2026-11 design). --}}
                    <section class="ap-card rounded-xl door-recent" aria-labelledby="door-recent-title">
                        <h2 id="door-recent-title" class="door-kicker">{{ __('messages.recent_checkins') }}</h2>
                        <ul v-if="stats.recent_checkins.length > 0" class="door-people" role="list">
                            <li v-for="(checkin, index) in stats.recent_checkins" :key="index" class="door-person">
                                <span class="door-person-main">
                                    <bdi class="door-person-name">@{{ checkin.name }}</bdi>
                                    <span class="door-person-type">@{{ checkin.ticket_type }}<span v-if="checkin.seat_label"> &middot; @{{ checkin.seat_label }}</span></span>
                                </span>
                                <span class="door-person-ago">@{{ relativeTime(checkin.timestamp) }}</span>
                            </li>
                        </ul>
                        <div v-else>
                            <x-page-empty compact :title="__('messages.no_checkins_yet')" />
                        </div>
                    </section>
                </template>
            </div>
        </div>
    </div>

    <script {!! nonce_attr() !!}>
        const { createApp } = Vue

        createApp({
            data() {
                return {
                    events: @json($events),
                    selectedEventId: @json($selectedEventId ?? ''),
                    selectedDate: '{{ now()->format('Y-m-d') }}',
                    availableDates: [],
                    stats: null,
                    loading: false,
                    pollInterval: null,
                    dropdownOpen: false,
                    searchQuery: '',
                    searchResults: [],
                    searching: false,
                    searchTimer: null,
                }
            },
            computed: {
                selectedEvent() {
                    return this.events.find(e => e.id === this.selectedEventId) || null;
                },
                progressPercent() {
                    if (!this.stats || this.stats.total_sold === 0) return 0;
                    return Math.min(100, Math.round((this.stats.total_checked_in / this.stats.total_sold) * 100));
                }
            },
            methods: {
                /** Debounced, because this fires on every keystroke at a door with poor signal. */
                onSearch() {
                    clearTimeout(this.searchTimer);

                    if (this.searchQuery.trim().length < 2) {
                        this.searchResults = [];
                        return;
                    }

                    this.searchTimer = setTimeout(this.runSearch, 300);
                },
                async runSearch() {
                    if (! this.selectedEventId) return;

                    this.searching = true;
                    try {
                        // Built the same way the stats URL beside it is, so both follow whatever
                        // routing the install uses (subdomain on hosted, path on selfhost).
                        const url = '{{ route("checkin.search", ["event_id" => "__EVENT_ID__"]) }}'.replace('__EVENT_ID__', this.selectedEventId);
                        const res = await fetch(url + '?q=' + encodeURIComponent(this.searchQuery) + '&date=' + encodeURIComponent(this.selectedDate), {
                            headers: { Accept: 'application/json' },
                            credentials: 'same-origin',
                        });
                        const data = res.ok ? await res.json() : { results: [] };
                        this.searchResults = data.results || [];
                    } catch (e) {
                        this.searchResults = [];
                    } finally {
                        this.searching = false;
                    }
                },
                // A date of a recurring event, as the reader's language writes it: the picker
                // listed them as 2026-01-04.
                prettyDate(date) {
                    const day = new Date(date + 'T12:00:00');
                    if (isNaN(day)) return date;
                    try {
                        return day.toLocaleDateString(window.appLocale || undefined, { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
                    } catch (e) {
                        return date;
                    }
                },
                ticketPercent(ticket) {
                    if (ticket.sold === 0) return 0;
                    return Math.min(100, Math.round((ticket.checked_in / ticket.sold) * 100));
                },
                relativeTime(timestamp) {
                    const now = Math.floor(Date.now() / 1000);
                    const diff = now - timestamp;
                    if (diff < 60) return @json(__('messages.just_now'));
                    const mins = Math.floor(diff / 60);
                    if (mins < 60) return mins + ' ' + @json(__('messages.minutes_ago'));
                    const hrs = Math.floor(mins / 60);
                    if (hrs < 24) return hrs + 'h ' + @json(__('messages.ago'));
                    return new Date(timestamp * 1000).toLocaleDateString();
                },
                toggleDropdown() {
                    this.dropdownOpen = !this.dropdownOpen;
                },
                closeDropdown() {
                    this.dropdownOpen = false;
                },
                onEventChange(eventId) {
                    this.selectedEventId = eventId;
                    this.closeDropdown();
                    this.stats = null;
                    this.availableDates = [];
                    if (this.selectedEventId) {
                        this.fetchStats();
                    }
                    this.startPolling();
                },
                fetchStats() {
                    if (!this.selectedEventId) return;

                    const isInitial = !this.stats;
                    if (isInitial) this.loading = true;

                    const url = '{{ route("checkin.stats", ["event_id" => "__EVENT_ID__"]) }}'.replace('__EVENT_ID__', this.selectedEventId) + '?date=' + encodeURIComponent(this.selectedDate);

                    fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => {
                        if (!r.ok) throw new Error();
                        return r.json();
                    })
                    .then(data => {
                        this.stats = data;
                        this.availableDates = data.available_dates || [];

                        // If selectedDate not in available dates, pick first
                        if (this.availableDates.length > 0 && !this.availableDates.includes(this.selectedDate)) {
                            // Pick today if available, otherwise first
                            const today = new Date().toISOString().slice(0, 10);
                            this.selectedDate = this.availableDates.includes(today) ? today : this.availableDates[0];
                            this.fetchStats();
                            return;
                        }

                        this.loading = false;
                    })
                    .catch(() => {
                        this.loading = false;
                    });
                },
                startPolling() {
                    this.stopPolling();
                    if (this.selectedEventId) {
                        this.pollInterval = setInterval(() => {
                            if (!document.hidden) {
                                this.fetchStats();
                            }
                        }, 10000);
                    }
                },
                stopPolling() {
                    if (this.pollInterval) {
                        clearInterval(this.pollInterval);
                        this.pollInterval = null;
                    }
                }
            },
            mounted() {
                if (this.selectedEventId) {
                    this.fetchStats();
                    this.startPolling();
                }

                document.addEventListener('visibilitychange', () => {
                    if (!document.hidden && this.selectedEventId) {
                        this.fetchStats();
                    }
                });

                document.addEventListener('click', (e) => {
                    const el = document.getElementById('event-selector-dropdown');
                    if (el && !el.contains(e.target)) {
                        this.closeDropdown();
                    }
                });

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        this.closeDropdown();
                    }
                });
            },
            beforeUnmount() {
                this.stopPolling();
            }
        }).mount('#app')
    </script>

</x-app-admin-layout>
