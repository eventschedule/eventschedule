<template>
    <div class="vma" :dir="state.rtl ? 'rtl' : 'ltr'">
        <!-- ============ 1. Setting up ============ -->
        <section v-if="!state.exists" class="vma-card vma-setup">
            <div class="vma-setup-head">
                <h2>{{ t.setup_title }}</h2>
                <p>{{ t.setup_lead }}</p>
            </div>

            <fieldset class="vma-field">
                <legend>{{ t.kind_question }}</legend>
                <div class="vma-chips" role="radiogroup">
                    <button v-for="(label, k) in state.kinds" :key="k" type="button" role="radio" class="vma-chip"
                            :aria-checked="setup.kind === k ? 'true' : 'false'" :class="{ 'is-on': setup.kind === k }" @click="setup.kind = k">{{ label }}</button>
                </div>
            </fieldset>

            <fieldset v-if="state.events.length" class="vma-field">
                <legend>{{ t.event_question }}</legend>
                <div class="vma-chips">
                    <button v-for="e in state.events" :key="e.id" type="button" class="vma-chip" :class="{ 'is-on': setup.event === e.id }"
                            @click="setup.event = setup.event === e.id ? null : e.id"><bdi>{{ e.name }}</bdi></button>
                    <button type="button" class="vma-chip" :class="{ 'is-on': setup.event === null }" @click="setup.event = null">{{ t.new_event }}</button>
                </div>
            </fieldset>

            <template v-if="setup.event === null">
                <label class="vma-field">
                    <span>{{ t.market_name }}</span>
                    <input type="text" v-model="setup.name" maxlength="200" dir="auto" :placeholder="state.scheduleName" />
                </label>
                <fieldset class="vma-field">
                    <legend>{{ setup.kind === 'expo' ? t.dates_question : t.days_question }}</legend>
                    <div v-if="setup.kind !== 'expo'" class="vma-days">
                        <button v-for="d in weekdays" :key="d.n" type="button" class="vma-day" :class="{ 'is-on': setup.days.includes(d.n) }"
                                :aria-pressed="setup.days.includes(d.n) ? 'true' : 'false'" @click="toggleDay(d.n)">{{ d.short }}</button>
                    </div>
                    <div class="vma-row">
                        <label class="vma-field is-small"><span>{{ setup.kind === 'expo' ? t.first_day : t.first_day }}</span><input type="text" v-model="setup.first_date" v-flat="{ options: dateOpts, set: (v) => (setup.first_date = v) }" /></label>
                        <label v-if="setup.kind === 'expo'" class="vma-field is-small"><span>{{ t.last_day }}</span><input type="text" v-model="setup.last_date" v-flat="{ options: dateOpts, set: (v) => (setup.last_date = v) }" /></label>
                        <label class="vma-field is-small"><span>{{ t.from_time }}</span><input type="text" v-model="setup.starts" v-flat="{ options: timeOpts, set: (v) => (setup.starts = v) }" /></label>
                        <label class="vma-field is-small"><span>{{ t.to_time }}</span><input type="text" v-model="setup.ends" v-flat="{ options: timeOpts, set: (v) => (setup.ends = v) }" /></label>
                    </div>
                    <label v-if="setup.kind !== 'expo'" class="vma-toggle">
                        <input type="checkbox" v-model="setup.weekly" class="sr-only" />
                        <span class="vma-switch" :class="{ 'is-on': setup.weekly }" aria-hidden="true"><i></i></span>
                        <span>{{ t.every_week }}</span>
                    </label>
                </fieldset>
            </template>

            <p v-if="error" class="vma-error" role="alert">{{ error }}</p>
            <div class="vma-actions">
                <button type="button" class="vma-primary" :disabled="busy || !canSetup" @click="createMap">
                    {{ t.continue }}
                    <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.25" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
                </button>
            </div>
        </section>

        <!-- ============ 2. No spots yet: photos or draw ============ -->
        <section v-else-if="!state.hasSpots || photosAgain" class="vma-card">
            <VendorMapPhotos v-if="state.aiAvailable" :t="t" :kicker="(state.scheduleName ? state.scheduleName + ' · ' : '') + (t.tab || '')" :urls="state.urls" :csrf-token="csrfToken" :resume="resume" />
            <div v-else class="vma-noai">
                <h2>{{ t.photos_title }}</h2>
                <p>{{ state.isPro ? t.no_ai : t.upgrade_ai }}</p>
                <div class="vma-actions">
                    <a v-if="!state.isPro" :href="state.urls.pricing" class="vma-ghost">{{ t.upgrade_link }}</a>
                    <a :href="state.urls.design" class="vma-primary">{{ t.draw_myself }}</a>
                </div>
            </div>
        </section>

        <!-- ============ 3. The market ============ -->
        <template v-else>
            <div v-if="state.registrationOpen === false" class="vma-warn" role="note">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                <span>{{ t.registration_off }}</span>
            </div>
            <div v-if="!state.event" class="vma-warn" role="note">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
                <span>{{ t.event_gone }}</span>
                <select class="vma-select" @change="chooseEvent($event.target.value)">
                    <option value="">{{ t.choose_event }}</option>
                    <option v-for="e in state.events" :key="e.id" :value="e.id">{{ e.name }}</option>
                </select>
            </div>

            <!-- Two links, two jobs. -->
            <div class="vma-cards">
                <section class="vma-card vma-share">
                    <div class="vma-share-head">
                        <span class="vma-icon is-blue" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 21l1.6-4.8A8.5 8.5 0 1112 20.5a8.4 8.4 0 01-4.2-1.1L3 21z" stroke-linejoin="round" /></svg></span>
                        <div>
                            <h3>{{ state.strings.for_people }}</h3>
                            <p v-if="state.isPro">{{ t.vendors_can_join_lead || '' }}</p>
                        </div>
                    </div>
                    <template v-if="state.isPro">
                        <template v-if="state.claimUrl">
                            <textarea v-model="whatsappText" rows="3" dir="auto" class="vma-message" :aria-label="t.edit_message"></textarea>
                            <div class="vma-actions is-start">
                                <button type="button" class="vma-ghost" @click="copy(state.claimUrl)">{{ copied === state.claimUrl ? t.copied : t.copy_link }}</button>
                                <a :href="whatsappHref" target="_blank" rel="noopener" class="vma-primary is-whatsapp">{{ t.send_whatsapp }}</a>
                            </div>
                            <div class="vma-link-row">
                                <label class="vma-toggle">
                                    <input type="checkbox" :checked="state.claimsOpen" class="sr-only" @change="linkAction(state.claimsOpen ? 'close' : 'open')" />
                                    <span class="vma-switch" :class="{ 'is-on': state.claimsOpen }" aria-hidden="true"><i></i></span>
                                    <span>{{ t.vendors_can_join }}</span>
                                </label>
                                <button type="button" class="vma-link" @click="rotate">{{ t.make_new_link }}</button>
                            </div>
                        </template>
                        <button v-else type="button" class="vma-primary" @click="linkAction('make')">{{ t.send_whatsapp }}</button>
                    </template>
                    <template v-else>
                        <p class="vma-muted">{{ t.upgrade_ai }}</p>
                        <a :href="state.urls.pricing" class="vma-ghost">{{ t.upgrade_link }}</a>
                    </template>
                </section>

                <section class="vma-card vma-share">
                    <div class="vma-share-head">
                        <span class="vma-icon is-sky" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h2v2h-2zM18 18h2v2h-2zM14 18h2M18 14h2" stroke-linejoin="round" /></svg></span>
                        <div>
                            <h3>{{ t.for_shoppers }}</h3>
                            <p>{{ readiness }}</p>
                        </div>
                    </div>
                    <div class="vma-actions is-start">
                        <a :href="state.publicUrl" target="_blank" rel="noopener" class="vma-ghost">{{ t.see_what_shoppers_see }}</a>
                        <button type="button" class="vma-ghost" @click="copy(state.publicUrl)">{{ copied === state.publicUrl ? t.copied : t.copy_link }}</button>
                        <button type="button" class="vma-ghost" @click="showEmbed = !showEmbed">{{ t.embed }}</button>
                        <a :href="state.urls.sign" target="_blank" rel="noopener" class="vma-primary">{{ t.print_sign }}</a>
                    </div>
                    <div v-if="showEmbed" class="vma-embed">
                        <p class="vma-muted">{{ t.embed_help }}</p>
                        <code dir="ltr">{{ embedCode }}</code>
                        <button type="button" class="vma-link" @click="copy(embedCode)">{{ copied === embedCode ? t.copied : t.copy_link }}</button>
                    </div>
                </section>
            </div>

            <!-- The market day. -->
            <section class="vma-card vma-market-day">
                <div class="vma-day-head">
                    <div class="vma-dates" role="tablist" :aria-label="t.date">
                        <button v-for="d in day.dates" :key="d.date" type="button" role="tab" class="vma-date"
                                :aria-selected="d.date === day.date ? 'true' : 'false'" :class="{ 'is-on': d.date === day.date }" @click="loadDay(d.date)">{{ d.label }}</button>
                    </div>
                    <div class="vma-day-tools">
                        <a :href="state.urls.design" class="vma-ghost">{{ t.edit_map }}</a>
                        <button v-if="day.open" type="button" class="vma-ghost" @click="cancelOpen = true">{{ t.cancel_day }}</button>
                    </div>
                </div>

                <p class="vma-counts" aria-live="polite">
                    <span v-if="!day.open && day.cancelled" class="vma-pill is-red">{{ t.cancelled_banner }}</span>
                    <button v-if="!day.open && day.cancelled" type="button" class="vma-ghost" :disabled="busy" @click="reopenDay">{{ t.reopen_day }}</button>
                    <span v-else-if="!day.open" class="vma-pill">{{ t.no_market_day }}</span>
                    <template v-else>
                        <span class="vma-pill is-green">{{ fill(t.here_count, day.counts.present) }}</span>
                        <span v-if="day.counts.absent" class="vma-pill">{{ fill(t.not_coming_count, day.counts.absent) }}</span>
                        <span v-if="day.counts.free" class="vma-pill">{{ fill(t.free_count, day.counts.free) }}</span>
                    </template>
                    <button v-if="day.counts.waiting" type="button" class="vma-pill is-blue" @click="selectedSpot = null; reviewing = true">{{ fill(t.waiting_count, day.counts.waiting) }}</button>
                </p>

                <div class="vma-day-body">
                    <div class="vma-mapwrap">
                        <MapCanvas
                            :width="structure.width" :height="structure.height"
                            :spots="mapSpots" :areas="structure.areas" :landmarks="structure.landmarks"
                            :here="structure.here" :selected-id="selectedSpot" :highlight-ids="highlightIds"
                            :show-names="true" :category-icons="true" :rtl="state.rtl"
                            :labels="{ spot: state.strings.spot, absent: t.not_coming, zoomIn: t.zoom_in, zoomOut: t.zoom_out, fit: t.fit, here: t.you_are_here }"
                            :category-labels="state.categories" :wheel-needs-modifier="true"
                            @select="onSpot" @deselect="selectedSpot = null" />
                        <p v-if="moving" class="vma-moving" role="status">{{ t.move_help }}
                            <button type="button" class="vma-link" @click="moving = null">{{ t.cancel }}</button>
                        </p>
                    </div>

                    <aside class="vma-side" :class="{ 'is-open': sideOpen }">
                        <button type="button" class="vma-x vma-side-close" :aria-label="t.close" @click="selectedSpot = null; reviewing = false">&times;</button>

                        <!-- One stall. -->
                        <template v-if="selectedSpot && spotInfo">
                            <h3 class="vma-side-title">{{ state.strings.spot.replace(':number', spotInfo.label) }}</h3>

                            <div v-for="c in claimsFor(selectedSpot)" :key="c.id" class="vma-claim">
                                <ClaimCard :c="c" :t="t" :strings="state.strings" :categories="state.categories" :busy="busy"
                                           @approve="decide(c, 'approve')" @decline="decline(c)" @move="startMove(c)" />
                            </div>

                            <template v-if="spotInfo.occupant">
                                <div class="vma-vendor">
                                    <img v-if="spotInfo.occupant.photo" :src="spotInfo.occupant.photo" alt="" class="vma-avatar" loading="lazy" />
                                    <span v-else class="vma-avatar is-initial" aria-hidden="true">{{ initial(spotInfo.occupant.name) }}</span>
                                    <div>
                                        <p class="vma-vendor-name"><bdi>{{ spotInfo.occupant.name }}</bdi></p>
                                        <p class="vma-muted">{{ categoryLine(spotInfo.occupant) }}</p>
                                        <p v-if="spotInfo.state === 'absent'" class="vma-pill">{{ t.not_coming }}</p>
                                    </div>
                                </div>
                                <div class="vma-actions is-start">
                                    <a v-if="spotInfo.occupant.phone" :href="waLink(spotInfo.occupant.phone, '')" target="_blank" rel="noopener" class="vma-ghost">WhatsApp</a>
                                    <a v-if="spotInfo.occupant.url" :href="spotInfo.occupant.url" target="_blank" rel="noopener" class="vma-ghost">{{ t.visit_page }}</a>
                                    <button type="button" class="vma-ghost" @click="startMove(spotInfo.occupant)">{{ t.move_to }}</button>
                                    <button v-if="day.open && spotInfo.state === 'present' && !spotInfo.dated" type="button" class="vma-ghost" @click="decide(spotInfo.occupant, 'absent', { date: day.date })">{{ t.not_coming }}</button>
                                    <button v-if="spotInfo.state === 'absent'" type="button" class="vma-ghost" @click="decide(spotInfo.occupant, 'present', { date: day.date })">{{ t.coming_after_all }}</button>
                                    <button v-if="spotInfo.occupant.placeholder || spotInfo.dated" type="button" class="vma-danger" @click="decide(spotInfo.occupant, 'remove')">{{ t.remove_vendor }}</button>
                                </div>
                            </template>
                            <template v-else-if="day.open && !claimsFor(selectedSpot).length">
                                <p class="vma-muted">{{ t.free_spot }}</p>
                                <form class="vma-dayform" @submit.prevent="addForDay">
                                    <p class="vma-side-sub">{{ t.add_for_today }}</p>
                                    <label class="vma-field"><span>{{ t.name }}</span><input type="text" v-model="dayVendor.name" maxlength="120" dir="auto" required /></label>
                                    <label class="vma-field"><span>{{ t.category }}</span>
                                        <select v-model="dayVendor.category">
                                            <option value="">-</option>
                                            <option v-for="(label, slug) in state.categories" :key="slug" :value="slug">{{ label }}</option>
                                        </select>
                                    </label>
                                    <button type="submit" class="vma-primary" :disabled="busy || !dayVendor.name.trim()">{{ t.add }}</button>
                                </form>
                            </template>
                        </template>

                        <!-- The claims waiting, the conflicts first. A phone sees one at a time. -->
                        <template v-else-if="day.pending.length">
                            <div class="vma-review-head">
                                <h3 class="vma-side-title">{{ t.waiting_for_you }} <span class="vma-count">{{ day.pending.length }}</span></h3>
                                <button v-if="conflictFree.length" type="button" class="vma-primary is-small" :disabled="busy" @click="approveAll">
                                    {{ fill(t.approve_all, conflictFree.length) }}
                                </button>
                            </div>
                            <div v-if="isPhone" class="vma-onebyone">
                                <p class="vma-muted">{{ t.n_of_m.replace(':n', String(reviewIndex + 1)).replace(':m', String(sortedPending.length)) }}</p>
                                <ClaimCard v-if="sortedPending[reviewIndex]" :c="sortedPending[reviewIndex]" :t="t" :strings="state.strings" :categories="state.categories" :busy="busy" with-spot
                                           @approve="decide(sortedPending[reviewIndex], 'approve')" @decline="decline(sortedPending[reviewIndex])" @move="startMove(sortedPending[reviewIndex])"
                                           @show="selectedSpot = sortedPending[reviewIndex].spot_id" />
                            </div>
                            <ul v-else class="vma-claims">
                                <li v-for="c in sortedPending" :key="c.id">
                                    <ClaimCard :c="c" :t="t" :strings="state.strings" :categories="state.categories" :busy="busy" with-spot
                                               @approve="decide(c, 'approve')" @decline="decline(c)" @move="startMove(c)" @show="selectedSpot = c.spot_id" />
                                </li>
                            </ul>
                        </template>

                        <!-- Everyone, by area. -->
                        <template v-else>
                            <h3 class="vma-side-title">{{ state.strings.people }}</h3>
                            <input v-if="vendorList.length > 8" type="search" v-model="search" class="vma-search" :placeholder="t.search" dir="auto" />
                            <p v-if="!vendorList.length" class="vma-muted">{{ t.nobody_yet }}</p>
                            <ul class="vma-list">
                                <li v-for="v in filteredVendors" :key="v.spot">
                                    <button type="button" class="vma-list-row" @click="selectedSpot = v.spot">
                                        <span class="vma-num">{{ v.label }}</span>
                                        <bdi class="vma-list-name">{{ v.name }}</bdi>
                                        <span v-if="v.state === 'absent'" class="vma-pill">{{ t.not_coming }}</span>
                                    </button>
                                </li>
                            </ul>
                        </template>
                    </aside>
                </div>
            </section>

            <details class="vma-card vma-settings">
                <summary>{{ t.settings }}</summary>
                <div class="vma-row">
                    <label class="vma-field"><span>{{ t.kind }}</span>
                        <select :value="state.kind" @change="saveSettings({ kind: $event.target.value })">
                            <option v-for="(label, k) in state.kinds" :key="k" :value="k">{{ label }}</option>
                        </select>
                    </label>
                    <label class="vma-field"><span>{{ t.event }}</span>
                        <select :value="state.event || ''" @change="saveSettings({ event_id: $event.target.value || null })">
                            <option value="">-</option>
                            <option v-for="e in state.events" :key="e.id" :value="e.id">{{ e.name }}</option>
                        </select>
                    </label>
                </div>
                <div class="vma-actions is-start">
                    <button v-if="state.aiAvailable" type="button" class="vma-link" @click="photosAgain = true; scrollTo({ top: 0, behavior: 'smooth' })">{{ t.redo_with_photos }}</button>
                </div>
            </details>
        </template>

        <!-- Cancel a market day. -->
        <div v-if="cancelOpen" class="vma-dialog" role="dialog" aria-modal="true" :aria-label="t.cancel_day_title" @keydown.esc="cancelOpen = false">
            <div class="vma-dialog-card">
                <h2>{{ t.cancel_day_title }}</h2>
                <p class="vma-muted">{{ t.cancel_day_confirm.replace(':date', currentDateLabel) }}</p>
                <div class="vma-chips">
                    <button v-for="r in ['weather', 'holiday', 'other']" :key="r" type="button" class="vma-chip" :class="{ 'is-on': cancelReason === r }" @click="cancelReason = r">{{ t['reason_' + r] }}</button>
                </div>
                <div class="vma-actions">
                    <button type="button" class="vma-ghost" @click="cancelOpen = false">{{ t.close }}</button>
                    <button type="button" class="vma-danger is-solid" :disabled="busy" @click="cancelDay">{{ t.cancel_day }}</button>
                </div>
            </div>
        </div>

        <!-- Move: one week or the season. -->
        <div v-if="moveAsk" class="vma-dialog" role="dialog" aria-modal="true" :aria-label="t.move_to" @keydown.esc="moveAsk = null">
            <div class="vma-dialog-card">
                <h2>{{ moveAsk.swap ? t.swap : t.move_to }}</h2>
                <div class="vma-actions">
                    <button type="button" class="vma-ghost" @click="doMove(false)">{{ t.rest_of_season }}</button>
                    <button type="button" class="vma-primary" @click="doMove(true)">{{ t.this_week_only }}</button>
                </div>
            </div>
        </div>

        <div v-if="toast" class="vma-toast" role="status" aria-live="polite">
            <span>{{ toast.text }}</span>
            <a v-if="toast.whatsapp" :href="toast.whatsapp" target="_blank" rel="noopener">{{ t.tell_on_whatsapp.replace(':name', toast.name || '') }}</a>
            <button v-if="toast.undo" type="button" @click="undoLast">{{ t.undo }}</button>
        </div>
    </div>
</template>

<script setup>
import { computed, defineComponent, h, onMounted, reactive, ref, watch } from 'vue';
import MapCanvas from './MapCanvas.vue';
import VendorMapPhotos from './VendorMapPhotos.vue';

const props = defineProps({
    initial: { type: Object, required: true },
    t: { type: Object, required: true },
    csrfToken: { type: String, required: true },
    structure: { type: Object, default: null },
    day: { type: Object, default: null },
    resume: { type: Object, default: null },
});

const t = props.t;
const state = reactive({ ...props.initial });
// "Make the map again from photos": the photo step, over a map that already has spots (the
// designer then asks before it replaces anything).
const photosAgain = ref(false);
const scrollTo = (o) => window.scrollTo(o);

/** The app's date and time picker (Flatpickr, bundled in app.js) on a field bound with v-model. */
const vFlat = {
    mounted(el, binding) {
        if (!window.flatpickr) return;
        const lang = (document.documentElement.lang || 'en').split('-')[0];
        el._fp = window.flatpickr(el, {
            ...binding.value.options,
            locale: (window.flatpickrLocales || {})[lang] || 'default',
            defaultDate: el.value || null,
            onChange: (dates, str) => binding.value.set(str),
        });
    },
    unmounted(el) {
        if (el._fp) el._fp.destroy();
    },
};
const dateOpts = { dateFormat: 'Y-m-d', altInput: true, altFormat: 'M j, Y' };
const timeOpts = { enableTime: true, noCalendar: true, dateFormat: 'H:i', time_24hr: true };
const structure = reactive(props.structure || { width: 1000, height: 700, areas: [], spots: [], landmarks: [], here: null });
const day = reactive(props.day || { date: null, dates: [], open: false, spots: {}, pending: [], counts: { present: 0, absent: 0, free: 0, waiting: 0 } });

const busy = ref(false);
const error = ref('');
const selectedSpot = ref(null);
const moving = ref(null);
const moveAsk = ref(null);
const cancelOpen = ref(false);
const cancelReason = ref('weather');
const showEmbed = ref(false);
const copied = ref(null);
const search = ref('');
const reviewIndex = ref(0);
const reviewing = ref(false);
const toast = ref(null);
const dayVendor = reactive({ name: '', category: '' });
let toastTimer = null;

const isPhone = window.matchMedia('(max-width: 767px)').matches;

// ---- setup ----

const weekdays = computed(() => {
    const fmt = new Intl.DateTimeFormat(document.documentElement.lang || undefined, { weekday: 'short' });
    // 2023-01-01 was a Sunday: day n of that week is weekday n.
    return [0, 1, 2, 3, 4, 5, 6].map((n) => ({ n, short: fmt.format(new Date(2023, 0, 1 + n)) }));
});

const setup = reactive({
    kind: props.initial.preferKind || 'market',
    // A new market event by default: a venue's next concert is not its market. One of its own
    // events is a deliberate choice in the list.
    event: null,
    name: '',
    days: [],
    first_date: new Date().toISOString().slice(0, 10),
    last_date: '',
    starts: '08:00',
    ends: '14:00',
    weekly: true,
});

const canSetup = computed(() => setup.event !== null || (setup.kind === 'expo' ? !!setup.first_date : setup.days.length > 0));

function toggleDay(n) {
    const i = setup.days.indexOf(n);
    if (i >= 0) setup.days.splice(i, 1); else setup.days.push(n);
}

async function createMap() {
    const body = { kind: setup.kind };
    if (setup.event) {
        body.event_id = setup.event;
    } else {
        Object.assign(body, {
            name: setup.name,
            days: setup.kind === 'expo' || !setup.weekly ? [] : setup.days,
            starts: setup.starts,
            ends: setup.ends,
            first_date: setup.first_date || null,
            last_date: setup.kind === 'expo' ? (setup.last_date || null) : null,
        });
        if (setup.kind !== 'expo' && !setup.weekly && setup.days.length) {
            body.days = [];
        }
    }
    const data = await post(state.urls.setup, body);
    if (data?.props) Object.assign(state, data.props);
}

// ---- requests ----

async function post(url, body, { method = 'POST' } = {}) {
    busy.value = true;
    error.value = '';
    try {
        const res = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': props.csrfToken },
            body: JSON.stringify(body || {}),
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
            const msg = Object.values(data.errors || {})[0]?.[0] || data.message || t.couldnt_save;
            error.value = msg;
            showToast(msg);
            return null;
        }
        return data;
    } catch (e) {
        showToast(t.couldnt_save);
        return null;
    } finally {
        busy.value = false;
    }
}

function applyDay(data) {
    if (!data || !data.spots) return;
    Object.assign(day, {
        date: data.date, dates: data.dates, open: data.open, cancelled: data.cancelled,
        spots: data.spots, pending: data.pending, counts: data.counts,
    });
    state.waiting = data.counts.waiting;
    if (reviewIndex.value >= day.pending.length) reviewIndex.value = Math.max(0, day.pending.length - 1);
}

async function loadDay(date) {
    try {
        const res = await fetch(state.urls.day + '?date=' + encodeURIComponent(date), { headers: { Accept: 'application/json' } });
        applyDay(await res.json());
        selectedSpot.value = null;
    } catch (e) {
        showToast(t.couldnt_save);
    }
}

const lastDecision = ref(null);

async function decide(occupant, action, extra = {}) {
    const url = state.urls.decide.replace('__ID__', occupant.id).replace('__ACTION__', action);
    const data = await post(url, { ...extra, view_date: day.date });
    if (!data) return;
    applyDay(data);
    if (action === 'approve') {
        lastDecision.value = occupant;
        showToast(t.approved_toast.replace(':name', occupant.name), {
            undo: true,
            whatsapp: occupant.phone ? waLink(occupant.phone, t.whatsapp_approved
                .replace(':name', occupant.name)
                .replace(':spot', state.strings.spot.replace(':number', occupant.spot_label || ''))) : null,
            name: occupant.name,
        });
    } else if (action === 'decline') {
        lastDecision.value = occupant;
        showToast(t.declined_toast.replace(':name', occupant.name), { undo: true });
    }
}

function decline(c) {
    const note = window.prompt(t.decline_note, '');
    if (note === null) return;
    decide(c, 'decline', { note });
}

async function undoLast() {
    if (!lastDecision.value) return;
    const occ = lastDecision.value;
    lastDecision.value = null;
    toast.value = null;
    const data = await post(state.urls.decide.replace('__ID__', occ.id).replace('__ACTION__', 'undo'), { view_date: day.date });
    if (data) applyDay(data);
}

async function approveAll() {
    const data = await post(state.urls.approveAll, { view_date: day.date });
    if (data) {
        applyDay(data);
        showToast(t.approved_toast.replace(':name', String(data.approved?.length || 0)));
    }
}

function startMove(occupant) {
    moving.value = occupant;
    selectedSpot.value = null;
}

function onSpot(spotId) {
    if (moving.value) {
        const target = day.spots[spotId];
        moveAsk.value = { spot: spotId, swap: !!target?.occupant && target.state !== 'free' };
        return;
    }
    selectedSpot.value = spotId;
    dayVendor.name = '';
    dayVendor.category = '';
}

async function doMove(thisWeekOnly) {
    const occ = moving.value;
    const spot = moveAsk.value.spot;
    moveAsk.value = null;
    moving.value = null;
    // A waiting claim is moved by approving it onto the new stall: decline here and let the
    // vendor ask again is worse, so the claim's own spot changes through the same move.
    const data = await post(state.urls.move, { occupant: occ.id, spot, date: thisWeekOnly ? day.date : null, view_date: day.date });
    if (data) {
        applyDay(data);
        selectedSpot.value = spot;
    }
}

async function addForDay() {
    const data = await post(state.urls.dayVendor, { spot: selectedSpot.value, date: day.date, name: dayVendor.name.trim(), category: dayVendor.category || null });
    if (data) {
        applyDay(data);
        dayVendor.name = '';
    }
}

async function cancelDay() {
    const data = await post(state.urls.cancelDay, { date: day.date, reason: cancelReason.value });
    cancelOpen.value = false;
    if (data) applyDay(data);
}

async function reopenDay() {
    const data = await post(state.urls.reopenDay, { date: day.date });
    if (data) applyDay(data);
}

async function linkAction(action) {
    const data = await post(state.urls.link, { action });
    if (data?.props) Object.assign(state, data.props);
}

function rotate() {
    if (window.confirm(t.make_new_link_confirm)) linkAction('rotate');
}

async function saveSettings(body) {
    const data = await post(state.urls.settings, body);
    if (data?.props) {
        Object.assign(state, data.props);
        if (state.date) loadDay(state.date);
    }
}

function chooseEvent(id) {
    if (id) saveSettings({ event_id: id });
}

// ---- what is shown ----

const spotsById = computed(() => Object.fromEntries((structure.spots || []).map((s) => [s.id, s])));

const mapSpots = computed(() => (structure.spots || []).map((s) => {
    const d = day.spots[s.id] || { state: 'free', occupant: null };
    const waiting = day.pending.some((p) => p.spot_id === s.id);
    return {
        ...s,
        area: s.area_id,
        state: waiting && d.state === 'free' ? 'pending' : d.state,
        vendor: d.occupant ? { name: d.occupant.name, categories: d.occupant.categories, photo: d.occupant.photo } : (s.name ? { name: s.name, categories: s.category ? [s.category] : [] } : null),
        badge: waiting ? day.pending.filter((p) => p.spot_id === s.id).length : 0,
    };
}));

// Dimming the rest is for the phone's one-at-a-time review; on the overview a waiting claim's
// own dashed outline marks it, and dimming every other stall made the confirmed vendors look gone.
const highlightIds = computed(() => (reviewing.value && !moving.value ? day.pending.map((p) => p.spot_id) : null));

const spotInfo = computed(() => {
    const s = spotsById.value[selectedSpot.value];
    if (!s) return null;
    const d = day.spots[s.id] || { state: 'free', occupant: null, dated: false };
    return { label: s.label, state: d.state, dated: d.dated, occupant: d.occupant };
});

const sortedPending = computed(() => [...day.pending].sort((a, b) => (b.conflict || b.name_differs) - (a.conflict || a.name_differs)));
const conflictFree = computed(() => day.pending.filter((p) => !p.conflict && !p.name_differs));

function claimsFor(spotId) {
    return day.pending.filter((p) => p.spot_id === spotId);
}

const vendorList = computed(() => (structure.spots || []).map((s) => {
    const d = day.spots[s.id];
    if (!d?.occupant) return null;
    return { spot: s.id, label: s.label, name: d.occupant.name, state: d.state };
}).filter(Boolean).sort((a, b) => String(a.label).localeCompare(String(b.label), undefined, { numeric: true })));

const filteredVendors = computed(() => {
    const q = search.value.trim().toLocaleLowerCase();
    return q ? vendorList.value.filter((v) => v.name.toLocaleLowerCase().includes(q) || String(v.label).toLocaleLowerCase() === q) : vendorList.value;
});

const sideOpen = computed(() => !!selectedSpot.value || (isPhone && day.pending.length > 0 && reviewing.value));

const readiness = computed(() => {
    const total = (structure.spots || []).length;
    const named = (structure.spots || []).filter((s) => s.name).length;
    return t.readiness.replace(':named', String(named)).replace(':total', String(total));
});

const currentDateLabel = computed(() => day.dates.find((d) => d.date === day.date)?.label || day.date || '');

const whatsappText = ref('');
const whatsappHref = computed(() => 'https://wa.me/?text=' + encodeURIComponent(whatsappText.value));
const embedCode = computed(() => `<iframe src="${state.publicUrl}?embed=1" width="100%" height="560" style="border:0;border-radius:12px" loading="lazy" title="${state.scheduleName}"></iframe>`);

function waLink(phone, text) {
    return 'https://wa.me/' + String(phone).replace(/[^\d]/g, '') + (text ? '?text=' + encodeURIComponent(text) : '');
}

/**
 * Laravel's trans_choice, for a string the page was handed whole: "{0} ...|{1} ...|[2,*] ...".
 * Picks the part whose range holds n (the last part when none says), and fills :count.
 */
function fill(s, n) {
    const parts = String(s || '').split('|');
    let chosen = parts[parts.length - 1];
    for (const part of parts) {
        const exact = part.match(/^\s*\{(\d+)\}\s*/);
        const range = part.match(/^\s*\[(\d+),(\d+|\*)\]\s*/);
        if (exact && Number(exact[1]) === n) { chosen = part; break; }
        if (range && n >= Number(range[1]) && (range[2] === '*' || n <= Number(range[2]))) { chosen = part; break; }
    }
    return chosen.replace(/^\s*(\{\d+\}|\[\d+,(\d+|\*)\])\s*/, '').replace(':count', String(n));
}

function initial(name) {
    return (name || '?').trim().charAt(0).toUpperCase();
}

function categoryLine(o) {
    const cats = (o.categories || []).map((c) => state.categories[c]).filter(Boolean).join(', ');
    return [cats, o.sells].filter(Boolean).join(' · ');
}

async function copy(text) {
    try {
        await navigator.clipboard.writeText(text);
        copied.value = text;
        setTimeout(() => { if (copied.value === text) copied.value = null; }, 2000);
    } catch (e) { /* the text stays selectable */ }
}

function showToast(text, { undo = false, whatsapp = null, name = '' } = {}) {
    clearTimeout(toastTimer);
    toast.value = { text, undo, whatsapp, name };
    toastTimer = setTimeout(() => { toast.value = null; lastDecision.value = null; }, undo ? 10000 : 5000);
}

const messageFor = (link) => (t.whatsapp_message || '').replace(':link', link || '').replace(':name', state.scheduleName || '');

// The message always carries the link that works: made on the first press, or a new one after
// "Make a new link" (the old one no longer opens). Edits the organizer made are kept.
watch(() => state.claimUrl, (now, before) => {
    if (!now) return;
    if (before && whatsappText.value.includes(before)) {
        whatsappText.value = whatsappText.value.split(before).join(now);
    } else if (!whatsappText.value.includes(now)) {
        whatsappText.value = whatsappText.value === messageFor('') || !whatsappText.value.trim()
            ? messageFor(now)
            : whatsappText.value.trimEnd() + ' ' + now;
    }
});

onMounted(() => {
    whatsappText.value = messageFor(state.claimUrl);
    if (state.exists && state.hasSpots && !props.day && state.date) loadDay(state.date);
});

/** One claim: who, what, which stall, and the three answers (the forward one last). */
const ClaimCard = defineComponent({
    props: { c: Object, t: Object, strings: Object, categories: Object, busy: Boolean, withSpot: Boolean },
    emits: ['approve', 'decline', 'move', 'show'],
    setup(p, { emit }) {
        return () => {
            const c = p.c;
            const cats = (c.categories || []).map((k) => p.categories[k]).filter(Boolean).join(', ');
            return h('div', { class: ['vma-claimcard', { 'is-conflict': c.conflict || c.name_differs }] }, [
                h('div', { class: 'vma-claim-top' }, [
                    c.photo ? h('img', { src: c.photo, alt: '', class: 'vma-avatar', loading: 'lazy' }) : h('span', { class: 'vma-avatar is-initial', 'aria-hidden': 'true' }, (c.name || '?').charAt(0).toUpperCase()),
                    h('div', { class: 'vma-claim-who' }, [
                        h('p', { class: 'vma-vendor-name' }, [h('bdi', c.name)]),
                        h('p', { class: 'vma-muted' }, [cats, c.sells].filter(Boolean).join(' · ')),
                        p.withSpot ? h('button', { type: 'button', class: 'vma-link', onClick: () => emit('show') }, p.strings.spot.replace(':number', c.spot_label || '')) : null,
                        h('p', { class: 'vma-muted' }, c.dates ? p.t.on_dates.replace(':dates', c.dates.join(', ')) : p.strings.every_date),
                    ]),
                ]),
                c.conflict ? h('p', { class: 'vma-pill is-amber' }, p.t.two_want) : null,
                c.name_differs ? h('p', { class: 'vma-pill is-amber' }, p.t.name_differs) : null,
                h('div', { class: 'vma-actions is-start' }, [
                    c.phone ? h('a', { href: 'https://wa.me/' + String(c.phone).replace(/[^\d]/g, ''), target: '_blank', rel: 'noopener', class: 'vma-link' }, 'WhatsApp') : null,
                    h('button', { type: 'button', class: 'vma-ghost', disabled: p.busy, onClick: () => emit('decline') }, p.t.decline),
                    h('button', { type: 'button', class: 'vma-ghost', disabled: p.busy, onClick: () => emit('move') }, p.t.move_to),
                    h('button', { type: 'button', class: 'vma-primary is-small', disabled: p.busy, onClick: () => emit('approve') }, p.t.approve),
                ]),
            ]);
        };
    },
});
</script>

<style>
.vma { display: flex; flex-direction: column; gap: 1rem; }
.vma-card { background: rgb(var(--ap-surface)); border: 1px solid rgb(var(--ap-border)); border-radius: 1rem; padding: 1.25rem; box-shadow: var(--ap-shadow-card); }
.vma-setup { display: flex; flex-direction: column; gap: 1.1rem; max-width: 48rem; }
.vma-setup-head h2, .vma-noai h2 { font-size: 1.35rem; font-weight: 700; color: rgb(var(--ap-ink)); }
.vma-setup-head p, .vma-noai p { margin-top: 0.3rem; color: rgb(var(--ap-ink-2)); }
.vma-field { display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.875rem; color: rgb(var(--ap-ink-2)); border: 0; padding: 0; margin: 0; }
.vma-field legend { margin-bottom: 0.4rem; font-weight: 600; color: rgb(var(--ap-ink)); }
.vma-field > span { font-weight: 500; }
.vma-field input, .vma-field select, .vma-select { padding: 0.6rem 0.8rem !important; border-radius: 0.7rem; border: 1px solid rgb(var(--ap-border-strong)); background: rgb(var(--ap-bg)); color: rgb(var(--ap-ink)); font-size: 0.9375rem !important; }
.vma-field.is-small { min-width: 8rem; }
.vma-row { display: flex; flex-wrap: wrap; gap: 0.75rem; }
.vma-chips { display: flex; flex-wrap: wrap; gap: 0.5rem; }
.vma-chip { padding: 0.55rem 0.95rem; border-radius: 999px; border: 1px solid rgb(var(--ap-border-strong)); font-size: 0.9rem; font-weight: 500; color: rgb(var(--ap-ink)); background: rgb(var(--ap-surface)); transition: all 0.2s; }
.vma-chip:hover { border-color: var(--brand-blue); }
.vma-chip.is-on { background: var(--brand-button-bg); border-color: transparent; color: #fff; }
.vma-days { display: flex; gap: 0.4rem; flex-wrap: wrap; }
.vma-day { width: 3.1rem; height: 3.1rem; border-radius: 0.9rem; border: 1px solid rgb(var(--ap-border-strong)); font-weight: 600; font-size: 0.85rem; color: rgb(var(--ap-ink)); transition: all 0.2s; }
.vma-day.is-on { background: var(--brand-button-bg); color: #fff; border-color: transparent; box-shadow: 0 6px 16px -8px rgba(78, 129, 250, 0.8); }
.vma-toggle { display: inline-flex; align-items: center; gap: 0.6rem; font-size: 0.9rem; color: rgb(var(--ap-ink)); cursor: pointer; }
.vma-switch { position: relative; width: 2.6rem; height: 1.5rem; border-radius: 999px; background: rgb(var(--ap-border-strong)); transition: background 0.2s; flex-shrink: 0; }
.vma-switch i { position: absolute; top: 0.2rem; inset-inline-start: 0.2rem; width: 1.1rem; height: 1.1rem; border-radius: 999px; background: #fff; transition: inset-inline-start 0.2s; box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2); }
.vma-switch.is-on { background: var(--brand-button-bg); }
.vma-switch.is-on i { inset-inline-start: 1.3rem; }
.vma-toggle input:focus-visible + .vma-switch { outline: 2px solid var(--brand-blue); outline-offset: 2px; }
.vma-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; justify-content: flex-end; align-items: center; }
.vma-actions.is-start { justify-content: flex-start; }
.vma-primary { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.75rem 1rem; border-radius: 0.75rem; font-weight: 600; font-size: 1rem; color: #fff; background: var(--brand-button-bg); transition: all 0.2s; }
.vma-primary:hover:not(:disabled) { background: var(--brand-button-bg-hover); }
.vma-primary:disabled { opacity: 0.5; }
.vma-primary.is-small { padding: 0.5rem 0.85rem; font-size: 0.875rem; }
.vma-primary.is-whatsapp { background: #16a34a; }
.vma-primary.is-whatsapp:hover { background: #15803d; }
.vma-ghost { display: inline-flex; align-items: center; padding: 0.55rem 0.85rem; border-radius: 0.7rem; border: 1px solid rgb(var(--ap-border-strong)); font-size: 0.875rem; font-weight: 500; color: rgb(var(--ap-ink)); background: rgb(var(--ap-surface)); transition: all 0.2s; }
.vma-ghost:hover:not(:disabled) { background: rgb(var(--ap-surface-hover)); }
.vma-danger { padding: 0.55rem 0.85rem; border-radius: 0.7rem; font-size: 0.875rem; font-weight: 500; color: #dc2626; border: 1px solid rgba(220, 38, 38, 0.35); }
.vma-danger.is-solid { background: #dc2626; color: #fff; border-color: transparent; padding: 0.75rem 1rem; font-weight: 600; }
.vma-link { font-size: 0.875rem; font-weight: 500; color: var(--brand-blue); align-self: flex-start; text-align: start; }
.vma-muted { font-size: 0.875rem; color: rgb(var(--ap-ink-3)); }
.vma-error { color: #b91c1c; font-size: 0.875rem; }
.vma-warn { display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; padding: 0.75rem; border-radius: 0.6rem; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; font-size: 0.875rem; }
.dark .vma-warn { background: rgba(120, 53, 15, 0.2); border-color: #b45309; color: #fde68a; }
.vma-cards { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; }
.vma-share { display: flex; flex-direction: column; gap: 0.85rem; }
.vma-share-head { display: flex; gap: 0.75rem; align-items: flex-start; }
.vma-share-head h3 { font-size: 1.05rem; font-weight: 700; color: rgb(var(--ap-ink)); }
.vma-share-head p { font-size: 0.875rem; color: rgb(var(--ap-ink-2)); }
.vma-icon { display: flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; flex-shrink: 0; }
.vma-icon svg { width: 1.25rem; height: 1.25rem; }
.vma-icon.is-blue { background: rgba(78, 129, 250, 0.1); color: var(--brand-blue); }
.vma-icon.is-sky { background: rgba(14, 165, 233, 0.1); color: #0284c7; }
.dark .vma-icon.is-sky { color: #38bdf8; }
.vma-message { width: 100%; border-radius: 0.75rem; border: 1px solid rgb(var(--ap-border-strong)); background: rgb(var(--ap-bg)); color: rgb(var(--ap-ink)); padding: 0.7rem 0.8rem; font-size: 0.9rem; }
.vma-link-row { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap; padding-top: 0.6rem; border-top: 1px solid var(--ap-hairline, rgb(var(--ap-border))); }
.vma-embed { display: flex; flex-direction: column; gap: 0.4rem; }
.vma-embed code { display: block; padding: 0.6rem; border-radius: 0.6rem; background: rgb(var(--ap-bg)); font-size: 0.75rem; overflow-x: auto; white-space: nowrap; color: rgb(var(--ap-ink-2)); }
.vma-market-day { display: flex; flex-direction: column; gap: 0.85rem; }
.vma-day-head { display: flex; justify-content: space-between; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
.vma-dates { display: flex; gap: 0.35rem; overflow-x: auto; padding: 0.25rem; background: rgb(var(--ap-bg)); border-radius: 0.9rem; max-width: 100%; }
.vma-date { padding: 0.45rem 0.8rem; border-radius: 0.65rem; font-size: 0.85rem; font-weight: 500; white-space: nowrap; color: rgb(var(--ap-ink-2)); transition: all 0.2s; }
.vma-date.is-on { background: rgb(var(--ap-surface)); color: rgb(var(--ap-ink)); box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.12); }
.vma-day-tools { display: flex; gap: 0.5rem; }
.vma-counts { display: flex; flex-wrap: wrap; gap: 0.4rem; }
.vma-pill { display: inline-flex; align-items: center; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.78rem; font-weight: 600; background: rgb(var(--ap-bg)); color: rgb(var(--ap-ink-2)); }
.vma-pill.is-green { background: #ecfdf5; color: #047857; }
.vma-pill.is-blue { background: rgba(78, 129, 250, 0.12); color: var(--brand-blue); }
.vma-pill.is-red { background: #fef2f2; color: #b91c1c; }
.vma-pill.is-amber { background: #fffbeb; color: #b45309; }
.dark .vma-pill.is-green { background: rgba(16, 185, 129, 0.15); color: #6ee7b7; }
.dark .vma-pill.is-red { background: rgba(239, 68, 68, 0.15); color: #fca5a5; }
.dark .vma-pill.is-amber { background: rgba(245, 158, 11, 0.15); color: #fcd34d; }
.vma-day-body { display: grid; grid-template-columns: minmax(0, 1fr) 22rem; gap: 1rem; min-height: 30rem; }
.vma-mapwrap { position: relative; border-radius: 0.9rem; overflow: hidden; border: 1px solid rgb(var(--ap-border)); min-height: 30rem; display: flex; }
.vma-mapwrap > :first-child { flex: 1; }
.vma-moving { position: absolute; top: 0.75rem; left: 50%; transform: translateX(-50%); padding: 0.5rem 0.9rem; border-radius: 999px; background: #111827; color: #fff; font-size: 0.85rem; display: flex; gap: 0.75rem; align-items: center; }
.vma-moving .vma-link { color: #93c5fd; }
.vma-side { position: relative; display: flex; flex-direction: column; gap: 0.75rem; overflow-y: auto; max-height: 38rem; padding-inline-end: 0.25rem; }
.vma-side-close { display: none; }
.vma-side-title { font-size: 1.05rem; font-weight: 700; color: rgb(var(--ap-ink)); display: flex; align-items: center; gap: 0.5rem; }
.vma-side-sub { font-weight: 600; color: rgb(var(--ap-ink)); font-size: 0.9rem; }
.vma-count { display: inline-flex; min-width: 1.6rem; height: 1.6rem; align-items: center; justify-content: center; border-radius: 999px; background: var(--brand-button-bg); color: #fff; font-size: 0.8rem; }
.vma-review-head { display: flex; justify-content: space-between; align-items: center; gap: 0.5rem; flex-wrap: wrap; }
.vma-claims, .vma-list { display: flex; flex-direction: column; gap: 0.6rem; }
.vma-claimcard { display: flex; flex-direction: column; gap: 0.6rem; padding: 0.85rem; border-radius: 0.9rem; background: rgb(var(--ap-bg)); border: 1px solid rgb(var(--ap-border)); }
.vma-claimcard .vma-actions { gap: 0.4rem; }
.vma-claimcard .vma-ghost { padding: 0.45rem 0.7rem; font-size: 0.8125rem; }
.vma-claimcard .vma-primary.is-small { padding: 0.45rem 0.8rem; font-size: 0.8125rem; }
.vma-claimcard .vma-link { margin-inline-end: auto; }
.vma-claimcard.is-conflict { border-color: #fcd34d; }
.vma-claim-top { display: flex; gap: 0.7rem; align-items: flex-start; }
.vma-claim-who { display: flex; flex-direction: column; gap: 0.15rem; min-width: 0; }
.vma-vendor { display: flex; gap: 0.75rem; align-items: center; }
.vma-avatar { width: 3rem; height: 3rem; border-radius: 0.8rem; object-fit: cover; flex-shrink: 0; }
.vma-avatar.is-initial { display: flex; align-items: center; justify-content: center; background: rgba(78, 129, 250, 0.12); color: var(--brand-blue); font-weight: 700; font-size: 1.2rem; }
.vma-vendor-name { font-weight: 600; color: rgb(var(--ap-ink)); }
.vma-search { padding: 0.55rem 0.8rem !important; border-radius: 0.7rem; border: 1px solid rgb(var(--ap-border-strong)); background: rgb(var(--ap-bg)); color: rgb(var(--ap-ink)); }
.vma-list-row { display: flex; align-items: center; gap: 0.6rem; width: 100%; padding: 0.5rem; border-radius: 0.6rem; text-align: start; transition: background 0.2s; }
.vma-list-row:hover { background: rgb(var(--ap-surface-hover)); }
.vma-num { min-width: 2.2rem; height: 2.2rem; display: inline-flex; align-items: center; justify-content: center; border-radius: 0.55rem; background: rgb(var(--ap-bg)); font-weight: 700; font-size: 0.85rem; color: rgb(var(--ap-ink)); }
.vma-list-name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: rgb(var(--ap-ink)); }
.vma-dayform { display: flex; flex-direction: column; gap: 0.6rem; }
.vma-settings summary { cursor: pointer; font-weight: 600; color: rgb(var(--ap-ink)); }
.vma-settings[open] summary { margin-bottom: 0.85rem; }
.vma-x { width: 2rem; height: 2rem; border-radius: 0.5rem; font-size: 1.25rem; line-height: 1; color: rgb(var(--ap-ink-3)); }
.vma-dialog { position: fixed; inset: 0; z-index: 60; display: flex; align-items: center; justify-content: center; background: rgba(0, 0, 0, 0.4); padding: 1rem; }
.vma-dialog-card { width: 100%; max-width: 28rem; background: rgb(var(--ap-surface)); border-radius: 1.25rem; padding: 1.25rem; display: flex; flex-direction: column; gap: 0.9rem; box-shadow: var(--ap-shadow-card); }
.vma-dialog-card h2 { font-size: 1.15rem; font-weight: 700; color: rgb(var(--ap-ink)); }
.vma-toast { position: fixed; bottom: 1.25rem; left: 50%; transform: translateX(-50%); z-index: 70; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; justify-content: center; padding: 0.8rem 1.1rem; border-radius: 0.9rem; background: #111827; color: #fff; font-size: 0.9rem; box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5); animation: vma-rise 0.25s ease backwards; max-width: calc(100% - 2rem); }
.vma-toast a { font-weight: 700; color: #86efac; }
.vma-toast button { font-weight: 700; color: #93c5fd; }
@keyframes vma-rise { from { opacity: 0; transform: translate(-50%, 8px); } }
.vma-noai { display: flex; flex-direction: column; gap: 1rem; }
@media (max-width: 1023px) {
    .vma-cards { grid-template-columns: 1fr; }
    .vma-day-body { grid-template-columns: 1fr; }
    .vma-mapwrap { min-height: 55vh; }
}
@media (max-width: 767px) {
    .vma-side { position: fixed; inset-inline: 0; bottom: 0; z-index: 50; max-height: 70vh; background: rgb(var(--ap-surface)); border-radius: 1.25rem 1.25rem 0 0; padding: 1.25rem 1rem; transform: translateY(100%); transition: transform 0.25s ease; box-shadow: 0 -10px 30px -12px rgba(0, 0, 0, 0.35); }
    .vma-side.is-open { transform: none; }
    .vma-side-close { display: block; position: absolute; top: 0.75rem; inset-inline-end: 0.75rem; }
    .vma-day-tools { width: 100%; }
}
@media (prefers-reduced-motion: reduce) {
    .vma-toast { animation: none; }
    .vma-side { transition: none; }
}
</style>
