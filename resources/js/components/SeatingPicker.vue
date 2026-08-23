<template>
    <div class="mt-3">
        <!-- Default path: quantity + best available. Most buyers want N seats together, not a
             floor plan, and this is also the answer on a phone and without a pointer. -->
        <div v-if="mode === 'quantity'" class="flex flex-wrap items-end gap-3">
            <div>
                <label :for="`seatqty-${ticket.id}`" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ t.howMany }}
                </label>
                <select :id="`seatqty-${ticket.id}`" v-model.number="qty" @change="pickBest"
                    class="mt-1 rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] shadow-sm">
                    <option :value="0">0</option>
                    <option v-for="n in maxSelectable" :key="n" :value="n">{{ n }}</option>
                </select>
            </div>
            <button :id="`seatpick-${ticket.id}`" type="button" @click="openMap"
                class="text-sm font-medium text-[var(--brand-blue)] hover:underline pb-2">
                {{ t.chooseOwn }}
            </button>
        </div>

        <!-- Map / list -->
        <div v-else class="rounded-xl border border-gray-200 dark:border-gray-700 p-3">
            <div class="flex flex-wrap items-center gap-3 mb-3">
                <button type="button" @click="closeMap" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">
                    &larr; {{ t.backToQuantity }}
                </button>
                <div class="flex items-center gap-1 rounded-lg bg-gray-100 dark:bg-gray-800 p-1">
                    <button type="button" @click="mode = 'map'" :aria-pressed="mode === 'map'"
                        class="px-2 py-1 rounded text-xs transition-all duration-200"
                        :class="mode === 'map'
                            ? 'bg-white dark:bg-gray-700 shadow-sm text-gray-900 dark:text-gray-100'
                            : 'text-gray-500 dark:text-gray-400'">{{ t.mapView }}</button>
                    <button type="button" @click="mode = 'list'" :aria-pressed="mode === 'list'"
                        class="px-2 py-1 rounded text-xs transition-all duration-200"
                        :class="mode === 'list'
                            ? 'bg-white dark:bg-gray-700 shadow-sm text-gray-900 dark:text-gray-100'
                            : 'text-gray-500 dark:text-gray-400'">{{ t.listView }}</button>
                </div>
                <!-- Only worth showing when there is somewhere else to go. -->
                <div v-if="myLevels.length > 1" class="flex items-center gap-1 rounded-lg bg-gray-100 dark:bg-gray-800 p-1">
                    <button v-for="lvl in myLevels" :key="lvl.id" type="button" @click="activeLevelId = lvl.id"
                        :aria-pressed="lvl.id === activeLevel?.id"
                        class="px-2 py-1 rounded text-xs transition-all duration-200"
                        :class="lvl.id === activeLevel?.id
                            ? 'bg-white dark:bg-gray-700 shadow-sm text-gray-900 dark:text-gray-100'
                            : 'text-gray-500 dark:text-gray-400'">{{ lvl.name || t.level }}</button>
                </div>

                <div v-show="mode === 'map'" class="flex items-center gap-1 rounded-lg bg-gray-100 dark:bg-gray-800 p-1">
                    <button type="button" @click="zoomBy(-0.15)" class="px-2 py-1 rounded text-sm text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200" :aria-label="t.zoomOut">&minus;</button>
                    <button type="button" @click="zoomBy(0.15)" class="px-2 py-1 rounded text-sm text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200" :aria-label="t.zoomIn">+</button>
                    <button type="button" @click="fit" class="px-2 py-1 rounded text-xs text-gray-600 dark:text-gray-300 hover:bg-white dark:hover:bg-gray-700 transition-all duration-200">{{ t.fit }}</button>
                </div>

                <span v-if="countdown" class="ms-auto text-sm" :class="countdownSoon ? 'text-amber-600 dark:text-amber-400' : 'text-gray-500 dark:text-gray-400'">
                    {{ t.holdingFor }} {{ countdown }}
                    <button v-if="countdownSoon" type="button" @click="pushHold" class="ms-2 text-[var(--brand-blue)] hover:underline">{{ t.moreTime }}</button>
                </span>
            </div>

            <!-- A lapsed hold is a thing that happened TO the buyer, so it says so rather than
                 letting the seats quietly leave the selection. -->
            <div v-if="lapsed" class="mb-3 rounded-lg border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-3 text-sm text-amber-800 dark:text-amber-200" role="status">
                {{ t.holdLapsed }}
                <button type="button" @click="pickAgain" class="ms-2 font-medium underline">{{ t.pickAgain }}</button>
            </div>

            <p v-if="error" class="mb-3 rounded-lg border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-2 text-sm text-amber-800 dark:text-amber-200">
                {{ error }}
            </p>

            <p v-if="loading" class="text-sm text-gray-500 dark:text-gray-400">{{ t.loading }}</p>

            <template v-else-if="mySections.length">
                <!-- MAP -->
                <div v-show="mode === 'map'">
                    <svg ref="svgEl" v-bind="bind" :viewBox="viewBox" class="w-full select-none touch-none"
                        :style="{ height: mapHeight, cursor: 'grab' }" role="group" :aria-label="t.mapLabel">
                        <defs>
                            <pattern id="seatTakenHatch" width="4" height="4" patternUnits="userSpaceOnUse" patternTransform="rotate(45)">
                                <line x1="0" y1="0" x2="0" y2="4" stroke="#6b7280" stroke-width="1.6" />
                            </pattern>
                        </defs>
                        <g :transform="`translate(${pan.x} ${pan.y}) scale(${zoom})`">
                        <g v-for="s in mySections" :key="s.id" :transform="`translate(${s.x} ${s.y})`">
                            <text :x="0" :y="-10" font-size="12" class="fill-gray-500 dark:fill-gray-400">
                                {{ s.name }}<tspan v-if="props.priceLabel"> &middot; {{ priceLabel }}</tspan>
                            </text>
                            <!-- Row labels sit beside the row, the way the list view already names them. -->
                            <template v-if="showSeatLabels">
                                <text v-for="row in rowsOf(s)" :key="`rl-${row.key}`"
                                    :x="rowLabelX(s, row) - 16" :y="rowLabelY(s, row) + 4"
                                    text-anchor="end" font-size="9" class="fill-gray-400 dark:fill-gray-500">{{ row.key.split('-').pop() }}</text>
                            </template>
                            <g v-for="seat in s.seats" :key="seat.id"
                                :transform="`translate(${seatX(s, seat)} ${seatY(s, seat)})`">
                                <circle :id="`seat-${uid}-${seat.id}`" r="9" :fill="fillFor(s, seat)" :stroke="strokeFor(s, seat)" stroke-width="1.5"
                                    :stroke-dasharray="seat.kind === 'companion' ? '3 2' : null"
                                    :class="seat.state === 'taken' ? '' : 'cursor-pointer'"
                                    role="button"
                                    :tabindex="seat.id === focusedSeatId ? 0 : -1"
                                    :aria-label="labelFor(s, seat)"
                                    :aria-pressed="isSelected(seat)"
                                    :aria-disabled="seat.state === 'taken'"
                                    @focus="focusedSeatId = seat.id"
                                    @click="toggle(seat)"
                                    @keydown="onSeatKey($event, seat)" />
                                <!-- Taken seats carry a HATCH as well as a darker fill: status must
                                     never be encoded by colour alone, and two greys is exactly that. -->
                                <circle v-if="seat.state === 'taken'" r="9" fill="url(#seatTakenHatch)"
                                    opacity="0.55" pointer-events="none" />
                                <text v-if="seat.kind === 'wheelchair'" text-anchor="middle" dy="4" font-size="10" fill="#1f2937">&#9855;</text>
                                <!-- The seat's own number, once it is large enough to read. -->
                                <text v-else-if="showSeatLabels && seat.seat" text-anchor="middle" dy="3.5" font-size="9"
                                    :fill="labelInkFor(seat)" pointer-events="none">{{ seat.seat }}</text>
                            </g>
                        </g>
                        </g>
                    </svg>
                    <p class="mt-1 text-xs text-gray-400 dark:text-gray-500">{{ t.keyboardHint }}</p>
                </div>

                <!-- LIST: a complete alternative path, not a summary. A purchase can be finished
                     here with no map at all, which is what makes this usable with a screen reader. -->
                <div v-show="mode === 'list'" class="space-y-3">
                    <!-- Every level, not just the one on the map. The map shows one level at a time
                         because a balcony is not beside the stalls; the list has no such problem and
                         staying complete is the whole point of it. -->
                    <div v-for="lvl in myLevels" :key="`lvl-${lvl.id}`">
                        <h3 v-if="myLevels.length > 1" class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ lvl.name || t.level }}</h3>
                    <div v-for="s in lvl.sections" :key="s.id">
                        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ s.name }}</h4>
                        <div v-for="row in rowsOf(s)" :key="row.key" class="mt-1">
                            <span class="text-xs text-gray-500 dark:text-gray-400 me-2">{{ row.label }}</span>
                            <button v-for="seat in row.seats" :key="seat.id" type="button"
                                @click="toggle(seat)"
                                :disabled="seat.state === 'taken'"
                                :aria-label="labelFor(s, seat)"
                                :aria-pressed="isSelected(seat)"
                                class="inline-flex items-center justify-center w-8 h-8 m-0.5 rounded text-xs border transition-all duration-200 disabled:opacity-40 disabled:cursor-not-allowed"
                                :class="isSelected(seat)
                                    ? 'bg-[var(--brand-button-bg)] text-white border-transparent'
                                    : 'border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:border-[var(--brand-blue)]'">
                                {{ seat.seat || '&middot;' }}
                            </button>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Legend. Status is never colour alone: taken seats are also dimmed and
                     disabled, and every seat states itself in its aria-label. -->
                <div class="mt-3 flex flex-wrap gap-4 text-xs text-gray-500 dark:text-gray-400">
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full border" style="background:#e5e7eb;border-color:#9ca3af"></span>{{ t.legendAvailable }}</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full" style="background:var(--brand-blue)"></span>{{ t.legendSelected }}</span>
                    <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full border"
                        style="border-color:#6b7280;background:repeating-linear-gradient(45deg,#9ca3af,#9ca3af 1.5px,#6b7280 1.5px,#6b7280 3px)"></span>{{ t.legendTaken }}</span>
                </div>
            </template>

            <p v-else class="text-sm text-gray-500 dark:text-gray-400">{{ t.noSeats }}</p>
        </div>

        <div v-if="selectedSeats.length" class="mt-2 flex flex-wrap items-center gap-2" aria-live="polite">
            <span class="text-sm text-gray-600 dark:text-gray-400">{{ t.yourSeats }}:</span>
            <!-- One chip per seat, each droppable on its own. The comma-joined list this replaced
                 left "clear the lot and start again" as the only way to change your mind. -->
            <span v-for="seat in selectedSeats" :key="seat.id"
                class="inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-gray-800 ps-3 pe-1 py-1 text-sm text-gray-700 dark:text-gray-300">
                {{ seat.label }}
                <button type="button" @click="removeSeat(seat.id)"
                    :aria-label="(t.removeSeat || 'Remove :seat').replace(':seat', seat.label)"
                    class="inline-flex items-center justify-center w-5 h-5 rounded-full text-gray-500 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-200 dark:hover:bg-gray-700 transition-all duration-200">&times;</button>
            </span>
        </div>
        <!-- No waitlist call to action here on purpose: when EVERY band is gone the parent form
             hides the ticket rows and renders its own (Pro-gated) waitlist panel. This line only
             appears while other bands are still buyable, where a waitlist would be the wrong offer
             and the panel would not render anyway. -->
        <p v-else-if="soldOut" class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ t.soldOut }}</p>

        <!-- What the checkout POST actually claims. -->
        <input v-for="id in selected" :key="id" type="hidden" name="seat_ids[]" :value="id">
        <input type="hidden" :name="`tickets[${ticket.id}]`" :value="selected.length">
    </div>
</template>

<script setup>
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { loadMap, startPolling } from '../seat-map-store';
import { useMapViewport } from '../seat-map-viewport';

let instanceSeq = 0;

const props = defineProps({
    ticket: { type: Object, required: true },
    // Preformatted by the ticket form, which owns the currency. Building it here would mean a
    // second money formatter, and the repo has rules about hardcoded symbols for good reason.
    priceLabel: { type: String, default: '' },
    eventId: { type: String, required: true },
    date: { type: String, default: '' },
    stateUrl: { type: String, required: true },
    holdUrl: { type: String, required: true },
    csrfToken: { type: String, required: true },
    strings: { type: Object, default: () => ({}) },
});

const t = props.strings;
// Distinguishes this picker's seat elements from the other bands' on the same page.
const uid = ++instanceSeq;
const mode = ref('quantity');
const focusedSeatId = ref(null);
const qty = ref(0);
const selected = ref([]);
const map = ref(null);
const loading = ref(false);
const error = ref('');
const expiresAt = ref(null);
const now = ref(Date.now());
let tick = null;

const maxSelectable = computed(() => Math.max(0, Number(props.ticket.quantity) || 0));
const soldOut = computed(() => maxSelectable.value === 0);

/**
 * Levels of this venue that hold seats this ticket prices.
 *
 * A guest never picks out of somebody else's band, and levels are separate spaces so they are shown
 * one at a time. Drawing them together superimposed a balcony on the stalls, because every level's
 * first section is seeded at the same origin.
 */
const myLevels = computed(() => {
    if (!map.value) return [];
    return (map.value.levels || [])
        .map((lvl) => ({
            id: lvl.id,
            name: lvl.name,
            sections: (lvl.sections || []).filter((s) => s.ticket_id === props.ticket.id && s.kind !== 'standing'),
        }))
        .filter((lvl) => lvl.sections.length);
});

const activeLevelId = ref(null);
const activeLevel = computed(() => myLevels.value.find((l) => l.id === activeLevelId.value) || myLevels.value[0] || null);

/** Only what is on screen. */
const mySections = computed(() => (activeLevel.value && activeLevel.value.sections) || []);

/** Every section this ticket prices, on every level - what a held seat is resolved against. */
const allMySections = computed(() => myLevels.value.flatMap((l) => l.sections));

const svgEl = ref(null);

/** Bounding box of the level on screen, in content units. */
function contentBounds() {
    const xs = [], ys = [];
    mySections.value.forEach((s) => s.seats.forEach((seat) => {
        xs.push(s.x + seatX(s, seat)); ys.push(s.y + seatY(s, seat));
    }));
    if (!xs.length) return null;
    const pad = 18;
    const minX = Math.min(...xs) - pad, minY = Math.min(...ys) - pad - 14;
    return { minX, minY, w: Math.max(1, Math.max(...xs) - minX + pad), h: Math.max(1, Math.max(...ys) - minY + pad) };
}

const { zoom, pan, canvas, bind, fit, zoomBy, observe, revealPoint } = useMapViewport({ svgEl, contentBounds });

/**
 * The canvas IS the viewBox, and zoom/pan move the content inside it. Fitting the viewBox to the
 * content instead letterboxed a wide, shallow room in a tall box and left no way to get closer than
 * whatever the column happened to be - which on a phone is nothing like close enough.
 */
const viewBox = computed(() => `0 0 ${canvas.w} ${canvas.h}`);
const mapHeight = computed(() => {
    const b = contentBounds();
    if (!b) return '18rem';
    const ratio = Math.min(1.1, Math.max(0.28, b.h / b.w));
    return `${Math.round(Math.min(512, Math.max(160, canvas.w * ratio)))}px`;
});

/**
 * Seat numbers are only drawn once they are big enough to read.
 *
 * A 9-unit seat at 0.4 zoom is under 4 pixels: printing a label in it turns the map into grey mush,
 * which is why the list view existed as the only place to see a seat number at all.
 */
const showSeatLabels = computed(() => zoom.value >= 0.75);

/**
 * Sections whose seats carry no usable geometry.
 *
 * The designer's row builder assigns x/y, but a plan built any other way - the API, a future
 * importer, hand-inserted rows - can arrive with every seat at the origin. Rendered literally that
 * is 36 seats stacked in one place and a map nobody can use, so fall back to laying the section out
 * from row_position and position, which every seat has.
 */
const degenerateSections = computed(() => {
    const out = new Set();
    allMySections.value.forEach((s) => {
        const xs = new Set(s.seats.map((x) => x.x));
        const ys = new Set(s.seats.map((x) => x.y));
        if (s.seats.length > 1 && xs.size === 1 && ys.size === 1) out.add(s.id);
    });
    return out;
});

const GAP_X = 26;
const GAP_Y = 30;

function seatX(s, seat) {
    if (degenerateSections.value.has(s.id)) {
        return (rowsOf(s).find((r) => r.seats.includes(seat))?.seats.indexOf(seat) ?? 0) * GAP_X;
    }
    const tb = seat.table_id ? (s.tables || []).find((x) => x.id === seat.table_id) : null;
    return tb ? tb.x + seat.x : seat.x;
}
function seatY(s, seat) {
    if (degenerateSections.value.has(s.id)) {
        const rows = rowsOf(s);
        return rows.findIndex((r) => r.seats.includes(seat)) * GAP_Y;
    }
    const tb = seat.table_id ? (s.tables || []).find((x) => x.id === seat.table_id) : null;
    return tb ? tb.y + seat.y : seat.y;
}

/** Every seat this picker can move focus through, in reading order. */
const focusableSeats = computed(() => {
    const out = [];
    mySections.value.forEach((s) => s.seats.forEach((seat) => out.push(seat)));
    return out;
});

/**
 * Roving tabindex: ONE tab stop into the map, arrows to move within it.
 *
 * A tab stop per seat means a 2,000-seat house is 2,000 stops between the map and the checkout
 * button, which is worse for a keyboard user than the map not being focusable at all.
 */
function onSeatKey(evt, seat) {
    if (evt.key === 'Enter' || evt.key === ' ') {
        evt.preventDefault();
        toggle(seat);
        return;
    }

    const step = { ArrowRight: 1, ArrowDown: 1, ArrowLeft: -1, ArrowUp: -1 }[evt.key];
    if (!step) return;

    evt.preventDefault();
    const list = focusableSeats.value;
    const i = list.findIndex((x) => x.id === seat.id);
    const next = list[Math.min(list.length - 1, Math.max(0, i + step))];
    if (!next) return;

    focusedSeatId.value = next.id;
    const owner = mySections.value.find((s) => s.seats.includes(next));
    if (owner) revealPoint(owner.x + seatX(owner, next), owner.y + seatY(owner, next));
    nextTick(() => document.getElementById(`seat-${uid}-${next.id}`)?.focus());
}

function isSelected(seat) { return selected.value.includes(seat.id); }
/**
 * An available seat wears its section's colour, so a buyer can see the bands on the map.
 *
 * The designer sets a colour per section and the box office honours it; this map used to paint
 * every available seat the same grey, so the one person choosing between two price bands was the
 * only one who could not see them. Taken seats keep the flat grey AND the hatch over the top -
 * status is never colour alone.
 */
function fillFor(s, seat) {
    if (isSelected(seat)) return 'var(--brand-blue)';
    if (seat.state === 'taken') return '#9ca3af';
    if (seat.kind === 'wheelchair') return '#bfdbfe';
    return tint(s.color) || '#e5e7eb';
}
function strokeFor(s, seat) {
    if (isSelected(seat)) return 'var(--brand-blue)';
    if (seat.state === 'taken') return '#6b7280';
    return s.color || '#9ca3af';
}

/** A pale wash of the section colour, light enough to read a seat number on. */
function tint(hex) {
    const m = /^#?([0-9a-f]{6})$/i.exec(hex || '');
    if (!m) return null;
    const n = parseInt(m[1], 16);
    const mix = (c) => Math.round(c + (255 - c) * 0.72);
    return `rgb(${mix((n >> 16) & 255)} ${mix((n >> 8) & 255)} ${mix(n & 255)})`;
}

/** The first seat of a row, which is what the row label is pinned to. */
function rowLabelX(s, row) { return row.seats.length ? seatX(s, row.seats[0]) : 0; }
function rowLabelY(s, row) { return row.seats.length ? seatY(s, row.seats[0]) : 0; }

/** Dark enough to read on the tint above; the label is drawn inside the seat. */
function labelInkFor(seat) {
    return isSelected(seat) ? '#ffffff' : '#374151';
}

function labelFor(s, seat) {
    const bits = [s.name];
    if (seat.row) bits.push((t.rowPattern || 'Row :row').replace(':row', seat.row));
    if (seat.seat) bits.push((t.seatPattern || 'Seat :seat').replace(':seat', seat.seat));
    if (seat.kind === 'wheelchair') bits.push(t.wheelchair || '');
    bits.push(seat.state === 'taken' ? (t.legendTaken || '') : (isSelected(seat) ? (t.legendSelected || '') : (t.legendAvailable || '')));
    return bits.filter(Boolean).join(', ');
}

const selectedSeats = computed(() => {
    const out = [];
    allMySections.value.forEach((s) => s.seats.forEach((seat) => {
        if (isSelected(seat)) {
            const bits = [];
            if (seat.row) bits.push((t.rowPattern || 'Row :row').replace(':row', seat.row));
            if (seat.seat) bits.push((t.seatPattern || 'Seat :seat').replace(':seat', seat.seat));
            out.push({ id: seat.id, label: `${s.name} ${bits.join(' ')}`.trim() });
        }
    }));
    return out;
});

function rowsOf(s) {
    const groups = new Map();
    s.seats.forEach((seat) => {
        const key = seat.row || '';
        if (!groups.has(key)) groups.set(key, []);
        groups.get(key).push(seat);
    });
    return [...groups.entries()].map(([key, seats]) => ({
        key: `${s.id}-${key}`,
        label: key ? (t.rowPattern || 'Row :row').replace(':row', key) : '',
        seats,
    }));
}

async function post(url, body) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': props.csrfToken },
        credentials: 'same-origin',
        body: JSON.stringify(body),
    });
    return { ok: res.ok, status: res.status, data: await res.json().catch(() => ({})) };
}

function base() {
    return { event_id: props.eventId, date: props.date };
}

/** Quantity mode: the server chooses, the guest never sees a map. */
async function pickBest() {
    error.value = '';
    if (!qty.value) { await sendHold([]); return; }

    const { ok, data } = await post(props.holdUrl, { ...base(), ticket_id: props.ticket.id, quantity: qty.value });
    if (!ok) { error.value = data.error || t.holdFailed; return; }

    selected.value = data.held || [];
    expiresAt.value = data.expires_at ? Date.parse(data.expires_at) : null;
    if (selected.value.length < qty.value) error.value = t.fewerSeats;
    emitChange();
}

async function sendHold(ids, attempt = 0) {
    const { ok, status, data } = await post(props.holdUrl, { ...base(), seat_ids: ids });

    if (!ok) {
        error.value = data.error || t.holdFailed;
        // 409 carries the refreshed map, so the taken seat greys out immediately and only THAT
        // seat drops out of the selection - the rest of the choice survives.
        // Bounded: each pass drops at least one taken seat, so it terminates - but a server that
        // kept refusing a seat it also reports as free would otherwise spin forever.
        if (status === 409 && data.state && attempt < 3) {
            map.value = data.state;
            selected.value = selected.value.filter((id) => seatState(id) !== 'taken');
            await sendHold(selected.value, attempt + 1);
        }
        return;
    }

    selected.value = data.held || [];
    expiresAt.value = data.expires_at ? Date.parse(data.expires_at) : null;
    error.value = '';
    emitChange();
}

function seatState(id) {
    for (const s of allMySections.value) {
        const seat = s.seats.find((x) => x.id === id);
        if (seat) return seat.state;
    }
    return 'taken';
}

/**
 * Every free seat of a whole-table-only table, or just this seat.
 *
 * The server refuses a partial take (WholeTableRule), so clicking one chair at such a table has to
 * mean the table - otherwise the buyer's only feedback is a rejection they cannot act on.
 */
function tableGroup(seat) {
    if (! seat.table_id) return [seat];

    const section = allMySections.value.find((sec) => (sec.tables || []).some((x) => x.id === seat.table_id));
    const table = section && (section.tables || []).find((x) => x.id === seat.table_id);
    if (! table || table.booking_mode !== 'whole') return [seat];

    return section.seats.filter((s) => s.table_id === seat.table_id);
}

function toggle(seat) {
    if (seat.state === 'taken') return;

    const group = tableGroup(seat);
    const already = selected.value.includes(seat.id);

    if (already) {
        // Deselecting any chair of a whole table drops the whole table with it.
        selected.value = selected.value.filter((id) => ! group.some((s) => s.id === id));
    } else {
        // A whole table cannot be taken at all if part of it has gone.
        if (group.some((s) => s.state === 'taken')) { error.value = t.wholeTableGone; return; }

        const additions = group.filter((s) => ! selected.value.includes(s.id)).map((s) => s.id);
        if (selected.value.length + additions.length > maxSelectable.value) {
            error.value = t.maxReached;
            return;
        }
        selected.value.push(...additions);
    }

    qty.value = selected.value.length;
    sendHold(selected.value);
}

async function openMap() {
    mode.value = 'map';
    await load();
}
function closeMap() { mode.value = 'quantity'; }

async function load() {
    if (map.value) return;

    loading.value = true;
    try {
        // Shared across every band's picker on the page, and fetched at most once.
        const data = await loadMap(props.stateUrl, props.eventId, props.date);
        if (!data) { error.value = t.loadFailed; return; }
        map.value = data;

        // Seats already held by this session read back as "mine" and stay selected across a reload.
        const mine = [];
        allMySections.value.forEach((s) => s.seats.forEach((seat) => { if (seat.state === 'mine') mine.push(seat.id); }));
        if (mine.length) { selected.value = mine; qty.value = mine.length; emitChange(); }

        activeLevelId.value = myLevels.value[0]?.id ?? null;
        focusedSeatId.value = focusableSeats.value.find((x) => x.state !== 'taken')?.id
            ?? focusableSeats.value[0]?.id ?? null;

        // One poller per map, started only once a map is actually on screen.
        startPolling(props.stateUrl, props.eventId, props.date, map.value, onPolled);
    } finally {
        loading.value = false;
    }

    // AFTER loading clears, not before: the template renders a "loading" line in place of the map
    // while it is true, so measuring inside the try above found no <svg> at all. canvas.w then kept
    // its 900 default and the map was sized for a canvas three times the width of the real one.
    await nextTick();
    observe();
    fit();
}

const lapsed = ref(false);

/**
 * The shared poller mutated the map in place; drop anything this picker has lost.
 *
 * Losing every seat at once is a lapsed hold, not a race with another buyer, and it used to happen
 * in total silence: the countdown stopped at 0:00 and the seats simply left the selection. Say so,
 * so the buyer knows to pick again rather than wondering what they did wrong.
 */
function onPolled() {
    const before = selected.value.length;
    selected.value = selected.value.filter((id) => seatState(id) === 'mine');

    if (before && !selected.value.length) {
        lapsed.value = true;
        expiresAt.value = null;
        qty.value = 0;
        emitChange();
    }
}

/** Clear the lapsed notice and go again from the map the poll has already refreshed. */
function pickAgain() {
    lapsed.value = false;
    error.value = '';
}

const countdown = computed(() => {
    if (!expiresAt.value || !selected.value.length) return '';
    const left = Math.max(0, Math.floor((expiresAt.value - now.value) / 1000));
    const m = Math.floor(left / 60), s = left % 60;
    return `${m}:${String(s).padStart(2, '0')}`;
});
const countdownSoon = computed(() => expiresAt.value && (expiresAt.value - now.value) < 120000);

async function pushHold() { await sendHold(selected.value); }

/** Drop one seat without clearing the lot - what the comma-joined list gave no way to do. */
async function removeSeat(id) {
    const next = selected.value.filter((x) => x !== id);
    selected.value = next;
    qty.value = next.length;
    await sendHold(next);
}

/**
 * The parent ticket form owns the running total and its own validation, so tell it what changed
 * rather than reaching into it. Mirrors the es-cart-add event the cart already uses.
 */
function emitChange() {
    document.dispatchEvent(new CustomEvent('es-seats-changed', {
        detail: { ticketId: props.ticket.id, quantity: selected.value.length, seatIds: [...selected.value] },
    }));
}

// Each level has its own extent, so a switch has to reframe or the new level lands off screen.
watch(activeLevelId, () => nextTick(fit));

onMounted(() => {
    // Deliberately does NOT fetch the map. The default flow is the quantity dropdown, where the map
    // is never shown - loading it on mount pulled the whole house once per band for nothing.
    tick = setInterval(() => { now.value = Date.now(); }, 1000);
});
onBeforeUnmount(() => clearInterval(tick));
</script>
