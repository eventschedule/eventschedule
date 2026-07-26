<x-app-guest-layout :role="$role">
    @php
        // Path-relative URLs: absolute subdomain URLs inside json_encode'd props escape their
        // slashes, so the ResolveCustomDomain HTML rewrite misses them and custom-domain visitors
        // would fetch/POST cross-origin (CORS-blocked). Relative paths stay same-origin everywhere.
        $props = [
            'slotsUrl' => route('appointments.slots', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug], false),
            'bookUrl' => route('appointments.book.store', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug], false),
            'backUrl' => route('appointments.book', ['subdomain' => $role->subdomain], false),
            'turnstileEnabled' => \App\Utils\TurnstileUtils::isActiveForRequest(),
            'turnstileSiteKey' => \App\Utils\TurnstileUtils::isActiveForRequest() ? \App\Utils\TurnstileUtils::getSiteKey() : null,
            'csrf' => csrf_token(),
            'initial' => $initialSlots,
            'scheduleTz' => $initialSlots['schedule_timezone'] ?? config('app.timezone'),
            'scheduleName' => $role->name,
            'typeName' => $type->name,
            'typeDescription' => $type->description,
            'duration' => $type->duration_minutes,
            'priceLabel' => $type->isFree() ? __('messages.free') : strtoupper((string) $type->currency_code).' '.number_format((float) $type->price, 2),
            'isFree' => $type->isFree(),
            'requiresApproval' => (bool) $type->requires_approval,
            'askPhone' => (bool) $type->ask_phone,
            'requirePhone' => (bool) $type->require_phone,
            'use24' => get_use_24_hour_time($role) ? true : false,
            'firstDay' => (int) ($role->first_day_of_week ?? 0),
            'accent' => $role->accent_color ?? '#4E81FA',
            'authName' => auth()->user()->name ?? '',
            'authEmail' => auth()->user()->email ?? '',
            't' => [
                'pickDate' => __('messages.appointments_pick_date'),
                'yourDetails' => __('messages.appointments_your_details'),
                'confirmBooking' => __('messages.appointments_confirm_booking'),
                'confirmAndPay' => __('messages.appointments_confirm_and_pay', ['price' => $type->isFree() ? '' : strtoupper((string) $type->currency_code).' '.number_format((float) $type->price, 2)]),
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
            ],
        ];
    @endphp

    <div id="booking-app" data-props="{{ json_encode($props) }}" class="max-w-4xl mx-auto px-4 py-8">
        {{-- The guest page background comes from the schedule's own theme and does not follow
             dark mode, so the widget takes its own dark surface and every child inside it
             carries a dark: text color. Light mode is unchanged. --}}
        <div class="rounded-2xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 overflow-hidden md:flex">
            {{-- Left panel --}}
            <div class="md:w-1/3 p-6 border-b md:border-b-0 md:border-e border-gray-200 dark:border-gray-700">
                <a :href="backUrl" class="text-xs text-gray-400"><span class="inline-block rtl:rotate-180">&larr;</span> @{{ t.back }}</a>
                <h1 class="text-xl font-bold dark:text-gray-100 mt-2">@{{ typeName }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">@{{ scheduleName }}</p>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">@{{ duration }} @{{ t.minutes }} &middot; @{{ priceLabel }}</p>
                <p v-if="requiresApproval" class="inline-block mt-2 text-xs px-2 py-1 rounded-full border border-gray-300 dark:border-gray-600 dark:text-gray-300">@{{ t.requiresConfirmation }}</p>
                <p v-if="typeDescription" class="text-sm text-gray-600 dark:text-gray-300 mt-3">@{{ typeDescription }}</p>

                <div class="mt-6">
                    <button type="button" @click="tzOpen = !tzOpen" class="inline-flex items-center gap-1 text-xs px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 dark:text-gray-300">
                        <span>&#127760;</span> @{{ t.timesShownIn }} @{{ tz }}
                    </button>
                    <select v-if="tzOpen" v-model="tz" class="mt-2 w-full text-sm px-2 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 dark:text-gray-100">
                        <option v-for="z in tzList" :key="z" :value="z">@{{ z }}</option>
                    </select>
                    <p v-if="tz !== scheduleTz" class="text-xs text-gray-400 mt-1">@{{ t.scheduleIn }} @{{ scheduleTz }}</p>
                </div>
            </div>

            {{-- Right panel --}}
            <div class="md:w-2/3 p-6">
                {{-- Step: date + time --}}
                <div v-if="step === 'pick'">
                    <div class="sm:flex sm:gap-6">
                        {{-- Calendar --}}
                        <div class="sm:w-1/2">
                            <div class="flex items-center justify-between mb-3">
                                <div class="font-semibold dark:text-gray-100">@{{ monthLabel }}</div>
                                <div class="flex gap-1">
                                    <button type="button" @click="changeMonth(-1)" class="px-2 py-1 rounded border border-gray-200 dark:border-gray-600 dark:text-gray-300 rtl:rotate-180">&lsaquo;</button>
                                    <button type="button" @click="changeMonth(1)" class="px-2 py-1 rounded border border-gray-200 dark:border-gray-600 dark:text-gray-300 rtl:rotate-180">&rsaquo;</button>
                                </div>
                            </div>
                            <div class="grid grid-cols-7 gap-1 text-center text-xs text-gray-400 mb-1">
                                <div v-for="d in weekdayLabels" :key="d">@{{ d }}</div>
                            </div>
                            <div class="grid grid-cols-7 gap-1">
                                <template v-for="(cell, i) in flatCells" :key="i">
                                    <button v-if="cell" type="button"
                                        :disabled="!hasSlots(cell.date)"
                                        @click="selectDate(cell.date)"
                                        :style="selectedDate === cell.date ? ('background-color:' + accent + ';color:#fff') : ''"
                                        :class="['aspect-square rounded-lg text-sm flex items-center justify-center relative',
                                            hasSlots(cell.date) ? 'dark:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-700 font-medium cursor-pointer' : 'text-gray-300 dark:text-gray-500 cursor-default']">
                                        @{{ cell.day }}
                                        <span v-if="hasSlots(cell.date) && selectedDate !== cell.date" class="absolute bottom-1 w-1 h-1 rounded-full" :style="'background-color:' + accent"></span>
                                    </button>
                                    <div v-else></div>
                                </template>
                            </div>
                            <p v-if="loading" class="text-xs text-gray-400 mt-2">&hellip;</p>
                            <p v-if="!loading && !anySlots && !nextAvailable" class="text-sm text-gray-400 mt-3">@{{ t.noTimes }}</p>
                            <p v-if="!loading && !anySlots && nextAvailable" class="text-xs mt-3">
                                <button type="button" @click="jumpToNext" class="underline" :style="'color:' + accent">@{{ t.nextAvailable }}: @{{ nextAvailable }}</button>
                            </p>
                        </div>

                        {{-- Times --}}
                        <div class="sm:w-1/2 mt-6 sm:mt-0">
                            <div v-if="error" class="mb-3 p-2 rounded bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 text-sm">@{{ error }}</div>
                            <div v-if="selectedDate">
                                <div class="font-semibold dark:text-gray-100 mb-2">@{{ selectedDateLabel }}</div>
                                <template v-for="group in ['morning','afternoon','evening']" :key="group">
                                    <div v-if="slotGroups[group].length" class="mb-3">
                                        <div v-if="showGroups" class="text-xs text-gray-400 uppercase mb-1">@{{ t[group] }}</div>
                                        <div class="space-y-2">
                                            <div v-for="u in slotGroups[group]" :key="u" class="flex gap-2">
                                                <button type="button" @click="armSlot(u)"
                                                    :class="['flex-1 py-2 rounded-lg border text-sm transition', armed === u ? 'text-white' : '']"
                                                    :style="armed === u ? ('background-color:' + accent + ';border-color:' + accent) : ('border-color:' + accent + ';color:' + accent)">
                                                    @{{ localTime(u) }}
                                                </button>
                                                <button v-if="armed === u" type="button" @click="confirmSlot(u)" class="px-4 py-2 rounded-lg text-white text-sm" :style="'background-color:' + accent">@{{ t.next }}</button>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                            <p v-else class="text-sm text-gray-400">@{{ t.pickDate }}</p>
                        </div>
                    </div>
                </div>

                {{-- Step: details --}}
                <div v-else-if="step === 'details'">
                    <button type="button" @click="step = 'pick'" class="text-xs text-gray-400 mb-3"><span class="inline-block rtl:rotate-180">&larr;</span> @{{ t.back }}</button>
                    <h2 class="mb-3 text-base font-semibold dark:text-gray-100">@{{ t.yourDetails }}</h2>
                    <div class="mb-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-700 text-sm">
                        <div class="font-semibold dark:text-gray-100">@{{ selectedSlotLabel }}, @{{ localTime(selectedSlot) }}</div>
                        <div class="text-gray-400">@{{ tz }}</div>
                    </div>
                    <div v-if="error" class="mb-3 p-2 rounded bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300 text-sm">@{{ error }}</div>
                    <form @submit.prevent="submit" class="space-y-3 max-w-md">
                        <input type="text" style="display:none" v-model="website" tabindex="-1" autocomplete="off">
                        <input v-model="form.name" :placeholder="t.name" autocomplete="name" required class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
                        <span v-if="fieldErrors.name" class="block text-xs text-red-600 dark:text-red-400">@{{ fieldErrors.name }}</span>
                        <input v-model="form.email" type="email" inputmode="email" :placeholder="t.email" autocomplete="email" required class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
                        <span v-if="fieldErrors.email" class="block text-xs text-red-600 dark:text-red-400">@{{ fieldErrors.email }}</span>
                        <input v-if="askPhone" v-model="form.phone" type="tel" :placeholder="t.phone" autocomplete="tel" :required="requirePhone" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500">
                        <span v-if="fieldErrors.phone" class="block text-xs text-red-600 dark:text-red-400">@{{ fieldErrors.phone }}</span>
                        <textarea v-model="form.notes" :placeholder="t.notes" rows="3" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 dark:text-gray-100 dark:placeholder-gray-500"></textarea>
                        <div v-if="turnstileEnabled" id="turnstile-booking-widget"></div>
                        <button type="submit" :disabled="submitting" class="w-full py-3 rounded-lg text-white font-semibold disabled:opacity-60" :style="'background-color:' + accent">@{{ ctaLabel }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if (\App\Utils\TurnstileUtils::isActiveForRequest())
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
                        nextAvailable: null,
                        fieldErrors: {},
                        submitting: false,
                        website: '',
                        turnstileToken: '',
                        turnstileWidgetId: null,
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
                            labels.push(new Intl.DateTimeFormat(undefined, { weekday: 'short', timeZone: 'UTC' }).format(dt));
                        }
                        return labels;
                    },
                    monthLabel() {
                        if (!this.month) return '';
                        var parts = this.month.split('-');
                        return new Intl.DateTimeFormat(undefined, { month: 'long', year: 'numeric', timeZone: 'UTC' }).format(new Date(Date.UTC(+parts[0], +parts[1] - 1, 1)));
                    },
                    selectedDateLabel() {
                        return this.dayLabel(this.selectedDate);
                    },
                    selectedSlotLabel() {
                        return this.selectedSlot ? this.dayLabel(this.localDate(this.selectedSlot)) : '';
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
                        return (this.visitorDays[this.selectedDate] || []).length > 16;
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
                    dayLabel(ymd) {
                        if (!ymd) return '';
                        var p = ymd.split('-');
                        return new Intl.DateTimeFormat(undefined, { weekday: 'long', month: 'long', day: 'numeric', timeZone: 'UTC' }).format(new Date(Date.UTC(+p[0], +p[1] - 1, +p[2])));
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
                        return new Intl.DateTimeFormat(undefined, { timeZone: this.tz, hour: 'numeric', minute: '2-digit', hour12: !this.use24 }).format(new Date(utc));
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
                    async fetchMonth(monthStr) {
                        if (this.fetched[monthStr]) return;
                        this.loading = true;
                        try {
                            var res = await fetch(this.slotsUrl + '?from=' + monthStr + '-01&days=31', { headers: { 'Accept': 'application/json' } });
                            var data = await res.json();
                            this.mergeDays(data);
                            this.fetched[monthStr] = true;
                        } catch (e) {}
                        this.loading = false;
                    },
                    changeMonth(delta) {
                        var parts = this.month.split('-'); var y = +parts[0]; var m = +parts[1] - 1 + delta;
                        var nd = new Date(Date.UTC(y, m, 1));
                        this.month = nd.getUTCFullYear() + '-' + String(nd.getUTCMonth() + 1).padStart(2, '0');
                        this.fetchMonth(this.month);
                    },
                    selectDate(date) { this.selectedDate = date; this.armed = null; this.error = ''; },
                    armSlot(u) { this.armed = (this.armed === u) ? null : u; },
                    confirmSlot(u) { this.selectedSlot = u; this.step = 'details'; this.error = ''; },
                    jumpToNext() {
                        if (!this.nextAvailable) return;
                        this.month = this.nextAvailable.slice(0, 7);
                        this.fetchMonth(this.month);
                        this.selectedDate = this.nextAvailable;
                    },
                    buildTzList() {
                        try { this.tzList = Intl.supportedValuesOf('timeZone'); }
                        catch (e) { this.tzList = [this.scheduleTz, this.tz]; }
                    },
                    async submit() {
                        this.submitting = true; this.error = ''; this.fieldErrors = {};
                        var fd = new FormData();
                        fd.append('name', this.form.name); fd.append('email', this.form.email);
                        if (this.form.phone) fd.append('phone', this.form.phone);
                        if (this.form.notes) fd.append('notes', this.form.notes);
                        fd.append('slot', this.selectedSlot); fd.append('guest_timezone', this.tz);
                        fd.append('website', this.website);
                        if (this.turnstileEnabled) fd.append('cf-turnstile-response', this.turnstileToken);
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
                        this.$watch('step', function (v) { if (v === 'details') setTimeout(renderWidget, 50); });
                    }
                    this.tz = Intl.DateTimeFormat().resolvedOptions().timeZone || this.scheduleTz;
                    this.mergeDays(this.initial);
                    this.buildTzList();
                    var dates = Object.keys(this.visitorDays).sort();
                    if (dates.length) { this.selectedDate = dates[0]; this.month = dates[0].slice(0, 7); }
                    else {
                        var now = new Date();
                        this.month = now.getUTCFullYear() + '-' + String(now.getUTCMonth() + 1).padStart(2, '0');
                    }
                },
            }).mount('#booking-app');
        })();
    </script>
</x-app-guest-layout>
